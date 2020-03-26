<?php
//主播
namespace Common\Model;

use Think\Model;

class AnchorModel extends Model
{
    //主播入驻
    function ruZhu($data){
        if(empty($data['name'])){
            $this->error = '请填写您的名字';
            return false;
        }

        $info = M('users')->where(array('id'=>$data['uid'],'cmf_is_verify'=>1))->find();
        if(empty($info)){
            $this->error = '实名认证未成功';
            return false;
        }

        $shiInfo = M('user_verify')->field('realname')->where(array('uid'=>$data['uid']))->find();
        if($shiInfo['realname'] != $data['name']){
            $this->error = '您的名字与您的实名不符,请检查后重新填写';
            return false;
        }

        if(M('anchor_ruzhu')->where(array('name'=>$data['name']))->find()){
            $this->error = '您已入驻';
            return false;
        }

        if(empty($data['ZidCardPic']) || empty($data['FidCardPic']) || empty($data['SCidCard'])){
            $this->error = '请上传证件';
            return false;
        }

        if(strpos($data['ZidCardPic'],',')  || strpos($data['FidCardPic'],',') || strpos($data['SCidCard'],',') !==false){
            $this->error = '每个只能上传一张图片';
            return false;
        }

        //全身照
        $quanInfo = M('anchor_quanshen')->where(array('uid'=>$data['uid']))->find();
        if(empty($quanInfo)){
            $this->error = '请上传全身照';
            return false;
        }

        if($quanInfo['status'] != 2){
            $this->error = '全身照不合格，请重新上传';
            return false;
        }

        if(empty($data['face_status'])){
            $this->error = '请上传刷脸信息';
            return false;
        }

        if($data['face_status'] == 2){
            $this->error = '刷脸未通过';
            return false;
        }

        if(empty($data['bank_card_num'])){
            $this->error = '银行卡号不能为空';
            return false;
        }

        $is_agree = I('is_agree');
        if($is_agree !== 'checked'){
            $this->error = '请勾选协议';
            return false;
        }

        $data['add_time'] = time();

        $res = M('anchor_ruzhu')->add($data);
        $data['id'] = $res;

        $data['ZidCardPic'] = getImgVideo($data['ZidCardPic']);
        $data['FidCardPic'] = getImgVideo($data['FidCardPic']);
        $data['SCidCard'] = getImgVideo($data['SCidCard']);
        return $data;
    }

    //上传全身照
    function quanShen($data){
        if(empty($data['pic'])){
            $this->error = '请上传全身照';
            return false;
        }
        $info = M('anchor_quanshen')->where(array('uid'=>$data['uid'],'pic'=> $data['pic']))->find();
        if($info['status']==1){
            $this->error = '已上传待审核';
            return false;
        }elseif ($info['status'] == 3){
            $this->error = '不合格';
            return false;
        }

        if(empty($info)){
            $res = M('anchor_quanshen')->add($data);
            if($res){
                return true;
            }
        }
    }

    //赠送礼物
    function giftSend($data){
        $prefix = C('DB_PREFIX');
        if(empty($data['gift_id'])){
            $this->error = '请选择礼物';
            return false;
        }

        if(empty($data['room_no'])){
            $this->error = '请选择直播间的房间号';
            return false;
        }

        $coin = M('gift')->field('needsilvercoin')->where(array('id'=>$data['gift_id']))->find();//获取礼物所需银币
        if(empty($coin)){
            $this->error = '礼物不存在';
            return false;
        }

        $myCoin = M('users')->field('silver_coin')->where(array('id'=>$data['uid']))->find();
        if($myCoin['silver_coin'] <= $coin['needsilvercoin']){
            $this->error = '银币不足，请及时充值';
            return false;
        }

        $data['send_time'] = time();
        $data['gift_coin'] = $coin['needsilvercoin'];

        $anData = M('zhibo')->field('anchor_id')->where(array('room_no'=>$data['room_no']))->find();
        $data['anchor_id'] = $anData['anchor_id'];
        unset($data['_uid']);
        unset($data['_sign']);

        $add = M('gift_sendlog')->add($data);           //用户赠送礼物 记录
        if($add){
            //减少用户的银币
            $delCoin = $myCoin['silver_coin'] - $data['gift_coin'];
            M('users')->where(array('id'=>$data['uid']))->save(array('silver_coin'=>$delCoin));
            return $data;
        }

    }

    //计算魅力
    function meili($id,$silverCoin){
        $beforeMeili = M('anchor_ruzhu')->field('charm')->where(array('id'=>$id))->find();
        $meili = bcadd($beforeMeili['charm'] , $silverCoin ,2);
        return $meili;
    }

    //计算个人银币
    function userSilverCoin($uid,$silverCoin){
        $beforeSilver = M('users')->field('silver_coin')->where(array('id'=>$uid))->find();
        $coin = $beforeSilver['silver_coin'] + $silverCoin;
        return $coin;
    }

    //魅力贡献榜
    function meiliBang($type,$myid){
        $prefix = C('DB_PREFIX');
        if(empty($type)){
            $this->error = '请正确查询';
            return false;
        }

        //周榜
        if($type){
            //本周的起始
            if($type == 'week'){
                $beginWeek = mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y"));
                $endWeek = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
                $where = [
                    'send_time'=>array('between',"$beginWeek,$endWeek")
                ];
            }

            //本日的起始
            if($type == 'day'){
                $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
                $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                $where = [
                    'send_time'=>array('between',"$beginToday,$endToday")
                ];
            }

            //总榜
            if($type == 'all'){
                $where = '';
            }

            $info = M('gift_sendlog')
                ->field(['*','sum(gift_coin)'=>'meili'])
                ->group('uid')
                ->order('meili desc')
                ->where($where)
                ->select();

            foreach ($info as $k=>&$v){
                $userInfo = M('users')->field('id,user_nicename,avatar')->where(array('id'=>$v['uid']))->find();
                $attenInfo = M('userRelation')->where(array('uid'=>$myid))->select();
                foreach ($attenInfo as $key=>&$val){
                    if($v['uid'] == $val['relation_uid']){
                        $v['is_guanzhu'] = '1';
                    }
                }
                $v['uname'] = $userInfo['user_nicename'];
                $v['avatar'] = $userInfo['avatar'];
                unset($v['gift_id']);
                unset($v['gift_coin']);
            }

            return $info;
        }
    }

    //根据主播首月 房间在线人数获取福利
    function personNumFenCheng($num,$anchor_id,$room){
        $prefix = C('DB_PREFIX');
        //获取本次直播间的礼物总额
        $giftNum = M('gift_sendlog')->where(array('room_no'=>$room))->sum('gift_coin');

         //$bili 获取所得魅力值比例
        switch ($num) {
            case $num < 1500:
                 $bili = 0.7;
                break;
            case ( $num >= 1500) AND ( $num < 2000):
                 $bili = 0.75;
                break;
            case ( $num >= 2000) AND ( $num < 3000):
                 $bili = 0.8;
                break;
            case ( $num >= 3000) AND ( $num < 4000):
                 $bili = 0.85;
                break;
            case ( $num >= 4000) AND ( $num < 5000):
                 $bili = 0.9;
                break;
            default:
                 $bili = 0.95;
        }

        //获取每日补贴的银币
        switch ($num){
            case ( $num >= 300) AND ( $num < 400):
                $silver_coin = 100;
                break;
            case ( $num >= 400) AND ( $num < 500):
                $silver_coin = 200;
                break;
            case ( $num >= 500) AND ( $num < 600):
                $silver_coin = 300;
                break;
            case ( $num >= 600) AND ( $num < 700):
                $silver_coin = 400;
                break;
            case ( $num >= 700) AND ( $num < 800):
                $silver_coin = 500;
                break;
            case ( $num >= 900) AND ( $num < 1000):
                $silver_coin = 600;
                break;
            case ( $num >= 1000) AND ( $num < 1100):
                $silver_coin = 700;
                break;
            case $num >= 1100:
                $silver_coin = 1000;
                break;
            default:
                $silver_coin = 0;
        }

        //获取 每日幸运轮盘次数 每日黄金宝箱次数 每日神秘红包
        //幸运轮盘次数$panNumber   黄金宝箱次数$xiangNumber   每日神秘红包$baoNumber
        switch ($num){
            case ( $num >= 500) AND ( $num < 1000):
                $panNumber = 1;
                $xiangNumber = 1;
                $baoNumber = 1;
                break;
            case ( $num >= 1000) AND ( $num < 1500):
                $panNumber = 2;
                $xiangNumber = 2;
                $baoNumber = 2;
                break;
            case ( $num >= 1500) AND ( $num < 2000):
                $panNumber = 3;
                $xiangNumber = 3;
                $baoNumber = 3;
                break;
            case ( $num >= 2000) AND ( $num < 2500):
                $panNumber = 4;
                $xiangNumber = 3;
                $baoNumber = 3;
                break;
            case $num >= 2500:
                $panNumber = 5;
                $xiangNumber = 4;
                $baoNumber = 3;
                break;
            default:
                $silver_coin = 0;
        }

        //计算分成的魅力值
        $meiliNum = $giftNum * $bili;
        //增加魅力值
        $meiliValue = $this->meili($anchor_id,$meiliNum);
        M('anchor_ruzhu')->where(array('id'=>$anchor_id))->save(array('charm'=>$meiliValue));  //修改主播表中的魅力值

        //今天开始的时间和结束的时间
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        //查看今天是否开播
        $todayuser['begin_time'] = array('between',"$beginToday,$endToday");
        $todayInfo = M('zhibo')->where($todayuser)->find();
        if(!empty($todayInfo)){
            //查找主播所属的用户ID
            $uid = M('anchor_ruzhu')->field('uid')->where(array('id'=>$anchor_id))->find();
            $updateSilverCoin = $this->userSilverCoin($uid['uid'],$silver_coin);
            M('users')->where(array('id'=>$uid['uid']))->save(array('silver_coin'=>$updateSilverCoin));  //修改用户表中的银币值

            //获取主播幸运轮盘 黄金宝箱  神秘红包的次数
            $fuliInfo = M('users')->field('lunpan_num,baoxiang_num,redbag_num')->where(array('id'=>$uid['uid']))->find();
            $updFuliInfo = [
                'lunpan_num'=>$fuliInfo['lunpan_num'] + $panNumber,
                'baoxiang_num'=>$fuliInfo['baoxiang_num'] + $xiangNumber,
                'redbag_num'=>$fuliInfo['redbag_num'] + $baoNumber
            ];
            M('users')->where(array('id'=>$uid['uid']))->save($updFuliInfo);


        }
//
    }

    //主播考核
    function anchorKaohe($anchor_id){
        //根据主播ID查询所有房间  中的在线人数
        $onlinePerson = M('zhibo')->field('person_num p')->where(array('anchor_id'=>$anchor_id))->order('p desc')->select();

        //如果在线人数小于100  则取消主播的认证资质
        if($onlinePerson[0] < 100){
            M('anchor_ruzhu')->where(array('id'=>$anchor_id))->save(array('status=2'));
        }

    }
}
