<?php

//话题管理
namespace Common\Model;

use Think\Model;

class ThemeModel extends Model {

    protected $_validate = array(
        array('title', 'require', '话题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
    );
    
    protected $_auto = array(
        array('addtime', NOW_TIME, self::MODEL_INSERT),
    );

    //添加话题
    function Insert($data = array()) {
        $data = $this->create($data, 1);
        if (isset($data['uid'])) {
            $data['uid'] = intval($data['uid']);
        } else {
            $data['uid'] = 1;
        }

        //判断重复
        $map['title'] = $data['title'];
        $map['cat'] = $data['cat'];
        $map['type'] = $data['type'];
        if ($this->where($map)->count()) {
            $this->error = '该话题已存在!';
            return false;
        }

        if ($this->add($data)) {
            return true;
        } else {
            $this->error = $this->getDbError();
            return false;
        }
    }

    //快速添加话题
    function kInsert($data = array()){
        $data = $this->create($data, 1);
        if (isset($data['uid'])) {
            $data['uid'] = intval($data['uid']);
        } else {
            $data['uid'] = 1;
        }
        
        $map['title'] = $data['title'];
        if (!$this->where($map)->count()) {
            $data['type'] = 2;
            $data['addtime'] = NOW_TIME;
            if ($this->add($data)) {
                return true;
            } else {
                $this->error = $this->getDbError();
                return false;
            }
        }
        return true;
    }




    //编辑话题
    function Update($data = array()) {
        $id = intval($data['id']);
        if (!$id) {
            $this->error = '参数错误,话题ID不能为空!';
            return false;
        }

        $Info = $this->where(array('id' => $id))->find();
        if (empty($Info)) {
            $this->error = '话题不存在!';
            return false;
        }

        //判断重复
        if ($data['title'] != $Info['title'] || $data['cat'] != $Info['cat'] || $data['type'] != $Info['type']) {
            $map['title'] = $data['title'];
            $map['cat'] = $data['cat'];
            $map['type'] = $data['type'];
            if ($this->where($map)->count()) {
                $this->error = '该话题已存在!';
                return false;
            }
        }

        if ($Info['status'] && ($data['status'] != 1 && $data['status'] != 3)) {
            $this->error = '该话题已审核!';
            return false;
        }

        $save['title'] = $data['title'];
        $save['type'] = $data['type'];
        $save['cat'] = $data['cat'];
        $save['comment'] = $data['comment'];
        $save['remark'] = $data['remark'];
        $save['status'] = $data['status'];
        $save['update_time'] = $data['update_time'];

        if (!$Info['status'] && $data['status']) {
            $save['verify_time'] = time();
        }
        if ($this->where(array('id' => $Info['id']))->save($save) !== false) {
            return true;
        } else {
            $this->error = $this->getDbError();
            return false;
        }
    }

}
