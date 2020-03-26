<?php

namespace Api\Model;

use Think\Model;

class ChannelNewsModel extends Model {

    protected $_validate = array(
        array('module', 'require', '缺少参数module', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('tid', 'require', '缺少参数tid', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', 'require', '请填写资讯标题', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,32', '钓场标题不能超过32个字', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('news_img', 'require', '请上传文章封面图，封面图会大大增强资讯表现力', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('news_content', 'require', '请填写资讯详情', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('status', 1, self::MODEL_INSERT),
        array('uid', 'is_login', self::MODEL_BOTH, 'function'),
    );

    /**
     * 更新数据
     * @author  
     */
    public function Update() {

        $data = $this->create();

        if (!$data) { //数据对象创建错误
            return false;
        }

        if ($data['id']) {
            $res = $this->where(array('uid' => is_login(), 'id' => $data['id']))->save($data);
        } else {
            $data['id'] = $this->add($data);
            if ($data['id'] !== false && !$data['sort'])
                $this->where(array('id' => $data['id']))->save(array('sort' => $data['id']));
        }

        return $data;
    }

}
