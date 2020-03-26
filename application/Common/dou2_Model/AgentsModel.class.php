<?php

/**
 * 代理商
 * 开发者: 
 * 创建日期: 16-4-7
 */
namespace Common\Model;

use Think\Model;

class AgentsModel extends Model {

    protected $_validate = array(
        array('name', '4,20', '代理商名称4-20个字以内', self::MUST_VALIDATE, 'length', self::MODEL_INSERT),
        array('name', 'require', '代理商名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        
        array('ming_password', '4,20', '代理商密码4-20个字以内', self::MUST_VALIDATE, 'length', self::MODEL_INSERT),
        array('ming_password', 'require', '代理商密码不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
    );
    
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );
    
    //添加代理
    function addAgents($data=array()){
        if(!empty($data['ming_password'])){
            $data['password'] = md5($data['ming_password']);
        }
        
        $data = $this->create($data);
        if(!$data){
            $this->error = $this->getError();
            return false;
        }
        
        $id = intval($data['id']);
        
        if($id == C('DEFAULTAGENTS') || $data['city'] == C('DEFAULTCITY')){
            $this->error = '全国代理总账号禁止编辑!';
            return false;
        }
       
        //编辑
        if($id){
            $agents = $this->getOne($id);
            if(!$agents){
                return false;
            }
            
            if(isset($data['city']) && $agents['city'] != $data['city']){
                $this->error = '无法修改代理区域';
                return false;
            }
            
            unset($data['id'],$data['city']);
            if($this->where(array('id'=>$id))->save($data) !== false){
                S('AgentsInfo_'.$id,null);
                $this->upRegion($id);
                return true;
            }  else {
                $this->error = $this->getDbError();
                return false;
            }
        }
        
        //添加
        else{
            
            if($this->where(array('city'=>$data['city']))->count()){
                $this->error = '所选区域已有代理商!';
                return false;
            }
            
            $data['status'] = 1;
            $id = $this->add($data);
            $this->upRegion($id);
            
            if($id){
                return true;
            }  else {
                $this->error = $this->getDbError();
                return false;
            }
        }
    }

    //新增代理商以后更新用户的所属地区
    function upRegion($agents_id){
        $agents = $this->getOne($agents_id);
        if(!$agents){
            return false;
        }
        
        if($agents['status'] == 1 && $agents['is_verify'] == 1){
            $map['region_id'] = $agents['city'];
            $map['agents_id'] = C('DEFAULTAGENTS');
            if(M('UcenterMember')->where($map)->setField('agents_id',$agents['id']) !== false){
                if(M('Store')->where($map)->setField('agents_id',$agents['id']) !== false){
                    return true;
                }
            }else{
                $this->error = M('UcenterMember')->getDbError();
                return false;
            }
        }
    }
    
    /**
     * 代理商登录认证
     * @param  string  $name 代理商名
     * @param  string  $password 代理商密码
     * @param  string  $table 代理商登录类型（记录代理商的数据表）
     * @return integer           登录成功-代理商ID，登录失败-错误编号
     */
    public function verify_login($name, $password) {
        if(empty($name)){
            $this->error = '登录名不能为空!';
            return false;
        }
        $map['name'] = $name;
        
        //获取代理商数据
        $agents = $this->where($map)->find();

        if (is_array($agents) && $agents['status']) {
            //验证代理商密码
            if (think_ucenter_md5($password) == $agents['password']) {
                $this->updateLogin($agents['id']); //更新代理商登录信息
                return $agents['id']; //登录成功，返回代理商ID
            } else {
                action_log('agents_password', 'agents', $agents['id'], $agents['id']);
                $this->error = '密码错误';
                return false;
            }
        } else {
            $this->error = '代理商不存在或被禁用!';
            return false;
        }
    }

    /**
     * 更新代理商登录信息
     * @param  integer $store_id 代理商ID
     */
    public function updateLogin($agents_id) {
        $data = array(
            'login' => array('exp', 'login+1'),
            'last_login_time' => time(),
            'last_login_ip' => get_client_ip(1),
        );
        $this->where(array('id' => $agents_id))->save($data);
    }

    /**
     * 登录指定代理商
     * @param  integer $agents_id 代理商ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($agents_id) {
        //检测是否在当前应用注册
        $agents = $this->where(array('id' => $agents_id))->find();
        
        //登录代理商
        $agents_id = $this->autoLogin($agents);
        
        //记录行为
        action_log('agents_login', 'agents', $agents_id, $agents_id);
        return $agents_id;
    }

    /**
     * 注销当前代理商
     * @return void
     */
    public function logout() {
        session('agents_auth', null);
        session('agents_auth_sign', null);
    }

    /**
     * 自动登录代理商
     * @param  integer $store 代理商信息数组
     */
    private function autoLogin($agents) {
        //记录登录SESSION和COOKIES
        $auth = array(
            'id' => $agents['id'],
            'agents_name' => $agents['name'],
            'last_login_time' => $agents['last_login_time']
        );

        session('agents_auth', $auth);
        session('agents_auth_sign', data_auth_sign($auth));


        return $agents['id'];
    }
    
    /**
     * 代理商添加业务员
     * $data['nickname'];
     * $data['mobile'];
     * $data['password'];
     * $data['sex'];
     * $data['parent_id']; 上级推荐人
     * $data['region_id']; 所在地区
     * $data['agents_id']; 代理商ID
     **/
    function addSalesman($data=''){
        $data['is_supplier'] = 0;
        $uid = D('UcenterMember')->editUser($data);
        if(is_numeric($uid) && $uid > 0){
            if(!M('UcenterMember')->where(array('id'=>$uid))->getField('salesman')){
                $save['salesman'] = $data['agents_id'];
                $save['agents_id'] = $data['agents_id'];
                M('UcenterMember')->where(array('id'=>$uid))->save($save);
                
                $jobs = $data['profession']?:'无';
                M('Member')->where(array('uid'=>$uid))->setField('profession',$jobs);
                
                CleanUser($uid);
                return true;
            }
        }else{
            $this->error = D('UcenterMember')->getError();
            return false;
        }
    }
    
    //代理商全部业务员
    function Salesman($agentsId=''){
        $agentsId = intval($agentsId);
        if(!$agentsId){
            $this->error = '参数有误!';
            return false;
        }
        
        $map['status'] = 1;
        $map['salesman'] = $agentsId;
        $_list = $this->where($map)->getField('id',true);
        return $_list;
    }

    //代理商的全部会员||商家
    //$is_supplier 是否是商家 0:否 1:是
    function agentsUser($agentsId='',$is_supplier=1){
        $agentsId = intval($agentsId);
        if(!$agentsId){
            $this->error = '参数有误!';
            return false;
        }
        
        $map['status'] = 1;
        $map['agents_id'] = $agentsId;
        $map['is_supplier'] = intval($is_supplier);
        
        $_list = $this->where($map)->getField('id',true);
        return $_list;
       
    }
    
    //代理商详情
    function getOne($id='', $field=true, $new=false){
        $id = intval($id);
        if(!$id){
            $this->error = '参数有误!';
            return false;
        }
        
        $key = 'AgentsInfo_'.$id;
        
        if($new === true){
            S($key,null);
        }
        
        $info = S($key);
        
        if(empty($info)){
            $info = $this->where(array('id'=>$id))->find();
            if(!empty($info)){
                $info['cityname'] = cityName($info['city']);
                $info['region_name'] = $info['cityname'];
                S($key,$info);
            }else{
                $this->error = '代理商不存在!';
                return false;
            }
        }
        
        if ($field === true) {
            return $info;
        } 
        elseif (!is_array($field) && is_string($field)) {
            return $info[$field];
        } 
        elseif (is_array($field)) {
            foreach ($field as $k => $v) {
                $ret[$v] = $info[$v];
            }
            return $ret;
        }
    }
    
    //根据所代理的地区获取代理商ID
    function cityAgentsid($city=''){
        $city = intval($city);
        if(!$city){
            $this->error = '参数错误!';
            return false;
        }
        
        $map['city'] = $city;
        $map['status'] = 1;
        
        $id = $this->where($map)->getField('id');
        if($id){
            return $id;
        }else{
            return C('DEFAULTAGENTS');//代理总账号
        }
    }
    
    
}
