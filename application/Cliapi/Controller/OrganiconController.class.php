<?php
/**
 * 认证
 */
namespace Cliapi\Controller;

use Think\Controller;

class OrganiconController extends MemberController{
 //企业机构认证申请
 public function organadd(){
    $parem = I('post.');
    $User = D('Organicon');
    if(!$User->create()){
        // 如果创建失败 表示验证没有通过 输出错误提示信息       
        $this->ajaxRet(array('status' => 0, 'info' => $User->getError(),'data'=>''));
    }
    //检测手机验证码
    if (!D('Verify')->checkVerify($parem['mobile'], 'enterprise', $parem['sms_code'])) {
          $this->ajaxRet(array('info' => D('Verify')->getError()));
    }
    //检测手机号
    $Phone = Verify_Phone($parem['mobile']);
    if(!$Phone){
        $this->ajaxRet(array('status' => 0, 'info' => '手机号不合法！','data'=>''));
    } 
    $become =D('Organicon')->where('user_id='.$this->uid)->where('is_audit=2')->find();
    if($become){
       $this->ajaxRet(array('status' => 0, 'info' => '您已经认证成功请勿在此提交','data'=>''));
    }
    //查询数据
    $find = D('Organicon')->where('user_id='.$this->uid)->where('is_audit=1')->find();
    if($find){
      $this->ajaxRet(array('status' => 0, 'info' => '认证审核中，暂不可申请','data'=>''));
    }
    $parem['user_id'] = $this->uid;
    $parem['is_audit'] = 1;
    //写入数据
    $data = $User->add($parem);
    if(!$data){
        $this->ajaxRet(array('status' => 0, 'info' => '创建失败','data'=>''));
    }
    $this->ajaxRet(array('status' => 1, 'info' => '创建成功,等待审核','data'=>''));

 }
 //个人/自媒体认证申请
  public function wemedia(){
    $parem = I('post.');
    $User = D('Wemedia');
    if(!$User->create()){
        // 如果创建失败 表示验证没有通过 输出错误提示信息       
        $this->ajaxRet(array('status' => 0, 'info' => $User->getError(),'data'=>''));
    }
    //检测手机验证码
    if (!D('Verify')->checkVerify($parem['mobile'], 'wemedia', $parem['sms_code'])) {
          $this->ajaxRet(array('info' => D('Verify')->getError()));
    }
    //检测手机号
    $Phone = Verify_Phone($parem['mobile']);
    if(!$Phone){
        $this->ajaxRet(array('status' => 0, 'info' => '手机号不合法！','data'=>''));
    } 
    //查询数据
    $become = D('Wemedia')->where('user_id='.$this->uid)->where('is_audit=2');
    if($become){
      $this->ajaxRet(array('status' => 0, 'info' => '您已经认证成功请勿在此提交','data'=>''));
    }
    $find = D('Wemedia')->where('user_id='.$this->uid)->where('is_audit=1')->find();
    if($find){
      $this->ajaxRet(array('status' => 0, 'info' => '认证审核中，暂不可申请','data'=>''));
    }
    $parem['user_id'] = $this->uid;
    $parem['is_audit'] = 1;
    //写入数据
    $data = $User->add($parem);
    if(!$data){
        $this->ajaxRet(array('status' => 0, 'info' => '创建失败','data'=>''));
    }
    $this->ajaxRet(array('status' => 1, 'info' => '创建成功,等待审核','data'=>''));

  }
  //申请条件
  public function teamexpert(){
    $uid = $this->uid;

    //查看是否是清晰头像
    $data = [
      'portrait' => 'no',
      'mobile' => 'no',
      'team' => 'no',
    ];
    $portrait  = D('users')->where('id='.$uid)->find();
    if(!empty($portrait['avatar'])){
      $data['portrait'] = 'yes';
    }
    if(!empty($portrait['mobile'])){
      $data['mobile'] = 'yes';
    }
    //查询是否设置主队
    $team = D('User_team')->where('uid='.$uid)->find();
    if($team && $team['is_zhu'] == 1){
       $data['team'] = 'yes';
    }
    $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));

  }
  //球队达人
  public function teamligent(){
  	$parem = I('post.');
  	$uid = $this->uid;
  	$portrait  = D('users')->where('id='.$uid)->find();
  	$team = D('User_team')->where('uid='.$uid)->find();
  	if(empty($portrait['avatar']) || empty($portrait['mobile']) || $team['is_zhu'] == 0 ){
       $this->ajaxRet(array('status' => 0, 'info' => '请您开启申请条件！','data'=>$data));
  	}
  	$User = D('Teamligent');
    if(!$User->create()){
        // 如果创建失败 表示验证没有通过 输出错误提示信息       
        $this->ajaxRet(array('status' => 0, 'info' => $User->getError(),'data'=>''));
    }
    //检测手机验证码
    if(!D('Verify')->checkVerify($parem['mobile'], 'teamligent', $parem['sms_code'])){
          $this->ajaxRet(array('info' => D('Verify')->getError()));
    }
    //检验身份证号
    $card = checkCard($parem['idnumber']);
    if(!$card){
          $this->ajaxRet(array('status' => 0, 'info' => '身份证号不合法！','data'=>''));
    }
    $become = D('Teamligent')->where('user_id='.$this->uid)->where('is_audit=2');
    if($become){
      $this->ajaxRet(array('status' => 0, 'info' => '您已经认证成功请勿在此提交','data'=>''));
    }
    //查询数据
    $find = D('Teamligent')->where('user_id='.$this->uid)->where('is_audit=1')->find();
    if($find){
      $this->ajaxRet(array('status' => 0, 'info' => '认证审核中，暂不可申请','data'=>''));
    }
    $parem['user_id'] = $this->uid;
    $parem['is_audit'] = 1;
    //写入数据
    $data = $User->add($parem);
    if(!$data){
        $this->ajaxRet(array('status' => 0, 'info' => '创建失败','data'=>''));
    }
    $this->ajaxRet(array('status' => 1, 'info' => '创建成功,等待审核','data'=>''));

  }
}