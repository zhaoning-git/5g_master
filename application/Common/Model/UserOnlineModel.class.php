<?php
//在线记录
namespace Common\Model;

use Think\Model;

class UserOnlineModel extends Model {
    
    protected $_validate = array(
        array('uid', 'require', '用户ID不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('type', 'require', '类型不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
        array('start', 'require', '开始时间不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
        array('stage_end', 'require', '阶段结束时间不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
    );
    
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );
    
    //每30分钟增加一条记录
    public function addOnline($data){
        $Second = 30*60; //30分钟的秒数
        
        $data = $this->create($data);
        if (!$data) {
            $this->error = $this->getError();
            return false;
        }
        
        $typeArr = array('live_play', 'look_live_play', 'online_time');
        if(!in_array($data['type'], $typeArr)){
            $this->error = '不被允许的类型!';
            return false;
        }
        
        $map['uid'] = $data['uid'];
        $map['type'] = $data['type'];
        $map['start'] = $data['start'];
        
        if(NOW_TIME >= $data['stage_end']){
            $data['stage_end_date'] = date('Y-m-d H:i:s', $data['stage_end']);
            $data['date'] = date('Ymd', NOW_TIME);
            $id = $this->add($data);
            if($id){
                Coin($id, $data['type']);
                if($data['type'] == 'online_time'){
                    upLevel($data['uid']);
                }
            }
        }
        return true;
    }
    
}