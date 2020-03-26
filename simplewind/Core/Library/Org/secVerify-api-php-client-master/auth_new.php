<?php
$host = "http://identify.verify.mob.com";
$url = "{$host}/auth/auth/sdkClientFreeLogin";

$appkey = "";
$appSecret = "";
$token = "74549593313083392";
$opToken = "STsid0000001564046011594ry0fzna6bgCQwp00Lh4hCThy10WTpQd2";
$operator = "CMCC";
$md5 = "811251dbb9c56b39e2a2d89702be6524";

function getSign($data, $secret) {
    ksort($data);
    $str = '';
    foreach ($data as $k => $v ) {
        $str .= "$k=$v&";
    }
    $str = substr($str, 0, -1);
    return md5($str.$secret);
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
    'timestamp'=> 1564046825531
);
if ($md5 != '') {
    $post_data['md5'] = $md5;
}
$post_data['sign'] = getSign($post_data, $appSecret);
$jsonStr = json_encode($post_data);
curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonStr);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
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
    $d = openssl_decrypt($json->res, "DES-CBC", $appSecret, 0, "00000000");
//    $des = new CryptDes($appSecret);
//    $json->res = $des->decrypt($json->res);
    $json->res = json_decode($d, true);
}
//显示获得的数据
print_r($json);
