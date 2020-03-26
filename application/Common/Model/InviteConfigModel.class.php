<?php

namespace Common\Model;

use Common\Model\CommonModel;

class InviteConfigModel extends CommonModel {
    
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('level', 'require', '邀请等级不能为空！', 1, self:: MODEL_BOTH),
        array('end_num', 'require',  '升级所需用户数不能为空！', 1, self:: MODEL_BOTH),
        array('coin', 'require',  '每邀请一个奖励银币数量不能为空！', 1, self:: MODEL_BOTH),
    );
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_UPDATE),
    );
    
    
    function _initialize() {
        parent::_initialize();
    }

    function addConfig($data){
        $data = $this->create($data);
        if(!$data){
            $this->error = $this->getError();
            return false;
        }

        $info = $this->where(array('level'=>$data['level']))->find();
        
        //添加
        if(empty($info)){
            $level = $this->Max('level');
            if($data['level']-1 != $level){
                $this->error = '请顺序添加邀请等级';
                return false;
            }
            $data['up_num'] = $this->StageUserNum($data['level'], $data['end_num']);
            $Result = $this->add($data);
        }
        
        //更新
        else{
            $Result = $this->where(array('id'=>$info['id']))->save($data);
        }
        return $Result;
    }
    
    //本阶段用户数量 本阶段升级需要完成数
    //$Level:本阶段级别
    //$EndNum:本阶段结束数
    function StageUserNum($Level, $EndNum){
        if($Level == 1){
            $StageUserNum = $EndNum;
        }
        //获取上一级配置
        $upLevel = $this->where(array('level'=>$Level-1))->find();
        $StageUserNum = $EndNum - $upLevel['end_num'];
        return $StageUserNum;
    }

    function myConfig($uid){
        $myLevel = $this->myLevel($uid);//我的最大等级
        
        $Level = $myLevel['level'];
        
        $Config1  = D('InviteConfig')->getConfig($Level);

        if($myLevel['invite_num'] >= $Config1['up_num']){
            $Level = $Level + 1;
            $Config2  = D('InviteConfig')->getConfig($Level);
            if($Config2['end_num']){
                //升一级
                D('InviteConfig')->upLevel($uid, $Level);
            }
        }

        $coin = $Config2['coin']?:$Config1['coin'];

        if($coin){
            $myLevel = $this->myLevel($uid);//我的最大等级
            $up['up_time'] = NOW_TIME;
            $up['coin_num'] = array('exp', "coin_num+$coin");
            $up['invite_num'] = array('exp', "invite_num+1");
            M('InviteLog')->where(array('id'=>$myLevel['id']))->save($up);
            return $coin;
        }else{
            return 0;
        }
    }

    //升级
    function upLevel($uid, $level){
        if(!M('InviteLog')->where(array('level'=>$level))->count()){
            $Config = $this->getConfig($level);
            $data['uid'] = $uid;
            $data['level'] = $level;
            $data['invite_num'] = 0;
            $data['coin_num'] = 0;
            $data['up_num'] = $Config['up_num'];//本阶段升级需要完成数
            $data['coin'] = $Config['coin'];
            $data['create_time'] = NOW_TIME;
            M('InviteLog')->add($data);
        }
    }

    //获取我的最大等级
    function myLevel($uid){
        $InviteLog = M('InviteLog')->where(array('uid'=>$uid))->order('level DESC')->find();
        if(empty($InviteLog)){
            $InviteLog['level'] = 0;
            $InviteLog['invite_num'] = 0;
        }
        return $InviteLog;
    }

    //获取配置
    function getConfig(int $Level){
        $map['level'] = $Level;
        $Result = $this->where($map)->find();
        if(empty($Result)){
            $Result['end_num'] = 0;
        }
        return $Result;
    }
    
    
    
}