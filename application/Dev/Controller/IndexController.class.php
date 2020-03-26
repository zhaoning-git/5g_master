<?php

namespace Dev\Controller;

use Think\Controller;

class IndexController extends DevController {

    /**
     * 页面初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Api = M('DevApi');
        $this->Debug = M('DevApiDebug');
    }

    public function index($password = null) {
        $user = array(
            'zzz000' => 'php',
            '123456' => 'app'
        );

        if (IS_POST) {
            if (!$password) {
                $this->error('密码呢？即便是明文密码也不能让你一脚踏进去');
            }

            if ($user[$password]) {
                session('ok', $user[$password]);
                $this->success('登陆成功', U('Dev/Index/' . $user[$password]));
            } else {
                $this->error('密码错误');
            };
        }
        $this->setTitle('开发者登陆');
        $this->display();
    }

    public function Php($id = null) {

        if (session('ok') != 'php') {
            redirect(U('Dev/Index/index'));
        }

        if (IS_POST) {

            $api_remark = $_POST['api_remark'];
            $data = I('post.');
            $data['$api_remark'] = $api_remark;
            
            $data['create_time'] = time();
            if (!$data['api_title'])
                $this->error('接口名称必须填写');
            if (!$data['api_url'])
                $this->error('接口网址必须填写');
            if (!$data['api_category'])
                $this->error('接口分组必须填写');

            if ($data['field']) {
                foreach ($data['field']['name'] as $k => $v) {
                    $api_field[$k]['name'] = $v;
                    $api_field[$k]['remark'] = $data['field']['remark'][$k];
                    $api_field[$k]['value'] = $data['field']['value'][$k];
                    $api_field[$k]['type'] = $data['field']['type'][$k];
                    $api_field[$k]['is_must'] = $data['field']['is_must'][$k];
                    $api_field[$k]['intro'] = $data['field']['intro'][$k];
                }
                $data['api_field'] = json_encode($api_field);
            }

            if ($data['id']) {
                $res = $this->Api->where(array('id' => $id))->save($data);
            } else {
                $res = $this->Api->add($data);
            }

            if ($res !== false) {
                $this->success('接口编辑成功', U('Index/Php'));
                exit;
            } else {
                $this->error('未知错误');
            }
        }

        $category = M('DevApi')->distinct(true)->order('id ASC')->getField('id,api_category');

        foreach ($category as $k => $v) {
            $list[$v] = $this->Api->where(array('api_category' => $v))->select();
        }
        $info = $this->Api->where(array('id' => $id))->find();

        if ($info) {
            $info['api_field'] = json_decode($info['api_field'], true);
        }

        $this->assign('list', $list);
        $this->assign('info', $info);
        $this->setTitle('接口发布');
        $this->display('php');
    }

    public function DelApi($id) {
        $res = $this->Api->where(array('id' => $id))->delete();
        if ($res !== false) {
            $this->success('删除成功', U('Php'));
            exit;
        } else {
            $this->error('未知错误');
        }
    }

    public function App($id = null) {

        if (session('ok') == 'php' || session('ok') == 'app') {

            $category = $this->Api->distinct(true)->field('api_category')->getField('id,api_category');

            foreach ($category as $k => $v) {
                $list[$v] = $this->Api->where(array('api_category' => $v))->select();
            }
            $info = $this->Api->where(array('id' => $id))->find();

            if ($info) {
                $info['api_field'] = json_decode($info['api_field'], true);
            }
            
            $info['api_name'] = strtolower($info['api_url']);
            
            $apiurl = explode('/',$info['api_url']);
            
            $info['api_url'] = U($apiurl['1'].'/'.$apiurl['2'].'/'.$apiurl['3'],'', true, true);
            $this->assign('list', $list);
            $this->assign('info', $info);
            $this->setTitle('接口调试');
            $this->display('app');
        } else {
            redirect(U('Dev/Index/index'));
        }
    }

    public function Debug($id = null) {

        if ($id) {
            $info = $this->Debug->where(array('id' => intval($id)))->order('create_time desc')->find();
            $info['data'] = json_decode($info['data'], true);
            $this->assign('info', $info);
            exit($this->display('debuglog'));
        }


        $list = $this->Debug->order('create_time desc')->limit(50)->select();
        $this->assign('list', $list);
        $this->setTitle('接口访问日志');
        $this->display('debug');
    }

    public function test($id = null) {
        $list = $this->Api->select();
        $this->ajaxReturn($list);
    }

    public function getSign(){
        $data = I('post.');
        $sign = md5($data['uid'].strtolower($data['api']).$data['token']);
        $this->ajaxReturn(array('status'=>1,'sign'=>$sign));
        
    }
    
    public function setTitle($Title){
        $this->assign('title', $Title);
    }

    //WebSocket
    public function WebSocket(){
        if (session('ok') == 'php' || session('ok') == 'app'){
          $this->setTitle('WebSocket');
          $this->display('WebSocket');
        }else{
            redirect(U('Dev/Index/index'));
        }
    }
    
}
