<?php
/**
 *俱乐部发帖
 */
namespace Cliapi\Controller;

use Think\Controller;
use Think\Think;

class TopicController extends MemberController {

    function _initialize(){
        parent::_initialize();
    }

    /**
     * 俱乐部发帖
     */
    function sendTopic()
    {
        $data = I('post.');

        $data['uid'] = $this->uid;

        if(!empty($data['type'])){
            if($data['type'] != 1 && $data['type'] != 3){
                $this->ajaxRet(array('status'=>0,'info'=>'您不能发其他类型的帖子哦'));
            }
        }

        if(empty($data['cid'])){
            $this->ajaxRet(array('status'=>0,'info'=>'需要进入俱乐部才能发帖哦'));
        }
        $data['is_club'] = $data['cid'];
        $data['index_mol'] = 4;

        if (D('Show')->Insert($data) === true) {
            $data['img_path'] = getImgVideo($data['data']);
            unset($data['_uid']);
            unset($data['_sign']);
            unset($data['is_club']);
            $this->ajaxRet(array('status' => 1, 'info' => '发表成功','data'=>$data));
        } else {
            $this->ajaxRet(array('info' => D('Show')->getError()));
        }
    }

    //发帖球场分布
    function qiuChang(){
        $data = I('post.data');
        $asd = json_decode($data);
        foreach ($asd as $k=>&$v){
            $addData = [
                'zuobiao'=>$k,
                'mem_id'=>$v,
                'uid'=>$this->uid,
                'create_time'=>time()
            ];
            $res = M('topic_qiuchang')->add($addData);

            $prefix = C('DB_PREFIX');
            $name = M('ball_mem m')
                ->join("left join {$prefix}picture p on p.id = m.headimg")
                ->field('m.name,m.headimg,p.url')
                ->where(array('m.id'=>$addData['mem_id']))
                ->select();
            foreach ($name as $key=>&$value){
                $addData['mem_name'] = $value['name'];
                $addData['headimg'] = getImgVideo($value['headimg']);
            }

            if($res){
                $this->ajaxRet(array('status' => 1, 'info' => 'success!!','data'=>$addData));
            }
        }
    }

    /**
     *俱乐部发帖列表
     */
    function topicList(){
        $cid = I('c_id');
        if(empty($cid)){
            $this->ajaxRet(array('status' => 0, 'info' => '请选择俱乐部进行查看'));
        }
        $topicList = M('show')
            ->where(array('is_club'=>$cid))
            ->order('is_top desc,addtime desc')
            ->select();
        foreach ($topicList as $k=>&$v){
            $v['data_url'] = getImgVideo($v['data']);
        }
        if($topicList){
            $data['_list'] = $topicList;
            $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$data));
        }else{
            $this->ajaxRet(array('status' => 0, 'info' => '还没有人在该俱乐部下发表帖子哦'));
        }
    }

    /**
     * 帖子详情
     */
    function topicDetail(){
        $id = I('id');          //帖子id
        $detail = M('show')->where(array('id'=>$id))->find();
        if($detail['status']==1){
            $this->ajaxRet(array('status'=>1,'info'=>'','data'=>$detail));
        }else {
            $this->ajaxRet(array('status'=>0,'info'=>'该帖子已被删除或禁用'));
        }
    }

}