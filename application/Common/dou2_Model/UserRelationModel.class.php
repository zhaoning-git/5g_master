<?php
//用户关系  好友,关注,粉丝
namespace Common\Model;
use Think\Model;

class UserRelationModel extends Model {
    
    /** 添加
     * uid: 用户ID
     * relation_uid: 关系用户ID
     * type: 关注类型 1:好友 2:关注 
     */
    function Insert($data=array()){
        $data['uid'] = intval($data['uid']);
        $data['relation_uid'] = intval($data['relation_uid']);
        
        if(!$data['uid'] || !$data['relation_uid']){
            $this->error = '参数有误!!!!~';
            return false;
        }
        
        if(!User($data['uid'],false)){
            $this->error = '用户不存在!';
            return false;
        }
        
        if($data['uid'] == $data['relation_uid']){
            $this->error = '不能为同一个人!';
            return false;
        }
        
        $map['uid'] = $data['uid'];
        $map['relation_uid'] = $data['relation_uid'];
        $map['type'] = $data['type'];
        if($this->where($map)->count()){
            $this->error = '关系已添加!';
            return false;
        }
        
        //关系用户详情
        $userInfo = User($data['relation_uid']);
        if(empty($userInfo)){
            $this->error = '用户不存在!!';
            return false;
        }
        
        //加为好友,需要对方审核
        if($data['type'] == 1){
            $data['status'] = -1;
        }
        
        //关注需要根据身价送花
        elseif($data['type'] == 2){
            $my_price = $userInfo['my_price'];
            if($my_price > 0){
                $Flower['uid'] = $data['uid'];
                $Flower['to_uid'] = $userInfo['uid'];
                $Flower['type'] = 1;
                if(!D('Flowerlog')->Insert($Flower)){
                    $this->error = D('Flowerlog')->getError();
                    return false;
                }
            }
        }
        
        $data['is_supplier'] = $userInfo['is_supplier'];
        $data['addtime'] = time();
        if($this->add($data)){
            return true;
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }
    
    //粉丝数量
    function fans($uid){
        $list = $this->fans_list($uid);
        return $list?count($list):0;
    }
    
    //粉丝列表
    function fans_list($uid){
        $uid = intval($uid);
        if(!$uid){
            $this->error = '参数有误!';
            return false;
        }
        
        $map['relation_uid'] = $uid;
        return $this->where($map)->getField('uid',true);
    }
    
    
    
}