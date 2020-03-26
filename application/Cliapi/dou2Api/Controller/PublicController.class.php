<?php

/** 不需要登录的基类
 */
namespace Api\Controller;
use Common\Api\GpsApi;
class PublicController extends ApiController {

    function _initialize() {
        parent::_initialize();
        $this->Member = D("Member");
        $this->Ucenter = D('UcenterMember');
        $this->Token = D('UserToken');
        //$this->Friend = D('Friend');
        //$this->Address = D('UserAddress');
    }
    
    /** 会员注册
     */
    public function Reg() {
        $data = I('post.', '', 'text');
        
        if (!$data['mobile'])
            $this->ajaxRet(array('info' => '手机号必填'));
        
        if (!$data['sms_code'])
            $this->ajaxRet(array('info' => '请填写手机验证码'));

        if (!D('Verify')->checkVerify($data['mobile'], 'reg', $data['sms_code'])) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
        }
        $member = M('ucenter_member')
            ->alias('um')
            ->field('um.id,m.nickname')
            ->join('__MEMBER__ m ON m.uid = um.id')
            ->where(array('um.username'=>$data['mobile']))
            ->find();
        // 判断是否完善资料
        if ($member && !$member['nickname']){
            // 未完善资料，直接返回参数
            D('Verify')->delVerify($data['mobile']); //删除短信
            $this->Ucenter->login($member['id']); //登陆
            $member['token'] = M('UserToken')->where(array('uid'=>$member['id']))->getField('token');
            $this->ajaxRet(array('status' => 1, 'info' => '注册成功', 'data' => $member));
        }
        $uid = $this->Ucenter->register($data['mobile'], $data['password']);

        if ($uid && is_numeric($uid)) {
            D('Verify')->delVerify($data['mobile']); //删除短信
            
            $this->Ucenter->login($uid); //登陆
            //此时用户信息获取不能用User()
            $UserInfo = M('UcenterMember')->where(array('id'=>$uid))->find();
            if(!empty($UserInfo)){
                $UserInfo['token'] = M('UserToken')->where(array('uid'=>$uid))->getField('token');
                $this->ajaxRet(array('status' => 1, 'info' => '注册成功' . $msg, 'data' => $UserInfo));
            }
        }else {
            $this->ajaxRet(array('info' => $this->Ucenter->getMsgError($uid)));
        }
    }

    /**
     * 注册/登陆，第二步检测短信验证码，并注册或登陆，获取token
     */
    public function login(){
        $data = I('post.', '', 'text');

        if (!$data['mobile'])
            $this->ajaxRet(array('info' => '手机号必填'));

        if (!$data['sms_code'])
            $this->ajaxRet(array('info' => '请填写手机验证码'));

        if (!D('Verify')->checkVerify($data['mobile'], 'common', $data['sms_code'])) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
        }
        $member = M('ucenter_member')
            ->alias('um')
            ->field('um.id,m.nickname')
            ->join('__MEMBER__ m ON m.uid = um.id')
            ->where(array('um.username'=>$data['mobile']))
            ->find();
        $return = array(
            'uid'   =>  0,
            'token' =>  '',
            'check_info'  =>  0
        );
        // 判断是否完善资料
        if ($member){
            $return['uid'] = $member['id'];
            $return['token'] = $this->Ucenter->getToken($member['id']);
            D('Verify')->delVerify($data['mobile']); //删除短信
            $this->Ucenter->login($member['id']); //登陆
            $return['token'] = M('UserToken')->where(array('uid'=>$member['id']))->getField('token');
            if (!$member['nickname']){
                // 未完善资料，直接返回参数
                $return['check_info'] = 0;
                $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $return));
            } else {
                $return['check_info'] = 1;
                $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $return));
            }
        } else {
            // 新增用户
            $uid = $this->Ucenter->register($data['mobile']);
            if ($uid && is_numeric($uid)) {
                $return['uid'] = $uid;
                D('Verify')->delVerify($data['mobile']); //删除短信
                $this->Ucenter->login($uid); //登陆
                $return['token'] = M('UserToken')->where(array('uid'=>$uid))->getField('token');
                $return['check_info'] = 0;
                $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $return));
            }else {
                $this->ajaxRet(array('info' => $this->Ucenter->getMsgError($uid)));
            }
        }
    }

    /**
     * 找回密码
     */
    public function change_pwd()
    {
        $data = I('post.', '', 'text');
        if (!$data['mobile']){
            $this->ajaxRet(array('info' => '手机号必填!'));
        }
        if(!checkPhone($data['mobile'])){
            $this->ajaxRet(array('info' => '手机号码格式错误!'));
        }
        if (empty($data['password'])){
            $this->ajaxRet(array('info' => '请填写密码!'));
        }
        if (!$data['sms_code'])
            $this->ajaxRet(array('info' => '请填写手机验证码'));

        if (!D('Verify')->checkVerify($data['mobile'], 'pwd', $data['sms_code'])) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
        }
        $member = M('ucenter_member')->where(array('username'=>$data['mobile']))->find();
        if (!$member){
            $this->ajaxRet(array('info' => '该手机号未注册，请注册!'));
        }
        // 修改密码
        $res = $this->Ucenter->changePwd($member['id'],$data['password']);
        if (!$res) {
            $this->ajaxRet(array('status'=>0,'info' => '密码失败!'));
        }
        //返回成功信息
        CleanUser($member['id'], 'password'); //删除缓存
        D('user_token')->where('uid=' . $member['id'])->delete();
        $this->ajaxRet(array('status'=>1,'info' => '密码已修改!'));
    }

    /** 
     * 发送验证短信
     * type:reg 注册;pwd 找回密码;common 通用，无校验
     */
    public function SendSms() {
        $data = I('post.', '', 'text');
        if (!$data['mobile']){
            $this->ajaxRet(array('info' => '手机号必填!'));
        }
        
        if(!checkPhone($data['mobile'])){
            $this->ajaxRet(array('info' => '手机号码格式错误!'));
        }

        if ($data['type'] == 'reg'){
            // 注册发送验证码，判断手机是否已注册
            $member = M('ucenter_member')->where(array('username'=>$data['mobile']))->find();
            if ($member){
                $this->ajaxRet(array('info' => '该手机号已经注册!'));
            }
        }

        if ($data['type'] == 'pwd'){
            // 注册发送验证码，判断手机是否已注册
            $member = M('ucenter_member')->where(array('username'=>$data['mobile']))->find();
            if (!$member){
                $this->ajaxRet(array('info' => '该手机号未注册!'));
            }
        }
        
        $send = D('Verify')->addVerify($data['mobile'], $data['type']);

        if ($send === false) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
        } else {
            $this->ajaxRet($send);
        }
    }

    /** 
     * 会员登录
     */
    public function userLogin() {
        $data = I('post.', '', 'text');

        if (empty($data['loginstring'])){
            $this->ajaxRet(array('info' => '手机号,邮箱或都玩号必填!'));
        }
        
        if (empty($data['password'])){
            $this->ajaxRet(array('info' => '请填写密码!'));
        }
        
        $uid = $this->Ucenter->Authlogin($data['loginstring'], $data['password']); //通过账号密码取到uid
        
        if ($uid > 0) {
            $this->Ucenter->login($uid, 1); //自动登录
            
            $UserInfo = User($uid);

            // 获取环信账号
            //$member = M('UcenterMember')->where(array('id'=>$uid))->find();
            //$UserInfo['hx_username'] = md5($uid);
            //$UserInfo['hx_password'] = isset($member['hx_password'])?$member['hx_password']:'';
            $this->ajaxRet(array('status' => 1, 'info' => '登录成功', 'data' => $UserInfo));
        } else {
            $this->ajaxRet(array('info' => $this->Ucenter->getMsgError($uid)));
        }
    }

    /** 
     * 第三方登录
     * $data['type'] 登录注册方式 0:用户名密码 1:微信 2:QQ
     * $data['third_token'] 第三方登录令牌标识
     * 
     */
    public function Thirdlogin(){
        $data = I('post.');
        $data['type'] = intval($data['type']);
        
        if(!$data['type']){
            $this->ajaxRet(array('info' => '登录类型必填!'));
        }
        
        if(empty($data['third_token'])){
            $this->ajaxRet(array('info' => '登录令牌标识必填!'));
        }
        
        $map['type'] = $data['type'];
        $map['third_token'] = $data['third_token'];
        $uid = M('UcenterMember')->where($map)->getField('id');
        
        //用户不存在,开始注册
        if(!$uid){
            $uid = $this->Ucenter->ThirdRegister($data['type'], $data['third_token']);
            if(!$uid){
                $this->ajaxRet(array('info' => D('UcenterMember')->getError()));
            }
        }

        //开始登录
        $this->Ucenter->login($uid); //自动登录
        //此时用户信息获取不能用User()
        $UserInfo = M('UcenterMember')->where(array('id'=>$uid))->find();
        if(!empty($UserInfo)){
            //判断是否完善资料
            if(!M('Member')->where(array('uid'=>$uid))->count()){
                $UserInfo['nickname'] = '';
                $UserInfo['token'] = M('UserToken')->where(array('uid'=>$uid))->getField('token');
                $this->ajaxRet(array('status' => 1, 'info' => '注册成功!,请先完善资料!', 'data' => $UserInfo));
            }else{
                $this->ajaxRet(array('status' => 1, 'info' => '登录成功', 'data' => User($uid)));
            }
        }else{
            $this->ajaxRet(array('info' => '注册失败'));
        }
        
    }


    //获取话题
    function theme(){
        $data = I('post.');
        $map['status'] = 1;
        $map['type'] = $data['type']?:1;
        
        $_list = M('Theme')->where($map)->select();
        
        if(!empty($_list)){
            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$_list));
        }else{
            $this->ajaxRet(array('info'=>'获取失败'));
        }
    }

    //获取话题分类
    function themecat(){
        $map['type'] = I('post.type',1,'intval');//话题类型 1:好玩店话题 2:有趣事话题
        $_list = M('ThemeCategory')->where($map)->select();
        if(!empty($_list)){
            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$_list));
        }else{
            $this->ajaxRet(array('info'=>'获取失败'));
        }
        
    }

    //附近的商家
    public function Near(){
        $data = I('post.');

        //范围
        $data['radius'] = $data['radius']?:500;

        if(empty($data['lng'])){
            $this->ajaxRet(array('info'=>'请传入正确的经度'));
        }

        if(empty($data['lat'])){
            $this->ajaxRet(array('info'=>'请传入正确的纬度'));
        }

        //根据经纬度和半径获得查询条件
        $map = searchByLatAndLng($data['lat'], $data['lng'], $data['radius'], 'returnMap');
        $map['s.lng'] = $map['lng'];
        $map['s.lat'] = $map['lat'];
        unset($map['lng'],$map['lat']);

        $map['is_supplier'] = 1;

        $prefix = C('DB_PREFIX');
        $model = M()->table($prefix.'store')->alias('s')->join($prefix.'ucenter_member as u ON u.id = s.uid')->join($prefix . 'member as m ON m.uid=u.id');

        $res = $this->lists($model, $map, '', array(), 's.*');

        $gpsApi = new GpsApi();

        if(!empty($res)){
            foreach ($res as $value){
                $_data = User($value['uid']);
                $_data['hx_username'] = md5($value['uid']);
                $_data['distance'] = $gpsApi->MapAway($data['lng'].','. $data['lat'], $_data['lng'].','.$_data['lat'], 2)?:0;
                $_data['test'] = $_data['lat'].','.$_data['lng'];
                // 判断是否关注
                $_data['is_guanzhu'] = 0;
                if ($data['uid']){
                    $map2['status'] = 1;
                    $map2['type'] = 2;
                    $map2['relation_uid'] =$value['uid'];
                    $map2['uid'] = $data['uid'];
                    if(M('UserRelation')->where($map2)->count()){
                        $_data['is_guanzhu'] = 1;
                    }
                }

                $_Result[] = $_data;
            }
        }

        $Result['_list'] = $_Result ? $_Result : array();
        $Result['_totalPages'] = $this->_totalPages;//总页数

        //$Result = searchByLatAndLng($data['lat'], $data['lng'], $data['radius']);
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$Result));
    }
    
    //附近的人
    public function Near_bak(){
        $data = I('post.');
        
        //范围
        $data['radius'] = $data['radius']?:500;
        
        if(empty($data['lng'])){
            $this->ajaxRet(array('info'=>'请传入正确的经度'));
        }
        
        if(empty($data['lat'])){
            $this->ajaxRet(array('info'=>'请传入正确的纬度'));
        }
        
        //根据经纬度和半径获得查询条件
        $map = searchByLatAndLng($data['lat'], $data['lng'], $data['radius'], 'returnMap');
        
        if(isset($data['sex']) && $data['sex'] != ''){
            $map['sex'] = intval($data['sex']);
        }
        
        if(isset($data['is_supplier']) && $data['is_supplier']){
            $map['is_supplier'] = intval($data['is_supplier']);
        }
        
        $prefix = C('DB_PREFIX');
        $model = M()->table($prefix.'ucenter_member as u')->join($prefix . 'member as m ON m.uid=u.id');
        
        $res = $this->lists($model, $map, '', array(), 'id');
        
        $gpsApi = new GpsApi();
        
        if(!empty($res)){
            foreach ($res as $value){
                $_data = User($value['id']);
                $_data['hx_username'] = md5($value['id']);
                $_data['distance'] = $gpsApi->MapAway($data['lng'].','. $data['lat'], $_data['lng'].','.$_data['lat'], 2)?:0;
                $_data['test'] = $_data['lat'].','.$_data['lng'];
                $_Result[] = $_data;
            }
        }

        $Result['_list'] = $_Result;
        $Result['_totalPages'] = $this->_totalPages;//总页数
        
        //$Result = searchByLatAndLng($data['lat'], $data['lng'], $data['radius']);
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$Result));
    }
    
    //网红店
    public function Store(){
        $uid = I('post._uid',0,'intval');
        $distance['lng'] = I('post.lng');
        $distance['lng'] = I('post.lng');

        $map['s.region_id'] = I('post.region_id',0,'intval');
        if(!$map['s.region_id']){
            $this->ajaxRet(array('info'=>'区域ID参数错误'));
        }
        
        $map['s.status'] = 1;
        $map['s.is_supplier'] = 1;
        
        $supplier_type = I('post.supplier_type','');
        if(!empty($supplier_type)){
            $map['u.supplier_type'] = $supplier_type;
        }
        
        $prefix = C('DB_PREFIX');
        
        $model = M()->table($prefix.'show as s')->join($prefix . 'ucenter_member as u ON s.uid=u.id');

        $_list = $this->lists($model, $map,'id DESC',array(),'s.*,u.supplier_type');
        
        $Result['_list'] = D('Show')->Showlist($_list, $distance,$uid);

        if ($Result['_list']){
            // 显示是否点赞和踩
            foreach ($Result['_list'] as &$item){
                $show_log = M('show_log')->where(array('show_id'=>$item['id'],'uid'=>$uid,'type'=>'praise','status'=>1))->find();
                if ($show_log){
                    $item['is_praise'] = 1;
                } else {
                    $item['is_praise'] = 0;
                }
                $show_log = M('show_log')->where(array('show_id'=>$item['id'],'uid'=>$uid,'type'=>'cons','status'=>1))->find();
                if ($show_log){
                    $item['is_cons'] = 1;
                } else {
                    $item['is_cons'] = 0;
                }
            }
        }
        
        $Result['_totalPages'] = $this->_totalPages;//总页数
        
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$Result));
    }

    /**
     * 获取推荐会员
     */
    public function recommend_member()
    {
        $type = I('type',0,'intval');//0商家 1普通会员
        $map['um.status'] = 1;
        $map['um.is_recommend'] = 1;
        if ($type == 1){
            // 普通会员
            $map['um.is_supplier'] = 0;
            $total = M('ucenter_member')
                ->alias('um')
                ->field('um.id,um.username,m.nickname,a.headimgurl')
                ->join('__MEMBER__ m ON m.uid = um.id')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = um.id')
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
            $list = M('ucenter_member')
                ->alias('um')
                ->field('um.id,um.username,m.nickname,a.headimgurl')
                ->join('__MEMBER__ m ON m.uid = um.id')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = um.id')
                ->where($map)
                ->order('um.id desc')
                ->limit($limit)
                ->select();
            if (!$list){
                // 无推荐，则随机固定条数
                $ucenter_member = M('ucenter_member')->getTableName();
                $member_list = M('')->query("SELECT * FROM {$ucenter_member} WHERE id >= (SELECT FLOOR( MAX(id) * RAND()) FROM {$ucenter_member} ) ORDER BY id LIMIT 2;");
                $list = array();
                if ($member_list){
                    foreach ($member_list as $value){
                        // 获取头像
                        $avatar = M('avatar')->where(array('uid'=>$value['id']))->getField('headimgurl');
                        if ($avatar){
                            $avatar = '/Uploads/Avatar'.$avatar;
                        } else {
                            $avatar = '';
                        }
                        // 获取昵称
                        $nickname = M('member')->where(array('uid'=>$value['id']))->getField('nickname');
                        if (!$nickname){
                            $nickname = '';
                        }
                        $list[] = array(
                            'id'    =>  $value['id'],
                            'username'  =>  $value['username'],
                            'nickname'  =>  $nickname,
                            'headimgurl'    =>  $avatar
                        );
                    }
                }
                $totalPages = 1;
            }
        } else {
            // 商家
            $map['um.is_supplier'] = 1;
            $total = M('ucenter_member')
                ->alias('um')
                ->field('um.id,um.username,m.nickname,a.headimgurl,st.name supplier_type')
                ->join('__MEMBER__ m ON m.uid = um.id')
                ->join('__SUPPLIER_TYPE__ st ON st.id = um.supplier_type')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = um.id')
                ->where($map)
                ->count();
            $REQUEST = (array) I('request.');
            if (isset($REQUEST['r'])) {
                $listRows = (int)$REQUEST['r'];
            } else {
                $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
            }
            $page = new \Think\Page($total, $listRows, $_REQUEST);
            $page->show();
            $totalPages = $page->totalPages;//总页数
            $limit = $page->firstRow . ',' . $page->listRows;
            $list = M('ucenter_member')
                ->alias('um')
                ->field('um.id,um.username,m.nickname,a.headimgurl,st.name supplier_type')
                ->join('__MEMBER__ m ON m.uid = um.id')
                ->join('__SUPPLIER_TYPE__ st ON st.id = um.supplier_type')
                ->join('LEFT JOIN __AVATAR__ a ON a.uid = um.id')
                ->where($map)
                ->order('um.id desc')
                ->limit($limit)
                ->select();
            if (!$list){
                // 无推荐，则随机固定条数
                $ucenter_member = M('ucenter_member')->getTableName();
                $member_list = M('')->query("SELECT * FROM {$ucenter_member} WHERE is_supplier = 1 AND id >= (SELECT FLOOR( MAX(id) * RAND()) FROM {$ucenter_member} ) ORDER BY id LIMIT 2;");
                $list = array();
                if ($member_list){
                    foreach ($member_list as $value){
                        // 获取头像
                        $avatar = M('avatar')->where(array('uid'=>$value['id']))->getField('headimgurl');
                        if ($avatar){
                            $avatar = '/Uploads/Avatar'.$avatar;
                        } else {
                            $avatar = '';
                        }
                        // 获取昵称
                        $nickname = M('member')->where(array('uid'=>$value['id']))->getField('nickname');
                        if (!$nickname){
                            $nickname = '';
                        }
                        // 获取商户类型
                        $supplier_type = M('supplier_type')->where(array('id'=>$value['supplier_type']))->getField('name');
                        if (!$supplier_type){
                            $supplier_type = '';
                        }
                        $list[] = array(
                            'id'    =>  $value['id'],
                            'username'  =>  $value['username'],
                            'nickname'  =>  $nickname,
                            'headimgurl'    =>  $avatar,
                            'supplier_type' =>  $supplier_type
                        );
                    }
                }
                $totalPages = 1;
            }
        }
        foreach ($list as &$item){
            if ($item['headimgurl']){
                $item['headimgurl'] = '/Uploads/Avatar'.$item['headimgurl'];
            } else {
                $item['headimgurl'] = '';
            }
        }
        $data = array(
            'list'  =>  $list,
            'total_page'    =>  $totalPages,
        );
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$data));
    }
    
    //好玩事
    public function Goodfun(){
        $distance['lat'] = I('post.lat');
        $distance['lng'] = I('post.lng');
        
        $map['s.status'] = 1;
        $map['s.region_id'] = I('post.region_id',0,'intval');
        if(!$map['s.region_id']){
            $this->ajaxRet(array('info'=>'区域ID参数错误'));
        }
        $map['s.is_supplier'] = 0;
        
        $sex = I('post.sex', '');
        if($sex != ''){
            $map['m.sex'] = $sex;
        }
        
        $prefix = C('DB_PREFIX');
        
        $model = M()->table($prefix.'show as s')->join($prefix . 'member as m ON s.uid=m.uid');
        
        $_list = $this->lists($model, $map, 's.id DESC');
        
        $Result['_list'] = D('Show')->Showlist($_list, $distance);
        $Result['_totalPages'] = $this->_totalPages;//总页数
        
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$Result));
    }
    
    //鲜花兑换金币优惠比例
    public function toGold(){
        $youhui = C('FLOWERGOLD');
        if(!empty($youhui)){
            foreach ($youhui as $key=>$value){
                $var['flower'] = $key;
                $var['gold'] = $value;
                $_var[] = $var;
            }
        }
        
        $data['youhui'] = $_var;
        $data['default'] = C('FLOWERGOLD_DEFAULT');
        
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$data));
    }
    
    //金币兑换现金
    public function toMoney(){
        $youhui = C('GOLDMONEY');
        if(!empty($youhui)){
            foreach ($youhui as $key=>$value){
                $var['gold'] = $key;
                $var['money'] = $value;
                $_var[] = $var;
            }
        }
        
        $data['youhui'] = $_var;
        $data['default'] = C('GOLDMONEY_DEFAULT');
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$data));
    }
    
    //支付渠道列表
    function PayTypeList(){
        $this->ajaxRet(array('status'=>1,'info'=>'成功','data'=>D('Ping')->PayTypeList()));
    }
    

    public function test_pic()
    {
        $file = realpath('Uploads/Photowall/4/59f07c2692b2c1025.mp4');
        $name = realpath('Uploads/Photowall/4/59f07c2692b2c1025.jpg');
        $this->getVideoCover($file,1,$name);
    }

    /**
     * 生成视频缩略图
     * @param $file
     * @param int $time
     * @param string $name
     */
    private function getVideoCover($file,$time = 1,$name = '') {
        $str = "ffmpeg -i ".$file." -y -f mjpeg -ss 3 -t ".$time." -s 320x240 ".$name;
        $result = system($str);
    }
    
    //用户搜索
    public function Searchuser(){
        $map = array();
        //0:用户 1:商家 2:全部
        $type = I('type', 2, 'intval');
        if($type != 2){
            $map['u.is_supplier'] = $type;
        }
        
        $keyword = I('keyword');
        if(empty($keyword)){
            $this->ajaxRet(array('info'=>'请输入要搜索的内容!'));
        }
        
//        if(is_numeric($keyword)){
//            if(checkPhone($keyword)){
//                $map['u.username'] = $keyword;
//            }else{
//                $map['u.id'] = intval($keyword);
//            }
//        }else{
//            $map['m.nickname'] = array('like', '%'.$keyword.'%');
//        }
        
        
        $map['u.username|u.id|m.nickname'] = array('like', '%'.$keyword.'%');
        
        $prefix = C('DB_PREFIX');
        $model = M()->table($prefix.'ucenter_member as u')->join($prefix . 'member as m ON m.uid=u.id');
        
        $res = $this->lists($model, $map, '', array(), 'id');
        
        if(!empty($res)){
            foreach ($res as $value){
                $_data = User($value['id']);
                $_data['hx_username'] = md5($value['id']);
                $_Result[] = $_data;
            }
        }

        $Result['_list'] = $_Result;
        $Result['_totalPages'] = $this->_totalPages;//总页数
        
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$Result));
    }
    
    //动态搜索
    public function Searchshow(){
        $keyword = I('keyword');
        if(empty($keyword)){
            $this->ajaxRet(array('info'=>'请输入要搜索的内容!'));
        }
        
        $map['content'] = array('like', '%'.$keyword.'%');
        $_list = $this->lists('Show', $map, 'id DESC');
        
        $Result['_list'] = D('Show')->Showlist($_list);;
        $Result['_totalPages'] = $this->_totalPages;//总页数
        $this->ajaxRet(array('status'=>1, 'info'=>'成功', 'data'=>$Result));
    }

    // 用户协议
    public function agreement(){
        $id = 3;
        $content = M('news')->where(array('id'=>$id))->getField('content');
        $this->assign('content',$content);
        $this->display('agreement');
    }
    
    
}