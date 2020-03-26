<?php

//生成token
function getToken($uid) {
    $user = M('Users')->where(array('id' => intval($uid)))->field('id,user_login,last_login_time')->find();
    if (empty($user)) {
        return false;
    }

    $auth = array(
        'uid' => $user['id'],
        'user_login' => $user['user_login'],
        'last_time' => $user['last_login_time']//+mktime(0,0,0,date('m',time()),date('d',time()),date('Y',time())),
    );
    $string = json_encode($auth);
    $Token = sha1(think_encrypt($string));
    return $Token;
}

//验证sign
function ActionSign($uid, $action = __ACTION__) {

    //获取token
    $token = getToken($uid);
    if (empty($token)) {
        return false;
    }
    $string = md5($uid . strtolower($action) . $token);

    return $string;
}

//验证手机号码
function checkPhone($phone) {
    if (empty($phone)) {
        return false;
    }

    if (preg_match("/^1[34578]{1}\d{9}$/", $phone)) {
        return true;
    } else {
        return false;
    }
}

//验证邮箱
function checkEmail($email_address = '') {
    if (empty($email_address)) {
        return false;
    }
    $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    if (preg_match($pattern, $email_address)) {
        return true;
    } else {
        return false;
    }
}

function getcUrl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回  
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回 
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    $output = curl_exec($ch);
    return $output;
}

/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
    //数据类型检测
    if (!is_array($data)) {
        $data = (array) $data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key 加密密钥
 * @param int    $expire 过期时间 单位 秒
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_encrypt($data, $key = '', $expire = 0) {
    $key = md5(empty($key) ? C('AUTHCODE') : $key);
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
    return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key 加密密钥
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_decrypt($data, $key = '') {
    $key = md5(empty($key) ? C('AUTHCODE') : $key);
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);

    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $x = 0;
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

/**
 * create_rand随机生成一个字符串
 * @param int $length  字符串的长度
 * @param string $type  类型
 * @return string
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */
function create_rand($length = 8, $type = 'all') {
    $num = '0123456789';
    $letter = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($type == 'num') {
        $chars = $num;
    } elseif ($type == 'letter') {
        $chars = $letter;
    } else {
        $chars = $letter . $num;
    }

    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

/**
 * curl_get_headers 获取链接header
 * @param $start 开始数字
 * @return end 最大允许数字
 * @return array 数据集的一维数组
 * @author:
 */
function number_rand($start = 1, $end, $array = array()) {

    if (sizeof($array) == $end) {
        return false;
    }
    $rand = rand(1, $end);
    if (in_array($rand, $array)) {
        return number_rand($start, $end, $array);
    } else {
        return $rand;
    }
}

//收入支出记录
function Coin($id, $action){
    $Result = D('UsersCoinrecord')->addCoin($id, $action);
    if(!$Result){
        return D('UsersCoinrecord')->getError();
    }
    return $Result;
}

//通过ID获取图片或视频的真实地址
function getImgVideoPath($data){
    $data = explode(',', $data);
    if(!empty($data)){
        foreach ($data as $id){
            $id = intval($id);
            if($id){
                $result[] = AddHttp(M('Picture')->where(array('id'=>$id))->getField('path'));
            }
        }
        if(!empty($result)){
            return json_encode($result);
        }
    }
    return null;
}

//获取资源文件
function getImgVideo($data){
    if(empty($data)){
        return null;
    }
    
    $_list = M('Picture')->where(array('id' => array('in', $data)))->field('id,path')->select();
    if(!empty($_list)){
        foreach ($_list as &$value){
            $value['path'] = AddHttp($value['path']);
        }
    }
    
    if(count($_list) == 1){
        return AddHttp($_list[0]['path']);
    }
    
    return $_list;
}

//通过ID获取图片路径
function Imgpath($id){
    if(is_numeric($id) && intval($id)){
        return AddHttp(M('Picture')->where(array('id'=>$id))->getField('path'));
    }else{
       return AddHttp($id);
    }
    
}


//redis链接
function Redis(){
    $REDIS_HOST = C('REDIS_HOST');
    $REDIS_PORT = C('REDIS_PORT');
    $redis = new \Redis();
    $redis->pconnect($REDIS_HOST, $REDIS_PORT);
    return $redis;
}


//红包条件
function RedPack($type, $amount=''){
    $Result = D('LotteryRedpack')->RedPack($type, $amount);
    if(!$Result){
        return D('LotteryRedpack')->getError();
    }
    return $Result;
}

//用户升级
function upLevel($uid){
    D('UserLevel')->upLevel($uid);
}

//用户特权
function userPriv($uid, $Privid){
    return D('UserLevel')->getUserPriv($uid, $Privid);
}
//检测身份证是否合法
function checkCard($id) {
          $id = strtoupper($id);
          $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
          $arr_split = array();
          if(!preg_match($regx, $id)){
            return FALSE;
          }
          //检查15位
          if(15==strlen($id)) {

              $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
              @preg_match($regx, $id, $arr_split);

              //检查生日日期是否正确

              $dtm_birth = "19".$arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];

              if(!strtotime($dtm_birth)){
                return FALSE;
              } else {
                return TRUE;
              }

          } else{
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

            $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

            $sign = 0;

            for ( $i = 0; $i < 17; $i++ ){

                $b = (int) $id{$i};

                $w = $arr_int[$i];

                $sign += $b * $w;

            }

            $n  = $sign % 11;

            $val_num = $arr_ch[$n];

            if ($val_num != substr($id,17, 1)){

                return FALSE;

            }else{

                return TRUE;

            }

          }

}
//检测手机号是否合法性
function Verify_Phone($Phone = null){
     /**
     * 移动：134、135、136、137、138、139、150、151、152、157、158、159、182、183、184、187、188、178(4G)、147(上网卡);
     * 联通：130、131、132、155、156、185、186、176(4G)、145(上网卡);
     * 电信：133、153、180、181、189 、177(4G);
     * 卫星通信：1349;
     * 虚拟运营商：170;
     * 130、131、132、133、134、135、136、137、138、139
     * 145、147
     * 150、151、152、153、155、156、157、158、159
     * 170、176、177、178
     * 180、181、182、183、184、185、186、187、188、189
     */
    $ret = false;
    //判断是否有值
    if($Phone){
        $Phone_preg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';
        //判断是否是正确手机号
        if(preg_match($Phone_preg,$Phone)){
            $ret = true;
        }
    }
    return $ret; 
}
//计算两个时间差
function times($startdate,$enddate){
  $hour=floor((strtotime($enddate)-strtotime($startdate))%86400/3600);
  if($hour>=24){
    //计算天数
    $date=floor((strtotime($enddate)-strtotime($startdate))/86400);
    return $date."天前发布";
  }
  if($hour <= 0){
    $minute=floor((strtotime($enddate)-strtotime($startdate))%86400/60);
    if($minute <= 0){
      $second=floor((strtotime($enddate)-strtotime($startdate))%86400%60);
      return $second.'秒前发布';
    }
    return $minute.'分钟前发布';
  }
  return $hour."小时前发布"; 
}

function AddHttp($url){
    if(stristr($url,'http://') || stristr($url,'https://')){
        return $url;
    }else{
        return APP_URL.$url;
    }
}

//验证身份证号的格式
function isCardNo($card){

// 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X
   $preg = "/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/";
    if(preg_match($preg,$card)){
        return true;
    }else{
        return false;
    }
}

//获取默认头像
function getDefaultHead(){
    $sconfig = M("options")->where("option_name='configpub'")->getField("option_value");
    $sconfig = json_decode($sconfig, true);
    return Imgpath($sconfig['head_default_pic']);
}   