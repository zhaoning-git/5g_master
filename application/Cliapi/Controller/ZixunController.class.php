<?php
/**
 * 资讯管理
 */
namespace Cliapi\Controller;

use Think\Controller;
use Cliapi\Controller\GuoduController;

class ZixunController extends GuoduController {
    //首页标签分类数据
    function typeInfo(){
        $type = I('type');
        if($type == 1 || $type == 2 || $type == 4){
            $info = M('club_type')->where(array('type'=>$type))->select();
            $this->ajaxReturn(array('status'=>1,'info'=>'获取成功','data'=>$info));
        }else{
            $this->ajaxReturn(array('info'=>D('Show')->getError()));
        }

    }

    //首页信息
    function indexInfo(){
        $index_mol = I('post.type') ?: 1;   //如果没接到 默认足球
        if($index_mol == 1){
            $sel_type = I('sel_type') ?: 10000; //默认头条
        }else{
            $sel_type = I('sel_type') ?: 10010;//默认NBA
        }
        $info = D('Show')->getZixunInfo($index_mol,$sel_type);



        $data['_list'] = $info;
        //$data['_totalPages'] = $this->_totalPages; //总页数
        if($info != false){
            $this->ajaxReturn(array('status'=>1,'info'=>'获取成功','data'=>$data));
        }else{
            $this->ajaxReturn(array('info'=>D('Show')->getError()));
        }
    }

    //搜索
    function goSearch(){
        $prefix = C('DB_PREFIX');
        $name = I('post.name');
        if(empty($name)){
            $this->error = '搜索条件不能为空';
            return false;
        }
        $data = array();
        //查询球队
        $teamData = D('Match')->teamData();
        foreach ($teamData as $k=>$v){
            foreach ($teamData['teamList'] as $key=>$val){
                $res = strpos($val['nameChs'],$name);
                if($res !==false){
                    $value[] = [
                        'teamId'=>$val['teamId'],
                        'nameEn'=>$val['nameEn'],
                        'nameChs'=>$val['nameChs'],
                        'areaCn'=>$val['areaCn']
                    ];
                    $data['team'] = $value;
                }
            }
        }

        //查询球员
        $teamMem = M('Ball_mem')->field('id,name,age,headimg')->where(array('name'=>['like',"%$name%"]))->select();
        foreach ($teamMem as $k=>&$v){
            $v['img_path'] = getImgVideo($v['headimg']);
        }
        $data['mem'] = $teamMem;

        //查询专家（懂球号）
        $speci = M('Specialist')
            ->field('id,speciname,introduce,portrait')
            ->where(array('speciname'=>['like',"%$name%"]))
            ->select();
        foreach ($speci as $k=>&$v){
            $v['portrait'] = getImgVideo($v['portrait']);
        }
        $data['speci'] = $speci;

        //查询用户（球迷）
        $user = M('Users')->field('id,user_nicename,avatar')->where(array('user_nicename'=>['like',"%$name%"]))->select();
        $data['user'] = $user;

        //查询商品
        $goods = M('Duihuan')->field('id,shopname,jinbi,yingbi,img')->where(array('shopname'=>['like',"%$name%"]))->select();
        $data['goods'] = $goods;

        //查询帖子
        $map['title|content|theme'] = array('like', '%' . $name . '%');
        $topic = M('Show')
            ->field('id,title,theme,content,data,addtime')
            ->where($map)->select();
        foreach ($topic as $k=>&$v){
            $v['data'] = getImgVideo($v['data']);
        }
        $data['topic'] = $topic;

        $this->ajaxReturn(array('status'=>1,'info'=>'搜索成功','data'=>$data));
    }

    //资讯详情
    function zixunDetail(){
        $uid = I('_uid');
        $id = I('show_id');
        if(empty($id)){
            $this->ajaxReturn(array('status'=>0,'info'=>'参数错误'));
        }

        $info = M('show')->field('id,uid,uname,title,content,data,addtime,sqs_status,identifshi, praise,favor')->where(array('id'=>$id,'type'=>2))->find();

        if(empty($info['uname'])){
            $uname = M('users')->field('user_nicename')->where(array('id'=>$info['uid']))->find();
            $info['uname'] = $uname['user_nicename'];
        }
        if(is_numeric(substr($info['data'],0,1))) { //如果以数字开头
            $info['data']= getImgVideo($info['data']);
        }


        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->auth('yunbao.com');
        $read_key = "count_reading_view:show_id:".$id;
        $redis->incr($read_key);
        $read_count =  $redis->get($read_key);
        $info['reading'] = $read_count;
        $info['addtime'] = friendlyDate($info['addtime']);
        $info['is_zan'] = M('ShowLog')->where(['show_id'=>$id,'uid'=>$uid,'status'=>1,'type'=>'praise'])->count();
        $info['is_favor'] = M('ShowLog')->where(['show_id'=>$id,'uid'=>$uid,'status'=>1,'type'=>'favor'])->count();;
        M('show')->where(array('id'=>$id))->save(array('reading'=>$read_count));

        //评论

        $this->ajaxReturn(array('status'=>1,'info'=>'获取成功','data'=>$info));
    }


    
     //获取评论列表
     public function zan(){
        $input = I('post.');

        if(!$input['show_id']){
            $this->ajaxRet(0, 'show_id is empty');
        }
         
        
        // D('Show')->setShow($input['show_id'],27543,'praise');



        $map['id'] = $input['show_id'];

      
 
        if(session('zanzixun_' . $input['show_id'])){
            $list = M('Show')->where($map)->setDec('praise');
            session('zanzixun_' . $input['show_id'], null);
        }else{
            $list = M('Show')->where($map)->setInc('praise');
            session('zanzixun_' . $input['show_id'], true);
        }
     
        $this->ajaxReturn(array('status'=>1,'info'=>'点赞成功'));
    }
    

    //作者首页信息
    function ZixunWriter(){
        $uid = intval(I('uid'));
        if(empty($uid)){
            $this->ajaxReturn(array('status'=>0,'info'=>'作者ID不能为空'));
        }
        $userData = M('users')->field('user_nicename,avatar,province,city,create_time')->where(array('id'=>$uid,'status'=>1))->find();
        $datetime1 = date_create($userData['create_time']);
        $datetime2 = date_create(date('Y-m-d H:i:s',time()));
        $interval = date_diff($datetime1,$datetime2);
        $days = $interval->format('%a');
        $userData['days'] = $days;                  //从注册账号至今有多少天

        $count_prise = M('show')->where(array('uid'=>$uid))->sum('praise');
        $prefix = C('DB_PREFIX');
        $name = M('user_team t')
            ->join("{$prefix}ball_team b on b.id = t.team_id")
            ->field('b.name')
            ->where(array('t.uid'=>$uid,'t.is_zhu'=>1))
            ->find();


        if(empty($userData['avatar'])){
            $userData['avatar'] = getDefaultHead();
        }   
        $userData['qiuMi'] = $name['name'];             //是哪个球队的球迷
        $userData['priase'] = $count_prise;             //点赞数量
        $this->ajaxReturn(array('status'=>1,'info'=>'获取成功','data'=>$userData));
    }

    //作者发表
    function writerFaBiao(){
        $uid = intval(I('uid'));
        if(empty($uid)){
            $this->ajaxRet(array('status'=>0,'info'=>'请选择用户'));
        }
        $info = M('show')->field('id,title,content,praise,data')->where(array('uid'=>$uid,'type'=>2))->select();
        foreach ($info as $k=>&$v){
            $v['data'] = getImgVideo($v['data']);
            $com_sum =M('comment')->where(array('show_id'=>$v['id']))->select();
            $uname = M('users')->field('user_nicename')->where(array('id'=>$v['uid']))->select();
            foreach ($uname as $key=>&$val){
                $v['uname'] = $val['user_nicename'];
            }
            $v['comm_sum']=count($com_sum);

        }
        $this->ajaxReturn(array('status'=>1,'info'=>'获取成功','data'=>$info));
    }

    //作者关注/粉丝
    function writerFenSi(){
        $prefix = C('DB_PREFIX');
        $uid = I('uid');
        $catg = I('type');              //1是关注   2是粉丝
        if(empty($uid) || empty($catg)){
            $this->ajaxRet(array('status'=>0,'info'=>'参数错误'));
        }

        if($catg == 1){         //查看我关注了谁
            $info = M('user_relation')
                ->join('cmf_users on cmf_user_relation.relation_uid = cmf_users.id')
                ->field('relation_uid,avatar,sex,user_nicename,province,city')
                ->where(array('uid'=>$uid,'r.type'=>2))
                ->select();    //关注
        }

        elseif ($catg == 2){//谁关注了我  即粉丝
            $info = M('user_relation r')
                ->join("{$prefix}users u on r.uid = u.id")
                ->field('r.uid,u.user_nicename,u.avatar,u.sex,u.province,u.city')
                ->where(array('r.relation_uid'=>$uid,'r.type'=>2))
                ->select();

        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'参数类型错误'));
        }
        $this->ajaxReturn(array('status'=>1,'info'=>'获取关注/粉丝成功','data'=>$info));

    }

}