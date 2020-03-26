<?php

//动态

namespace Common\Model;

use Think\Model;
use Common\Api\GpsApi;

class ShowModel extends Model {

    protected $_validate = array(
        array('uid', 'require', '用户ID不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
            //array('title', 'require', '标题不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
            //array('theme', 'require', '话题不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
    );
    protected $_auto = array(
        array('addtime', NOW_TIME, self::MODEL_INSERT),
    );

    //添加动态
    function Insert($data = array()) {
        $Ins['uid'] = intval($data['uid']);

        if (is_array($data['img'])) {
            $Ins['img'] = implode(',', $data['img']);
        } else {
            $Ins['img'] = $data['img'];
        }

        if (empty($data['title']) && empty($data['content']) && empty($Ins['img'])) {
            $this->error = '您的动态需要点内容!';
            return false;
        }

        if (!empty($data['title'])) {
            $Ins['title'] = trim($data['title']);
        }

        if (!empty($data['theme'])) {
            $Ins['theme'] = trim($data['theme']);
        }

        if (!empty($data['content'])) {
            $Ins['content'] = trim($data['content']);
        }

        if (!empty($data['map'])) {
            $Ins['map'] = $data['map'];
        }


        if ($data['goods_id']) {
            $Ins['goods_id'] = intval($data['goods_id']);
        }

        //转发内容相关
        if ($data['old_id']) {
            $Ins['old_id'] = intval($data['old_id']);
            $oldInfo = $this->getOne($Ins['old_id']);
            if (!$oldInfo) {
                return false;
            }

            if ($oldInfo['old_id']) {
                $Ins['old_id'] = $oldInfo['old_id'];
            }
        }

        if ($data['zcontent']) {
            $Ins['zcontent'] = $data['zcontent'];
        }

        if (!$data['is_open']) {
            $Ins['is_open'] = 0;
        }

        //街道
        if (!empty($data['street'])) {
            $Ins['street'] = $data['street'];
        }

        //@
        $_at = $data['at'];
        
        $Ins['stauts'] = 1;

        $data = $this->create($Ins);
        if (!$data) {
            $this->error = $this->getError();
            return false;
        }

        $data['is_supplier'] = User($Ins['uid'], 'is_supplier');

        //地区
        $data['region_id'] = User($Ins['uid'], 'region_id');

        //添加到数据库
        $id = $this->add($data);
        
        //@艾特用户
        if(!empty($_at)){
            $atuser = json_decode($_at, true);
            if(!empty($atuser) && is_array($atuser)){
                $atuser = array_unique($atuser);
                foreach ($atuser as $atuid){
                    $this->setShow($id, $atuid, 'at');
                }
            }
        }
        
        
        if ($id) {
            return true;
        } else {
            $this->error = $this->getDbError();
            return false;
        }
    }

    /**
     * 设置动态置顶
     * @param int $uid
     * @param $id
     * @param int $is_top
     * @return bool
     */
    public function setTop($uid = 0, $id, $is_top = 0) {
        if (!$uid || !$id)
            return false;

        $map['uid'] = $uid;

        //先取消所有的置顶
        $data = array(
            'is_top' => 0,
            'top_time' => 0
        );

        M('Show')->where($map)->save($data);

        if ($is_top) {
            $map['id'] = $id;
            $data = array(
                'is_top' => 1,
                'top_time' => time()
            );
        }
        return M('Show')->where($map)->save($data);
    }

    /**
     * 删除动态
     * @param int $uid
     * @param $id
     * @return bool
     */
    public function delTop($uid = 0, $id) {
        if (!$uid || !$id)
            return false;
        if (!is_array($id)) {
            $id = array($id);
        }
        $map['id'] = array('in', $id);
        $map['uid'] = $uid;
        $data = array(
            'status' => 0,
        );
        return M('show')->where($map)->save($data);
    }

    //转发动态
    function zInsert($data = array()) {
        if ($data['old_id']) {
            $Show = $this->getOne(intval($data['old_id']));
            if (!$Show) {
                $this->error = '转发内容错误:' . $this->getError();
                return false;
            }
            if (!$Show['is_open']) {
                // 非公开，不允许转发
                $this->error = '不能转发';
                return false;
            }
        }

        $uid = intval($data['uid']);
        if (!User($uid, false)) {
            $this->error = '用户不存在!';
            return false;
        }

        $Show['uid'] = $uid;
        $Show['old_id'] = $Show['id'];
        $Show['zcontent'] = $data['zcontent'];


        unset($Show['id'], $Show['status'], $Show['addtime'], $Show['userInfo']);

        if ($this->Insert($Show) === true) {
            return true;
        } else {
            return false;
        }
    }

    //$field:
    // praise:点赞
    // read:阅读
    // flower:送花
    // dislike:不感兴趣
    // complaint:投诉
    // at 艾特用户
    // cons 踩
    function setShow($id, $uid, $field = 'praise', $other = '') {
        $uid = intval($uid);

        if (!User($uid, false)) {
            $this->error = '用户不存在!';
            return false;
        }

        $info = $this->getOne($id);
        if (!$info) {
            return false;
        }

        switch ($field) {
            case 'praise':
                // 判断是否踩过
                $map['uid'] = $uid;
                $map['show_id'] = $info['id'];
                $map['type'] = 'cons';
                $log = M('ShowLog')->where($map)->find();
                if ($log){
                    $this->error = '您已踩过';
                    return false;
                }
                $map['uid'] = $uid;
                $map['show_id'] = $info['id'];
                $map['type'] = 'praise';
                $log = M('ShowLog')->where($map)->find();
                if (empty($log)){
                    $logSave['status'] = 1;
                    $Save['praise'] = array('exp', "praise+1");
                }else{
                    $this->error = '您已赞过';
                    return false;
                }
//                if (empty($log) || $log['status'] == 0) {
//                    $logSave['status'] = 1;
//                    $Save['praise'] = array('exp', "praise+1");
//                } else {
//                    $logSave['status'] = 0;
//                    $Save['praise'] = array('exp', "praise-1");
//                }
                break;
            case 'cons':
                // 判断是否赞过
                $map['uid'] = $uid;
                $map['show_id'] = $info['id'];
                $map['type'] = 'praise';
                $log = M('ShowLog')->where($map)->find();
                if ($log){
                    $this->error = '您已赞过';
                    return false;
                }
                $map['uid'] = $uid;
                $map['show_id'] = $info['id'];
                $map['type'] = 'cons';
                $log = M('ShowLog')->where($map)->find();
                if (empty($log)){
                    $logSave['status'] = 1;
                    $Save['cons'] = array('exp', "cons+1");
                }else{
                    $this->error = '您已踩过';
                    return false;
                }
//                if (empty($log) || $log['status'] == 0) {
//                    $logSave['status'] = 1;
//                    $Save['cons'] = array('exp', "cons+1");
//                } else {
//                    $logSave['status'] = 0;
//                    $Save['cons'] = array('exp', "cons-1");
//                }
                break;
            case 'flower':
                $logSave['flower_number'] = 1; //送花数量

                $data['uid'] = $uid;
                $data['to_uid'] = $info['uid'];
                $data['type'] = 2;

                if (!D('Flowerlog')->Insert($data)) {
                    $this->error = D('Flowerlog')->getError();
                    return false;
                }

                $Save['flower'] = array('exp', "flower+{$logSave['flower_number']}");
                break;
            case 'read':
                $Save['read'] = array('exp', "read+1");
                break;
            case 'favor':

                break;
            case 'dislike':

                break;
            case 'complaint':
                $logSave['content'] = $other;
                break;
            case 'at':
                
                break;
            default :
                $this->error = '不能识别的动态类型!';
                return false;
        }

        if (!empty($Save)) {
            $this->where(array('id' => $info['id']))->save($Save);
        }

        //记录
        if ($field == 'praise' && !empty($log)) {
            M('ShowLog')->where(array('id' => $log['id']))->save($logSave);
        } else {
            $logSave['show_id'] = $info['id'];
            $logSave['uid'] = $uid;
            $logSave['show_uid'] = $info['uid'];
            $logSave['type'] = $field;
            $logSave['addtime'] = time();
            M('ShowLog')->add($logSave);
        }
        return true;
    }

    function getOne($id = '') {
        $id = intval($id);
        if (!$id) {
            $this->error = '参数有误!';
            return false;
        }

        $key = 'ShowInfo_' . $id;

        $info = S($key);

        if (empty($info)) {
            $info = $this->where(array('id' => $id))->find();
            if (empty($info)) {
                $this->error = '动态不存在!';
                return false;
            }

            $info['userInfo'] = User($info['uid']);

            S($key, $info);
        }

        return $info;
    }

    //获取不感兴趣动态ID
    function getDislike($uid) {
        $uid = intval($uid);
        if (!$uid) {
            $this->error = '参数有误!';
            return false;
        }

        $map['uid'] = $uid;
        $map['type'] = 'dislike';
        $ids = M('ShowLog')->where($map)->getField('id', true);

        if (empty($ids)) {
            return false;
        } else {
            return $ids;
        }
    }

    //获取网红值
    function Celebrity($uid) {
        //粉丝数
        $fans = FnasNum($uid);

        //收花数
        $flower = FlowerNum($uid);

        //被喜欢(被转发)
        $like = Forwarding($uid);

        //被查看
        $look = lookme($uid);

        $Celebrity = $fans + $flower + $like + $look;

        $data['fans'] = $fans;
        $data['flower'] = $flower;
        $data['like'] = $like;
        $data['look'] = $look;
        $data['celebrity'] = $Celebrity;
        if (User($uid, 'celebrity') != $Celebrity) {
            M('UcenterMember')->where(array('id' => $uid))->setField('celebrity', $Celebrity);
            CleanUser($uid);
        }
        return $data;
    }

    //处理动态列表
    //$distance 经纬度
    //$uid 登陆用户UID
    function Showlist($_list, $distance = array(),$uid = 0) {
        if ($distance['lat'] && $distance['lng']) {
            $gpsApi = new GpsApi();
        } else {
            $gpsApi = false;
        }
       
        
        if (!empty($_list)) {
            
            foreach ($_list as &$value) {
                // 获取是否关注
                if ($uid > 0){
                    $relation = M('user_relation')->where(array('uid'=>$uid,'relation_uid'=>$value['uid'],'type'=>2,'is_supplier'=>1))->find();
                    if ($relation){
                        $value['subscribe'] = 1;
                    } else {
                        $value['subscribe'] = 0;
                    }
                }
                //动态图片列表
                if (!empty($value['img'])) {
                    $value['imgpath'] = M('Picture')->where(array('id' => array('in', $value['img'])))->getField('path', true);
                }else {
                    $value['imgpath'] = null;
                }
                
                $value['img_path'] = $value['imgpath'];
                
                //鲜花数量
                $value['user_flower'] = User($value['uid'], 'flower');
                
                //是否点赞
                $value['is_praise'] = $this->isPraise($value['id'],$value['uid'])? : 0;
                
                //如果是转发,转发用户的信息
                if ($value['old_id']) {
                    $showInfo = D('Show')->getOne($value['old_id']);
                    $value['old_userInfo'] = User($showInfo['uid'], array('nickname'));
                }
                
                //距离
                if ($value['uid']) {
                    $value['userInfo'] = User($value['uid']);
                    if ($gpsApi) {
                        $value['distance'] = $gpsApi->MapAway($distance['lng'] . ',' . $distance['lat'], $value['userInfo']['lng'] . ',' . $value['userInfo']['lat'], 2)? : 0;
                    } else {
                        $value['distance'] = 0;
                    }
                }
                
                //发布动态用户头像
                $value['avatar'] = $value['userInfo']['avatar'];
                
                //发布动态用户昵称
                $value['nickname'] = $value['userInfo']['nickname'];
                
                //送花
                $Flower = $this->ShowGiveFlower($value['id']);
                
                //送花用户数量
                $value['flower_num'] = $Flower['flower_num'];
                
                //送花用户列表
                $value['flower_list'] = $Flower['flower_list'];
                
                //艾特
                $value['atuser'] = $this->ShowAt($value['id']);
                
            }
            
            return $_list;
        }
        
        else{
            return null;
        }
    }

    
    //获取动态的送花数据
    //$id 动态ID
    function ShowGiveFlower($id){
        $id = intval($id);
        //送花
        $map['status'] = 1;
        $map['type'] = 'flower';
        $map['show_id'] = $id;
        $show_log = M('ShowLog')
                ->where($map)
                ->group('uid')
                ->field("uid, sum(flower_number) as flowernum")
                ->order('flowernum DESC')
                ->select();


        if ($show_log) {
            //送花用户
            foreach ($show_log as &$item) {
                //送花用昵称
                $item['flower_nickname'] = User($item['uid'], 'nickname');
                
                //送花用户头像
                $item['flower_avatar'] = User($item['uid'], 'avatar');
            }
            
            //送花列表
            $data['flower_list'] = $show_log;
            
            //送花用户数量
            $data['flower_num'] = count($show_log);
        } else {
            $data['flower_list'] = array();
            $data['flower_num'] = 0;
        }
        return $data;
    }
    
    //艾特动态
    function ShowAt($id){
        $id = intval($id);
        
        $map['type'] = 'at';
        $map['show_id'] = $id;
        
        $uids = M('ShowLog')->where($map)->getField('uid',true);
        if(!empty($uids)){
            foreach ($uids as $uat){
                $_uat['uid'] = $uat;
                $_uat['nickname'] = User($uat,'nickname');
                $at[] = $_uat;
            }
            return $at;
        }
        return null;
    }
    
    //是否点赞
    function isPraise($id,$uid){
        $where['show_id'] = $id;
        $where['uid'] = $uid;
        $where['type'] = 'praise';
        return M('ShowLog')->where($where)->getField('status');
    }
    
    
}
