<?php

namespace Api\Model;
use Think\Model;

class OrderCarModel extends Model{
	
	protected $_validate = array(
        array('goods_id', 'require', '缺少参数goods_id', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
		array('number', 'require', '请填写购买数量', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
		array('uid', 'is_login', self::MODEL_BOTH, 'function'),
    );
	

	public function Lists(){

		$map['uid'] = is_login();
		
		$store = $this->where($map)->distinct(true)->field('store_id')->order('create_time desc')->select();
		foreach($store as $k =>$v){
			$store[$k]['store'] = arrList(M('Store')->where(array('store_id'=>$v['store_id']))->field('title,content,img,address')->find(),1,100,100);
			$store[$k]['goods'] = arrList($this->where($map+array('store_id'=>$v['store_id']))->field('id,store_id,goods_id,title,goods_img,goods_price,number')->order('create_time desc')->select(),2,300,300);
			unset($store[$k]['store_id']);
		}
		return $store;
	}
		
	


	/**
	 * 添加到购物车
	 * @author  
	 */
	public function addCar(){
	
	 	$data = $this->create();
		
        if(!$data){ //数据对象创建错误
            return false;
        }
		
		$info = M('Goods')->where(array('id'=>$data['goods_id']))->field('id,uid,title,goods_price,goods_img,goods_shiptpl_id,goods_weight,group_begintime,group_endtime')->find();


		if(!$info){
			$this->error = '该商品不存在';
			return false;
		}

		if($info['group_begintime'] && time() < $info['group_begintime']){
			$this->error = '该商品还没到抢购时间，无法购买';
			return false;
		}

		if($info['group_endtime'] && time() > $info['group_endtime']){
			$this->error = '该商品抢购已经结束，请等待下一期吧';
			return false;
		}
		
		

		$info['goods_id'] = $info['id'];
		$info['store_id'] = $info['uid'];
		$info['number'] = $data['number'];
		$info['uid'] = $data['uid'];
		$info['create_time'] = $data['create_time'];
		unset($info['id']);

		$have = $this->where(array('uid'=>is_login(),'goods_id'=>$data['goods_id']))->find();

	   	if($have){
			$res = $this->where(array('uid'=>is_login(),'goods_id'=>$data['goods_id']))->setInc('number',$data['number']);
		}else{
			$res = $this->add($info);
		}

		if($res === false){
			$this->error = '未知错误';
			return false;
		}

        return $info;
    }


	 /**
	 * 更新购物车并生成订单
	 * @author  
	 */
	public function CreateOrder($cardata){
		
		$cardata = json_decode($cardata,true);

		foreach($cardata['goods_id'] as $k => $v){

			$goods[$k] = M('Goods')->where(array('id'=>$v,'status'=>'success'))->field('id,status,update_timem,create_timem,sort,cid,is_inno,is_sell,is_best',true)->find();

			if(!$goods[$k]){
				$this->error = '“'.$cardata['title'][$k].'” 可能已被商家下架';
				return false;
			}

			$list[$goods[$k]['uid']][$k] = $goods[$k];
			$list[$goods[$k]['uid']][$k]['goods_id'] = $cardata['goods_id'][$k];
			$list[$goods[$k]['uid']][$k]['number'] = $cardata['number'][$k];
			$list[$goods[$k]['uid']][$k]['weight'] = $goods[$k]['goods_weight']*$cardata['number'][$k];
			$list[$goods[$k]['uid']] = array_values($list[$goods[$k]['uid']]);
			$price+=$goods[$k]['goods_price'];
			$number+=$cardata['number'][$k];
			$weight+=$goods[$k]['goods_weight'];
		}




		$res = D('Order')->addOrder($list, $price, $number, $weight);

		if($res!==false){
			return $res;
		}else{
			$this->error = D('Order')->getError();
			return false;
		}

	}
	
	
	
	
	
}
