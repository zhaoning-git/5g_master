<?php
/**
 * 极光
 */
namespace Cliapi\Controller;

use Org\JMessage\JMessage;
use Org\JMessage\IM\User;
class JiguangController extends ApiController {
    public $jm;
    public $user;
    public function _initialize() {
        parent::_initialize();
        $appKey = '21ae81f3aa95a22e9b92939f';
        $masterSecret = '563bcb0ed6081afc3ebdd2ed';
        $this->jm = new JMessage($appKey, $masterSecret);
        $this->user = new User($this->jm);
    }
    
    public function index(){
        $user = new User($this->jm);
        print_r($user);
    }
    
    //注册用户
    public function register($username='',  $password=''){
        if(empty($username) || empty($password)){
            $data = I('post.');
            $username = $data['username'];
            $password = $data['password'];
            $response = $this->user->register($username, $password);
            $this->ajaxRet(1, '成功', $response);
        }else{
            return $this->user->register($username, $password);
        }
    }
    
    //获取用户信息
    public function show($username){
        $response = $this->user->show($username);
        $this->ajaxRet(1, '成功', $response);
    }
    
    //更新用户信息
    public function update($username, $data){
        return $this->user->update($username, $data);
    }
    
    
    
}