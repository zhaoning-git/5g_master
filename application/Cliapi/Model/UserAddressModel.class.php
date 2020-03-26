<?php

namespace Api\Model;
use Think\Model;

class UserAddressModel extends Model{
	
	protected $_validate = array(
        array('truename', 'require', '请填写收件人姓名', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
		array('mobile', '1,32', '请填写收件人手机号', self::MUST_VALIDATE , 'length', self::MODEL_BOTH),
		array('province', 'require', '请选择省份', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('city', 'require', '请选择城市', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('district', 'require', '请选择区/县', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('address', 'require', '请填写详细的收货地址', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
		array('uid', 'is_login', self::MODEL_BOTH, 'function'),
    );
	
	
	/**
	 * 更新数据
	 * @author  
	 */
	public function Update(){
	
	 	$data = $this->create();
		
        if(!$data){ //数据对象创建错误
            return false;
        }
		
	   	if($data['id']){
			$res = $this->where(array('uid'=>is_login(),'id'=>$data['id']))->save($data);
		}else{
			$res = $this->add($data);
			$data['id'] = $res;
		}
		
		if($res!==false){
			 return $data['id'];
		}else{
			$this->error = '收货地址创建失败';
			return false;
		}
       
    }
	
	
	
	
}
