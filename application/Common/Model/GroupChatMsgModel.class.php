<?php

namespace Common\Model;

//聊天消息
use Think\Model;

class GroupChatMsgModel extends Model {
    
    //添加群聊消息
    function addGroupMsg($data){
        $data['is_group'] = 1;
        $data['addtime'] = NOW_TIME;
        $this->add($data);
        return true;
    }
    
    //添加私聊消息
    function addUserMsg($data){
        $data['is_group'] = 0;
        $data['addtime'] = NOW_TIME;
        $this->add($data);
        return true;
    }



    //设置聊天记录
    //$data 单独一条聊天记录
    function setMsg($data){
        $data['ql_image']    = AddHttp(M('GroupChat')->where(array('id'=>$data['toid']))->getField('image'));
        $data['user_avatar'] = AddHttp(M('Users')->where(array('id'=>$data['uid']))->getField('avatar'));
        return $data;
    }


    
    
}