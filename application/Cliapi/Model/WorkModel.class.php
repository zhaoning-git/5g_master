<?php

namespace Api\Model;
use Think\Model;

class WorkModel extends Model{
	
	protected $_validate = array(
        array('content', 'require', '要说点什么呢~', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
		array('uid', 'is_login', self::MODEL_BOTH, 'function'),
    );
	
	/**上传作品*/

public function shangchuan($array){

	$data = $this->create($array);

   if(!$data){ //数据对象创建错误
  			return false;
        }else{
			M("work")->add($data);
			return true;

		}

	
	
}

}
