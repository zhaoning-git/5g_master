<?php
/**
 * 竞猜
 */
namespace Cliapi\Controller;

use Think\Controller;

class JingcaiController extends MemberController {

    function _initialize(){
        parent::_initialize();
    }

    //竞猜列表
    function index(){
        $type = I('type', 0, 'intval');
        if(!empty($type)){
            $map['type'] = $type;
        }
        
        $uid = I('uid', 0, 'intval');
        if(!empty($uid)){
            $map['uid'] = $uid;
        }
        
        $_list = $this->lists('Jingcai', $map, 'addtime DESC');
        
        if(!empty($_list)){
            foreach ($_list as $value){
                $list[] = D('Jingcai')->setJingcai($value);
            }
        }
        
        
        $data['_list'] = $list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }
    
    //添加竞猜
    function addJingcai(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        if(D('Jingcai')->addJingcai($data)){
            $this->ajaxRet(1,'成功');
        }else{
            $this->ajaxRet(0, '失败:'.D('Jingcai')->getError());
        }
    }

    //参与竞猜
    function inJingcai(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        if(D('Jingcai')->inJingcai($data)){
            $this->ajaxRet(1,'成功');
        }else{
            $this->ajaxRet(0, '失败:'.D('Jingcai')->getError());
        }
    }



    //获取比赛
    function getMatch(){
        $list = D('Jingcai')->getMatch();
        $this->ajaxRet(1,'成功', $list['match']);
    }
    
}
