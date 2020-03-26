<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

/**
 * Class FankuiController
 * @package Admin\Controller
 * 反馈
 */
class FankuiController extends AdminbaseController{

    public function index(){
        $club_model = M('clubs');
        $count = $club_model->count();
        $page = $this->page($count, 20);

        $prefix = C('DB_PREFIX');
        $data = M('user_fankui f')
            ->join("{$prefix}users u on u.id = f.uid")
            ->field('f.id,u.user_nicename,f.uid,f.content,f.mobile,f.create_time,f.status')
            ->order('status desc','create_time desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign("data", $data);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    //添加反馈
    function add(){
        $this->display();
    }

    function add_do(){
        if(IS_POST)
        {
            $data  =  I('post.');
            $data['create_time'] = time();

            if(empty($data['uid'])){
                $this->error = '请填写反馈人哦';
                return false;
            }

            if(empty($data['content']) || empty($data['mobile'])){
                $this->error = '必要信息未填写';
                return false;
            }

            if(Verify_Phone($data['mobile']) == false){
                $this->error = '手机号格式错误';
                return false;
            };

            $res = M('user_fankui')->add($data);
            if($res){
                $this->success('提交成功');
            }else{
                $this->error(D('Clubs')->getError());
            }
        }
    }

    //处理
    function edit(){
        $prefix = C('DB_PREFIX');
        $id=intval($_GET['id']);
        $info = M('user_fankui f')
            ->join("{$prefix}users u on u.id = f.uid")
            ->field('f.id,u.user_nicename,f.uid,f.content,f.mobile,f.create_time,f.status')
            ->where(array('f.id'=>$id))
            ->find();
        $this->assign('info', $info);
        $this->display();
    }

    function edit_do(){
        if(IS_POST)
        {
            $model = M("user_fankui");
            $data = $model->create();
            $result = $model->where(array('id'=>$data['id']))->save(array('status'=>$data['status']));

            if($result!==false)
            {
                $action="修改反馈信息状态：{$data['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }
        }
    }

    //搜索
    function goSearch(){
        $name = I('name');
        $prefix= C("DB_PREFIX");
        $info = M('user_fankui f')
            ->join("{$prefix}users u on u.id = f.uid")
            ->field('f.id,u.user_nicename,f.uid,f.content,f.mobile,f.status')
            ->where(array('u.user_nicename'=>['like',"%$name%"]))
            ->select();
        $this->ajaxReturn(array('data'=>$info));
    }

}
