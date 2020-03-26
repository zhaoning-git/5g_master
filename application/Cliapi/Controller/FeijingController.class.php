<?php
/**
 * 竞猜
 */

namespace Cliapi\Controller;

use Think\Controller;
use think\Db;
use Common\Model\MatchModel;

class FeijingController extends MemberController
{

    function _initialize()
    {
        parent::_initialize();
    }


    public function getFootBallData()
    {
        $date = I('date');
        if (!$date) {
            $date = date('Y-m-d', time());
        }

        $data = $this->curl_file_get_contents('http://interface.win007.com/football/schedule.aspx?date=' . $date);
        $data = json_decode($data, true);

        $return_data = [];
        $return_data['_list'] = [];

        foreach ($data['matchList'] as $key => $value) {
            //主队
            $homeId = $value['homeId'];
            $home_team = D('library_team')->field('logo')->where('teamId', $homeId)->find();
            $return_data['_list'][$key]['homeLogo'] = $home_team['logo'];
            //客队
            $awayId = $value['awayId'];
            $away_team = D('library_team')->field('logo')->where('teamId', $awayId)->find();
            $return_data['_list'][$key]['awayLogo'] = $away_team['logo'];

            $return_data['_list'][$key]['leagueEn'] = $value['leagueEn'];
            $return_data['_list'][$key]['leagueEnShort'] = $value['leagueEnShort'];
            $return_data['_list'][$key]['leagueChsShort'] = $value['leagueChsShort'];
            $return_data['_list'][$key]['subLeagueEn'] = $value['subLeagueEn'];
            $return_data['_list'][$key]['subLeagueChs'] = $value['subLeagueChs'];
            $return_data['_list'][$key]['subLeagueCht'] = $value['subLeagueCht'];
            $return_data['_list'][$key]['matchTime'] = $value['matchTime'];
            $return_data['_list'][$key]['startTime'] = $value['startTime'];
            $return_data['_list'][$key]['homeEn'] = $value['homeEn'];
            $return_data['_list'][$key]['homeChs'] = $value['homeChs'];
            $return_data['_list'][$key]['homeCht'] = $value['homeCht'];
            $return_data['_list'][$key]['awayEn'] = $value['awayEn'];
            $return_data['_list'][$key]['awayChs'] = $value['awayChs'];
            $return_data['_list'][$key]['awayCht'] = $value['awayCht'];
            $return_data['_list'][$key]['homeScore'] = $value['homeScore'];
            $return_data['_list'][$key]['awayScore'] = $value['awayScore'];


        }
        $this->ajaxRet(1, '成功', $return_data);


    }

    /**
     * 获取赛程
     */
    public function getSaiChengData()
    {
        $days = I('days');
        $date = date('Y-m-d', time() - (86400 * $days));
        $return_data = [];
        $return_data['_list'] = [];
        $return_data['_list']['date'] = $date;
        $return_data['_list']['match_list'] = $this->getDateSaiChengData($date);

        $this->ajaxRet(1, '成功', $return_data);
    }

    /**
     * 获取联赛/杯赛球员详细技术统计
     */
    public function getPlayerCountData()
    {
        $teamId = I('team_id');

        if (!$teamId) {
            $this->ajaxRet(0, '缺少球队id');
        }

        //获取球队对应的联赛id
        $leagueId = M('LibraryTeam')->where(['teamId' => $teamId])->find()['leagueid'];

        //获取所有数据
        $data = D('Match')->playerCount([
            'leagueId' => $leagueId
        ]);


        //从数据筛选出属于该球队的所有球员数据
        $data = $this->filter_by_value($data, 'teamId', $teamId);

        //配置字段
        $config_field = [
            [
                'name' => '进球',
                'field' => 'goals',
                'group' => '0',
            ],
            [
                'name' => '点球',
                'field' => 'penaltyGoals',
                'group' => '0',
            ],
            [
                'name' => '射门',
                'field' => 'shots',
                'group' => '0',
            ],
            [
                'name' => '射正',
                'field' => 'shotsTarget',
                'group' => '0',
            ],
            [
                'name' => '传球数',
                'field' => 'totalPass',
                'group' => '1',
            ],
            [
                'name' => '传球成功数',
                'field' => 'passSuc',
                'group' => '1',
            ],
            [
                'name' => '关键传球数',
                'field' => 'keyPass',
                'group' => '1',
            ],
            [
                'name' => '铲断',
                'field' => 'tackles',
                'group' => '2',
            ],
            [
                'name' => '拦截',
                'field' => 'interception',
                'group' => '2',
            ],
            [
                'name' => '解围',
                'field' => 'clearance',
                'group' => '2',
            ],
            [
                'name' => '犯规',
                'field' => 'fouls',
                'group' => '3',
            ],
            [
                'name' => '红牌',
                'field' => 'red',
                'group' => '3',
            ],
            [
                'name' => '黄牌',
                'field' => 'yellow',
                'group' => '3',
            ],

        ];



        //将动作分组处理
        $return_data = [
            ['group_name' => '进攻'],
            ['group_name' => '组织'],
            ['group_name' => '防守'],
            ['group_name' => '纪律']
        ];


        foreach ($config_field as $key =>$value)
        {
            $return_data[$value['group']]['data'][] = [
                'name' => $value['name'],
                'value' =>  array_sum(array_column($data, $value['field']))
            ];

        }


        $this->ajaxRet(1, '成功', ['_list'=>$return_data]);
    }


    /**
     * 处理赛程数据
     * @param $date
     * @return array
     */
    protected function getDateSaiChengData($date)
    {
        $data = D('Match')->retSaichengData([
            'date' => $date
        ]);

        //$data = array_slice($data, 0, 30);

        $return_data = [];

        $filed = [
            'homelogo',
            'awaylogo',
            'leagueEn',
            'leagueEnShort',
            'leagueChsShort',
            'leagueChtShort',
            'subLeagueEn',
            'subLeagueChs',
            'subLeagueCht',
            'matchTime',
            'startTime',
            'homeEn',
            'homeChs',
            'homeCht',
            'awayEn',
            'awayChs',
            'awayCht',
            'homeScore',
            'awayScore'
        ];

        foreach ($data as $key => $value) {
            foreach ($filed as $filed_value) {
                $return_data[$key][$filed_value] = $value[$filed_value];
            }
            $return_data[$key]['matchDay'] = date('Y-m-d', strtotime($value['matchTime']));
        }

        return $return_data;

    }

    public function getTeamInfo()
    {
        $teamId = I('team_id');

        if (!$teamId) {
            $this->ajaxRet(0, '缺少球队id');
        }

        $team = M('library_team')->field(
            'foundingdate',
            'areaCn',
            'gymCn',
            'mobile'
        )->where('teamId',$teamId)->find();

    }

    /**
     * 数组过滤筛选
     * @param $array
     * @param $index
     * @param $value
     * @return mixed
     */
    protected function filter_by_value($array, $index, $value)
    {
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key) {
                $temp[$key] = $array[$key][$index];
                if ($temp[$key] == $value) {
                    $newarray[$key] = $array[$key];
                }
            }
        }
        return $newarray;
    }

}
