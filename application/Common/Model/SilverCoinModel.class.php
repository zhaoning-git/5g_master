<?php

namespace Common\Model;

use Common\Model\CommonModel;

class SilverCoinModel extends CommonModel {
    public function setConfig($data){
        if(!empty($data['id'])){
            $data['id'] = intval($data['id']);
        }
        
        if($data['id']){
            $info = $this->where(array('id'=>$data['id']))->find();
            if(empty($info)){
                $this->error = '规则不存在!';
                return false;                
            }
            $data['up_time'] = NOW_TIME;
            $this->where(array('id'=>$info['id']))->save($data);
        }else{
            $data['create_time'] = NOW_TIME;
            $this->add($data);
        }
        return true;
    }
}