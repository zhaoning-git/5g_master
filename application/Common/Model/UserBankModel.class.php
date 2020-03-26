<?php

namespace Common\Model;

use Common\Model\CommonModel;

class UserBankModel extends CommonModel {
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
//        /array('title', 'require', '所属银行不能为空！', 1, CommonModel:: MODEL_BOTH),
        array('opnebank', 'require',  '开户行不能为空！',         1, CommonModel:: MODEL_BOTH),
        array('account', 'require', '银行卡号不能为空！', 1, CommonModel:: MODEL_BOTH),
        array('truename', 'require', '账户名不能为空！',  1, CommonModel:: MODEL_BOTH),
    );
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );
    
    //添加 && 编辑 银行
    function addBank($bankName='',$id=''){
        $id = intval($id);
        
        if(empty($bankName)){
            $this->error = '银行名称不能为空!';
            return false;
        }
        
        if($id){
            $bank = M('Bank')->where(array('id'=>$id))->find();
            if(empty($bank)){
                $this->error = '银行不存在!';
                return false;
            }
            
            if($bank['bankname'] != $bankName && M('Bank')->where(array('bankname'=>$bankName))->count()){
                $this->error = '银行名称已存在!';
                return false;
            }
            
            if(M('Bank')->where(array('id'=>$id))->setField('bankname',$bankName) !== false){
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }else{
            if(M('Bank')->where(array('bankname'=>$bankName))->count()){
                $this->error = '银行名称已存在!';
                return false;
            }elseif(M('Bank')->add(array('bankname'=>$bankName))){
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }
    }

    //绑定银行卡
    function bindBank($data){
        $data = $this->create($data);
        if(!$data){
            $this->error = $this->getError();
            return false;
        }
        
        $map['account'] = $data['account'];
        if($this->where($map)->count()){
            $this->error = '银行卡已绑定!';
            return false;
        }

        if(!$this->is_bank( $data['account'])){
            $this->error = '银行卡格式错误!';
            return false;
        }
        
        if(!empty($data['mobile']) && !checkPhone($data['mobile'])){
            $this->error = '手机号码格式错误!';
            return false;
        }
        
        if($data['is_defult'] == 1){
            $this->where(array('uid' => $data['uid']))->setField('is_defult', 0);
        }
        
        if($this->where(array('uid'=>$data['uid']))->count() < 5){
            if(empty($data['title'])){
                unset($data['title']);
            }
            $id = $this->add($data);
            if($id){
                //邀请好友送银币 好友必须同时绑定身份证和银行卡方算邀请成功
                Coin($data['uid'], 'invite_friend');
                //绑定银行卡送288银币
                Coin($data['uid'], 'bind_bank_card');
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }else{
            $this->error = '每个账号最多绑定5张银行卡!';
            return false;
        }
    }

    /**
     * 银行卡正则
     * @param $bank
     * @return bool
     */
    public function is_bank($bank) {
        $chars = "/^(\d{16}|\d{19}|\d{17})$/";
        if (preg_match($chars, $bank)) {
            return true;
        } else {
            return false;
        }
    }
    public function untyingBank($data)
    {
        //查看银行卡是否存在
        $account = $data['account'];
        $uid = $data['uid'];

        $user_bank = $this
            ->where(['uid'=>$uid])
            ->where(['account'=>$account])->find();

        if(!$user_bank){
            $this->error = '银行卡不存在!';
            return false;
        }else{
            $user_bank = $this->where(['uid'=>$uid])
                ->where(['account'=>$account])->save(['uid'=>1]);
            return true;
        }

    }

}
