<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//球队
class ClassifyController extends AdminbaseController{
   //球队列表
   function classifyshow(){
        $whereData=[];
	 	$data=I('get.');
      
	 	$query = http_build_query($data);
	    $whereData['page']=!empty($data['page'])? $data['page'] : 1;
		$whereData['size']=!empty($data['size'])? $data['page'] :C('branches');
		
		//获取总条数
        $totl = D('Ball_team')
              ->field('cmf_ball_team.name,types.name as typesname,world_rank,count_money,number,create_time,cmf_ball_team.id')
              ->join('cmf_club_type as types ON cmf_ball_team.event_id = types.id')
              ->where('softdel=1')
               ->count();
       
        //总数转换成多少页
        $pageTo=ceil($totl/C('branches'));
        $from=($whereData['page']-1)*$whereData['size'];
      
        $datas = M('Ball_team')
              ->field('cmf_ball_team.name,types.name as typesname,world_rank,count_money,number,create_time,cmf_ball_team.id')
              ->join('cmf_club_type as types ON cmf_ball_team.event_id = types.id')
              ->limit($from,$whereData['size'])
              ->where('softdel=1')
              ->select();
        $this->assign([
        	'data'=>$datas,
        	'pageTo'=>$pageTo,
			'dang' =>$whereData['page'],
			'query'=> $query,

        ]);
        	
        $this->display();

   }
   //球队编辑
   function edit(){
   	 if(IS_POST){
         $parem = I('post.');
         $User = M("Ball_team"); // 实例化User对象
         // 根据表单提交的POST数据创建数据对象
         $User->create();
         $data = $User->save(); // 根据条件保存修改的数据
         if($data){

             $this->success('修改成功');
         }
          $this->error('修改失败');
   	 }
   	 $parem = I('get.');
   	 //查询 球队
   	 $where['id'] = $parem['id'];
   	 $data = D('Ball_team')->where($where)->find();
   	 //查询 类别
   	 $classify = D('Club_type')->where('status=0')->select();
     $this->assign("data",$data);
     $this->assign("classify",$classify);
     $this->display();

   	 
   }
   //球队删除
   function del(){

     $id=intval($_GET['id']);
     if(!$id){

         $this->error('数据传入失败！');
     }
     $where['id'] = $id;
     $up['softdel'] = 0;
     $data = D('Ball_team')->where($where)->save($up);
     if($data){
       $this->success('删除成功');
     }
       $this->error('删除失败');


   }
   //球队添加
   function add(){
   	 if(IS_POST){
       $parem = I('post.');
       $parem['create_time'] = date('Y-m-d H:i:s');

       $User = M('Ball_team');
        if($User->create()){
           $result = $User->add($parem); // 写入数据到数据库 
          if($result){
            // 如果主键是自动增长型 成功后返回值就是最新插入的值
            $this->success('添加成功');
          }
            $this->error('添加失败');
        }

   	 }
   	 //查询分类
   	 $classify = D('Club_type')->select();
   	 $this->assign('classify',$classify);
     $this->display();

   }
   
}