<?php

/**
 * Date: 17-08-022
 * Time: 下午5:59
 */
namespace Api\Controller;

use Think\Controller;

class ThemeController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }

    //添加话题
    function addTheme(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        if(D('Theme')->Insert($data) === true){
            $this->ajaxRet(array('status'=>1,'info'=>'话题贡献成功!请等待管理员审核'));
        }else{
            $this->ajaxRet(array('info'=>D('Theme')->getError()));
        }
    }
    
}