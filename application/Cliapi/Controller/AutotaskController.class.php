<?php

/**
 * 自动任务
 */
namespace Cliapi\Controller;
use Think\Controller;
use Cliapi\Controller\ZixuncaijiController;
class AutotaskController extends Controller {
    
    public function _initialize() {
        
    }
    
    //整点红包
    public function Zhengdian(){
        D('LotteryRedpack')->RedPack('zhengdian');
    }
    
    //检查会员到期
    public function checkUserlevel(){
        $map['level'] = array('GT', 1);
        $map['level_end_time'] = array('LT', NOW_TIME);
        
        $uP['level'] = 1;
        $uP['level_start_time'] = 0;
        $uP['level_end_time'] = 0;
        M('Users')->where($map)->save($uP);
    }
    
    //生日祝福
    
    //自动采集任务
    public function Zixuncaiji(){
        $Caiji = new ZixuncaijiController();
        
        //懂球帝足球
        $Caiji->donqiudi();
        
        //腾讯足球
        $Caiji->qqfootball();
        
        //腾讯篮球
        $Caiji->qqbasketball();
        
    }
    
    //更新球队资料
    public function LibraryTeam(){
        D('Match')->LibraryTeam();
    }
   
    //更新联赛杯赛资料
    public function League(){
        D('Match')->League();
    }
   
    //赛程赛果入库 频率限制：60秒/次 建议更新频率：12小时/次
    public function scheduleDb(){
        $time = time();
        //$data = date('Y-m-d', mktime(0, 0, 0, date('m', $time), date('d', $time)+1, date('Y', $time)));
        $data = D('Match')->saichengData(['date'=>date('Y-m-d', $time)]);
    }
}