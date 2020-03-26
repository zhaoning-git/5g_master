<?php

/**

 * 主播

 */

namespace Cliapi\Controller;

use Think\Controller;



class AnChorController extends MemberController {

    //主播入驻
    function ruZhu(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        $res = D('Anchor')->ruZhu($data);

        unset($res['_uid']);
        unset($res['_sign']);

        if($res !== false){
            $this->ajaxRet(array('status'=>1,'info'=>'入驻成功','data'=>$res));
        }else{
            $this->ajaxRet(array('info'=>D('Anchor')->getError()));
        }
    }



    //上传全身照
    function quanShen(){
        $data['uid'] = $this->uid;

        $data['pic'] = I('pid');



        $res = D('Anchor')->quanShen($data);

        if($res === true){

            $this->ajaxRet(array('status'=>1,'info'=>'上传成功，待审核','data'=>$res));

        }else{

            $this->ajaxRet(array('info'=>D('Anchor')->getError()));

        }

    }



    //获取礼物列表
    function giftList(){
        $info = M('gift')->select();
        $this->ajaxRet(array('status'=>1,'info'=>'获取礼物列表成功','data'=>$info));
    }



    //获取主播入驻列表
    function anchorList(){

        $info = M('anchor_ruzhu')->where('status=1')->select();

        foreach ($info as $k=>&$v){

            $v['zidcardpic'] = getImgVideo($v['zidcardpic']);

            $v['fidcardpic'] = getImgVideo($v['fidcardpic']);

            $v['scidcard'] = getImgVideo($v['scidcard']);

        }

        $this->ajaxRet(array('status'=>1,'info'=>'获取主播列表成功','data'=>$info));

    }



    //送礼物
    function giftSend(){

        $data = I('post.');

        $data['uid'] = $this->uid;



        $res = D('Anchor')->giftSend($data);

        if($res !== false){

            $this->ajaxRet(array('status'=>1,'info'=>'赠送成功','data'=>$res));

        }else{

            $this->ajaxRet(array('info'=>D('Anchor')->getError()));

        }

    }



    //魅力贡献榜

    function meiliBang(){

       $type = I('type');

       $myid = $this->uid;

       $res = D('Anchor')->meiliBang($type,$myid);

       if($res != false){

           $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$res));

       }else{

           $this->ajaxRet(array('info'=>D('Anchor')->getError()));

       }

    }



    //用户资料
    function userData(){
        $prefix = C('DB_PREFIX');
        $id = I('uid');

        //如果没有接到uid  就去查当前用户id
        if(empty($id)){
            $id = $this->uid;
            $info = M('users u')
                ->join("{$prefix}anchor_ruzhu r on u.id = r.uid")
                ->field('u.id,u.user_nicename,u.avatar,u.province,r.level,r.charm,r.income,u.silver_coin,u.gold_coin')
                ->where(array('u.id'=>$this->uid))
                ->find();

            //判断该用户是不是主播  主播当前资质是否有效  若是则显示主播等级
            $is_ruzhu = M('anchor_ruzhu')->field('level')->where(array('uid'=>$id,'status'=>1))->find();
            if(empty($is_ruzhu)){
                $info['level'] = 1;
            }

            if(empty($info['avatar'])){
                $info['avatar'] = getDefaultHead();
            }
            $info['avatar'] = AddHttp($info['avatar']);
            $info['fans_count'] = D('UserRelation')->fans($id);        //粉丝数量
            $info['attention_count'] = count(M('user_relation')->where(array('uid'=>$id))->select()); //我的关注数量
            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$info));
        }

        //判断接收的uid是否为主播
        $is_ruzhu = M('anchor_ruzhu')->field('level')->where(array('uid'=>$id,'status=1'))->find();
        if(empty($is_ruzhu)){
            $level = 1;
        }else{
            $level = $is_ruzhu['level'];
        }

        //查询非当前用户的资料
        $info = M('users')->field('id,user_nicename,avatar,province')->where(array('id'=>$id))->find();
        $coin = M('gift_sendlog')->where(array('uid'=>$id))->sum('gift_coin');
        $info['coin'] = $coin;
        $info['level'] = $level;
        $info['fans_count'] = D('UserRelation')->fans($id);        //粉丝数量
        $info['attention_count'] = count(M('user_relation')->where(array('uid'=>$id))->select()); //我的关注数量
        $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$info));
    }

    //关注或粉丝列表
    function myAttention(){

        $prefix = C('DB_PREFIX');

        $uid = intval(I('uid')) ?: $this->uid;

        $type = I('type');

        if($type == 1 ){     //关注

            $info = M('user_relation r')

                ->join("{$prefix}users u on r.relation_uid = u.id")

                ->field('r.relation_uid,u.user_nicename,u.sex')

                ->where(array('r.uid'=>$uid))

                ->select();

        }

        elseif ($type == 2){    //粉丝

            $info = M('user_relation r')

                ->join("{$prefix}users u on r.uid = u.id")

                ->field('r.uid,u.user_nicename,u.sex')

                ->where(array('r.relation_uid'=>$uid))

                ->select();

        }



        //遍历获取等级   若已入驻则获取入驻等级  否则则默认为1

        foreach ($info as $k=>&$v){

            $data = M('anchor_ruzhu')->where(array('uid'=>$v['uid']))->find();

            if(empty($data)){

                $v['level'] = 1;

            }else{

                $v['level'] = $data['level'];

            }

        }



        $this->ajaxReturn(array('status'=>1,'info'=>'获取成功','data'=>$info));

    }

    //我的收益 -- 页面

    function income(){
        $uid = $this->uid;
        $duihuan['duihuan'] = M('meili_coin')->select();           //魅力兑换银币
        $duihuan['now_meili'] = M('anchor_ruzhu')->field('charm')->where(array('uid'=>$uid))->find()?:['charm'=>0];//当前魅力值
        $this->ajaxRet(array('status'=>1,'info'=>'获取成功', 'data'=>$duihuan));
    }

    //我的收益 -- 去兑换
    function incomeDo(){
        $did = intval(I('did'));     //兑换参数的id
        $uid = $this->uid;
        if(empty($did)){
            $this->ajaxRet(array('status'=>0,'info'=>'请选择要兑换的参数'));
        }

        $mData = M('meili_coin')->where(array('id'=>$did))->find();      //根据传的id去查询所需的魅力值及可兑换的银币
        $anData = M('anchor_ruzhu')->where(array('uid'=>$uid))->find();  //查询主播入驻表
        if($anData['charm'] <= $mData['meili']){
            $this->ajaxRet(array('status'=>0,'info'=>'魅力值不足'));
        }

        //魅力值足够   兑换
        $income_coin = $anData['income'] + $mData['silver_coin'];
        $anchor_charm = $anData['charm'] - $mData['meili'];

        //修改主播表   我的魅力值、收益
        M('anchor_ruzhu')->where(array('uid'=>$uid))->save(array('income'=>$income_coin,'charm'=>$anchor_charm));

        //修改用户表中的银币数量
        $uData = M('users')->field('silver_coin')->where(array('id'=>$uid))->find();
        $silver_coin = $uData['silver_coin'] + $mData['silver_coin'];
        M('users')->where(array('id'=>$uid))->save(array('silver_coin'=>$silver_coin));

        //记录兑换记录
        $log = [
            'uid'=>$uid,
            'did'=>$did,
            'add_time'=>time()
        ];

        $res = M('meilicoin_log')->add($log);

        if($res){
            $this->ajaxRet(array('status'=>1,'info'=>'兑换成功','data'=>''));
        }
    }

    //搜索    暂时按照主播名搜索
    function search(){
        $data = I('search');
        if(empty($data)){
            $this->ajaxRet(array('status'=>0,'info'=>'搜索条件不能为空'));
        }

        $info = M('anchor_ruzhu')->field('id,name,level,charm')->where(array('name'=>['like',"%$data%"],'status=1'))->select();

        //将搜索历史存入redis
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->auth('yunbao.com');
        $redis->lpush($this->uid,$data);

        if(empty($info)){
            $this->ajaxRet(array('status'=>0,'info'=>'无该主播'));
        }else{
            $this->ajaxRet(array('status'=>1,'info'=>'搜索成功','data'=>$info));
        }
    }

    //搜索历史
    public function searchHistory(){
        $parem = $this->uid;
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->auth('yunbao.com');
        $lens = $redis->llen($parem);

        for ($i=0; $i <$lens ; $i++) {
            $uid[] = $redis->lindex($parem,$i);
        }

        $redis->expire($parem,'24*3600*30');
        $uids = array_unique($uid);
        $datauid = array_slice($uids,0,10);
        $this->ajaxRet(array('status'=>1,'info'=>'','data'=>$datauid));
    }

}