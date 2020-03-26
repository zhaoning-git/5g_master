<?php 
$aop = new AopClient ();//统一收单交易退款查询
$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->appId = 'your app_id';
$aop->rsaPrivateKey = '请填写开发者私钥去头去尾去回车，一行字符串';
$aop->alipayrsaPublicKey='请填写支付宝公钥，一行字符串';
$aop->apiVersion = '1.0';
$aop->signType = 'RSA2';
$aop->postCharset='GBK';
$aop->format='json';
$request = new AlipayTradeFastpayRefundQueryRequest ();
$request->setBizContent("{" .
		"    \"trade_no\":\"20150320010101001\"," .
		"    \"out_trade_no\":\"2014112611001004680073956707\"," .
		"    \"out_request_no\":\"2014112611001004680073956707\"" .
		"  }");
$result = $aop->execute ( $request);

$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;
if(!empty($resultCode)&&$resultCode == 10000){
	echo "成功";
} else {
	echo "失败";
}