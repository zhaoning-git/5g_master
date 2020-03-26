<?php
//优惠券
namespace Common\Model;

use Think\Model;

class CouponModel extends Model {
    
    //添加编辑优惠券
    function setCoupon($data){
        if(!empty($data['id'])){
            $id = intval($data['id']);
        }
        
        if($id){
            $info = $this->where(array('id'=>$id))->find();
            if(empty($info)){
                $this->error = '优惠券不存在!';
                return false;                
            }
            $data['up_time'] = NOW_TIME;
            $this->where(array('id'=>$id))->save($data);
        }else{
            $data['create_time'] = NOW_TIME;
            $id = $this->add($data);
        }
        return $id;
        
    }

    //我的优惠券
    function myCoupon($uid, $type=null, $status=null){
        $map['type'] = array('NEQ', 'CouponSilver');
        if(is_null($type)){
            $where['type'] = array('NEQ', 'CouponSilver');
            $where['is_shop'] = 0;
            $Coupon = $this->where($where)->select();
            
            foreach ($Coupon as $value){
                $map['uid'] = $uid;
                if(!is_null($status)){
                    $map['status'] = $status;
                }else{
                    unset($map['status']);
                }
                
                if(!empty($value['type'])){
                    $myCoupon[$value['type']] = M($value['type'])->where($map)->select();
                }
            }
            
        }else{
            $map['uid'] = $uid;
            if(!is_null($status)){
                $map['status'] = $status;
            }
            if($this->where(array('type'=>$type))->count()){
                $myCoupon = M($type)->where($map)->select();
            }else{
                $this->error = '不存在的优惠券类型!';
                return false;
            }
        }
        return $myCoupon;
    }

    /***************金币立减券*******************/
    
    //添加一张金币立减券
    //获得方式(来源) Lottery:幸运转盘
    function addGold($uid, $money, $source='Lottery', $source_id=0){
        $Coupon = $this->where(array('type'=>'CouponGold'))->find();
        
        $data['uid'] = $uid;
        $data['money'] = $money;
        $data['source'] = $source;
        $data['source_id'] = $source_id;
        $data['status'] = 0;//状态 -1:被删除  0:未使用  1:已使用 2:过期
        $data['end_time'] = $Coupon['youxiao_day']?$this->EndTime($Coupon['youxiao_day']):0;
        $data['addtime'] = NOW_TIME;
        return M('CouponGold')->add($data);
    }
    
    //金币立减券信息
    function getGold($id, $field=true){
        $id = intval($id);
        if(!$id){
            $this->error = '参数错误!';
            return false;
        }
        
        $info = M('CouponGold')->where(array('id'=>$id))->find();
        if($field === true){
            return $info;
        }
        if($field == 'money'){
            if($info['status']){
                return 0;
            }elseif($info['status'] == 0){
                return $info['money'];
            }
        }else{
            return $info[$field];
        }
        
    }
    
    //使用金币立减券
    function useGold($id, $uid){
        $GoldInfo = $this->checkCoupon($id, $uid, 'CouponGold');
        if(!$GoldInfo){
            return false;
        }
        
        $data['uid'] = $uid;
        $data['coupon_id'] = $GoldInfo['id'];
        $data['money'] = $GoldInfo['money'];
        $data['addtime'] = NOW_TIME;
        
        if(M('CouponGoldLog')->add($data)){
            $up['status'] = 1;
            $up['uptime'] = NOW_TIME;
            M('CouponGold')->where(array('id'=>$info['id']))->save($up);
            return $GoldInfo['money'];
        }
    }
    
    
    
    /***************竞猜加奖券*******************/
    
    //添加一张竞猜加奖券
    //$coin 是百分比
    function addJingcai($uid, $coin, $source='Lottery', $source_id=0){
        $Coupon = $this->where(array('type'=>'CouponJingcai'))->find();
        $data['uid'] = $uid;
        $data['coin'] = $coin;
        $data['limit'] = $Coupon['limit'];
        $data['source'] = $source;
        $data['source_id'] = $source_id;
        $data['status'] = 0; //状态 -1:被删除  0:未使用  1:已使用 2:过期
        $data['end_time'] = $Coupon['youxiao_day']?$this->EndTime($Coupon['youxiao_day']):0;
        $data['addtime'] = NOW_TIME;
        return M('CouponJingcai')->add($data);
    }
    
    //使用竞猜加奖券 竞猜时勾选使用本券,派奖时额外派发银币(百分比)，加奖累积上限每人每日1,000银币
    function useJingcai($id, $uid){
        $info = $this->checkCoupon($id, $uid, 'CouponJingcai');
        if(!$info){
            return false;
        }
        
        //加奖累积上限每人每日1,000银币
        $start = strtotime(date('Y-m-d', NOW_TIME));
        $end = $start + 86399;
        $map['uptime'] = array(array('EGT',$start), array('ELT', $end)); //今日时间戳范围
        $map['uid'] = $uid;
        $map['status'] = 1;
        
        $Limit = $this->where(array('type'=>'CouponJingcai'))->getField('limit');
        if(M('CouponJingcai')->where($map)->Sum('coin') >= $Limit){
            $this->error = '加奖累积上限每人每日'.$Limit.'银币!';
            return false;
        }
        
        $data['status'] = 1;//状态 -1:被删除  0:未使用  1:已使用 2:过期
        $data['uptime'] = NOW_TIME;
        M('CouponJingcai')->where(array('id'=>$info['id']))->save($data);
        return true;
    }
    
    
    
    /***************万能合并券*******************/
    
    //添加一张万能合并券
    function addMerge($uid, $limit=38, $source='Lottery', $source_id=0){
        $Coupon = $this->where(array('type'=>'CouponMerge'))->find();
        $data['uid'] = $uid;
        $data['limit'] = 38; //合并上限
        $data['source'] = $source;
        $data['source_id'] = $source_id;
        $data['status'] = 0;//状态 -1:被删除  0:未使用  1:已使用 2:过期
        $data['end_time'] = $Coupon['youxiao_day']?$this->EndTime($Coupon['youxiao_day']):0;
        $data['addtime'] = NOW_TIME;
        return M('CouponMerge')->add($data);
    }
    
    //使用万能合并券
    //可合并会员抵扣券或金币立减券，需同类券合并（如会员抵扣券2元+会员抵扣券3元=会员抵扣券5元）
    //单张合并上限38元，每合并1张同类券消耗1张万能合并券
    //$id:万能合并券 $uid:用户ID $type:需要合并的券类型 $par:需要合并的券ID(数组或用,分割)
    function useMerge($id, $uid, $type, $par){
        $Mergeinfo = $this->checkCoupon($id, $uid, 'CouponMerge');
        if(!$Mergeinfo){
            return false;
        }
        
        if(empty($par)){
            $this->error = '参数有误!';
            return false;
        }
        
        if(is_string($par)){
            $par = explode(',', $par);
        }
        
        $batch = (M('CouponMergeLog')->Max('batch')) + 1;
        $_field = $type=='CouponGold'?'money':'coin';
        
        foreach ($par as $value){
            $info = $this->checkCoupon($value, $uid, $type);
            if(!$info){
                return false;
            }else{
                $limit[] = $info[$_field];
            }
            $data['uid'] = $uid;
            $data['coupon_id'] = $Mergeinfo['id'];//使用的合并券ID
            $data['old_id'] = $value;
            $data['batch'] = $batch;
            $data['type'] = $type;
            $data['number'] = $info[$_field];
            $data['addtime'] = NOW_TIME;
            if(!M('CouponMergeLog')->where(array('old_id'=>$value, 'type'=>$type))->count()){
                $_data[] = $data;
            }
        }
        
        //合并后的总额不能大于限制金额38元
        $Newnum = array_sum($limit);
        if($Newnum > $Mergeinfo['limit']){
            $this->error = '单张合并上限'.$Mergeinfo['limit'].'元!';
            return false;
        }
        
        if(!empty($_data)){
            if(M('CouponMergeLog')->addAll($_data)){
                switch ($type){
                    //金币立减券
                    case 'CouponGold':
                        $func = 'addGold';
                        break;
                    //竞猜加奖券
                    case 'CouponJingcai':
                        $func = 'addJingcai';
                        break;
                    //万能合并券
                    case 'CouponMerge':
                        $func = 'addMerge';
                        break;
                    //万能延时券
                    case 'CouponDelay':
                        $func = 'addDelay';
                        break;
                    //会员抵扣券
                    case 'CouponDikou':
                        $func = 'addikou';
                        break;
                    default :
                        $this->error = '未知的类型';
                        return false;
                    
                    
                    
                }
                
                //增加一张新的同类券
                $new_id = $this->$func($uid, $Newnum, 'CouponMerge', $batch);
                if($new_id){
                    //设置合并券为已使用
                    $save['status'] = 1;
                    $save['uptime'] = NOW_TIME;
                    M('CouponMerge')->where(array('id'=>$Mergeinfo['id']))->save($save);
                }
                
                //更新合并记录
                $up['new_id'] = $new_id;
                $up['new_number'] = $Newnum;
                M('CouponMergeLog')->where(array('batch'=>$batch))->save($up);
                
                //设置原券的状态为已合并
                M($type)->where(array('id'=>array('IN', $par)))->setField('status', 3);
                
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }else{
            $this->error = '已合并,请勿重复提交!';
            return false;
        }
    }
    
    
    /***************万能延时券*******************/
    
    //添加一张万能延时券
    function addDelay($uid, $day, $source='Lottery', $source_id=0){
        $Coupon = $this->where(array('type'=>'CouponDelay'))->find();
        $data['uid'] = $uid;
        $data['day'] = $day; //延时天数
        $data['source'] = $source;
        $data['source_id'] = $source_id;
        $data['status'] = 0; //状态 -1:被删除  0:未使用  1:已使用 2:过期
        $data['end_time'] = $Coupon['youxiao_day']?$this->EndTime($Coupon['youxiao_day']):0;
        $data['addtime'] = NOW_TIME;
        return M('CouponDelay')->add($data);
    }

    //使用万能延时券
    //$id:使用的延时券ID
    //$type:券类型
    //$old_id 需要被延时的券ID
    function useDelay($id, $uid, $type, $old_id){
        $DelayInfo = $this->checkCoupon($id, $uid, 'CouponDelay');
        if(!$DelayInfo){
            return false;
        }
        
        //需要被延时的券信息
        $info = $this->checkCoupon($old_id, $uid, $type);
        if(!$info){
            return false;
        }
        
        $new_end_time = $info['end_time'] + $DelayInfo['day'] * 86400;
        
        $data['uid'] = $uid;
        $data['coupon_id'] = $DelayInfo['id'];
        $data['old_id'] = $old_id;
        $data['old_end_time'] = $info['end_time'];
        $data['day'] = $DelayInfo['day'];
        $data['new_end_time'] = $new_end_time;
        $data['addtime'] = NOW_TIME;
        
        if(M('CouponDelayLog')->add($data)){
            //设置延时券已使用
            M('CouponDelay')->where(array('id'=>$DelayInfo['id']))->setField('status', 1);
            //设置被延时的券新的的到期时间
            M($type)->where(array('id'=>$old_id))->setField('end_time', $new_end_time);
            return true;
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }

    /***************会员抵扣券 CouponDikou*******************/
    
    //添加一张会员抵扣券
    function addDikou($uid, $money, $source='Lottery', $source_id=0){
        $Coupon = $this->where(array('type'=>'CouponDikou'))->find();
        $data['uid'] = $uid;
        $data['money'] = $money;
        $data['source'] = $source;
        $data['source_id'] = $source_id;
        $data['status'] = 0;//状态 -1:被删除  0:未使用  1:已使用 2:过期
        $data['end_time'] = $Coupon['youxiao_day']?$this->EndTime($Coupon['youxiao_day']):0;
        $data['addtime'] = NOW_TIME;
        return M('CouponDikou')->add($data);
    }
    
    //使用会员抵扣券
    function useDikou($id, $uid){
        $DikouInfo = $this->checkCoupon($id, $uid, 'CouponDikou');
        if(!$info){
            return false;
        }
        
        $data['uid'] = $uid;
        $data['coupon_id'] = $DikouInfo['id'];
        $data['money'] = $DikouInfo['money'];
        $data['addtime'] = NOW_TIME;
        if(M('CouponDikouLog')->add($data)){
            
            //设置抵扣券已使用
            $up['status'] = 1;
            $up['uptime'] = NOW_TIME;
            M('CouponDikou')->where(array('id'=>$DikouInfo['id']))->save($up);
            return $DikouInfo['money'];
        }
    }


    /***************会员权益券 CouponQuanyi*******************/
    //添加会员权益券
    function addQuanyi($uid, $day, $source='Lottery', $source_id=0){
        $Coupon = $this->where(array('type'=>'CouponQuanyi'))->find();
        $data['uid'] = $uid;
        $data['day'] = $day; //权益天数
        $data['source'] = $source;
        $data['source_id'] = $source_id;
        $data['status'] = 0; //状态 -1:被删除  0:未使用  1:已使用 2:过期
        $data['end_time'] = $Coupon['youxiao_day']?$this->EndTime($Coupon['youxiao_day']):0;
        $data['addtime'] = NOW_TIME;
        return M('CouponQuanyi')->add($data);
    }

    //使用会员权益券
    function useQuanyi($id, $uid){
        $QuanyiInfo = $this->checkCoupon($id, $uid, 'CouponQuanyi');
        if(!$QuanyiInfo){
            return false;
        }
     
        $data['uid'] = $uid;
        $data['coupon_id'] = $QuanyiInfo['id'];
        $data['day'] = $QuanyiInfo['day'];
        $data['addtime'] = NOW_TIME;
        if(M('CouponQuanyiLog')->add($data)){
            $level_end_time = $QuanyiInfo['day'] * 86399;
            $map['uid'] = $uid;
            M('Users')->where($map)->setInc('level_end_time', $level_end_time);
            
            //设置会员权益券已使用
            $up['status'] = 1;
            $up['uptime'] = NOW_TIME;
            M('CouponQuanyi')->where(array('id'=>$QuanyiInfo['id']))->save($up);
            return $HuiyuanInfo['month'];
        }
        
    }


    /***************会员兑换************************************/
    //添加会员兑换券
    function addHuiyuan($info){
        $Coupon = $this->where(array('type'=>$info['type']))->find();
        $userLevel = M('user_level')->where(array('name'=>$Coupon['title']))->getField('id');
        
        $data['uid'] = $info['uid'];
        $data['month'] = $info['cost'];
        $data['user_level'] = $userLevel;
        $data['source'] = $info['welfare'];
        $data['source_id'] = $info['id'];
        $data['status'] = 0;//状态 -1:被删除  0:未使用  1:已使用 2:过期
        $data['end_time'] = $Coupon['youxiao_day']?$this->EndTime($Coupon['youxiao_day']):0;
        $data['addtime'] = NOW_TIME;
        return M('CouponHuiyuan')->add($data);
    }

    //使用会员兑换券
    //$id券ID
    function useHuiyuan($id, $uid){
        $HuiyuanInfo = $this->checkCoupon($id, $uid, 'CouponHuiyuan');
        if(!$HuiyuanInfo){
            return false;
        }
        
        $userInfo = M('Users')->where(array('id'=>$uid))->find();
        
        if($userInfo['level'] >= $HuiyuanInfo['user_level']){
            $userLevelName = M('UserLevel')->where(array('id'=>$userInfo['level']))->getField('name');
            $upLevelName = M('UserLevel')->where(array('id'=>$HuiyuanInfo['user_level']))->getField('name');
            $this->error = '您的会员级别是'.$userLevelName.'无法升级为'.$upLevelName;
            return false;
        }
        
        
        $data['uid'] = $uid;
        $data['user_level'] = $HuiyuanInfo['user_level'];
        $data['coupon_id'] = $HuiyuanInfo['id'];
        $data['month'] = $HuiyuanInfo['month'];
        $data['addtime'] = NOW_TIME;
        if(M('CouponHuiyuanLog')->add($data)){
            D('UserLevel')->upLevel($uid, $HuiyuanInfo['user_level'], 2);
            
            //设置会员兑换券已使用
            $up['status'] = 1;
            $up['uptime'] = NOW_TIME;
            M('CouponHuiyuan')->where(array('id'=>$HuiyuanInfo['id']))->save($up);
            return $HuiyuanInfo['month'];
        }
        
    }

    /*****************补签卡***************************************/
    //添加补签卡
    //$num获得的补签卡数量
    function addSign($uid, $num, $source='Lottery', $source_id=0){
        
    }


    /*****************改名卡***************************************/
    //添加改名卡
    function addRename(){
        
    }












    /********************************************************/
    
    
    
    
    
    //验证优惠券
    function checkCoupon($id, $uid, $monde){
        $id = intval($id);
        if(!$id){
            $this->error = '参数错误!';
            return false;
        }
        $info = M($monde)->where(array('id'=>$id, 'uid'=>$uid))->find();
        if(empty($info)){
            $this->error = '优惠券不存在!';
            return false;
        }
        
        if($info['end_time'] && NOW_TIME >= $info['end_time']){
            M($monde)->where(array('id'=>$info['id']))->setField('status', 2);
            $info['status'] = 2;
        }
        
        if($info['status'] != 0){
            if($info['status'] == 1){
                $this->error = '优惠券已使用!';
                return false;
            }elseif($info['status'] == 2){
                $this->error = '优惠券已过期!';
                return false;
            }elseif($info['status'] == -1){
                $this->error = '优惠券已被删除!';
                return false;
            }
        }
        return $info;   
    }
    
    //到期时间
    //$day:有效天数
    function EndTime($day){
        $time = NOW_TIME + $day * 86400;
        return strtotime(date('Y-m-d', $time)) + 86399;
    }
    
    //处理优惠券列表
    function setlist($list){
        if(empty($list)){
            $this->error = '优惠券列表数据不能为空!';
            return false;
        }
        
        $statusTxt = array(-1=>'被删除', 0=>'未使用', 1=>'已使用', 2=>'过期', 3=>'被合并');
        $sourceTxt = array('Lottery'=>'幸运转盘', 'CouponMerge'=>'万能合并券');
        
        
        foreach ($list as &$value){
            $value['nickname'] = User($value['uid'], 'user_nicename');
            $value['status_txt'] = $statusTxt[$value['status']];
            $value['source_txt'] = $sourceTxt[$value['source']];
            $value['addtime_txt'] = date('Y-m-d H:i:s', $value['addtime']);
            $value['uptime_txt'] = $value['uptime']?date('Y-m-d H:i:s', $value['uptime']):'--';
            $value['end_time_txt'] = $value['end_time']?date('Y-m-d H:i:s', $value['end_time']):'--';
        }
        
        return $list;
    }
    
    
}

