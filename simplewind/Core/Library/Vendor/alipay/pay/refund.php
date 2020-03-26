<?php 
//这个地方要引入下面要用到的类
require_once '../aop/AopClient.php';
require_once '../aop/request/AlipayTradeRefundRequest.php';
$price=$_GET['refund_fee'];//钱数
$out_trade_no=$_GET['bianhao'];//商户订单号
$aop = new AopClient ();//统一收单交易退款接口
$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->appId = '2018122962717378';//appid
//这个地方填写私钥，就是我们在上面用工具生成的私钥，这个私钥必须是和上传到支付宝的公钥匹配，不让，支付宝访问的时候会匹配错误
$aop->rsaPrivateKey = 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCelXUsS5SuQcK59rOPDQf4l4doNlzkBvYx5oBbF4xhWE2SxRdzTQk3FwlsR6eUVQMxOCsJbQHdGldncDurfhAuStOVvGA6wK/NUs4kSDKbEH1konP+xQA6b9jltJE7gs88hpi+mpr0AR7S4GRdR+oAdzXV0LEXDbG/YsmfQy0Ys622Il3x/rnjqRCnpe3sH0w+Zr0bI8jGhr0y9vzbBA5olh2BeKhGrl0fn/bmKL5TVLVdvDf85yJfqU4KwfvHTJXPCNXvCRJ+A+wChSh3rrRoESrfPSB5d6R6kYK2uEiYvXKvisFoYUfJKF1zfsolzhQ7AEN5uCEraizEDwtY07FDAgMBAAECggEAYOxp9AuxsJtin3QnpHvLbmV2jfwD5B5ZJICD3KjU1L46k0TAo2jnA/Ohf7t/scoPrGpu61DjfbZWy1KuNY9T5mc3UGIU0nPoPu6oLUavH1UBvpsHmCPhktW/VA17Ykr9zDlba8tkf1Ob+jjJPrXwUB6MGcV2JufoxldjcWKGUnyuWl5rUyYrVHmhYuZaSpTfIOpnoaJYjpEb/OtmAVQXSPwu0kL/yG07Swp7VBA8MUMFkH0EL2Dc8DAv9LIuLwvK/UiSq+TJkLBAwkVcl65q3hhNA8Uw2839vOjYHDWFek9tQ/WCInJQVs1xfBC2aOqfN0RJjTcSqq0SvK2zhnxYyQKBgQDmku2rf8HeKkwZT+6SgFOZu13I+g0BzaOnyrKcjdUkdqc+J7uRYwjulehrQshCEqsnlC5xLJ5sh5xxHfkaYJyGKcfaEZcNAdNWW2HEJ9w+EfSiNKsJwK8Ol0NFl8f8e1FxxTF92MWzatYiCaGGrFiPESsY0tbliJQg40MhKTAXFwKBgQCwEkH/xrn08jGt7GPph7R/wrautSkk9J6ua0tHd4zL/SwYWdXs5XEqjy0vpwKIbLcv+vD+8fI8NmPfPUMP4gnMxhaaoEqVr97LGwrHuG70NuOqBYXOpeyNQQduLfDx4OQuJMDGf34cbiJsih6XbOZu0GWxNzPBzVBTqnFgJcJStQKBgEGG5haEI1OBD8ltxu5Jm/inn/qa3ZFyoJzx5RrK0BUhx1vJrOufio1LexbojsMATTXdTemJ9agATjxzeTS+2hziyNI3h4cBys7S/5Dghx34ul5Su3z1DosF/+9KOGKPpVDA6tTKAz1/EkXwGMCOQEjAZ1ARs1LeIsLJ82z8KJeXAoGAI5YtHMNPxoBFJkAHGaZCByiVv84B2ORQRjSNbI5vOLOc+/b8U/sz2kRB/8Wjr2s5w0rW9tB0A1OC8BXzvfwTuv22h94p9QMs5qQ6k49lbZrCfRTMrK3Al/QyykQi1OB9fg4ToIsYNCrWblWkRTrPgEpCo4TiwyL6P07Nx38aPZkCgYAbtgZ7Jk86Bfn75nuAWXWJE1h+qDhr32SZmjESSZJ+NjHzweZqljp3sepBQOV22YwCSc/n6l5kEgLMj0SCV701s3sYRY2FXNw6/5Myz8v3/xt1nwXEGA8IE3Qz7tVxAv9JCuMDGBglIJeim6ISg1k5Whj4ZFRrVke48qFe3acxxg==' ;
//这个地方的公钥也是一样，必须是上传到支付宝的那个公钥要一样 支付宝公钥
$aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnpV1LEuUrkHCufazjw0H+JeHaDZc5Ab2MeaAWxeMYVhNksUXc00JNxcJbEenlFUDMTgrCW0B3RpXZ3A7q34QLkrTlbxgOsCvzVLOJEgymxB9ZKJz/sUAOm/Y5bSRO4LPPIaYvpqa9AEe0uBkXUfqAHc11dCxFw2xv2LJn0MtGLOttiJd8f6546kQp6Xt7B9MPma9GyPIxoa9Mvb82wQOaJYdgXioRq5dH5/25ii+U1S1Xbw3/OciX6lOCsH7x0yVzwjV7wkSfgPsAoUod660aBEq3z0geXekepGCtrhImL1yr4rBaGFHyShdc37KJc4UOwBDebghK2osxA8LWNOxQwIDAQAB';
$aop->apiVersion = '1.0';//	调用的接口版本，固定为：1.0
$aop->signType = 'RSA2';//商户生成签名字符串所使用的签名算法类型，目前支持RSA2和RSA，推荐使用RSA2
$aop->postCharset='utf-8';//请求使用的编码格式，如utf-8,gbk,gb2312等
$aop->format='json';//仅支持JSON
$request = new AlipayTradeRefundRequest ();
$request->setBizContent("{".
		"    \"out_trade_no\":\"$out_trade_no\",".//订单支付时传入的商户订单号,不能和 trade_no同时为空。
	  	/* "    \"trade_no\":\"$trade_no\",".//支付宝交易号，和商户订单号不能同时为空  */
		"    \"refund_amount\":$price," .//需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
		"    \"refund_reason\":\"正常退款\"," .//退款的原因说明
		"    \"out_request_no\":\"HZ01RF001\",".
		"    \"operator_id\":\"OP001\"," .
		"    \"store_id\":\"NJ_S_001\"," .
		"    \"terminal_id\":\"NJ_T_001\"" .
		"  }");
$result = $aop->execute ($request);
$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;
if(!empty($resultCode)&&$resultCode == 10000){
	print_r("1");
} else {
	print_r("2");
}