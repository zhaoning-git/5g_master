<?php
/**
 * 订单管理
 */
namespace Cliapi\Controller;

use com\unionpay\acp\sdk\AcpService;
use com\unionpay\acp\sdk\SDKConfig;
use Think\Controller;

class OrderController extends MemberController {
    //微信支付
    public $notify_url = 'http://47.98.97.133/index.php?g=Cliapi&m=Alipay&a=notify';     // 支付回调
    private $wx_url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    /*
    微信支付配置参数
    */
    public $config = [
        'appid' => "wxd5af665b240b75d4", /*微信开放平台上的应用id*/
        'mch_id' => "1500086022",/*微信申请成功之后邮件中的商户id*/
        'api_key' => "7c4a8d09ca3762af61e59520943AB26Q"/*在微信商户平台上自己设定的api密钥 32位*/
    ];

    //充值页面
    function index(){
        $uid = $this->uid;
        $haveGold = M('users')->field('gold_coin')->where(array('id'=>$uid))->find();   //金币余额
        $config = M("options")->where("option_name='SetGoldCoinBiLi'")->getField("option_value");
        $config  = json_decode($config,true);                               //充值比例
        $data['amount'] = $haveGold['gold_coin'];
        $data['bili'] = $config;

        $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$data));

    }


    //去支付
    function add_order(){
        $amount = I('amount');       //充值金额
        $pay_way = I('pay_way');    //充值方式  1是支付宝  2是微信  3是银联

        if (empty($amount)){
            $this->ajaxRet(array('status'=>0,'info'=>'请填写您充值的金额'));
        }
        if (empty($pay_way)){
            $this->ajaxRet(array('status'=>0,'info'=>'请选择支付方式'));
        }

        $info = M('order_duihuan')->field('pay_time')->where(array('uid'=>$this->uid))->select();
        foreach ($info as $k=>$v){
            if($v['pay_time'] == 0){
                $this->ajaxRet(array('status'=>0,'info'=>'您有未支付的订单'));
            }
        }

        $config = M("options")->where("option_name='SetGoldCoinBiLi'")->getField("option_value");
        $config  = json_decode($config,true);

        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $order_no = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        $data = [
            'order_no'=>$order_no,              //订单号
            'uid'=>$this->uid,                  //用户id
            'order_amount'=>$amount,            //交易金额
            'duihuan_gold'=>$config['dui_gold_coin']*$amount,//兑换金币的数量
            'pay_way'=>$pay_way,                //支付方式
            'create_time'=>time(),              //订单创建时间
        ];

        $res = M('order_duihuan')->add($data);
        if($res){
            if($pay_way==1){                //支付宝支付
                $body = '支付宝支付';
                $this->alipay($amount,$body);
            }
            if($pay_way==2){                //微信支付
                $body = '微信支付';
                $this->wxpay($amount,$order_no,$body);
            }
            if($pay_way == 3){              //银联支付
                $body = '银联支付';
                $this->yinpay($amount,$order_no,$body);
            }
        }
    }

    /**
     * 支付宝支付
     */
    public function alipay($price,$body){
        vendor('alipay/aop/AopClient');         //加载支付类
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = "2019012363088491";
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEA5ZHmlaJqdhXoC4iJsTybASbF/WkOFcbe41FwjM9cLGL/cyrFW+7CfNpT+OBH9P/MHxxRiOSCU/SC4xoweHtLm6lndv8ORHe+wFJBRtxMvojpdm1E2Bqb/iTAaY6LA2O2SEUBxzCSm1P1QoVU/h70k3kdrb5EiVcLQmj7cY/3/iuM6apY/Ipftv0hVD0zNIrw2zE8HEcSCS8O7NHVY0mebvkfaFess30veu2rpPRTqbbF7XiMin/J3PjDk/QoHU0YMlnbXeBcq8G4wFrOoEvDdu3jr0bCT9ONK+secPpSTOri/0g97BwX8jPG99LLXVuVvIqD8nH68tzi0QvI4QYLiQIDAQABAoIBADy2XTjtfyJDoN6L+X45/PRNAMeH5QW1VnTfsNhMbp/+fnhCs3cgqabDRrnm9qDAlYcUwitl42G4pHTTFEaItPIx1v8NbQSGdpf+KO1IjbNGGhgLxc0xHFgV+Bzyaak30ZlDRrkbC2qwxYgWIugh36SwvzyXuSBpOL0TbowV+wvokEwqn2v2/dVs1N2XK7F+DSex0668I0gUk68tVUWxjTm6K3dGTPgQDo6IvzU9cZ0dEMHSDZMy5D+TRsoPaZXvDlLi9FlfY/rxbe7702aST8yvOvgSpBB2GneLFJHKBNZwDAB2+P9b8XWTKA64CeZRouN5Tll1LAZ2Ax/mpzDYoBECgYEA+oDjbehTzddA1chztgcl66vkdsXfyklfhHyXDbEMFGhUMCutoQs1JWwabcWf337O/pfMZS3m+8ZqvFUyg+7F+BdIbJxhXTxDJMn4v//Pgjb4AmRm5edQDdgweq0UbEVzTqNhcwX8/OhQjA4PQ/v3I0dPjvPSbKFI1EnKjbPZtm8CgYEA6pttAJsAXZSppo4IGKuB/Xp3b9ac5X5ZvF2punnz/6r1cediMulfwFhVMfe4jgHsH4ArbJYxxm4e3HNOcA1Qh8PYT+kVorPFlLlxrbKoPE1EpAkIrypE0+hLzRwCZWbuAHhz+cZwoD6yhdwdUtJ7LPYzx/4oXeCVoOdpxkL6GYcCgYEAwWjSMAG48qzHekgSTvCl7pgBBVMxvlV4Uip/1ipkUT1cAvU2Uaj9l68nmKmFDmIyH4/EWDpgpPRFZLPDFOo+H5aabIExC3ODw9vNGzC/XqJiSjaK2+cEgCvzAkSwCAh3RrgOfWiaNAqKZhhU4QChh7N/UbgfN5AvUvjGUzIRXjECgYBXVPxCmXCNZWWxxnVlGZMOFKSZUT6ef2ZQJF2mOPeHWNB9UjDP2uf15evw6dIdqsGHCGMhzNdCkoOSdKniNFKh91lyhcW0B0+piU6z5imSKQFAPsyoyAdPCs4M0DxkGujF7BDgDeqm/C/gfIu95PRNTGkCLa5BI/UPLJXHY+9NyQKBgQCJDuWcOHMRH/SS0Wly253vjJCGHLhyphR2pzjt5shCFCVUCAvYZS0miWeQppjKnwqUPoytvnky4379UGlq9dTLSpcpjMU9n0DYvlcdPMqKN+OyMAFErmtd8LQlXyl6hjbK2hUkiwLChWNnGT3BrOH811oKtCBVlpqHQezslUmFeg==';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjeCR0DRYg2yHH9tRbvORae4SP2ZvtFM2uKNC/Q/CagQmPT17LSCa7VuNjomSvdWjQwdUp0+igMJ098I0BC3J3ZamWIwe8JHjyhuFi205S9239chd58gn2kKUVOKHffuYP9LL+hfnLqRS1IM06KSxVRRfIv76bgC2a31GlJUmo095V1UsF0QhzNRfvrh3/FzscNRNDJmxCFho0FOO99QJ4PTfJnUTv2R1Os4MPpvh3/ULl4KxvxIgMj643l0Xuj/pdRYabFB6M7nruNqZWSkIiBBxGGbQQ7yEimP5ePoPP152An3/N2ePrCo7sTc1T5V9IB0JOyYuhqhRIVK39Z/qjQIDAQAB';
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        vendor('alipay/aop/request/AlipayTradeAppPayRequest');      //加载支付类
        $request = new \AlipayTradeAppPayRequest;                   // 这个是手机APP支付
        $notify_url = "http://47.98.97.133/index.php?g=Cliapi&m=Alipay&a=notify";   //('商户外网可以访问的异步地址');
        $return_url = "http://47.98.97.133/index.php?g=Cliapi&m=Alipay&a=aliReturn";   //同步地址

        $total = floatval($price);

        // 订单号，示例代码使用时间值作为唯一的订单ID号
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"".$body."\","
//            . "\"out_trade_no\": \"".$out_trade_no."\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"".$total."\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request->setNotifyUrl($notify_url);
        $request->setReturnUrl($return_url);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        // 注意：这里不需要使用htmlspecialchars进行转义，直接返回即可
        echo  $response;
    }

    //微信支付
    public function wxpay($total_fee,$out_trade_no,$body)
    {
        $notify_url = $this->notify_url;//回调地址
        $total_fee = $total_fee * 100;//单位为分,所以乘100
        $order = $this->getPrePayOrder($body, $out_trade_no, $total_fee, $notify_url);//调用微信支付的方法
        file_put_contents('wx1.txt', json_encode($order), FILE_APPEND);
        echo "<pre>";
        print_r(json_encode($order));
        echo "</pre>";
        if (isset($order['prepay_id'])){//判断返回参数中是否有prepay_id
            $res['order_arr'] = $this->getOrder($order['prepay_id']);//执行二次签名返回参数
            file_put_contents('wx2.txt',json_encode($res['order_arr']),FILE_APPEND);
            if (!empty($res['order_arr'])) {
                $res['code'] = 1;
                $res['msg'] = '验签成功';

            }
        } else {
            $res['code'] = 0;
            $res['msg'] = '验签失败';
        }
        return json_encode($res);
    }

    //银联支付
    public function yinpay($txnAmt,$orderId,$body)        //金额 订单号  描述
    {
        header ( 'Content-type:text/html;charset=utf-8' );
        Vendor('Yunpay.acp_service');

        //前台通知地址
        $frontUrl = "http://47.98.97.133/index.php?g=Cliapi&m=YinLianpay&a=pay_success";
        //后台通知地址
        $backUrl = "http://47.98.97.133/index.php?g=Cliapi&m=YinLianpay&a=notify";
        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => SDKConfig::getSDKConfig()->version,  //版本号
            'encoding' => 'utf-8',                                                              //编码方式
            'txnType' => '01',                                                                  //交易类型
            'txnSubType' => '01',                                                              //交易子类
            'bizType' => '000201',                                                              //业务类型
            'frontUrl' =>  $frontUrl,                                                          //前台通知地址
            'backUrl' =>   $backUrl,                                                          //后台通知地址
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,                              //签名方法
            'channelType' => '08',                                                             //渠道类型，07-PC，08-手机
            'accessType' => '0',                                                                //接入类型
            'currencyCode' => '156',
            // 超过超时时间调查询接口应答origRespCode不是A6或者00的就可以判断为失败。
            'payTimeout' => date('YmdHis', strtotime('+15 minutes'))                         //订单发送时间
        );

        //加入商户参数
        $params['txnAmt'] = $txnAmt*100;
        $params['merId'] = C('UNIONPAY_CONFIG')['merId'];    //商户号
        $params['orderId'] = $orderId;
        $params['txnTime'] = C('UNIONPAY_CONFIG')['txnTime'];

        AcpService::sign($params);
        $uri = SDKConfig::getSDKConfig()->frontTrandUrl;
        $html_form = AcpService::createAutoFormHtml($params,$uri);
        echo $html_form;
    }



    /**
     * @name getPrePayOrder()
     * @desc 获取预支付订单
     * @param  string $attach 各种id，订单id 用户id
     * @param  string $body 订单描述
     * @param  string $out_trade_no 订单号
     * @param  string $total_fee    总金额
     * @param  [type] $notify_url  异步地址
     * @return array  微信接口返回一次签名
     */
    private function getPrePayOrder($body, $out_trade_no, $total_fee, $notify_url)
    {
        $url = $this->wx_url;
        $onoce_str = $this->getRandChar(32);
//        $data['attach'] = $attach;
        $data["appid"] = $this->config["appid"];
        $data["body"] = $body;
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        $data["notify_url"] = $notify_url;
        $data["out_trade_no"] = $out_trade_no;
        $data["spbill_create_ip"] = $this->get_client_ip();
        $data["total_fee"] = $total_fee;
        $data["trade_type"] = "APP";
        $s = $this->getSign($data, false);
        $data["sign"] = $s;

        $xml = $this->arrayToXml($data);
        $response = $this->postXmlCurl($xml, $url);

        //将微信返回的结果xml转成数组
        return $this->xmlToArray($response);
    }


    //执行第二次签名，才能返回给客户端使用
    public function getOrder($prepayId)
    {
        $data["appid"] = $this->config["appid"];
        $data["noncestr"] = $this->getRandChar(32);;
        $data["package"] = "Sign=WXPay";
        $data["partnerid"] = $this->config['mch_id'];
        $data["prepayid"] = $prepayId;
        $data["timestamp"] = time();
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
        return $data;

    }

    /*
     * 生成一次签名
     */
    private function getSign($Obj)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[strtolower($k)] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$this->config['api_key'];
        //签名步骤三：MD5加密,所有字符转为大写
        $result_ = strtoupper(md5($String));
        return $result_;
    }

    //获取指定长度的随机字符串
    private function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }


    //数组转xml
    private function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    //post https请求，CURLOPT_POSTFIELDS xml格式
    private function postXmlCurl($xml,$url,$second=30)
    {
        //初始化curl
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data)
        {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error"."<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /*
        获取当前服务器的IP
    */
    private function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

    //将数组转成uri字符串
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
//        $reqPar;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * XML转为数组
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function xmlToArray($data){
        $arr = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }




}