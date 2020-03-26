<?php
namespace Cliapi\Controller;

use Think\Controller;


class YinLianpayController extends Controller
{

    /**
     * 支付配置
     * @var array
     */
    public $config = array();
    /**
     * 支付参数，提交到银联对应接口的所有参数
     * @var array
     */
    public $params = array();

    /*生产支付参数 提交支付 */
    function pay(){
        $this->config = C('UNIONPAY');//从配置里读取
        $config = C('UNIONPAY_CONFIG');
        $config['certId']  = $this->getSignCertId(); //证书ID
        $config['orderId'] = mt_rand(111111111,999999999);//订单号 自定义
        $config['txnAmt']  = I("post.order_amount")*100; //交易金额，单位分
        $this->params = $config;

//     生产支付记录到本地数据库
        $money = I("post.money");
        $user_id = I('uid');
		$OrderId = $config['orderId'];//生成随机订单号
		$pay_type = "银联";//支付方式 1余额 2支付宝
//		$pay_fee = M('handfee')->find(2);
//		if ($pay_fee['type'] == 1){
//			$fee=$pay_fee['rate']*$money;
//        }else {
//			$fee=$pay_fee['fee'];
//
//        }

        //订单表数据
		$order = array(
            "order_id"=>$OrderId,
            "uid"=>$user_id,
            "pay_mode"=>1,
            "pay_channels"=>2,
            "fee"=>$fee,
            "status"=>0,//待审核
            "beizhu"=>"银联在线充值",
            "ent_money"=>$money-$fee,
            "time"=>time(),
            "sub_time"=>time(),
            "pay_money"=>$money,
            "pay_type"=>$pay_type,//1余额支付 2支付宝支付

        );
        echo "<pre>";
        print_r($order);
        echo "</pre>";die;
        //$Ord=M('pay');
        //$Ord->add($order);
        $html = $this->createPostForm();//构建自动提交HTML表单
        echo $html;

    }

    function usernotify(){// 付款后返回商家

    }

    function notify(){//后台通知路径
        /*付款后业务逻辑代码  */
        $orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
        $respCode = $_POST ['respCode']; //判断respCode=00或A6即可认为交易成功
        if ($respCode=='00'||$respCode=='A6'){
            /*通过写入文件的方式记录返回的订单号等 */
            $str = "--------- ".date('Y-m-d H:i:s')." ---------";
            $str .= "orderId:".$orderId."\r\n";
            $str .= "respCode:".$respCode."\r\n";
            $str .= "--------- END -----------"."\r\n";

            file_put_contents('unionpay_notify_log.log', $str);

            /* 以下是支付成功后的数据库操作 请根据需要自行操作

            $order['status']=1;
			$order['check_time']=time();
            M('pay')->where(array('order_id'=>$orderId))->save($order);

            $order_info = M('pay')->where(array('order_id'=>$orderId))->find();
            $log['user_id']=$order_info['uid'];
			$log['user_money']=$order_info['pay_money'];
			$log['change_time']=time();
			$log['desc']="银联在线充值";


            M('account_log')->add($log);
            M('users')->where('user_id='.$order_info['uid'])->setInc('user_money',$order_info['ent_money']);

            */

        }
    }

    /**
     * 验证签名
     * 验签规则：
     * 除signature域之外的所有项目都必须参加验签
     * 根据key值按照字典排序，然后用&拼接key=value形式待验签字符串；
     * 然后对待验签字符串使用sha1算法做摘要；
     * 用银联公钥对摘要和签名信息做验签操作
     * @throws \Exception
     * @return bool
     */
    public function verifySign()
    {
        $publicKey = $this->getVerifyPublicKey();
        $verifyArr = $this->filterBeforSign();

        ksort($verifyArr);
        $verifyStr = $this->arrayToString($verifyArr);
        $verifySha1 = sha1($verifyStr);
        $signature = base64_decode($this->params['signature']);
        $result = openssl_verify($verifySha1, $signature, $publicKey);
        if($result === -1) {

            // throw new \Exception('Verify Error:'.openssl_error_string());
            echo 'Verify Error:'.openssl_error_string();

        }
        return $result === 1 ? true : false;

    }

    /**
     * 取签名证书ID(SN)
     * @return string
     */
    public function getSignCertId()
    {
        return $this->getCertIdPfx($this->config['signCertPath']);
    }

    /**
     * 签名数据
     * 签名规则:
     * 除signature域之外的所有项目都必须参加签名
     * 根据key值按照字典排序，然后用&拼接key=value形式待签名字符串；
     * 然后对待签名字符串使用sha1算法做摘要；
     * 用银联颁发的私钥对摘要做RSA签名操作
     * 签名结果用base64编码后放在signature域
     * @throws \InvalidArgumentException
     * @return multitype|string
     */
    private function sign() {
        $signData = $this->filterBeforSign();
        ksort($signData);
        $signQueryString = $this->arrayToString($signData);
        if($this->params['signMethod'] == 01) {
            //签名之前先用sha1处理
            //echo $signQueryString;exit;
            $datasha1 = sha1($signQueryString);
            $signed = $this->rsaSign($datasha1);
        } else {
            //throw new \InvalidArgumentException('Nonsupport Sign Method');
            echo 'Nonsupport Sign Method';
        }
        return $signed;

    }

    /**
     * 数组转换成字符串
     * @param array $arr
     * @return string
     */
    private function arrayToString($arr)
    {
        $str = '';
        foreach($arr as $key => $value) {
            $str .= $key.'='.$value.'&';

        }
        return substr($str, 0, strlen($str) - 1);

    }

    /**
     * 过滤待签名数据
     * signature域不参加签名
     * @return array
     */
    private function filterBeforSign()
    {
        $tmp = $this->params;
        unset($tmp['signature']);
        return $tmp;

    }

    /**
     * RSA签名数据，并base64编码
     * @param string $data 待签名数据
     * @return mixed
     */
    private function rsaSign($data)

    {
        $privatekey = $this->getSignPrivateKey();
        $result = openssl_sign($data, $signature, $privatekey);
        if($result) {
            return base64_encode($signature);

        }
        return false;

    }

    /**
     * 取.pfx格式证书ID(SN)
     * @return string
     */
    private function getCertIdPfx($path)
    {
        $data = fopen($path);
        $pkcs12certdata = file_get_contents($path);

        openssl_pkcs12_read($pkcs12certdata, $certs, $this->config['signCertPwd']);
        $x509data = $certs['cert'];

        openssl_x509_read($x509data);
        $certdata = openssl_x509_parse($x509data);
        return $certdata['serialNumber'];

    }

    /**
     * 取.cer格式证书ID(SN)
     * @return string
     */
    private function getCertIdCer($path)
    {
        $x509data = file_get_contents($path);
        openssl_x509_read($x509data);
        $certdata = openssl_x509_parse($x509data);
        return $certdata['serialNumber'];
    }

    /**
     * 取签名证书私钥
     * @return resource
     */
    private function getSignPrivateKey()
    {
        $pkcs12 = file_get_contents($this->config['signCertPath']);
        openssl_pkcs12_read($pkcs12, $certs, $this->config['signCertPwd']);
        return $certs['pkey'];
    }

    /**
     * 取验证签名证书
     * @throws \InvalidArgumentException
     * @return string
     */
    private function getVerifyPublicKey()
    {
        //先判断配置的验签证书是否银联返回指定的证书是否一致
        if($this->getCertIdCer($this->config['verifyCertPath']) != $this->params['certId']) {
        // throw new \InvalidArgumentException('Verify sign cert is incorrect');
        echo 'Verify sign cert is incorrect';
    }
        return file_get_contents($this->config['verifyCertPath']);

    }

    /*支付成功后 前台通知地址*/
    public function pay_success(){
        echo "支付成功！";
    }

    /*失败交易前台跳转地址*/
    public function pay_fail(){
        echo "支付失败！";
    }

}