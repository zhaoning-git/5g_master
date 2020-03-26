<?php 
$aop = new AopClient ();//统一收单线下交易查询
$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->appId = '2018122962717378';
$aop->rsaPrivateKey = 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCelXUsS5SuQcK59rOPDQf4l4doNlzkBvYx5oBbF4xhWE2SxRdzTQk3FwlsR6eUVQMxOCsJbQHdGldncDurfhAuStOVvGA6wK/NUs4kSDKbEH1konP+xQA6b9jltJE7gs88hpi+mpr0AR7S4GRdR+oAdzXV0LEXDbG/YsmfQy0Ys622Il3x/rnjqRCnpe3sH0w+Zr0bI8jGhr0y9vzbBA5olh2BeKhGrl0fn/bmKL5TVLVdvDf85yJfqU4KwfvHTJXPCNXvCRJ+A+wChSh3rrRoESrfPSB5d6R6kYK2uEiYvXKvisFoYUfJKF1zfsolzhQ7AEN5uCEraizEDwtY07FDAgMBAAECggEAYOxp9AuxsJtin3QnpHvLbmV2jfwD5B5ZJICD3KjU1L46k0TAo2jnA/Ohf7t/scoPrGpu61DjfbZWy1KuNY9T5mc3UGIU0nPoPu6oLUavH1UBvpsHmCPhktW/VA17Ykr9zDlba8tkf1Ob+jjJPrXwUB6MGcV2JufoxldjcWKGUnyuWl5rUyYrVHmhYuZaSpTfIOpnoaJYjpEb/OtmAVQXSPwu0kL/yG07Swp7VBA8MUMFkH0EL2Dc8DAv9LIuLwvK/UiSq+TJkLBAwkVcl65q3hhNA8Uw2839vOjYHDWFek9tQ/WCInJQVs1xfBC2aOqfN0RJjTcSqq0SvK2zhnxYyQKBgQDmku2rf8HeKkwZT+6SgFOZu13I+g0BzaOnyrKcjdUkdqc+J7uRYwjulehrQshCEqsnlC5xLJ5sh5xxHfkaYJyGKcfaEZcNAdNWW2HEJ9w+EfSiNKsJwK8Ol0NFl8f8e1FxxTF92MWzatYiCaGGrFiPESsY0tbliJQg40MhKTAXFwKBgQCwEkH/xrn08jGt7GPph7R/wrautSkk9J6ua0tHd4zL/SwYWdXs5XEqjy0vpwKIbLcv+vD+8fI8NmPfPUMP4gnMxhaaoEqVr97LGwrHuG70NuOqBYXOpeyNQQduLfDx4OQuJMDGf34cbiJsih6XbOZu0GWxNzPBzVBTqnFgJcJStQKBgEGG5haEI1OBD8ltxu5Jm/inn/qa3ZFyoJzx5RrK0BUhx1vJrOufio1LexbojsMATTXdTemJ9agATjxzeTS+2hziyNI3h4cBys7S/5Dghx34ul5Su3z1DosF/+9KOGKPpVDA6tTKAz1/EkXwGMCOQEjAZ1ARs1LeIsLJ82z8KJeXAoGAI5YtHMNPxoBFJkAHGaZCByiVv84B2ORQRjSNbI5vOLOc+/b8U/sz2kRB/8Wjr2s5w0rW9tB0A1OC8BXzvfwTuv22h94p9QMs5qQ6k49lbZrCfRTMrK3Al/QyykQi1OB9fg4ToIsYNCrWblWkRTrPgEpCo4TiwyL6P07Nx38aPZkCgYAbtgZ7Jk86Bfn75nuAWXWJE1h+qDhr32SZmjESSZJ+NjHzweZqljp3sepBQOV22YwCSc/n6l5kEgLMj0SCV701s3sYRY2FXNw6/5Myz8v3/xt1nwXEGA8IE3Qz7tVxAv9JCuMDGBglIJeim6ISg1k5Whj4ZFRrVke48qFe3acxxg==' ;
$aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnpV1LEuUrkHCufazjw0H+JeHaDZc5Ab2MeaAWxeMYVhNksUXc00JNxcJbEenlFUDMTgrCW0B3RpXZ3A7q34QLkrTlbxgOsCvzVLOJEgymxB9ZKJz/sUAOm/Y5bSRO4LPPIaYvpqa9AEe0uBkXUfqAHc11dCxFw2xv2LJn0MtGLOttiJd8f6546kQp6Xt7B9MPma9GyPIxoa9Mvb82wQOaJYdgXioRq5dH5/25ii+U1S1Xbw3/OciX6lOCsH7x0yVzwjV7wkSfgPsAoUod660aBEq3z0geXekepGCtrhImL1yr4rBaGFHyShdc37KJc4UOwBDebghK2osxA8LWNOxQwIDAQAB';
$aop->apiVersion = '1.0';
$aop->signType = 'RSA2';
$aop->postCharset='GBK';
$aop->format='json';
$request = new AlipayTradeQueryRequest ();
$request->setBizContent("{" .
"    \"out_trade_no\":\"20150320010101001\"," .
"    \"trade_no\":\"2014112611001004680 073956707\"" .
"  }");
$result = $aop->execute ( $request); 
 
$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;
if(!empty($resultCode)&&$resultCode == 10000){
echo "成功";
} else {
echo "失败";
}