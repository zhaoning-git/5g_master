<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//邀请奖励
class InviteconfigController extends AdminbaseController{
    
    public function index(){
        $count = M('InviteConfig')->count();
        $page = $this->page($count, 20);
        $lists = M('InviteConfig')
                ->order("level asc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        if(!empty($lists)){
            foreach ($lists as $key=>&$value){
                $value['end'] = $value['end_num'];
                
                if($value['level'] == 1){
                    $value['start'] = 1;
                }else{
                    $value['start'] = $lists[$key-1]['end_num']+1;
                }
                $value['usernum'] = $value['end'] - $value['start'] + 1;
                $value['allcoin'] = $value['usernum'] * $value['coin'];
            }
        }
        
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->display();
        
    }
    
    public function setConfig(){
        if(IS_POST){
            $data = I('post.');
            
            $result = D('InviteConfig')->addConfig($data);
            
            if($result){
                $action = "添加/编辑邀请奖励：{$result}";
                setAdminLog($action);
                $this->success('添加/编辑成功', U('Inviteconfig/index'));
            }else{
                $this->error(D('InviteConfig')->getError());
            }
        }else{
            $id = I('get.id', '', 'intval');
            $info = M('InviteConfig')->where(array('id'=>$id))->find();
            $this->assign('info', $info);
            $this->display();
        }
    }
    
    //删除
    public function delConfig(){
        $id = I('get.id', '', 'intval');
        if(!$id){
            $this->error('参数错误!');
        }
        
        $Info = M('InviteConfig')->where(array('id'=>$id))->find();
        if(empty($Info)){
            $this->error('奖励规则不存在!');
        }
        
        $MaxLevel = M('InviteConfig')->Max('level');
        if($Info['level'] != $MaxLevel){
            $this->error('请先删除等级最大的奖励规则!');
        }else{
            M('InviteConfig')->where(array('id'=>$Info['id']))->delete();
            $action = "删除邀请奖励规则：{$Info['id']}";
            setAdminLog($action);
            $this->success('删除成功', U('Inviteconfig/index'));
        }
        
        
    }
    
}
