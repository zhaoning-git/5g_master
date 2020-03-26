<?php

namespace Common\Model;

use Common\Model\CommonModel;

class AdvertisingModel extends CommonModel {
	protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('types', 'require', '类型不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('buseiness', 'require', '推广信息不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('cardz', 'require',  '身份证正面不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('cardf', 'require', '身份证反面不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('selfcard', 'require', '本人持身份证不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('name', 'require', '姓名不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('card', 'require', '身份证号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('industry_id', 'require', '所属行业不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
       // array('uid', 'require', '用户id必须', 1, 'regex', CommonModel:: MODEL_INSERT),
    );
     

}