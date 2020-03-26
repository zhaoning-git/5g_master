<?php
namespace Cliapi\Controller;

use Think\Controller;

class AlipayController extends Controller
{
    /**
     * 支付宝支付
     */
    public function pay(){
        $order_no = I('order_no');
        $price = I('price');

        //验证订单状态 是否已支付 是否是有效订单
        $order_info = M('order')->where(['order_no'=>$order_no])->find();
        echo '<pre>';print_r($order_info);echo '</pre>';echo '<hr>';
        //判断订单是否已被支付
        if($order_info['pay_time']>0){
            die("订单已支付，请勿重复支付");
        }
        //判断订单是否已被删除
        if($order_info['status']=='delete'){
            die("订单已被删除，无法支付");
        }

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
        $notify_url = "http://api.1809a.zyzyz.top/alipay/notify";   //('商户外网可以访问的异步地址');

        $total = floatval($price);
        $body = '结算';

        // 订单号，示例代码使用时间值作为唯一的订单ID号
        // $out_trade_no = date('YmdHis', time());
        $out_trade_no = $order_no;
        //$out_trade_no = 'IA21528854004511';
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"".$body."\","
            . "\"out_trade_no\": \"".$out_trade_no."\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"".$total."\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        // 注意：这里不需要使用htmlspecialchars进行转义，直接返回即可
        echo  $response;
    }

    /**
     * 支付宝异步通知
     */
    public function notify()
    {
        $p = $_POST;
        $log_str = "\n>>>>>> " .date('Y-m-d H:i:s') . ' '.json_encode($p) . " \n";
        file_put_contents('logs/alipay_notify',$log_str,FILE_APPEND);

        //TODO 验签 更新订单状态
        if ($p['trade_status'] == 'TRADE_SUCCESS' || $p['trade_status'] == 'TRADE_FINISHED') { //处理交易完成或者支付成功的通知
            //更改订单状态
            M('order')->where(array('order_no'=>$p['out_trade_no']))->save(array('pay_time'=>time()));

            //充值金币
            $uid = M('order_duihuan')->field('id')->where(array('order_no'=>$p['out_trade_no']))->find();
            $id = $uid['id'];
            D('UsersCoinrecord')->addCoin($id, 'recharge_gold_coin');


        }
        echo 'success';

    }
    /**
     * 支付宝同步通知
     */
    public function aliReturn()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
    }

    //充值金币
    public function chongzhiGold(){

    }

}