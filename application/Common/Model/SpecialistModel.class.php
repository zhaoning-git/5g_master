<?php

namespace Common\Model;

use Common\Model\CommonModel;

class SpecialistModel extends CommonModel {

	protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('microblog', 'require', '微博号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('wasno', 'require', '百家号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('sex', 'require',  '性别不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('certificate', 'require', '相关证明图片不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('restapp', 'require', '其他知名APP名称不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('untnumber', 'require', '账号不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('speciname', 'require', '昵称不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('introduce', 'require', '介绍不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('portrait', 'require', '头像不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
        array('spec_type', 'require', '类型不能为空', 1, 'regex', CommonModel:: MODEL_INSERT),
       
    );
     

}