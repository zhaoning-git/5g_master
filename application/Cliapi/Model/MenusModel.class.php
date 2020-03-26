<?php

namespace Api\Model;
use Think\Model;

class MenusModel extends Model{
	
	protected $_validate = array(
        array('title', 'require', '菜谱名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('material', 'require', '用料不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('practice', 'require', '做法不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('recommend', 'require', '推荐不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
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
			M("menus")->add($data);
			return true;

		}

	
	
}

}
