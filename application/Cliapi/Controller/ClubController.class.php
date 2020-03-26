<?php

/**

 * 球会俱乐部

 */

namespace Cliapi\Controller;



use Think\Controller;



class ClubController extends MemberController {



    function _initialize(){

        parent::_initialize();

    }



    /**

     * 创建俱乐部

     */

    function addClub(){

        $data  =  I('post.');

        $data['uid'] = $data['uid'] ?: $this->uid;

        $res = D('Clubs')->Insert($data);



        if($res){

            $this->ajaxRet(array('status' => 1, 'info' => '创建俱乐部成功','data'=>$res));

        }else{

            $this->ajaxRet(array('info' => D('Clubs')->getError()));

        }

    }



    //我的好友列表

    function myFirend(){

        $uid = $this->uid;

        $fid = D('UserRelation')->friend_list($uid);



        foreach ($fid as $k=>$v){

            $info[] = M('users')->field('id,user_nicename,avatar')->where(array('id'=>$v))->find();

        }



        if(empty($info)){

            $list = M("users")->field('id,user_nicename,avatar')->limit(4)->order('rand()')->select();

            $this->ajaxRet(array('status'=>0,'info'=>'您还没有好友哦,系统为您随机匹配','data'=>$list));

        }else{

            $this->ajaxRet(array('status'=>0,'info'=>'获取好友列表成功','data'=>$info));

        }

    }



    //邀请好友加入俱乐部

    function invite(){

        $cid = intval(I('cid'));

        $mid = intval(I('mid'));

        if(empty($cid) || empty($mid)){

            $this->ajaxRet(array('status'=>0,'info'=>'参数错误'));

        }



        if(strrchr($mid,",") == "," ){      //结尾有，

            $this->ajaxRet(array('status'=>0,'info'=>'格式错误'));

        }



        if(strpos($mid,',') !== false){     //如果包含，

            $club_mem = explode(',', $mid);

            for ($i = 0; $i < count($club_mem); $i++) {

                $data[] = ['cid' => $cid, 'mid' => $club_mem[$i],'status'=>1,'add_time'=>time()];

            }

            $res = M('club_invite')->addAll($data);

        }else{

            $data = ['cid'=>$cid,'mid'=>$mid,'status'=>1,'add_time'=>time()];

            $res = M('club_invite')->add($data);

        }

        if($res){

            $this->ajaxRet(array('status'=>1,'info'=>'邀请成功','data'=>''));

        }

    }



    //加入俱乐部  筛选数据

    function joinTypeClub(){

        $type = I('type');

        if($type == 'near'){

            $jingdu = I('longitude');

            $weidu = I('latitude');

            if(empty($jingdu) || empty($weidu)){

                $this->ajaxRet(array('status'=>0,'info'=>'请获取当前位置'));

            }

            $info =  D('Clubs')->myNearby($jingdu,$weidu);

        }else{

            $info = D('Clubs')->joinTypeClub($type);

        }



        if($info){

            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$info));

        }else{

            $this->ajaxRet(array('info'=>D('Clubs')->getError()));

        }



    }



    /**

     * 加入俱乐部

     */

    function joinClub(){

        $club_id = I('c_id');

        $mem_id = I('m_id')?:$this->uid ;     //成员id

        $data = [

            'club_id'=>$club_id,

            'mem_id'=>$mem_id

        ];

        $res = D('Clubs')->InJect($data);

        if($res !== false){

            $this->ajaxRet(array('status' => 1, 'info' => '已申请','data'=>$res));

        }else{

            $this->ajaxRet(array('info' => D('Clubs')->getError()));

        }

    }



    //俱乐部成员列表

    function memList(){

        $cid = I('cid',1,'intval');

        $is_club = M('clubs')->where(array('id'=>$cid))->find();

        if(empty($is_club)){

            $this->ajaxRet(array('status'=>0,'info'=>'参数错误'));

        }



        $prefix = C('DB_PREFIX');

        $info = M('club_mem m')

            ->join("{$prefix}users u on m.mid = u.id")

            ->field('m.cid,m.mid,u.user_nicename,u.user_login,m.add_time,u.avatar')

            ->where(array('m.cid'=>$cid,'m.status'=>1))

            ->select();



        //根据头衔等级排序

        foreach ($info as $k=>&$v){

            $data = D('Clubs')->Title($v['mid'],$cid);

            $neme = M('club_title')->field('title_name')->where(array('level'=>$data['level']))->find();

            $v['level'] = $data['level'];

            $v['title_name'] = $neme['title_name'];

            $arr[] = $data['level'];

        }

        array_multisort($arr,SORT_ASC,$info);



        $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$info));

    }



    /**

     * 搜索

     */

    function search(){

        $name = I('name');

        var_dump($name);die;

        $info = D('Clubs')->Search($name);

        if($info !== false){

            $this->ajaxRet(array('status'=>1,'info'=>'搜索结果获取成功','data'=>$info));

        }else{

            $this->ajaxRet(array('info' => D('Clubs')->getError()));

        }

    }



    //去关注
    function goAttention(){

        $data = I('post.');

        $data['user_id'] = $this->uid;

        $res = D('Clubs')->goAttention($data);

        if($res !== false){

            $this->ajaxRet(array('status'=>1,'info'=>'关注成功','data'=>$res));

        }else{

            $this->ajaxRet(array('info'=>D('Clubs')->getError()));

        }

    }



    //我的关注(首页)

    function attention(){

        $uid = $this->uid;

        $res = D('Clubs')->isAttention($uid);

        if($res){

            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$res));

        }else{

            $this->ajaxRet(array('info'=>D('Clubs')->getError()));

        }

    }



    //当前热门（首页）

    function nowHot(){

        $res = D('Clubs')->nowHot();

        if($res){

            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$res));

        }else{

            $this->ajaxRet(array('info'=>D('Clubs')->getError()));

        }

    }



    //获取首页分类下的俱乐部

    function indexClubs(){

        $type = I('type');

        $res = D('Clubs')->clubFilter($type);

        if($res){

            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$res));

        }else{

            $this->ajaxRet(array('info'=>D('Clubs')->getError()));

        }



    }



    //获取俱乐部热聊下的数据

    function hotInfo(){

        $type = I('post.type');

        $res = D('Clubs')->hotInfo($type);

        if($res){

            $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$res));

        }else{

            $this->ajaxRet(array('info'=>D('Clubs')->getError()));

        }

    }



    //管理中心

    function manageCenter(){

        $type = I('type');

        $uid = $this->uid;

        $res = D('Clubs')->manageCenter($type,$uid);

        if($res){
            $data['_list'] = $res;
            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$data));

        }else{
            $this->ajaxRet(array('status'=>0,'info'=>'您还没有俱乐部'));

        }

    }



    /**

     * 俱乐部头衔

     * 数字1-8代表了头衔等级

     */

    function clubTitle(){

        $id = I('id',0,'intval') ?: $this->uid;

        $club_id = I('club_id');

        $data = D('Clubs')->Title($id,$club_id);

        if($data){

            $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$data));

        }else{

            $this->ajaxRet(array('info'=>D('Clubs')->getError()));

        }

    }



    /**

     * 俱乐部升级

     */

    function clubRank(){

        $club_id = I('c_id');

        $res = D('Clubs')->Rank($club_id);

        if($res === true){

            $level = M('clubs')->field('id,name,level')->where(array('id'=>$club_id))->find();

            $this->ajaxRet(array('status'=>1,'info'=>'升级成功','data'=>$level));

        }else{

            $this->ajaxRet(array('info' => D('Clubs')->getError()));

        }

    }



    /**

     * 捐赠金币/银币

     */

    function donateCoin(){

        $data = I('post.');

        $data['don_id'] = I('don_id',0,'intval') ?: $this->uid;

        $res = D('Clubs')->donCoin($data);

        if($res === true){

            $this->ajaxRet(array('status'=>1,'info'=>'捐赠成功','data'=>$data));

        }else{

            $this->ajaxRet(array('info'=>D('Clubs')->getError()));

        }

    }





    //俱乐部热聊
    function ClubsHot(){
        $map['is_hotchat'] = 1;
        $map['is_del'] = 0;
        $list = M('Clubs')->where($map)->order('sort desc')->select();
        $list = array_map(function($val){
            $val['usernum'] = M('ClubMem')->where(['cid'=>$val['id'], 'status'=>1])->count() + 1;
            $val['headimg'] = Imgpath($val['headimg']);
            return $val;
        }, $list);
        $data['_list'] = $list;
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$data));
    }



    //官方俱乐部
    function ClubsGf(){

        $map['is_del'] = 0;

        $map['type'] = 10012;

        $list = M('Clubs')->where($map)->order('sort desc')->select();

        $list = array_map(function($val){

            $val['headimg'] = Imgpath($val['headimg']);

            return $val;

        }, $list);

        $data['_list'] = $list;

        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$data));



    }



}