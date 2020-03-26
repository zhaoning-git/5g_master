<?php

namespace Common\Model;

use Common\Model\CommonModel;

class SigninConfigModel extends CommonModel {
    
    public function setConfig($data){
        $data['day'] = intval($data['day']);
        $data['coin'] = intval($data['coin']);
        if(!empty($data['extra_coin'])){
            $data['extra_coin'] = intval($data['extra_coin']);
        }
        
        if(!$data['day']){
            $this->error = '签到天数有误!';
            return false;
        }
        
        if(!$data['coin']){
            $this->error = '奖励银币数量有误!';
            return false;
        }
        
        $info = $this->where(array('day' => $data['day']))->find();
        if(!empty($info)){
            $data['uptime'] = NOW_TIME;
            $this->where(array('id'=>$info['id']))->save($data);
            $result = $info['id'];
        }else{
            $result = $this->add($data);
        }
        $this->ResetCache();
        return $result;
    }
    
    public function getConfig($day){
        $day = intval($day);
        if(!$day){
            $day = 1;
        }
        $Max = $this->Max('day');
        if($day > $Max){
            $day = 1;
        }
        
        $info = $this->where(array('day' => $day))->find();
        if(empty($info)){
            $this->error = '该签到天数没有奖励!';
            return false;
        }
        return $info;
    }

    function ResetCache() {
        $key = 'SigninConfig';
        $list = $this->select();
        if ($list) {
            setcaches($key, $list);
        }
        return 1;
    }
    
}