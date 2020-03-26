<?php

/**
 * Date: 17-08-022
 * Time: 下午5:04
 */
namespace Api\Controller;

use Think\Controller;

class FlowerController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }
    
    //鲜花排行榜
    function FlowerRank(){
        $type = I('post.type','');
        $supplier_type = I('post.supplier_type', 0, 'intval');
        
        
        $_list = D('Flowerlog')->FlowerRankingList($this->uid, $type, $supplier_type);
        if($_list !== false){
            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$_list));
        }else{
            $this->ajaxRet(array('info'=>D('Flowerlog')->getError()));
        }
        
    }
    
    //玩咖榜
    function playKa(){
        $type = I('type', 0, 'intval');
        
        //红人榜
        if($type){
            $model = M('Flowerlog')->group('to_uid');
            $list = $this->lists($model, array(), 'num DESC', array(), 'to_uid as uid,sum(number) as num');
        }
        
        //土豪榜
        else{
            $map['change_type'] = 5;
            $model = M('AccountLog')->group('uid');
            $list = $this->lists($model, $map, 'num DESC', array(), 'uid, sum(gain_number) as num');
        }
        
        if(!empty($list)){
            foreach ($list as &$value){
                $value['userInfo'] = User($value['uid']);
            }
        }
        
        $data['_list'] = $list;
        $data['_totalPages'] = $this->_totalPages;//总页数
        $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$data));
        
    }
    
}