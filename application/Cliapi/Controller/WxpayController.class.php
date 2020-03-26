<?php
namespace Cliapi\Controller;

use Think\Controller;

class WxpayController extends Controller
{
    /**
     * 微信支付
     */
    public $notify_url = 'https://1809zhangyuzhen.comcto.com/wxpay/notify';     // 支付回调
    private $wx_url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    /*
    配置参数
    */
    public $config = [
        'appid' => "wxd5af665b240b75d4", /*微信开放平台上的应用id*/
        'mch_id' => "1500086022",/*微信申请成功之后邮件中的商户id*/
        'api_key' => "7c4a8d09ca3762af61e59520943AB26Q"/*在微信商户平台上自己设定的api密钥 32位*/
    ];


    /**
     * @name pay()
     * @desc 微信支付方法
     * @param string $attach uid
     * @param string  $body 订单描述
     * @param string $out_trade_no 订单号
     * @param string $total_fee 总金额
     * @return json code 1 二次签名返回参数,前台唤起微信
     */
    public function pay()
    {

//        $attach = 4;//用户id
//        $body = '充值余额';//商品名
//        $out_trade_no = 'IB26600905036205';//单号
//        $total_fee = 0.01;//金额

        $attach = $_GET['order_id'];//订单id
        $body = $_GET['goods_name'];//商品名
        $out_trade_no = $_GET['order_no'];//单号
        $total_fee = $_GET['price'];//金额


        $notify_url = $this->notify_url;//回调地址
        $total_fee = $total_fee * 100;//单位为分,所以乘100
        $order = $this->getPrePayOrder($attach, $body, $out_trade_no, $total_fee, $notify_url);//调用微信支付的方法
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
        //var_dump($res);die;
        return json_encode($res);


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
    private function getPrePayOrder( $attach, $body, $out_trade_no, $total_fee, $notify_url)
    {
        $url = $this->wx_url;
        $onoce_str = $this->getRandChar(32);
        $data['attach'] = $attach;
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
        $reqPar;
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


    /**
     * 异步方法
     * @return [type] [description]
     */
    public function notify() {
        //允许从外部加载XML实体(防止XML注入攻击)
        libxml_disable_entity_loader(true);
        $postStr = $this->post_data(); //接收post数据
        echo "<pre>";
        print_r($postStr);
        echo "</pre>";
        $arr = $this->xmlToArray($postStr);
        ksort($arr); // 对数据进行排序
        $str = $this->params_tourl($arr); //对数据拼接成字符串
        $user_sign = strtoupper(md5($str)); //把微信返回的数据进行再次签名
        Log::write(json_encode($arr) , 'log'); //记入日志
        file_put_contents('wx_pay_notice.txt', json_encode($arr), FILE_APPEND);
        //验证签名
        if ($user_sign == $arr['sign']) {
            //验证签名成功  处理商户订单逻辑
            if ($arr['return_code'] == 'SUCCESS' && $arr['result_code'] == 'SUCCESS') {


            }
        } else {
            //签名验证失败   微信会再次访问回调方法
            return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }
    }


    /*
    *@name post_data()
    *@desc 微信接收异步数据方法
    * 微信是用$GLOBALS['HTTP_RAW_POST_DATA'];这个函数接收post数据的
    */
    private function post_data(){
        $receipt = $_REQUEST;
        if($receipt==null){
            $receipt = file_get_contents("php://input");
            if($receipt == null){
                $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
            }
        }
        return $receipt;
    }

    /**
     * 格式化参数格式化成url参数
     */
    private function params_tourl($arr)
    {
        $weipay_key = $this->config['api_key'];//微信key
        $buff = "";
        foreach ($arr as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff.'&key='.$weipay_key;
    }

}