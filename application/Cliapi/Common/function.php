<?php

function get_url() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
}
/**
 *  @desc 获取推拉流地址
 *  @param string $host 协议，如:http、rtmp
 *  @param string $stream 流名,如有则包含 .flv、.m3u8
 *  @param int $type 类型，0表示播流，1表示推流
 */
function PrivateKey($host, $stream, $type) {
     
    $configpri = getConfigPri();
   // $cdn_switch = $configpri['cdn_switch'];
    $cdn_switch = 1;
    //$cdn_switch=3;
    switch ($cdn_switch) {
        case '1':
            $url = getPushUrl($host, $stream, $type);
            break;
        case '2':
            $url = PrivateKey_t($host, $stream, $type);
            break;
        case '3':
            $url = PrivateKey_qn($host, $stream, $type);
            break;
        case '4':
            $url = PrivateKey_ws($host, $stream, $type);
            break;
        case '5':
            $url = PrivateKey_wy($host, $stream, $type);
            break;
        case '6':
            $url = PrivateKey_ady($host, $stream, $type);
            break;
    }


    return $url;
}
/**
 *  @desc 腾讯云推拉流地址
 *  @param string $host 协议，如:http、rtmp
 *  @param string $stream 流名,如有则包含 .flv、.m3u8
 *  @param int $type 类型，0表示播流，1表示推流
 */
function PrivateKey_t($host, $stream, $type) {
    $configpri = getConfigPri();
    $bizid = $configpri['tx_bizid'];
    $push_url_key = $configpri['tx_push_key'];
    $push = $configpri['tx_push'];
    $pull = $configpri['tx_pull'];
    $stream_a = explode('.', $stream);
    $streamKey = $stream_a[0];
    $ext = $stream_a[1];

    //$live_code = $bizid . "_" .$streamKey;      	
    $live_code = $streamKey;
    $now_time = time() + 3 * 60 * 60;
    $txTime = dechex($now_time);

    $txSecret = md5($push_url_key . $live_code . $txTime);
    $safe_url = "&txSecret=" . $txSecret . "&txTime=" . $txTime;

    if ($type == 1) {
        //$push_url = "rtmp://" . $bizid . ".livepush2.myqcloud.com/live/" .  $live_code . "?bizid=" . $bizid . "&record=flv" .$safe_url;	可录像
        $url = "rtmp://{$push}/live/" . $live_code . "?bizid=" . $bizid . "" . $safe_url;
    } else {
        $url = "http://{$pull}/live/" . $live_code . ".flv";
    }

    return $url;
}
/**
 *  @desc 阿里云直播A类鉴权
 *  @param string $host 协议，如:http、rtmp
 *  @param string $stream 流名,如有则包含 .flv、.m3u8
 *  @param int $type 类型，0表示播流，1表示推流
 */
function PrivateKey_al($host, $stream, $type) {
    $configpri = getConfigPri();
    // $push = $configpri['push_url'];
    $push = 'tuiliu.zyzyz.top/';
    $pull = $configpri['pull_url'];
    $key_push = $configpri['auth_key_push'];
    $length_push = $configpri['auth_length_push'];
    $key_pull = $configpri['auth_key_pull'];
    $length_pull = $configpri['auth_length_pull'];
    
    if ($type == 1) {
        $domain = $host . '://' . $push;
        $time = time() + $length_push;
    } else {
        $domain = $host . '://' . $pull;
        $time = time() + $length_pull;
    }
    
   $filename = "/tuiliu.zyzyz.top/" . $stream;

    //模拟
    $key_push = $stream;
    if ($type == 1) {
        if ($key_push != '') {
            $sstring = $filename . "-" . $time . "-0-0-" . $key_push;
            $md5 = md5($sstring);
            $auth_key = "auth_key=" . $time . "-0-0-" . $md5;
        }
        if ($auth_key) {
            $auth_key = '?' . $auth_key;
        }
        $url = $domain . $filename . $auth_key;
    } else {
        if ($key_pull != '') {
            $sstring = $filename . "-" . $time . "-0-0-" . $key_pull;
            $md5 = md5($sstring);
            $auth_key = "auth_key=" . $time . "-0-0-" . $md5;
        }
        if ($auth_key) {
            $auth_key = '?' . $auth_key;
        }
        $url = $domain . $filename . $auth_key;
    }

    return $url;
}
/**
 * 获取推流地址
 * 如果不传key和过期时间，将返回不含防盗链的url
 * @param domain 您的推流域名
 *        stream_id 您用来区别不同推流地址的唯一流ID
 *        key 安全密钥
 *        time 过期时间 sample 2016-11-12 12:00:00
 * @return String url */
  function getPushUrl($host, $stream, $type){
  	$push = 'tuiliu.zyzyz.top';
  	$pull = 'laliu.zyzyz.top';
  	$key = '38nB5nYQIS';//推流
	$keys = 'sdsdvsdsdv';//播流
    $time = time()+1800;
	$AppName = 'playzhan';
    $StreamName = 'test1';
    //推流
  	if ($type == 1) {
       $domain = 'tuiliu.zyzyz.top';
       $strpush = '/'.$AppName.'/'.$StreamName.'-'.$time.'-0-0-'.$key;
       $urls =  "rtmp://".$domain.'/'.$AppName.'/'.$StreamName.'?auth_key='.$time.'-0-0-'.md5($strpush);
       
    }
    if($type == 2){
    	//播流
        $doms = 'laliu.zyzyz.top';
        $rtmpPlayer = '/'.$AppName.'/'.$StreamName.'.flv'.'-'.$time.'-0-0-'.$keys;
 
        $urls = "http://".$doms.'/'.$AppName.'/'.$StreamName.'.flv'.'?auth_key='.$time.'-0-0-'.md5($rtmpPlayer);
        
    }
	
	return $urls;
}
