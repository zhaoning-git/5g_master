<?php

//资金记录管理
namespace Common\Model;
use Think\Model;

class AccountLogModel extends Model {

    //$type 变动类型 
    //0:鲜花兑换金币 1:金币兑换现金 2:现金提现 3:提现通过审核
    //4:提现未通过审核 5:购买金币 6:推荐注册送金币 7:认证 8:充值
    function addlog($uid, $number, $type, $other=''){
        $uid = intval($uid);
        if(!$uid){
            $this->error = '用户ID错误!';
            return false;
        }
        $number = intval($number);
        if(!$number){
            $this->error = '请输入正确的数量!';
            return false;
        }
        
        switch (intval($type)){
            //鲜花兑换金币
            case 0:
                $Result = $this->getToGold($uid,$number);
                if(!$Result){
                    return false;
                }
                
                $_number = $Result['_number'];
                $gain_number = $Result['gain_number'];
                
                $save['flower'] = array('exp',"flower-$_number");
                $save['gold'] = array('exp',"gold+$gain_number");
                
                $data['change_desc'] = $_number.'鲜花兑换金币'.$gain_number;

            break;
            
            //金币兑换现金
            case 1:
                $Result = $this->getToMoney($uid,$number);
                if(!$Result){
                    return false;
                }
                
                $_number = $Result['_number'];
                $gain_number = $Result['gain_number'];
                
                $save['gold'] = array('exp',"gold-$_number");
                $save['money'] = array('exp',"money+$gain_number");
                
                $data['change_desc'] = $_number.'金币兑换现金'.$gain_number;
            break;
            
            //鲜花兑换现金
            case 9:
                $Result = $this->FlowerToMoney($uid,$number);
                if(!$Result){
                    return false;
                }
                
                $_number = $Result['_number'];
                $gain_number = $Result['gain_number'];
                
                $save['flower'] = array('exp',"flower-$_number");
                $save['money'] = array('exp',"money+$gain_number");
                
                $data['change_desc'] = $_number.'鲜花兑换现金'.$gain_number;
                
                break;
            
            //现金提现
            case 2:
                $Result = $this->checkMoney($uid,$number);
                if(!$Result){
                    return false;
                }
                
                //提现金额
                $_number = $Result['_number'];
                
                //实际到账金额
                $gain_number = $Result['gain_number'];
                
                $save['money'] = array('exp',"money-$_number");
                $save['no_money'] = array('exp',"no_money+$_number");
                
                $data['change_desc'] = '提现申请提交成功,请等待管理员审核!';
                
                if(empty($other)){
                    $this->error = '提现支付宝账号不能为空!';
                    return false;
                }
                
                $data['alipay'] = $other;//提现支付宝账号
                
                break;
                
            //现金兑换金币
            case 5:
                $Result = $this->checkTogold($uid,$number);
                if(!$Result){
                    return false;
                }
                
                //使用的现金数量
                $_number = $Result['_number'];
                
                //兑换后得到的金币
                $gain_number = $Result['gain_number'];
                
                $save['gold'] = array('exp',"gold + $gain_number");
                $save['money'] = array('exp',"money-$_number");
                
                $data['change_desc'] = $_number.'现金兑换金币'.$gain_number;
                break;
            
            //推荐注册送金币
            case 6:
                $_number = $number;
                $save['gold'] = array('exp',"gold + $_number");
                $data['change_desc'] = $other?:'推荐注册获得金币!';
                break;
            
            //认证
            case 7:
                //认证需扣除的现金数量
                $gain_number = $number;
                
                $_number = $gain_number;
                
                $save['money'] = array('exp',"money-$gain_number");
                $data['change_desc'] = '申请店铺认证扣除现金'.$gain_number;
                break;
                
            //现金充值
            case 8:
                //充值后实际到账金额
                $gain_number = $number;
                
                $_number = $gain_number;
                
                $save['money'] = array('exp',"money+$gain_number");
                $data['change_desc'] = '充值现金'.$gain_number;
                break;
            
            default :
                $this->error = '未知的变动类型';
                return false;
        }
        
        if(M('UcenterMember')->where(array('id'=>$uid))->save($save)){
            CleanUser($uid);
            $userInfo = User($uid,array('uid','flower','gold','money','no_money'));

            $data['uid'] = $userInfo['uid'];
            $data['flower'] = $userInfo['flower'];
            $data['gold'] = $userInfo['gold'];
            $data['money'] = $userInfo['money'];
            $data['no_money'] = $userInfo['no_money'];
            $data['change_number'] = $_number;
            
            $data['gain_number'] = $gain_number?:0;
            
            $data['change_time'] = time();
            $data['change_type'] = $type;
            
            $accountlog_id = $this->add($data);
            
            if($accountlog_id){
                //现金充值
                if($type == 8){
                    M('Recharge')->where(array('id'=>intval($other)))->setField('accountlog_id',$accountlog_id);
                    D('AgentsLog')->Insert(array('uid'=>$uid,'money'=>$gain_number,'type'=>2));
                    return true;
                }
                
                //认证扣费成功后,在Store表中accountlog_id不为0,is_privilege为0的时候就是等待审核认证的店铺
                elseif($type == 7){
                    $save['is_privilege'] = 1;
                    $save['accountlog_id'] = $accountlog_id;
                    M('Store')->where(array('uid'=>intval($uid)))->save($save);
                    D('AgentsLog')->Insert(array('uid'=>$uid,'money'=>$gain_number,'type'=>1));
                    CleanStore(array('uid'=>intval($uid)));
                    return true;
                }
                
                return true;
            }
        }
        
        $this->error = $this->getDbError();
        return false;
    }
    
    //获得鲜花兑换金币数量
    //$is_virtual 1:模拟操作 2:真实操作
    function getToGold($uid, $number,$is_virtual=2){
        $userInfo = User($uid,array('flower'),true);
        if(empty($userInfo)){
            $this->error = '用户信息错误!~';
            return false;
        }
        
        $number = intval($number);
        if(!$number && $is_virtual == 2){
            $this->error = '请输入正确的兑换数量!';
            return false;
        }
       
        if(!$number && $is_virtual == 1){
            return array('gain_number'=>0,'_number'=>0);
        }
        
        if($is_virtual == 2 && $number > $userInfo['flower']){
            $this->error = '您的鲜花数量不足!';
            return false;
        }

        //鲜花兑换金币配置
        $FLOWERGOLD = C('FLOWERGOLD');
        
        $gain_number = $FLOWERGOLD[$number];
        
        if(empty($gain_number)){
            $apr = C('FLOWERGOLD_DEFAULT');
            $gain_number = intval($number * $apr / 100);
        }

        if(!$gain_number && $is_virtual == 2){
            $this->error = '请输入正确的兑换数量!';
            return false;
        }

        return array('gain_number'=>$gain_number?:0, '_number'=>$number);
    }
    
    //获得鲜花兑换现金数量
    //$is_virtual 1:模拟操作 2:真实操作
    function FlowerToMoney($uid, $number,$is_virtual=2){
        $userInfo = User($uid,array('flower'),true);
        if(empty($userInfo)){
            $this->error = '用户信息错误!~';
            return false;
        }
        
        $number = intval($number);
        if(!$number && $is_virtual == 2){
            $this->error = '请输入正确的兑换数量!';
            return false;
        }
       
        if(!$number && $is_virtual == 1){
            return array('gain_number'=>0,'_number'=>0);
        }
        
        if($is_virtual == 2 && $number > $userInfo['flower']){
            $this->error = '您的鲜花数量不足!';
            return false;
        }
        
        //鲜花兑换现金配置
        $FLOWERGOLD = C('FLOWERMONEY');
        
        $gain_number = $FLOWERGOLD[$number];
        
        if(empty($gain_number)){
            $apr = C('FLOWERMONEY_DEFAULT');
            $gain_number = intval($number * $apr / 100);
        }

        if(!$gain_number && $is_virtual == 2){
            $this->error = '请输入正确的兑换数量!';
            return false;
        }

        return array('gain_number'=>$gain_number?:0, '_number'=>$number);
    }

    //获得金币兑换现金数量
    //$is_virtual 1:模拟操作 2:真实操作
    function getToMoney($uid, $number, $is_virtual=2){
        $userInfo = User($uid,array('gold'),true);
        if(empty($userInfo)){
            $this->error = '用户信息错误!~';
            return false;
        }
        
        if($is_virtual == 2 && $number > $userInfo['gold']){
            $this->error = '您的金币数量不足!';
            return false;
        }
        //金币兑换现金配置
        $GOLDMONEY = C('GOLDMONEY');
        
        $gain_number = $GOLDMONEY[$number];
        
        if(empty($gain_number)){
            $apr = C('GOLDMONEY_DEFAULT');
            $gain_number = intval($number * $apr / 100);
        }

        if(!$gain_number && $is_virtual == 2){
            $this->error = '请输入正确的兑换数量!';
            return false;
        }

        return array('_number'=>$number, 'gain_number'=>$gain_number?:0);
    }
    
    //检查提现
    //$is_virtual 1:模拟操作 2:真实操作
    function checkMoney($uid, $number,$is_virtual=2){
        $userInfo = User($uid,array('money'),true);
        if(empty($userInfo)){
            $this->error = '用户信息错误!~';
            return false;
        }

        if($is_virtual == 2 && $number > $userInfo['money']){
            $this->error = '您的现金数量不足!';
            return false;
        }

        $map['uid'] = $uid;
        $map['change_type'] = 2;
        if($this->where($map)->count()){
            $this->error = '您还有未处理的提现申请!';
            return false;
        }
        
        return array('_number'=>$number, 'gain_number'=>$number);
    }
    
    //充值成功
    function RechargeDone($order_no=''){
        if(empty($order_no)){
            $this->error = '订单号不能为空!';
            return false;
        }
        
        $info = M('Recharge')->where(array('order_no'=>$order_no))->find();
        if(empty($info)){
            $this->error = '充值记录不存在!';
            return false;
        }
        
        if($info['status'] != 'success'){
            $this->error = '支付未成功!';
            return false;
        }else{
            $this->addlog($info['uid'], $info['money'], 8, $info['id']);
        }
        
        
        //兑换金币
        if($info['category'] == 'togold'){
            $Result = $this->moneyTogold($info['uid'], $info['money']);
        }
        
        //认证
        elseif ($info['category'] == 'verify') {
            $Result = D('Store')->Verify($info['uid']);
        }
        
        return $Result;
    }
    
    //兑换金币
    function moneyTogold($uid,$money){
        $userMoney = User($uid,'money');
        if($userMoney < $money){
            $this->error = '现金不足!';
            return false;
        }
        
        return $this->addlog($uid, $money, 5);
    }
    
    //现金兑换金币
    //$money 需要兑换的现金
    //$is_virtual 1:模拟操作 2:真实操作
    private function checkTogold($uid, $money, $is_virtual=2){
        $userInfo = User($uid,array('money'),true);
        if($is_virtual == 2 && $userInfo['money'] < $money){
            $this->error = '您的现金不足!';
            return false;
        }
        
        if($money == intval($money)){
            //充值兑换金币比例
            $apr = C('MONEYTOGOLD');//优惠比例
            $money = intval($money);
            $gold = $apr[$money];
        }

        if(empty($gold)){
            $apr = C('MONEYTOGOLD_DEFAULT');//正常比例
            $gold = intval($money * $apr / 100);
        }

        if($is_virtual == 2 && empty($gold)){
            $this->error = '兑换到的金币为空!';
            return false;
        }
        
        return array('gain_number'=>$gold?:0, '_number'=>$money);
        
    }
    
    
    
}