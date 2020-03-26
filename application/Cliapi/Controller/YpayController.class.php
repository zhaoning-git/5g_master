<?php
namespace Cliapi\Controller;

use com\unionpay\acp\sdk\AcpService;
use com\unionpay\acp\sdk\LogUtil;
use com\unionpay\acp\sdk\SDKConfig;
use Think\Controller;

class YpayController extends Controller{
    //银联充值操作
    public function pay()
    {
        header ( 'Content-type:text/html;charset=utf-8' );
        Vendor('Yunpay.acp_service');

        //前台通知地址
        $frontUrl = "http://47.98.97.133/index.php?g=Cliapi&m=YinLianpay&a=pay_success";
        //后台通知地址
        $backUrl = "http://47.98.97.133/index.php?g=Cliapi&m=YinLianpay&a=notify";
        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => SDKConfig::getSDKConfig()->version,  //版本号
            'encoding' => 'utf-8',                                                              //编码方式
            'txnType' => '01',                                                                  //交易类型
            'txnSubType' => '01',                                                              //交易子类
            'bizType' => '000201',                                                              //业务类型
            'frontUrl' =>  $frontUrl,                                                          //前台通知地址
            'backUrl' =>   $backUrl,                                                          //后台通知地址
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,                              //签名方法
            'channelType' => '08',                                                             //渠道类型，07-PC，08-手机
            'accessType' => '0',                                                                //接入类型
            'currencyCode' => '156',
            // 超过超时时间调查询接口应答origRespCode不是A6或者00的就可以判断为失败。
            'payTimeout' => date('YmdHis', strtotime('+15 minutes'))                         //订单发送时间
        );
        $txnAmt = I('post.order_amount');    //交易金额
        $orderId = I('post.orderId');  //商户订单号

        //加入商户参数
        $params['txnAmt'] = $txnAmt*100;
        $params['merId'] = C('UNIONPAY_CONFIG')['merId'];    //商户号
        $params['orderId'] = $orderId;
        $params['txnTime'] = C('UNIONPAY_CONFIG')['txnTime'];

        //商品描述，可空
        $body = trim(I('post.WIDbody'));
        $ud = I('uid');
        $data = array(
            'uid'=>$ud,                                    //用户id
            'win_code'=>$orderId,                          //商户订单号
            'winsubject'=>I('post.WIDsubject'),           //订单名称
            'wintotal_amount'=>$txnAmt,                   //付款金额
            'winbody'=>I('post.WIDbody'),                //商品描述
            'state'=>'yl',                              //支付方式
            'status'=>'0',                              //是否支付
            'ordertime'=>time()                         //交易时间
        );
        M("pay_record")->add($data); // 保存交易信息
        AcpService::sign($params);
        $uri = SDKConfig::getSDKConfig()->frontTrandUrl;
        $html_form = AcpService::createAutoFormHtml($params,$uri);

        echo $html_form;
    }


    //银联充值异步
    public function notify()
    {
        Vendor('Yunpay.acp_service');
        $logger = LogUtil::getLogger();
        $logger->LogInfo("receive back notify: " .\com\unionpay\acp\sdk\createLinkString($_POST,false,true));
        if (isset ( $_POST ['signature'] )) {
             echo AcpService::validate($_POST) ? '验签成功' : '验签失败';exit;
            $respCode = I('post.respCode');
            $orderId = I('post.orderId');         // 商户订单号
            $total_amount = I('post.settleAmt'); //订单金额
            $trade_no = I('post.queryId');         // queryId 银联唯一标识一笔交易

            //判断respCode=00、A6后，对涉及资金类的交易，请再发起查询接口查询，确定交易成功后更新数据库。
            if( $respCode=='00' ){
                $this->unionpay($orderId,$total_amount,$trade_no);
            }else{
                $res = $this->confirmpay($orderId,'1');
                if( $res == 'Successful' ){
                    $this->unionpay($orderId,$total_amount,$trade_no);
                } else {
                    echo '交易失败';
                }
            }

        } else {
            echo '签名为空';
        }


    }


    //银联充值
    public function unionpay($orderId,$total_amount,$trade_no)
    {
        $per = M("pay_record")->where('win_code='.$orderId)->find(); //查找该订单
        if( $per['status']=='1' ){
            echo '已充值';
            return;
        }
        $Pay = M("pay");
        // 在Pay模型中启动事务
        $Pay->startTrans();
        // 进行相关的业务逻辑操作
        $res = $Pay->where('uid='.$per['uid'])->setInc('money',$total_amount/100);
        //数据组合
        $data = array(
            'alipay_number'=>$trade_no,                  //银联唯一标识
            'status'=>'1',                              //交易状态
            'paytime'=>time()                          //交易时间
        );
        M("pay_record")->where('win_code='.$orderId)->save($data); // 修改交易信息

        if (!empty($res)){
            // 提交事务
            $Pay->commit();
        }else{
            // 事务回滚
            $Pay->rollback();
        }
    }


    //确定是否充值操作
    public function confirmpay($orderId,$L)
    {
        header ( 'Content-type:text/html;charset=utf-8' );
        Vendor('Yunpay.acp_service');
        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => SDKConfig::getSDKConfig()->version,          //版本号
            'encoding' => 'utf-8',          //编码方式
            'signMethod' => \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->signMethod,          //签名方法
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,          //签名方法
            'txnType' => '00',              //交易类型
            'txnSubType' => '00',          //交易子类
            'bizType' => '000000',          //业务类型
            'accessType' => '0',          //接入类型
            'channelType' => '07',          //渠道类型
        );
        if($L == '0'){
            $time = M("order_pay")->where('win_code = "'.$orderId.'"')->find()['addtime'];
        }else{
            $time = M("pay_record")->where('win_code = "'.$orderId.'"')->find()['ordertime'];
        }
        $params['merId'] = C('Yunpay.merId');       //商户号
        $params['orderId'] = $orderId;               //交易的订单号
        $params['txnTime'] = date('YmdHis',$time); //订单发送时间

        AcpService::sign($params);
        $url = SDKConfig::getSDKConfig()->singleQueryUrl;

        $result_arr = AcpService::post($params,$url);
        if(count($result_arr)<=0) { //没收到200应答的情况
            return 'No200';
        }
        if (!AcpService::validate($result_arr) ){
            return "应答报文验签失败";
        }
        if ($result_arr["respCode"] == "00"){
            if ($result_arr["origRespCode"] == "00"){
                //交易成功
                //TODO
                return "Successful";
            } else if ($result_arr["origRespCode"] == "03"
                || $result_arr["origRespCode"] == "04"
                || $result_arr["origRespCode"] == "05"){
                //后续需发起交易状态查询交易确定交易状态
                //TODO
                return "交易处理中，请稍微查询";
            } else {
                //其他应答码做以失败处理
                //TODO
                return "交易失败：" . $result_arr["origRespMsg"];
            }
        } else if ($result_arr["respCode"] == "03"
            || $result_arr["respCode"] == "04"
            || $result_arr["respCode"] == "05" ){
            //后续需发起交易状态查询交易确定交易状态
            //TODO
            return "处理超时，请稍微查询";
        } else {
            //其他应答码做以失败处理
            //TODO
            return "失败：" . $result_arr["respMsg"];
        }

    }
}