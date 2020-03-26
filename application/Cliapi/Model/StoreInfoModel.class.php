<?php

namespace Api\Model;
use Think\Model;

class StoreInfoModel extends Model{
	
	protected $_validate = array(
        array('title', 'require', '店铺标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
		array('title', '1,32', '店铺标题不能超过32个字', self::MUST_VALIDATE , 'length', self::MODEL_BOTH),
		array('content', 'require', '店铺介绍不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('photo', 'require', '上传店铺相册，可以更好的吸引渔民', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('tel', 'require', '店铺电话必填', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		
		array('province', 'require', '请选择省份', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('city', 'require', '请选择城市', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('district', 'require', '请选择地区', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('address', 'require', '请填写店铺详细地址', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('map', 'require', '请在地图中标注您店铺的位置', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
		array('status', 1, self::MODEL_INSERT),
		array('uid', 'is_login', self::MODEL_BOTH, 'function'),
		array('id', 'is_login', self::MODEL_BOTH, 'function'),
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
		
		$photo = explode(',',$data['photo']);
		if(!$data['img']) $data['img'] = $photo[0];
        
		$is = $this->where(array('uid'=>is_login()))->find();


	   	if($is){
			$res = $this->where(array('uid'=>is_login()))->save($data);
			
		}else{
			$res = $this->add($data);
		}

        return $res;
    }
	
	
	
	
}
