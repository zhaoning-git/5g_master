<?php

/**
 * 代理商佣金消费明细
 * 开发者: 
 * 创建日期: 17-9-9
 */
namespace Common\Model;

use Think\Model;

class AgentsLogModel extends Model {
    
    //$data['uid'] 消费用户或商家
    //$data['money'] 消费金额
    //$data['type'] 消费类型 1:认证 2:充值
    function Insert($data=array()){
        $data['uid'] = intval($data['uid']);
        if(!$data['uid']){
            $this->error = '参数有误!';
            return false;
        }
        
        //消费金额
        if(!$data['money'] || $data['money'] < 0){
            $this->error = '消费金额不正确!';
            return false;
        }
        
        //用户信息
        $userInfo = User($data['uid'],array('agents_id','salesman_id','is_supplier','region_id'));
        
        //用户没有所属代理商,不用添加代理商佣金消费明细
        if(!$userInfo['agents_id']){
            $this->error = '用户没有所属代理商';
            return false;
        }
        
        //消费用户是否是商家 0:不是 1:是
        $data['is_supplier'] = $userInfo['is_supplier'];
        
        //所属代理商
        $data['agents_id'] = $userInfo['agents_id'];
        
        //所属业务员
        $data['salesman_id'] = $userInfo['salesman_id'];
        
        //消费类型 1:认证 2:充值
        $data['type'] = $data['type']?:1;
        
        //获得分润金额
        if($data['type'] == 1){
            $apr = C('AGENTSVERIFYSTORE');
        }elseif($data['type'] == 2){
            $apr = C('AGENTSRECHARGE');
        }else{
            $this->error = '错误的消费类型!';
            return false;
        }
        
        $data['money_profit'] = round($data['money'] * $apr/100,2);
        
        //所在地区
        $data['city'] = $userInfo['region_id'];
        
        $data['addtime'] = time();
        if($this->add($data)){
            return true;
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }
    
    //获取指定日期一个月的消费明细
    //$type 消费类型 1:认证 2:充值
    //$is_supplier 消费用户是否是商家 0:不是 1:是
    //$field 数据返回字段 null返回列表 id 返回集合 money 返回消费总额 money_profit 返回分润总额
    function getMonthLog($agents_id, $date='', $field=null, $city=null, $type=null, $is_supplier=null)
    {
        $date = $date?:date('Y-m-d', time());
        
        $time = strtotime($date);
        
        //检查是否有符合条件的记录
        $_map['js_id'] = 0;
        $_map['agents_id'] = $agents_id;
        if(!empty($city)){
            $_map['city'] = $city;
        }
        
        if(!$this->where($_map)->count()){
            $this->error = '没有待结算的记录!';
            return false;
        }
        
        $start = mktime(0, 0, 0, date('m',$time), 1, date('Y',$time));
        $end = mktime(23, 59, 59, date('m',$time) + 1, 0, date('Y',$time));
        
        //代理商第一次结算,需要把1号之前的结算进来
        $_where['agents_id'] = $agents_id;
        if(!empty($city)){
            $_where['city'] = $city;
        }
        if(!M('AgentsSettlement')->where($_where)->count()){
            $where['addtime'] = array('LT', $start); //LT 小于
            $where['js_id'] = 0;
            $where['agents_id'] = $agents_id;
            if(!empty($city)){
                $where['city'] = $city;
            }
            $NewStart = $this->where($where)->order('id ASC')->getField('addtime');
            $start = $NewStart?:$start;
        }
        
        $map['js_id'] = 0;
        $map['agents_id'] = $agents_id;
        $map['addtime'] = array(array('EGT',$start),array('ELT',$end));
        
        if(!empty($city)){
            $map['city'] = $city;
        }
        
        if(!is_null($type)){
            $map['type'] = $type;
        }
        
        if(!is_null($is_supplier)){
            $map['is_supplier'] = $is_supplier;
        }
        
        if(is_null($field)){
            return $this->where($map)->select();
        }
        
        elseif($field == 'id'){
            return $this->where($map)->getField('id',true);
        }
        
        elseif($field == 'money'){
            return $this->where($map)->sum('money');
        }
        
        elseif($field == 'money_profit'){
            return $this->where($map)->sum('money_profit');
        }
        
        elseif($field == 'startend'){
            return array($start,$end);
        }
        
        elseif($field == 'all'){
            $all['list'] = $this->where($map)->select();
            $all['id'] = $this->where($map)->getField('id',true);
            $all['money'] = $this->where($map)->sum('money');
            $all['money_profit'] = $this->where($map)->sum('money_profit');
            $all['startend'] = array($start,$end);
            
            return $all;
        }
        
    }
    
}