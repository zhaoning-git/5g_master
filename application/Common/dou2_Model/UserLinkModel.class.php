<?php

namespace Common\Model;

use Think\Model;

/**
 * 会员关系记录
 * @package Common\Model
 * @author:
 */
class UserLinkModel extends Model {
    
    public function AddUserLink($uid, $link) {
        if (!intval($uid) || !intval($link)) {
            $this->error = '参数错误!';
            return false; //没有推荐人
        }

        if ($this->where(array('uid' => $uid))->find()) {
            $this->error = '已经建立过推荐人关系';
            return false;
        }
        
        $group_id = User($uid,'group_id');
        if($link == 1 && $group_id == 1){    
            $this->error = '超级管理员不能推荐普通用户';
            return false;
        }
        
        $linkInfo = $this->where(array('uid' => $link,'status'=>1))->find();
        if(empty($linkInfo)){
            $this->error = '推荐人的推荐关系不存在';
            return false;
        }
      
        $link_group_id = User($link,'group_id');
        
        
        //先获取推荐人的会员树
        $tree = $linkInfo['tree'] ? $linkInfo['tree'] . ',' . $link : $link;

        $data['uid'] = $uid;
        $data['nickname'] = get_nickname($uid);
        $data['tree'] = $tree;

        $data['group_id'] = $group_id;
        $data['link_group_id'] = $link_group_id;

        $data['link'] = $link;
        $data['link_nickname'] = get_nickname($link);
        $data['level'] = 0;
        $data['status'] = 1;
        $data['create_time'] = time();
        $ID = $this->add($data);
        if(!is_numeric($ID) || !$ID){
            $this->error = $this->getDbError();
            return false;
        }else{
            return true;
        }
    }


    /*
     * 查询推荐关系
     * $uid 要查找的会员
     * $type 三个参数 str、arr、more 分别是字符串，一位数组，二维数组
     * $level 限制查找层级
     * $order 向上查找或者向下查找
     * @author:
     */
    public function FindLink($uid, $type = 'str', $order = 'up', $level = 3) {

        if (!$uid) {
            $this->error = '查询推荐关系传入参数错误';
            return false; //没有推荐人
        }

        if ($order == 'up') {
            $aims_tree = $this->where(array('uid' => $uid))->getField('tree'); //上级推荐人信息;
            if (!$aims_tree)
                return false;

            /* 向上查找 */
            $tree = array_slice(explode(',', $aims_tree), -$level, 3);
            rsort($tree);

            foreach ($tree as $k => $v) {
                $uids[$k + 1] = $v;
            }

            if ($type == 'arr')
                return $uids;
            if ($type == 'str')
                return implode(',', $uids);
            if ($type == 'more') {
                foreach ($uids as $k => $v) {
                    $uids[$k] = User($v, array('uid', 'nickname', 'openid', 'avatar128', 'agents_level', 'agents_name', 'agents_id', 'status'));
                    $uids[$k]['level'] = $k;
                }
                return $uids;
            }
        }

        if ($order == 'bottom') {
            $invite_list = $this->where(array('link' => $uid, 'level' => array('elt', $level)))->order('level asc')->field('remark', true)->select();
            if (!$invite_list)
                return false;
            if ($type == 'arr')
                return array_column($invite_list, 'uid');
            if ($type == 'str')
                return implode(',', array_column($invite_list, 'uid'));
            if ($type == 'more') {
                foreach ($invite_list as $k => $v) {
                    $invite_list[$k] = User($v['uid'], array('uid', 'nickname', 'openid', 'avatar128', 'agents_level', 'agents_name', 'agents_id', 'status'));
                    $invite_list[$k]['level'] = $v['level'];
                }
                return $invite_list;
            }
        }
    }

    //删除或禁用会员
    public function DeleteLink($uid, $status) {
        $ret = $this->where(array('link' => array('in', $uid)))->setField('status', $status);
        return $ret;
    }

    //查询推荐关系
    //$type 1:直接上级 2:县级 3:市级
    function queryLink($uid,$type){
        $map['uid'] = intval($uid);
        switch ($type){
            case 1:
                $Result = $this->where($map)->getField('link');
                if(!$Result){
                    $this->error = '直接上级用户不存在!';
                }
                break;
            case 2:
                $Result = $this->where($map)->getField('xian');
                if(!$Result){
                    $this->error = '县级用户不存在!';
                }
                break;
            case 3:
                $Result = $this->where($map)->getField('shi');
                if(!$Result){
                    $this->error = '市级用户不存在!';
                }
                break;
            default :
                $this->error = '未定义的推荐关系查询!';
                return false;
        }
        return $Result;
    }
    
    
    
    
}
