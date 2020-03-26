<?php

/**
 * 代理商结算明细
 * 开发者: 
 * 创建日期: 17-9-9
 */
namespace Common\Model;

use Think\Model;

class AgentsSettlementModel extends Model {
    
    //每月一号结算上个月的
    function Insert($agents_id=''){
        $agents_id = intval($agents_id);
        if(!$agents_id){
            $this->error = '参数有误!';
            return false;
        }
        
        //代理商详情
        $Agents = D('Agents')->getOne($agents_id);
        if(!$Agents){
            $this->error = D('Agents')->getError();
            return false;
        }
        
        if($Agents['city']){
            $city = explode(',', $Agents['city']);
            //检查本月是否结算过
            foreach ($city as $value){
                if(!$this->checkJs($agents_id, $value)){//未结算,添加结算记录
                    $this->addSettlement($agents_id,$value);
                }
            }
        }else{
            if(!$this->checkJs($agents_id)){//未结算,添加结算记录
                $this->addSettlement($agents_id);
            }
        }
    }
    
    //添加结算记录
    private function addSettlement($agents_id, $city='', $date=''){
        $date = $date?:date('Y-m-d', time());
        
        $AgentsLog = D('AgentsLog')->getMonthLog($agents_id, $date, 'all', $city);
        if(!$AgentsLog){
            $this->error = '消费明细不存在!';
            return false;
        }
        
        //代理商ID
        $data['agents_id'] = $agents_id;
        
        //结算单号
        $data['order_no'] = $agents_id.date('YmdHis');
        
        //结算金额
        $data['money'] = $AgentsLog['money'];
        
        //获得分润金额
        $data['money_profit'] = $AgentsLog['money_profit'];
        
        //结算城市
        if(!empty($city)){
            $data['city'] = $city;
        }
        
        //账单出账日期
        $data['order_date'] = date('Y-m', $AgentsLog['startend'][1]);
        
        //账单起始日期
        $data['start_time'] = $AgentsLog['startend'][0];
        
        //账单结束日期
        $data['end_time'] = $AgentsLog['startend'][1];
        
        //添加时间
        $data['addtime'] = time();
        
        //添加日期
        $data['date'] = date('Ymd',time());
        
        $js_id = $this->add($data);
        if($js_id && is_numeric($js_id)){
            $map['id'] = array('in',$AgentsLog['id']);
            if(M('AgentsLog')->where($map)->setField('js_id', $js_id)){
                return true;
            }else{
                $this->error = M('AgentsLog')->getDbError();
                return false;
            }
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }

    //检查代理商结算情况
    private function checkJs($agents_id, $city='', $date=''){
        $date = $date?:date('Ym01');
        
        $map['date'] = array('EGT', intval($date));
        $map['agents_id'] = intval($agents_id);
        
        if(!empty($city)){
            $map['city'] = intval($city);
        }
        
        if($this->where($map)->count()){
            return true;
        }else{
            return false;
        }
    }
    
    //结算记录的状态
    function getStatus($Status){
        switch ($Status){
            case -1:
                $name = '等待审核';
                break;
            case 0:
                $name = '未结算';
                break;
            case 1:
                $name = '通过审核';
                break;
            case 2:
                $name = '结算失败';
                break;
            default :
                $name = '未知的状态类型';
        }
        return $name;
    }
    
    //审核申请
    //$data['id']
    //$data['status']
    //$data['ver_remark']
    function Ver($data=array()){
        $id = intval($data['id']);
        if(!$id){
            $this->error = '参数有误!';
            return false;
        }
        $info = $this->where(array('id'=>$id))->find();
        if(empty($info)){
            $this->error = '参数有误,或记录不存在!';
            return false;
        }
        
        if(isset($data['status'])){
            $save['status'] = $data['status'];
        }
        
        if(isset($data['ver_status'])){
            $save['ver_status'] = $data['ver_status'];
        }else{
            $save['ver_status'] = 0;
        }
        
        if(!empty($data['ver_remark'])){
            $save['ver_remark'] = trim($data['ver_remark']);
        }
        
        $save['ver_time'] = time();
        if($this->where(array('id'=>$info['id']))->save($save)){
            return true;
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }
    
}