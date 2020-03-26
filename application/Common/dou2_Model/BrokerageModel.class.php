<?php

namespace Common\Model;
use Think\Model;

/**
 * Class Brokerage 记录处理佣金
 * @author:() 
 */
class BrokerageModel extends Model 
{
	
     /**
     * 佣金发放入口
     * @param  String $category sale/spread/invite销售/差额/推荐 $price 金额数量 $info 其他信息
     * @author 
     */
   public function BrokerageIn($uid, $price = 0, $brokerage_type,  $info){
  
		if(!$uid || $price<=0){
			RecError($uid,'发布佣金缺少参数uid/price'.$price);
			$this->error = '缺少参数uid/price';
			return false;
		}
		
		if(!Store('store_spread')&&$brokerage_type == 'spread'){
			//如果类型为差额返还，但店铺没有开启则直接跳出
			return true;
		}

		$delay_time = Store('store_brokerage_give_'.$brokerage_type.'_time');//佣金发放延迟天数
		$in_way = Store('store_brokerage_flow_'.$brokerage_type);//奖金入账渠道
		$BrokerageArr = C('BROKERAGE_CATEGORY');
		
		$data['uid'] = $uid;
		$data['nickname'] = User($uid,'nickname');
		$data['store_id'] = Store();
		$data['price'] = $price;
		$data['category'] = 'brokerage';
		$data['brokerage_type'] = $brokerage_type;
		$data['transfers_way'] = $in_way?$in_way:$info['transfers_way'];
		$data['cause'] = $BrokerageArr[$brokerage_type].'奖励';
		$data['link_uid'] =  $info['link_uid']?$info['link_uid']:0;
		$data['link_nickname'] = get_nickname($data['link_uid']);
		$data['wishing'] =  C('WX_RED_WISHING');
		$data['remark'] =  GetStoreMsg('brokerage_'.$brokerage_type, $info['replace'])?GetStoreMsg('brokerage_'.$brokerage_type, $info['replace']):$info['remark'];
		$data['create_time'] = time();
		$data['md5'] = md5(Store().$uid.intval($price));
		if(!$delay_time){
			//不延迟，立即发放
			$data['status'] = 'unused'; 
		}else if($delay_time > 0){
			//延迟发放的情况
			$data['status'] = 'ban';
			$data['limit_time'] = time()+($delay_time*86400);
		}
		
		if(Store('store_agents_assess')==1){
			//开启了考核
			if(D('Common/Capitalpool')->AssessCount($uid)===false){
				$data['status'] = 'missed';//错失佣金的情况
				$data['limit_time'] = $data['wishing'] = null;
				$missMsg =  array('price'=>$price,'time'=>implode('至',AssessTime(Store('store_agents_assess_time'))));
				$data['remark'] = GetStoreMsg('brokerage_missed',$missMsg);
			}
		}
		
		//拆分佣金，最大为200一个
		if($price>200){
			$cycle = CutRed($price,200);
			foreach($cycle as $k =>$v){
				$data['price'] = $v;
				$res = $this->add($data);
			}
		}else{
			$res = $this->add($data);
		}
		
		if($res!==false){
			if($data['status'] == 'missed'){
				StoreMsg($uid,'错失佣金提醒','brokerage_missed',$missMsg);//发送消息
			}else{
				StoreMsg($uid,'佣金入账提醒','brokerage_'.$brokerage_type,$info['replace']);//发送消息	
			}
			return $res;	
		}else{
			RecError($uid,'发布佣金入库时出现错误',$data);
			return false;
		}
   }
   
   
   
   
    /*
	 * 发布销售佣金、发布差额佣金 只需要传入订单ID
	 * @author  
	 */
    public function BrokerageSale($CapID){
		
		if(!$CapID){
			RecError(null,'发布销售佣金缺少参数CapID');
			$this->error = '发布销售佣金缺少参数CapID';
			return false;
		}
		$Order = M('ShopOrder')->where(array('id'=>$CapID))->field('id,order_no,uid,goods_agents_total,goods_total,order_total')->find();//获取订单
		
		$BuyUser = User($Order['uid'],array('invite_uid','nickname','agents_level','invite_nick'));//购买者

		/*发布差额佣金*/
		$SpreadPrice = $Order['goods_total']-$Order['goods_agents_total'];//差价
		if($BuyUser['agents_level'] == 0 && $BuyUser['invite_uid'] && $SpreadPrice>0){ //买家是非代理的时候执行
			if($SpreadPrice!=0){ 
				$info['replace'] = array('nickname'=>$BuyUser['nickname'],'order_total'=>$Order['goods_total'],'agents_name'=>$BuyUser['invite_nick'],'price'=>$SpreadPrice);//模板替换
				$info['order_no'] = $Order['order_no'];
				$info['link_uid'] = $Order['uid'];
				$this->BrokerageIn($BuyUser['invite_uid'], $SpreadPrice, 'spread', $info);
			}
		}
	
		/*发布销售佣金*/
		$link_user = D('Common/UserLink')->FindLink($Order['uid'],'more','up');
		if(!$link_user){
			return true;
		}
		$i = 0;
			
		foreach($link_user as $k =>$v){
			$i++;
			$scale[$k] = M('Agents')->where(array('store_id'=>Store(),'status'=>1,'level'=>$v['agents_level']))->getField('sale_lv'.$i);
		
			if($scale[$k]!=0 && $scale[$k]){
				$price[$k] = $scale[$k]?$Order['goods_agents_total']*($scale[$k]/100):0.00;
				$more[$k]['order_no'] = $Order['order_no'];
				$more[$k]['link_uid'] = $Order['uid'];
				$more[$k]['link_nick'] = get_nickname($v['uid']);
				$more[$k]['order_link_id'] = $Order['id'];
				$more[$k]['replace'] = array('nickname'=>$BuyUser['nickname'],'order_total'=>$Order['order_total'],'agents_name'=>$v['agents_name'],'price'=>$price[$k]);
			
				$this->BrokerageIn($v['uid'], $price[$k], 'sale', $more[$k]);//发送
			}
		}
		
    }
	
	
	
   	/*
	 * 推荐佣金发布方法 
	 * $info UserAgents 记录表的一条array数据
	 * @author  
	 */
    public function InviteBrokerage($info){
		
		if(!$info){
			$this->error = '发放推荐佣金传入参数出现错误';
			return false;
		}
		 
		$link_user = D('Common/UserLink')->FindLink($info['uid'],'more','up');//获取会员推荐关系
		
		if(!$link_user){
			return true;
		}
		
	
		
		rsort($link_user);
		foreach($link_user as $k => $v){
			$k++; 
			$link[$k] = $v;
			$link[$k]['agents'] =  M('Agents')->where(array('store_id'=>Store(),'status'=>1,'level'=>$v['agents_level']))->field('id,store_id,sale_lv1,sale_lv2,sale_lv3,icon,create_time,cost',true)->find();
			
			if($link[$k]['agents']){
				  if($link[$k]['price'] == 0&&$link[$k]['price']){
					  $link[$k]['price'] = 0;
				  }else{
					  $link[$k]['price'] = $link[$k]['agents']['i_'.$k.'_'.$info['level'].''];
				  }
				  $link[$k]['sort'] = 'i_'.$k.'_'.$info['level'];
				  
				  //升级代理追加办法
				  if($uplevel['from_level'] ==3&&$info['level'] == 1){
					  $link[1]['price'] = 0;
				  }
				  if($uplevel['from_level'] ==3&&$info['to'] == 2){
					  $link[1]['price'] = 0;
				  }
				  if($uplevel['from_level'] ==2&&$info['to'] == 1){
					  $link[1]['price'] = 0;
					  $link[2]['price'] = 0;
				  }
				  
				  //发放
				  $more[$k]['link_uid'] = $info['uid'];	
				  $more[$k]['replace'] = array('nickname'=>get_nickname($info['uid']),'agents_name'=>$info['level_name'],'price'=>$link[$k]['price']);
				  $this->BrokerageIn($v['uid'], $link[$k]['price'], 'invite', $more[$k]);
			}
			
		}
		
		return true;
       
    }
	
	/**
	 * 向公众号发送红包请求 UseRed($uid,$type,$score,$from,$remark){
	 * @author  
	 */
	public function UseBrokerage($id){
		
		  if(!$id){
			$this->error = '缺少参数id';
			return false;	
		  }
		  
		  $map['uid'] = is_login();
		  $map['id'] = op_t($id);
		  $map['store_id'] =  Store();
		  $info =  $this->where($map)->find(); 
		  
		  if(!$info){
			$this->error = '没有查询到该笔佣金';
			return false;	
		  }
		  
		  if(!$info['status']){
			$this->error = '佣金状态异常';
			return false;	
		  }
		  
		  switch($info['status']){
			case 'missed':
				$errmsg = '人生有些事就是如此，错过了就是错过了';
			case 'process':
				$errmsg = '正在发放处理中，请稍后';
			case 'pass':
				$errmsg = '您已经点击过了发放，请等待公众号发放红包';
			case 'sent':
				$errmsg = '该笔佣金已经发放，请到公众号中领取';
			case "used":
				$errmsg = '该笔佣金您已经领取过了';
			case "refund":
				$errmsg = '该笔佣金相关订单的购买者已经退款，故佣金取消发放';
			case "expire":
				$errmsg = '该笔佣金已经过期作废';
			case "ban":
				$errmsg = '该笔佣金必须到'.date('Y-m-d',$info['limit_time']).'才可以领取';
			case "fail":
				$errmsg = '该红包曾遇到异常，请联系管理员核实后发放';
		  };
		  if($info['status']!=='unused' || $info['limit_time']>time() && $info['status'] == 'ban'){
			 $this->error = $errmsg;
			 return false;
		  }
		  
		  $md5 =  md5(Store().$info['uid'].intval($info['price']));
		  
		  if($md5!=$info['md5']){
			$this->error = '该红包加密验证不符，无法领取';
			return false;
		  }
		  
		  
		  if(time()-session('lingtime')<30){
			$this->error = '领取间隔为30秒';
			return false;	
		  }
		  session('lingtime',time());
		  
		  $this->where($map)->save(array('status'=>'pass')); //强制修改状态，避免通过非法渠道快速领取
		  
		  $ret = D('Common/Score')->MoneyGrant($info['id'], is_login(), $info['price'], $info['transfers_way'], 'brokerage', $info);
		  
		  if($ret['status'] == 'failed' || $ret['failure_msg']!==null){
				 //支付失败
				  $this->where($map)->save(array('status'=>'fail','used_time'=>time(),'failure_msg'=>$ret['failure_msg']));
				  $this->error = $ret['failure_msg'];
				  return false;	
		  }else if($ret['status'] == 'sending' || $ret['status'] == 'pending'){
			  //处理中
			  if($info['transfers_way'] == 'wx_red' || $info['transfers_way'] == 'wx_transfer'){
				 $res = $this->where($map)->save(array('status'=>'process','used_time'=>time(),'order_no'=>$ret['order_no'],'pingxx_code'=>$ret['id'])); 
			  }else if($info['transfers_way'] == 'self'){
				 $res = $this->where($map)->save(array('status'=>'used')); 
			  }
			  return true;
		  }
   	 }
	 
	 
	 
	 /**
	 * 追回佣金
	 * @author  
	 */
	public function Refund($order_id, $remark){
			
			if(!$order_id){
				$this->error = '取消佣金时没有订单号';
				return false;
			}
			
			//将能撤回的佣金全部撤回
			$list = $this->where(array('store_id'=>Store(),'link_order_id'=>op_t($order_id),'status'=>array('in'=>'ban,unused,pass')))->select();
			
			if(!$list){
				return false;
			}
			
			foreach($list as $k =>$v){
				$msg = StoreMsg($v['uid'],'佣金取消发放提醒','brokerage_refund',array('link_nickname'=>$v['link_nickname'],'price'=>$v['price']));//发送消息
			}
			
			$res = $this->where(array('store_id'=>Store(),'link_order_id'=>op_t($order_id),'status'=>array('in'=>'ban,unused,pass')))->save(array('remark'=>'该笔佣金相关订单的购买者已经退款，故佣金取消发放','limit_time'=>null,'status'=>'refund'));
			return	true;
    }
	 
	 
}
