<?php

/**
 * Date: 15-12-23
 * Time: 下午5:04
 */
namespace Cliapi\Controller;

use Think\Controller;
use Common\Api\GpsApi;

class UserController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }

    //我 用户资料
    public function userInfo(){
        $id = I('uid','','intval');
        if(!$id){
            $uid = $this->uid;
        }else{
            $uid = $id;
        }

        $userInfo = User($uid,true,true);
        if(empty($userInfo)){
            $this->ajaxRet(array('info'=>'用户不存在!'));
        }
        $this->ajaxRet(array('status' => 1,'info' =>'', 'data' => $userInfo));
    }

    /** 
     * 修改用户资料
     */
    public function UpdateUser() {
        $data = I('post.');
        
        if(!empty($data['user_nicename'])){
            $updata['user_nicename'] = $data['user_nicename'];
        }
        
        if(!empty($data['user_email'])){
            if(!checkEmail($data['user_email'])){
                $this->ajaxRet(array('status' => 0,'info' => '邮箱格式不正确!'));
            }
            if(empty($data['sms_code'])){
                $this->ajaxRet(array('status' => 0,'info' => '邮箱验证码不能为空!'));
            }
            
            if(!D('Users')->checkUniqueEmail($data['user_email'])){
                $this->ajaxRet(array('status' => 0,'info' => '邮箱已存在!'));
            }
            
            if (!D('Verify')->checkVerify($data['user_email'], 'editemail', $data['sms_code'])){
                $this->ajaxRet(array('status' => 0,'info' => D('Verify')->getError()));
            }
            
            $updata['user_email'] = $data['user_email'];
        }
       
        if($data['sex'] != ''){
            $updata['sex'] = $data['sex'];
        }
        
        if(!empty($data['birthday'])){
            $birthday = strtotime($data['birthday']);
            if(!$birthday){
                $this->ajaxRet(array('info' => '请输入正确的生日'));
            }else{
                $check = strtotime('1900-01-01');
                if($birthday <= $check){
                    $this->ajaxRet(array('info' => '生日不能早于1900-01-01'));
                }
                $data['birthday'] = date('Y-m-d', $birthday);
            }
            $updata['birthday'] = $data['birthday'];
        }
        
        if(!empty($data['signature'])){
            $updata['signature'] = $data['signature'];
        }

        if(!empty($data['mobile'])){
            if(!checkPhone($data['mobile'])){
                $this->ajaxRet(array('status' => 0,'info' => '手机号码格式不正确!'));
            }
            
            if(empty($data['sms_code'])){
                $this->ajaxRet(array('status' => 0,'info' => '手机号码验证码不能为空!'));
            }
            
            //是否重复
            if(D('Users')->checkUniqueMobile($data['mobile'])){
                $this->ajaxRet(array('status' => 0,'info' => '手机号码已存在!'));
            }
            
            if (!D('Verify')->checkVerify($data['mobile'], 'editmobile', $data['sms_code'])){
                $this->ajaxRet(array('status' => 0,'info' => D('Verify')->getError()));
            }
            
            $updata['mobile'] = $data['mobile'];
        }

        if(!empty($data['province'])){
            $updata['province'] = $data['province'];
        }
        
        if(!empty($data['city'])){
            $updata['city'] = $data['city'];
        }
        
        if(!empty($data['avatar'])){
            if(is_numeric($data['avatar'])){
                $updata['avatar'] = M('Picture')->where(array('id'=>$data['avatar']))->getField('path');
            }else{
                $updata['avatar'] = $data['avatar'];
            }
        }
        
        if(!empty($data['wx'])){
            $updata['wx'] = $data['wx'];
        }
        
        if(!empty($data['qq'])){
            $updata['qq'] = $data['qq'];
        }
        
        if(!empty($data['weibo'])){
            $updata['weibo'] = $data['weibo'];
        }
        
        if(!empty($data['sf_card']) && !empty($data['sf_img'])){
            $updata['sf_card'] = $data['sf_card'];
            $updata['sf_img'] = $data['sf_img'];
        }
        
        if((!empty($data['sf_card']) && empty($data['sf_img'])) || (empty($data['sf_card']) && !empty($data['sf_img']))){
            $this->ajaxRet(array('info' => '身份证号码和身份证图片必须同时不为空'));
        }
        
        if(empty($updata)){
            $this->ajaxRet(array('info' => '未修改任何信息'));
        }
        
        $Result = M('Users')->where(array('id'=>$this->uid))->save($updata);
        if ($Result !== false) {
            $userInfo = User($this->uid, true, true);
            if(!empty($updata['wx'])){
                Coin($this->uid, 'bind_wx');
            }
            
            elseif(!empty($updata['qq'])){
                Coin($this->uid, 'bind_qq');
            }
            
            elseif(!empty($updata['user_email'])){
                Coin($this->uid, 'bind_email');
            }

            elseif(!empty($updata['weibo'])){
                Coin($this->uid, 'bind_weibo');
            }
            
            elseif(!empty($updata['sf_card']) && !empty($updata['sf_img'])){
                Coin($this->uid, 'invite_friend');
            }
            
            $this->ajaxRet(array('status' => 1, 'info' => '资料修改成功', 'data' => $userInfo));
        } else {
            $this->ajaxRet(array('info' => M('Users')->getError()));
        }
    }

    /** 
     * 添加指定会员为好友
     */
    public function Friend() {
        $uid = I('uid', 0, 'intval');
        
        $data['uid'] = $this->uid;
        $data['relation_uid'] = $uid;
        $data['type'] = 1; //1:好友 2:关注 
        if(D('UserRelation')->Insert($data)){
            $ret['status'] = 1;
            $ret['info'] = '添加好友成功!';
        }else{
            $ret['info'] = D('UserRelation')->getError();
        }
        $this->ajaxRet($ret);
    }

    /** 
     * 关注
    */
    public function Subscribe(){
        $uid = I('uid', 0, 'intval');
        
        //是否取消关注 0:否 1:是
        $is_cancel = I('is_cancel', 0, 'intval');
        
        $data['uid'] = $this->uid;
        $data['relation_uid'] = $uid;
        $data['type'] = 2; //1:好友 2:关注
        
        if($is_cancel){
            $Info = M('UserRelation')->where($data)->find();
            if(!empty($Info) &&  M('UserRelation')->where(array('id'=>$Info['id']))->delete()){
                $ret['status'] = 1;
                $ret['info'] = '取消关注成功!';
            }else{
                $ret['info'] = '关系不存在,或取消关注失败!';
            }
            $this->ajaxRet($ret);
        }
        
        if(D('UserRelation')->Insert($data)){
            $ret['status'] = 1;
            $ret['info'] = '关注成功!';
        }else{
            $ret['info'] = D('UserRelation')->getError();
        }
        $this->ajaxRet($ret);
    }
    
    /** 
     * 好友or关注数量
    */
    public function FSnumber(){
        //类型 1:好友 2:关注 
        $type = I('type', 1, 'intval');
        $type = $type ?: 1;
        if($type == 1){
            $Number = D('UserRelation')->friend($this->uid);
        }elseif($type == 2){
            $Number = D('UserRelation')->fans($this->uid);
        }else{
            $Number = 0;
            $this->ajaxRet(array( 'info' => '错误的类型值!'));
        }
        
        $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $Number));
    }

    //兑换金币
    //$type 类型 0:鲜花兑换金币 1:金币兑换现金 2:现金提现
    //$toGold 0:兑换 1:返回可兑换数量
    public function toGold(){
        $number = I('post.number',0,'intval');
        $toGold = I('post.togold',0,'intval');
        if($toGold == 1){
            $this->ajaxRet(array('status'=>1,'info'=>'成功','data'=>D('AccountLog')->getToGold($this->uid,$number,1)));
        }
        if(D('AccountLog')->addlog($this->uid, $number, 0) === true){
            $this->ajaxRet(array('status'=>1,'info'=>'兑换成功'));
        }else{
            $this->ajaxRet(array('info'=>D('AccountLog')->getError()));
        }
    }
    
    
    //现金提现
    public function toCash(){
        //提现数量
        $number = I('post.number', 0, 'intval');
        $alipay = I('post.alipay');
        if(D('AccountLog')->addlog($this->uid, $number, 2, $alipay) === true){
            $this->ajaxRet(array('status'=>1,'info'=>'提现成功'));
        }else{
            $this->ajaxRet(array('info'=>D('AccountLog')->getError()));
        }
    }

    
    //提现记录
    public function cashLog(){
        $map['uid'] = $this->uid;
        $map['change_type'] = array('in',array(2,3,4));
        
        $_list = $this->lists('AccountLog',$map,'change_time DESC');
        if(!empty($_list)){
            $map['change_type'] = 3;
            $data['allmoney'] = M('AccountLog')->where($map)->sum('change_number');
            $data['_list'] = $_list;
            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$data));
        }
        $this->ajaxRet(array('info'=>'没有数据'));
    }
    
    //签到
    public function Signin(){
        if(D('UserSignin')->addSignin($this->uid)){
            $this->ajaxRet(array('status'=>1,'info'=>'签到成功'));
        }else{
            $this->ajaxRet(array('status'=>0,'info'=>D('UserSignin')->getError()));
        }
    }
    
    //我的银行卡
    public function Bankcard(){
        $map['uid'] = $this->uid;
        $data['_list'] = $this->lists('UserBank',$map,'create_time DESC');
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //绑定银行卡
    public function bindBank(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        if(D('UserBank')->bindBank($data)){
            $this->ajaxRet(array('status' => 1, 'info' => '成功'));
        }else{
            $this->ajaxRet(array( 'info' => D('UserBank')->getError()));
        }
    }

    //解绑银行卡
    public function untyingBank(){
        $data = I('post.');
        $data['uid'] = $this->uid;


        if(D('UserBank')->untyingBank($data)){
            $this->ajaxRet(array('status' => 1, 'info' => '成功'));
        }else{
            $this->ajaxRet(array( 'info' => D('UserBank')->getError()));
        }
    }
    
    //我的优惠券
    public function myCoupon(){
        $data = I('post.');
               
        $type   = empty($data['type'])?NULL:trim($data['type']);
        $status = empty($data['status'])?NULL:intval($data['status']);
        
        $data = D('Coupon')->myCoupon($this->uid, $type, $status);
        if($data){
            $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data'=>$data));
        }else{
            $this->ajaxRet(array( 'info' => D('Coupon')->getError()));
        }
    }
    
    
    //更新用户的经纬度
    //lng 经度
    //lat 纬度
    public function lnglat(){
        $data = I('post.');
        
        $save['lng'] = $data['lng'];
        if(empty($save['lng'])){
            $this->ajaxRet(array('info'=>'请传入正确的经度'));
        }
        
        $save['lat'] = $data['lat'];
        if(empty($save['lat'])){
            $this->ajaxRet(array('info'=>'请传入正确的纬度'));
        }
        
        if(M('Users')->where(array('id'=>$this->uid))->save($save) !== false){
            $this->ajaxRet(array('status'=>1, 'info'=>'成功'));
        }
    }
    //我的竞猜
    public function quzi(){
        $parem = I('post.');
      
        $uid = $this->uid;
   
        $page = !empty($parem['p'])? $parem['p'] : 1;
        $size = !empty($parem['r'])? $parem['r'] : C('p');
        $totl = D('jingcai_log')->where('uid='.$uid)->count();

        //总数转换成多少页
        $pageTo=ceil($totl/$size);

        $from = ($page-1)*$size; 
        $data['_list'] = D('jingcai_log log')
                ->field('cai.home,cai.away,log.profit,cai.status,log.addtime')
                ->join('cmf_jingcai cai on log.jingcai_id = cai.id')
                ->where('log.uid='.$uid)
                ->limit($from,$size)
                ->select();
        $data['_totalPages'] = $pageTo;       
        $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data'=>$data));        

    }
    //名人堂 - 个人
    public function famie(){
        
        $parem = I('post.');
        $uid = $this->uid;
        if(empty($parem['type'])){

          $this->ajaxRet(array('status' => 0, 'info' => '类型为空', 'data'=>''));
        }
        $page = !empty($parem['p'])? $parem['p'] : 1;
        $size = !empty($parem['r'])? $parem['r'] : C('p');
        $totl = D('users')->count();
        
        //总数转换成多少页
        $pageTo=ceil($totl/$size);

        $from = ($page-1)*$size; 

        //个人排名
        if($parem['type'] == 'person'){

            $data['_list'] = D('users')
                    ->field('user_nicename,avatar,level')
                    ->order('level desc')
                    ->limit($from,$size)
                    //->where(array('id'=>$this->uid))
                    ->select();

            $data['_totalPages'] = $pageTo;         
            $this->ajaxRet(array('status' => 1, 'info' => '', 'data'=>$data));        


        }
        //球队排名 按人数排序
        if($parem['type'] == 'team'){
           $ball = D('ball_team team')
                     ->field('team.name names,mem.name')
                     ->join('cmf_ball_mem mem ON team.id = mem.team_id')
                     ->select();
            $this->ajaxRet(array('status' => 1, 'info' => '', 'data'=>$ball));          
        }
    }
    //我的消息
    public function woinformation(){
        $parem = I('post.');
        if(empty($parem['type'])){
          $this->ajaxRet(array('status' => 0, 'info' => '类型为空', 'data'=>'')); 
        }
        //为官方消息
        if($parem['type'] == 'official'){
           $map =  ['user_id'=>$this->uid,'classify'=>'official'];
           $data['_list'] = $this->lists('sysmessage',$map);
           
        }
        //为俱乐部消息
        if($parem['type'] == 'club'){
           $map =  ['user_id'=>$this->uid,'classify'=>'club'];
           $data['_list'] = $this->lists('sysmessage',$map);
        }
        //为商城消息
        if($parem['store']){
           $map =  ['user_id'=>$this->uid,'classify'=>'store'];
           $data['_list'] = $this->lists('sysmessage',$map);
        }
        $data['_totalPages'] = $this->_totalPages;
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data'=>$data)); 
    }

    /**
     * 获取直播等级
     */
    public function getLevel()
    {
        $uid = $this->uid;
        $data = User($uid,true,true);

        $now_level_data = M('experlevel_anchor')
            ->where(['levelid'=>['EQ',$data['level']]])->find();

        $now_empiric = 120;

        $next_level_data = M('experlevel_anchor')
            ->where(['levelid'=>['EQ',$data['level']+1]])
            ->find();

        $next_empiric = $next_level_data['level_up'] - $now_empiric;

        $empiric_percentage = round(($now_empiric/$next_empiric)*100);

        $return_data = [
            'level' => $data['level'],
            'thumb' => AddHttp($now_level_data['thumb']),
            'now_empiric' => $now_empiric,
            'empiric_percentage'=>$empiric_percentage ,
            'next_empiric' =>$next_level_data['level_up'] - $now_empiric
        ];

        $this->ajaxRet(1, '成功', $return_data);
    }

    public function updatePassword()
    {
        $uid = $this->uid;

        $data= I('post.');

        $old_password = I('old_password');
        $new_password = sp_password($data['new_password']);

        if(!$old_password){
            $this->ajaxRet(array('status' => 0, 'info' => '请输入旧密码'));
        }

        if(!$new_password){
            $this->ajaxRet(array('status' => 0, 'info' => '请输入新密码'));
        }

        //验证旧密码
        $user = User($uid,true,true);
        $loginstring = $user['user_login'];

        $uid = D('Users')->Authlogin($loginstring, $old_password, 1);


        if ($uid<=0 ){

            $this->ajaxRet(array('status' => 0, 'info' => '密码错误'));
        }


        $ret = M('Users')->where(array('id' => $user['id']))->setField('user_pass', $new_password);

        if ($ret !== false) {
            $this->ajaxRet(array('status' => 1, 'info' => '操作成功，请使用您的新密码登陆吧'));
        } else {
            $this->ajaxRet(array('status' => 0, 'info' => $user['id']));
        }


    }

    /**
     * 获取银行列表
     */
    public function getBankList()
    {
        $bank = M('bank')->select();
        $this->ajaxRet(1, '成功', ['_list'=>$bank]);
    }

    public function silverCoinCash()
    {
        $uid = $this->uid;
        $silver_coin = I('silver_coin');
        $idcard_num = I('idcard_num');
        $mobile = I('mobile');
        $account=  I('account');
        $name=  I('name');

        if (empty($name)){
            $this->ajaxRet(array('status' => 0, 'info' => '请输入姓名', 'data'=>''));
        }
        if (empty($silver_coin)){
            $this->ajaxRet(array('status' => 0, 'info' => '请输入提现银币', 'data'=>''));
        }
        if(empty($account)||!$this->is_bank($account)){
            $this->ajaxRet(array('status' => 0, 'info' => '银行卡格式错误', 'data'=>''));
        }
        if(empty($idcard_num)||!$this->is_idcard($idcard_num)){
            $this->ajaxRet(array('status' => 0, 'info' => '身份证号格式错误', 'data'=>''));
        }
        if(empty($mobile) || !checkPhone($mobile)){
            $this->ajaxRet(array('status' => 0, 'info' => '手机号码格式错误', 'data'=>''));
        }


        //判断银币够不够
        $user = User($uid,true,true);

        if($user['silver_coin']<$silver_coin){
            $this->ajaxRet(array('status' => 0, 'info' => '银币不足', 'data'=>''));
        }

        //查询银行名称
        $bank_name = M('user_bank')->where(['account',$account])->find()['title'];

        $model = M();
        $model->startTrans();  // 开启事务

        try{

            //插入提现表
            M('users_cashrecord')->data([
                'silver_coin'=>$silver_coin,
                'uid'=>$uid,
                'status'=>0,
                'addtime'=>time(),
                'account_bank'=>$bank_name,
                'account' => $account,
                'name'=>$name
            ])->add();

            //扣银币

            M('users')->where(['id'=>$uid])->setDec('silver_coin',$silver_coin);

            $model->commit();
            $this->ajaxRet(array('status' => 1, 'info' => '发起提现审核成功'));
        }catch (\Exception $exception){
            $model->rollback();
            throw new \Exception($exception);

            $this->ajaxRet(array('status' => 0, 'info' => '提现失败', 'data'=>''));

        }


    }
    /**
     * 验证身份证号码格式
     * @param string $id_card 身份证号码
     * @return boolean
     */
    public function is_idcard($id_card) {
        $chars = "/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}(\d|x|X)$/";
        if (preg_match($chars, $id_card)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 银行卡正则
     * @param $bank
     * @return bool
     */
    public function is_bank($bank) {
        $chars = "/^(\d{16}|\d{19}|\d{17})$/";
        if (preg_match($chars, $bank)) {
            return true;
        } else {
            return false;
        }
    }
}
