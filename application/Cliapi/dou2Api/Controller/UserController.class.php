<?php

/**
 * Date: 15-12-23
 * Time: 下午5:04
 */
namespace Api\Controller;

use Think\Controller;
use Common\Api\GpsApi;

class UserController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }

    //我 用户资料
    public function userInfo(){
        $id = I('uid','','trim');
        // 如果是环信id
        if (!is_numeric($id) && !empty($id)){
            $hx_user = M('ucenter_member')->where(array('hx_id'=>$id))->find();
            if (!$hx_user){
                $this->ajaxRet(array('info'=>'用户不存在!'));
            }
            $id = $hx_user['id'];
        }
        if(!$id){
            $uid = $this->uid;
        }else{
            $uid = $id;
        }

        $userInfo = User($uid,true,true);
        if(empty($userInfo)){
            $this->ajaxRet(array('info'=>'用户不存在!'));
        }

        //是否关注
        if($id){
            $map['status'] = 1;
            $map['relation_uid'] = $id;
            $map['uid'] = $this->uid;
            if(M('UserRelation')->where($map)->count()){
                $userInfo['is_guanzhu'] = true;
            }else{
                $userInfo['is_guanzhu'] = false;
            }
        }

        //可兑换金币数量
        $toGold = D('AccountLog')->getToGold($uid, $userInfo['flower'],1);
        $userInfo['toGold'] = $toGold;

        $where['type'] = 2;
        $where['status'] = 1;

        //我的关注
        $where['uid'] = $this->uid;
        $where['is_supplier'] = 0;
        $relNum['my'] = M('UserRelation')->where($where)->count();

        //关注的商家
        $where['is_supplier'] = 1;
        $relNum['supplier'] = M('UserRelation')->where($where)->count();

        //我的粉丝
        unset($where['uid'], $where['is_supplier']);
        $where['relation_uid'] = $this->uid;
        $relNum['fans'] = M('UserRelation')->where($where)->count();

        //好友数量(互相关注的才是好友)
        unset($where['relation_uid']);
        $where['uid'] = $this->uid;
        $myIds = M('UserRelation')->where($where)->getField('relation_uid',true);
        if(!empty($myIds)){
            $_where['type'] = 2;
            $_where['status'] = 1;
            $_where['relation_uid'] = $this->uid;
            $_where['uid'] = array('in', $myIds);
            $relNum['friends'] = M('UserRelation')->where($_where)->count();
        }else{
            $relNum['friends'] = 0;
        }

        $userInfo['relNum'] = $relNum;

        $userInfo['show_num'] = 0;
        if ($userInfo['is_supplier']){
            $userInfo['show_num'] = M('show')->where(array('uid'=>$uid))->count();
        }

        //距离
        if($id){
            $mylnglat = User($this->uid,array('lng','lat'));//我的位置
            $gpsApi = new GpsApi();
            $userInfo['distance'] = $gpsApi->MapAway($mylnglat['lng'].','.$mylnglat['lat'], $userInfo['lng'].','.$userInfo['lat'],2);
        }

        $this->ajaxRet(array('status' => 1,'info' =>'', 'data' => $userInfo));
    }

    //我 用户资料
    public function userInfo_bak(){
        $id = I('uid','','trim');
        // 如果是环信id
        if (!is_numeric($id) && !empty($id)){
            $hx_user = M('ucenter_member')->where(array('hx_id'=>$id))->find();
            if (!$hx_user){
                $this->ajaxRet(array('info'=>'用户不存在!'));
            }
            $id = $hx_user['id'];
        }
        if(!$id){
            $uid = $this->uid;
        }else{
            $uid = $id;
        }
        
        $userInfo = User($uid);
        if(empty($userInfo)){
            $this->ajaxRet(array('info'=>'用户不存在!'));
        }
        
        //是否关注
        if($id){
            $map['status'] = 1;
            $map['relation_uid'] = $id;
            $map['uid'] = $this->uid;
            if(M('UserRelation')->where($map)->count()){
                $userInfo['is_guanzhu'] = true;
            }else{
                $userInfo['is_guanzhu'] = false;
            }
        }
        
        //可兑换金币数量
        $toGold = D('AccountLog')->getToGold($uid, $userInfo['flower'],1);
        $userInfo['toGold'] = $toGold;
        
        $where['type'] = 2;
        $where['status'] = 1;
        
        //我的关注
        $where['uid'] = $this->uid;
        $where['is_supplier'] = 0;
        $relNum['my'] = M('UserRelation')->where($where)->count();
        
        //关注的商家
        $where['is_supplier'] = 1;
        $relNum['supplier'] = M('UserRelation')->where($where)->count();
        
        //我的粉丝
        unset($where['uid'], $where['is_supplier']);
        $where['relation_uid'] = $this->uid;
        $relNum['fans'] = M('UserRelation')->where($where)->count();
        
        //好友数量(互相关注的才是好友)
        unset($where['relation_uid']);
        $where['uid'] = $this->uid;
        $myIds = M('UserRelation')->where($where)->getField('relation_uid',true);
        if(!empty($myIds)){
            $_where['type'] = 2;
            $_where['status'] = 1;
            $_where['relation_uid'] = $this->uid;
            $_where['uid'] = array('in', $myIds);
            $relNum['friends'] = M('UserRelation')->where($_where)->count();
        }else{
            $relNum['friends'] = 0;
        }
        
        $userInfo['relNum'] = $relNum;
        
        //距离
        if($id){
            $mylnglat = User($this->uid,array('lng','lat'));//我的位置
            $gpsApi = new GpsApi();
            $userInfo['distance'] = $gpsApi->MapAway($mylnglat['lng'].','.$mylnglat['lat'], $userInfo['lng'].','.$userInfo['lat'],2);
        }
        
        $this->ajaxRet(array('status' => 1,'info' =>'', 'data' => $userInfo));
    }

    /**
     * 设置到店送花
     */
    public function set_to_shop()
    {
        $type = I('type',0,'intval'); //0关闭 1开启
        $content = I('content','','trim');
        $data = array(
            'to_shop'       =>  $type,
            'to_shop_desc'  =>  $content
        );
        $res = M('ucenter_member')->where(array('id'=>$this->uid))->save($data);
        if ($res === false){
            $this->ajaxRet(array('info' =>'设置失败'));
        } else {
            $this->ajaxRet(array('status' => 1,'info' =>'成功'));
        }
    }

    /**
     * 获取环信注册用户名和密码
     */
    public function get_hx_pwd(){
        $member = M('ucenter_member')
            ->where(array('id'=>$this->uid))
            ->getField('hx_uuid,hx_password');
        if ($member['hx_uuid']){
            $member['hx_username'] = md5($this->uid);
            unset($member['hx_uuid']);
            $this->ajaxRet(array('status' => 1,'info' =>'获取成功', 'data' => $member));
        } else {
            // 未注册环信，重新注册
            // 注册环信用户
            $res = D('UcenterMember')->reg_hx($this->uid);
            if ($res){
                $member = M('ucenter_member')
                    ->where(array('id'=>$this->uid))
                    ->getField('hx_password');
                $member['hx_username'] = md5($this->uid);
            } else {
                $member['hx_username'] = '';
                $member['hx_password'] = '';
            }
            $this->ajaxRet(array('status' => 1,'info' =>'获取成功', 'data' => $member));
        }
    }

    //完善用户资料
    public function doneUser(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        if(!D('UcenterMember')->doneUser($data)){
//            $this->ajaxRet(array('info'=>D('UcenterMember')->getError()));
            $this->ajaxRet(array('status'=>0,'info'=>D('UcenterMember')->getError(),'data'=>''));
        }else{
            //注册送金币
            D('UcenterMember')->Regmoney($this->uid);
            
            // 注册环信用户
            $res = D('UcenterMember')->reg_hx($this->uid);
            
            $UserInfo = User($this->uid);
            
            $this->ajaxRet(array('status'=>1,'info'=>'资料完善成功!','data'=>$UserInfo));
        }
    }

    /** 
     * 修改用户资料
     */
    public function UpdateUser() {
        $data = I('post.');
        $data['uid'] = $this->uid;
        
        $uid = D('Member')->UpdateUser($data);
        if ($uid !== false) {
            // 若修改头像
            if ($data['avatar']) {
                $data['Avatar'] = $data['avatar'];
                unset($data['avatar']);
                $file = D('UcenterMember')->setAvatar($data);
                if ($file) {
                } else {
                    $this->ajaxRet(array('status' => 0,'info' => '头像上传失败'.D('UcenterMember')->getError()));
                }
            }
            $this->ajaxRet(array('status' => 1, 'info' => '资料修改成功', 'data' => User($this->uid, true, true)));
        } else {
            $this->ajaxRet(array('info' => D('Member')->getError()));
        }
    }

    /**
     * 修改密码
     */
    public function RePwd() {
        if (!is_login()) {
            $this->ajaxRet(array('status' => 0, 'info' => '不在登录状态'));
        }

        $data = I('post.', '', 'text');

        if (!$data['old_password'])
            $this->ajaxRet(array('status' => 0, 'info' => '请填写旧密码'));

        $old = D('UcenterMember')->verifyUser(is_login(), $data['old_password']); //验证密码

        if (!$old) {
            $this->ajaxRet(array('status' => 0, 'info' => '您的旧密码不正确，如忘记请找回密码'));
        }

        if (!$data['password'])
            $this->ajaxRet(array('status' => 0, 'info' => '请填写新密码'));
        if (!$data['re_password'])
            $this->ajaxRet(array('status' => 0, 'info' => '请确认密码'));
        if ($data['old_password'] == $data['password'])
            $this->ajaxRet(array('status' => 0, 'info' => '新密码和旧密码不能完全样'));
        if ($data['password'] != $data['re_password'])
            $this->ajaxRet(array('status' => 0, 'info' => '2次密码输入的不一样'));
        if (!$data['sms_code'])
            $this->ajaxRet(array('status' => 0, 'info' => '请填写手机验证码'));

        if (!D('Verify')->checkVerify(User(is_login(), 'mobile'), 'pwd', $data['sms_code'])) {
            $this->ajaxRet(array('status' => 0, 'info' => D('Verify')->getError()));
        }

        $ret = D('UcenterMember')->changePassword($data['old_password'], $data['password']);

        if ($ret !== false) {
            D('Member')->logout();
            D('Verify')->delVerify(User(is_login(), 'mobile'));
            $this->ajaxRet(array('status' => 1, 'info' => '密码重置成功，请重新登陆'));
        } else {
            $this->ajaxRet(array('status' => 0, 'info' => '未知错误'));
        }
    }

    /**
     * 忘记密码
     */
    public function ForgetPwd() {

        $data = I('post.', '', 'text');

        if (!$data['mobile'])
            $this->ajaxRet(array('status' => 0, 'info' => '请填写手机号'));

        $user = D('UcenterMember')->where(array('mobile' => $data['mobile']))->find();
        if (!$user)
            $this->ajaxRet(array('status' => 0, 'info' => '不存在该手机用户'));

        if (!$data['password'])
            $this->ajaxRet(array('status' => 0, 'info' => '请填写新密码'));
        if (!$data['re_password'])
            $this->ajaxRet(array('status' => 0, 'info' => '请确认密码'));
        if ($data['password'] != $data['re_password'])
            $this->ajaxRet(array('status' => 0, 'info' => '2次密码输入的不一样'));
        if (!$data['sms_code'])
            $this->ajaxRet(array('status' => 0, 'info' => '请填写手机验证码'));

        if (!D('Verify')->checkVerify($data['mobile'], 'pwd', $data['sms_code'])) {
            $this->ajaxRet(array('status' => 0, 'info' => D('Verify')->getError()));
        }

        $password = think_ucenter_md5($data['password']);
        $ret = D('UcenterMember')->updateUserFieldss($user['id'], array('password' => $password));


        if ($ret !== false) {
            D('Verify')->delVerify($data['mobile']);
            $this->ajaxRet(array('status' => 1, 'info' => '操作成功，请使用您的新密码登陆吧'));
        } else {
            $this->ajaxRet(array('status' => 0, 'info' => $user['id']));
        }
    }

    /**
     * 忘记密码
     */
    public function UpdateAddress($map = null) {

        if (!is_login()) {
            $this->ajaxRet(array('status' => 0, 'info' => '不在登录状态'));
        }
        if (!$map) {
            $this->ajaxRet(array('status' => 0, 'info' => '缺少参数map'));
        }

        $ret = D('Member')->UpdateAddress(is_login(), op_t($map));

        if ($ret !== false) {
            $this->ajaxRet(array('status' => 1, 'info' => '地理位置更新成功'));
        } else {
            $this->ajaxRet(array('status' => 0, 'info' => '地理位置更新失败'));
        }
    }

    /** 
     * 退出登陆
     */
    public function QuiteLogin() {
        if (!is_login()) {
            $this->ajaxRet(array('status' => 0, 'info' => '不在登录状态'));
        }

        D('Member')->logout();
        $this->ajaxRet(array('status' => 1, 'info' => '退出成功'));
    }

    //上传头像
    public function Avatar() {
        $data['Avatar'] = I('post.avatar');
        $data['uid'] = $this->uid;
        $file = D('UcenterMember')->setAvatar($data);

        if ($file) {
            $this->ajaxRet(array('status' => 1, 'info' => '上传成功', 'data' => User($this->uid, 'avatar256')));
        } else {
            $this->ajaxRet(array('info' => '上传失败'.D('UcenterMember')->getError()));
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
     * 创建收货地址
     */
    public function AddAddress($id = null) {
        if (!is_login()) {
            $this->ajaxRet(array('status' => 0, 'info' => '请先登录'));
        }

        $ret = D('UserAddress')->Update();

        if ($ret !== false) {
            $this->ajaxRet(array('status' => 1, 'info' => $id ? '修改成功' : '创建成功', 'aid' => $ret));
        } else {
            $this->ajaxRet(array('status' => 0, 'info' => D('UserAddress')->getError()));
        }
    }

    /** 
     * 获取收货地址列表
     */
    public function UserAddress() {
        if (!is_login()) {
            $this->ajaxRet(array('status' => 0, 'info' => '请先登录'));
        }

        $list = D('UserAddress')->where(array('uid' => is_login()))->order('create_time desc')->field('uid,town,street_number,map,create_time', true)->select();

        if ($list) {
            $this->ajaxRet(array('status' => 1, 'list' => $list));
        } else {
            $this->ajaxRet(array('status' => 0, 'info' => '您还没有收货地址，现在就创建一个吧'));
        }
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
    
    //兑换现金
    //$type 类型 0:鲜花兑换金币 1:金币兑换现金 2:现金提现
    public function toMoney(){
        $number = I('post.number',0,'intval');
        if(D('AccountLog')->addlog($this->uid, $number, 1) === true){
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

    //鲜花兑换现金
    //$toMoney 0:兑换 1:返回可兑换数量
    public function flowerToMoney(){
        //要兑换的鲜花数量
        $number = I('post.number', 0, 'intval');
        
        $toMoney = I('post.tomoney',0,'intval');
        if($toMoney == 1){
            $this->ajaxRet(array('status'=>1,'info'=>'成功','data'=>D('AccountLog')->FlowerToMoney($this->uid,$number,1)));
        }
        if(D('AccountLog')->addlog($this->uid, $number, 9) === true){
            $this->ajaxRet(array('status'=>1,'info'=>'兑换成功','data'=>User($this->uid,'money')));
        }else{
            $this->ajaxRet(array('info'=>D('AccountLog')->getError()));
        }
    }


    //通过鲜花直接提现 (通过金币中转,暂停使用)
    public function flowerToMoney_(){
        //要提现的鲜花数量
        $number = I('post.number',0,'intval');
        
        $GOLDMONEY = C('GOLDMONEY');
        $keys = array_keys($GOLDMONEY);
        
        $FLOWERGOLD = C('FLOWERGOLD');
        foreach ($FLOWERGOLD as $key => $value){
            if($value >= $keys[0]){
                $lowest_flower = $key;
                break;
            }
        }
        
        if($number < $lowest_flower){
            $this->ajaxRet(array('info'=>'鲜花提现需要最少'.$lowest_flower.'花'));
        }
        
        //1:返回鲜花可以直接提现的金额 2:执行鲜花提现
        $type = I('post.type',1,'intval');
        
        //鲜花兑换金币数量
        $gold_number = D('AccountLog')->getToGold($this->uid,$number);
        if(!$gold_number){
            $this->ajaxRet(array('info'=>D('AccountLog')->getError()));
        }
        
        //金币兑换现金数量
        $money_number = D('AccountLog')->getToMoney($this->uid,$gold_number['gain_number'],1);
        if(!$money_number){
            $this->ajaxRet(array('info'=>D('AccountLog')->getError()));
        }
        
        if($type == 1){
            $this->ajaxRet(array('status'=>1, 'info'=>'可兑换'.$money_number['gain_number'].'元现金', 'data'=>$money_number['gain_number']));
        }
        
        //检查提现
        if(!D('AccountLog')->checkMoney($this->uid, $money_number['gain_number'], 1)){
            $this->ajaxRet(array('info'=>D('AccountLog')->getError())); 
        }
        
        
        //执行鲜花兑换金币
        if(D('AccountLog')->addlog($this->uid, $number, 0)){
            //执行金币兑换现金
            if(D('AccountLog')->addlog($this->uid, $gold_number['gain_number'], 1)){
                //执行提现
                if(D('AccountLog')->addlog($this->uid, $money_number['gain_number'], 2)){
                    $this->ajaxRet(array('status'=>1, 'info'=>'成功'));
                }
            }
        }
        
        $this->ajaxRet(array('info'=>D('AccountLog')->getError()));
        
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
        
        if(M('UcenterMember')->where(array('id'=>$this->uid))->save($save) !== false){
            D('Show')->Celebrity($this->uid);
            CleanUser($this->uid);
            $this->ajaxRet(array('status'=>1, 'info'=>'成功'));
        }
        
    }

    /**
     * 关系列表
     */
    public function relation_list()
    {
        $type = I('type',1,'intval');//列表类型 1:关注商家 2:关注会员 3:粉丝 4:好友
        $keyword = I('keyword');
        $uid = I('uid',0,'intval');
        $uid = $uid ? $uid : $this->uid;
        
        $map['ur.status'] = 1;
        $map['m.status'] = 1;
        
        if(!empty($keyword)){
            $map['m.uid|m.nickname|m.mobile|mm.uid|mm.nickname|mm.mobile'] = array('like', "%$keyword%");
        }
        
        
        if ($type){
            $map['ur.type'] = 2;
        } else {
            $map['ur.type'] = 1;
        }
        $member = M('UcenterMember')->field('lng,lat')->where(array('id'=>$uid))->find();
        
        //关注的商家
        if ($type == 1){
            $map['ur.uid'] = $uid;
            $map['ur.is_supplier'] = 1;
            $total = M('user_relation')
                ->alias('ur')
                ->field('m.uid,m.nickname,a.headimgurl,um.flower,um.lng,um.lat,ur.is_supplier')
                ->join('__UCENTER_MEMBER__ um ON um.id = ur.relation_uid')
                ->join('__MEMBER__ m ON m.uid = ur.relation_uid')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = ur.relation_uid')
                ->where($map)
                ->count();
            $REQUEST = (array) I('request.');
            if (isset($REQUEST['r'])) {
                $listRows = (int)$REQUEST['r'];
            } else {
                $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
            }
            $page = new \Think\Page($total, $listRows, $REQUEST);
            $page->show();
            $totalPages = $page->totalPages;//总页数
            $limit = $page->firstRow . ',' . $page->listRows;
            $list = M('user_relation')
                ->alias('ur')
                ->field('m.uid,m.nickname,a.headimgurl,um.flower,um.lng,um.lat,ur.is_supplier')
                ->join('__UCENTER_MEMBER__ um ON um.id = ur.relation_uid')
                ->join('__MEMBER__ m ON m.uid = ur.relation_uid')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = ur.relation_uid')
                ->where($map)
                ->order('ur.id desc')
                ->limit($limit)
                ->select();
        } 
        
        //关注会员
        elseif($type == 2){
            $map['ur.uid'] = $uid;
            $map['ur.is_supplier'] = 0;
            $total = M('user_relation')
                ->alias('ur')
                ->field('m.uid,m.nickname,a.headimgurl,um.flower,um.lng,um.lat,ur.is_supplier')
                ->join('__UCENTER_MEMBER__ um ON um.id = ur.relation_uid')
                ->join('__MEMBER__ m ON m.uid = ur.relation_uid')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = ur.relation_uid')
                ->where($map)
                ->count();
            $REQUEST = (array) I('request.');
            if (isset($REQUEST['r'])) {
                $listRows = (int)$REQUEST['r'];
            } else {
                $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
            }
            $page = new \Think\Page($total, $listRows, $REQUEST);
            $page->show();
            $totalPages = $page->totalPages;//总页数
            $limit = $page->firstRow . ',' . $page->listRows;
            $list = M('user_relation')
                ->alias('ur')
                ->field('m.uid,m.nickname,a.headimgurl,um.flower,um.lng,um.lat,ur.is_supplier')
                ->join('__UCENTER_MEMBER__ um ON um.id = ur.relation_uid')
                ->join('__MEMBER__ m ON m.uid = ur.relation_uid')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = ur.relation_uid')
                ->where($map)
                ->order('ur.id desc')
                ->limit($limit)
                ->select();
        } 
        
        //粉丝
        elseif($type == 3){
            $map['ur.type'] = 2;
            $map['ur.relation_uid'] = $uid;
            $total = M('user_relation')
                ->alias('ur')
                ->field('m.uid,m.nickname,a.headimgurl,um.flower,um.lng,um.lat,ur.is_supplier')
                ->join('__UCENTER_MEMBER__ um ON um.id = ur.uid')
                ->join('__MEMBER__ m ON m.uid = ur.uid')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = ur.uid')
                ->where($map)
                ->count();
            $REQUEST = (array) I('request.');
            if (isset($REQUEST['r'])) {
                $listRows = (int)$REQUEST['r'];
            } else {
                $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
            }
            $page = new \Think\Page($total, $listRows, $REQUEST);
            $page->show();
            $totalPages = $page->totalPages;//总页数
            $limit = $page->firstRow . ',' . $page->listRows;
            $list = M('user_relation')
                ->alias('ur')
                ->field('m.uid,m.nickname,a.headimgurl,um.flower,um.lng,um.lat,ur.is_supplier')
                ->join('__UCENTER_MEMBER__ um ON um.id = ur.uid')
                ->join('__MEMBER__ m ON m.uid = ur.uid')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = ur.uid')
                ->where($map)
                ->order('ur.id desc')
                ->limit($limit)
                ->select();
        } 
        
        //好友
        elseif($type == 4) {
            /*
            $map['ur.uid'] = $uid;
            $total = M('user_relation')
                ->alias('ur')
                ->field('m.uid,m.nickname,a.headimgurl,um.flower,um.lng,um.lat,ur.is_supplier')
                ->join('__UCENTER_MEMBER__ um ON um.id = ur.relation_uid')
                ->join('__MEMBER__ m ON m.uid = ur.relation_uid')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = ur.relation_uid')
                ->where($map)
                ->count();
            $REQUEST = (array) I('request.');
            if (isset($REQUEST['r'])) {
                $listRows = (int)$REQUEST['r'];
            } else {
                $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
            }
            $page = new \Think\Page($total, $listRows, $REQUEST);
            $page->show();
            $totalPages = $page->totalPages;//总页数
            $limit = $page->firstRow . ',' . $page->listRows;
            $list = M('user_relation')
                ->alias('ur')
                ->field('m.uid,m.nickname,a.headimgurl,um.flower,um.lng,um.lat,ur.is_supplier')
                ->join('__UCENTER_MEMBER__ um ON um.id = ur.relation_uid')
                ->join('__MEMBER__ m ON m.uid = ur.relation_uid')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = ur.relation_uid')
                ->where($map)
                ->order('ur.id desc')
                ->limit($limit)
                ->select();
             */
            
            $where['ur.uid'] = $uid;
            if(!empty($keyword)){
                $where['ur.relation_uid|m.nickname|m.mobile'] = array('like', "%$keyword%");
            }
            
            //先获得我关注的用户
            $myIds = M('UserRelation')->alias('ur')
                    ->field('ur.relation_uid')
                    ->join('__MEMBER__ m ON m.uid = ur.relation_uid')
                    ->where($where)
                    ->select();
            
            if(!empty($myIds)){
                foreach($myIds as $value){
                    $_myIds[] = $value['relation_uid'];
                }
                
                $_where['type'] = 2;
                $_where['status'] = 1;
                $_where['relation_uid'] = $uid;
                $_where['uid'] = array('in', $_myIds);
                $list = $this->lists('UserRelation', $_where ,'id DESC');
                $totalPages = $this->_totalPages;
            }
        }
        
        //全部
        elseif($type == 5){
            unset($map['ur.type']);
            $map['ur.uid|ur.relation_uid'] = $uid;
            $_list = M('UserRelation')->alias('ur')
                    ->field('ur.uid, ur.relation_uid')
                    ->join('__MEMBER__ m ON m.uid = ur.relation_uid')
                    ->join('__MEMBER__ mm ON mm.uid = ur.uid')
                    ->where($map)
                    ->select();
            foreach($_list as $value){
                if($uid != $value['uid']){
                    $_data[] = $value['uid'];
                }
                if($uid != $value['relation_uid']){
                    $_data[] = $value['relation_uid'];
                }
            }
            
            $_data = array_unique($_data);
            if(!empty($_data)){
                foreach ($_data as $v){
                    $vs['uid'] = $v;
                    $list[] = $vs;
                }
            }
        }
        
        
        if ($list){
            foreach ($list as &$item){
                $item['userinfo'] = User($item['uid']);
                $lng = $item['userinfo']['lng'];
                $lat = $item['userinfo']['lat'];
                
                $gpsApi = new GpsApi();
                $distance = $gpsApi->MapAway($member['lng'].','.$member['lat'],$lng.','.$lat,2);
                if ($distance){
                    $item['distance'] = $distance;
                } else {
                    $item['distance'] = 0;
                }
            }
        }
        
        else {
            $list = array();
            $totalPages = 0;
        }
        
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>array('list'=>$list,'total_pages'=>$totalPages,'lng'=>$member['lng'],'lat'=>$member['lat'])));
    }

    /**
     * 粉丝排行 - 6条
     */
    public function subscribe_top()
    {
        $map['relation_uid'] = array('neq',$this->uid);
        $map['uid'] = array('neq',$this->uid);
        $map['type'] = 2;
        $list = M('user_relation')
            ->field('count(`id`) num,relation_uid uid')
            ->where($map)
            ->group('relation_uid')
            ->order('num desc')
            ->limit(6)
            ->select();
        if ($list){
            foreach ($list as &$item){
                $item['userinfo'] = User($item['uid']);
            }
        } else {
            $list = M('ucenter_member')->field('id')->order('id desc')->limit(6)->select();
            if ($list){
                foreach ($list as &$item){
                    $item['userinfo'] = User($item['uid']);
                }
            } else {
                $list = array();
            }
        }
        $this->ajaxRet(array('status'=>1, 'info'=>'成功','data'=>$list));
    }
}
