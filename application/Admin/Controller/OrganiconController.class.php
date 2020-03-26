<?php

/**
 * 认证审核
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class OrganiconController extends AdminbaseController {
	//企业机构认证
   public function organ(){
    $data = D('Organicon')
            ->field('cmf_organicon.id as orid,operator,information,location,organization,barcode,license,barcodes,letter,cmf_organicon.mobile as organmobile,user_id,licenumber,license.path as licenses,barcodes.path as barcodess,letter.path as letters,users.mobile as mobiles,is_audit')
           
            ->join('cmf_picture as license ON cmf_organicon.license = license.id')
            ->join('cmf_picture as barcodes ON cmf_organicon.barcodes = barcodes.id')
            ->join('cmf_picture as letter ON cmf_organicon.letter = letter.id')
            ->join('cmf_users as users ON cmf_organicon.user_id = users.id')
            ->select();
    $this->assign('data',$data);
    $this->display();      

   }
   //企业认证机构 审核
   public function orgdit(){
     $parem = I('get.');
     $data = D('Organicon')
            ->field('cmf_organicon.id as orid,operator,information,location,organization,barcode,license,barcodes,letter,cmf_organicon.mobile as organmobile,user_id,licenumber,license.path as licenses,barcodes.path as barcodess,letter.path as letters,users.mobile as mobiles,is_audit,user_id')
           
            ->join('cmf_picture as license ON cmf_organicon.license = license.id')
            ->join('cmf_picture as barcodes ON cmf_organicon.barcodes = barcodes.id')
            ->join('cmf_picture as letter ON cmf_organicon.letter = letter.id')
            ->join('cmf_users as users ON cmf_organicon.user_id = users.id')
            ->where('cmf_organicon.id='.$parem['id'])
            ->find();

    $this->assign('data',$data);
    $this->display();

   }
   //企业认证机构 审核
   public function orauit(){
      $parem = I('post.');
     
      if(empty($parem['id'])){
        $this->error('传入数据错误');
      }
       if(empty($parem['user_id'])){
        $this->error('传入数据错误');
      }
      //修改 状态 修改用户的标识
      try {
        $up = D('Organicon')->where('id='.$parem['id'])->save(['is_audit'=>2]);
        $ups = D('users')->where('id='.$parem['user_id'])->save(['organicon'=>1]);
      } catch (\Exception $e) {
      	   $this->error('审核失败');
       
      } $this->success('审核成功');
     
    

   }
   //个人/自媒体认证申请
   public function wemedia(){
        $data = D('Wemedia')
                ->field('information,cmf_wemedia.name as names,idnumber,cmf_wemedia.mobile as mobiles,user_id,cardself.path as cardselfs,prove.path as proves,is_audit,cmf_wemedia.id as ids,users.mobile as yonmobile')
                ->join('cmf_picture as cardself ON cmf_wemedia.cardself = cardself.id')
                ->join('cmf_picture as prove ON cmf_wemedia.prove = prove.id')
                ->join('cmf_users as users ON cmf_wemedia.user_id = users.id')
                ->select();
        $this->assign('data',$data);
        $this->display();
   }
   //个人自媒体 编辑
   public function wemedit(){
   	    $parem = I('get.');

   	    if(empty($parem['id'])){
           $this->error('数据传入失败');
   	    }
        $data = D('Wemedia')
                ->field('information,cmf_wemedia.name as names,idnumber,cmf_wemedia.mobile as mobiles,user_id,cardself.path as cardselfs,prove.path as proves,is_audit,cmf_wemedia.id as ids,users.mobile as yonmobile')
                ->join('cmf_picture as cardself ON cmf_wemedia.cardself = cardself.id')
                ->join('cmf_picture as prove ON cmf_wemedia.prove = prove.id')
                ->join('cmf_users as users ON cmf_wemedia.user_id = users.id')
                ->where('cmf_wemedia.id='.$parem['id'])
                ->find();
        $this->assign('data',$data);
        $this->display();        
        
   }
   //个人自媒体 审核
   public function wemeauit(){
   	$parem = I('post.');
   	 if(empty($parem['id'])){
        $this->error('传入数据错误');
      }
       if(empty($parem['user_id'])){
        $this->error('传入数据错误');
      }
      //修改 状态 修改用户的标识
      try {
        $up = D('Wemedia')->where('id='.$parem['id'])->save(['is_audit'=>2]);
        $ups = D('users')->where('id='.$parem['user_id'])->save(['wemedia'=>1]);
      } catch (\Exception $e) {
      	   $this->error('审核失败');
       
      } $this->success('审核成功'); 
   }
   //球队达人 
   public function teamligent(){
       $data = D('Teamligent')
                ->field('team,cmf_teamligent.name as names,idnumber,introduction,cmf_teamligent.mobile as mobiles,is_audit,cradself.path as cradselfs,prove.path as proves,cmf_teamligent.id as ids,users.mobile as usermobile')
                ->join('cmf_picture as cradself ON cmf_teamligent.cradself = cradself.id')
                ->join('cmf_picture as prove ON cmf_teamligent.prove = prove.id')
                ->join('cmf_users as users ON cmf_teamligent.user_id = users.id')
                ->select();
                
        $this->assign('data',$data);
        $this->display();
   }
   //球队大人 编辑
   public function temdit(){
   	    $parem = I('get.');
       
   	    if(empty($parem['id'])){
           $this->error('数据传入失败');
   	    }
   	    $data = D('Teamligent')
                ->field('team,cmf_teamligent.name as names,idnumber,introduction,cmf_teamligent.mobile as mobiles,is_audit,cradself.path as cradselfs,prove.path as proves,cmf_teamligent.id as ids,users.mobile as usermobile,user_id')
                ->join('cmf_picture as cradself ON cmf_teamligent.cradself = cradself.id')
                ->join('cmf_picture as prove ON cmf_teamligent.prove = prove.id')
                ->join('cmf_users as users ON cmf_teamligent.user_id = users.id')
                ->where('cmf_teamligent.id='.$parem['id'])
                ->find();
        
        $this->assign('data',$data);
        $this->display();       
   }
   public function temauit(){
   	 $parem = I('post.');
   	 if(empty($parem['id'])){
        $this->error('传入数据错误');
      }
       if(empty($parem['user_id'])){
        $this->error('传入数据错误');
      }
      //修改 状态 修改用户的标识
      try {
        $up = D('Teamligent')->where('id='.$parem['id'])->save(['is_audit'=>2]);
        $ups = D('users')->where('id='.$parem['user_id'])->save(['teamligent'=>1]);
      } catch (\Exception $e) {
      	   $this->error('审核失败');
       
      } $this->success('审核成功'); 
   }
   //专家认证
   public function specialist(){
      $data = D('Specialist')
               ->field('cmf_specialist.id as ids,speciname,introduce,user_id,microblog,wasno,cmf_specialist.sex as sexs,restapp,untnumber,is_audit,  certificate.path as certificates,users.mobile as mobiles,portrait.path as portraits,listtype.types as types')
                ->join('cmf_picture as certificate ON cmf_specialist.certificate = certificate.id')
                ->join('cmf_picture as portrait ON cmf_specialist.portrait = portrait.id')
                ->join('cmf_specialisttype as listtype ON cmf_specialist.spec_type = listtype.id')
                ->join('cmf_users as users ON cmf_specialist.user_id = users.id')
               ->select();

      $this->assign('data',$data);
      $this->display(); 
   }
   //专家认证 编辑
   public function speciadit(){
     $parem = I('get.');
     if(empty($parem['id'])){
        $this->error('数据传入失败');
     }
      $data = D('Specialist')
               ->field('cmf_specialist.id as ids,speciname,introduce,user_id,microblog,wasno,cmf_specialist.sex as sexs,restapp,untnumber,is_audit,  certificate.path as certificates,users.mobile as mobiles,portrait.path as portraits,listtype.types as types')
               ->join('cmf_picture as certificate ON cmf_specialist.certificate = certificate.id')
               ->join('cmf_users as users ON cmf_specialist.user_id = users.id')
               ->join('cmf_picture as portrait ON cmf_specialist.portrait = portrait.id')
               ->join('cmf_specialisttype as listtype ON cmf_specialist.spec_type = listtype.id')
               ->where('cmf_specialist.id='.$parem['id'])
               ->find();
               
      $this->assign('data',$data);
      $this->display();
   }
   //专家审核
   public function speciaauit(){
     $parem = I('post.');
     if(empty($parem['id'])){
        $this->error('传入数据错误');
     }
     if(empty($parem['user_id'])){
        $this->error('传入数据错误');
      }
      //修改 状态 修改用户的标识
      try {
        $up = D('Specialist')->where('id='.$parem['id'])->save(['is_audit'=>2]);
        $ups = D('users')->where('id='.$parem['user_id'])->save(['specialist'=>1]);
      } catch (\Exception $e) {
           $this->error('审核失败');
       
      } $this->success('审核成功');

   }
}