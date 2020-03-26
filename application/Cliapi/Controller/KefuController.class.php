<?php
/**
 * 客服
 */
namespace Cliapi\Controller;

use Think\Controller;

class KefuController extends MemberController {

    function _initialize(){
        parent::_initialize();
    }



    //反馈
    function coupleBack(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        $data['create_time'] = time();
        if(empty($data['content']) || empty($data['mobile'])){
            $this->ajaxRet(array('status'=>0,'info'=>'必要信息未填写！'));
        }

        if(Verify_Phone($data['mobile']) === false){
            $this->ajaxRet(array('status'=>0,'info'=>'手机号格式错误！'));
        };

        $res = M('user_fankui')->add($data);
        if($res){

            $this->ajaxRet(array('status'=>1,'info'=>'提交成功！','data'=>$data));
        }
    }

    //反馈记录
    function coupleBackHistory(){
        $uid = $this->uid;
        $info = M('user_fankui')->where(array('uid'=>$uid))->select();
        if(empty($info)){
            $this->ajaxRet(array('status'=>0,'info'=>'暂无数据！'));
        }else{
            $this->ajaxRet(array('status'=>1,'info'=>'获取成功！','data'=>$info));
        }
    }


}