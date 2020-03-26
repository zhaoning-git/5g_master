<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//邀请奖励
class InvitelogController extends AdminbaseController{
    
    public function index(){
        $count = M('InviteLog')->count();
        $page = $this->page($count, 20);
        $lists = M('InviteLog')
                ->order("id asc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        
        if(!empty($lists)){
            foreach ($lists as &$value){
                $value['nickname'] = User($value['uid'], 'user_nicename');
                $value['up_time'] = date('Y-m-d H:s', $value['create_time']);
                
            }
        }
        
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->display();
        
    }
    
}