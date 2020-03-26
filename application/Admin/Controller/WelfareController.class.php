<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//福利体系
class WelfareController extends AdminbaseController{
    
    public $WName;
    
    function _initialize() {
        parent::_initialize();
        $this->WName = D('Lottery')->WelfareName;
    }


    //优惠券管理
    public function index(){
        $count = M('Coupon')->count();
        $page = $this->page($count, 20);
        $lists = M('Coupon')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    //添加&&管理
    public function addConfig(){
        if(IS_POST){
            $data = I('post.');
            $result = D('Coupon')->setCoupon($data);
            if($result){
                $action = "添加/编辑优惠券规则：{$result}";
                setAdminLog($action);
                $this->success('添加/编辑成功');
            }else{
                $this->error(D('Coupon')->getError());
            }
        }else{
            $id = I('get.id', '', 'intval');
            $info = M('Coupon')->where(array('id'=>$id))->find();
            $this->assign('info', $info);
            $this->display();
        }
    }
    
    //金币立减券
    public function CouponGold(){
        $map['status'] = array('EGT', 0);
        $count = M('CouponGold')->where($map)->count();
        $page = $this->page($count, 20);
        $lists = M('CouponGold')
                ->where($map)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        $this->assign('lists', D('Coupon')->setlist($lists));
        $this->assign("page", $page->show('Admin'));
        $this->display('CouponGold/index');
    }
    
    //竞猜加奖券
    public function CouponJingcai(){
        $map['status'] = array('EGT', 0);
        $count = M('CouponJingcai')->where($map)->count();
        $page = $this->page($count, 20);
        $lists = M('CouponJingcai')
                ->where($map)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        $this->assign('lists', D('Coupon')->setlist($lists));
        $this->assign("page", $page->show('Admin'));
        $this->display('CouponJingcai/index');
    }

    //万能合并券
    public function CouponMerge(){
        $map['status'] = array('EGT', 0);
        $count = M('CouponMerge')->where($map)->count();
        $page = $this->page($count, 20);
        $lists = M('CouponMerge')
                ->where($map)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        $this->assign('lists', D('Coupon')->setlist($lists));
        $this->assign("page", $page->show('Admin'));
        $this->display('CouponMerge/index');
    }

    //万能延时券
    public function CouponDelay(){
        $map['status'] = array('EGT', 0);
        $count = M('CouponDelay')->where($map)->count();
        $page = $this->page($count, 20);
        $lists = M('CouponDelay')
                ->where($map)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        $this->assign('lists', D('Coupon')->setlist($lists));
        $this->assign("page", $page->show('Admin'));
        $this->display('CouponDelay/index');
    }

    //会员抵扣券
    public function CouponDikou(){
        $map['status'] = array('EGT', 0);
        $count = M('CouponDikou')->where($map)->count();
        $page = $this->page($count, 20);
        $lists = M('CouponDikou')
                ->where($map)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        $this->assign('lists', D('Coupon')->setlist($lists));
        $this->assign("page", $page->show('Admin'));
        $this->display('CouponDikou/index');
    }
    
    
    //奖品列表
    public function Welfareitem(){
        $Welfare = I('type');
        
        $count = M('Lottery')->where(array('welfare'=>$Welfare))->count();
        $page = $this->page($count, 20);
        $lists = M('Lottery')
                ->where(array('welfare'=>$Welfare))
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        
        if(!empty($lists)){
            foreach ($lists as &$value){
                $value['create_time_txt'] = date('Y-m-d', $value['create_time']);
            }
        }
        
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('title', $this->WName[$Welfare]);
        $this->assign('type', $Welfare);
        $this->display();
    }
    
    //添加奖品
    public function addLottery(){
        if(IS_POST){
            $data = I('post.');
            if(D('Lottery')->setLottery($data)){
                $this->success('添加/编辑成功', U('Welfare/Welfareitem', array('type'=>$data['welfare'])));
            }else{
                $this->error('失败:'.D('Lottery')->getError());
            }
        }else{
            $Welfare = I('type');
            $id = I('id', 0, 'intval');
            if($id){
                $info = M('Lottery')->where(array('id'=>$id))->find();
            }
            
            $map['status'] = 1;
            $map['is_shop'] = 0;
            $couponlist = M('Coupon')->where($map)->select();
            $this->assign('couponlist', $couponlist);
            $this->assign('title', $this->WName[$Welfare]);
            $this->assign('type', $Welfare);
            $this->assign('info', $info);
            $this->display();
        }
    }
    
    //中奖记录
    public function LotteryLog(){
        
    }
    
    //删除奖品
    public function del(){
        $id = I('get.id', 0, 'intval');
        $type = I('get.type');
        if(!$id){
            $this->error('参数错误!');
        }
        
        if(M('Lottery')->where(array('id'=>$id))->delete()){
            $this->success('删除成功!', U('Welfare/Welfareitem', array('type'=>$type)));
        }else{
            $this->error('失败!');
        }
        
        
    }
    
    
}
