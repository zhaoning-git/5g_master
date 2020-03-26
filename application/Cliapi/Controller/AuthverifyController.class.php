<?php

/**
 * Date: 19-10-25
 * Time: 上午10:51
 */
namespace Cliapi\Controller;

use Think\Controller;

class AuthverifyController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }

    //实名认证
    public function Realname(){
        $data = I('post.');
        
        if($data['is_realname'] != 1){
            $this->ajaxRet(array('info'=>'验证未通过!'));
        }
        
        $Verify['uid'] = $this->uid;
        $Verify['id_card'] = $data['id_card'];
        $Verify['is_verify'] = 1;
        $Verify['type'] = 'realname';
        $Verify['realname'] = $data['realname'];
        $Verify['otherdata'] = $data['otherdata'];
        if(!D('UserVerify')->Verify($Verify)){
            $this->ajaxRet(array('info'=>D('UserVerify')->getError()));
        }else{
            $this->ajaxRet(array('status'=>1, 'info'=>'成功!'));
        }
    }
    
    //银行卡认证
    public function Bankcard(){
        $data = I('post.');
        
        if($data['is_bankcard '] != 1){
            $this->ajaxRet(array('info'=>'验证未通过!'));
        }
        
        $Verify['uid'] = $this->uid;
        $Verify['is_verify'] = 1;
        $Verify['type'] = 'bankcard';
        $Verify['otherdata'] = $data['otherdata'];
        if(!D('UserVerify')->Verify($Verify)){
            $this->ajaxRet(array('info'=>D('UserVerify')->getError()));
        }else{
            $this->ajaxRet(array('status'=>1, 'info'=>'成功!'));
        }
    }
    
    //刷脸
    public function Facescan(){
        $data = I('post.');
        
        if($data['is_facescan '] != 1){
            $this->ajaxRet(array('info'=>'验证未通过!'));
        }
        
        $Verify['uid'] = $this->uid;
        $Verify['is_verify'] = 1;
        $Verify['type'] = 'facescan';
        $Verify['otherdata'] = $data['otherdata'];
        if(!D('UserVerify')->Verify($Verify)){
            $this->ajaxRet(array('info'=>D('UserVerify')->getError()));
        }else{
            $this->ajaxRet(array('status'=>1, 'info'=>'成功!'));
        }
    }
     
    
    
    
}