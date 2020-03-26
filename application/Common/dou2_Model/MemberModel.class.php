<?php

namespace Common\Model;

use Think\Model;

/**
 * 文档基础模型
 */
class MemberModel extends Model {

    public function register($id, $nickname = '', $group_id = 1) {
        $id = intval($id);
        if(!M('UcenterMember')->where(array('id'=>$id))->count()){
            $this->error = '用户信息不存在注册失败，请重试！';
            return false;
        }
        //在当前应用中注册用户
        $user = array('uid' => $id, 'nickname' => $nickname);
        if (!$this->add($user)) {
            $this->error = '前台用户信息注册失败，请重试！';
            return false;
        }
        $group_id = intval($group_id) ? intval($group_id) : 1;
        M('AuthGroupAccess')->add(array('uid' => $id, 'group_id' => $group_id));
        return $id;
    }

    /**
     * 同步登陆时添加用户信息
     * @param $uid
     * @param $info
     * @return mixed
     * autor:xjw129xjt
     */
    public function addSyncData($uid, $info) {
        if (!$uid || !$info)
            return false;
        //注册后更改到期时间为0 良子
        M("Member")->where("uid = $uid")->save(array('service_over_time' => ""));
        $info['uid'] = $uid;
        $info['subscribe'] = $info['subscribe'] ? $info['subscribe'] : 0;
        $res = $this->add($info);
        $sync = M('SyncLogin')->add(array('uid' => $uid, 'wx_pub_subscribe' => $info['subscribe'], 'openid' => $info['openid'], 'type' => 'weixinpub', 'oauth_token' => $info['openid'], 'oauth_token_secret' => $info['openid'], 'is_sync' => 0));

        if ($res !== false && $sync !== false) {
            D('Common/Avatar')->saveAvatar($uid, $info['headimgurl']); //保存头像
            return $uid;
        } else {
            M('SyncLogin')->delete($sync); //删除
            return false;
        }
    }

    /**
     * 前台用户信息
     * @param $uid
     * @param $fieldout 排除哪些可修改自动，前台使用的时候很有必要使用
     */
    public function UpdateUser($data, $fieldout = 'uid') {
        if (empty($data)) {
            $this->error = '更新资料数据不能为空!';
            return false;
        }
        
        if(!empty($data['store_title'])){
            //$Storedata['title'] = $data['store_title'];//这里禁止更新商店名称
            unset($data['store_title']);
        }
        
        if(!empty($data['store_phone'])){
            $Storedata['phone'] = $data['store_phone'];
            unset($data['store_phone']);
        }
        
        if(!empty($data['store_address'])){
            $Storedata['address'] = $data['store_address'];
            unset($data['store_address']);
        }
        
        if (!$data['uid'] || !$data) {
            $this->error = '数据对象错误或缺少会员标识';
            return false;
        }

        $uid = intval($data['uid']);
        //unset($data['uid'],$data['sex'],$data['status']);
        unset($data['uid'],$data['status']);

        $userInfo = User($uid);
        if(!$userInfo){
            $this->error = '用户不存在,或用户资料未完善!';
            return false;
        }
        
        if(isset($data['nickname']) && empty($data['nickname'])){
            $this->error = '用户昵称不能为空';
            return false;
        }
        
        if(isset($data['nickname']) && $data['nickname'] != $userInfo['nickname']){
            if($this->where(array('nickname'=>$data['nickname']))->count()){
                $this->error = '昵称已被占用';
                return false;
            }
            if(!is_username($data['nickname'])){
                $this->error = '昵称必须是三位以上的字母,数字,或者下划线!';
                return false;
            }
        }
        
        $data['last_update_time'] = time();
        $res = $this->where(array('uid' => $uid))->save($data);
        
        if($userInfo['is_supplier'] == 1){
            $Store = M('Store')->where(array('uid'=>$uid))->find();
            if(!empty($Store) && !empty($Storedata)){
                M('Store')->where(array('id'=>$Store['id']))->save($Storedata);
            }
        }
        
        CleanUser($data['uid']);
        return $res;
    }
    
    public function logout() {
        session('user_auth', null);
        session('user_auth_sign', null);
        cookie('OX_LOGGED_USER', NULL);
        //session('SYNCLOGIN', null);
    }

    /**
     * 更新用户地理位置
     * @param $uid
     * @param $data
     */
    public function UpdateAddress($uid, $map) {

        if (!$map || !$uid)
            return false;

        $api = api('Gps/Map2Address', array('map' => $map));

        $address = array(
            'country' => $api['address_component']['nation'],
            'province' => $api['address_component']['province'],
            'city' => $api['address_component']['city'],
            'district' => $api['address_component']['district'],
            'address' => $api['address'],
            'map' => $api['location']['lat'] . ',' . $api['location']['lng']
        );

        return $this->where(array('uid' => $uid))->save($address);
    }

}
