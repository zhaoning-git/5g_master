<?php

namespace Common\Model;

//邀请群成员
use Think\Model;

class GroupChatInviteModel extends Model {
    
    //发起邀请
    function Invite($data){
        $Ins['uid'] = $data['uid'];
        $Ins['ql_id'] = $data['ql_id'];
        $Ins['bei_uid'] = $data['bei_uid'];
        
        if(!$Ins['ql_id']){
            $this->error = '群聊ID不能为空!';
            return false;
        }
        
        if(!$Ins['bei_uid']){
            $this->error = '被邀请用户ID不能为空!';
            return false;
        }
        
        if($Ins['uid'] == $Ins['bei_uid']){
            $this->error = '您不能邀请自己!';
            return false;
        }
        
        //群聊信息
        $QLinfo = M('GroupChat')->where(array('id' => $Ins['ql_id']))->find();
        if(empty($QLinfo)){
            $this->error = '群聊不存在!';
            return false;
        }elseif($QLinfo['status'] != 1){
            $this->error = '群聊已关闭!';
            return false;
        }
        
        $count = M('GroupChatUser')->where(array('ql_id'=>$QLinfo['id'], 'status'=>array('LT', 2)))->count();
        if($QLinfo['max_num'] <= $count){
            $this->error = '群聊人数已满!';
            return false;
        }
        
        //发起邀请用户是否是群成员
        $Fuser = M('GroupChatUser')->where(array('ql_id'=>$QLinfo['id'],'uid'=>$Ins['uid']))->find();
        if(!empty($Fuser)){
            if($Fuser['status'] == 0){
                $this->error = '您还未通过管理员审核!';
                return false;
            }elseif($Fuser['status'] == 2){
                $this->error = '您在该群聊黑名单中!';
                return false;
            }
        }else{
            $this->error = '您还不是群成员!';
            return false;
        }
        
        //被邀请用户是否已经在群聊中存在
        $BeiUser = M('GroupChatUser')->where(array('ql_id'=>$QLinfo['id'],'uid'=>$Ins['bei_uid']))->find();
        if(!empty($BeiUser)){
            if($BeiUser['status'] == 0){
                $this->error = '该用户已在群聊中,等待管理员审核!';
                return false;
            }elseif($BeiUser['status'] == 1){
                $this->error = '该用户已在群聊中!';
                return false;
            }elseif($BeiUser['status'] == 2){
                $this->error = '该用户已在群聊黑名单中!';
                return false;
            }
        }
        
        //24小时之内发送5次邀请
        $hour = 24;
        $yqnum = 5;
        unset($map);
        
        $map['uid'] = $Ins['uid'];
        $map['ql_id'] = $Ins['ql_id'];
        $map['bei_uid'] = $Ins['bei_uid'];
        //第一次邀请时间
        $firstInvite = $this->where($map)->order('addtime ASC')->getField('addtime');
        print_r($firstInvite);exit;
        if(!empty($firstInvite)){
            $endTime = $firstInvite + $hour*3600;
            $map['addtime'] = array(array('EGT',$firstInvite),array('ELT',$endTime));
            if($this->where($map)->count() >= $yqnum){
                $this->error = $hour.'小时之内只能发送'.$yqnum.'次邀请';
                return false;
            }
        }
        
        
        
        $Ins['addtime'] = NOW_TIME;
        $Ins['remark'] = '邀请您加入'.$QLinfo['name'];
        $id = $this->add($Ins);
        if($id){
            return true;
        }else{
            $this->error = $this->getDbError();
            return false;
        }
        
    }
    
    //同意&&拒绝 邀请
    function setInvite($data){
        $id = intval($data['id']);
        $info = $this->where(array('id'=>$id))->find();
        if($info['bei_uid'] != $data['uid']){
            $this->error = '邀请记录不存在!';
            return false;
        }
        
        //被邀请用户是否同意0:未同意(等待同意) 1:同意 2:拒绝
        if(!$data['is_agree']){
            $this->error = '参数错误!';
            return false;
        }
                
        //被邀请用户是否已经在群聊中存在
        $BeiUser = M('GroupChatUser')->where(array('ql_id'=>$info['ql_id'],'uid'=>$info['bei_uid']))->find();
        if(!empty($BeiUser)){
            if(!$info['is_agree']){
                $ups['uptime']   = NOW_TIME;
                $ups['is_agree'] = $data['is_agree'];
                $ups['is_in'] = 1;
                $ups['remark'] = $data['is_agree']==1?'同意加入群聊!':'拒绝了你的邀请';
                $this->where(array('id'=>$info['id']))->save($ups);
            }
            
            if($BeiUser['status'] == 0){
                $this->error = '您已在群聊中,等待管理员审核!';
                return false;
            }elseif($BeiUser['status'] == 1){
                $this->error = '您已在群聊中!';
                return false;
            }elseif($BeiUser['status'] == 2){
                $this->error = '您已在群聊黑名单中!';
                return false;
            }
        }
        
        $up['uptime']   = NOW_TIME;
        $up['is_agree'] = $data['is_agree'];
        
        if($this->where(array('id'=>$info['id']))->save($up)){
            
            if($data['is_agree'] == 1){
                $save['is_in'] = 1;
                $save['remark'] = '同意加入群聊!';
                //加入群聊
                $addChat['uid'] = $info['bei_uid'];
                $addChat['ql_id'] = $info['ql_id'];
                $Result = D('GroupChat')->addChat($addChat);
                if(!$Result){
                    $save['is_in'] = 0;
                    $save['remark'] = D('GroupChat')->getError();
                }
            }
            
            else{
               $save['is_in'] = 0;
               $save['remark'] = '拒绝了你的邀请!'; 
            }
            
            $this->where(array('id'=>$info['id']))->save($save);
            if($Result === false){
                $this->error = D('GroupChat')->getError();
                return false;
            }else{
                return true;
            }
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }
}

