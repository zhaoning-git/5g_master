<?php

/**
 * 商品管理
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class GoodsController extends AdminbaseController {

    function index() {
        $Car = M("car");
        $count = $Car->count();
        $page = $this->page($count, 20);
        $lists = $Car
                ->order("orderno asc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        $this->assign('lists', $lists);
       // $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    function del() {
        $id = intval($_GET['id']);
        if ($id) {
            $result = M("car")->delete($id);
            if ($result) {
                $action = "删除坐骑：{$id}";
                setAdminLog($action);
                $this->resetcache();
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    //排序
    public function listorders() {

        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("car")->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $action = "更新坐骑排序";
            setAdminLog($action);
            $this->resetcache();
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
    //商品消息广告排序
    public function lisxiao(){
         $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['sort'] = $r;
            M("duihuan_tong")->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            // $action = "更新坐骑排序";
            // setAdminLog($action);
            // $this->resetcache();
            $this->success("排序更新成功！",U('Goods/limitsxi'));
        } else {
            $this->error("排序更新失败！");
        }
    }

    function add() {
        $this->display();
    }

    function add_post() {
        if (IS_POST) {

            $name = $_POST['name'];

            if ($name == "") {
                $this->error("请填写坐骑名称");
            }
            $needcoin = $_POST['needcoin'];
            if ($needcoin == "") {
                $this->error("请填写坐骑所需点数");
            }

            if (!is_numeric($needcoin)) {
                $this->error("请确认坐骑所需点数");
            }

            $swftime = $_POST['swftime'];
            if ($swftime == "") {
                $this->error("请填写动画时长");
            }

            if (!is_numeric($needcoin)) {
                $this->error("请确认动画时长");
            }

            $words = $_POST['words'];
            if ($words == "") {
                $this->error("请填写进场话术");
            }


            $Car = M("car");
            $Car->create();
            $Car->addtime = time();
            $result = $Car->add();
            if ($result !== false) {
                $action = "添加坐骑：{$result}";
                setAdminLog($action);
                $this->resetcache();
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }
    }

    function edit() {
        $id = intval($_GET['id']);
        if ($id) {
            $car = M("car")->find($id);
            $this->assign('car', $car);
        } else {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function edit_post() {
        if (IS_POST) {
            $Car = M("car");
            $Car->create();
            $result = $Car->save();
            if ($result !== false) {
                $action = "修改坐骑：{$_POST['id']}";
                setAdminLog($action);
                $this->resetcache();
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        }
    }

    function user_index() {

        $Vip_u = M("users_vip");
        $count = $Vip_u->count();
        $page = $this->page($count, 20);
        $lists = $Vip_u
                ->order("endtime asc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        foreach ($lists as $k => $v) {
            $lists[$k]['userinfo'] = getUserInfo($v['uid']);
        }
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('type', $this->type);
        $this->display();
    }

    function user_del() {
        $id = intval($_GET['id']);
        if ($id) {
            $result = M("users_vip")->delete($id);
            if ($result) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function user_add() {
        $this->assign('type', $this->type);
        $this->display();
    }

    function do_user_add() {

        if (IS_POST) {
            $uid = $_POST['uid'];
            if ($uid == '') {
                $this->error('用户ID不能为空');
            }
            $isexist = M("users")->field("id")->where("id={$uid}")->find();
            if (!$isexist) {
                $this->error('该用户不存在');
            }

            $Vip_u = M("users_vip");
            $isexist2 = $Vip_u->field("id")->where("uid={$uid}")->find();
            if ($isexist2) {
                $this->error('该用户已购买过会员');
            }

            $Vip_u->create();
            $Vip_u->addtime = time();
            $Vip_u->endtime = strtotime($_POST['endtime']);
            $result = $Vip_u->add();
            if ($result !== false) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }
    }

    function user_edit() {

        $id = intval($_GET['id']);
        if ($id) {
            $data = M("users_vip")->where("id={$id}")->find();
            $data['userinfo'] = getUserInfo($data['uid']);
            $this->assign('data', $data);
            $this->assign('type', $this->type);
        } else {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function do_user_edit() {
        if (IS_POST) {
            $Vip_u = M("users_vip");
            $Vip_u->create();
            $Vip_u->endtime = strtotime($_POST['endtime']);
            $result = $Vip_u->save();
            if ($result !== false) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        }
    }

    function resetcache() {
        $key = 'carinfo';

        $car_list = M("car")->order("orderno asc")->select();
        if ($car_list) {
            setcaches($key, $car_list);
        }
        return 1;
    }
    //商城消息广播通知
    public function limitsxi(){
        //查询
        $data = D('duihuan_tong')->order('sort asc')->select();
        $this->assign('lists',$data);
        $this->display();
    }
    //商城轮播广播通知添加
    public function adds(){
      //判断请求类型
        if(IS_POST){
          //添加操作
          $parem = I('post.');
          if(empty($parem['content'])){
             $this->error('参数为空');
          }
          if($parem['status'] == ''){
            $this->error('状态为空');
          }
          $mod = M('duihuan_tong');
          $mod->sort = $parem['sort'];
          $mod->content = $parem['content'];
          $mod->status = $parem['status'];
          $data = $mod->add();
         // $data  = D('duihuan_tong')->create($parem);
          if(!$data){
             $this->error('添加失败');
          }
          $this->success('添加成功',U('Goods/limitsxi'));
        }
        $this->display();
    }
    //商品轮播广播通知删除
    public function  limitsdel(){
       $parem = I('get.'); 
       if(empty($parem['id'])){
         $this->error('参数id为空');
       }
       //删除操作
       $data = M('duihuan_tong')->delete($parem['id']);
       if(!$data){
          $this->error('添加失败');
       }
       $this->success('添加成功');
    }
    //商品轮播广播通知修改
    public function limitedit(){
        $parem = I('get.'); 
        if(empty($parem['id'])){
         $this->error('参数id为空');
        }
        //查询
        $find = M('duihuan_tong')->find($parem['id']);
        if(!$find){
           $this->error('参数有误');
        }
        $this->assign('data',$find);
        $this->display();
    }
    //商品轮播广播通知修改
    public function limit_post(){
        $parem = I('post.');
        if(empty($parem['id'])){
           $this->error('参数id为空');
        }
        if(empty($parem['content'])){
             $this->error('参数为空');
        }
        if($parem['status'] == ''){
            $this->error('状态为空');
          
        }
        $data = M('duihuan_tong');
        $data->create();
        $da = $data->save();
        if($da){
          $this->success('修改成功',U('Goods/limitsxi')); 
        }
        $this->error('修改失败');

    } 
    //商品警告
    public  function revoms(){
         //查询
        $data = D('inform')->select();
        $this->assign('lists',$data);
        $this->display(); 
    }
    //添加
     public function  revomsadd(){
      //判断请求类型
        if(IS_POST){
          //添加操作
          $parem = I('post.');
          if(empty($parem['content'])){
             $this->error('参数为空');
          }
          if($parem['status'] == ''){
            $this->error('状态为空');
          }
          $mod = M('inform');
         // $mod->sort = $parem['sort'];
          $mod->content = $parem['content'];
          $mod->status = $parem['status'];
          $data = $mod->add();
         // $data  = D('duihuan_tong')->create($parem);
          if(!$data){
             $this->error('添加失败');
          }
          $this->success('添加成功',U('Goods/revoms'));
        }
        $this->display();
    }
    //修改
     public function  revomsdit(){
        $parem = I('get.'); 
        if(empty($parem['id'])){
         $this->error('参数id为空');
        }
        //查询
        $find = M('inform')->find($parem['id']);
        if(!$find){
           $this->error('参数有误');
        }
        $this->assign('data',$find);
        $this->display();
    }
    //修改 post
    public function revoms_post(){
        $parem = I('post.');
        if(empty($parem['id'])){
           $this->error('参数id为空');
        }
        if(empty($parem['content'])){
             $this->error('参数为空');
        }
        if($parem['status'] == ''){
            $this->error('状态为空');
          
        }
        $data = M('inform');
        $data->create();
        $da = $data->save();
        if($da){
          $this->success('修改成功',U('Goods/revoms')); 
        }
        $this->error('修改失败');

    } 
    //删除
     public function  revomsdel(){
       $parem = I('get.'); 
       if(empty($parem['id'])){
         $this->error('参数id为空');
       }
       //删除操作
       $data = M('inform')->delete($parem['id']);
       if(!$data){
          $this->error('添加失败');
       }
       $this->success('添加成功');
    }
    
}
