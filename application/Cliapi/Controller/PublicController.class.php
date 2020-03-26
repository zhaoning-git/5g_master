<?php

/** 不需要登录的基类
 */
namespace Cliapi\Controller;
//use Common\Api\GpsApi;
class PublicController extends ApiController {

    function _initialize() {
        parent::_initialize();
    }

    public function League(){
        $data['birthday'] = '1899-01-01';

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
            print_r($data);
    }

    public function dhzb(){
        $PREFIX = C('DB_PREFIX');
        $map['it.logo'] = ['NEQ', ''];
        //$list = M('LibraryTeam')->where($map)->field('id,logo')->select();
        $lsit = M('LibraryLeague as ll')
            ->join($PREFIX.'library_team as it ON it.leagueId = ll.leagueId')//球队
            ->field('ll.leagueId, ll.nameChsShort')
            ->where($map)
            ->select();

        print_r($lsit);
    }





    public function item(){
        $data = D('Match')->retSaichengData(['date'=>'2020-03-07']);
        print_r($data);
    }
    
    public function bifenData(){
        $data = D('Match')->bifenData();
        print_r($data);
    }


    /** 
     * 会员注册
     */
    public function Reg() {
        $data = I('post.');
        
        if (!$data['mobile'])
            $this->ajaxRet(array('info' => '手机号必填'));
        
        if (!$data['sms_code'])
            $this->ajaxRet(array('info' => '请填写手机验证码'));

        if (!D('Verify')->checkVerify($data['mobile'], 'reg', $data['sms_code'])) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
        }
        
        
        $uid = D('Users')->register($data['mobile'], $data['password'], $data['invite_id']);

        if ($uid && $uid > 0 && is_numeric($uid)) {
            //D('Verify')->delVerify($data['mobile']); //删除短信
            
            D('Users')->login($uid); //登陆
            
            //此时用户信息获取不能用User()
            $UserInfo = M('Users')->where(array('id'=>$uid))->find();
            if(!empty($UserInfo)){
                RedPack('newreg', 1);
                if($UserInfo['invite_id']){
                    //会员升级
                    upLevel($UserInfo['invite_id']);
                }
                
                $UserInfo['token'] = M('UserToken')->where(array('uid'=>$uid))->getField('token');
                $UserInfo['uid'] = $UserInfo['id'];
                $this->ajaxRet(array('status' => 1, 'info' => '注册成功' . $msg, 'data' => $UserInfo));
            }
        }
        elseif($uid === false){
            $this->ajaxRet(array('info' => D('Users')->getError()));
        }
        else {
            $this->ajaxRet(array('info' => D('Users')->getMsgError($uid)));
        }
    }

    /** 
     * 会员登录
     */
    public function userLogin() {
        $data = I('post.');

        if (empty($data['loginstring'])){
            $this->ajaxRet(array('info' => '手机号,或登录名必填!'));
        }
        
        $data['type'] = $data['type']?:1;
        
        if (empty($data['password']) && $data['type'] != 2){
            $this->ajaxRet(array('info' => '请填写密码!'));
        }
        
        $uid = D('Users')->Authlogin($data['loginstring'], $data['password'], $data['type']); //通过账号密码取到uid
        
        if ($uid > 0) {
            D('Users')->login($uid, 1); //自动登录
            $UserInfo = User($uid);
            $where['user_id'] = $UserInfo['id'];
            $adver = D('Binding')->where($where)->find();
            //查询用户是否申请广告主  并且审核通过
            $tising = D('advertising')->where($where)->where('is_audit=2')->find();
            if($adver && $tising){
              //是广告主
              $UserInfo['advertising'] = '1';
            }else{
              $UserInfo['advertising'] = '0'; 
            }
            $this->ajaxRet(array('status' => 1, 'info' => '登录成功', 'data' => $UserInfo));
        } else {
            $this->ajaxRet(array('info' => D('Users')->getMsgError($uid)));
        }
    }

    /**
     * 找回密码
     */
    public function ForgetPwd() {

        $data = I('post.');

        if (empty($data['mobile']) && empty($data['email'])){
            $this->ajaxRet(array('status' => 0, 'info' => '手机和邮箱不能同时为空'));
        }
        
        if (!$data['sms_code']){
            $this->ajaxRet(array('status' => 0, 'info' => '请填写验证码'));
        }
        
        if(empty($data['password'])){
            $this->ajaxRet(array('status' => 0, 'info' => '新密码不能为空'));
        }
        elseif(!D('Users')->checkPassword($data['password'])){
            $this->ajaxRet(array('status' => 0, 'info' => D('Users')->getError()));
        }
        
        if(!empty($data['mobile'])){
            $account = $data['mobile'];
            $map['mobile'] = $data['mobile'];
        }else{
            $account = $data['email'];
            $map['user_email'] = $data['email'];
        }
        
        $user = M('Users')->where($map)->find();
        if (!$user){
            $this->ajaxRet(array('status' => 0, 'info' => '该用户不存在'));
        }
        
        if (!D('Verify')->checkVerify($account, 'pwd', $data['sms_code'])) {
            $this->ajaxRet(array('status' => 0, 'info' => D('Verify')->getError()));
        }

        $password = sp_password($data['password']);
        $ret = M('Users')->where(array('id' => $user['id']))->setField('user_pass', $password);

        if ($ret !== false) {
            $this->ajaxRet(array('status' => 1, 'info' => '操作成功，请使用您的新密码登陆吧'));
        } else {
            $this->ajaxRet(array('status' => 0, 'info' => $user['id']));
        }
    }

    
    /** 
     * 发送手机验证短信
     * type  reg:注册 pwd:找回密码 login:登录 bindphone:绑定手机 editmobile:修改手机号码  editemail:修改邮箱
     */
    public function SendSms() {
        return $this->SendYun();
        $data = I('post.');
        if (!$data['mobile']){
            $this->ajaxRet(array('info' => '手机号必填!'));
        }
        
        if($data['type'] == 'editemail'){
            $this->ajaxRet(array('info' => '接口调用错误,请使用发送邮件接口!'));
        }
        
        if(!checkPhone($data['mobile'])){
            $this->ajaxRet(array('info' => '手机号码格式错误!'));
        }

        $member = M('Users')->where(array('mobile'=>$data['mobile']))->find();
        
        if ($data['type'] == 'reg' || $data['type'] == 'bindphone'){
            if ($member){
                $this->ajaxRet(array('info' => '该手机号已经注册!'));
            }
        }
        else{
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
     *云片发送语音验证码
     * apikey 用户唯一标识 mobile 接收的手机号、固话（需加区号）code 验证码，支持 4~6 位阿拉伯数字
     */
    public function SendYun(){

        // $da =  D('Verify')->SendPhones('13326898427','1235');
        // var_dump($da);die;
        $data = I('post.');
        if(empty($data['type'])){
            $this->ajaxRet(array('info' => 'type必填！')); 
        }
       

        if (!$data['mobile']){
            $this->ajaxRet(array('info' => '手机号必填!'));
        }
        if($data['type'] == 'editemail'){
            $this->ajaxRet(array('info' => '接口调用错误,请使用发送邮件接口!'));
        }
        
        if(!checkPhone($data['mobile'])){
            $this->ajaxRet(array('info' => '手机号码格式错误!'));
        }
        $member = M('Users')->where(array('mobile'=>$data['mobile']))->find();

        
        if ($data['type'] == 'reg' || $data['type'] == 'bindphone'){
            
            if ($member){
                $this->ajaxRet(array('info' => '该手机号已经注册!'));
            }
        }
        else{
           
            if (!$member){
                $this->ajaxRet(array('info' => '该手机号未注册!'));
            }
        }
        //发送语音验证
        $send = D('Verify')->senYu($data['mobile'], $data['type']);


        if ($send === false) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
        } else {
            $this->ajaxRet($send);
        }


    }

    /** 
     * 发送邮件验证短信
     * type  reg:注册 pwd:找回密码 login:登录 bindphone:绑定手机 editmobile:修改手机号码  editemail:修改邮箱
     */
    public function SendEmail(){
        $data = I('post.');
        if (!$data['email']){
            $this->ajaxRet(array('info' => '邮箱必填!'));
        }
        
        if($data['type'] == 'editmobile' || $data['type'] == 'bindphone'){
            $this->ajaxRet(array('info' => '接口调用错误,请使用发送手机短信接口!'));
        }
        
        if(!checkEmail($data['email'])){
            $this->ajaxRet(array('info' => '邮箱格式错误!'));
        }
        
        if ($data['type'] != 'editemail'){
            $member = M('Users')->where(array('user_email'=>$data['email']))->find();
            if ($data['type'] == 'reg'){
                if ($member){
                    $this->ajaxRet(array('info' => '该邮箱已经注册!'));
                }
            }
            else{
                if (!$member){
                    $this->ajaxRet(array('info' => '该邮箱未注册!'));
                }
            }
        }
        
        $send = D('Verify')->addVerify($data['email'], $data['type']);

        if ($send === false) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
        } else {
            $this->ajaxRet($send);
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
        $uid = M('Users')->where($map)->getField('id');
        
        //用户不存在,开始注册
        if(!$uid){
            $uid = D('Users')->ThirdRegister($data['type'], $data['third_token']);
            if(!$uid){
                $this->ajaxRet(array('info' => D('Users')->getError()));
            }
        }

        //开始登录
        D('Users')->login($uid); //自动登录
        //此时用户信息获取不能用User()
        $UserInfo = M('Users')->where(array('id'=>$uid))->find();
        if(!empty($UserInfo)){
            $this->ajaxRet(array('status' => 1, 'info' => '登录成功', 'data' => User($uid)));
        }else{
            $this->ajaxRet(array('info' => '注册失败'));
        }
        
    }

    //获取帖子列表
    function getShow(){
        $type = I('post.type', '', 'intval');
        if (!empty($type)) {
            $map['type'] = $type;
        }
        
        $_list = $this->lists('Show', $map, 'addtime DESC');
        if (!empty($_list)) {
            $data['_list'] = D('Show')->Showlist($_list);
            $data['_totalPages'] = $this->_totalPages; //总页数
            $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
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
    
    //获取幻灯片
    public function getSlide(){
        $slide_cid = I('slide_cid', 0, 'intval');
        if(!$slide_cid){
            $this->ajaxRet(array('status'=>0, 'info'=>'请传入正确的幻灯片分类id'));
        }

        $map['slide_status'] = 1;
        $map['slide_cid'] = $slide_cid;
        $slide = M('Slide')->where($map)->select();
        if(!empty($slide)){
            $slide = array_map(function($val){
                $val['slide_pic'] = AddHttp($val['slide_pic']);
                return $val;
            }, $slide);
            $data['_list'] = $slide;
            $this->ajaxRet(array('status'=> 1, 'info'=>'成功', 'data'=>$data));
        }else{
            $this->ajaxRet(array('status'=>0, 'info'=>'幻灯片不存在!'));
        }



    }


    
}