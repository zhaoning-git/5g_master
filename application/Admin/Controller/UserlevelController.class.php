<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

//会员等级
class UserlevelController extends AdminbaseController {

    //会员等级列表
    public function index() {
        $count = M('UserLevel')->count();
        $page = $this->page($count, 20);
        $lists = M('UserLevel')
                ->order("id asc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        
        if(!empty($lists)){
            foreach ($lists as &$value){
                $value['privnum'] = M('UserPriv')->where(array('level_id'=>$value['id']))->count();
            }
        }
        
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function setLevel() {
        if (IS_POST) {
            $data = I('post.');
            $result = D('UserLevel')->setLevel($data);
            if ($result) {
                $action = "添加/编辑会员等级：{$result}";
                setAdminLog($action);
                $this->success('添加/编辑成功');
            } else {
                $this->error(D('UserLevel')->getError());
            }
        } else {
            $id = I('get.id', '', 'intval');
            $info = M('UserLevel')->where(array('id' => $id))->find();
            $this->assign('info', $info);
            $this->display();
        }
    }

    //添加特权
    public function addUserpriv() {
        if (IS_POST) {
            $data = I('post.');
            
            //开启事务
            M()->startTrans();
            
            $map['level_id'] = $data['id'];
            M('UserPriv')->where($map)->delete();
            
            if(!empty($data['priv'])){
                foreach ($data['priv'] as $priv_id){
                    $info = M('UserPriv')->where(array('id' => $priv_id))->field('id, priv_title,value')->find();
                    if(empty($info)){
                        $this->error('参数错误!');
                    }
                    $Ins['level_id'] = $data['id'];
                    $Ins['priv_id'] = $priv_id;
                    $Ins['priv_title'] = $info['priv_title'];
                    
                    if(!empty($data['value'][$priv_id])){
                        $Ins['value'] = $data['value'][$priv_id];
                    }else{
                        $Ins['value'] = 0;
                    }
                    
                    if(empty($Ins['value']) && $info['value']){
                        $this->error('请设置'.$info['priv_title'].'的值!');
                    }
                    
                    M('UserPriv')->add($Ins);
                    unset($info,$Ins);
                }
                //提交事务
                M()->commit();
                $this->success('添加成功', U('Userlevel/index'));
            }
            //提交事务
            M()->commit();
            $this->error('未添加任何特权!');
            
        } 
        
        else {
            $id = I('get.id', 0, 'intval');
            if (!$id) {
                $this->error('参数错误!');
            }

            $info = M('UserLevel')->where(array('id' => $id))->find();
            $priv = M('UserPriv')->where(array('level_id' => 0))->select();

            $this->assign('info', $info);
            $this->assign('priv', $priv);
        }
        $this->display();
    }

    //查看特权
    public function Userpriv(){
        $id = I('get.id', 0, 'intval');
        
        $info = M('UserLevel')->where(array('id' => $id))->find();
        
        $map['status'] = 1;
        $map['level_id'] = $id;
        $UserPriv = M('UserPriv')->where($map)->field('priv_id, value')->select();
        if(!empty($UserPriv)){
            foreach ($UserPriv as $val){
                $priv_list[$val['priv_id']] = $val;
            }
        }
        
        $priv = M('UserPriv')->where(array('level_id' => 0))->select();
        foreach ($priv as &$value){
            if($priv_list[$value['id']]){
                $value['checked'] = 'checked';
            }else{
                $value['checked'] = '';
            }
            
            $value['values'] = $priv_list[$value['id']]['value'];
            
        }
        
        
        $this->assign('info', $info);
        $this->assign('priv', $priv);
        $this->assign('list', $list);
        $this->display('addUserpriv');
    }
    
}
