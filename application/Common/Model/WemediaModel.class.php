<?php
//个人自媒体认证
namespace Common\Model;

use Common\Model\CommonModel;

class WemediaModel extends CommonModel {
	protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '姓名不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('information', 'require', '认证信息不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('idnumber', 'require',  '身份证号不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('cardself', 'require', '手持身份证不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('prove', 'require', '图片证明不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('mobile', 'require', '手机号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('sms_code', 'require', '手机号验证码不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
       // array('uid', 'require', '用户id必须', 1, 'regex', CommonModel:: MODEL_INSERT),
    );
}