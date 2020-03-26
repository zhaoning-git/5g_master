<?php


namespace Common\Model;

use Think\Model;
use Com\Gateway;

class SocketModel extends Model {
    
    public  $gateway;
    public $Return;
    
    public function _initialize() {
        $gateway = new Gateway;
        $gateway::$registerAddress = '123.56.16.17:1238';
        $this->gateway = $gateway;
    }
    
    //发送消息
    function sendToUser($uid, $type, $data){
        $sendData['type'] = $type;
        $sendData['data'] = $data;
        $sendData = json_encode($sendData);
        $this->gateway->sendToUid($ToUid, $sendData);
    }
    
    //向所有人发送消息
    function sendToAll($type, $data){
        $sendData['type'] = $type;
        $sendData['data'] = $data;
        $sendData = json_encode($sendData);
        $this->gateway->sendToAll($sendData);
    }


    //绑定客户端到对应的用户ID或用户名
    function Binduser($uid, $client_id) {
        if (!empty($client_id)) {
            $ClientList = $this->gateway->getClientIdByUid($uid);
            if(count($ClientList)){
                foreach ($ClientList as $value){
                    $this->closeClient($value);
                }
            }
            
            if($this->gateway->isOnline($client_id)){
                $this->gateway->bindUid($client_id, $uid);
                $this->gateway->setSession($client_id, array('uid'=>$uid));
                return true;
            }else{
                $this->error = '客户端已离线!';
                return false;
            }
            
        }else{
            $this->error = '客户端ID错误!';
            return false;
        }
    }
    
    
    
}
