<?php
namespace Common\Model;

//群聊
use Think\Model;
use Com\Gateway;
class GroupChatModel extends Model {
    public $gateway;
    
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('uid', 'require', '用户ID不能为空！', 1, self:: MODEL_INSERT),
        array('name', 'require',  '群聊名称不能为空！', 1, self:: MODEL_INSERT),
        array('image', 'require',  '群聊头像不能为空！', 1, self:: MODEL_INSERT),
        array('remark', 'require',  '群介绍不能为空！', 1, self:: MODEL_INSERT),
    );
    
    function _initialize() {
        parent::_initialize();
        $this->gateway = new Gateway;
    }

    //创建群聊
    function CreateChat($data){
        $data = $this->create($data);
        if(!$data){
            $this->error = $this->getError();
            return false;
        }
        
        //用户已创建的群聊数量
        $qlCount = $this->where(['uid'=>$data['uid']])->count();
        if($qlCount >= 100){
            $this->error = '每人只可创建100个群聊!';
            return false;
        }
        
        $id = intval($data['id']);
        if($id){
            $info = $this->where(array('id'=>$id))->find();
        }
        
        //编辑
        if(!empty($info)){
            if($info['uid'] != $data['uid']){
                $this->error = '您不是群主!';
                return false;
            }
            
            if(!empty($data['name']) && $data['name'] != $info['name'] && $this->where(array('name'=>$data['name']))->count()){
                $this->error = '群聊名称已存在!';
                return false;
            }
            
            if(!empty($data['image']) && $data['image'] != $info['image'] && is_numeric($data['image'])){
                $data['image'] = Imgpath($data);
            }
            
            if(empty($data['name'])){
                unset($data['name']);
            }
            
            if(empty($data['image'])){
                unset($data['image']);
            }
            
            if(empty($data['remark'])){
                unset($data['remark']);
            }
            
            if(empty($data['tags'])){
                unset($data['tags']);
            }
            
            
            unset($data['uid'],$data['max_num'],$data['status']);
            
            $data['uptime'] = NOW_TIME;
            if($this->where(array('id'=>$id))->save($data)){
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }
        
        //添加
        else{
            if($this->where(array('name'=>$data['name']))->count()){
                $this->error = '群聊名称已存在!';
                return false;
            }

            if(is_numeric($data['image'])){
                $data['image'] = Imgpath($data['image']);
            }

            $data['status'] = 1;
            $data['addtime'] = NOW_TIME;
            $data['max_num'] = 200;//最大群员数量
            $ql_id = $this->add($data);
            if($ql_id){
                if($this->addChat(['uid'=>$data['uid'], 'ql_id'=>$ql_id])){
                   return true; 
                }else{
                    return false;
                }
                
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }
        
        
    }
    
    //群信息
    function getInfo($id){
        $info = $this->where(array('id'=>$id))->find();
        if(empty($info)){
            $this->error = '群聊不存在!';
            return false;
        }elseif($info['status'] != 1){
            $this->error = '群聊已关闭!';
            return false;
        }
        
        $info['userlist'] = M('GroupChatUser')->where(array('ql_id'=>$info['id'],'status'=>array('LT', 2)))->select();
        if(!empty($info['userlist'])){
            foreach ($info['userlist'] as &$value){
                $value['avatar'] = AddHttp(M('Users')->where(array('id'=>$value['uid']))->getField('avatar'));
            }
        }
        return $info;
    }
    
    //加入群聊
    function addChat($data){
        $Ins['uid'] = intval($data['uid']);
        $Ins['ql_id'] = intval($data['ql_id']);
        
        $userInfo = M('Users')->where(array('id'=>$Ins['uid']))->find();
        if(empty($userInfo)){
            $this->error = '用户不存在!';
            return false;
        }
        
        $QlInfo = $this->getInfo($Ins['ql_id']);
        if(empty($QlInfo)){
            $this->error = '群聊不存在!';
            return false;
        }
        
        //判断是否已在群聊中
        if(M('GroupChatUser')->where(array('uid'=>$Ins['uid']))->count()){
            $this->error = '已在群聊中!';
            return false;
        }
        
        $count = M('GroupChatUser')->where(array('ql_id'=>$QlInfo['id'], 'status'=>array('LT', 2)))->count();
        if($QlInfo['max_num'] <= $count){
            $this->error = '群聊人数已满!';
            return false;
        }
        
        $Ins['status'] = 1;
        $Ins['addtime'] = NOW_TIME;
        $Ins['user_nickname'] = $userInfo['user_nicename'];
        M('GroupChatUser')->add($Ins);
        return true;
    }
    
    
    
    
    //处理群聊列表
    function setList($list){
        if(empty($list)){
            $this->error = '列表不能为空!';
            return false;
        }
    }
    
    //系统给指定用户推送群通知(主要用于推送邀请信息)
    //$id 邀请记录
    //发送通知给被邀请用户
    function sendGrouptouser($id){
        //发送通知
        $info = M('GroupChatInvite')->where(array('id'=>$id))->find();
        //发起邀请的人
        $userNicename = M('Users')->where(array('id'=>$info['uid']))->getField('user_nicename');
        //群聊名称
        $qlName = M('GroupChat')->where(array('id'=>$info['ql_id']))->getField('name');
        
        $content = $userNicename.'邀请您进入'.$qlName;
        
        $sendData['type'] = 'GroupTzUser';
        $sendData['data'] = $content;
        $sendData = json_encode($sendData);
        $this->gateway->sendToUid($info['bei_uid'], $sendData);
        
    }
    
    //客户端主动拉取群通知
    function GroupTz($uid){
        $ql_list = M('GroupChatInvite')->where(array('bei_uid'=>$uid))->field('id,ql_id,uid,remark')->select();
        
        $content = false;
        if(!empty($ql_list)){
            foreach ($ql_list as $info){
                //发起邀请的人
                $userInfo = M('Users')->where(array('id'=>$info['uid']))->field('avatar,user_nicename')->find();
                $userInfo['avatar'] = AddHttp($userInfo['avatar']);
                
                //群聊名称
                $qlName = M('GroupChat')->where(array('id'=>$info['ql_id']))->getField('name');
                
                
                $_data['id'] = $info['id'];
                $_data['content'] = $info['remark'];
                $_data['userInfo'] = $userInfo;
                
                
                
                $content[] = $_data;
            }
        }
        return $content;
    }
    
}