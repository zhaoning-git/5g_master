<?php

//幸运转盘

namespace Common\Model;

use Think\Model;

class LotteryModel extends Model {

    public $WelfareName;
    
    protected $_validate = array(
        array('title', 'require', '奖品标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('type', 'require', '奖品类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cost', 'require', '奖品价值不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('chance', 'require', '中奖概率不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('welfare', 'require', '福利类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    function _initialize() {
        parent::_initialize();
        $this->WelfareName = array('luckturn'=>'幸运转盘', 'goldbox'=>'黄金宝箱', 'redpack'=>'神秘红包');
    }


    //添加修改转盘
    function setLottery($data) {
        $data = $this->create($data);
        
        if (!$data) {
            $this->error = $this->getError();
            return false;
        }

        if (floor($data['chance']) != $data['chance']) {
            $this->error = '中奖概率必须是整数!';
            return false;
        }

        if($this->checkstr($data['cost'], ',')){
            $data['cost'] = $this->checkNum(explode(',', $data['cost']), ',');
            if(!$data['cost']){
                $this->error = '请输入正整数!';
                return false;
            }else{
                $data['cost'] = implode(',', $data['cost']);
            }
        }
        
        elseif($this->checkstr($data['cost'], '-')){
            $data['cost'] = $this->checkNum(explode('-', $data['cost']), '-');
            if(!$data['cost']){
                $this->error = '请输入正整数!';
                return false;
            }else{
                $data['cost'] = implode('-', $data['cost']);
            }
        }
        
        
        
        if(!$data['cost'] || empty($data['cost'])){
            $this->error = '奖品价值不能为空!';
            return false;
        }
        
        
        $id = intval($data['id']);
        $info = $this->where(array('id' => $id))->find();

        //添加
        if (empty($info)) {
            $data['welfare_name'] = $this->WelfareName[$data['welfare']];
            $data['create_time'] = NOW_TIME;
            $Result = $this->add($data);
        }

        //更新
        else {
            $data['up_time'] = NOW_TIME;
            $Result = $this->where(array('id' => $id))->field('title,cost,chance,up_time')->save($data);
        }
        
        return $Result;
        
    }

    //参加幸运转盘抽奖
    function inLottery($uid) {
        //判断抽奖次数
        if (!$this->getLotteryNum($uid)) {
            $this->error = '您的抽奖次数已用完!';
            return false;
        }

        $rid = $this->getRid('luckturn', $uid);
        if(!$rid){
            return false;
        }

        $create_time = NOW_TIME;
        $token = $this->getToken($uid, $rid, $create_time);
        
        $Result['lid'] = $this->addLotteryLog($uid, $rid, $token, $create_time);
        $Result['token'] = $token;
        $Result['lottery'] = $this->field('id,title')->select();
        return $Result;
    }

    //参加黄金宝箱
    //每人每日开宝箱次数不限 每次宝箱消耗100银币
    function inGoldBox($uid){
        //参加黄金宝箱消耗银币数量
        $coin = C('CJ_GoldBox_Coin');
        
        //判断拥有银币数量
        $silver_coin = M('Users')->where(array('id' => $uid))->getField('silver_coin');
        if($silver_coin < $coin){
            $this->error = '参加黄金宝箱需消耗银币'.$coin.',您银币数量不足!';
            return false;
        }
        
        
        $rid = $this->getRid('goldbox', $uid);
        if(!$rid){
            return false;
        }

        $create_time = NOW_TIME;
        $token = $this->getToken($uid, $rid, $create_time);
        
        $Result['lid'] = $this->addLotteryLog($uid, $rid, $token, $create_time);
        $Result['token'] = $token;
        $Result['lottery'] = $this->field('id,title')->select();
        return $Result;
    }

    //抢红包
    function inRedPack($uid, $rplogid, $token){
        $redis = Redis();
        $RedpackLogInfo = $redis->get('LotteryRedpackLog_info_'.$rplogid);
        $RedpackLogInfo = unserialize($RedpackLogInfo);
        if(empty($RedpackLogInfo)){
            $this->error = '红包不存在!';
            return false;
        }elseif($RedpackLogInfo['token'] != $token){
            $this->error = 'token参数有误!';
            return false;
        }
        
        $RedpackNum = $redis->decr($token);//红包数量
        if($RedpackNum < 0){
            $redis->del($token);
            $redis->del('LotteryRedpackLog_info_'.$rplogid);
            $this->error = '红包已抢完!';
            return false;
        }else{
            M('LotteryRedpackLog')->where(array('id'=>$RedpackLogInfo['id']))->setField('is_open', 1);
            $RedPack = $this->openRedPack($uid);
            $Result  = $this->getResult($RedPack['lid'], $RedPack['token']);
            return $Result;
        }
    }
    
    //神秘红包开奖 该$uid用户已经抢到红包
    function openRedPack($uid){
        $rid = $this->getRid('redpack', $uid);
        if(!$rid){
            return false;
        }

        $create_time = NOW_TIME;
        $token = $this->getToken($uid, $rid, $create_time);
        
        $Result['lid'] = $this->addLotteryLog($uid, $rid, $token, $create_time);
        $Result['token'] = $token;
        $Result['lottery'] = $this->field('id,title')->select();
        return $Result;
    }

    //获取抽奖结果
    //$lid: 表LotteryLog的主键ID
    function getResult($lid, $token) {
        if (empty($token)) {
            $this->error = 'token不能为空!';
            return false;
        }

        $info = M('LotteryLog')->where(array('id' => intval($lid)))->find();
        if (empty($info)) {
            $this->error = '抽奖记录不存在!';
            return false;
        }

        if ($info['status']) {
            $this->error = '抽奖已结束!';
            return false;
        }

        if (!$this->cToken($token, $info)) {
            $this->error = '数据异常,本次抽奖无效!';
            return false;
        } else {
            $up['status'] = 1;
        }
        $up['hit_time'] = NOW_TIME;
        M('LotteryLog')->where(array('id' => $info['id']))->save($up);

        //发放奖品
        $this->giveout($info['id']);

        $LotteryInfo = $this->where(array('id'=>$info['lottery_id']))->find();
        $return = array(
            'lid' => $info['id'],
            'rid' => $info['lottery_id'],
            'title'=>$LotteryInfo['title'], 
            'cost'=>$info['cost'],
            'text'=>'恭喜您获得'.$LotteryInfo['title'].''
        );
        
        return $return;
    }

    //发放奖品
    function giveout($logid) {
        //奖品记录
        $info = M('LotteryLog')->where(array('id' => $logid))->find();
        if (empty($info)) {
            $this->error = '中奖记录不存在!';
            return false;
        }

        if ($info['status'] != 1) {
            $this->error = '抽奖未成功,无法发放奖品!';
            return false;
        }

        //奖品
        $lottery = $this->where(array('id' => $info['lottery_id']))->find();
        
        //如果是黄金宝箱则扣除费用
        if($info['welfare'] == 'goldbox'){
            $Coin = Coin($info['id'], 'cj_gold_box');
            //扣费失败
            if(!is_numeric($Coin)){
                M('LotteryLog')->where(array('id' => $logid))->getField('status', 2);
                $this->error = $Coin;
                return false;
            }
        }
        
        if(strstr($lottery['type'], 'CouponHuiyuan')){
            $CouponType = 'CouponHuiyuan';
        }else{
            $CouponType = $lottery['type'];
        }
        
        switch ($CouponType) {
            //金币立减券
            case 'CouponGold':
                if(D('Coupon')->addGold($info['uid'], $info['cost'], $info['welfare'], $info['id'])){
                    M('LotteryLog')->where(array('id' => $info['id']))->setField('giveout', 1);
                }
                break;

            //会员抵扣券
            case 'CouponDikou':
                if(D('Coupon')->addDikou($info['uid'], $info['cost'], $info['welfare'], $info['id'])){
                    M('LotteryLog')->where(array('id' => $info['id']))->setField('giveout', 1);
                }

                break;

            //银币 银币是立即发放到账户
            case 'CouponSilver':
                if($info['welfare'] == 'luckturn'){
                    $CoinType = 'luck_lottery';
                }
                elseif($info['welfare'] == 'goldbox'){
                    $CoinType = 'gold_box';
                }
                
                elseif($info['welfare'] == 'redpack'){
                    $CoinType = 'red_pack';
                }
                
                $Coin = Coin($info['id'], $CoinType);
                if(is_numeric($Coin)){
                    M('LotteryLog')->where(array('id' => $info['id']))->setField('giveout', 1);
                }
                
                //会员特权额外增加
                Coin($info['uid'], $CoinType.'_priv');
                
                break;
            
            //竞猜加奖券 百分比
            case 'CouponJingcai':
                if(D('Coupon')->addJingcai($info['uid'], $info['cost'], $info['welfare'], $info['id'])){
                    M('LotteryLog')->where(array('id' => $info['id']))->setField('giveout', 1);
                }
                break;
            
            //万能合并券
            case 'CouponMerge':
                if(D('Coupon')->addMerge($info['uid'], $info['cost'], $info['welfare'], $info['id'])){
                    M('LotteryLog')->where(array('id' => $info['id']))->setField('giveout', 1);
                }
                break;
            
            //万能延时券
            case 'CouponDelay':
                
                break;
            
            //会员权益券
            case 'CouponQuanyi':
                if(D('Coupon')->addQuanyi($info['uid'], $info['cost'], $info['welfare'], $info['id'])){
                    M('LotteryLog')->where(array('id' => $info['id']))->setField('giveout', 1);
                }
                
                break;
                
            //会员兑换券
            case 'CouponHuiyuan':
                $info['type'] = $lottery['type'];
                if(D('Coupon')->addHuiyuan($info)){
                    M('LotteryLog')->where(array('id' => $info['id']))->setField('giveout', 1);
                }else{
                    $this->error = D('Coupon')->getError();
                    return false;
                }
            break;
            
                
            
            
        }
    }

    function Cost($cost) {
        if($this->checkstr($cost, ',')){
            $cost = array_unique(explode(',', $cost));
            $key = array_rand($cost, 1);
            return $cost[$key];
            
        }
        
        elseif($this->checkstr($cost, '-')){
            $cost = explode('-', $cost);
            return mt_rand($cost[0], $cost[1]);
        }
        
        else{
            return $cost;
        }
        
    }

    function checkstr($str, $needle) {
        $tmparray = explode($needle, $str);
        if (count($tmparray) > 1) {
            return true;
        } else {
            return false;
        }
    }

    function checkNum($cost, $needle){
        $cost = array_filter(array_unique($cost));
        
        foreach ($cost as $Num){
            if(!is_numeric($Num) || !intval($Num)){
                return false;
            }
        }
        
        if($needle == '-'){
            if(!$cost[0] || $cost[0] <= 0){
                return false;
            }
            if(!$cost[1] || $cost[1] <= 0){
                return false;
            }
        }
        
        return $cost;
    }


    //获取中奖奖品ID
    function getRid($welfare, $uid){
        $map['uid'] = $uid;
        $map['status'] = 0;
        $map['welfare'] = $welfare;
        if (M('LotteryLog')->where($map)->count()) {
            $this->error = '您还有未开奖的记录';
            return false;
        }

        $prize_arr = $this->LotteryInfo($welfare);
        if(!empty($prize_arr)){
            foreach ($prize_arr as $key => $val) {
                $arr[$val['id']] = $val['v'];
            }

            $rid = $this->GetRand($arr); //根据概率获取奖项id
            return $rid;
        } else {
            return false;
        }
    }

    //生成token
    function getToken($uid, $rid, $create_time){
        $token = M('UserToken')->where(array('uid' => $uid))->getField('token');
        $token = sha1($create_time . $token . $uid . $rid);
        return $token;
    }

    //验证token
    function cToken($token, $LotteryLogInfo){
        $UserToken = M('UserToken')->where(array('uid' => $LotteryLogInfo['uid']))->getField('token');
        $_token = sha1($LotteryLogInfo['create_time'] . $UserToken . $LotteryLogInfo['uid'] . $LotteryLogInfo['lottery_id']);
        return $token == $_token;
    }

    //添加中奖记录
    function addLotteryLog($uid, $rid, $token, $create_time){
        //奖品
        $lottery = $this->where(array('id' => $rid))->find();
        
        //优惠券金额,这里根据券的不同单位也不同,
        //金币立减券:立减人民币,
        //竞猜加奖券:银币的百分比
        //万能合并券:合并上限
        //万能延时券:延时天数
        //会员抵扣券:抵扣人民币
        $Cost = $this->Cost($lottery['cost']);
        
        $data['welfare'] = $lottery['welfare'];
        $data['lottery_id'] = $rid;
        $data['uid'] = $uid;
        $data['token'] = $token;
        $data['cost']  = $Cost;
        $data['status'] = 0;
        $data['create_time'] = $create_time;
        $id = M('LotteryLog')->add($data);
        return $id;
    }

    //奖品信息
    function LotteryInfo($welfare) {
        $list = $this->where(array('welfare'=>$welfare))->select();
        if(empty($list)){
            $this->error = '奖品不存在!';
            return true;
        }
        
        foreach ($list as $value) {
            $_data['id'] = $value['id'];
            $_data['prize'] = $value['title'];
            $_data['v'] = $value['chance'];
            $data[$value['id']] = $_data;
        }
        return $data;
    }

    //概率计算
    function GetRand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proArr);
        return $result;
    }

    //获取有效抽奖次数
    function getLotteryNum($uid) {
        return 100;
        $start = strtotime(date('Y-m-d', NOW_TIME));
        $end = $start + 86399;

        $map['uid'] = $uid;
        $map['status'] = 1;
        $map['create_time'] = array(array('EGT', $start), array('ELT', $end)); //今日时间戳范围
        //已抽奖次数
        $logNum = M('LotteryLog')->where($map)->count();

        //每人每日最多有20次免费抽奖机会
        if ($logNum >= 20) {
            $this->error = '每人每日最多有20次免费抽奖机会';
            return false;
        }

        //进入本活动即有1次免费抽奖机会
        $Num = 1;

        //会员特权次数
        $userPriv = userPriv($uid, 7)?:0;
        $Num = $Num + $userPriv;
        
        //当天邀请新注册用户每成功1个即可1次抽奖机会,最多累加5次
        unset($map);
        $map['invite_id'] = $uid;
        $map['user_status'] = 1;
        $map['create_time'] = array(array('EGT', $start), array('ELT', $end)); //今日时间戳范围
        $userNum = M('Users')->where($map)->count();
        if ($userNum >= 5) {
            $userNum = 5;
        }

        $Num = $Num + $userNum;

        //当天竞猜2次即可1次抽奖机会,最多累加5次 (竞猜功能做好后再继续完善)
        $jcNum = 0;
        $Num = $Num + $jcNum;

        //剩余次数
        $syNum = $Num - $logNum;

        if ($syNum > 0) {
            return $syNum;
        } else {
            $this->error = '您的抽奖次数已用完!';
            return false;
        }
    }

}
