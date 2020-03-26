<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Model;
use Think\Cache\Driver\Redis;
use Think\Upload;
use QL\QueryList;
use Cliapi\Controller\ZixuncaijiController;
/**
 * 后台管理资讯帖子
 */
class ZixunController extends AdminbaseController {
    public $Caijiobj;
    public function _initialize() {
        parent::_initialize();
        $this->Caijiobj = new ZixuncaijiController();
    }

    

    public function indexInfo() {
        $show_model = M('show');
        $count = $show_model->count();
        $page = $this->page($count, 20);
        $info = $show_model
                ->where(array('type' => 2, 'status' => 1))
                ->order('addtime desc')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('addtime desc')
                ->select();
        $this->assign("data", $info);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    //标签分类列表
    public function typeList() {
        $arr = array(1, 2);
        $where['type'] = array("in", $arr);
        $where['status'] = 0;

        $info = M('club_type')->where($where)->order('type desc')->select();
        $page = $this->page(count($info), 10);

        $this->assign("typeInfo", $info);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    //后台添加资讯标签分类
    public function add_basket_type() {
        $this->display();
    }

    public function add_type_do() {
        if (IS_POST) {
            $data = I('post.');
            if (empty($data)) {
                $this->error = '请填写正确的信息/不能为空';
            }
            $info = M('club_type')->where(array('name' => $data['name']))->find();
            if (!empty($info)) {
                $this->error = '该标签已存在';
            }
            $res = M('club_type')->add($data);
            if ($res) {
                $action = "添加标签：{$data['id']}";
                setAdminLog($action);
                $this->success('添加成功');
            } else {
                $this->error('出现错误');
            }
        }
    }

    //修改分类标签
    function edit_type() {
        $id = intval($_GET['id']);
        $info = M('club_type')->where(array('id' => $id))->find();

        $this->assign('info', $info);
        $this->display();
    }

    //标签删除
    public function type_del() {
        $id = intval($_GET['id']);
        if ($_GET['type'] == 'del') {
            $result = M("club_type")->where("id=" . $id)->setField(array("status" => 1));
        } else {
            $result = M("club_type")->where("id=" . $id)->setField(array("status" => 0));
        }
        if ($result !== false) {
            $action = "操作标签：{$id}";
            setAdminLog($action);
            $this->success('操作成功');
        } else {
            $this->error('出现错误');
        }
    }

    //后台添加资讯
    public function addMessage() {
        $arr = array(1, 2);
        $where['type'] = array("in", $arr);
        $where['status'] = 0;
        $info = M('club_type')->where($where)->select();

        $userInfo = M('users')->field('id,user_nicename')->select();
        $clubInfo = M('clubs')->field('id,name')->select();

        $this->assign("info", $info);
        $this->assign("userInfo", $userInfo);
        $this->assign("clubInfo", $clubInfo);
        $this->display();
    }

    public function add_message_do() {
        if (IS_POST) {
            $data = I('post.');

            if (empty($data)) {
                $this->error = '所填信息不能为空';
            }
            $_type = M('club_type')->where(array('id' => $data['sel_type']))->find();
            if (empty($_type)) {
                $this->error = '没有该分类的标签';
            }
            $data['type'] = 2;
            $data['addtime'] = time();

            //解决多图片问题
            if (strpos($data['data'], ',' === false)) {    //不包含
                $this->error = '出现错误';
            } else {
                $data['data'] = ltrim($data['data'], ",");
            }

            $res = D('Show')->add($data);
            //更改草稿箱状态
            $lid = $_SESSION['ADMIN_ID'];
            M('draft')->where(array('lid' => $lid))->save(array('status=2'));

            if ($res == true) {
                $action = "添加资讯：{$data['id']}";
                setAdminLog($action);
                $this->success('添加成功');
            } else {
                $this->error(D('Show')->getError());
            }
        }
    }

    //草稿箱
    public function draft() {
        $info = M('draft')->order('addtime desc,status asc')->select();

        foreach ($info as $k => &$v) {
            $v['content'] = json_decode($v['content'], true);
            if ($v['content']['uid'] == 0) {
                $v['content']['uid'] = '未选择';
            }
            $uInfo1 = M('users')->field('user_nicename')->where(array('id' => $v['lid']))->find();
            $uInfo2 = M('users')->field('user_nicename')->where(array('id' => $v['content']['uid']))->find();
            $v['lid'] = $uInfo1['user_nicename'];
            $v['content']['uid'] = $uInfo2['user_nicename'];
        }

        $page = $this->page(count($info), 10);

        $this->assign("data", $info);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    //保存草稿
    public function addDraft() {
        $data = $_POST;

        $lid = $_SESSION['ADMIN_ID'];
        if ($data['bigType'] == '俱乐部') {
            $data['is_club'] = $data['is_club'];
        } else {
            unset($data['is_club']);
        }
        if (!empty($data['img'])) {
            $data['img'] = ltrim($data['img'], ",");
        }

        $add['lid'] = $lid;
        $add['content'] = json_encode($data);
        $add['status'] = 1;
        $add['addtime'] = time();

        //查看该用户是否已有保存未发送的草稿   如果有则替换
        $draftInfo = M('draft')->where(array('lid' => $lid, 'status=1'))->find();
        if (empty($draftInfo)) {
            //实现添加操作
            M('draft')->add($add);
        } else {
            //实现更新操作
            M('draft')->where(array('id' => $draftInfo['id']))->save($add);
        }
        echo M()->getLastSql();
    }

    //草稿箱发布
    public function fabuDraft() {
        $id = $_POST['id'];
        $info = M('draft')->where(array('id' => $id))->find();

        $info = json_decode($info['content'], true);
        echo "<pre>";
        print_r($info);
        echo "</pre>";
    }

    //图片上传
    function uploadPic() {
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize = 3145728; // 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
        $upload->autoSub = true; // 开启子目录保存 并以日期（格式为Ymd）为子目录
        $upload->subName = array('date', 'Ymd');
        $upload->rootPath = './data/upload/showData/';                       // 设置根路径
        $upload->savePath = '';     // 设置附件上传根目录
        $upload->saveName = array('uniqid', ''); // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->uploadOne($_FILES['file']);
        if ($info) {
            $info['uid'] = 27543;
            $info['name'] = $info['savepath'] . $info['savename'];
            $info['path'] = "/data/upload/goods/" . $info['savepath'] . $info['savename'];
            $info['create_time'] = time();

            //将获取到的图片信息加入图片表  获取加入后的主键id
            M('picture')->add($info);
            $id = M()->getLastInsID();
            $arr = [
                'code' => 0,
                'msg' => '上传成功',
                'src' => $info['savepath'] . $info['savename'],
                'pid' => $id
            ];
            $this->ajaxReturn($arr);
        } else {
            // 上传失败获取错误信息
            $this->error($upload->getError());
        }
    }

    //删除图片
    function delPic() {
        $url = I('post.key');
        if (empty($url)) {
            $this->ajaxReturn(array('code' => -1, 'info' => '参数错误'));
        } else {
            unlink("./data/upload/showData/" . $url);
            M('picture')->where(array('name' => $url))->delete();
            $this->ajaxReturn(array('code' => 1, 'info' => '已取消'));
        }
    }

    //资讯标签列表
    public function biaoQian() {
        $index_mol = $_POST['mol'];
        $info = M('club_type')->where(array('type' => $index_mol, 'status' => 0))->select();

        $this->ajaxReturn(array('status' => 1, 'info' => 'seccess', 'data' => $info));
    }

    //详情
    public function detail() {
        $id = $_REQUEST['id'];
        $detailInfo = M('show')->where(array('id' => $id))->find();   //帖子详情
        //处理图片问题
        if (is_numeric(substr($detailInfo['data'], 0, 1))) {            //如果以数字开头
            // if(strpos($detailInfo['data'],',') === false){
            //     $detailInfo['pics'] = [
            //         'o_no'=>substr(rand(111111111,999999999),0,2),
            //         'path'=>getImgVideo($detailInfo['data'])
            //     ];
            // }else{
            $detailInfo['data'] = getImgVideo($detailInfo['data']);
            // }
        }

        //处理作者问题
        if ($detailInfo['uid'] == '' || $detailInfo['uid'] == 0) {
            
        } else {
            $userInfo = M('users')->field('user_nicename')->where(array('id' => $detailInfo['uid']))->find();     //用户详情
            $detailInfo['uname'] = $userInfo['user_nicename'];
        }
        //评论
        $detailInfo['comment'] = $this->commentList($id);

        $this->assign('data', $detailInfo);
        $this->display();
    }

    //修改详情信息
    public function edit_message() {
        if (IS_POST) {
            $data = M('show')->create($_POST);
            
            $result = M('show')->save();

            if ($result !== false) {
                $action = "修改升级信息：{$data['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        }
    }

    //删除
    function del() {
        $id = intval($_GET['id']);
        $result = M("show")->where("id=" . $id)->setField(array("status" => 2));
        if ($result !== false) {
            $action = "删除帖子：{$id}";
            setAdminLog($action);
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    //资讯认证
    function authSqs() {
        $id = $_REQUEST['id'];
        $sqsInfo = M('show')->field('id,title,sqs_status')->where(array('id' => $id))->find();
        $this->assign('data', $sqsInfo);
        $this->display();
    }

    function authSqs_do() {
        if (IS_POST) {
            $show = M("show");
            $data = $show->create();
            $result = $show->save();
            if ($result !== false) {
                $action = "修改资讯认证：{$data['id']}";
                setAdminLog($action);
                $this->success('认证成功');
            } else {
                $this->error('认证失败');
            }
        }
    }

    //评论列表
    function commentList($id) {
        $info = D('Comment')->getCommlist($id);
        return $info;
    }

    //爬取数据
    public function paqu_() {
        require 'vendor/autoload.php';
        header("Content-type:text/html;charset=utf-8");
        // $querylist= new QueryList();
        $url = 'http://www.ppsport.com/';
        $data = QueryList::get($url)
                        ->rules([
                            //'title'=>array('.render1-info-list a .ads-img','alt'),
                            'iii' => array('.fixed_wrap.info-panel-wrap .info-panel .main-info .match-tw div a', 'href'),
                            'img' => array('.fixed_wrap .info-panel .main-info .match-tw .tw-head a .tw-img img', 'data-original'),
                            // 'bao' => array('.info-panel-wrap .info-panel .main-info .match-tw .tw-head a .tw-txt span','text'),
                            'bao' => array('.fixed_wrap .info-single-panel .main-info .match-tw .tw-head a .tw-txt .txt-title', 'text'),
                            'time' => array('.info-panel-wrap .info-panel .main-info .match-tw .tw-head .txt-bottom .txt-time', 'text'),
                        ])->query()->getData();
        $datas = $data->all();
        // $this->assign('data',$datas);
        $this->display('caiji');
        // echo "<pre>";
        // print_r($data->all());
        // echo "<pre>";
    }

    //爬取数据
    public function paqu(){
        $this->display('zhuaqu');
    }

    public function paquList(){
        $type = I('type');
        $fun = $type.'_list';
        $data = $this->Caijiobj->$fun();
        
        //$data = $this->Caijiobj->sinabasketball_content();
        //print_r($data);exit;
        $this->assign('class', $type);
        $this->assign('data', $data);
        $this->display('donqiudi');
    }
    
    
    //懂球帝足球
    public function donqiudi(){
        $data = $this->Caijiobj->donqiudi_list();
        print_r($data);
    }

    

    //足球
    public function zu() {
        //1 是足球 2 是篮球
        $parem = I('get.');

        if (empty($parem['ids'])) {
            $this->error('参数错误');
        }
        //足球
        if ($parem['ids'] == 1) {
            $url = "http://snsis.suning.com/snsis-web/client/queryInforList/9%20.htm?_callback=channelinfo&callback=channelinfo";
        }
        //篮球
        if ($parem['ids'] == 2) {
            $url = 'http://snsis.suning.com/snsis-web/client/queryInforList/608%20.htm?_callback=channelinfo&callback=channelinfo';
        }

        $data = file_get_contents($url);
        $sub = substr($data, 12);
        $dd = substr($sub, 0, strlen($sub) - 1);
        //转数组
        $array = json_decode($dd, true);
        // $data = $this->curl($url,'','GET');
        $this->assign('data', $array);
        $this->assign('ids', $parem['ids']);

        $this->display();
    }

    //篮球
    public function lan() {
        $url = 'http://snsis.suning.com/snsis-web/client/queryInforList/608%20.htm?_callback=channelinfo&callback=channelinfo';
        $data = file_get_contents($url);
        $sub = substr($data, 12);
        $dd = substr($sub, 0, strlen($sub) - 1);
        //转数组
        $array = json_decode($dd, true);
        // $data = $this->curl($url,'','GET');
        $this->assign('data', $array);
        // echo "<pre>";
        // print_r($array);
        // echo "<pre>";die;

        $this->display('zu');
    }

    
    public function chaku(){
        if (IS_AJAX) {
            $class = I('class');
            $count = $this->Caijiobj->$class();
            $this->ajaxReturn(array('status' => 1, 'info' => '批量入库成功'.$count.'条'));
        }
    }
    
    public function chaku_() {
        require 'vendor/autoload.php';
        if (IS_AJAX) {
            $parem = I('get.da');
            $ids = I('get.ids');

            //足球
            if ($ids == 1) {
                $ctype = 1;
            }
            //篮球
            if ($ids == 2) {
                $ctype = 2;
            }


            foreach ($parem as $key => $value) {
                if ($value == 'javascript:;' || $value == '') {
                    unset($parem[$key]);
                }
            }
            //var_dump($parem);die;
            foreach ($parem as $key => $value) {
                $data = QueryList::get($value)
                                // 设置采集规则
                                ->rules([
                                    'title' => array('.cont .artinfo .arttitle', 'text'),
                                    'add_time' => array('.cont .artinfo .subtitle', 'text'),
                                    'content' => array('#articleContent', 'html'),
                                    'img' => array('.layoutRow img', 'src'),
                                ])->query()->getData();
                //打印结果
                $img_out[] = $data->all();
            }
            //var_dump($img_out);die;
            $dass = [];
            try {
                foreach ($img_out as $key => $values) {
                    foreach ($values as $k => $value) {
                        //处理接收的日期时间
                        $time = substr($value['add_time'], 0, 26);
                        $arr = date_parse_from_format('Y年m月d日H:i:s', $time);
                        $ts = mktime($arr['hour'], $arr['minute'], $arr['second'], $arr['month'], $arr['day'], $arr['year']);
                        //php获取今日开始时间戳和结束时间戳
                        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                        //处理作者
                        // $uname = substr($value['add_time'],40,25);
                        $unames = trim(strrchr($value['add_time'], ':'), ':');
                        $uname = substr($unames, strripos($unames, "：") + 1);
                        //获取今日的数据
                        if ($ts > $beginToday && $ts < $endToday) {
                            $dass['title'] = $value['title'];
                            $dass['uname'] = $uname;
                            $dass['content'] = $value['content'];
                            $dass['addtime'] = $ts;
                            $dass['data'] = $value['img'];
                            $dass['identifshi'] = 1;
                            $dass['type'] = 2;
                            $dass['index_mol'] = $ctype;
                            $typeInfo = M('club_type')->where(array('status' => 0, 'type' => $ctype))->select();
                            unset($typeInfo[4]);
                            $type_one = array_rand($typeInfo, 1);
                            $dass['sel_type'] = $typeInfo[$type_one]['id'];
                            M('Show')->add($dass);
                        }
                    }
                }
                //var_dump($dass);die;
                //将今日数据 批量入库
                // for ($i=0;$i<count($dass['title']);$i++){
                //     $insert = array();
                //     $insert['title'] = $dass['title'][$i];
                //     $insert['uname'] = $dass['uname'][$i];
                //     $insert['content'] = $dass['content'][$i];
                //     $insert['addtime'] = $dass['addtime'][$i];
                //     $insert['identifshi'] = 1;       //标识   爬虫抓取
                //     $insert['type'] = 2;             //类型  资讯
                //     $insert['index_mol']= $ctype;    //首页类型
                //     //$insert['data'] = $dass['img']
                //     //随机获取类型
                //     $typeInfo = M('club_type')->where(array('status'=>0,'type'=>$ctype))->select();
                //     $type_one = array_rand($typeInfo,1);
                //     $insert['sel_type'] = $typeInfo[$type_one]['id'];
                //     $res = M('Show')->add($insert);
                // }
                // if($res){
                $this->ajaxReturn(array('status' => 1, 'info' => '批量入库成功'));
                // }
            } catch (\Exception $e) {
                $this->ajaxReturn(array('status' => 0, 'info' => '批量入库失败'));
            }
        }
    }

    function zuqiu($url) {
        // 采集规则
        $data = QueryList::get($url)
                        // 设置采集规则
                        ->rules([
                            'title' => array('div h1', 'text'),
                            'add_time' => array('div .subtitle', 'text'),
                            'content' => array('#articleContent p', 'text'),
                            'img' => array('.layoutRow img', 'src')
                        ])
                        ->query()->getData();
        //打印结果
        $img_out = $data->all();

        //处理时间
        $time = substr($img_out[0]['add_time'], 0, 26);
        $arr = date_parse_from_format('Y年m月d日H:i:s', $time);
        $ts = mktime($arr['hour'], $arr['minute'], $arr['second'], $arr['month'], $arr['day'], $arr['year']);
        //处理作者
        $uname = substr($img_out[0]['add_time'], 39, 23);
        $arr = [];
        foreach ($img_out as $k => $v) {
            $arr[] = $v['content'];
            $arr = array_merge($arr);
        }
        //随机获取类型
        $typeInfo = M('club_type')->where(array('status' => 0, 'type' => 1))->select();
        $type_one = array_rand($typeInfo, 1);

        $addData['title'] = $img_out[0]['title'];
        $addData['addtime'] = $ts;
        $addData['uname'] = $uname;

        $addData['type'] = 2;
        $addData['sel_type'] = $typeInfo[$type_one]['id'];
        $addData['data'] = $img_out[0]['img'];
        $addData['content'] = implode('', $arr);
        return $addData;
    }

    public function curl($url, $data, $type) {
        $data = json_encode($data);
        $ch = curl_init(); //初始化CURL句柄 
        curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type); //设置请求方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); //设置提交的字符串
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

}
