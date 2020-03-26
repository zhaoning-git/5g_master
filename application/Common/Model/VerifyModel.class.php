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
use Org\PHPMailer;
use Org\Exception;

class VerifyModel extends Model {

    protected $tableName = 'verify';
    protected $_auto = array(array('create_time', NOW_TIME, self::MODEL_INSERT));

    //type  reg:注册 pwd:找回密码 login:登录 bindphone:绑定手机 editmobile:修改手机号码  editemail:修改邮箱 binding:绑定广告 relation:个人联系方式 binlogin:广告登录 comption：企业联系方式 enterprise :企业机构认证申请 wemedia:个人自媒体认证申请 teamligent:球队达人
    public function addVerify($account, $type = 'reg', $uid = 0) {
        if(checkPhone($account)){
            $sendType = 'phone';
        }elseif(checkEmail($account)){
            $sendType = 'email';
        }else{
            $this->error = '格式错误!';
            return false;
        }
        
        //每天限制短信发送条数
        C('SMS_LIMIT', 5);
        
        if (!in_array($type, array('reg', 'pwd', 'login', 'bindphone', 'editmobile', 'editemail','binding','relation','binlogin','comption','enterprise','wemedia','teamligent'))) {
            $this->error = '验证类型错误!';
            return false;
        }
        
        //删除旧数据
        $_where['type'] = $type;
        $_where['datetx'] = array('LT', date('Ymd', time()));
        $this->where($_where)->delete();
        
        $where['type']   = $type;
        $where['account'] = $account;
        
        //验证码
        $verify = create_rand(6, 'num');
        
        $Info = $this->where($where)->find();
        if(!empty($Info)){
            if (time() - $Info['create_time'] < 60) {
                $this->error = '验证码已发送,您可以在' . (60 - (time() - $Info['create_time'])) . '秒后再次进行获取';
                return false;
            }

            if ($Info['count'] >= C('SMS_LIMIT')) {
                $this->error = '您今天发送已经超过' . C('SMS_LIMIT') . '条，不能再发送';
                return false;
            }
            
            $data['is_check'] = 0;
            $data['verify']      = $verify;
            $data['count']       = $Info['count'] + 1;
            $data['create_time'] = NOW_TIME;
            $res = $this->where(array('id'=>$Info['id']))->save($data);
        }
        
        else{
            $uid = $uid ? $uid : is_login();
            $data['verify'] = $verify;
            $data['account'] = $account;
            $data['type'] = $type;
            $data['uid'] = $uid;
            $data['count'] = $count + 1;
            $data['datetx'] = date('Ymd', time());

            $data = $this->create($data);
            $res = $this->add($data);
        }
        
        if (!$res) {
            $this->error = '未知错误，验证码获取失败!';
            return false;
        }

        $Result = $this->SendCode($account, $verify, $type, $sendType);
        
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
    //语音验证码
    public function senYu($account, $type = 'reg', $uid = 0){
        if(checkPhone($account)){
            $sendType = 'phones';
        }else{
            $this->error = '格式错误!';
            return false;
        }
        
        //每天限制短信发送条数
        C('SMS_LIMIT', 5);
        
        if (!in_array($type, array('reg', 'pwd', 'login', 'bindphone', 'editmobile', 'editemail','binding','relation','binlogin','comption','enterprise','wemedia','teamligent'))) {
            $this->error = '验证类型错误!';
            return false;
        }
        //删除旧数据
        $_where['type'] = $type;
        $_where['datetx'] = array('LT', date('Ymd', time()));
        $this->where($_where)->delete();
        
        $where['type']   = $type;
        $where['account'] = $account;
        
        //验证码
        $verify = create_rand(6, 'num');
        
        $Info = $this->where($where)->find();
        if(!empty($Info)){
            if (time() - $Info['create_time'] < 60) {
                $this->error = '验证码已发送,您可以在' . (60 - (time() - $Info['create_time'])) . '秒后再次进行获取';
                return false;
            }

            if ($Info['count'] >= C('SMS_LIMIT')) {
                $this->error = '您今天发送已经超过' . C('SMS_LIMIT') . '条，不能再发送';
                return false;
            }
            
            $data['is_check'] = 0;
            $data['verify']      = $verify;
            $data['count']       = $Info['count'] + 1;
            $data['create_time'] = NOW_TIME;
            $res = $this->where(array('id'=>$Info['id']))->save($data);
        }
        
        else{
            $uid = $uid ? $uid : is_login();
            $data['verify'] = $verify;
            $data['account'] = $account;
            $data['type'] = $type;
            $data['uid'] = $uid;
            $data['count'] = $count + 1;
            $data['datetx'] = date('Ymd', time());

            $data = $this->create($data);
            $res = $this->add($data);
        }
        
        if (!$res) {
            $this->error = '未知错误，验证码获取失败!';
            return false;
        }

        $Result = $this->SendsCode($account, $verify, $type, $sendType);
        
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
    public function SendsCode($account, $code, $type, $sendType){
         if($sendType == 'phones' && !checkPhone($account)){
            $this->error = '手机号码格式错误!';
            return false;
        }

        

        // switch ($type) {
        //     case 'reg':
        //         $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
        //         if($sendType=='email'){
        //             $Subject = '欢迎注册';
        //             $content = "您的验证码是" . $code . "。请及时验证";
        //         }
        //         break;
        //     case 'pwd':
        //         $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
        //         if($sendType=='email'){
        //             $Subject = '修改密码';
        //             $content = "您的验证码是" . $code . "。请及时验证";
        //         }
        //         break;
        //     case 'login':
        //         $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
        //         if($sendType=='email'){
        //             $Subject = '登录';
        //             $content = "您的验证码是" . $code . "。请及时验证";
        //         }
        //         break;
        //     case 'bindphone':
        //         $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
        //         break;
        //     case 'editmobile':
        //         $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
        //         break;
        //     case 'editemail':
        //         $Subject = '修改邮箱';
        //         $content = "您的验证码是" . $code . "。请及时验证";
        //         break;
        // }

        // if (1 !== false) {
        //     if($sendType == 'email'){
        //         $res = $this->SendEmail($account, $Subject, $content);
        //         return array('status' => 1, 'info' => 'DEBUG模式:' . $content);
        //     }
        //     return array('status' => 1, 'info' => 'DEBUG模式;您的验证码为：' . $code);
        // }
        
        
        
        if($sendType == 'phones'){
             $res = $this->SendPhones($account, $code);
            
             

        }
        if($res['count']!== ''){
           return true;
        }else{
            return false;
        }
        // if ($res['status'] == 1) {
        //     return true;
        // } else {
        //     $this->error = $res['info'];
        //     return false;
        // }

    }
    public function SendPhones($account, $content){
        //apikey 
        $apikey = 'f693a939f9d253986e18610401191357';
        //请求地址
        $url = 'https://voice.yunpian.com/v2/voice/send.json';
        $data = [
             'mobile' =>$account,
             'code' => $content,
             'apikey' => $apikey,
        ];
        return $this->postRequest($url,$data);

    }
     function curls($url,$data,$type){

        $data = json_encode($data);
        $ch = curl_init(); //初始化CURL句柄 
        curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,$type); //设置请求方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置提交的字符串
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output,true);
    }
    public function getVerify($id) {
        $verify = $this->where(array('id' => $id))->getField('verify');
        return $verify;
    }

    public function delVerify($account) {
        $this->where(array('account' => $account))->delete();
    }

    //验证验证码
    public function checkVerify($account, $type, $verify_code, $uid='') {
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

    function MobTechCheckVerify($account, $verify_code){
        // 配置项
        $api = 'https://webapi.sms.mob.com/sms/verify';
        $appkey = '您的appkey';

        // 发送验证码
        $params = array(
            'appkey' => $appkey,
            'phone' => $account,
            'zone' => '86',
            'code' => $verify_code,
        );
        
        $response = postRequest( $api, $params);
    }

    /**
     * 发起一个post请求到指定接口
     * @param string $api 请求的接口
     * @param array $params post参数
     * @param int $timeout 超时时间
     * @return string 请求结果
     */
    function postRequest( $api, array $params = array(), $timeout = 30 ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $api );
        // 以返回的形式接收信息
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        // 设置为POST方式
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
        // 不验证https证书
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Accept: application/json',
        ) ); 
        // 发送数据
        $response = curl_exec( $ch );
        // 不要忘记释放资源
        curl_close( $ch );
        return $response;
    }
    
    /**
     * doSendVerify  发送验证码
     * @param $account
     * @param $verify
     * @param $type
     * @return bool|string
     */
    //type  reg:注册 pwd:找回密码 login:登录 bindphone:绑定手机 editmobile:修改手机号码  editemail:修改邮箱
    public function SendCode($account, $code, $type, $sendType) {
        if($sendType == 'phone' && !checkPhone($account)){
            $this->error = '手机号码格式错误!';
            return false;
        }

        if($sendType == 'email' && !checkEmail($account)){
            $this->error = '邮箱格式错误!';
            return false;
        }

        switch ($type) {
            case 'reg':
                $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                if($sendType=='email'){
                    $Subject = '欢迎注册';
                    $content = "您的验证码是" . $code . "。请及时验证";
                }
                break;
            case 'pwd':
                $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                if($sendType=='email'){
                    $Subject = '修改密码';
                    $content = "您的验证码是" . $code . "。请及时验证";
                }
                break;
            case 'login':
                $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                if($sendType=='email'){
                    $Subject = '登录';
                    $content = "您的验证码是" . $code . "。请及时验证";
                }
                break;
            case 'bindphone':
                $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                break;
            case 'editmobile':
                $content = "您的验证码是" . $code . "。如非本人操作，请忽略本短信";
                break;
            case 'editemail':
                $Subject = '修改邮箱';
                $content = "您的验证码是" . $code . "。请及时验证";
                break;
        }

        if (1 !== false) {
            if($sendType == 'email'){
                $res = $this->SendEmail($account, $Subject, $content);
                return array('status' => 1, 'info' => 'DEBUG模式:' . $content);
            }
            return array('status' => 1, 'info' => 'DEBUG模式;您的验证码为：' . $code);
        }
        
        
        
        if($sendType == 'phone'){
            $res = $this->SendPhone($account, $content);
        }
        elseif($sendType == 'email'){
            $res = $this->SendEmail($account, $Subject, $content);
        }

        
        if ($res['status'] == 1) {
            return true;
        } else {
            $this->error = $res['info'];
            return false;
        }
    }

    function SendPhone($phone, $content){
        
    }
    
    function SendEmail($email, $Subject, $content){
        $mail = new \PHPMailer(true);
        
        try {
            //使用STMP服务
            $mail->isSMTP();
 
            //这里使用我们第二步设置的stmp服务地址
            $mail->Host = "smtp.163.com";
 
            //设置是否进行权限校验
            $mail->SMTPAuth = true;
 
            //第二步中登录网易邮箱的账号
            $mail->Username = "fxmade@163.com";
 
            //客户端授权密码，注意不是登录密码
            $mail->Password = "qg112233";
 
            //使用ssl协议
            $mail->SMTPSecure = 'ssl';
 
            //端口设置
            $mail->Port = 465;
 
            //字符集设置，防止中文乱码
            $mail->CharSet= "utf-8";
 
            //设置邮箱的来源，邮箱与$mail->Username一致，名称随意
            $mail->setFrom($mail->Username, "趣构科技");
 
            //设置收件的邮箱地址
            $mail->addAddress($email);
 
            //设置回复地址，一般与来源保持一直
            $mail->addReplyTo($mail->Username, "趣构科技");
 
            $mail->isHTML(true);
            //标题
            $mail->Subject = $Subject;
            //正文
            $mail->Body    = $content;
            $mail->send();
            return array('status'=> 1, 'info'=>'发送成功');
        } 
        
        catch (Exception $e) {
            echo $e;
        }


        
    }
    
}
