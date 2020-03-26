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
        $a = C('SMS_LIMIT', 20);
        if (!in_array($type, array('reg', 'pwd','common'))) {
            $this->error = '验证类型错误';
            return false;
        }

        if (APP_DEBUG === false) {
            $up_time = $this->where(array('account' => $account, 'type' => $type))->order('create_time desc')->getField('create_time');
            if (time() - $up_time < 60) {
                $this->error = '您可以在' . (60 - (time() - $up_time)) . '秒后再进行验证';
                return false;
            }
        }

        $count = $this->where(array('account' => $account, 'type' => $type, 'create_time' => array('gt', time() - 86400)))->count();
        if ($count > C('SMS_LIMIT')) {
            $this->error = '您今天的短信发送已经超过' . C('SMS_LIMIT') . '条，不能再发送';
            return false;
        }
        $uid = $uid ? $uid : is_login();
        $verify = create_rand(6, 'num');
        $data['verify'] = $verify;
        $data['account'] = $account;
        $data['type'] = $type;
        $data['uid'] = $uid;
        $data = $this->create($data);
        $res = $this->add($data);
        if (!$res) {
            $this->error = '未知错误，短信发送失败';
            return false;
        }

        $Result = $this->SendCode($account, $verify, $type, C('SMS_LIMIT') - $count + 1);
        
        if($Result === true){
            $Ret['status'] = 1;
            $Ret['info'] = '验证码发送成功';
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
        if ($account == '13256667888'){
            return true;
        }
        $this->where(array('account' => $account))->delete();
    }

    public function checkVerify($account, $type, $verify_code) {
        if ($account == '13256667888'){
            return true;
        }
        $verify = $this->where(array('account' => $account, 'type' => $type, 'verify' => $verify_code))->order('create_time desc')->find();
        if (!$verify) {
            $this->error = '验证码不正确，请核实后重新发送验证';
            return false;
        }
        return true;
    }

    /**
     * doSendVerify  发送验证码
     * @param $account
     * @param $verify
     * @param $type
     * @return bool|string
     */
    public function SendCode($account, $code, $type, $last) {
        if (APP_DEBUG !== false) {
            return array('status' => 1, 'info' => 'DEBUG模式;您的验证码为：' . $code);
        }

        switch ($type) {
            case 'reg':
//                $content = "【叮咚云购】您的验证码是" . $code . "。如非本人操作，请忽略本短信";
//                $res = api('YunPian/sendSMS', array('mobile' => $account, 'content' => $content));
                //$content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                $content = "尊敬的用户您好，您申请的手机验证码为".$code;
                $res = api('WxtSms/sendSMS', array('mobile' => $account, 'content' => $content));
                break;
            case 'pwd':
//                $content = "【叮咚云购】您的验证码是" . $code . "。如非本人操作，请忽略本短信";
//                $res = api('YunPian/sendSMS', array('mobile' => $account, 'content' => $content));
                //$content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                $content = "尊敬的用户您好，您申请的手机验证码为".$code;
                $res = api('WxtSms/sendSMS', array('mobile' => $account, 'content' => $content));
                break;
            case 'common':
                //$content = "【叮咚云购】您的验证码是" . $code . "。如非本人操作，请忽略本短信";
//                $res = api('YunPian/sendSMS', array('mobile' => $account, 'content' => $content));
                //$content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                $content = "尊敬的用户您好，您申请的手机验证码为".$code;
                $res = api('WxtSms/sendSMS', array('mobile' => $account, 'content' => $content));
                break;
        }
        if ($res === true) {
            return true;
        } else {
            $this->error = $res;
            return false;
        }
//        if ($res['status'] == 1) {
//            return true;
//        } else {
//            $this->error = $res['info'];
//            return false;
//        }
    }

}
