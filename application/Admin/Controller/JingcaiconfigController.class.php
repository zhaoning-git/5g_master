<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

//竞猜奖励
class JingcaiconfigController extends AdminbaseController {

    public function index() {
        $lists = M('JingcaiConfig')->order('id DESC')->select();
        $this->assign('lists', $lists);
        $this->display();
    }

    //添加修改
    public function setConfig(){
        if(IS_POST){
            $data = I('post.');
            $id = intval($data['id']);
            
            if(empty($data['number'])){
                $this->error('请填写竞猜次数');
            }
            
            $data['coin'] = $data['coin']?:0;
            
            $map['number'] = $data['number'];
            $info = M('JingcaiConfig')->where($map)->find();
            if(!empty($info)){
                $id = $info['id'];
            }
            
            if($id){
                $save['coin'] = $data['coin'];
                $save['uptime'] = NOW_TIME;
                $result = M('JingcaiConfig')->where(array('id'=>$id))->save($save);
            }else{
                $result = M('JingcaiConfig')->add(array('number'=>$data['number'],'coin'=>$data['coin'],'addtime'=>NOW_TIME));
            }
            
            
            if($result){
                $action = "添加/编辑竞猜奖励：{$result}";
                setAdminLog($action);
                $this->success('添加/编辑成功', U('Jingcaiconfig/index'));
            }else{
                $this->error(M('JingcaiConfig')->getDbError());
            }
        }else{
            $id = I('get.id', '', 'intval');
            $info = M('JingcaiConfig')->where(array('id'=>$id))->find();
            $this->assign('info', $info);
            $this->display();
        }
        
    }
    
    //删除
    public function delConfig(){
        $id = I('get.id');
            if(!$id){
            $this->error('参数错误!');
        }
        
        $Info = M('JingcaiConfig')->where(array('id'=>$id))->find();
        if(empty($Info)){
            $this->error('奖励规则不存在!');
        }
    
        M('JingcaiConfig')->where(array('id'=>$Info['id']))->delete();
        $action = "删除竞猜奖励规则：{$Info['id']}";
        setAdminLog($action);
        $this->success('删除成功', U('Jingcaiconfig/index'));
    }
    
}
