<?php
/**
 * 主播
 */
namespace Cliapi\Controller;

use Think\Controller;

class anchController extends MemberController
{
  public function createRoom() {
  	$parem = I('post.');
  	if(!isset($parem['type']) || empty($parem['type'])){
        $this->ajaxRet(array('status' => 0, 'info' => '直播类型为空或者参数错误','data'=>''));
  	}
  	
  	$uid = $this->uid;
  	//查询用户是否正常
  	$where= ['id'=>$uid,'user_status'=>1];
  	$data = D('users')->where($where)->find();

  	if(!$data){
       $this->ajaxRet(array('status' => 0, 'info' => '该用户不存在或者账户异常','data'=>''));
  	}
  	$wheres = ['uid'=>$uid,'status'=>1];
  	$users = D('anchor_ruzhu')->where($wheres)->find();
  	
  	if(!$users){

        $this->ajaxRet(array('status' => 0, 'info' => '不是主播或者资质取消','data'=>''));
  	}
  	 //直播房间号
    $room_no = rand(99999,00000);
  	//查询主播开播
  	$map = ['uid'=>$uid,'anchor_id'=>$users['id'],'status'=>1];
  	$zhib = D('zhibo')->where($map)->find();
  	if($zhib){
       $this->ajaxRet(array('status' => 0, 'info' => '您已经开播，请勿重试','data'=>''));
  	}

  	$nowtime = time();
  	$stream=$uid.'_'.$nowtime;
  	//推流
    $wyinfo = PrivateKey('rtmp',$stream,1);
    //拉流
	$pull=PrivateKey('rtmp',$stream,2);
	
    if(empty($wyinfo) || empty($pull)){
       $this->ajaxRet(array('status' => 0, 'info' => '开播失败请重试','data'=>''));
    }
    //直播房间号
    $room_no = rand(99999,00000);
    //直播 开始时间
    $begin_time = time();
    $data = [
      'uid' => $uid,
      'anchor_id' => $users['id'],
      'room_no' => $room_no,
      'begin_time' => time(),
      'video_path' => $wyinfo,
      'la_puth' => $pull,
      'status' => 1,
      'type' => $parem['type'],
    ];
    $create = M('zhibo')->add($data);
    if(!$create){
       $this->ajaxRet(array('status' => 0, 'info' => '服务器异常','data'=>''));
    }
    $this->ajaxRet(array('status' => 1, 'info' => '','data'=>['wyinfo'=>$wyinfo]));

  }
}