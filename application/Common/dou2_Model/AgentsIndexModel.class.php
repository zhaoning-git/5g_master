<?php

//代理商后台首页数据
namespace Common\Model;
use Think\Model;

class AgentsIndexModel extends Model {
    
    public $agents_id;
    public $start;
    public $end;
    public $time;
    
    function _initialize() {
        parent::_initialize();
    }
    
    //年
    function getYears($agents_id, $date = '', $New = false){
        $this->agents_id = intval($agents_id);
        if(!$this->agents_id){
            $this->error = '参数有误!';
            return false;
        }
        
        $date = $date?:date('Y-m-01', time());
        
        $key = 'AgentsIndex_'.$this->agents_id.'_Y_'.date('Y',strtotime($date));
        
        //调试模式
        S($key,null);
        
        if($New === true){
            S($key,null);
        }
        
        $info = S($key);
        if(empty($info)){
            $this->getDatefw($date,'Y');
            
            $renzheng = $this->AgentsData(1);
            $chongzhi = $this->AgentsData(2);

            /**/
            $info['agents_id'] = $this->agents_id;
            $info['chongzhi'] = $chongzhi[0];
            $info['chongzhi_fr'] = $chongzhi[1];
            $info['renzheng'] = $renzheng[0];
            $info['renzheng_fr'] = $renzheng[1];
            $info['rzdianpu'] = $this->rzDianpu();
            $info['all_fr'] = $info['chongzhi_fr']+$info['renzheng_fr'];
            $info['date'] = $this->time;
            $info['date_txt'] = date('Y',$this->time);
            S($key,$info);
        }
        return $info;
    }
    
    //月
    function getMonths($agents_id, $date = '', $New = false){
        $this->agents_id = intval($agents_id);
        if(!$this->agents_id){
            $this->error = '参数有误!';
            return false;
        }
        
        $this->getDatefw($date);
        
        $key = 'AgentsIndex_'.$this->agents_id.'_'.date('Ym',$this->time);
        
        //调试模式
        S($key,null);
        
        if($New === true){
            S($key,null);
        }
        
        $info = S($key);
        if(empty($info)){
            $map['agents_id'] = $this->agents_id;
            $map['date_txt'] = date('Ym',$this->time);

            $info = $this->where($map)->find();
            if(empty($info)){
                $info = $this->Insert($date);
            }
            
            if(intval(date('Ym', time())) == intval(date('Ym',$this->time))){
                $this->Update();
            }
            
            
            if($info === false){
                return false;
            }
            
            S($key,$info);
        }
        return $info;
    }
    
    private function Insert($date = ''){
       
        $this->getDatefw($date);
        
        $map['agents_id'] = $this->agents_id;
        $map['date_txt'] = date('Ym',$this->time);
        
        if($this->where($map)->count()){
            $this->error = '当前月份记录已写入!';
            return false;
        }
        
        $renzheng = $this->AgentsData(1);
        $chongzhi = $this->AgentsData(2);
        
        $data['agents_id'] = $this->agents_id;
        $data['chongzhi'] = $chongzhi[0];
        $data['chongzhi_fr'] = $chongzhi[1];
        $data['renzheng'] = $renzheng[0];
        $data['renzheng_fr'] = $renzheng[1];
        $data['rzdianpu'] = $this->rzDianpu();
        $data['all_fr'] = $data['chongzhi_fr']+$data['renzheng_fr'];
        $data['date'] = $this->time;
        $data['date_txt'] = date('Ym',$this->time);
        $data['addtime'] = time();
        $id = $this->add($data);
        if($id){
            return $this->where(array('id'=>$id))->find();
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }
    
    private function Update(){
        $this->getDatefw();
        
        $map['agents_id'] = $this->agents_id;
        $map['date_txt'] = date('Ym', $this->time);
        
        $info = $this->where($map)->find();
        
        if(empty($info)){
            $info = $this->Insert(date('Y-m-d',$this->time));
        }
        
        $renzheng = $this->AgentsData(1);
        $chongzhi = $this->AgentsData(2);
        
        $data['chongzhi'] = $chongzhi[0];
        $data['chongzhi_fr'] = $chongzhi[1];
        $data['renzheng'] = $renzheng[0];
        $data['renzheng_fr'] = $renzheng[1];
        $data['rzdianpu'] = $this->rzDianpu();
        $data['all_fr'] = $data['chongzhi_fr']+$data['renzheng_fr'];
        if($this->where(array('id'=>$info['id']))->save($data)){
            $key = 'AgentsIndex_'.$this->agents_id.'_'.date('Ym',$this->time);
            S($key,null);
            return true;
        }else{
            $this->error = $this->getError();
            return false;
        }
    }
    
    
    
    //根据日期获取时间戳
    //$type Y年 m月 数字:季度 1是第一季度 2是第二季度 3是第三季度 4是第四季度
    //$type为m $date空时返回当前月的上个月
    //$type为Y $data空时返回当前年
    //$date 不为空时 $date等于当前月或大于当前月则返回上个月,其余则返回$date指定的月份
    function getDatefw($date = '', $type = 'm'){
        $Nowtime = time();
        
        //月
        if($type == 'm'){
            if(!empty($date) && intval(date('Ym',strtotime($date))) < intval(date('Ym',$Nowtime))){
                $time = strtotime($date);
            }else{
                $time = $Nowtime;
            }

            $start = mktime(0, 0, 0, date('m',$time), 1, date('Y',$time));
            $end = mktime(23, 59, 59, date('m',$time) + 1, 0, date('Y',$time));//月的最后一天
        }
        
        //年
        elseif($type == 'Y'){
            if(!empty($date) && intval(date('Y',strtotime($date))) < intval(date('Y',$Nowtime))){
                $time = strtotime($date);
            }else{
                $time = $Nowtime;
            }
            
            $start = mktime(0, 0, 0, 1, 1, date('Y',$time));
            $end = mktime(23, 59, 59, 12, 31, date('Y',$time));
        }
        
        //季度
        else{
            
        }
        
        $this->time = $time;
        $this->start = $start;
        $this->end = $end;
        
        return array($start, $end);
    }
    
    //充值 or 认证
    private function AgentsData($type){
        $map['type'] = $type?:1;
        $map['agents_id'] = $this->agents_id;
        $map['addtime'] = array(array('EGT',$this->start), array('ELT',$this->end));
        $recharge = M('AgentsLog')->where($map)->sum('money');
        $profit = M('AgentsLog')->where($map)->sum('money_profit');
        return array($recharge?:0.00,$profit?:0.00);
    }
        
    //认证店铺数
    private function rzDianpu(){
        $map['status'] = 1;
        $map['is_privilege'] = 1;
        $map['agents_id'] = $this->agents_id;
        $map['addtime'] = array(array('EGT',$this->start), array('ELT',$this->end));
        $number = M('Store')->where($map)->count();
        return $number?:0;
    }
    
    
}

