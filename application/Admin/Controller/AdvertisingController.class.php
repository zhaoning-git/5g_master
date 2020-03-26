<?php

/**
 * 广告主
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class AdvertisingController extends AdminbaseController {
	//联系方式
	public function relation(){
	  //查询数据
      $data = D('Relation')
                 ->field('citys.areaName as areaNmae,cityss.areaName as areaNmaee,id,enterprise,name,province_id,city,mobile,types')
                ->join('cmf_city as citys ON cmf_relation.province_id = citys.areaId')
                ->join('cmf_city as cityss ON cmf_relation.city = cityss.areaId')
                ->where('del=0')
                ->select();
     
      $this->assign("data",$data);
    	
      $this->display();

	}
	//联系方式删除
	public function addel(){
	   $id=intval($_GET['id']);
	
       if(!$id){

         $this->error('数据传入失败！');
        }
        $where['id'] = $id;
        $up['del'] = 1;
        $data = D('Relation')->where($where)->save($up);
        if($data){
          $this->success('删除成功');
        }
          $this->error('删除失败');
	}
	//行业信息
	public function industry(){
		$data = D('industry')->select();
		$this->assign('data',$data);
		$this->display();
	}
	//行业删除
	public function industrydel(){

		$id=intval($_GET['id']);
	
        if(!$id){

         $this->error('数据传入失败！');
        }
        $where['id'] = $id;
        $up['status'] = 1;
        $data = D('industry')->where($where)->save($up);
        if($data){
          $this->success('删除成功');
        }
          $this->error('删除失败');
	}
	//行业恢复
	public function industryrecover(){
		$id=intval($_GET['id']);
		if(!$id){

         $this->error('数据传入失败！');
        }
        $where['id'] = $id;
        $up['status'] = 0;
        $data = D('industry')->where($where)->save($up);
        if($data){
          $this->success('恢复成功');
        }
          $this->error('恢复失败');
	}
	public function industryadd(){
		if(IS_POST){
		  $parem = I('post.');
		  if(empty($parem['industry'])){
             $this->success('行业名称为空');
		  }
		  $User = D('industry');
		  if($User->create()){
            $result = $User->add($parem); // 写入数据到数据库 
            if($result){
              // 如果主键是自动增长型 成功后返回值就是最新插入的值
              $this->success('添加成功');
            }
            $this->error('添加失败');
          }
		}
       $this->display();

	}
	//资质审核列表
	public function personageAd(){
		// echo C('url');die;
		$data  = D('Advertising')
		          ->field('picardz.path as picardzname,picardf.path as picardfnmaee,picardselfcard.path as picardselfcardname,cmf_advertising.id as singid,buseiness,cmf_advertising.name as singname,industry.industry as industry,card,is_audit,users.mobile as mobile,types')
                 ->join('cmf_picture as picardz ON cmf_advertising.cardz = picardz.id')
                 ->join('cmf_picture as picardf ON cmf_advertising.cardf = picardf.id')
                 ->join('cmf_picture as picardselfcard ON cmf_advertising.selfcard = picardselfcard.id')
                 ->join('cmf_industry as industry ON cmf_advertising.industry_id = industry.id')
                 ->join('cmf_users as users ON cmf_advertising.user_id = users.id')
		         ->select();
		//var_dump($data);die();         
		$this->assign('data',$data);
		$this->display();
	}
	//资质审核详情
	public function personageAddetails(){
		$parem = I('get.');
	    //查询
	    $where['id'] = $parem['id'];
	    $User = D('Advertising')->where($where)->find();
	    //个人
	    if($User['types'] == 1){
           $data = D('Advertising')
		          ->field('picardz.path as picardzname,picardf.path as picardfnmaee,picardselfcard.path as picardselfcardname,cmf_advertising.id as singid,buseiness,cmf_advertising.name as singname,industry.industry as industry,card,is_audit,users.mobile as mobile,types')
                 ->join('cmf_picture as picardz ON cmf_advertising.cardz = picardz.id')
                 ->join('cmf_picture as picardf ON cmf_advertising.cardf = picardf.id')
                 ->join('cmf_picture as picardselfcard ON cmf_advertising.selfcard = picardselfcard.id')
                 ->join('cmf_industry as industry ON cmf_advertising.industry_id = industry.id')
                 ->join('cmf_users as users ON cmf_advertising.user_id = users.id')
                 ->where('cmf_advertising.id ='.$parem['id'])
		         ->find();
		         
            $this->assign('data',$data);
		    $this->display();
	    }
	    //企业
	    if($User['types'] == 2){
	    	$data = D('Advertising')
		          ->field('picardz.path as picardzname,picardf.path as picardfnmaee,picardselfcard.path as picardselfcardname,cmf_advertising.id as singid,buseiness,cmf_advertising.name as singname,industry.industry as industry,card,is_audit,users.mobile as mobile,types,licenses.path as licensesname,accos.path as accosname,accounts.path as accountsname,company,egistration,citys.areaName as areanmae')
                 ->join('cmf_picture as picardz ON cmf_advertising.cardz = picardz.id')
                 ->join('cmf_picture as picardf ON cmf_advertising.cardf = picardf.id')
                 ->join('cmf_picture as picardselfcard ON cmf_advertising.selfcard = picardselfcard.id')
                 ->join('cmf_industry as industry ON cmf_advertising.industry_id = industry.id')
                 ->join('cmf_users as users ON cmf_advertising.user_id = users.id')
                 ->join('cmf_picture as licenses ON cmf_advertising.license = licenses.id')
                 ->join('cmf_picture as accos ON cmf_advertising.acco = accos.id')
                 ->join('cmf_picture as accounts ON cmf_advertising.account = accounts.id')
                 ->join('cmf_city as citys ON cmf_advertising.province = citys.areaId')
                 ->where('cmf_advertising.id = '.$parem['id'])
		         ->find();
		    //var_dump($data);die;    
            $this->assign('data',$data);
		    $this->display('personageAddetailss');

	    }
	}
	//资质审核、
	public function audit(){
		$parem = I('post.');
		//更改审核状态
		if(empty($parem['id'])){
          $this->success('传入数据错误');
		}
		$up['is_audit'] = 2;
		$data = D('Advertising')->where('id='.$parem['id'])->save($up);
		if($data){
           $this->success('审核成功');  
		}
		$this->success('审核失败');
	}
	//资质删除
	public function Advertisingdel(){
		$parem = I('get.');
		if(empty($parem['id'])){
           $this->success('传入数据错误'); 
		}
		//删除
		//$del['del'] = 1;
		$data = D('Advertising')->where('id='.$parem['id'])->delete()();
		if($data){
           $this->success('删除成功');  
		}
		$this->success('删除失败');  

	}	
}