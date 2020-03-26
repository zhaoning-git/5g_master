<?php
namespace Common\Model;

//竞猜
use Think\Model;

class JingcaiModel extends Model {
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '竞猜名称不能为空！', 1, self:: MODEL_BOTH),
        array('type', 'require',  '竞猜类型不能为空！', 1, self:: MODEL_BOTH),
        array('maxbets', 'require',  '发起底注不能为空！', 1, self:: MODEL_BOTH),
        array('match_id', 'require',  '比赛ID不能为空！', 1, self:: MODEL_BOTH),
        array('cyrs', 'require',  '参与人数不能为空！', 1, self:: MODEL_BOTH),
    );
    
    
    
    //竞猜详情
    function JingcaiInfo($id){
        $info = $this->where(array('id'=>$id))->find();
        if(!empty($info)){
            return $this->setJingcai($info);
        }else{
            $this->error = '竞猜不存在!';
            return false;
        }
        
    }

    //整理数据
    //$info 竞猜详情
    //竞猜类型
    //1:胜平负
    //2:具体比分
    //3:进球数
    //4:半全场
    //5:让球胜平负
    function setJingcai($info){
        $info['odds'] = json_decode($info['odds'],true);
        $_odds = array();
        switch ($info['type']){
            //1:胜平负
            case 1:
                $odds = $info['odds']['spf'];
                if(!empty($odds)){
                    $v = array(
                        'spf3' => '胜',
                        'spf1' => '平',
                        'spf0' => '负',
                    );
                    
                    foreach ($odds as $key => $value){
                        if(!empty($value)){
                            $_d['m'] = $key;
                            $_d['s'] = $v[$key];
                            $_d['r'] = $value;
                            $_odds[$key] = $_d;
                        }
                    }
                }
                
                break;
            
            //2:具体比分
            case 2:
                $odds = $info['odds']['bf'];
                unset($odds['sw5'],$odds['sd4'],$odds['sl5'],$odds['stop']);
                if(!empty($odds)){
                    foreach ($odds as $key => $value){
                        if(!empty($value)){
                            $str = substr($key, 2, 2);
                            $_d['m'] = $key;
                            $_d['s'] = $str{0}.'-'.$str{1};
                            $_d['r'] = $value;
                            $_odds[$key] = $_d;
                        }
                    }
                }
                
                break;
            
            //3:进球数
            case 3:
                $odds = $info['odds']['jq'];
                if(!empty($odds)){
                    foreach ($odds as $key => $value){
                        if(!empty($value)){
                            $str = substr($key, 1, 1);
                            $_d['m'] = $key;
                            $_d['s'] = $str==7? $str.'+个进球': $str.'个进球';
                            $_d['r'] = $value;
                            $_odds[$key] = $_d;
                        }
                    }
                }
                
                break;
            
            //4:半全场
            case 4:
                $odds = $info['odds']['bqc'];
                if(!empty($odds)){
                    $v = array(
                        'ht33' => '半场胜全场胜',
                        'ht31' => '半场胜全场平',
                        'ht30' => '半场胜全场负',
                        'ht13' => '半场平全场胜',
                        'ht11' => '半场平全场平',
                        'ht10' => '半场平全场负',
                        'ht03' => '半场负全场胜',
                        'ht01' => '半场负全场平',
                        'ht00' => '半场负全场负',
                    );
                    foreach ($odds as $key => $value){
                        if(!empty($value)){
                            $_d['m'] = $key;
                            $_d['s'] = $v[$key];
                            $_d['r'] = $value;
                            $_odds[$key] = $_d;
                        }
                    }
                }
                
                break;
            
            //5:让球胜平负
            case 5:
                $odds = $info['odds']['rqspf'];
                if(!empty($odds)){
                    $v = array(
                        'rq3' => '让球胜',
                        'rq1' => '让球平',
                        'rq0' => '让球负',
                    );
                    
                    foreach ($odds as $key => $value){
                        if(!empty($value)){
                            $_d['m'] = $key;
                            $_d['s'] = $v[$key];
                            $_d['r'] = $value;
                            $_odds[$key] = $_d;
                        }
                    }
                }
                
                break;
                
        }
        $info['maxRate'] = max($odds);
        $info['odds'] = $_odds;
        return $info;
    }

    //发起竞猜
    //$data['maxbets'] 底注(单场最大可以输的银币)
    function addJingcai($data){
        
        $data = $this->create($data);
        if(!$data){
            $this->error = $this->getError();
            return false;
        }
        
        if($this->where(array('uid'=>$data['uid'],'status'=>array('LT', 2)))->count()){
            $this->error = '还有未结束的竞猜!';
            return false;
        }
        
        $Ins['uid'] = $data['uid'];
        $Ins['name'] = $data['name'];
        $Ins['type'] = $data['type'];
        
        if($this->where(array('name'=>$Ins['name']))->count()){
            $this->error = '竞猜名称重复!';
            return false;
        }
        
        if($Ins['type'] < 1 || $Ins['type'] > 5){
            $this->error = '不支持的竞猜类型';
            return false;
        }
        
        //发起方底注
        if(!$this->JingConfig('fqdz', $data['uid'], $data['maxbets'])){
            return false;
        }
        
        //发起方发起次数
        if(!$this->JingConfig('fqcs', $data['uid'], $data['maxbets'])){
            return false;
        }
        
        //发起方参与人数
        if(!$this->JingConfig('fqrs', $data['uid'], $data['cyrs'])){
            return false;
        }
        
        //发起方结算输/赢
        if(!$this->JingConfig('fqsy', $data['uid'], $data['maxbets'])){
            return false;
        }
        
        $Ins['match_id'] = $data['match_id'];
        
        $Match = $this->getMatch();
        
        $odds  = $Match['odds'][$Ins['match_id']];
        if(empty($odds)){
            $this->error = '赔率数据获取错误!';
            return false;
        }
        
        $match = $Match['match'][$Ins['match_id']];
        if(empty($match)){
            $this->error = '比赛数据获取错误!';
            return false;
        }
        
        $info['odds'] = json_encode($odds);
        $info['type'] = $data['type'];
        $maxRate = $this->setJingcai($info);
        $maxRate = $maxRate['maxRate'];
        
        $Ins['maxrate'] = $maxRate;
        $Ins['cyrs'] = $data['cyrs'];
        $Ins['maxbets'] = $data['maxbets'];
        $Ins['match_date'] = $match['matchTime'];
        $Ins['match_time'] = strtotime($match['matchTime']);
        $Ins['home']   = $match['home'];
        $Ins['away']   = $match['away'];
        $Ins['isTurn']   = $match['isTurn']?1:0;
        $Ins['odds']   = json_encode($odds);
        $Ins['add_date'] = date('Ymd', NOW_TIME);
        $Ins['addtime'] = NOW_TIME;
        
        $id = $this->add($Ins);
        
        if($id){
            //冻结底注金额
            M('Users')->where(array('id' => $data['uid']))->setDec('silver_coin', $data['maxbets']);
            $this->where(array('id'=>$id))->setField('frozen_bets', $data['maxbets']);
            return true;
        }
        
    }
    
    //参与竞猜
    function inJingcai($data){
        $data['uid']        = intval($data['uid']);
        $data['jingcai_id'] = intval($data['jingcai_id']);
        $data['betting']    = intval($data['betting']);//下注金额(银币)
        $Info = $this->JingcaiInfo($data['jingcai_id']);
        if(empty($Info)){
            $this->error = '竞猜不存在!';
            return false;
        }else{
            $MatchResult = D('MatchResult')->getMatchResult($Info['match_id']);
            if($MatchResult['state'] != 0){
                $this->error = '比赛不是未开场状态,无法参与竞猜!';
                return false;
            }
        }
        
        if(M('JingcaiLog')->where(['uid'=>$data['uid'], 'jingcai_id'=>$data['jingcai_id']])->count()){
            $this->error = '您已参与该竞猜!';
            return false;
        }

        //参与方底注
        if(!$this->JingConfig('cydz', $data['uid'], $data['betting'])){
            return false;
        }

        //参与方结算输赢 当日累计
        if(!$this->JingConfig('cysy', $data['uid'])){
            return false;
        }
        
        $jcNum = $this->JingcaiNum($data['jingcai_id']);
        
        if($jcNum <= 0){
            $this->error = '参与竞猜用户已达上限!';
            return false;
        }
        
        if(!$data['uid']){
            $this->error = '参与竞猜用户ID参数错误';
            return false;
        }
        
        if(!$data['jingcai_id']){
            $this->error = '竞猜ID参数错误';
            return false;
        }
        
        if(!$data['betting']){
            $this->error = '下注金额参数错误';
            return false;
        }else{
            $userSilver = M('Users')->where(array('id' => $data['uid']))->getField('silver_coin');
            if($userSilver < $data['betting']){
                $this->error = '银币不足!';
                return false;
            }
        }
        
        if(empty($data['mycai'])){
            $this->error = '我猜不能为空';
            return false;
        }
        
        if($Info['uid'] == $data['uid']){
            $this->error = '不能参与自己发起的竞猜!';
            return false;
        }
        
        if(!array_key_exists($data['mycai'], $Info['odds'])){
            $this->error = '我猜值不存在!';
            return false;
        }else{
            $data['ratio'] = $Info['odds'][$data['mycai']]['r'];
        }
        
        //如果参与方赢发起方最大赔付金额
        $data['yj_profit'] = $data['betting'] * $data['ratio'];
        
        $jingcaiPeifu = M('JingcaiLog')->where(array('jingcai_id'=>$data['jingcai_id']))->sum('yj_profit')?:0;
        
        if(($jingcaiPeifu + $data['yj_profit']) > $Info['maxbets']){
            $this->error = '发起方最大赔付金额还剩余:'.($Info['maxbets'] - $jingcaiPeifu).'!';
            return false;
        }
        
        
        $data['maxrate'] = $Info['maxrate'];
        $data['fq_uid']  = $Info['uid'];
        $data['addtime'] = NOW_TIME;
        
        $id = M('JingcaiLog')->add($data);
        
        if($id){
            //冻结下注金额
            M('Users')->where(array('id' => $data['uid']))->setDec('silver_coin', $data['betting']);
            M('JingcaiLog')->where(array('id'=>$id))->setField('frozen_betting', $data['betting']);
        }
        return $Info;
    }
    
    //竞猜结果
    //$id 竞猜ID
    //$MatchResult['state']比赛状态0:未开 1:上半场 2:中场 3:下半场 4:加时 5:点球 -1:完场 -10:取消 -11:待定 -12:腰斩 -13:中断 -14:推迟
    function ResultJingcai($id){
        //竞猜信息
        $JcInfo = $this->JingcaiInfo($id);
        if(!$JcInfo){
            return false;
        }elseif($JcInfo['state'] == 2){
            $this->error = '比赛已结束';
            return false;
        }elseif($JcInfo['status'] == 2){
            $this->error = '竞猜已结束';
            return false;
        }
        
        //足球即时竞彩 2.即时变化的比分数据
        $MatchResult = D('MatchResult')->getMatchResult($JcInfo['match_id']);
        
        if($MatchResult['state'] > 0){
            $this->where(array('id'=>$JcInfo['id']))->setField('status', 1);
            $this->error = '比赛进行中';
            return false;
        }
        
        elseif($MatchResult['state'] == 0){
            $this->error = '比赛未开始';
            return false;
        }
        
        elseif($MatchResult['state'] == -1){
            return $this->setLog($id);
        }
        return $MatchResult;
    }
    
    //$id 竞猜ID
    function setLog($id){
        $list = M('JingcaiLog')->where(array('jingcai_id'=>$id,'status'=>0))->select();
        if(!empty($list)){
            foreach ($list as $value){
                $_data['result'] = $this->getResult($id); //比赛结果

                //猜中
                if($value['mycai'] == $_data['result']){
                    $_data['profit'] = $value['betting'] * $value['ratio'];
                    $fq_profit = -$_data['profit']; //发起人赔付金额
                    $conType = 'cy_jing_cai_ying';
                }

                //未猜中
                else{
                    $_data['profit'] = -$value['betting'];
                    $fq_profit = $value['betting']; //发起人赢利金额
                    $conType = 'cy_jing_cai_shu';
                }
                
                $_data['status'] = 1;
                $_data['uptime'] = NOW_TIME;
                //$_data['frozen_betting'] = 0;
                $data[] = $_data;

                if(M('JingcaiLog')->where(array('id'=>$value['id']))->save($_data)){
                    //发起竞猜收益和发起竞猜冻结
                    $JingcaiUp['profit'] = array('exp', "profit+$fq_profit");
                    if($fq_profit < 0){
                        $JingcaiUp['frozen_bets'] = array('exp', "frozen_bets+$fq_profit");
                    }
                    $this->where(array('id'=>$id))->save($JingcaiUp);
                    unset($fq_profit,$JingcaiUp);
                    
                    
                    //发起竞猜者获得参与者下注金额 $value['frozen_betting']
                    D('UsersCoinrecord')->addCoin($value['id'], 'fq_getcy_frozen_betting');
                    
                    D('UsersCoinrecord')->addCoin($value['id'], $conType);
                    
                    //参与竞猜冻结底注
                    M('JingcaiLog')->where(array('id'=>$value['id']))->setField('frozen_betting', 0);
                    
                    //D('UsersCoinrecord')->addCoin($value['id'], 'fq_jing_cai_ying');
                    D('UsersCoinrecord')->addCoin($value['id'], 'fq_jing_cai_shu');
                    //竞猜有奖
                    D('JingcaiReceiveLog')->addLog($value['uid']);
                }
            }
            
            //返回发起者剩余冻结底注
            if($this->where(array('id'=>$id))->getField('frozen_bets') > 0){
               D('UsersCoinrecord')->addCoin($id, 'fq_fanhui_frozen_bets');
               $jcup['frozen_bets'] = 0;
            }

            $jcup['status'] = 2;
            $jcup['uptime'] = NOW_TIME;

            $this->where(array('id'=>$id))->save($jcup);
                    
        }
        
        else{
            $this->error = '参与记录不存在!';
            return false;
        }
    }

    //获取比赛结果
    //$id 竞猜ID
    function getResult($id){
        //竞猜信息
        $JcInfo = $this->JingcaiInfo($id);
        
        //足球即时竞彩 2.即时变化的比分数据
        $MatchResult = D('MatchResult')->getMatchResult($JcInfo['match_id']);

        $odds = $JcInfo['odds'];
        
        if($JcInfo['isTurn'] == 1){ //主客是否翻转0:否 1:是
            //全场
            $homescore = $MatchResult['awayscore'];
            $awayscore = $MatchResult['homescore'];
            
            //半场
            $homeHalfScore = $MatchResult['awayHalfScore'];
            $awayHalfScore = $MatchResult['homeHalfScore'];
            
            //角球
            $homeCorner = $MatchResult['awayCorner'];
            $awayCorner = $MatchResult['homeCorner'];
            
        }else{
            //全场
            $homescore = $MatchResult['homescore'];
            $awayscore = $MatchResult['awayscore'];
            
            //半场
            $homeHalfScore = $MatchResult['homeHalfScore'];
            $awayHalfScore = $MatchResult['awayHalfScore'];
            
            //角球
            $homeCorner = $MatchResult['homeCorner'];
            $awayCorner = $MatchResult['awayCorner'];
            
        }
        
        
        //1.胜平负
        if($JcInfo['type'] == 1){
            if($homescore == $awayscore){
                $res = 'spf1';//平
            }

            elseif($homescore > $awayscore){
                $res = 'spf3';//胜
            }

            elseif($homescore < $awayscore){
                $res = 'spf0';//负
            }
        }
        
        //2.具体比分
        elseif($JcInfo['type'] == 2){
            $bf = $homescore.'-'.$awayscore;
            foreach ($odds as $value){
                if($bf == $value['s']){
                    $res = $value['m'];
                    break;
                }
            }
        }
        
        //3.进球数
        elseif($JcInfo['type'] == 3){
            $res = 't'.$homescore + $awayscore;
        }
        
        //4.半全场
        elseif($JcInfo['type'] == 4){
            //半场胜全场胜
            if($homeHalfScore > $awayHalfScore && $homescore > $awayscore){
                $res = 'ht33';
            }
            
            //半场胜全场平
            elseif($homeHalfScore > $awayHalfScore && $homescore == $awayscore){
                $res = 'ht31';
            }
            
            //半场胜全场负
            elseif($homeHalfScore > $awayHalfScore && $homescore < $awayscore){
                $res = 'ht30';
            }
            
            //半场平全场胜
            elseif($homeHalfScore == $awayHalfScore && $homescore > $awayscore){
                $res = 'ht13';
            }
            
            //半场平全场平
            elseif($homeHalfScore == $awayHalfScore && $homescore == $awayscore){
                $res = 'ht11';
            }
            
            //半场平全场负
            elseif($homeHalfScore == $awayHalfScore && $homescore < $awayscore){
                $res = 'ht10';
            }
            
            //半场负全场胜
            elseif($homeHalfScore < $awayHalfScore && $homescore > $awayscore){
                $res = 'ht03';
            }
            
            //半场负全场平
            elseif($homeHalfScore < $awayHalfScore && $homescore == $awayscore){
                $res = 'ht01';
            }
            
            //半场负全场负
            elseif($homeHalfScore < $awayHalfScore && $homescore < $awayscore){
                $res = 'ht00';
            }
        }
        
        //5.让球胜平负
        elseif($JcInfo['type'] == 5){
            if($homescore == $awayscore){
                $res = 'rq1';//平
            }

            elseif($homescore > $awayscore){
                $res = 'rq3';//胜
            }

            elseif($homescore < $awayscore){
                $res = 'rq0';//负
            }
        }
        
        return $res;
        
    }

    //参与竞猜上限
    function JingcaiNum($jcId){
        $jcId = intval($jcId);
        $count = M('JingcaiLog')->where(array('id'=>$jcId))->count();
        
        $cyrs = $this->where(array('id'=>$jcId))->getField('cyrs');
        return $cyrs-$count;
        
    }


    //获取用于竞猜的比赛和赔率
    function getMatch(){
        $Peilv = D('Match')->JczqOdds();
        $MatchidInterface = $this->MatchidInterface();
        if(empty($Peilv['list']) || !$MatchidInterface){
            $this->error = '没有获取到数据!';
            return false;
        }
        
        $Result = array('match'=>[], 'odds'=>[]);
        
        foreach ($Peilv['list'] as $value){
            $arr = $MatchidInterface[$value['id']];
            if(!empty($arr)){
                $_data['matchId'] = $arr['matchId'];
                $_data['week'] = $value['id'];
                $_data['matchTime'] = $value['matchTime'];
                $_data['home'] = $value['home'];
                $_data['away'] = $value['away'];
                $_data['league'] = $arr['league'];
                $_data['isTurn'] = $arr['isTurn'];
                
                
                $_ratio['rqspf']  = $value['rqspf'];//让球胜平负
                $_ratio['bf']  = $value['bf'];//比分
                $_ratio['jq']  = $value['jq'];//进球
                $_ratio['bqc']  = $value['bqc'];//半全场
                $_ratio['spf']  = $value['spf'];//胜平负

                $ratio[$arr['matchId']] = $_ratio;
                $data[$arr['matchId']] = $_data;
            }
        }
        
        $Result['match'] = $data;
        $Result['odds']  = $ratio;
        return $Result;
    }
    
    //8.彩票赛程与比赛ID关联表
    function MatchidInterface(){
        $MatchidInterface = D('Match')->MatchidInterface();
        if(!empty($MatchidInterface)){
            foreach ($MatchidInterface['list'][0]['jczq'] as $value){
                $data[$value['id']] = $value;
            }
            return $data;
        }
        return false;
    }
    
    //竞猜配置
    function JingConfig($type, $uid, $value=''){
        $JincaiConfig = getcaches('getConfigJingcai');
        
        //发起底注
        if ($type == 'fqdz'){
            //发起方底注浮动值
            if($JincaiConfig['fq_dizhu_radio'] == 'fd'){
                $Silver = M('Users')->where(array('id' => $uid))->getField('silver_coin');
                $maxPeifu = floor($Silver * $JincaiConfig['fq_dizhu_fd'] / 100);
                if($value > $maxPeifu){
                    $this->error = '由于超出竞猜单场赔付银币最大值'.$maxPeifu.'，请选择调整底注、选择较低赔率玩法、调低参与人数等方式重新发起竞猜';
                    return false;
                }else{
                    return true;
                }
            }
            //发起方底注固定范围
            else{
                if($value < $JincaiConfig['fq_dizhu_fw_1'] || $value > $JincaiConfig['fq_dizhu_fw_2']){
                    $this->error = '由于底注不在'.$JincaiConfig['fq_dizhu_fw_1'].'-'.$JincaiConfig['fq_dizhu_fw_2'].'范围内，请选择调整底注、选择较低赔率玩法、调低参与人数等方式重新发起竞猜';
                    return false;
                }else{
                    return true;
                }
            }
        }
        
        //发起次数
        elseif($type == 'fqcs'){
            $map['uid'] = $uid;
            $map['add_date'] = date('Ymd', NOW_TIME);
            $csCount = $this->where($map)->count();
            
            //发起次数浮动值
            if($JincaiConfig['fq_cs_radio'] == 'fd'){
                $fd = floor($JincaiConfig['fq_cs_gd'] + $JincaiConfig['fq_cs_gd'] * $JincaiConfig['fq_cs_fd'] / 100);
            }else{
                $fd = $JincaiConfig['fq_cs_gd'];
            }
            
            if($csCount > $fd){
                $this->error = '您今天的发起次数已达限值'.$fd;
                return false;
            }
            return true;
        }
        
        //发起参与人数
        elseif ($type == 'fqrs') {
            if($JincaiConfig['fq_rs_radio'] == 'fd'){
                $rs = floor($JincaiConfig['fq_rs_gd'] + $JincaiConfig['fq_rs_gd'] * $JincaiConfig['fq_rs_fd'] / 100);
            }else{
                $rs = $JincaiConfig['fq_rs_gd'];
            }
            
            if($value > $rs){
                $this->error = '参与数已达限值'.$rs;
                return false;
            }
            return true;
        }
        
        //发起方结算输赢 当日累计
        elseif ($type == 'fqsy'){
            $map['uid'] = $uid;
            $map['action'] = 'fq_jing_cai_ying';
            $map['add_date'] = date('Ymd', NOW_TIME);
            
            //发起方今日赢银币
            $Profit = M('UsersCoinrecord')->where($map)->sum('giftcount');
            if($Profit >= $JincaiConfig['fq_js_ying']){
                $this->error = '今日赢银币已达上限';
                return false;
            }
            
            //发起方今日输银币
            $map['action'] = 'fq_jing_cai_shu';
            $Shu = M('UsersCoinrecord')->where($map)->sum('giftcount');
            if($Shu >= $JincaiConfig['fq_js_shu']){
                $this->error = '今日赢银币已达上限';
                return false;
            }
            return true;
        }
        
        //参与底注
        elseif($type == 'cydz'){
            //参与方底注浮动值
            if($JincaiConfig['cy_dizhu_radio'] == 'fd'){
                $Silver = M('Users')->where(array('id' => $uid))->getField('silver_coin');
                $dizhu = floor($Silver * $JincaiConfig['cy_dizhu_fd'] / 100);
                if($value >= $dizhu){
                    $this->error = '由于超出竞猜单场赔付银币最大值'.$dizhu.'，请选择调整底注、选择较低赔率玩法、调低参与人数等方式重新发起竞猜';
                    return false;
                }else{
                    return true;
                }
            }
            //发起方底注固定范围
            else{
                if($value < $JincaiConfig['cy_dizhu_fw_1'] || $value > $JincaiConfig['cy_dizhu_fw_2']){
                    $this->error = '由于底注不在'.$JincaiConfig['cy_dizhu_fw_1'].'-'.$JincaiConfig['cy_dizhu_fw_2'].'范围内，请选择调整底注、选择较低赔率玩法、调低参与人数等方式重新发起竞猜';
                    return false;
                }else{
                    return true;
                }
            }
            
        }
        
        //参与方结算输赢 当日累计
        elseif ($type == 'cysy'){
            $map['uid'] = $uid;
            $map['action'] = 'fq_jing_cai_ying';
            $map['add_date'] = date('Ymd', NOW_TIME);
            
            //发起方今日赢银币
            $Profit = M('UsersCoinrecord')->where($map)->sum('giftcount');
            if($Profit >= $JincaiConfig['fq_js_ying']){
                $this->error = '今日赢银币已达上限';
                return false;
            }
            
            //发起方今日输银币
            $map['action'] = 'fq_jing_cai_shu';
            $Shu = M('UsersCoinrecord')->where($map)->sum('giftcount');
            if($Shu >= $JincaiConfig['fq_js_shu']){
                $this->error = '今日赢银币已达上限';
                return false;
            }
            return true;
        }
        
        
        
    }
    
    
    
}

