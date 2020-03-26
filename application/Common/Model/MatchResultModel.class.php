<?php

//比赛结果 足球即时竞彩 2.即时变化的比分数据
namespace Common\Model;
use Think\Model;

class MatchResultModel extends Model {
    //20秒执行一次
    function addResult(){
        $url = D('Match')->url.'/getJishiData';
        $data = json_decode(D('Match')->curl($url), true);
        $data = $data['changeList'];
        if(!empty($data)){
            foreach ($data as $value){
                $this->Ins($value);
            }
        }
    }
    
    function Ins($data){
        $str = json_encode($data);
        $hash = hash('sha256',$str);
        $map['hash'] = $hash;
        if(!$this->where($map)->count()){
            $data['match_id'] = $data['matchId'];
            $data['hasLineup'] = $data['hasLineup']==1?1:0;
            $data['match_time'] = strtotime($data['matchTime']);
            $data['match_date'] = $data['matchTime'];

            $data['start_time'] = strtotime($data['startTime']);
            $data['start_date'] = $data['startTime'];
            $data['hash'] = $hash;
            $data['addtime'] = NOW_TIME;
            $this->add($data);
        }
    }
    
    function getMatchResult($match_id){
        $map['match_id'] = $match_id;
        $info = $this->where($map)->order('addtime DESC')->find();
        return $info;
    }
    
    
    
}