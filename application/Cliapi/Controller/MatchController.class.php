<?php

/**

 * 赛事数据

 */

namespace Cliapi\Controller;



use Think\Controller;



class MatchController extends MemberController {



    function _initialize(){

        parent::_initialize();

    }

    
 	/*
    * 分组数据 
    * @author 郑薏玮<715713881@qq.com>
    */
    function category(){

        $this->ajaxRet(array('status'=>1,'info'=>'获取分组数据','data'=>D('Match')->categoryData()));

    }
    
    
    //20.球员资料
    public function player(){
        $teamId = I('team_id');
        $data['_list'] = D('Match')->player($teamId);
        $this->ajaxRet(array('status'=>1,'info'=>'球员资料','data'=>$data));
    }



    //获取当天比赛的比分数据
    function bifenData(){
        $data['_list'] = D('Match')->bifenData();
        $this->ajaxRet(array('status'=>1,'info'=>'当天比赛的比分数据','data' => $data));
    }

    //即时变化的比分数据
    function jishiData(){
        $data['_list'] = D('Match')->jishiData();
        $this->ajaxRet(array('status'=>1,'info'=>'即时变化的比分数据','data'=>$data));
    }

    //赛程赛果   有参数
    function saichengData(){
        $data = I();
        if((empty($data['date']) && empty($data['leagueId']) && empty($data['matchId']) || !empty($data['date']) && !empty($data['leagueId']) && !empty($data['matchId']))){
            $this->ajaxRet(array('status'=>0,'info'=>'“date”、“leagueId”、“matchId“三个参数必填其中一个，且三个参数不支持同时使用。'));
        }

        $res['_list'] = D('Match')->retSaichengData($data);
        $this->ajaxRet(array('status'=>1,'info'=>'赛程赛果','data'=>$res));

    }

    /**
     * 比赛删除&修改时间记录
     * 接口返回过去12小时内的赛程删除、比赛时间修改记录
     */
    function ModifyRecord(){
        $this->ajaxRet(array('status'=>1,'info'=>'获取修改记录成功','data'=>D('Match')->ModifyRecord()));
    }


    /**
     * 比赛的详细事件&技术统计
     * 用于获取比赛的入球、红黄牌等比赛事件，以及技术统计数据
     */
    function detailData(){
        $this->ajaxRet(array('status'=>1,'info'=>'获取比赛的详细事件成功','data'=>D('Match')->detailData()));
    }



    //杯赛联赛资料
    function LeagueData(){
        $this->ajaxRet(array('status'=>1,'info'=>'获取杯赛联赛资料成功','data'=>D('Match')->LeagueData()));
    }





    //球队资料

    function teamData(){

        $this->ajaxRet(array('status'=>1,'info'=>'球队资料获取成功','data'=>D('Match')->teamData()));

    }



    //单盘口赔率：即时赔率接口

    function odds(){

        $this->ajaxRet(array('status'=>1,'info'=>'单盘口赔率获取成功','data'=>D('Match')->odds()));

    }



    //单盘口赔率：30秒内变化赔率接口

    function oddsChange(){

        $this->ajaxRet(array('status'=>1,'info'=>'单盘口赔率(30秒内变化赔率)获取成功','data'=>D('Match')->oddsChange()));

    }



    //单盘口历史赔率/未来赔率

    function historyOdds(){

        $this->ajaxRet(array('status'=>1,'info'=>'单盘口历史赔率/未来赔率获取成功','data'=>D('Match')->historyOdds()));

    }



    //半场欧赔

    function banchang(){

        $this->ajaxRet(array('status'=>1,'info'=>'半场欧赔获取成功','data'=>D('Match')->banchang()));

    }



    //多盘口赔率:即时

    function duoOdds(){

        $this->ajaxRet(array('status'=>1,'info'=>'半场欧赔获取成功','data'=>D('Match')->duoOdds()));

    }



    //多盘口赔率：30秒内变化赔率

    function duoOddsChange(){

        $this->ajaxRet(array('status'=>1,'info'=>'半场欧赔获取成功','data'=>D('Match')->duoOddsChange()));

    }



    //多盘口历史赔率/未来赔率

    function duoHistoryOdds(){

        $this->ajaxRet(array('status'=>1,'info'=>'多盘口历史赔率/未来赔率获取成功','data'=>D('Match')->duoHistoryOdds()));

    }



    //走地赔率

    function oddsRunning(){

        $this->ajaxRet(array('status'=>1,'info'=>'走地赔率获取成功','data'=>D('Match')->oddsRunning()));

    }



    //走地赔率  半场

    function oddsRunningHalf(){

        $this->ajaxRet(array('status'=>1,'info'=>'走地赔率获取成功','data'=>D('Match')->oddsRunningHalf()));

    }



    //竞彩足球

    function JczqOdds(){

        $this->ajaxRet(array('status'=>1,'info'=>'获取竞彩足球成功','data'=>D('Match')->JczqOdds()));

    }



    //彩票赛程与比赛ID关联表

    function MatchidInterface(){

        $this->ajaxRet(array('status'=>1,'info'=>'获取彩票赛程与比赛ID关联表成功','data'=>D('Match')->MatchidInterface()));

    }



    //彩票比赛的比分数据

    function Zq_BF(){

        $this->ajaxRet(array('status'=>1,'info'=>'获取彩票比赛的比分数据成功','data'=>D('Match')->Zq_BF()));

    }



    //彩票比赛的事件&技术统计

    function Zq_Event(){

        $this->ajaxRet(array('status'=>1,'info'=>'获取票比赛的事件&技术统计成功','data'=>D('Match')->Zq_BF()));

    }



    //curl  get请求

    function curl($url){

        $ch = curl_init();

        //设置选项，包括URL

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HEADER, 0);



        //执行并获取HTML文档内容

        $output = curl_exec($ch);

        //释放curl句柄

        curl_close($ch);

        return $output;

//        return json_decode($output);

    }





}