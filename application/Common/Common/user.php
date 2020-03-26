<?php

    /**
     * 检测用户是否登录
     * @return integer 0-未登录，大于0-当前登录用户ID
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    function is_login() {
        return get_current_userid();
    }

    function get_uid() {
        return is_login();
    }

    /**
     * 检测当前用户是否为管理员
     * @return boolean true-管理员，false-非管理员
     */
    function is_administrator($uid = null) {

        $uid = is_null($uid) ? is_login() : $uid;
        $admin_uids = explode(',', C('USER_ADMINISTRATOR')); //调整验证机制，支持多管理员，用,分隔
        return $uid && (in_array(intval($uid), $admin_uids)); //调整验证机制，支持多管理员，用,分隔
    }

    function is_admin($uid) {
        return is_administrator($uid);
    }

    /**
     * 获取会员全部字段
     * 字段：fields
     * 会员：uid
     * 强制获取最新：new
     */
    function User($uid = null, $fields = true, $new = false) 
    {
        $CacheTime = 60 * 60 * 12;
        $u = $fields;

        if((is_string($uid) && !intval($uid)) || is_array($uid)){
            $fields = $uid;
            if(empty($u)){
                $uid = is_login();
            }elseif(is_numeric($u) && $u){
                $uid = $u;
            }
        }

        //默认获取自己的资料
        $uid = $uid ? $uid : is_login();
        if (!$uid) {
            return false;
        }
S('QueryUser_' . $uid, null);
        
        if ($new === true) {
            S('QueryUser_' . $uid, null);

            //$fields = false时只更新缓存
            if($fields === false){
                return true;
            }
        }
        
        //用来判断用户是否存在
        elseif($fields === false){
            if(M('Users')->where(array('id' => $uid))->count()){
                return true;
            }else{
                return false;
            }
        }

        $UserData = S('QueryUser_' . $uid);

        if (empty($UserData)) {
            $UserData = M('Users')->alias('u')
                    ->join(C('DB_PREFIX') . 'user_token c ON c.uid = u.id', 'left')//Api登录token
                    ->field("u.*, c.uid,c.ip,c.token")
                    ->where(array('u.id'=>$uid))
                    ->find();

            if(empty($UserData)){
                return false;
            }
            
            $UserData['uid'] = $UserData['id'];
            
            //用户认证
            $userVerify = D('UserVerify')->getVerify($UserData['uid']);
            $UserData['is_realname'] = false;
            $UserData['is_bankcard'] = false;
            $UserData['is_facescan'] = false;
            if(!empty($userVerify)){
                foreach ($userVerify as $ver){
                    if($ver['is_verify'] == 1 && $ver['type'] == 'realname'){
                        $UserData['is_realname'] = true;
                    }elseif($ver['is_verify'] == 1 && $ver['type'] == 'bankcard'){
                        $UserData['is_bankcard'] = true;
                    }elseif($ver['is_verify'] == 1 && $ver['type'] == 'facescan'){
                        $UserData['is_facescan'] = true;
                    }
                }
            }
           
            //我添加的好友数量
            $UserData['myFriend'] = M('UserRelation')->where(array('uid'=>$UserData['uid'],'type'=>1))->count();
            //我关注的用户数量
            $UserData['myFans'] = M('UserRelation')->where(array('uid'=>$UserData['uid'],'type'=>2))->count();
            //我的发帖数量
            $UserData['myShows'] = M('Show')->where(array('uid'=>$UserData['uid']))->count();

            //用户头像
            $sconfig = M("options")->where("option_name='configpub'")->getField("option_value");
            $sconfig = json_decode($sconfig, true);
            $head_default_pic = $sconfig['head_default_pic'];
            
            $UserData['avatar'] = empty($UserData['avatar'])?Imgpath($head_default_pic):Imgpath($UserData['avatar']);
            $UserData['avatar_thumb'] = empty($UserData['avatar_thumb'])?Imgpath($UserData['avatar']):Imgpath($UserData['avatar_thumb']);
            
            //用户等级图标
            $medal_img = M('UserLevel')->where(['id'=>$UserData['level']])->getField('medal_img');
            $medal_img = empty($medal_img)?Imgpath('/data/upload/20200228/5e59169209c97.jpg'):Imgpath($medal_img);
            $UserData['level_icon'] = $medal_img;
            $UserData['level_name'] = M('UserLevel')->where(['id'=>$UserData['level']])->getField('name')?:'普通会员';


            unset($UserData['create_time'], $UserData['user_pass'], $UserData['type'], $UserData['wechat_qrcode'], $UserData['mark'], $UserData['wx_pub_subscribe'], $UserData['time'], $UserData['ip'],  $UserData['headimgurl'], $UserData['oauth_token_secret'], $UserData['oauth_token'], $UserData['is_sync']); //删除无用字段
            S('QueryUser_' . $uid, $UserData, $CacheTime);
        }

        if(!empty($UserData)){
            if ($fields === true) {
                return $UserData;
            }
            
            elseif (!is_array($fields) && is_string($fields)) {
                return $UserData[$fields];
            }
            
            elseif (is_array($fields)) {
                foreach ($fields as $k => $v) {
                    $ret[$v] = $UserData[$v];
                }
                return $ret;
            }
        }
    }


    /**
     * 获取虚拟用户
     */
    function virtual_nickname($uid = 0) {
        $name = M('MemberVirtual')->where('uid=' . $uid)->getField('nickname');
        return $name;
    }

    /** 清理用户数据缓存，即时更新User返回结果。
     * @param $uid
     * @param $field
     * @auth 陈一枭
     */
    function CleanUser($uid) {
        if (!$uid)
            return true;
        S('QueryUser_' . $uid, null);
        S('AvatarCache_' . $uid . (32), null);
        S('AvatarCache_' . $uid . (64), null);
        S('AvatarCache_' . $uid . (128), null);
        S('AvatarCache_' . $uid . (256), null);
        S('AvatarCache_' . $uid . (512), null);
    }

    //清理店铺缓存
    function CleanStore($data=array()){
        if(empty($data)){
            return true;
        }
        
        if($data['uid']){
            $Store = D('Store')->getStore($data['uid']);
        }
        
        if($data['id']){
            $Store = D('Store')->getOne($data['id']);
        }
        S('getStore_'.$Store['uid'], NULL);
        S('StoreGetOne_'.$Store['id'], NULL);
    }
    
    function Sex($sex) {
        switch ($sex) {
            case '0' :
                return '保密';
                break;
            case '1' :
                return '男';
                break;
            case '2' :
                return '女';
                break;
        }
    }

    //获取地区名称
    function cityName($regionID){
        return D('District')->getCityName($regionID);
    }
    
    
    
    /**
     * 检测权限
     */
    function CheckPermission($uids) {
        if (is_administrator()) {
            return true;
        }
        if (in_array(is_login(), $uids)) {
            return true;
        }
        return false;
    }

    function check_auth($rule = '', $except_uid = -1, $type = AuthRuleModel::RULE_URL) 
    {
        if (is_administrator()) {
            return true; //管理员允许访问任何页面
        }
        if ($except_uid != -1) {
            if (!is_array($except_uid)) {
                $except_uid = explode(',', $except_uid);
            }
            if (in_array(is_login(), $except_uid)) {
                return true;
            }
        }
        $rule = empty($rule) ? MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME : $rule;
        // 检测是否有该权限
        if (!M('auth_rule')->where(array('name' => $rule, 'status' => 1))->find()) {
            return false;
        }
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \Think\Auth();
        }
        if (!$Auth->check($rule, get_uid(), $type)) {
            return false;
        }
        return true;
    }

    function check_username(&$username, &$email, &$mobile, &$type = 0) 
    {
        if ($type) {
            switch ($type) {
                case 2:
                    $email = $username;
                    $username = '';
                    $mobile = '';
                    $type = 2;
                    break;
                case 3:
                    $mobile = $username;
                    $username = '';
                    $email = '';
                    $type = 3;
                    break;
                default :
                    $mobile = '';
                    $email = '';
                    $type = 1;
                    break;
            }
        } else {
            $check_email = preg_match("/[a-z0-9_\-\.]+@([a-z0-9_\-]+?\.)+[a-z]{2,3}/i", $username, $match_email);
            $check_mobile = preg_match("/^(1[0-9])[0-9]{9}$/", $username, $match_mobile);
            if ($check_email) {
                $email = $username;
                $username = '';
                $mobile = '';
                $type = 2;
            } elseif ($check_mobile) {
                $mobile = $username;
                $username = '';
                $email = '';
                $type = 3;
            } else {
                $mobile = '';
                $email = '';
                $type = 1;
            }
        }
        return true;
    }

    /**
     * check_reg_type  验证注册格式是否开启
     * @param $type
     * @return bool
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    function check_reg_type($type) {
        $t[1] = $t['username'] = 'username';
        $t[2] = $t['email'] = 'email';
        $t[3] = $t['mobile'] = 'mobile';

        $switch = modC('REG_SWITCH', '', 'USERCONFIG');
        if ($switch) {
            $switch = explode(',', $switch);
            if (in_array($t[$type], $switch)) {
                return true;
            }
        }
        return false;
    }

    /**
     * check_login_type  验证登录提示信息是否开启
     * @param $type
     * @return bool
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    function check_login_type($type) {
        $t[1] = $t['username'] = 'username';
        $t[2] = $t['email'] = 'email';
        $t[3] = $t['mobile'] = 'mobile';

        $switch = modC('LOGIN_SWITCH', 'username', 'USERCONFIG');
        if ($switch) {
            $switch = explode(',', $switch);
            if (in_array($t[$type], $switch)) {
                return true;
            }
        }
        return false;
    }

    /**
     * set_user_status   设置用户状态
     * @param $uid
     * @param $status
     * @return bool
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    function set_user_status($uid, $status) {
        D('Member')->where(array('uid' => $uid))->setField('status', $status);
        UCenterMember()->where(array('id' => $uid))->setField('status', $status);
        return true;
    }

    /**
     * set_users_status   批量设置用户状态
     * @param $map
     * @param $status
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    function set_users_status($map, $status) {
        D('Member')->where($map)->setField('status', $status);
        UCenterMember()->where($map)->setField('status', $status);
        return true;
    }

    /**
     * check_step_can_skip  判断注册步骤是否可跳过
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    function check_step_can_skip($step) {
        $skip = modC('REG_CAN_SKIP', '', 'USERCONFIG');
        $skip = explode(',', $skip);
        if (in_array($step, $skip)) {
            return true;
        }
        return false;
    }

    function check_and_add($args) {
        $Member = D('Member');
        $uid = $args['uid'];

        $check = $Member->find($uid);
        if (!$check) {
            $args['status'] = 1;
            $Member->add($args);
        }
        return true;
    }

    function think_ucenter_md5($str) {
        return sp_password($str);
    }

    /**
     * 系统加密方法
     * @param string $data 要加密的字符串
     * @param string $key 加密密钥
     * @param int $expire 过期时间 (单位:秒)
     * @return string
     */
    function think_ucenter_encrypt($data, $key, $expire = 0) {
        $key = md5($key);
        $data = base64_encode($data);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l)
                $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }
        $str = sprintf('%010d', $expire ? $expire + time() : 0);
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
        }
        return str_replace('=', '', base64_encode($str));
    }

    /**
     * 系统解密方法
     * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
     * @param string $key 加密密钥
     * @return string
     */
    function think_ucenter_decrypt($data, $key) {
        $key = md5($key);
        $x = 0;
        $data = base64_decode($data);
        $expire = substr($data, 0, 10);
        $data = substr($data, 10);
        if ($expire > 0 && $expire < time()) {
            return '';
        }
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l)
                $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return base64_decode($str);
    }

    //增加用户可用资金
    function addMoney($uid,$money,$desc='资金记录'){
        $userInfo = User(intval($uid));
        if(!$userInfo){
            return '用户不存在';
        }
        
        $data['user_money'] = array('exp',"user_money+$money");
        if(M('Users')->where(array('user_id'=>$userInfo['id']))->save($data)){
            accountLog($userInfo['id'],$money,$desc);
            CleanUser($uid);
            return true;
        }else{
            return M('Users')->getDbError();
        }
    }
    
    //增加商家返款营业额
    function addSuppliersMoney($Suppliers_id,$money,$desc='商家营业额返款',$change_type=98){
        $Supplier = D('UserSupplier')->getOne($Suppliers_id);
        if(!$Supplier){
            return D('UserSupplier')->getError();
        }
        
        $money = floatval($money);
        
        $data['suppliers_money'] = array('exp',"suppliers_money+$money");
        $data['last_update_time'] = time();
        if(M('UserSupplier')->where(array('id'=>$Supplier['id']))->save($data)){
            accountLog($Supplier['uid'],$money,$desc,$change_type);
            CleanUser($Supplier['uid']);
            S('UserSupplierInfo_'.$Supplier['id']);
            return true;
        }else{
            return M('UserSupplier')->getDbError();
        }
    }
    
    
    
    //用户资金记录
    function accountLog($uid,$money,$desc='资金记录',$change_type=99){
        if(User($uid,'suppliers_id')){
            $use_supplier_money = M('UserSupplier')->where(array('uid'=>$uid))->getField('suppliers_money');
            $data['use_supplier_money'] = $use_supplier_money;
        }
        
        $data['use_money'] = M('Users')->where(array('user_id'=>intval($uid)))->getField('user_money');
        
        $data['user_money'] = $money;//操作金额
        $data['user_id'] = intval($uid);
        $data['change_time'] = time();
        $data['change_desc'] = $desc;
        $data['change_type'] = $change_type;
        return M('AccountLog')->add($data);
    }
    
    //用户队列账户记录
    function RebateAccountlog($uid,$type,$relevance,$amount,$remark){
        $Account = M('RebateAccount')->where(array('uid'=>$uid))->find();
        if(empty($Account)){
            return false;
        }
        
        unset($Account['id']);
        
        $Account['type'] = intval($type);
        $Account['relevance'] = intval($relevance);
        
        $Account['amount'] = $amount;
        $Account['remark'] = $remark;
        
        M('RebateAccountLog')->add($Account);
        return true;
    }
    
    
    function is_username($username){ 
        $utf8_strlen = utf8_strlen_xxx($username);
        
        $m_strlen = mb_strlen($username);
        
        if($m_strlen > $utf8_strlen){
            if($utf8_strlen == 1){
                $strlen = 1;
            }else{
                $strlen = $m_strlen;
            }
        }else{
            $strlen = $m_strlen;
        }
        
        if (!preg_match("/^[a-zA-Z0-9_\x7f-\xff]{3,60}$/",$username)){
            return false; 
        }elseif(is_numeric($username)){
            return false;
        }
        elseif (20 < $strlen || $strlen < 3){
            return false; 
        } 
        return true;
    }    
    
    // 计算中文字符串长度
    function utf8_strlen_xxx($string = null) {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);
        // 返回单元个数
        return count($match[0]);
    }    
