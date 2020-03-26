<?php
//这个地方要引入下面要用到的类
require_once '../aop/AopClient.php';
require_once '../aop/request/AlipayTradeAppPayRequest.php';
require_once '../aop/request/AlipayTradeWapPayRequest.php';


$price=$_REQUEST['price'];//钱数
$bianhao=$_REQUEST['bianhao'];//订单编号
$body=$_REQUEST['name'];//商品名称
$isinvoice=$_REQUEST['isinvoice'];//发票类型

/*
if($isinvoice==2){
  $price=$price+$price*0.04;
}elseif($isinvoice==3){
  $price=$price+$price*0.17;
}
*/

//$appid = "2018122962717378";
//$alipayrsaPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnpV1LEuUrkHCufazjw0H+JeHaDZc5Ab2MeaAWxeMYVhNksUXc00JNxcJbEenlFUDMTgrCW0B3RpXZ3A7q34QLkrTlbxgOsCvzVLOJEgymxB9ZKJz/sUAOm/Y5bSRO4LPPIaYvpqa9AEe0uBkXUfqAHc11dCxFw2xv2LJn0MtGLOttiJd8f6546kQp6Xt7B9MPma9GyPIxoa9Mvb82wQOaJYdgXioRq5dH5/25ii+U1S1Xbw3/OciX6lOCsH7x0yVzwjV7wkSfgPsAoUod660aBEq3z0geXekepGCtrhImL1yr4rBaGFHyShdc37KJc4UOwBDebghK2osxA8LWNOxQwIDAQAB";
//$rsaPrivateKey = "MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCelXUsS5SuQcK59rOPDQf4l4doNlzkBvYx5oBbF4xhWE2SxRdzTQk3FwlsR6eUVQMxOCsJbQHdGldncDurfhAuStOVvGA6wK/NUs4kSDKbEH1konP+xQA6b9jltJE7gs88hpi+mpr0AR7S4GRdR+oAdzXV0LEXDbG/YsmfQy0Ys622Il3x/rnjqRCnpe3sH0w+Zr0bI8jGhr0y9vzbBA5olh2BeKhGrl0fn/bmKL5TVLVdvDf85yJfqU4KwfvHTJXPCNXvCRJ+A+wChSh3rrRoESrfPSB5d6R6kYK2uEiYvXKvisFoYUfJKF1zfsolzhQ7AEN5uCEraizEDwtY07FDAgMBAAECggEAYOxp9AuxsJtin3QnpHvLbmV2jfwD5B5ZJICD3KjU1L46k0TAo2jnA/Ohf7t/scoPrGpu61DjfbZWy1KuNY9T5mc3UGIU0nPoPu6oLUavH1UBvpsHmCPhktW/VA17Ykr9zDlba8tkf1Ob+jjJPrXwUB6MGcV2JufoxldjcWKGUnyuWl5rUyYrVHmhYuZaSpTfIOpnoaJYjpEb/OtmAVQXSPwu0kL/yG07Swp7VBA8MUMFkH0EL2Dc8DAv9LIuLwvK/UiSq+TJkLBAwkVcl65q3hhNA8Uw2839vOjYHDWFek9tQ/WCInJQVs1xfBC2aOqfN0RJjTcSqq0SvK2zhnxYyQKBgQDmku2rf8HeKkwZT+6SgFOZu13I+g0BzaOnyrKcjdUkdqc+J7uRYwjulehrQshCEqsnlC5xLJ5sh5xxHfkaYJyGKcfaEZcNAdNWW2HEJ9w+EfSiNKsJwK8Ol0NFl8f8e1FxxTF92MWzatYiCaGGrFiPESsY0tbliJQg40MhKTAXFwKBgQCwEkH/xrn08jGt7GPph7R/wrautSkk9J6ua0tHd4zL/SwYWdXs5XEqjy0vpwKIbLcv+vD+8fI8NmPfPUMP4gnMxhaaoEqVr97LGwrHuG70NuOqBYXOpeyNQQduLfDx4OQuJMDGf34cbiJsih6XbOZu0GWxNzPBzVBTqnFgJcJStQKBgEGG5haEI1OBD8ltxu5Jm/inn/qa3ZFyoJzx5RrK0BUhx1vJrOufio1LexbojsMATTXdTemJ9agATjxzeTS+2hziyNI3h4cBys7S/5Dghx34ul5Su3z1DosF/+9KOGKPpVDA6tTKAz1/EkXwGMCOQEjAZ1ARs1LeIsLJ82z8KJeXAoGAI5YtHMNPxoBFJkAHGaZCByiVv84B2ORQRjSNbI5vOLOc+/b8U/sz2kRB/8Wjr2s5w0rW9tB0A1OC8BXzvfwTuv22h94p9QMs5qQ6k49lbZrCfRTMrK3Al/QyykQi1OB9fg4ToIsYNCrWblWkRTrPgEpCo4TiwyL6P07Nx38aPZkCgYAbtgZ7Jk86Bfn75nuAWXWJE1h+qDhr32SZmjESSZJ+NjHzweZqljp3sepBQOV22YwCSc/n6l5kEgLMj0SCV701s3sYRY2FXNw6/5Myz8v3/xt1nwXEGA8IE3Qz7tVxAv9JCuMDGBglIJeim6ISg1k5Whj4ZFRrVke48qFe3acxxg==";

$appid = "2019022863423522";
$alipayrsaPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmO4Xhn6dbUJ6vydwwNUhKd8dzOA/19UPJXr4ysFd2mxDTibjvJGrWaT22nrKqFat+6Ps8gCRINpxA00tQyqFX3vr2Ok6UTFKKVFYQAu6zIMkWvUk4Y+Xfa39hXGLc30iEPFWW6gEq0I4RGF1L7yTzdGsY7dYd8cSzrofhzbbm5TIbYLKenSE3k77wgpImxWUelz1wqGIUMErchgF/5jK3Y8XCq/Bi8WFCPLgh/G80zfgz/bLnm+Iy+hLNJkfsouasPCOSEqL85oH82LI1hwXk2jV/NMC77AsLPJp1Ry8yvJdz+sgEsS/JG0LexMZ3QIdtRaKz9jYrPwWVlca+rkq+wIDAQAB";
$rsaPrivateKey = "MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCs+ShJDp7n8SWsbiCES2sT68qMlNn6WLopxcI9vhvMt9JRixtJFOF+BLOFIAztjnM/Y9OgFsjVla4GVqLzzQU7jU4OjgbNhA/9sAJqxOtusZRLuqDVw5CuSnbStiznguqZHmQBYTj2hQOkzMs/ZPlq7dFmPM196G+C8uW++SuGDjBC08z/jEYJk4BmDdUuNRpOnfo8ijkXJudhJ1jxw1wf7SAkkeRnxl4BLKN5Jcg33VPksSP7XvZlr901cviCMIzqfxRz0vPw/jXFlAKMGToKkXs7Mjyq9W+wAoDqUXFdn7bhWDBEPJD1RZJ5dOeOr0E7EDvyVT0gPePolqD9AhydAgMBAAECggEBAIQtYTtykLts6vz4qqBtgUWXyQ/kLy2+34NMO0IzI8ssLOtjAVa3PhiuPwBbnYVqdWbbQbvzrUSJPJJbYeoroPQA5sT4gvpJEG8rMK2mDMJpsIit9HllGAoXnPUngOjogZzAPGtRf9Xhjwc/95BZ+L5dPMaDcAPmuk+2jRmCDmBA9IeI5yjanltqYSGDAmwb6T30JrkTXwv1cf509fkpa7Hvob1+9SZTFlFZ4ClsT/aqk47sJdkTI9QflvMx0kb2aCT4tUAscxYvVIDGT9Ef9it4d3cGw+807ff3ImsRSNPJqjcy5rAVEr8Z49CUxQWBKkx8X23uPKpXhw3xYvbDKAECgYEA5b2Rv18DeV0tdZcEhhrCFczo0i7vWWIMhkTG+rOgYZTwaQJ0LupqnAcK9yyb+V/7pFNjtx3V+1FvGR0rUKdrpu8bvmWFOq9awRiiQlrxamyQTOdhWskxV0nI8ILiMsKmbQMUGEXesNTTUQ4Ay/862dSVjBaaNpi3ntW0jWKc8bECgYEAwL6GBtRhLTxNhewh01VhPfQ5y2Z1SocIZWz8J18uu0MJX7ch3d3T12IVpG4r8qbWBP0aHnwUCcOXZ+d9HiiWh0s1ujPBHIjyPvCHVw4RsM5ZVauqoXu6ySlY//QC2qITooXyzl4+x/pEmjBwj+TPmYyPEsVAH8IlV7GXucHJSK0CgYEA4taDgwZIgtuamCGNiPAAtM/HDkjjcUjbfvOH2F/lupP5sGWI3A3/R6G4lQeJ1feMmtrveQ3Piz8DyDNB4nf8Vi5/IUZR/vjHTiOJiqUK+37IF/bZ7G789efXrIo+18nT040XmUPbtpPdNonyBXoz3IMHSfPDPqcZno35oSQ+PHECgYBMh9njwFRNh9IoSNgtd4tC9EQ2dRpBW1iEHUAJygteI4IHVUnHNJs2vCVnwq8EjCYSmQTT8eRq9BScFxhg7zDFy6Erq/0TfXTidNLoSBfSIjzqfV0k/WHvGdHS32p6sTwmnhUIx/cZpE+1AWLQX8Pmbh8pDtUlRwtYxJVAFqf7iQKBgQDcqgIVfy4KFKsBm28kYKtojwXQqbU13VrKOtPWVNV/SC6DOXLiCc84aIPg1TA34RLdCV2RMtbjCvjFWGDqYx/3bBXDZ3KjovjZL2iNu/QrSUaEF6dh8RiCpb1PIRWbzrKMRsUSP41AIDO1lV05uxYqJBxcsnEOtaozm/t7JgVxrg==";

$aop = new AopClient;
//生成的唯一订单
$dingdan=$aop->order();
//这里是网关，下面这个网关是正式环境的，等会配置沙箱测试环境的时候会换一个网关
//$aop->gatewayUrl = "https://openapi.alipaydev.com/gateway.do";    //沙箱
$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";       //生产环境
 
//填写appid，在应用的头上面有
$aop->appId = $appid;
//这个地方填写私钥，就是我们在上面用工具生成的私钥，这个私钥必须是和上传到支付宝的公钥匹配，不让，支付宝访问的时候会匹配错误
$aop->rsaPrivateKey = $rsaPrivateKey;
$aop->format = "json";
$aop->charset = "UTF-8";
$aop->signType = "RSA2";
//这个地方的公钥也是一样，必须是上传到支付宝的那个公钥要一样
$aop->alipayrsaPublicKey = $alipayrsaPublicKey;


//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
$request = new AlipayTradeAppPayRequest();  // 这个是手机APP支付
//$request = new AlipayTradeWapPayRequest();    // 这个是手机网站支付


//SDK已经封装掉了公共参数，这里只需要传入业务参数
$bizcontent = "{\"body\":\"商品名称为：$body\","     //这个地方写一些参数，在弹出支付的时候会显示
                . "\"subject\": \"$body\","         //这个可以进行支付的一些描述
                . "\"out_trade_no\": \"$bianhao\"," //订单号，必须是唯一的，等会我会给一个生成订单号的函数，
                . "\"timeout_express\": \"30m\","
                . "\"total_amount\": \"$price\","

                . "\"product_code\":\"QUICK_MSECURITY_PAY\""   // 这个是手机APP支付
                //. "\"product_code\":\"QUICK_WAP_WAY\""           // 这个是手机网站支付

                . "}";
$request->setNotifyUrl("http://qilu10086.com/index.php/Payfee/zfbpaynotify");
$request->setBizContent($bizcontent);


//这里和普通的接口调用不同，使用的是sdkExecute
$response = $aop->sdkExecute($request);  // 这个是手机APP支付
//$request = $aop->pageExecute($request);    // 这个是手机网站支付


echo "{\"alipay\":".json_encode($response)."}";
//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
//echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
