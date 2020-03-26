<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//签到
class SigninconfigController extends AdminbaseController{
    
    public function index(){
        $count = M('SigninConfig')->count();
        $page = $this->page($count, 20);
        $lists = M('SigninConfig')
                ->order("day asc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }
    
    public function setConfig(){
        if(IS_POST){
            $data = I('post.');
            $result = D('SigninConfig')->setConfig($data);
            if($result){
                $action = "添加/编辑签到奖励：{$result}";
                setAdminLog($action);
                $this->success('添加/编辑成功');
            }else{
                $this->error(D('SigninConfig')->getError());
            }
        }else{
            $id = I('get.id', '', 'intval');
            $info = M('SigninConfig')->where(array('id'=>$id))->find();
            $this->assign('info', $info);
            $this->display();
        }
    }
    
    
}
