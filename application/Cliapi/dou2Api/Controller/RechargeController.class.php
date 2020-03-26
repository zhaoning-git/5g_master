<?php

/**
 * Date: 17-10-18
 * Time: 下午5:04
 */
namespace Api\Controller;

//充值控制器
class RechargeController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }
    
    function index(){
        $this->ajaxRet(array('info'=>'error'));
    }
    
    
    //充值
    function Recharge(){
        $data = I('post.');
        S('Recharge',$_POST);
        //充值类型 togold:购买金币 verify:认证
        $other['category'] = $data['category'];
        
        //支付渠道
        $other['pay_way'] = $data['pay_way'];
        
        //订单号
        $MakeOrder = D('Recharge')->MakeOrder($this->uid, $data['money'], $other);
        
        //支付方式
        if(D('Ping')->PayTypeList($data['pay_way']) == false){
            $this->ajaxRet(array('info'=>D('Ping')->getError()));
        }
        
        //订单标题,用网站标题代替吧
        $title = C('SITE_TITLE');
        
        //订单详情
        switch ($other['category']){
            case 'togold':
                $body = '购买金币';
                break;
            case 'verify':
                $body = '认证店铺';
                break;
            default :
                $body = '充值';
        }
        
        $Pay = D('Ping')->Pay($MakeOrder['id'],$MakeOrder['order_no'],$data['money'],$data['pay_way'], $title, $body);
        $this->ajaxRet(array('status'=>1,'info'=>'成功','data'=>$Pay));
    }
    
    
}