<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//赛事
class ClubEventController extends AdminbaseController{

    public function index(){
        $club_model = M('event');
        $count = $club_model->count();
        $page = $this->page($count, 20);
        $data = $club_model
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign("data", $data);
        $this->display();
    }

    //添加赛事
    public function add_event(){
        $this->display();
    }

    public function add_event_do(){
        if(IS_POST)
        {
            $name=$_POST['name'];
            if(!empty($name))
            {
                $eventInfo=M("event")->where(array('ename'=>$name))->find();
                if($eventInfo)
                {
                    $this->error('该赛事已存在');
                }

                $data = [
                    'ename'=>$name
                ];
                $result=M('event')->add($data);
                $e_id = M()->getLastInsID();
                if($result!==false)
                {
                    $action="添加的赛事：{$e_id}";
                    setAdminLog($action);
                    $this->success('添加成功');
                }else
                {
                    $this->error('添加失败');
                }
            }
            else
            {
                $this->error('赛事名称不能为空');
            }
        }
    }

    //编辑
    public function edit_event(){
        $id=intval($_GET['id']);
        if($id){
            $eventInfo=M("event")->find($id);
            $this->assign('eventInfo', $eventInfo);
        }else {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function edit_events_do()
    {
        if(IS_POST)
        {
            $event=M("event");
            $data = $event->create();
            $result=$event->save();
            if($result!==false)
            {
                $action="修改赛事信息：{$data['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }
        }
    }

    //删除
    function del_event()
    {
        $id=intval($_GET['id']);
        if($id)
        {
            $info = M('event')
                ->join('cmf_clubs on cmf_club_event.id = cmf_clubs.event_id')
                ->where(array('event_id'=>$id))
                ->select();
            if(count($info) > 0){
                $this->error('赛事下还有俱乐部存在，不可删除');
            }else{
                $result=M("clubs")->where("id=".$id)->setField(array("status"=>1));
                if($result!==false)
                {
                    $action="删除赛事：{$id}";
                    setAdminLog($action);
                    $this->success('删除成功');
                }
                else
                {
                    $this->error('删除失败');
                }
            }

        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    //俱乐部加入的赛事列表
    function clubJoinEventList(){
        $joinInfo = M('club_event')
            ->join('cmf_event on cmf_event.id = cmf_club_event.event_id')
            ->join('cmf_clubs on cmf_clubs.id = cmf_club_event.club_id')
            ->select();
        $this->assign('joinInfo',$joinInfo);
        $this->display();
    }

    //修改俱乐部的赛事信息
    function edit_clubevent(){
        $id=intval($_GET['id']);
        if($id){
            $eventInfo=M("club_event")->where(array('club_id'=> $id))->find();
            $event_name = M('event')->field('ename')->where(array('id'=>$eventInfo['event_id']))->find();
            $club_name = M('clubs')->field('name')->where(array('id'=>$eventInfo['club_id']))->find();

            $this->assign('event_name', $event_name);
            $this->assign('club_name', $club_name);
            $this->assign('eventInfo', $eventInfo);
        }else {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function edit_clubevents_do()
    {
        if(IS_POST)
        {
            $event=M("club_event");
            $data = $event->create();
            $result=$event->save();
            if($result!==false)
            {
                $action="修改赛事信息：{$data['id']}";
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
