<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//全身照认证
class ZhuboController extends AdminbaseController{

    public function index(){
        $prefix = C('DB_PREFIX');
        $page = $this->page(M('anchor_quanshen')->count(), 10);
        $data = M('anchor_quanshen q')
            ->join("{$prefix}picture p on q.pic = p.id")
            ->join("{$prefix}anchor_ruzhu r on q.uid = r.uid")
            ->field('q.id,r.name,p.url,q.status,q.create_time,q.pass_time')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->assign("data", $data);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    //审核
    public function ShenHe(){
        $id=intval($_GET['id']);
        $prefix = C('DB_PREFIX');
        $data = M('anchor_quanshen q')
            ->join("{$prefix}picture p on q.pic = p.id")
            ->join("{$prefix}anchor_ruzhu r on q.uid = r.uid")
            ->field('q.id,r.name,p.url,q.status')
            ->where(array('q.id'=>$id))
            ->find();

        $this->assign('data',$data);
        $this->display();
    }

    public function shenHeDo(){
        if(IS_POST)
        {
            $model=M("anchor_quanshen");
            $data = $model->create();
            $data['pass_time']=time();
            $result=$model->save($data);
            if($result!==false)
            {
                $action="修改主播信息：{$data['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }
        }
    }



}
