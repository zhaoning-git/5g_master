<?php

/** 需要登录的基类
 */
namespace Cliapi\Controller;

class MemberController extends ApiController {

    public $uid;
            
    function _initialize() {
        parent::_initialize();
        $this->Login();
    }

    public function Login() {
        //签名
        $sign = I('_sign');
        if(empty($sign)){
            $this->ajaxRet(array('status'=>3,'info' => '_sign参数无效,不能为空!'));
        }
        
        //用户ID
        $uid = I('_uid',0,'intval');
        if(empty($uid)){
            $this->ajaxRet(array('status'=>3,'info' => '_uid参数无效,不能为空!'));
        }
        
        
        //接口
        $_Action = I('_action',__ACTION__);

        $_Action = strtolower(str_ireplace('/index.php', '', $_Action));
        
        $action_len = stripos($_Action, "/cliapi");
        $_Action = substr($_Action, $action_len, strlen($_Action)-$action_len);

        //调试
        //$this->ajaxRet(array('info' => ActionSign($uid,$_Action)));
        $sign = ActionSign($uid,$_Action);
        if($sign != ActionSign($uid,$_Action)){
            $this->ajaxRet(array('status'=>3,'info' => '签名验证失败!'));
        }
        
        $user_token = getToken($uid);
        if ($user_token) {
            $day = 5000; //登陆有效期10天
            $user_token_data = M('UserToken')->where(array('token' => $user_token))->find();
            if(empty($user_token_data)){
                $this->ajaxRet(array('status'=>3,'info' => '登录失效,请重新登录!'));
            }else{
                if (!$user_token_data['uid']) {
                    $this->ajaxRet(array('status'=>3,'info' => '您的网络环境异常，为了账户安全，请重新登陆!'));
                }

                if($user_token_data['uid'] != $uid){
                    $this->ajaxRet(array('status'=>3,'info' => '登录TOKEN错误!'));
                }

                if ($user_token_data['time'] + ($day * 86400) < time()) {
                    $this->DelToken($user_token_data['uid']);
                    $this->ajaxRet(array('status'=>3,'info' => '您已经超过' . $day . '天没有登陆了，请重新登录'));
                }

                $this->uid = $user_token_data['uid'];
            }
        }
        
        else{
            $this->ajaxRet(array('status'=>3,'info' => '您的账户已经被删除或禁用'));
        }
    }
    
    //上传图片 mm
    public function uploadImg(){
        $data = I('post.');
        $uid = $this->uid;
        $data = json_decode($data['data'], true);
        if(empty($data) || !is_array($data)){
            $this->ajaxRet(array('info'=>'参数错误!'));
        }
        
        foreach($data as $key=>$value){
            $key = $key+1;
            $pid = D('Common/File')->Savebase64img($value, $uid,'PICTURE_UPLOAD');
            
            if($pid && is_numeric($pid)){
                $Picture = M('Picture')->where(array('id'=>$pid))->find();
                if(!empty($Picture) && file_exists('.' . $Picture['path'])){
                    $pic[] = (string)$pid;
                }else{
                    $errs[$key] = '第'.$key.'张图片数据不存在!';
                }
            }else{
                $errs[$key] = '第'.$key.'张图片上传错误:'.D('Common/File')->getError();
            }
        }
        
        if(empty($pic)){
            $status = 0;
        }else{
            $status = 1;
            $info = '上传成功';
        }
        
        if(!empty($errs)){
            $info = implode(',', $errs);//'未全部上传成功';
        }
        
        $this->ajaxRet(array('status'=>$status, 'info'=>$info, 'data'=>$pic));
        
        
    }

    //t退出
    public function Logout(){
        $this->DelToken($this->uid);
        $this->ajaxRet(array('status'=>1,'info' => '您已成功退出登录'));
    }
    
    //在线记录
    public function Useronline(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        if(D('UserOnline')->addOnline($data)){
            $this->ajaxRet(1, '成功');
        }else{
            $this->ajaxRet(0, D('UserOnline')->getError());
        }
    }
    
}
