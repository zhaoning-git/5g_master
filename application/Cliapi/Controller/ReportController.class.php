<?php
/**
 * Created by PhpStorm.
 * User: baozhi
 * Date: 2017/11/9
 * Time: 11:08
 */
namespace Api\Controller;

class ReportController extends MemberController
{
    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 获取举报分类列表
     */
    public function cate()
    {
        $list = M('report_cate')
            ->field('name,status')
            ->order('id desc')
            ->select();
        if (!$list){
            $list = array();
        }
        $this->ajaxRet(array('status' => 1,'info' =>'获取成功', 'data' => $list));
    }

    /**
     * 举报
     */
    public function submit()
    {
        $cate_id = I('cate_id',0,'intval');//举报分类
        $uid = I('uid',0,'intval');//被举报人
        $cate = M('report_cate')->where(array('id'=>$cate_id))->find();
        if (!$cate){
            $this->ajaxRet(array('info'=>'请重新选择举报内容!'));
        }
        $member = M('ucenter_member')->where(array('id'=>$uid))->find();
        if (!$member){
            $this->ajaxRet(array('info'=>'被举报用户或已删除不存在!'));
        }
        // 判断是否存在已举报记录
        $map['report_id'] = $this->uid;
        $map['uid'] = $uid;
        $report = M('report')->where($map)->find();
        if ($report){
            $this->ajaxRet(array('info'=>'请勿重复举报!'));
        }
        $data = array(
            'report_id' =>  $this->uid,
            'uid'       =>  $uid,
            'cate_id'   =>  $cate_id,
            'add_time'  =>  time(),
            'status'    =>  0
        );
        $id = M('report')->add($data);
        if ($id > 0){
            $this->ajaxRet(array('status'=>1,'info'=>'已提交举报!'));
        } else {
            $this->ajaxRet(array('info'=>'提交失败，请重试!'));
        }
    }
}