<?php
//订单
namespace Common\Model;

use Think\Model;

class OrderInfoModel extends Model {
    
    private $uid;
    private $region_id;
    private $suppliers_id;
    private $Suppliers;
    private $goods_name;
    
    
    //自动验证
    protected $_validate = array(
        array('user_id', 'require', '消费用户ID不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('region_id', 'require', '营业区域ID不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('goods_amount', 'require', '消费金额不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('consignee', 'require', '收货人不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('mobile', 'require', '联系电话不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('address', 'require', '收货地址不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
    );
    
    //自动完成
    protected $_auto = array(
        array('is_diy', 1, self::MODEL_BOTH),
        array('order_status', 5, self::MODEL_INSERT),
        array('add_time', NOW_TIME, self::MODEL_INSERT),
    );
    
    public function _initialize() {
        parent::_initialize();
    }
    
    function Insert($data=array()){
        $_data = $data;
        
        $order_id = intval($_data['order_id']);
        unset($_data['order_id']);
        if($order_id){
            $save['consignee'] = $_data['consignee'];
            $save['mobile'] = $_data['mobile'];
            $save['address'] = $_data['address'];
            $save['admin_remark'] = $_data['admin_remark'];
            $save['update_time'] = NOW_TIME;
            if($this->where(array('order_id'=>$order_id))->save($save)){
                return true;
            }else{
                $this->error = $this->getError();
                return false;
            }
        }
        
        $_data['goods_amount'] = floatval($_data['goods_amount']);
        if(empty($_data['goods_amount']) || !$_data['goods_amount']){
            $this->error = '请输入正确的消费金额!';
            return false;
        }elseif($_data['goods_amount'] < 0.01){
            $this->error = '消费金额不能低于0.01元!';
            return false;
        }
        
        
        $_data['order_amount'] = $_data['goods_amount'];
        
        $data = $this->create($data,1);
        if(!$data){
            $this->error = $this->getError();
            return false;
        }
        
        $_data = array_merge($_data,$data);
        
        if($_data['is_saoma'] == 1){
            $_data['order_status'] = 0;
        }
        
        //验证用户 Tp:1
        if(!$this->checkUser($_data['user_id'])){
            return false;
        }
        
        //验证营业区域 Tp:2
        if(!$this->checkRegion($_data['region_id'])){
            return false;
        }
        
        //验证供应商
        if(!$this->checkSuppliers($_data['suppliers_id'])){
            return false;
        }
        
        //商品名称(要在验证供应商以后执行)
        $this->goodsName($_data['goods_name']);
        
        //生成订单号
        $_data['order_sn'] = $this->OrderSn();
        
        //添加订单
        $order_id = $this->add($_data);
        
        //添加订单商品
        if(!$this->addOrderGoods($order_id,$_data['goods_amount'],$_data['suppliers_id'])){
            $this->where(array('order_id'=>$order_id))->delete();
            return false;
        }
        
        if($_data['shipping_status'] == 2 && $_data['pay_status'] == 2){
            if(!D('Rebate')->rebateUserLog($order_id)){
                $this->where(array('order_id'=>$order_id))->setField('shipping_status',1);
                $this->error = D('Rebate')->getError();
                return false;
            }
        }
        return $order_id;
    }
    
    //商品名称
    function goodsName($goodsName){
        if(empty($goodsName)){
            $this->goods_name = '管理员后台提单自动商品'.$this->Suppliers['name'];
        }else{
            $this->goods_name = $goodsName;
        }
    }


    //验证用户 Tp:1
    function checkUser($uid){
        $uid = intval($uid);
        if(!$uid){
            $this->error = '消费用户UID不正确';
            return false;
        }
        
        if(!User($uid,false)){
            $this->error = '消费用户不存在!';
            return false;
        }else{
            $this->uid = $uid;
        }
        return true;
    }
    
    //验证营业区域 Tp:2
    function checkRegion($region_id){
        $RegionInfo = D('RebateRegion')->getOne($region_id);
        if(!$RegionInfo){
            $this->error = D('RebateRegion')->getError();
            return false;
        }
        
        $this->region_id = $region_id;
        if($this->uid){
            if(User($this->uid,'region_id') != $this->region_id){
                $this->error = '消费用户不是该营业区域的!';
                return false;
            }
        }
        return true;
    }
    
    //验证供应商
    function checkSuppliers($suppliers_id){
        $this->Suppliers = D('UserSupplier')->getOne($suppliers_id);
        if(!$this->Suppliers){
            $this->error = D('UserSupplier')->getError();
            return false;
        }
        $this->suppliers_id = $suppliers_id;
        return true;
    }
    
    //生成订单号
    function OrderSn(){
        do {
            //选择一个随机的方案
            mt_srand((double) microtime() * 1000000);
            $OrderSn = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $map['order_sn'] = $OrderSn;
            $count = M('OrderInfo')->where($map)->count();
        }while ($count);
        
        return $OrderSn;
    }
    
    //添加订单商品
    function addOrderGoods($order_id,$goods_amount){
        if(!$this->suppliers_id){
            $this->error = '供应商ID错误!';
            return false;
        }
        //添加一个商品
        $goodsInfo = $this->addGoods($goods_amount);
        if($goodsInfo){
            $data['order_id'] = $order_id;
            $data['goods_id'] = $goodsInfo['goods_id'];
            $data['goods_name'] = $goodsInfo['goods_name'];
            $data['goods_sn'] = $goodsInfo['goods_sn'];
            $data['suppliers_id'] = $this->suppliers_id;
            $data['goods_number'] = 1;
            $data['market_price'] = $goods_amount;
            $data['goods_price'] = $goods_amount;
            $data['send_number'] = 1;
            $data['is_real'] = 0;
            
            if(M('OrderGoods')->add($data)){
                return true;
            }else{
                $this->error = M('OrderGoods')->getDbError();
                return false;
            }
        }
        return false;
    }
    
    //添加商品
    function addGoods($goods_amount){
        if(!$this->suppliers_id){
            $this->error = '供应商ID错误!!';
            return false;
        }
        
        $data['cat_id'] = 1;
        $data['suppliers_id'] = $this->suppliers_id;
        $data['goods_sn'] = $this->GoodsSn();
        $data['goods_name'] = $this->goods_name;
        //库存
        $data['goods_number'] = 0;
        //市场价
        $data['market_price'] = $goods_amount;
        //本店售价
        $data['shop_price'] = $goods_amount;
        //库存警告数量
        $data['warn_number'] = 0;
        //是否是实物 1是 0否
        $data['is_real'] = 0;
        //是否上架
        $data['is_on_sale'] = 0;
        //添加时间
        $data['add_time'] = '';
        //是否回收站的商品
        $data['is_delete'] = 1;
        $goods_id = M('Goods')->add($data);
        
        if($goods_id){
            $goodsInfo['goods_id'] = $goods_id;
            $goodsInfo['goods_name'] = $data['goods_name'];
            $goodsInfo['goods_sn'] = $this->mkGoodsSn($goods_id);
            M('Goods')->where(array('goods_id'=>$goods_id))->setField('goods_sn',$goodsInfo['goods_sn']);
            return $goodsInfo;
        }  else {
            $this->error = M('Goods')->getDbError();
            return false;
        }
    }
    
    //生成唯一的货号
    function GoodsSn(){
        $goods_id = M('Goods')->getField("MAX(goods_id) + 1 as goods_id");
        do {
            $GoodsSn = $this->mkGoodsSn($goods_id);
            $map['goods_sn'] = $GoodsSn;
            $count = M('Goods')->where($map)->count();
        }while ($count);
        return $GoodsSn;
    }
    
    
    private function mkGoodsSn($goods_id){
        return 'DIY'.str_repeat('0', 6 - strlen($goods_id)) . $goods_id;
    }
    
    
}