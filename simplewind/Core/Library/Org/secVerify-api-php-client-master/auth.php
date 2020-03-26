<?php
$url = "http://identify.verify.mob.com/auth/auth/sdkClientFreeLogin";

$appkey = "";
$appSecret = "";
$token = "59616292321333248";
$opToken = "opToken";
$operator = "CUCC";
$md5 = "";

function getSign($data, $secret) {
    ksort($data);
    $str = '';
    foreach ($data as $k => $v ) {
        $str .= "$k=$v&";
    }
    $str = substr($str, 0, -1);
    return md5($str.$secret);
}



class CryptDes {
    function __construct($key){
        $this->key = $key; //密钥
        $this->iv = '00000000'; //偏移量
    }
    /*
     * 加密
     */
    function encrypt($input){
        $size = mcrypt_get_block_size(MCRYPT_DES,MCRYPT_MODE_CBC); //3DES加密将MCRYPT_DES改为MCRYPT_3DES
        $input = $this->pkcs5_pad($input, $size); //如果采用PaddingPKCS7，请更换成PaddingPKCS7方法。
        $key = str_pad($this->key,8,'0'); //3DES加密将8改为24
        $td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_CBC, '');
        if( $this->iv == '' )
        {
            $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        }
        else
        {
            $iv = $this->iv;
        }
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);//如需转换二进制可改成 bin2hex 转换
        return $data;
    }
    /*
     * 解密
     */
    function decrypt($encrypted){
        $encrypted = base64_decode($encrypted); //如需转换二进制可改成 bin2hex 转换
        $key = str_pad($this->key,8,'0'); //3DES加密将8改为24
        $td = mcrypt_module_open(MCRYPT_DES,'',MCRYPT_MODE_CBC,'');//3DES加密将MCRYPT_DES改为MCRYPT_3DES
        if( $this->iv == '' )
        {
            $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        }
        else
        {
            $iv = $this->iv;
        }
        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $y=$this->pkcs5_unpad($decrypted);
        return $y;
    }
    function pkcs5_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    function pkcs5_unpad($text){
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
    function PaddingPKCS7($data) {
        $block_size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);//3DES加密将MCRYPT_DES改为MCRYPT_3DES
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char),$padding_char);
        return $data;
    }
}


//初始化
$curl = curl_init();
//设置抓取的url
curl_setopt($curl, CURLOPT_URL, $url);
//设置头文件的信息作为数据流输出
curl_setopt($curl, CURLOPT_HEADER, 0);
//设置获取的信息以文件流的形式返回，而不是直接输出。
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//设置post方式提交
curl_setopt($curl, CURLOPT_POST, 0);
//设置post数据
$post_data = array(
    "appkey" => $appkey,
    "token" => $token,
    "opToken" => $opToken,
    'operator'=> $operator,
    'timestamp'=> time()
);
if ($md5 != '') {
    $post_data['md5'] = $md5;
}
$post_data['sign'] = getSign($post_data, $appSecret);
$jsonStr = json_encode($post_data);
curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonStr);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: ' . strlen($jsonStr)
    )
);
//执行命令
$data = curl_exec($curl);
//关闭URL请求
curl_close($curl);
$json = json_decode($data);

if ($json->status == 200) {
    $des = new CryptDes($appSecret);
    $json->res = $des->decrypt($json->res);
}
//显示获得的数据
print_r($json);
