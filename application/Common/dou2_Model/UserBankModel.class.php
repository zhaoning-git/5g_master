<?php

namespace Common\Model;

use Think\Model;

class UserBankModel extends Model {


    //添加 && 编辑 银行
    function addBank($bankName='',$id=''){
        $id = intval($id);
        
        if(empty($bankName)){
            $this->error = '银行名称不能为空!';
            return false;
        }
        
        if($id){
            $bank = M('Bank')->where(array('id'=>$id))->find();
            if(empty($bank)){
                $this->error = '银行不存在!';
                return false;
            }
            
            if($bank['bankname'] != $bankName && M('Bank')->where(array('bankname'=>$bankName))->count()){
                $this->error = '银行名称已存在!';
                return false;
            }
            
            if(M('Bank')->where(array('id'=>$id))->setField('bankname',$bankName) !== false){
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }else{
            if(M('Bank')->where(array('bankname'=>$bankName))->count()){
                $this->error = '银行名称已存在!';
                return false;
            }elseif(M('Bank')->add(array('bankname'=>$bankName))){
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }
    }
    
    
    
}
