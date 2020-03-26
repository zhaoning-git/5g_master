<?php
namespace Common\Model;

use Common\Model\CommonModel;

class UserVerifyModel extends CommonModel {
    
    public function Verify($data){
        $uid = intval($data['uid']);
        if(!$uid){
            $this->error = '用户ID参数错误';
            return false;
        }
        if(empty($data['id_card'])){
            $this->error = '身份证号不能为空';
            return false;
        }
        if(empty($data['realname'])){
            $this->error = '姓名不能为空';
            return false;
        }
        if(empty($data['type'])){
            $this->error = '验证类型不能为空';
            return false;
        }

        
        if(!empty($data['otherdata']) && is_null(json_decode($data['otherdata']))){
            $data['otherdata'] = json_encode($data['otherdata']);
        }
        
        //通过验证
        if($data['is_verify']){
            $map['uid'] = $uid;
            $map['type'] = $data['type'];
            $info = $this->where($map)->find();
            if(empty($info)){
                $data['create_time'] = NOW_TIME;
                $data['create_date'] = date('Y-m-d', NOW_TIME);
                $this->add($data);
                M('users')->where(array('id'=>$uid))->save(array('sf_card'=>$data['id_card'],'sf_is_verify'=>1));
            }else{
                $data['up_time'] = NOW_TIME;
                $this->where(array('id'=>$info['id']))->add($data);
            }
            
            switch ($data['type']){
                //实名
                case 'realname':
                    Coin($uid, 'realname_verify');
                    break;
                
                //银行卡
                case 'bankcard:':
                    
                    break;
                
                //刷脸
                case 'facescan:':
                    Coin($uid, 'facescan_verify');
                    break;
            }
            
            
            
        }
        return true;
    }
    
    public function getVerify($uid, $type=''){
        $uid = intval($uid);
        if(!$uid){
            $this->error = '用户ID参数错误';
            return false;
        }
        
        $map['uid'] = $uid;
        if(!empty($type)){
            $map['type'] = $type;
            return $this->where($map)->find();
        }
        return $this->where($map)->select();
    }
    
    
}
