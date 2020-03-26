<?php

/**
 */
namespace Cliapi\Controller;

use Think\Controller;

class ApiController extends Controller {

    public $Return;
    public $sconfig;
    public function _initialize() {
        $sconfig = M("options")->where("option_name='configpub'")->getField("option_value");
        $this->sconfig = json_decode($sconfig, true);
        
        $this->Return = array('status' => 0, 'info' => null, 'data' => null);
        $this->Debug = M('DevApiDebug');
        $this->Debug();
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods:POST,GET");
        header("Access-Control-Allow-Headers:x-requested-with,content-type");
        header("Content-type:text/json;charset=utf-8");
    }

    public function ajaxRet($par = 0, $info='', $data=null) {
        if(is_array($par)){
            $this->ajaxReturn(array_merge($this->Return, $par));
            exit;
        }else{
            $arr = array('status'=>$par, 'info'=>$info, 'data'=>$data);
            $this->ajaxReturn(array_merge($this->Return, $arr));
            exit;
        }
    }

    
    //记录接口访问日志
    public function Debug() {
        if (IS_POST) {
            $data = I('post.');
            $type = 'post';
        } else {
            $data = I('get.');
            $type = 'get';
        }
        $array['uid'] = is_login();
        $array['url'] = get_url();
        $array['data'] = json_encode($data);
        $array['type'] = $type;
        $array['create_time'] = time();
        $array['ip'] = get_client_ip();

        $this->Debug->add($array);
    }
    
    //删除user_token
    protected function DelToken($uid) {
        D('UserToken')->where(array('uid' => $uid))->delete();
        D('Users')->logout();
    }
    

    //通过文件流上传视频
    public function UploadMedia() {
        if (!is_login()) {
            $this->ajaxRet(array('status'=>0,'info'=>'未登录会员无法上传文件'));		
        }

        $Obj = A('Core/File');
        $file = $Obj->uploadMedia('self');

        if ($file['status'] == 1) {
            $this->ajaxRet(array('status' => 1, 'info' => $file['info'], 'id' => $file['data']['file']['id'], 'path' => Host() . $file['data']['file']['path']));
        } else {
            $this->ajaxRet(array('status' => 0, 'info' => $file['info']));
        }
    }

    //中国地区
    public function Area($id) {
        $map['pid'] = intval($id);

        $list = M('District')->where($map)->order('id asc')->field('id,name')->select();

        if ($list) {
            $this->ajaxRet(array('status' => 1, 'list' => $list));
        } else {
            $this->ajaxRet(array('status' => 0, 'info' => '没有获取到城市'));
        }
    }
    
    protected function lists($model, $where = array(), $order = '', $base = array(), $field = true) {
        $options = array();
        $REQUEST = (array) I('request.');
        if (is_string($model)) {
            $model = M($model);
        }

        $OPT = new \ReflectionProperty($model, 'options');
        $OPT->setAccessible(true);

        $pk = $model->getPk();
        if ($order === null) {
            //order置空
        } elseif (isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']), array('desc', 'asc'))) {
            $options['order'] = '`' . $REQUEST['_field'] . '` ' . $REQUEST['_order'];
        } elseif ($order === '' && empty($options['order']) && !empty($pk)) {
            $options['order'] = $pk . ' desc';
        } elseif ($order) {
            $options['order'] = $order;
        }
        unset($REQUEST['_order'], $REQUEST['_field']);

        $options['where'] = array_filter(array_merge((array) $base, /* $REQUEST, */ (array) $where), function ($val) {
            //if ($val === '' || $val === null) {
            if ($val === null) {
                return false;
            } else {
                return true;
            }
        });
        if (empty($options['where'])) {
            unset($options['where']);
        }
        $options = array_merge((array) $OPT->getValue($model), $options);
        $total = $model->where($options['where'])->count();

        if (isset($REQUEST['r'])) {
            $listRows = (int)$REQUEST['r'];
        } else {
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        }
        $page = new \Think\Page($total, $listRows, $REQUEST);
        if ($total > $listRows) {
            $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p = $page->show();
        $this->assign('_page', $p ? $p : '');//分页HTML
        $this->assign('_total', $total);//总条数
        $this->assign('_listRows', $listRows); //每页显示条数
        $this->assign('_totalPages', $page->totalPages); //总页数
        $options['limit'] = $page->firstRow . ',' . $page->listRows;
        $model->setProperty('options', $options);
        return $model->field($field)->select();
    }
    //curl 请求
    
    
    
    
}
