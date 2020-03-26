<?php

namespace Common\Model;

//竞猜奖励记录
use Think\Model;

class JingcaiReceiveLogModel extends Model {
    
    //添加竞猜奖励
    function addLog($uid){
        //竞猜次数
        $JingcaiNum = $this->getJingcaiNum($uid);
        
        $map['uid'] = $uid;
        $list = $this->where($map)->getField('number',true);
        if(!empty($list)){
            $where['number'] = array(array('not in', $list),array('ELT', $JingcaiNum));
        }else{
            $where['number'] = array('ELT', $JingcaiNum);
        }
        
        $configList = M('JingcaiConfig')->where($where)->select();
        
        if(!empty($configList)){
            foreach ($configList as $value){
                $data['uid'] = $uid;
                $data['config_id'] = $value['id'];
                $data['number'] = $value['number'];
                $data['coin'] = $value['coin'];
                $data['status'] = 0;
                $data['addtime'] = NOW_TIME;
                if(!$this->where(array('uid'=>$uid,'number'=>$value['number']))->count()){
                    $this->add($data);
                }
            }
        }
    }
    
    //发放竞猜奖励 银币奖励在签到后自动发放到个人账户中
    function Jiangli($uid){
        $map['uid'] = $uid;
        $map['status'] = 0;
        $list = $this->where($map)->select();
        if(!empty($list)){
            foreach ($list as $value){
                if(D('UsersCoinrecord')->addCoin($value['id'], 'jing_cai_reward')){
                    $up['status'] = 1;
                    $up['receive_time'] = NOW_TIME;
                    $this->where(array('id'=>$value['id']))->save($up);
                }else{
                    $this->error = D('UsersCoinrecord')->getError();
                    return false;
                }
            }
        }
    }


    //获取竞猜次数
    function getJingcaiNum($uid){
        $map['uid'] = $uid;
        $map['status'] = 1;
        $Num = M('JingcaiLog')->where($map)->count();
        return $Num;
    }
    
}
