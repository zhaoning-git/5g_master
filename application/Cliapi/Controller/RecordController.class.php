<?php

/**
 * Date: 19-10-28
 * Time: 下午5:04
 */
namespace Cliapi\Controller;

//记录
class RecordController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }
    
    //签到记录
    public function Signin() {
        $map['uid'] = $this->uid;
        
        $data['alwaysSignDay'] = M('UserSignin')->where($map)->order('create_time DESC')->getField('daynum');//连续签到天数

        $data['countSilver'] = M('UserSignin')->where($map)->sum('coin');//签到获取的银币


        $beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
        $endThismonth = mktime(23,59,59,date('m'),date('t'),date('Y'));
        $map['create_time'] = [['EGT', $beginThismonth],['ELT', $endThismonth]];
        $data['countSignDay'] =  M('UserSignin')->where($map)->count();//本月签到天数
        
        $data['canPerson'] = count(M('UserSignin')->group('uid')->getField('uid',true));//参与人数

        $data['_list'] = $this->lists('UserSignin',$map,'create_time ASC');
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }
    
    //邀请记录
    public function Invite(){
        $map['invite_id'] = $this->uid;
        $field = 'id, user_nicename, user_email, avatar, avatar_thumb';
        $data['_list'] = $this->lists('Users', $map, 'create_time DESC', array(), $field);
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }
    
    //银币记录
    public function SilverCoin(){
        $map['uid'] = $this->uid;
        $map['coin_type'] = 'silver_coin';
        $data['_list'] = $this->lists('UsersCoinrecord', $map, 'addtime DESC');
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
        
    }
    
    //中奖记录
    public function LotteryLog(){
        $map['uid'] = $this->uid;
        $field = 'id,uid,lottery_id,cost,status,create_time,hit_time';
        $list = $this->lists('LotteryLog', $map, 'create_time DESC', array(), $field);
        if(!empty($list)){
            $status = array(0=>'正在抽奖', 1=>'成功抽奖', 2=>'抽奖失败');
            foreach ($list as &$value){
                $value['status_txt'] = $status[$value['status']];
                $value['lottery_info'] = M('Lottery')->where(array('id'=>$value['lottery_id']))->field('welfare,welfare_name,title')->find();
            }
        }
        
        $data['_list'] = $list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }
    
    //参与竞猜记录
    public function cyJingcai(){
        $map['uid'] = $this->uid;
        $data['_list'] = $this->lists('JingcaiLog', $map, 'addtime DESC');
        if(!empty($data['_list'])){
            foreach ($data['_list'] as &$value){
                $JingcaiInfo = D('Jingcai')->JingcaiInfo($value['jingcai_id']);
                $value['home'] = $JingcaiInfo['home'];
                $value['away'] = $JingcaiInfo['away'];
                $value['mycaiTxt'] = $JingcaiInfo['odds'][$value['mycai']];
            }
        }
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }
    
    //发起竞猜记录
    public function fqJingcai(){
        $map['uid'] = $this->uid;
        $data['_list'] = $this->lists('Jingcai', $map, 'addtime DESC');
        if(!empty($data['_list'])){
            foreach ($data['_list'] as &$value){
                $value = D('Jingcai')->setJingcai($value);
                //$value['']
            }
        }
        
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
        
    }
    
}
