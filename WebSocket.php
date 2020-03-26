<?php
//use GatewayClient\Gateway;
include_once './GatewayClient/Gateway.php';

$func = $_REQUEST['func'];

$gateway = new Gateway;
$gateway::$registerAddress = '127.0.0.1:1238';

switch ($func){
    case 'sendToUser':
//        print_r($_REQUEST);
        sendToUser($_REQUEST['uid'], $_REQUEST['type'], $_REQUEST['data']);
        break;
    
    case 'sendToAll':
        sendToAll($_REQUEST['type'], $_REQUEST['data']);
        break;
    
    case 'Binduser':
        Binduser($_REQUEST['uid'], $_REQUEST['client_id']);
        break;
    
}

    
    //发送消息
    function sendToUser($uid, $type, $data){
        global $gateway;
        //file_put_contents('/www/wwwroot/WebSocket/data/sendToUser.text', print_r($_REQUEST, true), FILE_APPEND);
        $sendData['type'] = $type;
        $sendData['data'] = $data;
        $sendData = json_encode($sendData);
        $gateway->sendToUid($uid, $sendData);
    }
    
    //向所有人发送消息
    function sendToAll($type, $data){
        global $gateway;
        $sendData['type'] = $type;
        $sendData['data'] = $data;
        $sendData = json_encode($sendData);
        $gateway->sendToAll($sendData);
    }


    //绑定客户端到对应的用户ID或用户名
    function Binduser($uid, $client_id) {
        global $gateway;
        if (!empty($client_id)) {
            $ClientList = $gateway->getClientIdByUid($uid);
            if(count($ClientList)){
                foreach ($ClientList as $value){
                    $gateway->closeClient($value);
                }
            }
            
            if($gateway->isOnline($client_id)){
                $gateway->bindUid($client_id, $uid);
                $gateway->setSession($client_id, array('uid'=>$uid));
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
    
    

