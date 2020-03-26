<?php

namespace Common\Model;

use Common\Model\CommonModel;

class BindingModel extends CommonModel {
	protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('company', 'require', '公司名称不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('types', 'require', '类型不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('name', 'require',  '姓名不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('site', 'require', '地址不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('industry_id', 'require', '行业不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('wx', 'require', '微信号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('mailbox', 'require', '邮箱不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('mobile', 'require', '手机号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('sms_code', 'require', '验证码不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('uid', 'require', '用户id必须', 1, 'regex', CommonModel:: MODEL_INSERT),
    );
    

}