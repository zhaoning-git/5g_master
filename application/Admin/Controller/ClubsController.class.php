<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use http\Client\Request;

/**
 * 俱乐部管理
 */
class ClubsController extends AdminbaseController{

    public function index(){
        $club_model = M('clubs');
        $count = $club_model->count();
        $page = $this->page($count, 10);
        $prefix = C("DB_PREFIX");

        $data = M('clubs c')
            ->join("{$prefix}picture p on c.headimg = p.id")
            ->field('p.path as url,c.sort,c.is_hot,c.id,c.name,c.level,c.is_del,c.gold_coin,c.silver_coin,c.type,c.create_time,c.update_time')
            ->order("c.create_time desc, c.sort desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach ($data as $k=>&$v){
            $type = M('club_type')->where(array('id'=>$v['type']))->find();
            $v['typename'] = $type['name'];
        }

        //print_r($data);exit;
        $this->assign('index', 'class="active"');
        $this->assign("data", $data);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    //热聊俱乐部
    public function Hotchat(){
        $map['c.is_hotchat'] = 1;
        $this->assign('Hotchat', 'class="active"');
        $result = $this->ClubsData($map);
    }


    //官方俱乐部
    public function Gf(){
        $map['c.type'] = 10012;
        $this->assign('Gf', 'class="active"');
        $result = $this->ClubsData($map);

    }

    //热门推荐
    public function Hot(){
        $map['c.is_hot'] = 1;
        $this->assign('Hot', 'class="active"');
        $result = $this->ClubsData($map);

    }

    public function ClubsData($map=array()){
        $map['c.is_del'] = 0;
        $club_model = M('clubs c');
        $count = $club_model->where($map)->count();
        $page = $this->page($count, 10);
        $prefix = C("DB_PREFIX");

        $data = M('clubs c')
            ->join("{$prefix}picture p on c.headimg = p.id")
            ->field('p.path as url,c.sort,c.is_hot,c.sort,c.id,c.name,c.level,c.is_del,c.gold_coin,c.silver_coin,c.type,c.create_time,c.update_time')
            ->order("c.sort desc, c.create_time desc")
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach ($data as $k=>&$v){
            $type = M('club_type')->where(array('id'=>$v['type']))->find();
            $v['typename'] = $type['name'];
        }
        $this->assign("data", $data);
        $this->assign("page", $page->show('Admin'));
        $this->display('index');
        // return ['list'=>$data , 'page'=>$page->show('Admin')];
    }

    //修改排序
    public function upSort(){
        $data = I();
        //$Sort = array_unique();
        foreach($data['sort'] as $key=>$val){
            if($val){
                M('Clubs')->where(['id'=>$data['cid'][$key]])->setField('sort', $val);
            }
        }

        $this->ajaxReturn(['status'=>1, 'info'=>'成功']);
    }




    //标签分类列表
    public function typeList(){
        $arr = array(4,5);
        $where['type']=array("in",$arr);


            $whereData=[];
            $data=I('get.');
            $query = http_build_query($data);
            $whereData['page']=!empty($data['page'])? $data['page'] : 1;
            $whereData['size']=!empty($data['size'])? $data['page'] :C('branches');
            
            //获取总条数
            $totl = D('club_type')
                  ->field('name,id,type,status')
                  ->where($where)
                  ->count();
           
            //总数转换成多少页
            $pageTo=ceil($totl/C('branches'));
            $from=($whereData['page']-1)*$whereData['size'];
          
            $datas = M('club_type')
                  ->field('name,id,type,status')
                  ->limit($from,$whereData['size'])
                  ->where($where)
                  ->select();
            $this->assign([
                'data'=>$datas,
                'pageTo'=>$pageTo,
                'dang' =>$whereData['page'],
                'query'=> $query,

            ]);


        /*
        $info = M('club_type')->where($where)->select();
        $page = $this->page(count($info), 10);

        $this->assign("typeInfo", $info);
        $this->assign("page", $page->show('Admin'));
        */
        $this->display();
    }

    //添加分类标签
    public function add_type(){
        $parem = I('post.');
        
        if(IS_POST){
            $club_type = M('club_type');

            if($club_type->create())
            {
                $result = $club_type->add($parem);

                if($result)
                {
                    $this->success('添加成功');
                }
                $this->error('添加失败');
            }
        }

        $this->display();

    }

    //修改分类标签
    public function edit_type(){
        $id=intval($_GET['id']);
        $info = M('club_type')->where(array('id'=>$id))->find();

        $this->assign('info', $info);
        $this->display();
    }

    function edit_type_do(){
        if(IS_POST)
        {
            $club=M("club_type");
            $data = $club->create();
            $result=$club->save();
            if($result!==false)
            {
                $action="修改升级信息：{$data['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }
        }
    }

    //创建俱乐部
    function add_club(){
        $type = array(4,5);
        $where['type']=array("in",$type);
        $where['status'] = 0;

        $type = M('club_type')->where($where)->select();
        $this->assign('type',$type);
        $this->display();
    }

    //搜索
    function goSearch(){
        $name = I('name');
        $prefix= C("DB_PREFIX");
        $info = M('clubs c')
            ->join("{$prefix}picture p on c.headimg = p.id")
            ->field('c.id,c.name,p.url,c.level,c.is_del,c.gold_coin,c.silver_coin,c.create_time,c.update_time')
            ->where(array('c.name'=>['like',"%$name%"]))
            ->select();
        $this->ajaxReturn(array('data'=>$info));
    }

    function add_club_do(){
        if(IS_POST){
            $data  =  I('post.');
            if(empty($data['uid'])){
                $this->error('用户id不能为空');
            }
            // $info = $this->uploadPic();

           
                // $info['uid'] = $data['uid'];
                // $info['name'] =$info['savepath'].$info['savename'];
                // $info['path'] ="/data/upload/headimg/".$info['savepath'].$info['savename'];
                // $info['create_time'] = time();

                // //将获取到的图片信息加入图片表  获取加入后的主键id
                // M('picture')->add($info);
                // $id = M()->getLastInsID();
                //$data['headimg'] = $id;

                $res = D('Clubs')->Insert($data);
                if($res == true){
                    $this->success('创建俱乐部成功');
                }else{
                    $this->error(D('Clubs')->getError());
                }
            



        }
    }

    function uploadPic(){
        $rootPath = '/data/upload/Picture/';
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize =     3145728 ;// 设置附件上传大小
        $upload->exts    =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->autoSub = true; // 开启子目录保存 并以日期（格式为Ymd）为子目录
        $upload->subName = array('date','Ymd');
        $upload->rootPath =  '.'.$rootPath;                       // 设置根路径
        $upload->savePath =  '';     // 设置附件上传根目录
        $upload->saveName = array('uniqid',''); // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->uploadOne($_FILES['file']);

        $path = $rootPath.$info['savepath'].$info['savename'];
        
        $map = array('md5' => $info['md5'], 'sha1' => $info['sha1']);
        $Picture = M('Picture')->where($map)->find();
        if(!empty($Picture)){
            unlink($path);
            $path = $Picture['path'];
            $pid = $Picture['id'];
        }else{
            //保存到表Picture
            $data['name'] = $info['name'];
            $data['type'] = $info['type'];
            $data['path'] = $path;
            $data['md5'] = $info['md5'];
            $data['sha1'] = $info['sha1'];
            $data['status'] = 1;
            $data['create_time'] = time();
            $pid = M('Picture')->add($data);
        }
        echo json_encode(['code'=>0,'msg'=>'','path'=>$path,'pid'=>$pid]);
        exit;
    }

    //修改
    function xiugai(){
        $id=intval($_GET['id']);
        if($id){
            $prefix= C("DB_PREFIX");
            $club = M('clubs c')
                ->join("{$prefix}picture p on c.headimg = p.id")
                ->field('p.path as url,c.id,c.headimg,is_hotchat,c.name,c.level,c.is_hot,c.is_del,c.gold_coin,c.silver_coin,c.create_time,c.update_time,c.type')
                ->where(array('c.id'=>$id))
                ->find();

            $arr = array(4,5);
            $where['type']=array("in",$arr);
            $info = M('club_type')->where($where)->select();
            $this->assign('club', $club);
            $this->assign('type', $info);

        }else {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function xiugai_do(){
        if(IS_POST)
        {
            $club=M("Clubs");
            $data = $club->create();
            $data['update_time']=time();
            $club->istip=1;
            $result=$club->save();
            if($result!==false)
            {
                $action="修改俱乐部信息：{$data['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }
        }
    }

    //审核
    function edit(){
        $prefix= C("DB_PREFIX");
        $id=intval($_GET['id']);
        $chuang = M('users u')
            ->join("{$prefix}clubs c on u.id = c.uid")
            ->field('u.user_nicename')
            ->find();
        if($id){
            $prefix= C("DB_PREFIX");
            $club = M('clubs c')
                ->join("{$prefix}picture p on c.headimg = p.id")
                ->field('p.url,c.id,c.name,c.level,c.is_del,c.gold_coin,c.silver_coin,c.create_time,c.update_time')
                ->find(array('c.id'=>$id));

            $this->assign('club', $club);
            $this->assign('chuang', $chuang);
        }else {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function edit_do()
    {
        if(IS_POST)
        {
            $club=M("Clubs");
            $data = $club->create();
            $data['update_time']=time();
            $club->istip=1;
            $result=$club->save();
            if($result!==false)
            {
                $action="修改俱乐部信息：{$data['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }
        }
    }

    //删除
    function del()
    {
        $prefix= C("DB_PREFIX");
        $id=intval($_GET['id']);
        if($id)
        {
            $info = M('clubs c')
                ->join("{$prefix}club_mem m on c.id = m.cid")
                ->where(array('cid'=>$id))
                ->select();
            if(count($info) > 2){
                $this->error('俱乐部下还有成员存在，不可删除');
            }else{
                $result=M("clubs")->where("id=".$id)->setField(array("is_del"=>1,'update_time'=>time()));
                if($result!==false)
                {
                    $action="删除俱乐部：{$id}";
                    setAdminLog($action);
                    $this->success('删除成功');
                }
                else
                {
                    $this->error('删除失败');
                }
            }

        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    //恢复
    function enable()
    {
        $id=intval($_GET['id']);
        if($id)
        {
            $result=M("clubs")->where("id=".$id)->setField(array("is_del"=>0,'update_time'=>time()));
            if($result!==false)
            {
                $action="恢复俱乐部：{$id}";
                setAdminLog($action);
                $this->success('恢复成功');
            }
            else
            {
                $this->error('恢复失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    //成员列表
    function memList(){
        $prefix= C("DB_PREFIX");
        $page = $this->page(M("club_mem")->count(), 20);
        $memInfo = M('clubs c')
            ->join("{$prefix}club_mem m on c.id = m.cid")
            ->join("{$prefix}users u on m.mid = u.id")
            ->field('c.id,c.name,u.id as uid,u.user_nicename,u.last_login_ip,u.gold_coin,u.silver_coin,m.status,u.user_status')
            ->order('status','asc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->assign("memInfo", $memInfo);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    //俱乐部成员添加
    function mem_add()
    {
        $this->display();
    }

    function mem_add_post()
    {
        if(IS_POST)
        {
            $uid=$_REQUEST['mem_id'];
            $club_id=$_REQUEST['club_id'];
            $data = [
                'club_id'=>$club_id,
                'mem_id'=>$uid
            ];
            $res = D('Clubs')->InJect($data);
            if($res == true){
                $this->success('已申请');
            }else{
                $this->error(D('Clubs')->getError());
            }
        }
    }

    //审核
    function mem_check(){
        $id = intval($_GET['id']);
        $uid = intval($_GET['uid']);

        if($id){
            $club_mem=M("club_mem")->where(array('mid'=>$uid))->find();
            $userinfo=M("users")->field("user_nicename")->where(array('id'=>$club_mem['mid']))->find();

            $clubInfo=M("clubs")->field("name,id")->where(array('id'=>$club_mem['cid']))->find();
            $data = [
                'mem_name'=>$userinfo['user_nicename'],
                'club_name'=>$clubInfo['name'],
                'cid'=>$clubInfo['id'],
                'status'=>$club_mem['status'],
                'uid'=>$uid
            ];
            $this->assign("data", $data);
        }else
        {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function mem_check_do()
    {
        if(IS_POST)
        {
            $mem_model=M("club_mem");
            $data = I('post.');
            $info = $mem_model->where(array('mid'=>$data['mid'],'cid'=>$data['cid']))->find();

            $where = [
                'id'=>$info['id']
            ];
            $clubInfo=$mem_model->where($where)->save(array('status'=>$data['status'],'add_time'=>time()));

            if($clubInfo == 1)
            {
                $uid=$_POST['uid'];
                $action="审核信息：{$uid}";
                setAdminLog($action);
                $this->success('审核成功');
            }
            else
            {
                $this->error('请勿重复相同的操作');
            }
        }
    }

    //删除成员
    function mem_del()
    {
        $ids = $_GET;
        if($ids)
        {
            $mem_model=M("club_mem");
            $info = $mem_model->where(array('mid'=>$ids['id'],'cid'=>$ids['cid']))->find();
            $where = [
                'id'=>$info['id']
            ];
            $result=$mem_model->where($where)->save(array('status'=>2));
            if($result!==false)
            {
                $action="删除俱乐部成员：{$ids['mid']}";
                setAdminLog($action);
                $this->success('删除成功');
            }
            else
            {
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
    }

    //俱乐部升级
    function upGrade(){
        $club_model = M('club_upgrade');
        $count = $club_model->count();
        $page = $this->page($count, 20);
        $data = $club_model
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign("data", $data);
        $this->display();
    }

    //编辑升级所需金币银币
    function edit_upgrade(){
        $id=intval($_GET['id']);
        $info = M('club_upgrade')->where(array('id'=>$id))->find();
        $this->assign('info', $info);
        $this->display();
    }

    function edit_upgrade_do(){
        if(IS_POST)
        {
            $club=M("club_upgrade");
            $data = $club->create();
            $result=$club->save();
            if($result!==false)
            {
                $action="修改升级信息：{$data['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }
        }
    }

    //俱乐部头衔所需金币/银币
    function clubTitle(){
        $club_model = M('club_title');
        $count = $club_model->count();
        $page = $this->page($count, 20);
        $data = $club_model
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign("data", $data);
        $this->display();
    }

    //编辑头衔所需金币银币
    function edit_title(){
        $id=intval($_GET['id']);
        $info = M('club_title')->where(array('id'=>$id))->find();
        $this->assign('info', $info);
        $this->display();
    }

    function edit_title_do(){
        if(IS_POST)
        {
            $club=M("club_title");
            $data = $club->create();
            $result=$club->save();
            if($result!==false)
            {
                $action="修改升级信息：{$data['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }
        }
    }
}
