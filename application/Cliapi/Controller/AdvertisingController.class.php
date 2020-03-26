<?php 
/**
 * 广告投放
 */
namespace Cliapi\Controller;

use Think\Controller;

class AdvertisingController extends MemberController {
	//个人广告 提交审核
	public function personageAd(){
        //查询是否已经提交过审核
        $id =  $this->uid;
        $where['user_id'] = $id;
        $data = D('Advertising')->where($where)->find();
        if($data && $data['is_audit'] = 1){
          $this->ajaxRet(array('status' => 0, 'info' =>'您已经提交过审核，请勿重复提交','data'=>''));
        } 

        $parem = I('post.');
        $User = D('Advertising');
        if(!$User->create()){
             // 如果创建失败 表示验证没有通过 输出错误提示信息       
             $this->ajaxRet(array('status' => 0, 'info' => $User->getError(),'data'=>''));
        }
        //检验身份证号
        $card = checkCard($parem['card']);
        if(!$card){
          $this->ajaxRet(array('status' => 0, 'info' => '身份证号不合法！','data'=>''));
        }
        //检测手机号
        $Phone = Verify_Phone($parem['mobile']);
        if(!$Phone){
          $this->ajaxRet(array('status' => 0, 'info' => '手机号不合法！','data'=>''));
        } 
        //用户id
           $parem['user_id'] = $this->uid;
           //审核状态 默认 1 待审核
           $parem['is_audit'] = 1;    
        //判断是否是 个人 企业
        if($parem['types']==1){
            
          
           $data = $User->add($parem);
           if(!$data){
             $this->ajaxRet(array('status' => 0, 'info' => '创建失败','data'=>''));
           }
           $this->ajaxRet(array('status' => 1, 'info' => '创建成功,等待审核','data'=>''));

        }
        //企业
        if($parem['types']==2){
           //验证 字段
           if(empty($parem['license'])){
              $this->ajaxRet(array('status' => 0, 'info' => '营业执照为空','data'=>''));
           }
           if(empty($parem['acco'])){
              $this->ajaxRet(array('status' => 0, 'info' => '电信业务经营许可证为空','data'=>''));
           }
           if(empty($parem['account'])){
              $this->ajaxRet(array('status' => 0, 'info' => '开户许可证为空','data'=>''));
           }
           if(empty($parem['company'])){
              $this->ajaxRet(array('status' => 0, 'info' => '公司名称为空','data'=>''));
           }
           if(empty($parem['egistration'])){
              $this->ajaxRet(array('status' => 0, 'info' => '营业执照注册号为空','data'=>''));
           }
           if(empty($parem['province'])){
              $this->ajaxRet(array('status' => 0, 'info' => '省份为空','data'=>''));
           }
           $data = $User->add($parem);
           if(!$data){
             $this->ajaxRet(array('status' => 0, 'info' => '创建失败','data'=>''));
           }
           $this->ajaxRet(array('status' => 1, 'info' => '创建成功,等待审核','data'=>''));

        }
        
	}


  

	



}


