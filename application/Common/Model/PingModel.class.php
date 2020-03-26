<?php
namespace Common\Model;

use Think\Model;

class PingModel extends Model {

    public function Config() {
        Vendor('Ping.init');
        \Pingpp\Pingpp::setApiKey(C('PING_SECRET_KEY'));
        //\Pingpp\Pingpp::setPrivateKeyPath(dirname(__FILE__) . "/rsa_private_key.pem");
    }

    public function Pay($id, $order_no, $price, $pay_way, $title, $body) {

        if ($data['pay_way'] == 'wx_pub' && !is_wechat()) {
            throw new ApiException('微信支付只能在微信内使用');
        }

        self::Config();

        $client_ip = $_SERVER["REMOTE_ADDR"];

        try {
            $order = \Pingpp\Charge::create(
                            array(
                                'order_no' => $order_no,
                                'amount' => $price * 100,
                                'app' => array('id' => C('PING_APPID')),
                                'channel' => $pay_way, //支付方式
                                'currency' => 'cny', //货币代码
                                'client_ip' => $client_ip == '::1' ? '218.59.88.236' : $client_ip,
                                'extra' => self::PayType($pay_way),
                                'subject' => msubstr($title, 0, 20),
                                'body' => msubstr($body, 0, 20),
                                'metadata' => array('original_id' => $id)//自定义数据
                            )
            );
            return array('status' => 1, 'info' => '已经提交等待处理...', 'data' => json_decode($order, true));
        } catch (\Pingpp\Error\Base $e) {
            header('Status: ' . $e->getHttpStatus());
            $gete = json_decode($e->getHttpBody(), true);
            return array('status' => 0, 'info' => $gete['error']['message']);
        }
    }

    public function WechatRed($id, $type, $order_no, $price, $ArrData) {

        self::Config();
        try {
            $red = \Pingpp\RedEnvelope::create(
                            array(
                                'order_no' => $order_no,
                                'app' => array('id' => Store('store_app_key')),
                                'channel' => 'wx_pub', //红包基于微信公众帐号，所以渠道是 wx_pub
                                'amount' => $price * 100, //金额在 100-20000 之间
                                'currency' => 'cny',
                                'subject' => $ArrData['cause'],
                                'body' => $ArrData['wishing'],
                                'extra' => array(
                                    'nick_name' => $ArrData['store_name'],
                                    'send_name' => $ArrData['store_name']
                                ), //extra 需填入的参数请参阅 API 文档
                                'recipient' => $ArrData['openid'], //指定用户的 open_id
                                'description' => msubstr($ArrData['remark'], 0, 20),
                                'metadata' => array('original_id' => $id, 'type' => $type)
                            )
            );
            return json_decode($red, true);
        } catch (\Pingpp\Error\Base $e) {
            header('Status: ' . $e->getHttpStatus());
            $gete = json_decode($e->getHttpBody(), true);
            return array('failure_msg' => $gete['error']['message']);
        }
    }

    public function WechatTransfer($id, $type, $order_no, $price, $ArrData) {
        self::Config();
        try {
            $Transfer = \Pingpp\Transfer::create(
                            array(
                                'order_no' => $order_no,
                                'app' => array('id' => Store('store_app_key')),
                                'channel' => 'wx_pub',
                                'amount' => $price * 100,
                                'currency' => 'cny',
                                'type' => 'b2c',
                                'recipient' => $ArrData['openid'],
                                'description' => msubstr($ArrData['remark'], 0, 20),
                                'metadata' => array('original_id' => $id, 'type' => $type)
                            )
            );
            return json_decode($Transfer, true);
        } catch (\Pingpp\Error\Base $e) {
            header('Status: ' . $e->getHttpStatus());
            $gete = json_decode($e->getHttpBody(), true);
            return array('failure_msg' => $gete['error']['message']);
        }
    }

    //退款
    public function Refund($id, $pingxx_code, $price, $remark) {
        self::Config();

        try {
            $retrieve = $ch = \Pingpp\Charge::retrieve($pingxx_code);
            $ch->refunds->create(
                    array(
                        'amount' => $price * 100,
                        'description' => $remark,
                        'metadata' => array('original_id' => $id)
                    )
            );

            return self::RefundFind($pingxx_code);
        } catch (\Pingpp\Error\Base $e) {
            header('Status: ' . $e->getHttpStatus());
            $gete = json_decode($e->getHttpBody(), true);
            return array('status' => 0, 'info' => $gete['error']['message']);
        }
    }

    //退款查询
    public function RefundFind($pingxx_code) {

        self::Config();

        sleep(1); //让他睡一会，再去查询是否退款成功

        $Find = \Pingpp\Charge::retrieve($pingxx_code)->refunds->all();
        $red_data = json_decode($Find, true);
        $is_failed = 0;
        foreach ($red_data['data'] as $k => $v) {
            if ($v['status'] == 'failed') {
                $is_failed += 1;
                $failed_info[] = $v['failure_msg'];
            }
        }

        if (!$red_data['data']) {
            return array('status' => 0, 'info' => '退款结果未知，请到商户平台查询');
        } else if ($is_failed >= 1) {
            return array('status' => 0, 'info' => implode("<br>", $failed_info));
        } else if ($red_data['data'] && $is_failed == 0) {
            return array('status' => 1, 'info' => '退款成功');
        } else {
            return array('status' => 0, 'info' => '未知错误，请联系平台技术人员');
        }
    }

    //使用账户支付
    public function SelfPay($id, $body) {

        $info = M('Capitalpool')->where(array('id' => $id))->find();
        if (!$info) {
            return array('status' => 0, 'info' => '订单查询失败');
        }
        $info['transfers_way'] = $info['pay_way'] = 'self';
        $info['remark'] = $body;
        $ret = D('Common/Score')->SetScore($info['uid'], $info['price'], 'out', $info); //去扣款

        if ($ret !== false) {
            M('Capitalpool')->where(array('id' => $id))->save(array('pay_way' => 'self', 'status' => 'success', 'cause' => $title, 'remark' => $body)); //修改主表

            if ($info['category'] == 'order') {
                if (Store('store_sms_content') && Store('store_sms_mobile')) {
                    $content = Store('store_sms_content');
                    $content = str_replace('#nickname#', $info['nickname'], $content);
                    $content = str_replace('#price#', $info['price'], $content);
                    api('YunPian/sendSMS', array(Store('store_sms_mobile'), $content));
                }
                R('Core/PayOver/DealOrder', array('Capitalpool' => $info)); //去处理商品
                return array('status' => 1, 'info' => '支付成功');
            } else if ($info['category'] == 'agents') {
                R('Core/PayOver/DealAgents', array('Capitalpool' => $info)); //去处理商品
                return array('status' => 1, 'info' => '支付代理成功');
            }
        } else {
            return array('status' => 0, 'info' => D('Common/Score')->getError());
        }
    }

    //支付渠道列表
    public function PayTypeList($type = '') {

        $_data = array(
            'alipay' => '支付宝APP支付',
            'wx' => '微信 APP 支付',
        );

        if (!empty($type)) {
            if (!empty($_data[$type])) {
                return true;
            } else {
                $this->error = '不支持的支付渠道!';
                return false;
            }
        }

        foreach ($_data as $key => $value) {
            $v['title'] = $value;
            $v['code'] = $key;
            $data[] = $v;
        }

        return $data;
    }

    public function PayType($type) {
        switch ($type) {
            //支付宝 APP 支付
            case 'alipay':
                $array = array();
                break;
            //支付宝手机网页支付
            case 'alipay_wap':
                $array['success_url'] = 'http://douwan.bzbzz.wang'; //U('Shop/Order/PayOver', false, true, true); //成功同步网址
                $array['cancel_url'] = 'http://douwan.bzbzz.wang'; //U(ACTION_NAME, false, true, true); //取消支付
                break;

            //微信公众号支付
            case 'wx_pub':
                $array['open_id'] = User('openid', is_login(), true); //公众号身份ID
                break;

            //微信 APP 支付
            case 'wx':
                $array = array();
                break;

            //百度钱包
            case 'bfb_wap':
                $array['result_url'] = U('Shop/Order/PayOver', false, true, true); //支付完成的回调地址 
                $array['bfb_login'] = false; //为是否需要登录百度钱包来进行支付
                break;

            //银联
            case 'upmp_wap':
                $array['result_url'] = U('Shop/Order/PayOver', false, true, true); //支付完成的地址
                break;


            default :
                $this->error = '不支持的支付渠道!';
                return false;
        }

        return $array;
    }

}
