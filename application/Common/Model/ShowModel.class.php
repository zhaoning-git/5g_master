<?php
//贴子
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

    //添加贴子
    function Insert($data = array()) {
        $Ins['uid'] = intval($data['uid']);

        if($data['data']){
            if (is_array($data['data'])) {
                $Ins['data'] = implode(',', $data['data']);
            } else {
                $Ins['data'] = $data['data'];
            }
        }

        if (empty($data['content']) && empty($Ins['data'])) {
            $this->error = '您的帖子需要点内容!';
            return false;
        }

        if (!empty($data['title'])) {
            $Ins['title'] = trim($data['title']);
        }

        if (!empty($data['theme'])) {
            $Ins['theme'] = trim($data['theme']);
            D('Theme')->kInsert(array('uid'=>$Ins['uid'],'title'=>$Ins['theme']));
        }

        if (!empty($data['type'])) {
            $Ins['type'] = intval($data['type']);
        }else{
            $Ins['type'] = 1;
        }

        if (!empty($data['content'])) {
            $Ins['content'] = trim($data['content']);
        }

        //地理位置(经纬度)
        if (!empty($data['map'])) {
            $Ins['map'] = $data['map'];
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

        if ($data['is_club']) {
            $Ins['is_club'] = $data['is_club'];
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

        //添加到数据库
        $id = $this->add($data);

        //@艾特用户
        if (!empty($_at)) {
            $atuser = explode(',', $_at);
            if (!empty($atuser) && is_array($atuser)) {
                $atuser = array_unique($atuser);
                foreach ($atuser as $atuid) {
                    $this->setShow($id, $atuid, 'at');
                }
            }
        }

        if ($id) {
            Coin($id, 'post_show');
            return true;
        } else {
            $this->error = $this->getDbError();
            return false;
        }
    }

    /**
     * 设置贴子置顶
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
     * 删帖
     * @param int $uid
     * @param $id
     * @return bool
     */
    public function delShow($id) {
        if (!$id){
            $this->error = '要删除的帖子ID不能为空!';
            return false;
        }
        $map['id'] = $id;
        if(M('Show')->where($map)->setField('status', 0) !== false){
            Coin($id, 'del_show');
            return true;
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }

    //转发贴子
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

        $map['uid'] = $uid;
        $map['show_id'] = $info['id'];
        $map['type'] = $field;
        $log = M('ShowLog')->where($map)->find();
        
        switch ($field) {
            case 'praise':
                if(!empty($log)){
                    if (!$log['status']) {
                        $logSave['status'] = 1;
                        $Save['praise'] = array('exp', "praise+1");
                    }else{
                        $logSave['status'] = 0;
                        $Save['praise'] = array('exp', "praise-1");
                    }
                }else{
                    $logSave['status'] = 1;
                    $Save['praise'] = array('exp', "praise+1");
                }
                break;
            case 'cons':
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
                if(empty($log)){
                    $Save['reading'] = array('exp', "reading+1");
                }
                break;
            
            //收藏
            case 'favor':
                if(!empty($log)){
                    if (!$log['status']) {
                        $logSave['status'] = 1;
                        $Save['favor'] = array('exp', "favor+1");
                    }else{
                        $logSave['status'] = 0;
                        $Save['favor'] = array('exp', "favor-1");
                    }
                }else{
                    $logSave['status'] = 1;
                    $Save['favor'] = array('exp', "favor+1");
                }
                break;
            case 'dislike':

                break;
            case 'complaint'://投诉
                $logSave['content'] = $other;
                break;
            case 'at':
                
                break;
            default :
                $this->error = '不能识别的贴子类型!';
                return false;
        }

        if (!empty($Save)) {
            $this->where(array('id' => $info['id']))->save($Save);
        }

        //记录
        if (!empty($log) && $log['id'] && !empty($logSave)) {
            M('ShowLog')->where(array('id' => $log['id']))->save($logSave);
        } else {
            if(empty($log)){
                $logSave['show_id'] = $info['id'];
                $logSave['show_uid'] = $info['uid'];
                $logSave['uid'] = $uid;
                $logSave['type'] = $field;
                $logSave['addtime'] = time();
                M('ShowLog')->add($logSave);
            }
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

        S($key, NULL);//关闭缓存
        $info = S($key);

        if (empty($info)) {
            $info = $this->where(array('id' => $id))->find();
            if (empty($info)) {
                $this->error = '帖子不存在!';
                return false;
            }

            $info['userInfo'] = User($info['uid']);

            S($key, $info);
        }

        return $info;
    }

    //获取不感兴趣贴子ID
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

    //处理贴子列表
    //$distance 经纬度
    //$uid 登陆用户UID
    function Showlist($_list, $distance = array(), $uid = 0) {
        if (!empty($_list)) {
            foreach ($_list as &$value) {
                // 获取是否关注
                if ($uid && $uid > 0) {
                    $relation = M('UserRelation')->where(array('uid' => $uid, 'relation_uid' => $value['uid'], 'type' => 2))->find();
                    if ($relation) {
                        //$value['subscribe'] = 1;
                    } else {
                        //$value['subscribe'] = 0;
                    }
                }
                
                //$value['userInfo'] = User($value['uid']);
                $userInfo = User($value['uid']);
                
                //贴子图片列表
                $value['imgpath'] = getImgVideo($value['data']);

                //是否点赞
                //$value['is_praise'] = $this->isPraise($value['id'], $value['uid']) ?: 0;

                //如果是转发,转发用户的信息
                if ($value['old_id']) {
                    //$showInfo = D('Show')->getOne($value['old_id']);
                    //$value['old_userInfo'] = User($showInfo['uid'], array('nickname'));
                }

                //发布贴子用户头像
                $value['avatar'] = $userInfo['avatar'];

                //发布贴子用户昵称
                $value['nickname'] = $userInfo['user_nicename'];

                //艾特
                $value['atuser'] = $this->ShowAt($value['id']);
            }

            return $_list;
        } else {
            return null;
        }
    }


    //艾特贴子
    function ShowAt($id) {
        $id = intval($id);

        $map['type'] = 'at';
        $map['show_id'] = $id;

        $uids = M('ShowLog')->where($map)->getField('uid', true);
        if (!empty($uids)) {
            foreach ($uids as $uat) {
                $_uat['uid'] = $uat;
                $_uat['nickname'] = User($uat, 'user_nicename');
                $at[] = $_uat;
            }
            return $at;
        }
        return null;
    }

    //是否点赞
    function isPraise($id, $uid) {
        $where['show_id'] = $id;
        $where['uid'] = $uid;
        $where['type'] = 'praise';
        return M('ShowLog')->where($where)->getField('status');
    }

    //获取首页资讯信息
    function getZixunInfo($index_mol,$sel_type){  //足球/篮球  ， 标签分类
        $info = M('club_type')->where(array('id'=>$sel_type))->find();
        if(empty($info)){
            $this->error = '没有该标签分类';
            return false;
        }

        if($info['type'] !== $index_mol){
            $this->error = '请选择对应模块的分类进行查询';
            return false;
        }

        $where = [
            'index_mol' =>$index_mol,
            'sel_type' =>$sel_type,
            'type'=>2,
            'status'=>1
        ];
        $info = M('show')->field('id,uname,title,uid,data,reading,praise,favor')->where($where)->order('addtime desc')->limit(20)->select();

        foreach ($info as $k=>&$v){
            $com_sum =M('comment')->where(array('show_id'=>$v['id']))->select();
            $uname = M('users')->field('user_nicename')->where(array('id'=>$v['uid']))->select();
            foreach ($uname as $key=>&$val){
                $v['uname'] = $val['user_nicename'];
            }
            $v['comm_sum']=count($com_sum);

            if($v['data'] == '' || $v['data'] == 0){
                //获取内容字符串中的第一张图片
                $zheng = '/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i';
                if(preg_match($zheng, $v['content'], $matchContent)){
                    $v['data'] = $matchContent[1];
                };
            }else{
                if(is_numeric(substr($v['data'],0,1))) { //如果以数字开头
                    $v['data'] = getImgVideo($v['data']);
                }
            }
        }

        if($info){
            return $info;
        }else{
            $this->error = '该标签分类下没有资讯';
            return false;
        }

    }

}
