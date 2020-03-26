<?php
namespace Cliapi\Controller;

use Think\Controller;

class HuidiaoController extends Controller
{
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

    /**
     * 微信异步通知
     * @return [type] [description]
     */
    public function wxNotify() {
        //允许从外部加载XML实体(防止XML注入攻击)
        libxml_disable_entity_loader(true);
        $postStr = $this->post_data(); //接收post数据
        $arr = $this->xmlToArray($postStr);
        ksort($arr); // 对数据进行排序
        $str = $this->params_tourl($arr); //对数据拼接成字符串
        $user_sign = strtoupper(md5($str)); //把微信返回的数据进行再次签名

        //日志
        $log_str=date("Y-m-d h:i:s")."\n".$postStr."\n";
        file_put_contents('logs/wx_pay_notice.log',$log_str,FILE_APPEND);

        //验证签名
        if ($user_sign == $arr['sign']) {
            if ($arr['return_code'] == 'SUCCESS' && $arr['result_code'] == 'SUCCESS') {         //验证签名成功  处理商户订单逻辑
                //更改订单状态
                M('order')->where(array('order_no'=>$arr->out_trade_no))->save(array('pay_time'=>strtotime($arr->time_end)));

                //充值金币
                $uid = M('order_duihuan')->field('id')->where(array('order_no'=>$arr['out_trade_no']))->find();
                $id = $uid['id'];
                D('UsersCoinrecord')->addCoin($id, 'recharge_gold_coin');
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