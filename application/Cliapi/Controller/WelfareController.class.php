<?php
/**
 * Date: 19-10-31
 * Time: 20:19
 */
namespace Cliapi\Controller;

class WelfareController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }

    //参加抽奖
    public function inLottery(){
        $Result = D('Lottery')->inLottery($this->uid);
        if($Result){
            $this->ajaxRet(1, '成功', $Result);
        }else{
            $this->ajaxRet(0, D('Lottery')->getError());
        }
    }
    
    //参加黄金宝箱
    public function inGoldBox(){
        $Result = D('Lottery')->inGoldBox($this->uid);
        if($Result){
            $this->ajaxRet(1, '成功', $Result);
        }else{
            $this->ajaxRet(0, D('Lottery')->getError());
        }
    }

    //参加神秘红包
    function inRedPack(){
        $data = I('post.');
        $rplogid = intval($data['rplogid']);
        if(!$rplogid){
            $this->ajaxRet(0, '神秘红包记录ID参数有误');
        }
        
        $Result = D('Lottery')->inRedPack($this->uid, $rplogid, $data['token']);
        if($Result){
            $this->ajaxRet(1, '成功', $Result);
        }else{
            $this->ajaxRet(0, D('Lottery')->getError());
        }
    }
    


    //获取抽奖结果
    function getResult(){
        $data = I('post.');
        $Result = D('Lottery')->getResult($data['lid'], $data['token']);
        if($Result){
            $this->ajaxRet(1, '成功', $Result);
        }else{
            $this->ajaxRet(0, D('Lottery')->getError());
        }
    }
    
    //幸运转盘抽奖次数
    function getLotteryNum(){
        $Result = D('Lottery')->getLotteryNum($this->uid);
        if($Result){
            $this->ajaxRet(1, '成功', $Result);
        }else{
            $this->ajaxRet(0, D('Lottery')->getError(), 0);
        }
        
    }
    
    
}
