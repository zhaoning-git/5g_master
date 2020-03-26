<?php
namespace Common\Model;

use Common\Model\CommonModel;

class UserSigninModel extends CommonModel {
    
    //签到
    function addSignin($uid){
        $uid = intval($uid);
        if(!$uid){
            $this->error = '参数有误!';
            return false;
        }
        
        $map['uid'] = $uid;
        $map['date']  = date('Ymd', NOW_TIME);
        if($this->where($map)->count()){
            $this->error = '今日已签到';
            return false;
        }
        
        //昨天是否签到
        $zDay = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
        $map['date']  = date('Ymd', $zDay);
        
        $zDayInfo = $this->where($map)->find();
        //昨天未签到
        if(empty($zDayInfo)){
            $daynum = 1;
        }
        //昨天已签到
        else{
            $daynum = M('Users')->where(array('id'=>$uid))->getField('daynum') + 1;
        }
        
        $Config = $this->getConfig($daynum);
        if(!$Config){
            $Config['coin'] = $Config['extra_coin'] = 0;;
        }
        
        $data['uid']     = $uid;
        $data['date']    = date('Ymd', NOW_TIME);
        $data['daynum']  = $daynum;
        $data['coin']    = $Config['coin'] + $Config['extra_coin'];
        $data['create_time'] = NOW_TIME;
        
        $id = $this->add($data);
        if($id){
            M('Users')->where(array('id'=>$uid))->setField('daynum', $daynum);
            Coin($id, 'user_signin_reward');//$id:签到记录ID
            Coin($id, 'user_signin_reward_lx');
            D('JingcaiReceiveLog')->Jiangli($uid);
        }
        return true;   
    }
    
    function getConfig($daynum){
        $info = D('SigninConfig')->getConfig($daynum);
        if($info){
            return $info;
        }
        return false;
    }
    
    
}