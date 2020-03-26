<?php

namespace Common\Model;

use Common\Model\CommonModel;

class OrganiconModel extends CommonModel {
	protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('operator', 'require', '运营者姓名不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('information', 'require', '认证信息不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('location', 'require',  '所在地不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('organization', 'require', '机构名称不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('licenumber', 'require', '营业执照号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('barcode', 'require', '组织机构代码不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('license', 'require', '营业执照不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('barcodes', 'require', '组织机构代码证不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('letter', 'require', '认证公函不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('mobile', 'require', '手机号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('sms_code', 'require', '手机号验证码不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
       // array('uid', 'require', '用户id必须', 1, 'regex', CommonModel:: MODEL_INSERT),
    );
}