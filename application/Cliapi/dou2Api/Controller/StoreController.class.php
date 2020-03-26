<?php

/**
 * Date: 17-08-29
 * Time: 下午2:16
 */
namespace Api\Controller;

use Think\Controller;

class StoreController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }
    
    //完善商店资料
    function addStore(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        
        if(D('Store')->Insert($data) === true){
            $this->ajaxRet(array('status'=>1,'info'=>'资料完善成功!'));
        }else{
            $this->ajaxRet(array('info'=>D('Store')->getError()));
        }
    }
    
    //添加商品
    function addGoods(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        
        if(D('Goods')->addGoods($data) !== false){
            $this->ajaxRet(array('status'=>1,'info'=>'商品添加成功!'));
        }else{
            $this->ajaxRet(array('info'=>D('Goods')->getError()));
        }
    }
    
    //店铺认证
    function Verify(){
        if(D('Store')->Verify($this->uid) === true){
            $this->ajaxRet(array('status'=>1,'info'=>'成功'));
        }else{
            $this->ajaxRet(array('info'=>D('Store')->getError()));
        }
    }
}

