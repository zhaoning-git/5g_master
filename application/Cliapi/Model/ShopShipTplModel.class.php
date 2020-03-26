<?php

namespace Api\Model;
use Think\Model;

/**
 * Class ShopLogistics  计算物流费用
 * @author:() 
 */
class ShopShipTplModel extends Model
{
 
	 /**
	 * 计算物流费用逻辑
	 * @author:() 
	 */
	  public function UpdateShip($data, $aid, $store_id){
			  			  
			if(!$data){
				$this->error = '运费计算前数据没有传入';
				return false;	
			}
			
			
			if($aid) $map['id'] = $aid;
			$map['uid'] = is_login();
			$calculate['address'] = M('UserAddress')->where($map)->order('is_default desc')->find();

			foreach($data as $k =>$v){
				if($v['goods_shiptpl_id'] == 0 || !$v['goods_shiptpl_id']){
					//未选择模板 免运费
					$data[$k]['express'] = 0.00;
					$data[$k]['mail'] = 0.00;
					$data[$k]['ems'] = 0.00;
				}else{
					$temp['template'][$v['goods_shiptpl_id']]['id'] = $v['goods_shiptpl_id'];
					$temp['template'][$v['goods_shiptpl_id']]['number']+= $v['number'];
					$temp['template'][$v['goods_shiptpl_id']]['goods_weight']+= $v['goods_weight'];
					$temp['template'][$v['goods_shiptpl_id']]['title'][] = $v['title'];
					
				}
			}
			 	
			foreach($temp['template'] as $k =>$v){
					$pd = $this->SubShip($v['id'],$v['goods_weight'],$v['number'], $calculate['address']['province'],$v['uid']);//返回价格
					if($pd['express']!==false) $calculate['type']['express']+= $pd['express'];
					if($pd['mail']!==false) $calculate['type']['mail']+= $pd['mail'];
					if($pd['ems']!==false) $calculate['type']['ems']+= $pd['ems'];
			}
		
	  		return $calculate['type']['express']?$calculate['type']['express']:0;
	 }
	 
	 
	 //预处理独立城市的运费，如果没有直接返回
	 private function SubShip($id, $goods_weight, $buy_num, $province, $store_id){

		 $templete = $this->where(array('store_id'=>$store_id,'id'=>$id))->find();
		 
		 if(!$templete){
			 $this->error = '没有获取到运费模板';
			 return false;	
		 }
	


		
		 if($templete['express']){
			  $templete['express'] = json_decode($templete['express'],true);
			  foreach($templete['express']['sub'] as $k =>$array){
				  if(in_array($province,explode(',',$array['areas_name']))){
					$back['express']  = $this->CountPrice($templete['type'],$goods_weight,$buy_num,$array);
				  }
			  }
			 $back['express'] ||  $back['express']  = $this->CountPrice($templete['type'],$goods_weight,$buy_num,$templete['express']);
		 }else{
			 $back['express'] = false;
		 }
		 
		 if($templete['mail']){
			  $templete['mail'] = json_decode($templete['mail'],true);
			  foreach($templete['mail']['sub'] as $k =>$array){
				  if(in_array($province,explode(',',$array['areas_name']))){
					  $back['mail']  = $this->CountPrice($templete['type'],$goods_weight,$buy_num,$array);
				  }
			  }
			 $back['mail'] ||  $back['mail']  = $this->CountPrice($templete['type'],$goods_weight,$buy_num,$templete['mail']);
		 }else{
			 $back['mail'] = false;
		 }
		 
		 if($templete['ems']){
			  $templete['ems'] = json_decode($templete['ems'],true);
				   foreach($templete['ems']['sub'] as $k =>$array){
					  if(in_array($province,explode(',',$array['areas_name']))){
						  $back['ems']  = $this->CountPrice($templete['type'],$goods_weight,$buy_num,$templete['ems']);
					  } 
				  }
				$back['ems'] ||  $back['ems']  = 0.00;
		 }else{
			 $back['ems'] = false;
		 }

		 	
		return $back;
		
	 }
	 
	 
	 //得出具体的金额数值
	 private function CountPrice($type, $weight, $buy_num, $template){
		 	
			if($type == 'item'){
				//按件数
				$itme = $buy_num - $template['start'];//购买数量减去首件数量
				
				if($itme<=0){
					$price = $template['postage'];
				}else{
					$price =  (floor($itme/$template['plus'])*$template['postageplus'])+$template['postage']; 
				}
			
			}else{
				//按重量
				$itme = $weight - $template['start'];//购买重量减去首件数量
				if($itme<=0){
					$price = $template['postage'];
				}else{
					$price =  (floor($itme/$template['plus'])*$template['postageplus'])+$template['postage']; 
				}
				
			}
		  return $price;
	 }
	 
	 
}
