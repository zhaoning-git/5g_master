<?php

namespace Common\Model;
//联系方式
use Common\Model\CommonModel;

class RelationModel extends CommonModel {
	protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
       // array('enterprise', 'require', '公司名称不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('types', 'require', '类型不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('name', 'require',  '姓名不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('province_id', 'require', '省不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('city', 'require', '市区不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('mobile', 'require', '手机号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('sms_code', 'require', '验证码不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
    );
    

}