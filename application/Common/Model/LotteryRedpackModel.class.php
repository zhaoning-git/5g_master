<?php

//红包规则
namespace Common\Model;

use Think\Model;

class LotteryRedpackModel extends Model {
    
    protected $redis;
            
    function _initialize() {
        parent::_initialize();
        
    }

    //应发红包记录
    function RedPack($type, $amount=''){
        if($type == 'zhengdian'){
            $amount = $amount?:intval(date('H', NOW_TIME));
            //红包规则
            $map['type'] = $type;
            $map['factor'] = $amount;
            $pack = $this->where($map)->find();
            if(!empty($pack)){
                unset($map);
                $map['type'] = $type;
                $map['amount'] = $amount;
                $map['date'] = date('Ymd', NOW_TIME);
                $log = M('LotteryRedpackLog')->where($map)->find();
                if(empty($log)){
                    $logid = $this->addlog($amount, $pack);
                    
                    //通知workerman
                    $this->WorkerMan($logid);
                }
            }
        }
        
        else{
            $map['type'] = $type;
            $map['date'] = date('Ymd', NOW_TIME);
            $log = M('LotteryRedpackLog')->where($map)->find();

            //修改
            if(!empty($log)){
                //查询是否有正在累积中的
                $map['is_give_out'] = 0;
                $log = M('LotteryRedpackLog')->where($map)->find();

                if(!empty($log)){
                    $newAmount = $log['amount'] + $amount;
                    //已达到条件
                    if($newAmount >= $log['factor']){
                        if($newAmount > $log['factor']){
                            $newAmount = $log['factor'];
                            $shengyu = ($amount - ($newAmount - $log['amount'])) + $newAmount;
                        }
                        
                        $data['is_give_out'] = 1;
                        $data['amount']      = $newAmount;
                        $data['uptime']      = NOW_TIME;
                        M('LotteryRedpackLog')->where(array('id'=>$log['id']))->save($data);
                        
                        //通知workerman
                        $this->WorkerMan($log['id']);
                        
                        if($shengyu && $shengyu > 0){
                            $this->RedPack($type, $shengyu);
                        }
                    }
                    
                    //未达到条件继续累积
                    else{
                        $data['amount']      = $newAmount;
                        $data['uptime']      = NOW_TIME;
                        M('LotteryRedpackLog')->where(array('id'=>$log['id']))->save($data);
                    }
                }

                //添加一个新的条件
                else{
                    unset($map['is_give_out']);
                    //今天目前最大一档
                    $log = M('LotteryRedpackLog')->where($map)->order('factor DESC')->find();
                    //查询下一档
                    $where['type'] = $type;
                    $where['factor'] = array('GT', $log['factor']); //GT:大于
                    $pack = $this->where($where)->order('factor ASC')->find();
                    if(empty($pack)){
                        //已达最大条件 (暂定为不在发放红包,后期根据客户需求再修改)

                    }

                    else{
                        $newAmount = $log['amount'] + $amount;
                        if($newAmount > $pack['factor']){
                            $newAmount = 0;
                            $shengyu = $amount - $newAmount;
                        }

                        $this->addlog($newAmount, $pack);
                        if($shengyu && $shengyu > 0){
                            $this->RedPack($type, $shengyu);
                        }
                    }
                }
            }

            //添加当天中的第一个条件
            else{
                //红包规则
                $pack = $this->where(array('type'=>$type))->order('factor ASC')->find();

                $newAmount = $amount;
                if($newAmount > $log['factor']){
                    $newAmount = 0;
                    $shengyu = $amount - $newAmount;
                }

                $this->addlog($newAmount, $pack);
                if($shengyu && $shengyu > 0){
                    $this->RedPack($type, $shengyu);
                }
            }
        }
    }

    
    function addlog($newAmount, $pack){
        $data['rpid']        = $pack['id'];
        $data['type']        = $pack['type'];
        $data['factor']      = $pack['factor'];
        $data['amount']      = $newAmount; //在原来的基础上进行累积
        $data['rednum']      = $pack['rednum']; //满足条件后派发红包数量
        $data['is_give_out'] = $pack['type'] == 'zhengdian'?1:0;
        $data['date']        = date('Ymd', NOW_TIME);
        $data['addtime']     = NOW_TIME;
        return M('LotteryRedpackLog')->add($data);
    }
    
    //通知workerman
    function WorkerMan($logid){
       
        $info = M('LotteryRedpackLog')->where(array('id'=>$logid))->find();
        if(empty($info)){
            $this->error = '记录不存在!';
            return false;
        }
        
        if($info['is_redis']){
            $this->error = '红包已发放!';
            return false;
        }
        
        $token = sha1(md5($info['type'].$info['id'].$info['addtime'].C('AUTHCODE')));
        
        //写入Redis 红包数量增加$info['rednum']个
        $redis = Redis();
        $redis->incrBy($token, $info['rednum']);
        
        $up['token']    = $token;
        
        if($info['id'] != 83){//测试
            $up['is_redis'] = 1;
        }
        
        M('LotteryRedpackLog')->where(array('id'=>$info['id']))->save($up);
        
        //缓存红包数据
        $info['token']    = $token;
        $info['is_redis'] = 1;
        $redis->set('LotteryRedpackLog_info_'.$info['id'], serialize($info));
        
        $data['rplogid'] = $info['id'];
        $data['token'] = $token;
        $data['rednum']   = $redis->get($token);
        D('Socket')->sendToAll('redpack', $data);
        return true;
    }
    
}