<?php

namespace Api\Model;
use Think\Model;

class ChannelGroundsModel extends Model{
	
	protected $_validate = array(
        array('title', 'require', '钓场标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
		array('title', '1,32', '钓场标题不能超过32个字', self::MUST_VALIDATE , 'length', self::MODEL_BOTH), 
		array('photo', 'require', '上传钓场相册，可以更好的吸引渔民', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		//array('img', 'require', '请上传钓场封面图', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('tel', 'require', '钓场电话必填', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('contacts', 'require', '联系人必填', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		
		array('province', 'require', '请选择省份', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('city', 'require', '请选择城市', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('district', 'require', '请选择地区', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('address', 'require', '请填写钓场详细地址', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('map', 'require', '请在地图中标注您钓场的位置', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
		array('update_time', NOW_TIME, self::MODEL_UPDATE),
		array('status', 1, self::MODEL_INSERT),
		array('uid', 'is_login', self::MODEL_BOTH, 'function'),
		array('view', 1, self::MODEL_INSERT),
		array('view_loadder', 1, self::MODEL_INSERT),
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
		
		/*if($data['ponds']){
			foreach($data['ponds']['name'] as $k =>$v){
				$data['ponds'][$k]['name'] = $v;
				$data['ponds'][$k]['price'] = $data['ponds']['price'][$k];
				$data['ponds'][$k]['recover'] = $data['ponds']['recover'][$k];
			}
			unset($data['ponds']['name'],$data['ponds']['price'],$data['ponds']['recover']);
			if($data['ponds']) $data['ponds'] = json_encode($data['ponds']);
		}*/

		$photo = explode(',',$data['photo']);
		if(!$data['img']) $data['img'] = $photo[0];
        
	   	if($data['id']){
			$res = $this->where(array('uid'=>is_login(),'id'=>$data['id']))->save($data);
		}else{
			$data['id'] = $this->add($data);
			$group_id = R('Message/AddGroup',array($data['title'].'群组', $data['img'] ,'self'));

			if($group_id === false){
				$this->error = '店铺创建成功，但群组创建失败';
				return false;
			}

			if($data['id']!==false && !$data['sort'])  $this->where(array('id'=>$data['id']))->save(array('sort'=>$data['id'],'group_id'=>$group_id));
		}
		
        return $data;
    }
	
	
	
	
}
