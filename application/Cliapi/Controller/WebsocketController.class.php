<?php

/**
 * Date: 19-11-08
 * Time: 22:38
 */

namespace Cliapi\Controller;

use Com\Gateway;

class WebsocketController extends MemberController {

    public $gateway;

    function _initialize() {
        parent::_initialize();
        $this->gateway = new Gateway;
    }

    //创建群聊
    function CreateChat() {
        $data = I('post.');
        $data['uid'] = $this->uid;
        if (D('GroupChat')->CreateChat($data)) {
            $this->ajaxRet(1, '成功');
        } else {
            $this->ajaxRet(0, '失败' . D('GroupChat')->getError());
        }
    }

    //修改群聊
    function EditChat() {
        $data = I('post.');
        $data['uid'] = $this->uid;
        if (D('GroupChat')->CreateChat($data)) {
            $this->ajaxRet(1, '成功');
        } else {
            $this->ajaxRet(0, '失败' . D('GroupChat')->getError());
        }
    }

    //群信息
    function info() {
        $id = I('id', '', 'intval');
        $info = D('GroupChat')->getInfo($id);
        $this->ajaxRet(1, '成功', $info);
    }

    //我创建的群聊列表
    function myList() {
        $map['uid'] = $this->uid;
        $data['_list'] = $this->lists('GroupChat', $map, 'addtime ASC');
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //我加入的群聊列表
    function inList() {
        $map['uid'] = $this->uid;
        $list = $this->lists('GroupChatUser', $map, 'addtime ASC');
        if (!empty($list)) {
            foreach ($list as $value) {
                $_list[] = M('GroupChat')->where(array('id' => $value['ql_id']))->find();
            }
        }

        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //加入群聊
    function addChat() {
        $data = I('post.');
        $data['uid'] = $this->uid;
        if (D('GroupChat')->addChat($data)) {
            $this->ajaxRet(1, '成功');
        } else {
            $this->ajaxRet(0, '失败' . D('GroupChat')->getError());
        }
    }

    //发送消息 (暂停)
    function sendTouser($uid, $type, $data) {
        $sendData['type'] = $type;
        $sendData['data'] = $data;
        $sendData = json_encode($sendData);
        $this->gateway->sendToUid($uid, $sendData);
    }



    //向所有人发送消息 (暂停)
    function sendToall($type, $data) {
        $sendData['type'] = $type;
        $sendData['data'] = $data;
        $sendData = json_encode($sendData);
        $this->gateway->sendToAll($sendData);
    }

    //发送群聊消息
    //$id 群聊ID
    function sendTogroup() {
        $data = I('post.');
        $id = intval($data['id']);

        $info = M('GroupChat')->where(array('id' => $id))->find();
        if (empty($info)) {
            $this->ajaxRet(0, '群聊不存在!');
        } elseif ($info['status'] != 1) {
            $this->ajaxRet(0, '群聊已关闭!');
        }

        $map['ql_id'] = $id;
        $map['status'] = array('LT', 2); //状态: 0:等待管理员审核 1:通过 2:黑名单 LT 小于
        $map['uid'] = $this->uid;
        if (!M('GroupChatUser')->where($map)->count()) {
            $this->ajaxRet(0, '您还不是该群聊成员!');
        }

        $sendData['type'] = $data['type'];
        $sendData['data'] = $data['data'];
        $sendData['content'] = $data['content'];
        $sendData = json_encode($sendData);

        unset($map['uid']);
        $userlist = M('GroupChatUser')->where($map)->select();
        if (!empty($userlist)) {
            $Msg['uid'] = $this->uid;
            $Msg['toid'] = $id;
            $Msg['data'] = $sendData;
            $Msg['content'] = $data['content'];

            if (!D('GroupChatMsg')->addGroupMsg($Msg)) {
                $this->ajaxRet(0, '失败' . D('GroupChatMsg')->getError());
            } else {
                foreach ($userlist as $value) {
                    $this->gateway->sendToUid($value['uid'], $sendData);
                }
                $this->ajaxRet(1, '成功');
            }
        }
        
    }

    //邀请
    function Invite() {
        $data = I('post.');
        $data['uid'] = $this->uid;
        if (D('GroupChatInvite')->Invite($data)) {
            $this->ajaxRet(1, '成功');
        }else{
            $this->ajaxRet(0, '失败' . D('GroupChatInvite')->getError());
        }
    }

    //同意&&拒绝 邀请
    function setInvite(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        if (D('GroupChatInvite')->setInvite($data)) {
            $this->ajaxRet(1, '成功');
        }else{
            $this->ajaxRet(0, '失败' . D('GroupChatInvite')->getError());
        }
    }
    
    //邀请我的
    function myInvite(){
        $map['bei_uid'] = $this->uid;
        $data['_list'] = $this->lists('GroupChatInvite',$map,'addtime ASC');
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //聊天记录
    function Msglog($ql_id){
        
        $map['toid'] = I('ql_id', 0, 'intval');
        if(!$map['toid']){
            $this->ajaxRet(0, '群聊ID不正确!');
        }
        
        $QlInfo = M('GroupChat')->where(array('id'=>$ql_id))->find();
        if(empty($QlInfo)){
            $this->ajaxRet(0, '群聊不存在!');
        }
        
        if(!M('GroupChatUser')->where(array('ql_id'=>$ql_id,'uid'=>$this->uid))->count()){
            $this->ajaxRet(0, '您不在群聊中!');
        }
        
        $list = $this->lists('GroupChatMsg', $map, 'addtime DESC');
        if(!empty($list)){
            foreach ($list as $value){
                $_list[] = D('GroupChatMsg')->setMsg($value);
            }
            $data['_list'] = $_list;
            $data['_totalPages'] = $this->_totalPages; //总页数
            $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
        }else{
            $this->ajaxRet(array('status' => 0, 'info' => '没有聊天记录'));
        }
    }
    
    //客户端主动拉取邀请入群通知
    function GroupTz(){
        $_list = D('GroupChat')->GroupTz($this->uid);
        if($_list){
            $this->ajaxRet(1,'成功', $_list);
        }else{
            $this->ajaxRet(0,'失败,没有通知');
        }
    }


    //绑定客户端到对应的用户ID或用户名
    function Binduser($uid, $client_id) {
        if (!empty($client_id)) {
            $ClientList = $this->gateway->getClientIdByUid($uid);
            if (count($ClientList)) {
                foreach ($ClientList as $value) {
                    $this->gateway->closeClient($value);
                }
            }

            if ($this->gateway->isOnline($client_id)) {
                $this->gateway->bindUid($client_id, $uid);
                $this->gateway->setSession($client_id, array('uid' => $uid));
                return true;
            } else {
                $this->error = '客户端已离线!';
                return false;
            }
        } else {
            $this->error = '客户端ID错误!';
            return false;
        }
    }

}
