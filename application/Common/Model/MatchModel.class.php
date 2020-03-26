<?php

namespace Common\Model;

use Think\Model;

class MatchModel extends Model{
    public $url;
    public $redis;

    protected $tableName = 'socket';

    public function _initialize(){
        parent::_initialize();
        //$this->url = 'http://data.zyzyz.top';
        $this->url = 'http://interface.win007.com/football';

        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->redis->auth('yunbao.com');
    }

    public function redis($key, $url){
        //$redis = new \Redis();
        //$redis->connect('127.0.0.1', 6379);
        $redis = $this->redis;

        $res = $redis->get($key);

        if (false == $res) {
            $data = $this->curl($url);

            $redis->set(md5($url), $data);
            if (strpos($key, 'Tian') > 0) {
                $redis->expire($key, 3600 * 24);
            }
            if (strpos($key, 'sai') > 0) {
                $redis->expire(md5($url), 3600 * 12);
            }
            $redis->expire(md5($url), 120);

            return json_decode($data, true);
        } else {
            return json_decode($res, true);
        }
    }

    function Redisdata($key, $data='', $expire=''){
        
        $key = 'feijing88_'.$key;
        //$this->redis->delete($key);
        if(empty($data)){
            $data = $this->redis->get($key);
            if($data == false){
                return false;
            }else{
                return json_decode($data, true);
            }
        }else{
            $this->redis->set($key, json_encode($data));
            if($expire){
                $this->redis->expire($key, $expire);
            }
            
            return $data;
        }
    }

    //获取球队资料 (更新)
    //频率限制：1小时/次
    //建议更新频率：1天/次
    public function LibraryTeam(){
        $key = 'team';
        $url = $this->url.'/'.$key.'.aspx?day=1';
        $data = $this->curl($url);
        
        $data = json_decode($data, true);
        
        if(!empty($data['teamList'])){
            foreach($data['teamList'] as $value){
                unset($up);
                $TeamInfo = M('LibraryTeam')->where(['teamId'=>$value['teamId']])->find();
                //添加
                if(empty($TeamInfo)){
                    $value['addtime'] = time();
                    if(!empty($value['logo'])){
                        $value['logo'] = $value['logo'].'?win007=sell';
                    }
                    M('LibraryTeam')->add($value);
                }
                //更新
                else{
                    if(!empty($value['leagueId'])) $up['leagueId'] = $value['leagueId'];
                    if(!empty($value['nameEn'])) $up['nameEn'] = $value['nameEn'];
                    if(!empty($value['nameChs'])) $up['nameChs'] = $value['nameChs'];
                    if(!empty($value['foundingDate'])) $up['foundingDate'] = $value['foundingDate'];
                    if(!empty($value['areaEn'])) $up['areaEn'] = $value['areaEn'];
                    if(!empty($value['areaCn'])) $up['areaCn'] = $value['areaCn'];
                    if(!empty($value['gymEn'])) $up['gymEn'] = $value['gymEn'];
                    if(!empty($value['gymCn'])) $up['gymCn'] = $value['gymCn'];
                    if(!empty($value['capacity'])) $up['capacity'] = $value['capacity'];
                    if(!empty($value['logo'])) $up['logo'] = $value['logo'].'?win007=sell';
                    if(!empty($value['addrEn'])) $up['addrEn'] = $value['addrEn'];
                    if(!empty($value['addrCn'])) $up['addrCn'] = $value['addrCn'];
                    if(!empty($value['website'])) $up['website'] = $value['website'];
                    if(!empty($value['coachEn'])) $up['coachEn'] = $value['coachEn'];
                    if(!empty($value['coachCn'])) $up['coachCn'] = $value['coachCn'];

                    if($up){
                        $up['uptime'] = time();
                        M('LibraryTeam')->where(['id'=>$TeamInfo['id']])->save($up);
                    }
                }
                unset($up);
            }
        }
        return $data;
    }

    /*
    * 分组数据
    * @author 郑薏玮<715713881@qq.com>
    */
    public function categoryData(){
        $list = M('LibraryLeague')->where(array('is_com'=>1))->field('leagueid, color, namechs, namechsshort, leaguelogo, countrylogo')->select();

        foreach ($list as $key => $item) {
            $list[$key]['_child'] = M('LibraryTeam')->field('teamid, namechs, logo')->where(array('leagueId'=>$item['leagueid']))->select();
        }
       
        return ['_list' => $list]; 
    }


    //获取当天比赛的比分数据（全量）
    public function bifenData(){
        $key = 'today';
        $data = $this->Redisdata($key);
        if(!$data){
            $url = $this->url.'/'.$key.'.aspx';
            // return $this->curl($url);
            $data = json_decode($this->curl($url), true);
            
            $data = $data['matchList'];
            
            if(!empty($data)){
                foreach($data as &$value){
                    $value['homelogo'] = $this->getTeamLogo($value['homeId']);
                    $value['awaylogo'] = $this->getTeamLogo($value['awayId']);
                    $matchIdkey = 'today_matchId_'.$value['matchId'];
                    $this->Redisdata($matchIdkey, $value);
                    
                }
                $data = $this->Redisdata($key, $data, 600);
                
            }
        }

        return array_slice($data,0,30);
        //return $data;
        
    }

    //
    public function RedisBifenData(){
        $list = $this->bifenData();
        if(!empty($list)){
            
            foreach($list as $value){
                $key = 'today_matchId_'.$value['matchId'];
                $this->Redisdata($key, $value, 600);
            }
            return $key;
        }
    }

    //即时变化的比分数据(20秒)
    public function jishiData(){
        $key = 'change';
        $data = $this->Redisdata($key);
        
        if(!$data){
            $url = $this->url.'/'.$key.'.aspx?language=cn';
            $data = json_decode($this->curl($url), true);
            $data = $data['changeList'];
            if(!empty($data)){
                
                foreach($data as &$value){
                    $bifenData = $this->Redisdata('today_matchId_'.$value['matchId']);
                    $value['homelogo'] = $bifenData['homelogo']?:'';
                    $value['awaylogo'] = $bifenData['awaylogo']?:'';
                    
                }
                $data = $this->Redisdata($key, $data, 20);
            }
        }
        return $data;

    }

    //16.联赛/杯赛资料
    public  function League(){
        $key = 'league';
        $url = $this->url.'/'.$key.'.aspx?day=1';
        $data = json_decode($this->curl($url), true);

        $list = $data['leagueList'];
        if(!empty($list)){
            foreach($list as $value){
                unset($up);
                $info = M('LibraryLeague')->where(['leagueId'=>$value['leagueId']])->find();
                //添加
                if(empty($info)){
                    $value['addtime'] = time();
                    if(!empty($value['leagueLogo'])){
                        $value['leagueLogo'] = $value['leagueLogo'].'?win007=sell';
                    }

                    if(!empty($value['countryLogo'])){
                        $value['countryLogo'] = $value['countryLogo'].'?win007=sell';
                    }
                    M('LibraryLeague')->add($value);
                }

                //更新
                else{
                    if(!empty($value['color'])) $up['color'] = $value['color'];
                    if(!empty($value['nameEn'])) $up['nameEn'] = $value['nameEn'];
                    if(!empty($value['nameEnShort'])) $up['nameEnShort'] = $value['nameEnShort'];
                    if(!empty($value['nameChs'])) $up['nameChs'] = $value['nameChs'];
                    if(!empty($value['nameChsShort'])) $up['nameChsShort'] = $value['nameChsShort'];
                    if(!empty($value['nameCht'])) $up['nameCht'] = $value['nameCht'];
                    if(!empty($value['nameChtShort'])) $up['nameChtShort'] = $value['nameChtShort'];
                    if(!empty($value['type'])) $up['type'] = $value['type'];
                    if(!empty($value['subSclassEn'])) $up['subSclassEn'] = $value['subSclassEn'];
                    if(!empty($value['subSclassCn'])) $up['subSclassCn'] = $value['subSclassCn'];
                    if(!empty($value['sumRound'])) $up['sumRound'] = $value['sumRound'];
                    if(!empty($value['currRound'])) $up['currRound'] = $value['currRound'];
                    if(!empty($value['currSeason'])) $up['currSeason'] = $value['currSeason'];
                    if(!empty($value['countryId'])) $up['countryId'] = $value['countryId'];
                    if(!empty($value['countryEn'])) $up['countryEn'] = $value['countryEn'];
                    if(!empty($value['countryCn'])) $up['countryCn'] = $value['countryCn'];
                    if(!empty($value['leagueLogo'])) $up['leagueLogo'] = $value['leagueLogo'].'?win007=sell';
                    if(!empty($value['countryLogo'])) $up['countryLogo'] = $value['countryLogo'].'?win007=sell';
                    if(!empty($value['areaId'])) $up['areaId'] = $value['areaId'];
                    if($up){
                        $up['uptime'] = time();
                        M('LibraryLeague')->where(['id'=>$info['id']])->save($up);
                    }
                }
                unset($up);
            }
        }
        return $data;
    }

    //赛程赛果   有参数
    public function saichengData($da){
        // $url = "http://interface.feijing88.com/football/schedule.aspx";
        
        $Method = 'schedule';
        $key = $Method.'_'.md5(http_build_query($da));
        $data = $this->Redisdata($key);
        if(!$data){
            $param['date'] = $da['date'];
            $param['leagueId'] = $da['leagueId'];
            $param['matchId'] = $da['matchId'];
            $param['season'] = $da['season'];

            $param = array_filter($param);
            
            $url = $this->url.'/'.$Method.'.aspx?'.http_build_query($param);
            
            $list = $this->curl($url);
            
            $data = json_decode($list, true);
            
            $data = $data['matchList'];
            if(!empty($data)){
                foreach($data as &$value){
                    $value['homelogo'] = $this->getTeamLogo($value['homeId']);
                    $value['awaylogo'] = $this->getTeamLogo($value['awayId']);
                }
                $this->scheduleDb($data);
                $data = $this->Redisdata($key, $data, 60);
            }else{
                $data = false;
            }
        }
        
        return $data;
    }

    //赛程赛果 入库
    function scheduleDb($list){
        if(empty($list)){
            return false;
        }

        foreach($list as $value){
            if(!empty($value['startTime'])){
                $value['start_time'] = strtotime($value['startTime']);
                $value['start_date'] = date('Ymd', $value['start_time']);
            }else{
                $value['start_time'] = '';
                $value['start_date'] = '';
            }
            
            if(!empty($value['matchTime'])){
                $value['match_time'] = strtotime($value['matchTime']);
                $value['match_date'] = date('Ymd', $value['match_time']);
            }else{
                $value['match_time'] = '';
                $value['match_date'] = '';
            }

            $info = M('LibrarySchedule')->where(['matchId'=>$value['matchId']])->find();
            if(empty($info)){
                $value['addtime'] = time();
                M('LibrarySchedule')->add($value);
            }else{
                $value['uptime'] = time();
                M('LibrarySchedule')->where(['id'=>$info['id']])->save($value);
            }
        }
    }

    //前端获取赛程赛果
    public function retSaichengData($da){
        if(!empty($da['date'])){
            $strTime = strtotime($da['date']);
            $endTime = mktime(23,59,59,date('m', $strTime),date('d', $strTime),date('Y', $strTime));

            $param['match_time'] = [['EGT', $strTime],['ELT', $endTime]];
        }
        
        if(!empty($da['leagueId'])){
            $param['leagueId'] = $da['leagueId'];
        }
        
        if(!empty($da['matchId'])){
            $param['matchId'] = $da['matchId'];
        }

        if(!empty($da['season'])){
            $param['season'] = $da['season'];
        }

        $data = M('LibrarySchedule')->where($param)->limit(30)->select();
        if(!empty($data)){
            foreach($data as &$value){
                $value['homeHalfScore'] = $value['homehalfscore'];
                $value['subLeagueChs'] = $value['subLeaguechs'];
                $value['homeCorner'] = $value['homecorner'];
                $value['awayCorner'] = $value['awaycorner'];
                $value['awayScore'] = $value['awayscore'];
                $value['homeScore'] = $value['homescore'];
                $value['leagueChsShort'] = $value['leaguechsshort'];
                $value['matchTime'] = $value['matchtime'];
                $value['homeChs'] = $value['homechs'];
                $value['awayChs'] = $value['awaychs'];
                $value['homelogo'] = $this->getTeamLogo($value['homeid']);
                $value['awaylogo'] = $this->getTeamLogo($value['awayid']);
            }
        }
        return $data;

    }


    //获取球队赛程
    function TeamSacheng($TeamId){
        $monthNum = 10; //获取10个月的数据
        $map['homeId']  = $TeamId;
        $map['awayId']  = $TeamId;
        $map['_logic'] = 'OR';
        $maxTime = M('LibrarySchedule')->where($map)->order('match_time DESC')->getField('match_time');
        $minTime = mktime(0, 0 , 0,date("m",$maxTime)-$monthNum, 1, date("Y",$maxTime));
        
        $where['_complex'] = $map;
        $where['match_time'] = ['EGT', $minTime];
        $list = M('LibrarySchedule')->where($where)->order('match_time asc')->select();
        if(!empty($list)){
            foreach($list as $value){
                $value['date'] = date('m-d H:i', $value['match_time']);
                $value['week'] = '周'.$this->setWeek(date('w', $value['match_time']));
                if(empty($value['homelogo'])){
                    $value['homelogo'] = M('LibraryTeam')->where(['teamId'=>$value['homeid']])->getField('logo')?:'';
                }
               
                if(empty($value['awaylogo'])){
                    $value['awaylogo'] = M('LibraryTeam')->where(['teamId'=>$value['awayid']])->getField('logo')?:'';
                }

                $key = date('Y年m月', $value['match_time']);
                $_list[$key][] = $value;
            }

            foreach($_list as $key => $val){
                $_data['title'] = $key;
                $_data['list'] = $val;
                $data[] = $_data;
            }
        }

        return $data;
    }

    public function setWeek($week){
        switch ($week){
            case 0:
                $res = '日';
            break;
            case 1:
                $res = '一';
            break;
            case 2:
                $res = '二';
            break;
            case 3:
                $res = '三';
            break;
            case 4:
                $res = '四';
            break;
            case 5:
                $res = '五';
            break;
            case 6:
                $res = '六';
            break;
            default:
                $res = '日';
        }
        return $res;
    }

    //动画直播
    public function dhzb(){
        $key = 'dt/getAll';
        $url = $this->url.'/'.$key.'.aspx';
        $list = json_decode($this->curl($url), true);
        $list = $list['list'];
        if(!empty($list)){
            foreach($list as $value){
                $match = explode('^', $value);
                $matchId = $match[0];
                $data[$matchId] = $value;
            }
        }


        return $data;
    }

    //20.球员资料 频率限制：90秒/次 建议更新频率：24小时/次
    public function player($teamId){
        $key = 'player';
        $url = $this->url.'/'.$key.'.aspx?teamId='.$teamId;
        $data = $this->redis($key, $url);
        $data = $data['playerList'];
        if(!empty($data)){
            foreach($data as &$value){
                $birthday = explode(' ',$value['birthday']);
                $value['introduceCn'] = '';
                $value['age'] = $this->birthday(trim($birthday[0]));
                $v['positionCn'] = $value['positionCn'];
                $_data[$value['positionCn']][] = $value;
                
                $positionCn[] = $value['positionCn'];
            }

            $positionCn = array_unique($positionCn);

            $zhujiaol = $_data['主教练'];
            unset($_data['主教练']);
            $_data = array_merge(['主教练'=>$zhujiaol],$_data);
        }

        foreach($_data as $key => $va){
            $_list['positionCn'] = $key;
            $_list['player'] = $va;
            $list[] = $_list;
        }

        return $list;
    }

    //计算年龄
    function birthday($birthday){ 
        $age = strtotime($birthday); 
        if($age === false){ 
         return false; 
        } 
        list($y1,$m1,$d1) = explode("-",date("Y-m-d",$age)); 
        $now = strtotime("now"); 
        list($y2,$m2,$d2) = explode("-",date("Y-m-d",$now)); 
        $age = $y2 - $y1; 
        if((int)($m2.$d2) < (int)($m1.$d1)) 
         $age -= 1; 
        return $age; 
    } 

    //获取球队logo
    function getTeamLogo($TeamId){
        return M('LibraryTeam')->where(['teamId'=>$TeamId])->getField('logo')?:'';
    }


    /**
     * 比赛删除&修改时间记录
     * 接口返回过去12小时内的赛程删除、比赛时间修改记录.
     */
    public function ModifyRecord(){
        // $url = "http://interface.feijing88.com/football/ModifyRecord.aspx";
        $key = 'ModifyRecord';
        $url = $this->url.'/'.$key.'.aspx';
        $list = $this->redis($key, $url);

        return ['_list' => $list['changeList']];
    }

    /**
     * 比赛的详细事件&技术统计
     * 用于获取比赛的入球、红黄牌等比赛事件，以及技术统计数据.
     */
    public function detailData()
    {
        // $url = "http://interface.feijing88.com/football/detail.aspx";
        $key = 'detail';
        $url = $this->url.'/'.$key.'.aspx';
        $list = $this->redis($key, $url);

        return ['_list' => $list['eventList']];
    }

    //杯赛联赛资料
    public function LeagueData()
    {
        // $url = "http://interface.feijing88.com/football/league.aspx";
        $key = 'league';
        $url = $this->url.'/'.$key.'.aspx';

        $list = $this->redis($key, $url);

        return ['_list' => $list['leagueList']];
    }

    //球队资料
    public function teamData()
    {
        // $url = "http://interface.feijing88.com/football/team.aspx";
        $key = 'team';
        $url = $this->url.'/'.$key.'.aspx';

        return $this->redis($key, $url);
    }

    //单盘口赔率：即时赔率接口
    public function odds()
    {
        // $url = "http://interface.feijing88.com/football/odds.aspx";
        $key = 'oddsData';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //单盘口赔率：30秒内变化赔率接口
    public function oddsChange()
    {
        //$url = "http://interface.feijing88.com/football/oddsChange.aspx";
        $key = 'oddsChange';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //单盘口历史赔率/未来赔率
    public function historyOdds(){
        //$url = "http://interface.feijing88.com/football/historyOdds.aspx";
        $re_date = I('date');
        if ($re_date) {
            $data = '2014-11-11'; //这里可以任意格式，因为strtotime函数很强大
            $is_date = strtotime($re_date) ? strtotime($data) : false;

            if (false === $is_date) {
                exit('输入的日期格式不正确');
            } else {
                $url = $this->url.'/historyOdds?date='.$re_date;
            }
        } else {
            $url = $this->url.'/historyOdds';
        }
        $key = 'historyOdds';

        return $this->redis($key, $url);
    }

    //半场欧赔
    public function banchang()
    {
        //$url ="http://interface.feijing88.com/football/1x2_half.aspx";
        $key = 'banChang';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //多盘口赔率:即时
    public function duoOdds()
    {
        //$url = "http://interface.feijing88.com/football/odds_Mult.aspx";
        $key = 'duoOdds';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //多盘口赔率：30秒内变化赔率
    public function duoOddsChange()
    {
        //$url = "http://interface.feijing88.com/football/oddsChange_Mult.aspx";
        $key = 'duoOddsChange';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //多盘口历史赔率/未来赔率
    public function duoHistoryOdds()
    {
        //$url = "http://interface.feijing88.com/football/historyOdds_mult.aspx";
        $re_date = I('date');
        if ($re_date) {
            $data = '2014-11-11'; //这里可以任意格式，因为strtotime函数很强大
            $is_date = strtotime($re_date) ? strtotime($data) : false;

            if (false === $is_date) {
                exit('输入的日期格式不正确');
            } else {
                $url = $this->url.'/duoHistoryOdds?date='.$re_date;
            }
        } else {
            $url = $this->url.'/duoHistoryOdds';
        }
        $key = 'duoHistoryOdds';

        return $this->redis($key, $url);
    }

    //走地赔率
    public function oddsRunning()
    {
        //$url = "http://interface.feijing88.com/football/odds_Running.aspx";
        $key = 'oddsRunning';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //走地赔率  半场
    public function oddsRunningHalf()
    {
        //$url = "http://interface.feijing88.com/football/odds_Running_Half.aspx";
        $key = 'oddsRunningHalf';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //竞彩足球
    public function JczqOdds(){
        //$url = "http://interface.feijing88.com/lottery/JczqOdds.aspx";
        $key = 'JczqOdds';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //彩票赛程与比赛ID关联表
    public function MatchidInterface()
    {
        //$url = "http://interface.feijing88.com/lottery/MatchidInterface.aspx";
        $key = 'MatchidInterface';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //彩票比赛的比分数据
    public function Zq_BF()
    {
//        $url = "http://interface.feijing88.com/lottery/Zq_BF.aspx";
        $key = 'Zq_BF';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //彩票比赛的事件&技术统计
    public function Zq_Event()
    {
//        $url = "http://interface.feijing88.com/lottery/Zq_Event.aspx";
        $key = 'Zq_Event';
        $url = $this->url.'/'.$key;

        return $this->redis($key, $url);
    }

    //25.联赛/杯赛球员详细技术统计 频率限制：15秒/次 建议更新频率：无
    public function playerCount($da=array()){

        $Method = 'playerCount';
        $key = $Method.'_'.md5(http_build_query($da));
        $data = $this->Redisdata($key);
        if(!$data){
            $param['leagueId'] = $da['leagueId'];

            $param = array_filter($param);

            $url = $this->url.'/'.$Method.'.aspx?'.http_build_query($param);

            $list = $this->curl($url);
            $data = json_decode($list, true);
            $data = $data['list'];
            if(!empty($data)){
                $data = $this->Redisdata($key, $data, 60);
            }
        }

        return $data;
    }


    //curl  get请求
    public function curl($url)
    {
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
    }
}
