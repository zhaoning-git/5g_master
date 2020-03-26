<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//银币规则
class SilvercoinController extends AdminbaseController{
    public function index(){
        $count = M('SilverCoin')->count();
        $page = $this->page($count, 20);
        $lists = M('SilverCoin')
                ->order("id asc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }
    
    public function addConfig(){
        if(IS_POST){
            $data = I('post.');
            $result = D('SilverCoin')->setConfig($data);
            if($result){
                $action = "添加/编辑银币规则：{$result}";
                setAdminLog($action);
                $this->success('添加/编辑成功');
            }else{
                $this->error(D('SilverCoin')->getError());
            }
        }else{
            $id = I('get.id', '', 'intval');
            $info = M('SilverCoin')->where(array('id'=>$id))->find();
            $this->assign('info', $info);
            $this->display();
        }
        
    }
    
    public function log(){
        $this->display();
    }
}