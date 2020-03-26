<?php

namespace Common\Model;

use Common\Model\CommonModel;

class UserLevelModel extends CommonModel {

    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '等级名称不能为空！', 1, 'regex', self:: MODEL_INSERT),
        array('medal_name', 'require',  '勋章名称不能为空！', 1, 'regex', self:: MODEL_INSERT),
        array('name', 'require',  '等级名称已存在！', 1, 'unique', self:: MODEL_BOTH),
        array('medal_name', 'require',  '勋章名称已存在！', 1, 'unique', self:: MODEL_BOTH),
    );
    
    protected $_auto = array(
        array('addtime', NOW_TIME, self::MODEL_INSERT),
        array('uptime', NOW_TIME, self::MODEL_UPDATE),
    );

    //添加编辑级别
    function setLevel($data){
        $id = intval($data['id']);
        if($id){
            $info = $this->where(array('id'=>$id))->find();
            if(empty($info)){
                $this->error = '会员等级不存在!';
                return false;
            }
            
            $data = $this->create($data, 2);
            if(!$data){
                $this->error = $this->getError();
                return false;
            }elseif($this->save($data)){
                return $info['id'];
            }
        }
        
        else{
            $data = $this->create($data, 1);
            if(!$data){
                $this->error = $this->getError();
                return false;
            }
            $id = $this->add($data);
            if($id){
                return $id;
            }
        }
        
        $this->error = $this->getDbError();
        return false;
    }
    
    //会员升级
    //$upType; 升级方法 1:系统  2:会员兑换券
    //$Level 要升级到的级别
    function upLevel($uid,$Level=0, $upType=''){
        $recharge = $this->Recharge($uid);
        $silver = $this->Silver($uid);
        $invite = $this->Invite($uid);
        $loginday = $this->Loginday($uid);
        if(!$Level){
            $Level = array();
            $Level[] = M('UserLevel')->where(array('recharge'=>array('ELT', $recharge)))->order('recharge DESC')->getField('id');
            $Level[] = M('UserLevel')->where(array('silver'=>array('ELT', $silver)))->order('silver DESC')->getField('id');
            $Level[] = M('UserLevel')->where(array('invite'=>array('ELT', $invite)))->order('invite DESC')->getField('id');
            $Level[] = M('UserLevel')->where(array('loginday'=>array('ELT', $loginday)))->order('loginday DESC')->getField('id');
            
            $Level = min($Level);
            $upType = 1;
        }
        
        $userLevel = M('Users')->where(array('id'=>$uid))->getField('level');
        
        $userLevelName = M('UserLevel')->where(array('id'=>$userLevel))->getField('name');
        $upLevelName   = M('UserLevel')->where(array('id'=>$Level))->getField('name');
        
        if($Level > $userLevel){
            $is_up = 1;
            $level_start_time = NOW_TIME;
            $level_end_time = NOW_TIME + 30*86399;
            $uP['level'] = $Level;
            $uP['level_start_time'] = $level_start_time;
            $uP['level_end_time'] = $level_end_time;
            M('Users')->where(array('id'=>$uid))->save($uP);
            $remark = $userLevelName.'升级到'.$upLevelName;
        }else{
            $is_up = 0;
            $level_start_time = 0;
            $level_end_time = 0;
            $remark = $userLevelName.'不能升级到'.$upLevelName;
        }
        
        $data['uid'] = $uid;
        $data['old_level'] = $userLevel;
        $data['new_level'] = $Level;
        $data['up_type'] = $upType; //升级方法 1:系统  2:会员兑换券
        $data['recharge'] = $recharge;
        $data['silver'] = $silver;
        $data['invite'] = $invite;
        $data['loginday'] = $loginday;
        $data['is_up'] = $is_up;
        $data['date'] = date('Ymd', NOW_TIME);
        $data['level_start_time'] = $level_start_time;
        $data['level_end_time'] = $level_end_time;
        $data['remark'] = $remark;
        $data['addtime'] = NOW_TIME;
        return M('UserLevelLog')->add($data);
    }
    
    //获取会员特权
    function getUserPriv($uid, $Privid){
        $userLevel = M('Users')->where(array('id'=>$uid))->getField('level');
        
        $map['priv_id']  = $Privid;
        $map['level_id'] = $userLevel;
        $Priv = M('UserPriv')->where($map)->find();
        if(empty($Priv)){
            return false;
        }else{
            if($Priv['value']){
                return $Priv['value'];
            }else{
                return true;
            }
        }
    }


    //累积充值金额 充值金额不累积,需要修改
    function Recharge($uid){
        $map['uid'] = $uid;
        $map['type'] = 'income';
        $map['coin_type'] = 'gold_coin';
        return M('UsersCoinrecord')->where($map)->sum('totalcoin')?:0;
    }
    
    //累积获得银币
    function Silver($uid){
        $map['uid'] = $uid;
        $map['type'] = 'income';
        $map['coin_type'] = 'silver_coin';
        return M('UsersCoinrecord')->where($map)->sum('giftcount')?:0;
    }
    
    //累积邀请注册
    function Invite($uid){
        $map['invite_id'] = $uid;
        return M('Users')->where($map)->count()?:0;
    }
    
    //累积登陆天数
    function Loginday($uid){
        $map['uid'] = $uid;
        $map['type'] = 'online_time';
        return count(M('UserOnline')->distinct(true)->where($map)->field('date')->select())?:0;
    }
    
}