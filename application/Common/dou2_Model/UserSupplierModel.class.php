<?php

namespace Common\Model;

use Think\Model;

class UserSupplierModel extends Model {
    
    protected $_validate = array(
        array('uid', 'require', '用户ID不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
        
        array('uid', '', '用户ID已经存在！', self::MUST_VALIDATE, 'unique', self::MODEL_INSERT),
        
        array('pid', 'require', '上级推荐人不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
        array('name', 'require', '供应商名称不能为空',self::MUST_VALIDATE,'regex', self::MODEL_BOTH),
        array('name', '', '供应商名称已经存在！', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
        
        array('phone', 'require', '供应商联系电话不能为空',self::MUST_VALIDATE,'regex', self::MODEL_BOTH),
        array('phone', 'checkPhone', '手机号码格式不正确', self::VALUE_VALIDATE, 'function', self::MODEL_BOTH),
    );
    
    protected $_auto = array(
        array('addtime', NOW_TIME, self::MODEL_INSERT),
        array('last_update_time', NOW_TIME, self::MODEL_UPDATE),
    );
    
    
    
    function _initialize() {
        parent::_initialize();
    }
    
    //添加 || 编辑供应商
    function Update($data=array()){
        if(!$data['id']){
            if(!User($data['uid'],false)){
                $this->error = 'UID:'.$data['uid'].'用户不存在!';
                return false;
            }
            $data['pid'] = User($data['uid'],'link');
            if(!$data['pid']){
                $this->error = 'UID:'.$data['uid'].'没有上级推荐人!';
                return false;
            }
            $uid = $data['uid'];
        }
        
        $data = $this->create($data);
        if(!$data){
            $this->error = $this->getError();
            return false;
        }
        
        //修改
        if($data['id']){
            $Supplier = $this->getOne($data['id']);
            if(empty($Supplier)){
                $this->error = '供应商信息不存在';
                return false;
            }
            $uid = $Supplier['uid'];
            $Supplier_id = $Supplier['id'];
            
            $Result = $this->save($data);
            S('UserSupplierInfo_'.$Supplier_id,null);
            
        }
        
        //添加
        else{
            if($this->where(array('uid'=>$data['uid']))->count()){
                $this->error = '该用户已开通供应商服务!';
                return false;
            }
            
            $Result = $Supplier_id = $this->add($data);
        }
        
        if($Result){
            if($this->addEcSupplier($Supplier_id)){
                return true;
            }else{
                return false;
            }
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }
    
    //给供应商推荐人发放奖励(需要冻结N日)
    function Recommend($oid='',$type=4){
        $SupplierAmount = $this->OrderSupplierAmount($oid);
        if(!$SupplierAmount){
            return false;
        }        
        
        $v['apr'] = C('AWARD_TJ_SUPPLIER');
        $v['type'] = $type;
            
        foreach ($SupplierAmount as $val){
            $v['from_uid'] = $val['user_id'];//订单消费用户ID
            $v['order_id'] = $val['order_id'];
            
            $v['uid'] = User($val['uid'],'link'); //供应商的上级推荐人
            $v['order_money'] = $val['money'];
            
            //20170623客户要求修改奖励金额
            //$v['money'] = round($v['order_money'] * ($v['apr'] / 100),2);
            
            $v['peas'] = round($v['order_money'] * ($val['apr_peas']/100));
            
            $v['money'] = round($v['peas'] * ($v['apr'] / 100),2);
            
            $v['suppliers_id'] = $val['suppliers_id'];
            
            if(!D('RebateAward')->Insert($v,true)){
                $this->error = D('RebateAward')->getError();
            }
        }
        return true;
    }

    //供应商返还营业额
    function Operate($oid=''){
        $SupplierAmount = $this->OrderSupplierAmount($oid);
        if(!$SupplierAmount){
            return false;
        }
        
        foreach ($SupplierAmount as $value){
            if(!$value['suppliers_id']){
                continue;
            }
            
            //供应商用户ID
            $uid = $value['uid'];
            
            //应返供应商的营业额
            $apr = 100 - $value['apr_supplier'];
            $money = round($value['money'] * ($apr / 100),2);
            
            //供应商用户ID
            $data['uid'] = $uid;
            
            $data['suppliers_id'] = $value['suppliers_id'];
            $data['order_id'] = $value['order_id'];
            $data['order_goods_money'] = $value['money'];
            $data['apr'] = $apr;
            $data['money'] = $money;
            $data['desc'] = "订单:(SN:".$value['order_sn'].")产生的营业额";
            
            if(D('RebateSupplier')->Insert($data) !== true){
                $this->error = D('RebateSupplier')->getError();
                $this->Writelog($this->error,'SuppliersOperate');
            }
        }
        
        return true;
    }

    
    //获得某订单商品的所属供应商
    function OrderSupplierAmount($oid=''){
        $orderInfo = D('Rebate')->orderInfo($oid);
        if(!$orderInfo){
            $this->error = D('Rebate')->getError();
            return false;
        }
        
        $orderGoods = M('OrderGoods')->where(array('order_id'=>$orderInfo['order_id']))->select();
        if(empty($orderGoods)){
            $this->error = '订单商品空!';
            return false;
        }
        
        foreach ($orderGoods as $value){
            //取得供应商ID
            $suppliers_id = D('Goods')->getOne($value['goods_id'],'suppliers_id',true);
            if(!$suppliers_id){
                $this->error = '订单ID:'.$orderInfo['order_id'].'供应商ID不能为空';
                continue;
            }
            
            //供应商详情
            $Supplier = $this->getOne($suppliers_id);
            
            $money = $value['goods_number'] * $value['goods_price'];
            
            //订单ID
            $V['order_id'] = $orderInfo['order_id'];
            
            //订单号
            $V['order_sn'] = $orderInfo['order_sn'];
            
            //订单消费用户ID
            $V['user_id'] = $orderInfo['user_id'];
            
            //供应商ID
            $V['suppliers_id'] = $suppliers_id;
            
            //供应商用户ID
            $V['uid'] = $Supplier['uid'];
            
            //供应商让利百分比
            $V['apr_supplier'] = $Supplier['apr_supplier'];
            
            //叮咚豆的获得百分比
            $V['apr_peas'] = $Supplier['apr_peas'];
            
            
            $data[$suppliers_id] = $V;
            $_data[$suppliers_id][] = $money;
        }
        
        foreach ($data as &$value){
            $value['money'] = array_sum($_data[$value['suppliers_id']]);
        }
        
        return $data;
    }


    //获取一个供应商详情
    function getOne($id='',$field=true){
        $id = intval($id);
        if(!$id){
            $this->error = '参数错误!';
            return false;
        }
        
        $key = 'UserSupplierInfo_'.$id;
        $info = S($key);
        if(empty($info)){
            $info = $this->where(array('id'=>$id))->find();
            if(!empty($info)){
                $userInfo = User($info['uid']);
                $info = array_merge($userInfo,$info);
                
                $RebateConfig = D('RebateConfig')->getOne($info['rebate_config']);
                if(!empty($RebateConfig)){
                    $info = array_merge($RebateConfig,$info);
                }
                
                S($key,$info);
            }else{
                $this->error = '供应商不存在!';
                return false;
            }
        }
        if($field === true){
            return $info;
        }else{
            return $info[$field];
        }
        
    }
    
    //添加ectouch供应商
    private function addEcSupplier($Supplier_id){
        $Supplier_id = intval($Supplier_id);
        if(!$Supplier_id){
            $this->error = '供应商ID有误!';
            return false;
        }
        
        $Supplier = $this->getOne($Supplier_id);
        if(!$Supplier){
            return false;
        }
        
        $data['suppliers_name'] = $Supplier['name'];
        $data['suppliers_desc'] = $Supplier['intro'];
        $data['is_check'] = $Supplier['status'];
        
        //修改
        if(M('Suppliers')->where(array('suppliers_id'=>$Supplier['suppliers_id']))->count()){
            M('Suppliers')->where(array('suppliers_id'=>$Supplier['suppliers_id']))->save($data);
            $this->addEcAdmin($Supplier['uid'],$Supplier['suppliers_id']);
            return true;
        }
        
        //添加
        else{
            $data['suppliers_id'] = $Supplier_id;
            if(M('Suppliers')->add($data)){
                $this->where(array('id'=>$Supplier_id))->setField('suppliers_id',$Supplier_id);
                S('UserSupplierInfo_'.$Supplier_id,null);
                $this->addEcAdmin($Supplier['uid'],$Supplier_id);
                return true;
            }
        }
        
    }

    //添加ectouch管理员
    private function addEcAdmin($uid,$suppliers_id){
        $userInfo = User($uid);

        $ec['user_name'] = $userInfo['username'];

        if(!empty($userInfo['email'])){
            $ec['email'] = $userInfo['email'];
        }

        $ec['suppliers_id'] = intval($suppliers_id);
        
        $AdminUser = M('AdminUser')->where(array('user_name'=>$ec['user_name']))->find();

        if(empty($AdminUser)){
            $ec['add_time'] = time();
            $ec['ec_salt'] = '';
            $ec['password'] = M('UcenterMember')->where(array('id'=>$uid))->getField('password');
            $ec['action_list'] = 'goods_manage,remove_back,picture_batch,gen_goods_script,delivery_view';
            
            M('AdminUser')->add($ec);
        }else{
            M('AdminUser')->where(array('user_id'=>$AdminUser['user_id']))->save($ec);
        }
        
    }
    
    
    //写入日志
    function Writelog($msg,$type){
        if(!empty($msg)){
            $msg = "=============".date('H:i:s')."================\r\n".$msg."\r\n\r\n";
            file_put_contents('./Data/'.$type.'/'.date('Ymd').'.txt', $msg,FILE_APPEND);
        }
    }
    
    
    
    
    
    
}
