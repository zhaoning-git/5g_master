<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-26
 * Time: 下午4:29
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */

namespace Common\Model;

use Think\Model;

class VerifyModel extends Model {

    protected $tableName = 'verify';
    protected $_auto = array(array('create_time', NOW_TIME, self::MODEL_INSERT));

    public function addVerify($account, $type = 'reg', $uid = 0) {
        if(!checkPhone($account)){
            $this->error = '手机号码格式错误!';
            return false;
        }
        
        //每天限制短信发送条数
        C('SMS_LIMIT', 5);
        
        if (!in_array($type, array('reg', 'pwd', 'login', 'editmobile', 'bindphone'))) {
            $this->error = '验证类型错误!';
            return false;
        }
        
        //删除旧数据
        $_where['uid'] = $uid;
        $_where['type'] = $type;
        $_where['datetx'] = array('LT', date('Ymd', time()));
        $this->where($_where)->delete();
        
        $where = array(
            'uid' => $uid, 
            'type' => $type
        );
        
        $map = array(
            'uid' => $uid, 
            'type' => $type,
            'account' => $account
        );
        
        
        $up_time = $this->where($map)->getField('create_time');
        if (time() - $up_time < 60) {
            $this->error = '验证码已发送,您可以在' . (60 - (time() - $up_time)) . '秒后再次进行获取';
            return false;
        }
        
        $where['datetx'] = date('Ymd', time());
        $count = $this->where($where)->getField('count');
        if ($count >= C('SMS_LIMIT')) {
            $this->error = '您今天的短信发送已经超过' . C('SMS_LIMIT') . '条，不能再发送';
            return false;
        }
        
        $uid = $uid ? $uid : is_login();
        $verify = create_rand(6, 'num');
        
        $data['verify'] = $verify;
        $data['account'] = $account;
        $data['type'] = $type;
        $data['uid'] = $uid;
        $data['count'] = $count + 1;
        $data['datetx'] = date('Ymd', time());
        
        if($count > 0){
            $this->where($where)->delete();
        }
        
        $data = $this->create($data);
        $res = $this->add($data);
        if (!$res) {
            $this->error = '未知错误，验证码获取失败!';
            return false;
        }

        $Result = $this->SendCode($account, $verify, $type);
        
        if($Result === true){
            $Ret['status'] = 1;
            $Ret['info'] = '验证码发送成功!';
        }elseif($Result === false){
            $Ret['status'] = 0;
            $Ret['info'] = $this->error;
        }else{
            $Ret = $Result;
        }
        return $Ret;
    }

    public function getVerify($id) {
        $verify = $this->where(array('id' => $id))->getField('verify');
        return $verify;
    }

    public function delVerify($account) {
        $this->where(array('account' => $account))->delete();
    }

    public function checkVerify($account, $type, $verify_code, $uid='') {
        if(!checkPhone($account)){
            $this->error = '手机号码格式错误';
            return false;
        }
        
        $where = array('account' => $account, 'type' => $type);
        
        if($uid){
            $where['uid'] = intval($uid);
        }
        $where['type'] = $type;
        $where['account'] = $account;
        
        
        
        $verify = $this->where($where)->find();
        if(empty($verify)){
            $this->error = '验证码不存在，请重新获取验证码!';
            return false;
        }
        
        
        if($verify['is_check'] == 1){
            $this->error = '验证码已使用，请重新获取验证码!';
            return false;
        }
        
        
        if ($verify['verify'] != $verify_code) {
            $this->error = '验证码不正确，请核实后重新验证!';
            return false;
        }
        
        
        if(time()-$verify['create_time'] > 5*60){
            $this->error = '验证码5分钟内有效，请重新获取验证码!';
            return false;
        }
        
        if ($verify['verify'] == $verify_code) {
            $this->where(array('id'=>$verify['id']))->setField('is_check', 1);
            return true;
        }
        
    }

    /**
     * doSendVerify  发送验证码
     * @param $account
     * @param $verify
     * @param $type
     * @return bool|string
     */
    public function SendCode($account, $code, $type) {
        if(!checkPhone($account)){
            $this->error = '手机号码格式错误!';
            return false;
        }
        if (APP_DEBUG !== false) {
            //return array('status' => 1, 'info' => 'DEBUG模式;您的验证码为：' . $code);
        }

        switch ($type) {
            case 'reg':
                $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                $res = api('YunPian/sendSMS', array('mobile' => $account, 'content' => $content));
                break;
            case 'pwd':
                $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                $res = api('YunPian/sendSMS', array('mobile' => $account, 'content' => $content));
                break;
            case 'bindphone':
                $res = api('AliyunSms/sendSms', array('phone' => $account, 'code' => $code));
                
                if($res['Code'] == 'OK'){
                    $res['status'] = 1;
                }else{
                    $res['status'] = 0;
                    $res['info'] = $res['Message'];
                }
                break;
        }

        if ($res['status'] == 1) {
            return true;
        } else {
            $this->error = $res['info'];
            return false;
        }
    }

}
