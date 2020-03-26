<?php

namespace Api\Model;
use Think\Model;

class ChannelBbsModel extends Model{
	
	protected $_validate = array(
        array('title', 'require', '帖子标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
		array('title', '1,32', '钓场标题不能超过32个字', self::MUST_VALIDATE , 'length', self::MODEL_BOTH),
		array('bbs_content', 'require', '帖子详情不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('bbs_content', '1,800', '帖子内容太长了，不能大于800个字', self::MUST_VALIDATE , 'length', self::MODEL_BOTH),
	
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
		array('update_time', NOW_TIME, self::MODEL_UPDATE),
		array('status', 1, self::MODEL_INSERT),
		array('uid', 'is_login', self::MODEL_BOTH, 'function'),
		array('view', 1, self::MODEL_INSERT),
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
		
		if($data['map'] && $data['show_address'] == 1){ 
		  	$address =  api('Gps/Map2Address',array('map'=>$data['map'])) ;
		 	$data['province'] = $address['address_component']['province'];
			$data['city'] = $address['address_component']['city'];
			$data['district'] = $address['address_component']['district'];
			$data['address'] = $address['address'];
		}
	
	   	if($data['id']){
			$res = $this->where(array('id'=>$data['id']))->field('status,id,uid',true)->save($data);
		}else{
			$data['id'] = $this->add($data);
		}
		
		if($data['id'] === false && $res === false){
			$this->error = '帖子发布失败';	
			return false;
		}
		
        return $data;
    }
	
	
	
	
}
