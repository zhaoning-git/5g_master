<?php
/**
 * 所属项目 OnePlus.
 * 开发者: 想天
 * 创建日期: 3/13/14
 * 创建时间: 7:41 PM
 * 版权所有 想天工作室(www.ourstu.com)
 */

namespace Common\Model;

use Think\Model;

class MessageModel extends Model
{


	//关注好友
	public function See($uid ,$me_uid) {

		if(!$uid) {
			$this->error ='缺少参数uid';return false;
		}
		if(!$me_uid) {
			$this->error ='缺少参数me_uid';return false;
		}

		$is = M('ImUser')->where(array('fuid'=>intval($uid),'uid'=>$me_uid))->find();
        if($is) {
			$this->error ='他已经是你关注的好友了';return false;
		}

        return M('ImUser')->add(array('fuid'=>intval($uid),'uid'=>$me_uid,'create_time'=>time()));
    
	}


	//将会员加入黑名单
	public function Black($uid, $unlock) {

		if(!$uid){
			$this->error = '缺少参数uid'; return false;
		}
		$is = M('ImUser')->where(array('fuid'=>$uid,'uid'=>is_login()))->find();

		if(!$is) {
			//粉丝本来就不在好友列表，需要临时创建一个
			return M('ImUser')->add(array('fuid'=>$uid,'uid'=>is_login(),'status'=>-1));
		}

		if($unlock == 1){
			$ret = M('ImUser')->where(array('fuid'=>$uid,'uid'=>is_login()))->save(array('status'=>1));
			if($ret===false) {
				$this->error ='移除黑名单失败';return false;
			}
			$ret = api('Rong/userBlacklistRemove',array('userId'=>is_login(), 'blackUserId'=>$uid));
		}else{
			$ret = M('ImUser')->where(array('fuid'=>$uid,'uid'=>is_login()))->save(array('status'=>-1));
			if($ret===false) {
				$this->error ='添加到黑名单失败';return false;
			}
			$ret = api('Rong/userBlacklistAdd',array('userId'=>is_login(), 'blackUserId'=>$uid));
		}

		if($ret['code']!== 200){
			M('ImUser')->where(array('fuid'=>$uid,'uid'=>is_login()))->save(array('status'=>1));
			$this->error = $ret['errorMessage']; return false;
		}

		return true;
    }


	//将会员加入黑名单
	public function DeleteFriend($uid) {

		if(!$uid){
			$this->error = '缺少参数uid'; return false;
		}

		$is = M('ImUser')->where(array('fuid'=>$uid,'uid'=>is_login()))->find();

		if(!$is) {
			$this->error ='无法操作，改用户不在您的好友列表';return false;
		}

		$ret = M('ImUser')->where(array('fuid'=>$uid,'uid'=>is_login()))->delete();

		if($ret===false) {
			$this->error ='取消关注失败';return false;
		}
		
		return true;
    }



    public function setAllReaded($uid) {
       $this->where('uid=' . $uid . ' and  is_read=0')->setField('is_read', 1);
    }

 

    /**
     * @param $发送消息
     */
    public function ChatMessage($uid, $to_uid, $content)
    {
		if(!$uid){
			$this->error = '发送人不该为空';
			return false;
		}

		if(!$to_uid){
			$this->error = '接受人不能为空';
			return false;
		}

		if(!$content){
			$this->error = '发送内容不该为空';
			return false;
		}


        $message['to_uid'] = $to_uid;
        $message['content'] = html($content);
        $message['uid'] = $uid;
		$message['is_read'] = 0;
        $message['type'] = 'im';
        $message['create_time'] = time();
	
        $rs = $this->add($message);

		if($rs === false){
			$this->error = '消息发送失败';
			return false;
		}
		
		$json['me_uid'] = $uid;
		$json['to_uid'] = $to_uid;
		$json['content'] = html($content);
		$json['create_time'] = date('Y-m-d H:i:s');
		$json['me_userinfo'] = User($uid,array('uid','nickname','avatar128'));
		$json['to_userinfo'] = User($to_uid,array('uid','nickname','avatar128'));
		$callback = json_encode($json);
		$re = api('Rong/messagePublish',array('fromUserId'=>$uid,'toUserId'=>$to_uid,'objectName'=>'RC:TxtMsg','content'=>$callback));

		if($re['code'] != 200){
			$this->error = $re['errorMessage'];
			return false;
		}

        return true;
    }
	
	
	  /*
	  * 系统发送消息给用户
      */
    public function SystemMsg($uid, $title, $content ,$url, $action)
    {
		if(!$uid || !$content) {
			$this->error = '缺少发送人或发送内容'; return false;
		}
		
		$User = User($uid,array('mobile','nickname'));
		$content = str_replace($User['nickname'], '您', $content);

        $message['uid'] = $uid;
        $message['content'] = $content;
        $message['title'] = $title?$title:'您有一条新消息';
        $message['url'] = $action['url'];
        $message['type'] = 'system';
        $message['create_time'] = time();
		$message['status'] = 1;
		
        if($action['send_message'] == 1) $this->add($message); //发送站内消息
		if($action['send_sms'] == 1) api('YunPian/sendSMS',array('mobile'=>$User['mobile'],'content'=>$content));//发送短信
		if($action['send_pm_kf'] == 1) R('Core/Wechat/SendMsg',array('uid'=>$uid,'content'=>$content));//发送微信公众号客服消息
		if($action['send_pm_tpl'] == 1) R('Core/Wechat/SendTplMsg',array('uid'=>$uid,'content'=>$content));//发送微信公众号模板消息

		
        return $rs;
    }


    public function readMessage($message_id)
    {
		if($message_id){
			 return $this->where(array('uid' =>is_login(),'id' => $message_id))->setField('is_read', 1);
		}else{
			 return $this->where(array('uid' =>is_login()))->setField('is_read', 1);	
		}
    }


	//创建融云群组
	public function CreateGroup($uid, $title, $type, $img) {

		if(!$uid || !$title || !$type || !$img){
			$this->error = '群组基本信息有误'; return false;
		}

		$is = M('ImGroup')->where(array('uid'=>$uid,'type'=>$type))->find();

		if($is){
			M('ImGroup')->where(array('id'=>$is['id']))->save(array('uid'=>is_login(),'title'=>$title,'img'=>$img));
			return $is['id'];
		}

		$id = M('ImGroup')->add(array('uid'=>is_login(),'title'=>$title,'type'=>$type,'img'=>$img,'create_time'=>time()));
		$adduser = M('ImGroupUser')->add(array('uid'=>is_login(),'group_id'=>$id,'group_boss_uid'=>$uid,'title'=>$title,'img'=>$img,'create_time'=>time()));

		if(!$id && $adduser){
			$this->error = '群组创建失败'; return false;
		}

		$ret = api('Rong/groupCreate',array($uid, $id, $title));

		if($ret['code'] == 200){
			 return $id;
		}else{
			M('ImGroup')->where(array('id'=>$id))->delete();
			M('ImGroupUser')->where(array('id'=>$adduser))->delete();
			$this->error = $ret['errorMessage']; return false;
		}
    }



	//向融云群组发送消息
	public function GroupMessage($uid, $group_id, $content) {

		if(!$uid || !$group_id){
			$this->error = '群组基本信息有误'; return false;
		}

		if(!$content){
			$this->error = '消息内容不能为空'; return false;
		}

		$message['to_group_id'] = $group_id;
        $message['content'] = html($content);
        $message['uid'] = $uid;
		$message['is_read'] = 0;
        $message['create_time'] = time();
	
        $rs = M('ImMessage')->add($message);

		if($rs === false){
			$this->error = '消息发送失败';
			return false;
		}

		$json['me_uid'] = $uid;
		$json['to_group_id'] = $group_id;
		$json['content'] = html($content);
		$json['create_time'] = date('Y-m-d H:i:s');
		$json['me_userinfo'] = User($uid,array('uid','nickname','avatar128'));
		$callback = json_encode($json);

		$ret = api('Rong/messageGroupPublish',array('fromUserId'=>$uid,'toGroupId'=>$group_id,'objectName'=>'RC:TxtMsg','content'=>$callback));
		if($ret['code'] != 200){
			$this->error = $ret['errorMessage']; return false;
		}

		return true;
    }


	//向融云群组发送消息
	public function UserToGroup($uid, $group_id) {

		if(!$uid || !$group_id){
			$this->error = '群组基本信息有误'; return false;
		}

		$is =  M('ImGroup')->where(array('id'=>$group_id))->find();
		$user =  M('Member')->where(array('uid'=>$uid))->find();

		if(!$is) {
			$this->error = '不存在的群组'; return false;
		}
		if(!$user) {
			$this->error = '不存在的用户'; return false;
		}

		$in = M('ImGroupUser')->where(array('uid'=>$uid,'group_id'=>$group_id))->find();

		if($in) {
			$this->error = '已经是该群组成员了'; return false;
		}

		$res = M('ImGroupUser')->where(array('group_id'=>$group_id))->add(array('uid'=>$uid,'group_id'=>$group_id,'group_boss_uid'=>$is['uid'],'title'=>$is['title'],'img'=>$is['img'],'create_time'=>time()));

		if($res === false) {
			$this->error = '加入群组失败'; return false;
		}

		$ret = api('Rong/groupJoin',array($uid, $group_id, $is['title']));
		
		if($ret['code']!== 200){
			 M('ImGroupUser')->where(array('id'=>$res))->delete();
			 $this->error = $ret['errorMessage']; return false;
		}

		return true;
    }



	//群组会员禁言
	public function GroupNoAsk($uid, $group_id, $unlock) {

		if(!$uid || !$group_id){
			$this->error = '缺少参数uid或group_id'; return false;
		}

		$info = M('ImGroupUser')->where(array('uid'=>$uid,'group_id'=>$group_id))->find();

		if(!$info){
			$this->error = '该用户不存在该群组'; return false;
		}

		if($info['group_boss_uid']!=is_login()){
			$this->error = '你没有权限'; return false;
		}

		if($info['group_boss_uid']==$uid){
			$this->error = '群主不能禁言'; return false;
		}

		if($unlock == 1){
			$ret = M('ImGroupUser')->where(array('uid'=>$uid,'group_id'=>$group_id))->save(array('status'=>1));
			if($ret===false) {
				$this->error ='解除禁言失败';return false;
			}
			$ret = api('Rong/groupUserGagRollback',array($uid, $group_id));
		}else{
			$ret = M('ImGroupUser')->where(array('uid'=>$uid,'group_id'=>$group_id))->save(array('status'=>-1));
			if($ret===false) {
				$this->error ='禁言失败';return false;
			}
			$ret = api('Rong/groupUserGagAdd',array($uid, $group_id,999999999));
		}

		if($ret['code']!== 200){
			M('ImGroupUser')->where(array('uid'=>$uid,'group_id'=>$group_id))->save(array('status'=>1));
			$this->error = $ret['errorMessage']; return false;
		}

		 return true;
    }



	//将会员移除群组
	public function GroupKit($uid, $group_id) {

		if(!$uid || !$group_id){
			$this->error = '缺少参数uid或group_id'; return false;
		}

		$info = M('ImGroupUser')->where(array('uid'=>$uid,'group_id'=>$group_id))->find();

		if(!$info){
			$this->error = '该用户不存在该群组'; return false;
		}

		if($info['group_boss_uid']!=is_login()){
			$this->error = '你没有权限'; return false;
		}

		if($info['group_boss_uid']==$uid){
			$this->error = '群主无法移除'; return false;
		}

		$ret = api('Rong/groupQuit',array('userId'=>$uid, 'groupId'=>$group_id));

		if($ret['code']!== 200){
			$this->error = $ret['errorMessage']; return false;
		}

		$ret = M('ImGroupUser')->where(array('uid'=>$uid,'group_id'=>$group_id))->delete();

		if($ret===false) {
			$this->error ='移除群组成员失败';return false;
		}

		return true;
    }










	




	
	
	/*
 	 * 消息默认模板
     * @param $to_uid 接受消息的用户ID
     * @param string $content 内容
     */
	public function MsgTpl($key){
		
		$Tpl = array(
			'invite_pass'=>'您已经接受了好友“{nickname}”的注册邀请，正式成为我们的会员了哦',
			'invite_sucess'=>'恭喜新，您邀请的好友“{nickname}”，已经正式成为您的下线会员了哦',
			'invite_level'=>'好消息，您的团队成员“{invite_nick}”帮助您发展了新的成员“{nickname}”，快去会员中心看看吧！',
			
			'score_in'=>'账户金额提醒：系统刚刚向您的账户内支付了{price}元，您可以在会员中心进行提现，支付理由：{remark}',
			'score_out'=>'账户金额提醒：您刚刚有一笔费用支出',
			
			'brokerage_missed'=>'错失佣金提醒！,您因{time}此期间销售考核未达标，刚刚错失了一笔{price}元的佣金',
			
			'brokerage_sale'=>'您的下线“{nickname}”购买了价值{order_total}元的商品,您作为我们的{agents_name}，您获得了{price}元的销售佣金，请再接再厉！',
			'brokerage_spread'=>'您的下线“{nickname}”购买了价值{order_total}元的商品,您作为我们的{agents_name}，您获得了{price}元的差额返还，请再接再厉！',
			'brokerage_invite'=>'您邀请的会员“{nickname}”成为我们的{agents_name}，您获得了{price}元的推荐佣金，请再接再厉！',
			'brokerage_invite_up'=>'团队成员“{nickname}”升级成为了{agents_name}，现追发您{price}元的推荐佣金！',
			'brokerage_refund'=>'用户{link_nickname}的一笔订单因退款，您因该笔订单所获的{price}元的佣金取消发放，敬请谅解',
			 
			'order_is_pay'=>'您刚刚支付了一笔{price}元的订单，订单号:{order_no}，我们会以最快的速度给您发货哦！',
			'order_is_ship'=>'订单：{order_no} 现已经发货，请关注物流状态，配送方式：{com},单号：{number}',
			'order_is_get'=>'订单：{order_no} 已经确认收货，感谢您的支持，如果满意请给我好评哦',
			'order_is_delete'=>'您有一笔订单已经关闭',
			'order_is_ping'=>'感谢你评论',
			'order_refund'=>'订单：{order_no} 已经申请了退款，请耐心等待商家操作，处理结果会通过公众号发送',
			'order_refund_success'=>'订单：{order_no} 已经给您退款，退款入账方式：{type}，退款金额：{price}元，感谢您的支持，期待您的再次光临',
			'order_refund_deny'=>'商家拒绝了您订单：{order_no} 的退款请求，拒绝原因{remark}',
			
			'agents_get'=>'您已经是我们的{agents_name}了，现在起您不仅享受购物优惠，并且还能获得赚取佣金的机会',
		);
		
		if($key){
			return $Tpl[$key];
		}else{
			return $Tpl;
		}
    }
	
} 