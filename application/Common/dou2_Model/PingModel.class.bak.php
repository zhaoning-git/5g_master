<?php

/**
 * @author:飞月 
 */

namespace Common\Model;

use Think\Model;

class PingModel extends Model {

    public function __construct() {
        //导入Ping++ 接口文件
        Vendor('Ping.init');
        //\Pingpp\Pingpp::setApiKey('sk_live_cYJodCTCuQgkyTGBtq1io1iy');
        //测试模式
        \Pingpp\Pingpp::setApiKey('sk_test_Ku14eH4OK0u180SOuHC4ubf9');
    }

    //Ping++手机端支付
    /**
     * @$id 订单id 
     * @$channel 交易渠道
     * @$amount  交易金额（按分算）
     * @ client_ip iP地址
     * */
    public function PingPay($id, $amount, $channel) {
        try {
            $red = \Pingpp\Charge::create(
                            array(
                                'order_no' => $id,
                                'amount' => $amount,
                                'app' => array('id' => 'app_PePKq5nH08WLDmnL'),
                                'channel' => $channel,
                                'currency' => 'cny',
                                'client_ip' => $_SERVER["REMOTE_ADDR"],
                                'extra' => array('success_url' => 'http://shop.com/ucenter/index/index.html', 'bfb_login' => false),
                                'subject' => 1,
                                'body' => $id
                            )
            );
            return array("status" => 1, "info" => "支付成功", "data" => json_decode($red, true));
        } catch (\Pingpp\Error\Base $e) {
            header('Status: ' . $e->getHttpStatus());
            $gete = json_decode($e->getHttpBody(), true);
            return array("status" => 0, "info" => $gete['error']['message']);
        }
    }

    /**
     * @@@@@@订单退款@@@@@@Ping++生成新的退款路径
     * $id 退款订单或信息id
     * $amount 退款金额（分）
     * $ping_charge 订单支付时生成的charge（根据他退款）
     *
     * */
    public function PingRefund($id, $amount, $ping_charge) {
        try {
            $ch = \Pingpp\Charge::retrieve($ping_charge);
            $li = $ch->refunds->create(
                    array(
                        'amount' => $amount,
                        'description' => $id
                    )
            );
            $reds = json_decode($li, true);
            $data['pingid'] = $reds['id'];
            $rels = explode(':', $reds['failure_msg']);
            $data['url'] = $rels[1] . ':' . $rels[2]; //Ping++退款路径只有当天有效，超时后应去生成新的路径
            $data['charge'] = $reds['charge'];
            return array('status' => 1, 'data' => $data);
        } catch (\Pingpp\Error\Base $e) {
            header('Status: ' . $e->getHttpStatus());
            $gete = json_decode($e->getHttpBody(), true);
            return array("status" => 0, "info" => $gete['error']['message']);
        }
    }

    //Ping++ PC端支付
    /*
     * @$id 订单id 
     * @$channel 交易渠道  alipay_pc_direct支付宝PC页面支付
     * @$amount  交易金额（按分算）
     */
    public function PcPingPay($id, $amount, $channel = 'alipay_pc_direct', $url = '/Business/Advertprice/pingsucceed') {
        $HTTP_HOST = 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__;
        try {
            $red = \Pingpp\Charge::create(
                            array(
                                'order_no' => $id . 'ad',
                                'amount' => $amount,
                                'app' => array('id' => 'app_PePKq5nH08WLDmnL'),
                                'channel' => $channel,
                                'currency' => 'cny',
                                'extra' => array('success_url' => $HTTP_HOST . $url), //支付成功后处理状态（路径自定义）
                                'client_ip' => $_SERVER["REMOTE_ADDR"], //ip地址
                                'subject' => 3,
                                'body' => $id
                            )
            );
            return array("status" => 1, "info" => "支付成功", "data" => json_decode($red, true));
        } catch (\Pingpp\Error\Base $e) {
            header('Status: ' . $e->getHttpStatus());
            $gete = json_decode($e->getHttpBody(), true);
            return array("status" => 0, "info" => $gete['error']['message']);
        }
    }

}
