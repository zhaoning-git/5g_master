<?php
//鲜花赠送记录
namespace Common\Model;
use Think\Model;

class FlowerlogModel extends Model {
    
    //添加
    function Insert($data=array()){
        $data['uid'] = intval($data['uid']);
        $data['to_uid'] = intval($data['to_uid']);
        $data['type'] = intval($data['type']);
        
        if(!$data['uid'] || !$data['to_uid']){
            $this->error = '参数有误!!!!!s';
            return false;
        }
        
        if(!User($data['uid'],false) || !User($data['to_uid'],false)){
            $this->error = '用户不存在!';
            return false;
        }
        
        if($data['uid'] == $data['to_uid']){
            $this->error = '你不能给自己送花!';
            return false;
        }
        
        
        if(isset($data['number'])){
            $data['number'] = intval($data['number']);
            if(!$data['number']){
                $this->error = '送花数量不正确!';
                return false;
            }
        }else{
            
            //1:关注送花
            if($data['type'] == 1){
                $data['number'] = User($data['to_uid'],'my_price');
            }
            
            //2:个人动态送花
            elseif($data['type'] == 2){
                $data['number'] = 1;
            }
            
            else{
                $this->error = '错误的送花类型!';
                return false;
            }
            
        }
        
        $data['addtime'] = time();
        
        $userFlower = User($data['uid'],'flower',true);
        if($data['number'] > $userFlower){
            $err = '';
            if($data['type'] == 1){
                $err = '关注该用户需要送花'.$data['number'].'朵,您的';
            }
            $this->error = $err.'鲜花数量不足!';
            return false;
        }
          
        if(M('UcenterMember')->where(array('id'=>$data['uid']))->setDec('flower',$data['number'])){
            if(M('UcenterMember')->where(array('id'=>$data['to_uid']))->setInc('flower',$data['number'])){
                $this->add($data);
                return true;
            }
        }
        $this->error = $this->getDbError();
        return false;
    }
    
    //排行榜
    function FlowerRankingList($uid='', $type='all', $supplier_type=0){
        $uid = intval($uid);
        if(!$uid){
            $this->error = '参数有误!';
            return false;
        }
        
        if(!$supplier_type){
            //收花用户
            $map['to_uid'] = $uid;
            $group = 'uid';
        }else{
            $where['is_supplier'] = 1;
            $where['supplier_type'] = $supplier_type;
            $uids = M('UcenterMember')->where($where)->getField('id',true);
            if(empty($uids)){
                $this->error = '没有该类型的商户!';
                return false;
            }
            $map['to_uid'] = array('in', $uids);
            $group = 'to_uid';
        }
        
        
        
        //日榜
        if($type == 'day'){
            $map['addtime'] = array('BETWEEN',array(strtotime(date('Ymd')),strtotime(date('Ymd'))+86399));
        }
        
        //周榜
        elseif($type == 'week'){
            $map['addtime'] = array('BETWEEN',getWeekRange(date('Ymd',time())));
        }
        
        $field = $group=='uid'?'uid':'to_uid as uid';
        $_list = $this->where($map)->group($group)->field($field.', SUM(number) AS amount')->order('amount DESC')->select();
        //return $field;
        if(!empty($_list)){
            foreach ($_list as &$value){
                $value['userInfo'] = User($value['uid']);
            }
        }
        
        return $_list;
    }
    
    //我获得鲜花列表
    //$type 送花类型 1:关注送花 2:个人动态送花
    //$num 返回条数
    function gainFlowerList($uid='',$type=1, $num=''){
        $map['to_uid'] = intval($uid);
        if(!$map['to_uid']){
            $this->error = '参数有误!';
            return false;
        }
        $map['type'] = intval($type)?$type:1;
        return $this->FlowerList($map, $num);
    }
    
    //我赠送给别人的鲜花列表
    //$type 送花类型 1:关注送花 2:个人动态送花
    function giveFlowerList($uid='',$type=1){
        $map['uid'] = intval($uid);
        if(!$map['uid']){
            $this->error = '参数有误!';
            return false;
        }
        $map['type'] = intval($type)?$type:1;
        
        return $this->FlowerList($map);
    }
    
    private function FlowerList($map, $num=''){
        $num = intval($num);
        if($num){
            $_list = $this->where($map)->limit($num)->select();
        }else{
            $_list = $this->where($map)->select();
        }
        
        if(!empty($_list)){
            $uidField = isset($map['uid'])?'to_uid':'uid';
            foreach ($_list as &$value){
                $value['userInfo'] = User($value[$uidField], array('avatar128'));
            }
        }
        return $_list;
    }
    
    
    
}
