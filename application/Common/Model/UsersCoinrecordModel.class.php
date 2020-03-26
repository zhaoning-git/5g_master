<?php

namespace Common\Model;

use Common\Model\CommonModel;

class UsersCoinrecordModel extends CommonModel {

    function _initialize() {
        parent::_initialize();
    }

    //$id 行为对应ID
    //$action 行为
    function addCoin($id, $action, $other='') {
        $id = intval($id);
        if (!$id) {
            $this->error = '参数有误!';
            return false;
        }

        $ActionName = C('UsersCoinRecordActionName');

        if (empty($action) || !array_key_exists($action, $ActionName)) {
            $this->error = '参数有误,或不支持的行为类型!';
            return false;
        }

        $insert['giftid'] = $id;
        $insert['action'] = $action;
        $insert['add_date'] = date('Ymd', NOW_TIME);
        $insert['addtime'] = NOW_TIME;

        switch ($action) {
            //充值金币
            case 'recharge_gold_coin':
                $info = M('OrderDuihuan')->where(array('id' => $id))->find();
                if (empty($info)) {
                    $this->error = '充值记录不存在!';
                    return false;
                }
                
                $uid = $info['uid'];
                $coin = $info['duihuan_gold'];
                if ($coin) {
                    $insert['type'] = 'income';
                    $insert['coin_type'] = 'gold_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $uid;
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '充值金币:'. $coin;
                }
                break;
            
            
            //签到奖励
            case 'user_signin_reward':
                $info = M('UserSignin')->where(array('id' => $id))->find();
                if (empty($info)) {
                    $this->error = '行为记录不存在!';
                    return false;
                }

                //获得奖励的用户ID
                $uid = $info['uid'];

                //获得奖励数量
                $Config = D('UserSignin')->getConfig($info['daynum']);
                $coin = $Config['coin'] ?: 0;

                $insert['type'] = 'income';
                $insert['coin_type'] = 'silver_coin';
                $insert['uid'] = $uid;
                $insert['touid'] = $uid;
                $insert['giftcount'] = $coin;
                $insert['totalcoin'] = $coin;
                $insert['showid'] = 0;
                $insert['remark'] = '签到' . $info['daynum'] . '天,获得奖励' . $coin;
                break;

            //连续签到奖励
            case 'user_signin_reward_lx':
                $info = M('UserSignin')->where(array('id' => $id))->find();
                if (empty($info)) {
                    $this->error = '行为记录不存在!';
                    return false;
                }

                //获得奖励的用户ID
                $uid = $info['uid'];

                //获得奖励数量
                $Config = D('UserSignin')->getConfig($info['daynum']);
                $coin = $Config['extra_coin'] ?: 0;

                $insert['type'] = 'income';
                $insert['coin_type'] = 'silver_coin';
                $insert['uid'] = $uid;
                $insert['touid'] = $uid;
                $insert['giftcount'] = $coin;
                $insert['totalcoin'] = $coin;
                $insert['showid'] = 0;
                $insert['remark'] = '连续签到' . $info['daynum'] . '天,获得额外奖励' . $coin;
                break;

            //邀请好友
            case 'invite_friend':
                //所要求好友必须同时绑定身份证和银行卡方算邀请成功
                $userInfo = M('Users')->where(array('id' => $id))->field('id, sf_card, sf_img')->find();

                $bank_is_verify = M('UserBank')->where(array('uid' => $id))->count();

                if (!empty($userInfo['sf_card']) && !empty($userInfo['sf_img']) && $bank_is_verify) {
                    //$id 被邀请用户的ID
                    $info = M('Users')->where(array('invite_id' => $id))->find();
                    if (empty($info)) {
                        $this->error = '行为记录不存在!';
                        return false;
                    }
                    //获得奖励的用户ID
                    $uid = $info['id'];

                    //获得奖励数量
                    $coin = D('InviteConfig')->myConfig($uid);
                    if ($coin) {
                        $insert['type'] = 'income';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $id;
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '邀请好友' . User($id, 'user_nicename') . '获得奖励:' . $coin;
                    }
                }

                break;

            //绑定微信  $id:绑定微信的用户ID 
            case 'bind_wx':
                //获得奖励的用户ID
                $uid = intval($id);
                if (User($uid, 'wx')) {
                    //查询是否已获得奖励
                    $map['uid'] = $uid;
                    $map['action'] = 'bind_wx';
                    if (!$this->where($map)->count()) {
                        $coin = M('SilverCoin')->where(array('type' => 'bind_wx'))->getField('coin');
                        if ($coin) {
                            $insert['type'] = 'income';
                            $insert['coin_type'] = 'silver_coin';
                            $insert['uid'] = $uid;
                            $insert['touid'] = $uid;
                            $insert['giftcount'] = $coin;
                            $insert['totalcoin'] = $coin;
                            $insert['showid'] = 0;
                            $insert['remark'] = '绑定微信' . User($uid, 'wx') . '获得奖励:' . $coin;
                        }
                    }
                }


                break;

            //绑定QQ  $id:绑定QQ的用户ID 
            case 'bind_qq':
                //获得奖励的用户ID
                $uid = intval($id);
                if (User($uid, 'qq')) {
                    //查询是否已获得奖励
                    $map['uid'] = $uid;
                    $map['action'] = 'bind_qq';
                    if (!$this->where($map)->count()) {
                        $coin = M('SilverCoin')->where(array('type' => 'bind_qq'))->getField('coin');
                        if ($coin) {
                            $insert['type'] = 'income';
                            $insert['coin_type'] = 'silver_coin';
                            $insert['uid'] = $uid;
                            $insert['touid'] = $uid;
                            $insert['giftcount'] = $coin;
                            $insert['totalcoin'] = $coin;
                            $insert['showid'] = 0;
                            $insert['remark'] = '绑定QQ' . User($uid, 'qq') . '获得奖励:' . $coin;
                        }
                    }
                }
                break;

            //绑定邮箱  $id:绑定邮箱的用户ID 
            case 'bind_email':
                //获得奖励的用户ID
                $uid = intval($id);
                if (User($uid, 'user_email')) {
                    //查询是否已获得奖励
                    $map['uid'] = $uid;
                    $map['action'] = 'bind_email';
                    if (!$this->where($map)->count()) {
                        $coin = M('SilverCoin')->where(array('type' => 'bind_email'))->getField('coin');
                        if ($coin) {
                            $insert['type'] = 'income';
                            $insert['coin_type'] = 'silver_coin';
                            $insert['uid'] = $uid;
                            $insert['touid'] = $uid;
                            $insert['giftcount'] = $coin;
                            $insert['totalcoin'] = $coin;
                            $insert['showid'] = 0;
                            $insert['remark'] = '绑定邮箱' . User($uid, 'user_email') . '获得奖励:' . $coin;
                        }
                    }
                }
                break;

            //绑定微博  $id:绑定微博的用户ID 
            case 'bind_weibo':
                //获得奖励的用户ID
                $uid = intval($id);
                if (User($uid, 'weibo')) {
                    //查询是否已获得奖励
                    $map['uid'] = $uid;
                    $map['action'] = 'bind_weibo';
                    if (!$this->where($map)->count()) {
                        $coin = M('SilverCoin')->where(array('type' => 'bind_weibo'))->getField('coin');
                        if ($coin) {
                            $insert['type'] = 'income';
                            $insert['coin_type'] = 'silver_coin';
                            $insert['uid'] = $uid;
                            $insert['touid'] = $uid;
                            $insert['giftcount'] = $coin;
                            $insert['totalcoin'] = $coin;
                            $insert['showid'] = 0;
                            $insert['remark'] = '绑定微博' . User($uid, 'weibo') . '获得奖励:' . $coin;
                        }
                    }
                }
                break;

            //实名认证 $id:实名认证的用户ID 
            case 'realname_verify':
                //获得奖励的用户ID
                $uid = intval($id);

                //查询是否已获得奖励
                $map['uid'] = $uid;
                $map['action'] = 'realname_verify';
                if (!$this->where($map)->count()) {
                    $where['uid'] = $uid;
                    $where['type'] = 'realname';
                    if (M('UserVerify')->where($where)->getField('is_verify') == 1) {
                        $coin = M('SilverCoin')->where(array('type' => 'realname_verify'))->getField('coin');
                        if ($coin) {
                            $insert['type'] = 'income';
                            $insert['coin_type'] = 'silver_coin';
                            $insert['uid'] = $uid;
                            $insert['touid'] = $uid;
                            $insert['giftcount'] = $coin;
                            $insert['totalcoin'] = $coin;
                            $insert['showid'] = 0;
                            $insert['remark'] = '实名认证通过获得奖励:' . $coin;
                        }
                    }
                }

                break;

            //绑定银行卡 $id:绑定银行卡的用户ID 
            case 'bind_bank_card':
                //获得奖励的用户ID
                $uid = intval($id);

                //查询是否已获得奖励
                $map['uid'] = $uid;
                $map['action'] = 'bind_bank_card';
                if (!$this->where($map)->count()) {
                    $where['uid'] = $uid;
                    if (M('UserBank')->where($where)->count()) {
                        $coin = M('SilverCoin')->where(array('type' => 'bind_bank_card'))->getField('coin');
                        if ($coin) {
                            $insert['type'] = 'income';
                            $insert['coin_type'] = 'silver_coin';
                            $insert['uid'] = $uid;
                            $insert['touid'] = $uid;
                            $insert['giftcount'] = $coin;
                            $insert['totalcoin'] = $coin;
                            $insert['showid'] = 0;
                            $insert['remark'] = '绑定银行卡获得奖励:' . $coin;
                        }
                    }
                }
                break;

            //刷脸认证 $id:刷脸认证的用户ID 
            case 'facescan_verify':
                //获得奖励的用户ID
                $uid = intval($id);

                //查询是否已获得奖励
                $map['uid'] = $uid;
                $map['action'] = 'facescan_verify';
                if (!$this->where($map)->count()) {
                    $where['uid'] = $uid;
                    $where['type'] = 'facescan';
                    if (M('UserVerify')->where($where)->getField('is_verify') == 1) {
                        $coin = M('SilverCoin')->where(array('type' => 'facescan_verify'))->getField('coin');
                        if ($coin) {
                            $insert['type'] = 'income';
                            $insert['coin_type'] = 'silver_coin';
                            $insert['uid'] = $uid;
                            $insert['touid'] = $uid;
                            $insert['giftcount'] = $coin;
                            $insert['totalcoin'] = $coin;
                            $insert['showid'] = 0;
                            $insert['remark'] = '刷脸认证通过获得奖励:' . $coin;
                        }
                    }
                }

                break;

            //分享朋友圈 $id:分享记录ID 
            case 'share_friends':
                $ShareInfo = M('ShareFriend')->where(array('id' => $id))->find();

                //获得奖励的用户ID
                $uid = $ShareInfo['uid'];

                //查询是否已获得奖励
                $map['uid'] = $uid;
                $map['action'] = 'share_friends';

                $start = strtotime(date('Y-m-d', NOW_TIME));
                $end = $start + 86399;
                $map['addtime'] = array(array('EGT', $start), array('ELT', $end)); //今日时间戳范围
                if (!$this->where($map)->count()) {
                    $coin = M('SilverCoin')->where(array('type' => 'share_friends'))->getField('coin');
                    if ($coin) {
                        $insert['type'] = 'income';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $uid;
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '分享朋友圈获得奖励:' . $coin;
                    }
                }

                break;

            //直播
            case 'share_friends':

                break;

            //观看直播
            case 'look_live_play':

                break;

            //发布主题帖
            case 'post_show':
                $ShowInfo = M('Show')->where(array('id' => $id))->find();

                //发帖用户ID
                $uid = $ShowInfo['uid'];

                $Config = M('SilverCoin')->where(array('type' => 'post_show'))->find();

                //每日上限25银币
                $map['uid'] = $uid;
                $map['action'] = 'post_show';
                $start = strtotime(date('Y-m-d', NOW_TIME));
                $end = $start + 86399;
                $map['addtime'] = array(array('EGT', $start), array('ELT', $end)); //今日时间戳范围
                $TodayCoin = $this->where($map)->Sum('giftcount');
                if ($TodayCoin < $Config['limit']) {
                    $coin = $Config['coin'];
                    if ($TodayCoin + $coin > $Config['limit']) {
                        $coin = $Config['limit'] - $TodayCoin;
                    }

                    if ($coin) {
                        $insert['type'] = 'income';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $uid;
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '发布主题帖获得奖励:' . $coin;
                    }
                }
                break;

            //回复贴
            case 'reply_show':
                $CommentInfo = M('Comment')->where(array('id' => $id))->find();
                //发帖用户ID
                $uid = $CommentInfo['uid'];

                $Config = M('SilverCoin')->where(array('type' => 'reply_show'))->find();

                //每日上限10银币
                $map['uid'] = $uid;
                $map['action'] = 'reply_show';
                $start = strtotime(date('Y-m-d', NOW_TIME));
                $end = $start + 86399;
                $map['addtime'] = array(array('EGT', $start), array('ELT', $end)); //今日时间戳范围
                $TodayCoin = $this->where($map)->Sum('giftcount');
                if ($TodayCoin < $Config['limit']) {
                    $coin = $Config['coin'];
                    if ($TodayCoin + $coin > $Config['limit']) {
                        $coin = $Config['limit'] - $TodayCoin;
                    }

                    if ($coin) {
                        $insert['type'] = 'income';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $uid;
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '回复贴获得奖励:' . $coin;
                    }
                }
                break;

            //被删帖 $id 被删帖子ID
            case 'del_show':
                $ShowInfo = M('Show')->where(array('id' => $id))->find();

                //发帖用户ID
                $uid = $ShowInfo['uid'];
                $coin = M('SilverCoin')->where(array('type' => 'del_show'))->getField('coin');
                
                //会员特权删帖免扣银币≤380
                $mianCoin = userPriv($uid, 6);
                if($mianCoin && $mianCoin>= 0){
                    $coin = $coin - $mianCoin;
                    $coin = $coin > 0 ? $coin  :0;
                    $mianCoinTxt = ',(会员特权删帖免扣银币'.$mianCoin.')';
                }
                
                if ($coin) {
                    $insert['type'] = 'expend';
                    $insert['coin_type'] = 'silver_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $uid;
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '删帖扣除银币:' . $coin.$mianCoinTxt;
                }
                break;

            //在线时长
            case 'online_time':
                $Useronline = M('UserOnline')->where(array('id' => $id))->find();

                //用户ID
                $uid = $Useronline['uid'];

                $Config = M('SilverCoin')->where(array('type' => 'online_time'))->find();

                $map['uid'] = $uid;
                $map['action'] = 'online_time';

                //每30分钟5银币
                $Minute = 30 * 60;
                $LastTime = $this->where($map)->order('addtime DESC')->getField('addtime');
                if (empty($LastTime) || $LastTime + $Minute <= NOW_TIME) {
                    $start = strtotime(date('Y-m-d', NOW_TIME));
                    $end = $start + 86399;
                    $map['addtime'] = array(array('EGT', $start), array('ELT', $end)); //今日时间戳范围
                    $TodayCoin = $this->where($map)->Sum('giftcount');
                    //每日限额25
                    if ($TodayCoin < $Config['limit']) {
                        $coin = $Config['coin'];
                        if ($TodayCoin + $coin > $Config['limit']) {
                            $coin = $Config['limit'] - $TodayCoin;
                        }
                        if ($coin) {
                            $insert['type'] = 'income';
                            $insert['coin_type'] = 'silver_coin';
                            $insert['uid'] = $uid;
                            $insert['touid'] = $uid;
                            $insert['giftcount'] = $coin;
                            $insert['totalcoin'] = $coin;
                            $insert['showid'] = 0;
                            $insert['remark'] = '在线得奖励:' . $coin;
                        }
                    }
                }
                break;

            //幸运轮盘
            case 'luck_lottery':
                $LotteryLog = M('LotteryLog')->where(array('id' => $id))->find();
                if (empty($LotteryLog)) {
                    $this->error = '中奖记录不存在!';
                    return false;
                }
                
                if($LotteryLog['status'] != 1){
                    $this->error = '中奖记录状态错误!';
                    return false;
                }
                
                $uid = $LotteryLog['uid'];
                $coin = $LotteryLog['cost'];
                if ($coin) {
                    $insert['type'] = 'income';
                    $insert['coin_type'] = 'silver_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $uid;
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '幸运大转盘获得奖励:' . $coin;
                }
                break;

            //幸运轮盘会员特权额外增加
            //$other:参加幸运轮盘获得银币数量
            case 'luck_lottery_priv':
                $uid = $id;
                $ratio = userPriv($uid, 4); //轮盘宝箱红包获得银币增加百分比
                if($ratio){
                    $coin = $other * $ratio/100;
                    if ($coin) {
                        $insert['type'] = 'income';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $uid;
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '幸运轮盘会员特权额外获得奖励:' . $coin;
                    }
                }
                break;
                
            //黄金宝箱
            case 'gold_box':
                $LotteryLog = M('LotteryLog')->where(array('id' => $id))->find();
                if (empty($LotteryLog)) {
                    $this->error = '中奖记录不存在!';
                    return false;
                }
                
                if($LotteryLog['status'] != 1){
                    $this->error = '中奖记录状态错误!';
                    return false;
                }
                
                $uid = $LotteryLog['uid'];
                $coin = $LotteryLog['cost'];
                if ($coin) {
                    $insert['type'] = 'income';
                    $insert['coin_type'] = 'silver_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $uid;
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '黄金宝箱获得奖励:' . $coin;
                }
                break;
            
            //黄金宝箱会员特权额外增加
            //$other:参加幸运轮盘获得银币数量
            case 'gold_box_priv':
                $uid = $id;
                $ratio = userPriv($uid, 4); //轮盘宝箱红包获得银币增加百分比
                if($ratio){
                    $coin = $other * $ratio/100;
                    if ($coin) {
                        $insert['type'] = 'income';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $uid;
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '黄金宝箱会员特权额外获得奖励:' . $coin;
                    }
                }
                break;
                
            //参加黄金宝箱 扣除银币
            case 'cj_gold_box':
                $LotteryLog = M('LotteryLog')->where(array('id' => $id))->find();
                if (empty($LotteryLog)) {
                    $this->error = '中奖记录不存在!';
                    return false;
                }
                
                $uid = $LotteryLog['uid'];
                $coin = C('CJ_GoldBox_Coin');
                if ($coin) {
                    $insert['type'] = 'expend';
                    $insert['coin_type'] = 'silver_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $uid;
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '参加黄金宝箱消耗银币:' . $coin;
                }
                
                break;
              
            //神秘红包
            case 'red_pack':
                $LotteryLog = M('LotteryLog')->where(array('id' => $id))->find();
                if (empty($LotteryLog)) {
                    $this->error = '中奖记录不存在!';
                    return false;
                }
                
                if($LotteryLog['status'] != 1){
                    $this->error = '中奖记录状态错误!';
                    return false;
                }
                
                $uid = $LotteryLog['uid'];
                $coin = $LotteryLog['cost'];
                if ($coin) {
                    $insert['type'] = 'income';
                    $insert['coin_type'] = 'silver_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $uid;
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '神秘红包获得奖励:' . $coin;
                }
                break;
            
            //神秘红包会员特权额外增加
            //$other:参加幸运轮盘获得银币数量
            case 'red_pack_priv':
                $uid = $id;
                $ratio = userPriv($uid, 4); //轮盘宝箱红包获得银币增加百分比
                if($ratio){
                    $coin = $other * $ratio/100;
                    if ($coin) {
                        $insert['type'] = 'income';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $uid;
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '神秘红包会员特权额外获得奖励:' . $coin;
                    }
                }
                break;
            
            //竞猜有奖
            case 'jing_cai_reward':
                $log = M('JingcaiReceiveLog')->where(array('id'=>$id))->find();
                if (empty($log)) {
                    $this->error = '奖励记录不存在!';
                    return false;
                }
                
                if($log['status']){ //状态 0:未领取 1:已领取(银币奖励在签到后自动发放到个人账户中)
                    $this->error = '奖励记录状态错误!';
                    return false;
                }
                
                $uid  = $log['uid'];
                $coin = $log['coin'];
                if ($coin) {
                    $insert['type'] = 'income';
                    $insert['coin_type'] = 'silver_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $uid;
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '竞猜获得奖励:' . $coin;
                }
                break;
             
            //发起竞猜扣除银币(底注) 已废弃:发起竞猜是冻结底注
            case 'jing_cai_bets':
                $jingcai = M('Jingcai')->where(array('id'=>$id))->find();
                $uid  = $jingcai['uid'];
                $coin = $jingcai['maxbets'];
                if ($coin) {
                    $insert['type'] = 'expend';
                    $insert['coin_type'] = 'silver_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $uid;
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '发起竞猜扣除银币:' . $coin;
                }
                
                break;
            
            //发起竞猜赢银币
            //$id 是"参与竞猜记录"ID
            //$JingcaiLog['profit'] 如果是负数 则发起者赢
            case 'fq_jing_cai_ying':
                $JingcaiLog = M('JingcaiLog')->where(array('id'=>$id))->find();
                
                $uid  = $JingcaiLog['fq_uid'];
                if($JingcaiLog['profit'] < 0){
                    $coin = abs($JingcaiLog['profit']);
                    if ($coin) {
                        $insert['type'] = 'income';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $JingcaiLog['uid'];
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '发起竞猜赢银币:' . $coin;
                    }
                }
                break;
            
            //发起竞猜输银币
            //$id 是"参与竞猜记录"ID
            //$JingcaiLog['profit'] 如果是正数 则发起者输
            case 'fq_jing_cai_shu':
                $JingcaiLog = M('JingcaiLog')->where(array('id'=>$id))->find();
                $uid  = $JingcaiLog['fq_uid'];
                if($JingcaiLog['profit'] > 0){
                    M('Users')->where(array('id' => $uid))->setInc('silver_coin', $JingcaiLog['profit']);
                    $coin = $JingcaiLog['profit'];
                    if ($coin) {
                        $insert['type'] = 'expend';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $JingcaiLog['uid'];
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '发起竞猜输银币:' . $coin;
                    }
                }
                
                break;
             
            //发起竞猜者获得参与者下注金额    
            case 'fq_getcy_frozen_betting':
                $JingcaiLog = M('JingcaiLog')->where(array('id'=>$id))->find();
                $uid  = $JingcaiLog['fq_uid'];
                $coin = $JingcaiLog['frozen_betting'];
                if ($coin) {
                    $insert['type'] = 'income';
                    $insert['coin_type'] = 'silver_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $JingcaiLog['uid'];
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '发起竞猜者获得参与者下注金额:' . $coin;
                }
                break;
            
            //返回发起者剩余冻结底注
            //$id 是"竞猜"ID    
            case 'fq_fanhui_frozen_bets':
                $Jingcai = M('Jingcai')->where(array('id'=>$id))->find();
                $uid  = $Jingcai['uid'];
                $coin = $Jingcai['frozen_bets'];
                if ($coin) {
                    $insert['type'] = 'income';
                    $insert['coin_type'] = 'silver_coin';
                    $insert['uid'] = $uid;
                    $insert['touid'] = $uid;
                    $insert['giftcount'] = $coin;
                    $insert['totalcoin'] = $coin;
                    $insert['showid'] = 0;
                    $insert['remark'] = '返回发起者剩余冻结底注:' . $coin;
                }
                break;
                
            //参与竞猜赢银币
            //$id 是"参与竞猜记录"ID
            //$JingcaiLog['profit'] 如果是正数 则参与者赢
            case 'cy_jing_cai_ying':
                $JingcaiLog = M('JingcaiLog')->where(array('id'=>$id))->find();
                $uid  = $JingcaiLog['uid'];
                if($JingcaiLog['profit'] > 0){
                    $coin = $JingcaiLog['profit'];
                    if ($coin) {
                        $insert['type'] = 'income';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $JingcaiLog['fq_uid'];
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '参与竞猜赢银币:' . $coin;
                    }
                }
                break;
                
            //参与竞猜输银币 需要先返回冻结的下注金额
            //$id 是"参与竞猜记录"ID
            //$JingcaiLog['profit'] 如果是负数 则参与者输
            case 'cy_jing_cai_shu':
                $JingcaiLog = M('JingcaiLog')->where(array('id'=>$id))->find();
                $uid  = $JingcaiLog['uid'];
                if($JingcaiLog['profit'] < 0){
                    M('Users')->where(array('id' => $uid))->setInc('silver_coin', $JingcaiLog['betting']);
                    $coin = $JingcaiLog['betting'];
                    if ($coin) {
                        $insert['type'] = 'expend';
                        $insert['coin_type'] = 'silver_coin';
                        $insert['uid'] = $uid;
                        $insert['touid'] = $JingcaiLog['fq_uid'];
                        $insert['giftcount'] = $coin;
                        $insert['totalcoin'] = $coin;
                        $insert['showid'] = 0;
                        $insert['remark'] = '参与竞猜输银币:' . $coin;
                    }
                }
                break;
            
            
            
            default :
                $coin = false;
                $uid = false;
        }

        if ($coin && $uid) {
            if ($this->add($insert)) {
                if ($insert['type'] == 'income') {
                    $func = 'setInc';
                    RedPack('yinbi', $coin);
                    upLevel($uid);
                } 
                
                elseif ($insert['type'] == 'expend') {
                    $func = 'setDec';
                    if($coin > M('Users')->where(array('id' => $uid))->getField($insert['coin_type'])){
                        $this->error = $insert['coin_type']=='silver_coin'?'银币数量不足':'金币数量不足';
                        return false;
                    }
                }
                
                M('Users')->where(array('id' => $uid))->$func($insert['coin_type'], $coin);
                
                return $coin;
            }
        }
        return false;
    }

    //设置记录列表
    function setList($list) {
        if (!empty($list)) {
            $ActionName = C('UsersCoinRecordActionName');
            foreach ($list as &$value) {
                $value['type_txt'] = $value['type'] == 'income' ? '收入' : '支出';
                $value['coin_type_txt'] = $value['coin_type'] == 'silver_coin' ? '银币' : '金币';
                $value['action_txt'] = $ActionName[$value['action']];
                $value['userInfo'] = User($value['uid']);
            }
        }
        return $list;
    }

}
