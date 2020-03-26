<?php

namespace Common\Model;

use Think\Model;

/**
 * Class Recharge 创建充值订单
 */
class RechargeModel extends Model {

    /**
     * 生成网站收支记录，返回id和order_no
     * @param  String $type in/out收入与支出 
     * $price 金额 
     * $data 其他信息 category 收支来源类型  pay_way 支付渠道
     */
    public function MakeOrder($uid, $price, $data, $type = 'in') {
        if(!User($uid,false)){
            $this->error = '用户不存在!';
            return false;
        }
        $data['uid'] = $uid;
        $data['status'] = 'wait';
        $data['nickname'] = User($uid,'nickname');
        $data['money'] = $price;
        $data['type'] = $type;
        $data['category'] = $data['category'] ? $data['category'] : 'recharge';
        $data['pay_way'] = $data['pay_way'] ? $data['pay_way'] : 'alipay_wap';
        $data['create_time'] = time();
        $data['md5'] = md5($uid . intval($price));

        $agents_id = User($uid,'agents_id');
        if($agents_id){
            $data['agents_id'] = $agents_id;
        }
        
        $map['uid'] = $uid;
        $map['status'] = 'wait';
        $map['money'] = $price;
        $map['type'] = $type;
        $map['category'] = $data['category'];
        $map['pay_way'] = $data['pay_way'];
        $res = $this->where($map)->getField('id');
        if(empty($res)){
            $res = $this->add($data);
        }
        
        if (!$res) {
            $this->error = '未知原因，订单号生成失败';
            return false;
        }

        $oid = strtoupper($data['category']) . $res . time(); //生成订单号

        $this->where(array('id' => $res))->save(array('order_no' => $oid));

        return array('id' => $res, 'order_no' => $oid);
    }

    /**
     * 商家给用户退款
     * $id capitalpool 表中的主键
     * @author 郑薏玮 <715713881@qq.com>
     */
    public function Refund($id, $price, $way, $remark) {

        if (!$id || !$price || !$way || !$remark) {
            $this->error = '退款参数错误';
            return false;
        }


        $info = $this->where(array('store_id' => Store(), 'id' => op_t($id)))->find();

        if ($info['status'] == 'refund') {
            $this->error = '该订单已经退过款了';
            return false;
        }

        if ($info['status'] != 'success') {
            $this->error = '该订单没有付款成功，不能退款';
            return false;
        }

        //管理员选择退款到账户内或者用户原本就是用账户支付
        if ($way == 'self' || $info['pay_way'] == 'self') {
            $param['transfers_way'] = 'self';
            $param['pay_way'] = 'self';
            $param['category'] = 'refund';
            $param['transaction_no'] = $info['transaction_no'];
            $param['remark'] = $remark;

            $res = D('Common/Score')->SetScore($info['uid'], $price, 'in', $param);

            if ($res !== false) {

                $this->where(array('store_id' => Store(), 'id' => op_t($id)))->save(array('status' => 'refund', 'over_time' => time(), 'type' => 'refund'));
                return true;
            } else {
                $this->error = D('Common/Score')->getError();
                return false;
            }
        }


        if (!$info) {
            $this->error = '交易信息数据未查询到';
            return false;
        }

        if ($price > $info['price']) {
            $this->error = '退款金额不能大于订单实付款';
            return false;
        }

        $api = api('PingPay/Refund', array($info['id'], $info['pingxx_code'], $price, $remark)); //发起退款

        if ($api['status'] == 1) {
            $this->where(array('store_id' => Store(), 'id' => op_t($id)))->save(array('status' => 'refund', 'over_time' => time(), 'type' => 'refund'));
        } else {
            $this->error = $api['info'];
            return false;
        }

        return true;
    }


}
