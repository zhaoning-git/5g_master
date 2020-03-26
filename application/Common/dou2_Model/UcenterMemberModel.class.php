<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Model;

use Think\Model;

/**
 * 会员模型
 */
class UcenterMemberModel extends Model {

    protected $_validate = array(
        //验证用户名
        array('username', 'checkUsername', -20, self::VALUE_VALIDATE, 'callback'), //用户名不合法
        //array('password', '/^[a-zA-Z0-9_]{6,18}$/', -13, self::VALUE_VALIDATE),
    );
    //用户模型自动完成
    protected $_auto = array(
        //array('password', 'think_ucenter_md5', self::MODEL_BOTH, 'function'),
        array('reg_time', NOW_TIME, self::MODEL_INSERT),
        array('reg_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
        array('update_time', NOW_TIME),
        array('status', '1'),
    );

    /**
     * 检测邮箱是不是被占用
     * @param  string $email 邮箱
     * @return boolean       ture - 未禁用，false - 禁止注册
     */
    protected function checkUniqueEmail($email) {
        if (M('Member')->where(array('email' => $email))->find()) {
            return false;
        }
        return true;
    }

    /**
     * 检测用户名是不是被占用
     * @param  string $email 邮箱
     * @return boolean       ture - 未禁用，false - 禁止注册
     */
    protected function checkUniqueUsername($username) {
        if ($this->where(array('username' => $username))->find()) {
            return false;
        }

        if (M('Users')->where(array('user_name' => $username))->find()) {
            return false;
        }

        return true;
    }

    /**
     * 检测用户名是不是合法
     * @param  string $email 邮箱
     * @return boolean       ture - 未禁用，false - 禁止注册
     */
    protected function checkUsername($username) {
        //如果用户名中有空格，不允许注册
        if (strpos($username, ' ') !== false) {
            return false;
        }
        preg_match("/^[a-zA-Z0-9_]{1,30}$/", $username, $result);

        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * 检测手机是不是被被占用
     * @param  string $mobile 手机
     * @return boolean        ture - 未禁用，false - 禁止注册
     */
    protected function checkUniqueMobile($mobile) {
        if (M('Member')->where(array('mobile' => $mobile))->find()) {
            return false;
        }
        return true;
    }

    /**
     * 检测手机是不是被禁止注册
     * @param  string $mobile 手机
     * @return boolean        ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyMobile($mobile) {
        return true;
    }

    /**
     * 注册一个新用户
     * @param  string $mobile 手机
     * @param  string $nickname 昵称
     * @param  string $password 用户密码
     * @return integer 注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $password = '') {
        $data['username'] = $username;
        $data['password'] = $password;

        if(!checkPhone($data['username'])){
            return '错误的用户名格式!';
        }
        
        //查询是否已经注册
        $userInfo = $this->where(array('username'=>$data['username']))->find();
        if(empty($userInfo)){
            //添加用户
            $usercenter_member = $this->create($data, 1);
            if ($usercenter_member) {
                $usercenter_member['password'] = $password ? think_ucenter_md5($password) : '';
                $uid = $this->add($usercenter_member);
                action_log('reg', 'ucenter_member', $uid, $uid);
            } else {
                return $this->getError(); //错误详情见自动验证注释
            }
        }else{
            $uid = $userInfo['id'];
            $Member = M('Member')->where(array('uid'=>$uid))->find();
            if(!empty($Member)){
                return '用户已经存在!';
            }
            $this->where(array('id'=>$uid))->setField('password',think_ucenter_md5($password));
        }
        
        return $uid ? $uid : 0; //0-未知错误，大于0-注册成功
    }

    //第三方注册
    public function ThirdRegister($type, $third_token){
        if(empty($type) || empty($third_token)){
            $this->error = '参数有误!';
            return false;
        }
        
        $data['type'] = intval($type);
        $data['third_token'] = $third_token;
        //查询是否已经注册
        $userInfo = $this->where($data)->find();
        if(empty($userInfo)){
            //添加用户
            $data['username'] = 'dw_' . ($this->max('id')+1);
            $data['password'] = 'TSeBuAs38Jesd';
            $data = $this->create($data, 1);
            if ($data) {
                $uid = $this->add($data);
                if(is_numeric($uid) && $uid){
                    $save['username'] = 'dw_'.$uid;
                    $save['password'] = think_ucenter_md5($data['password']);
                    $this->where(array('id'=>$uid))->save($save);
                }
                action_log('reg', 'ucenter_member', $uid, $uid);
            } else {
                $this->error = $this->getError(); //错误详情见自动验证注释
                return false;
            }
        }
        
        else{
            $uid = $userInfo['id'];
            $Member = M('Member')->where(array('uid'=>$uid))->find();
            if(!empty($Member)){
                $this->error = '用户已经存在';
                return false;
            }
        }
        
        return $uid ? $uid : 0;
    }

    

    /**
     * 注册环信
     * @param int $uid
     * @return bool
     */
    public function reg_hx($uid = 0){
//        $member = M('ucenter_member')
//            ->alias('um')
//            ->field('um.username,um.password,m.nickname,um.hx_uuid')
//            ->join('__MEMBER__ m ON m.uid = um.id')
//            ->where(array('um.id'=>$uid))
//            ->find();
        
        
        $member = User($uid);
        if (!$member['hx_uuid']){
            // 未注册，注册
            vendor('Huanxin');
            $huanxin = new \Huanxin(C('HX_CONFIG'));
            $now_time = time();
            $user = array(
                'username'  =>  md5($uid),
                'password'  =>  md5($uid.$now_time),
                'nickname'  =>  $member['nickname']
            );
            $res = $huanxin->createUser($user);
            if ($res){
                // 将用户信息保存到数据表
                $update_data = array(
                    'hx_uuid'  =>  empty($res[0]['uuid']) ? '' :$res[0]['uuid'],
                    'hx_id'     =>  md5($uid),
                    'hx_password'   =>  $user['password'],
                    'hx_reg_time'   =>  $now_time
                );
                M('UcenterMember')->where(array('id'=>$uid))->save($update_data);
                CleanUser($uid);
                return true;
            } else {
                file_put_contents('./hx_debug.log','[createUser]'.json_encode($huanxin->error)."\r\n",FILE_APPEND);
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 修改密码
     * @param int $uid
     * @param string $password
     * @return bool
     */
    public function changePwd($uid = 0,$password = '')
    {
        $data = array(
            'password'  =>  think_ucenter_md5($password)
        );
        $res = $this->where(array('id'=>$uid))->save($data);
        if ($res === false){
            return false;
        } else {
            return true;
        }
    }

    //完善用户资料 注册第二步,不适合做后期的用户资料修改
    //$uid, $parent_id  $region_id  $nickname, $mobile, $email, $birthday, $sex, $Avatardata,           $is_supplier
    //用户ID 推荐人       地区         昵称       手机号码   邮箱     生日       性别   头像文件base64格式数据    是否是商户
    public function doneUser($data = array()) {
        $uid = intval($data['uid']);
        $userInfo = $this->where(array('id'=>$uid))->find();

        if (empty($userInfo)) {
            $this->error = '用户不存在!';
            return false;
        }
        
        $Membercount = M('Member')->where(array('uid'=>$uid))->count();
        if($Membercount){
            $this->error = '用户资料已完善!';
            return false;
        }

        //是否是商户
        $data['is_supplier'] = intval($data['is_supplier']);
        
        //上级推荐人
        $parent_id = intval($data['parent_id']);
        if($parent_id){
            $parentInfo = User($parent_id,true,true);
            if(!$parentInfo){
                $this->error = '上级推荐人不存在!';
                return false;
            }
            $Ucen['parent_id'] = $parent_id;
            if($parentInfo['salesman']){
                $Ucen['salesman_id'] = $parent_id;
            }
        }
        
        
        if($data['is_supplier'] == 1){
            if (!$parent_id) {
                $this->error = '商户必须有上级推荐人!';
                return false;
            }
            
            if(!$parentInfo['salesman']){
                $this->error = '商户上级推荐人必须是业务员!';
                return false;
            }
            
            $Ucen['is_supplier'] = 1;
            if(!intval($data['supplier_type'])){
                $this->error = '请选择商户类型!';
                return false;
            }else{
                $Ucen['supplier_type'] = intval($data['supplier_type']);
            }
        }else{
            $Ucen['is_supplier'] = 0;
        }
        
        
        //所属地区
        $region_id = intval($data['region_id']);
        if(!$region_id){
            $this->error = '所属地区不能为空!';
            return false;
        }else{
            $district = M('District')->where(array('id'=>$region_id))->find();
            if(empty($district)){
                $this->error = '所选城市不存在!';
                return false;
            }elseif($region_id == C('DEFAULTCITY')){
                $this->error = '所在地区定位错误!';
                return false;
            }else{
                $Ucen['region_id'] = $region_id;
                
                //所属代理商
                $Ucen['agents_id'] = D('Agents')->cityAgentsid($Ucen['region_id']);
                if ($Ucen['is_supplier'] == 1 && $Ucen['agents_id'] = C('DEFAULTAGENTS')){
                    //$this->error = '商家的所属代理商不能是系统默认代理商!';
                    //return false;
                }
            }
        }
        
        //手机 && 邮箱
        $me = $this->check_Mobile_Email($data['mobile'], $data['email'], $uid);
        if ($me === false) {
            return false;
        }
        
        if (!empty($me['mobile'])) {
            $Member['mobile'] = $me['mobile'];
        }

        if (!empty($me['email'])) {
            $Member['email'] = $me['email'];
        }

        //联系方式
        if (!empty($data['phone'])) {
            $Member['phone'] = $data['phone'];
        }
        
        //昵称
        if (!empty($data['nickname'])) {
            $Member['nickname'] = $data['nickname'];
        }

        //性别
        if (!empty($data['sex'])) {
            $Member['sex'] = $data['sex'];
        }

        //生日
        if (!empty($data['birthday'])) {
            $Member['birthday'] = $data['birthday'];
            if (is_numeric($Member['birthday'])) {
                $Member['birthday'] = date('Y-m-d', $Member['birthday']);
            }
        }

        //头像处理
        if (!empty($data['Avatardata'])) {
            $Avatar['uid'] = $uid;
            $Avatar['Avatar'] = $data['Avatardata'];
            if (!$this->setAvatar($Avatar)) {
                return false;
            }
        }


        if (!empty($Ucen)) {
            $this->where(array('id' => $uid))->save($Ucen);
            //D('UserLink')->AddUserLink($uid, $Ucen['parent_id']);
        }

        if (!empty($Member)) {
            if(!$Membercount){
                $Member['uid'] = $uid;
                M('Member')->add($Member);
            }
        }

        CleanUser($uid);
        return true;
    }

    //检查手机号码和邮箱
    function check_Mobile_Email($mobile = '', $email = '', $uid = '') {
        $uid = intval($uid);
        if (!$uid) {
            $this->error = '参数有误!';
            return false;
        }

        $userInfo = M('Member')->where(array('uid'=>$uid))->find();
        
        $data = array();
        if (!empty($mobile)) {
            if (!checkPhone($mobile)) {
                $this->error = '手机号码格式不正确';
                return false;
            } elseif (!empty($userInfo)) {
                if ($userInfo['mobile'] != $mobile && !$this->checkUniqueMobile($mobile)) {
                    $this->error = '手机号码被占用!';
                    return false;
                }
            } else {
                if (!$this->checkUniqueMobile($mobile)) {
                    $this->error = '手机号码被占用!';
                    return false;
                }
            }
            $data['mobile'] = $mobile;
        }

        if (!empty($email)) {
            if (!checkEmail($email)) {
                $this->error = '邮箱格式不正确';
                return false;
            } elseif (!empty($userInfo)) {
                if ($userInfo['email'] != $email && !$this->checkUniqueEmail($email)) {
                    $this->error = '邮箱被占用!';
                    return false;
                }
            } else {
                if (!$this->checkUniqueEmail($email)) {
                    $this->error = '邮箱被占用!';
                    return false;
                }
            }
            $data['email'] = $email;
        }
        return $data;
    }

    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  string  $table 用户登录类型（记录用户的数据表）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function Authlogin($loginstring, $password, $table = 'Member') {
        if ($loginstring == null) {
            return -38;
        }

        if (checkPhone($loginstring)) {
            $map['username'] = $loginstring;
        } elseif (checkEmail($loginstring)) {
            $map['email'] = $loginstring;
        } elseif (is_numeric($loginstring) && !checkPhone($loginstring)) {
            $map['id'] = $loginstring;
        } else {
            $map['username'] = $loginstring;
        }


        //获取用户数据
        $user = $this->where($map)->find();
        if (empty($user)) {
            return -35; //用户不存在或被禁用
        }

        $join = M('Member')->where(array('uid' => $user['id']))->find();

        $return = check_action_limit('input_password', 'ucenter_member', $user['id'], $user['id']);
        if ($return && !$return['state']) {
            return $return['info'];
        }

        if (is_array($user) && $user['status']) {
            //验证用户密码
            if (think_ucenter_md5($password) == $user['password']) {
                if (empty($join)) {
                    return -64; //用户资料未完善
                }
                return $user['id']; //登录成功，返回用户ID
            } else {
                action_log('input_password', 'ucenter_member', $user['id'], $user['id']);
                return -34; //密码错误
            }
        } else {
            return -35; //用户不存在或被禁用
        }
    }

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($uid, $remember = false) {
        //检测是否在当前应用注册
        $user = $this->field(true)->find($uid);
        if (!$user || 1 != $user['status']) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }
        //记录行为
        action_log('user_login', 'ucenter_member', $uid, $uid);

        //登录用户 更新登录信息
        $data = array(
            'id' => $user['id'],
            'login' => array('exp', '`login`+1'),
            'last_time' => NOW_TIME,
            'last_ip' => get_client_ip(1),
        );

        $this->save($data);

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid' => $data['id'],
            'nickname' => get_nickname($user['id']),
            'last_time' => $data['last_time']
        );

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));

        $Ut = M('UserToken')->where(array('uid' => $user['id']))->find();

        $UT_data['token'] = getToken($user['id']);
        
        $UT_data['time'] = time();
        $UT_data['ip'] = get_client_ip();

        if (empty($Ut)) {
            $UT_data['uid'] = $user['id'];
            M('UserToken')->add($UT_data);
        } else {
            M('UserToken')->where(array('uid' => $user['id']))->save($UT_data);
        }
        CleanUser($user['id']);
        return true;
    }

    public function getToken($uid = 0){
        if (!$uid){
            return false;
        }
        $Ut = M('UserToken')->where(array('uid' => $uid))->find();
        $UT_data['token'] = getToken($uid);
        $UT_data['time'] = time();
        $UT_data['ip'] = get_client_ip();
        if (empty($Ut)) {
            $UT_data['uid'] = $uid;
            M('UserToken')->add($UT_data);
        } else {
            M('UserToken')->where(array('uid' => $uid))->save($UT_data);
        }
        return $UT_data['token'];
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout() {
        session('user_auth', null);
        session('user_auth_sign', null);
        cookie('OX_LOGGED_USER', NULL);
        //session('SYNCLOGIN', null);
    }

    public function getLocal($username, $password) {
        $map = array();
        $map['username'] = $username;

        /* 获取用户数据 */
        $user = $this->where($map)->find();

        if (is_array($user) && $user['status']) {
            /* 验证用户密码 */
            if (think_ucenter_md5($password, C('UC_AUTH_KEY')) === $user['password']) {
                return $user; //登录成功，返回用户ID
            } else {
                return false; //密码错误
            }
        } else {
            return false; //用户不存在或被禁用
        }
    }

    /**
     * 获取用户信息
     * @param  string  $uid 用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function info($uid, $is_username = false) {
        $map = array();
        if ($is_username) { //通过用户名获取
            $map['username'] = $uid;
        } else {
            $map['id'] = $uid;
        }

        $user = $this->where($map)->field('id,username,email,mobile,status')->find();
        if (is_array($user) && $user['status'] = 1) {
            return array($user['id'], $user['username'], $user['email'], $user['mobile']);
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 更新用户信息
     * @param int    $uid 用户id
     * @param string $password 密码，用来验证
     * @param array  $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author huajie <banhuajie@163.com>
     */
    public function updateUserFields($uid, $password, $data) {
        if (empty($uid) || empty($password) || empty($data)) {
            $this->error = '参数错误！25';
            return false;
        }

        //更新前检查用户密码
        if (!$this->verifyUser($uid, $password)) {
            $this->error = '验证出错：密码不正确！';
            return false;
        }

        //更新用户信息
        $data = $this->create($data, 2); //指定此处为更新数据
        if ($data) {
            return $this->where(array('id' => $uid))->save($data);
        }
        return false;
    }

    /**
     * 重置用户密码
     * @param int    $uid 用户id
     * @param string $password 密码，用来验证
     * @param array  $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author huajie <banhuajie@163.com>
     */
    public function updateUserFieldss($uid, $data) {

        if (empty($uid) || empty($data)) {
            $this->error = '参数错误！26';
            return false;
        }
        //更新用户信息

        if ($data) {
            return $this->where(array('id' => $uid))->save($data);
        }
        return false;
    }

    /**
     * 验证用户密码
     * @param int    $uid 用户id
     * @param string $password_in 密码
     * @return true 验证成功，false 验证失败
     * @author huajie <banhuajie@163.com>
     */
    public function verifyUser($uid, $password_in) {
        $password = $this->getFieldById($uid, 'password');
        if (think_ucenter_md5($password_in, C('UC_AUTH_KEY')) === $password) {
            return true;
        }
        return false;
    }

    //微信注册
    public function addSyncData($info) {
        $data['username'] = $this->rand_username();
        $data['email'] = $this->rand_email();
        $data['password'] = $this->create_rand(10);
        $data1 = $this->create($data);
        $uid = $this->add($data1);

        if (D('Member')->addSyncData($uid, $info) === false) {
            return $this->delete($uid); //删除
        }
        return $uid;
    }

    //随机邮箱
    public function rand_email() {
        $email = $this->create_rand(10) . '@ocenter.com';
        if ($this->where(array('email' => $email))->select()) {
            $this->rand_email();
        } else {
            return $email;
        }
    }

    //随机用户名
    public function rand_username() {
        $username = $this->create_rand(10);
        if ($this->where(array('username' => $username))->select()) {
            $this->rand_username();
        } else {
            return $username;
        }
    }

    function create_rand($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**  修改密码
     * @param $old_password
     * @param $new_password
     * @return bool
     * @auth 陈一枭
     */
    public function changePassword($old_password, $new_password) {
        //检查旧密码是否正确
        if (!$this->verifyUser(get_uid(), $old_password)) {
            $this->error = -41;
            return false;
        }
        //更新用户信息
        $model = $this;
        $data = array('password' => $new_password);
        $data = $model->create($data, 5);
        if (!$data) {
            $this->error = $model->getError();
            return false;
        }
        $model->where(array('id' => get_uid()))->save($data);
        //返回成功信息
        CleanUser(get_uid(), 'password'); //删除缓存
        D('user_token')->where('uid=' . get_uid())->delete();
        return true;
    }

    /**
     * 后台创建编辑会员
     * @author  
     */
    public function editUser($POST='') {
        $_POST = $POST;
        //添加
        if (empty($_POST['id'])) {

            $data = $this->create($_POST, 1);
            if (!$data) { //数据对象创建错误
                $this->error = $this->getMsgError($this->error);
                return false;
            }
           
            $uid = $this->register($_POST['mobile'], $data['password']);
            
            if(is_numeric($uid) && $uid > 0){
                $_POST['uid'] = $uid;
                if($this->doneUser($_POST) === true){
                    return $uid;
                }else{
                    return false;
                }
            }else{
                $this->error = $uid;
                return false;
            }
        }

        //更新
        else {
            $data = $this->create($_POST, 2);
            $uid = intval($data['uid']);
            
            $result = D('Member')->UpdateUser($uid, $data);
            if($result !== false){
                return true;
            }else{
                $this->error = D('Member')->getError();
                return false;
            }
        }
    }

    public function getMsgError($error_code = null) {
        $code = $error_code == null ? $this->error : $error_code;

        if (!is_numeric($code)) {
            return $code;
        }

        switch ($code) {
            case -1:
                $error = '用户名长度在3-12个字符之间,并且只能为字母、数字、下划线';
                break;
            case -2:
                $error = '手机不能为空!';
                break;

            case -3:
                $error = '用户名被占用';
                break;
            case -4:
                $error = '密码长度需要6-30位';
                break;
            case -5:
                $error = '邮箱格式不正确';
                break;
            case -7:
                $error = '请填写邮箱';
                break;
            case -8:
                $error = '该邮箱已经注册过';
                break;
            case -9:
                $error = '手机格式不正确！';
                break;
            case -10:
                $error = '手机被禁止注册！';
                break;
            case -11:
                $error = '该手机号已经注册过了！';
                break;
            case -13: $error = '密码格式不正确,由大小写字母,数字和下划线组成,6到18位！';
                break;
            case -20:
                $error = '用户名只能为字母、数字、下划线';
                break;
            case -22:
                $error = '请填写密码';
                break;
            case -30:
                $error = '昵称被占用！';
                break;
            case -41:
                $error = '用户旧密码不正确';
                break;
            case -31:
                $error = '昵称被禁止注册！';
                break;
            case -32:
                $error = '昵称只能由数字、字母、汉字和"_"组成！';
                break;
            case -33:
                $error = '昵称不能少于1个字！';
                break;
            case -34:
                $error = '密码错误';
                break;
            case -35:
                $error = '用户不存在';
                break;
            case -36:
                $error = '域名不能为空，请填写4-10个字母或数字';
                break;
            case -37:
                $error = '手机不能为空';
                break;
            case -38:
                $error = '你无权修改管理员';
                break;
            case -60:
                $error = '不存在该推荐人，请核实';
                break;
            case -61:
                $error = '推荐人不能为空!';
                break;
            case -62:
                $error = '参数错误!';
                break;
            case -63:
                $error = '数据对象创建错误!';
                break;
            case -64:
                $error = '用户资料未完善!';
                break;

            default:
                $error = '未知错误';
        }
        return $error;
    }

    //上传用户头像
    public function setAvatar($data = array()) {
        $Result = array('status' => 0, 'ret' => '');
        $uid = intval($data['uid']);

        if (strstr($data['Avatar'], 'http://') || strstr($data['Avatar'], 'https://')) {
            $save['headimgurl'] = trim($data['Avatar']);
            $save['create_time'] = time();

            if (M('Avatar')->where(array('uid' => $uid))->count()) {
                M('Avatar')->where(array('uid' => $uid))->save($save);
            } else {
                $save['uid'] = $uid;
                M('Avatar')->add($save);
            }

            //清理用户数据缓存
            CleanUser($uid);
            return true;
        }
        
        $pid = D('Core/File')->Savebase64img($data['Avatar'], $uid);
        if($pid === false){
            $this->error = D('Core/File')->getError();
            return false;
        }
        
        if($pid && is_numeric($pid)){
            $Picture = M('Picture')->where(array('id'=>$pid))->find();
            if(empty($Picture)){
                $this->error = '图片数据不存在!';
                return false;
            }
        }
        $ret = $Picture['path'];
        
        if (file_exists('.' . $ret)) {
            $save['status'] = 1;
            $save['is_temp'] = 0;
            $save['headimgurl'] = '/' . $uid . '/' . basename($ret);
            $save['create_time'] = time();

            if (M('Avatar')->where(array('uid' => $uid))->count()) {
                M('Avatar')->where(array('uid' => $uid))->save($save);
            } else {
                $save['uid'] = $uid;
                M('Avatar')->add($save);
            }

            //清理用户数据缓存
            CleanUser($uid);
            return true;
        } else {
            $this->error = '上传错误,图片文件不存在!';
            return false;
        }
    }

    //注册送金币 (只送通过推荐注册的用户,双方)
    function Regmoney($uid=''){
        $uid = intval($uid);
        if(!$uid){
            $this->error = '参数有误!';
            return false;
        }
        
        $userInfo = User($uid);
        if(!$userInfo){
            $this->error = '用户不存在!';
            return false;
        }
        
        $parent = User($userInfo['parent_id']);
        if(!$parent){
            $this->error = '上级推荐人不存在!';
            return false;
        }
        
        
        if($userInfo['reg_money'] > 0){
            $this->error = '上级推荐人奖励已发放!';
            return false;
        }
        
        $reg_money = C('REGMONEY');
        $reg_money = $reg_money?:0;
        
        //给上级发放奖励
        if(D('AccountLog')->addlog($parent['uid'],$reg_money,6,'推荐用户(UID:'.$userInfo['uid'].')注册获得金币'.$reg_money) === true){
            if(D('AccountLog')->addlog($userInfo['uid'],$reg_money,6,'通过上级推荐人(UID:'.$userInfo['uid'].')注册获得金币'.$reg_money) === true){
                $this->where(array('id'=>$userInfo['uid']))->setField('reg_money',$reg_money);
                return true;
            }
        }
        
        $this->error = D('AccountLog')->getError();
        return false;
    }

}
