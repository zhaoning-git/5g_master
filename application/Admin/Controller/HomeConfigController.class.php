<?php
/*
 * @Author: your name
 * @Date: 2020-03-04 13:41:11
 * @LastEditTime: 2020-03-04 17:53:32
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \Controller\AdScorting.class.php
 */
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class HomeConfigController extends AdminbaseController{

    // 房间后台页面
    public function index()
    {
        $whereData=[];
        $data=I('get.');
          
        $query = http_build_query($data);
        $whereData['page']=!empty($data['page'])? $data['page'] : 1;
        $whereData['size']=!empty($data['size'])? $data['page'] :C('branches');
            
        //获取总条数
        $totl = D('roominfo')
            ->field('id,type,about_label,title,info,background,number,address,add_time,update_time')
            ->where('status=1')
            ->count();     
           
        //总数转换成多少页
        $pageTo=ceil($totl/C('branches'));
        $from=($whereData['page']-1)*$whereData['size'];
          
        $datas = M('roominfo')
            ->field('id,type,about_label,title,info,background,number,address,add_time,update_time')
            ->limit($from,$whereData['size'])
            ->where('status=1')
            ->select();
        $this->assign([
            'data'=>$datas,
            'pageTo'=>$pageTo,
            'dang' =>$whereData['page'],
            'query'=> $query,

        ]);



        $this->display();
    }

    //添加房间
    public function add()
    {
            $time = getdate();
            if(IS_POST){
               $parem = I('post.');
               $file = $_FILES['file'];

               $parem['add_time'] = $time[0];

                if(isset($file['name']))
                {
                    $type = explode('/', $file['type']);
                    $dir = './data/upload/Picture/roominfo/';
                    if (!is_writable($dir)){
                        chmod($dir, 0777,true);
                    }
                        
                    $result = move_uploaded_file($file['tmp_name'], $dir . md5($time[0] . $file['name']) . '.' . $type[1]);
                            
                    $parem['background'] = substr($dir, 1, (strlen($dir))) . md5($time[0] . $file['name']) . '.' . $type[1];
                }

               $Brandcooperation = M('roominfo');
                if($Brandcooperation->create()){
                   $result = $Brandcooperation->add($parem); // 写入数据到数据库 
                  if($result){
                    // 如果主键是自动增长型 成功后返回值就是最新插入的值
                    $this->success('添加成功');
                  }
                    $this->error('添加失败');
                }

            }

            $this->display();

    }

    //删除
    function del(){
        $time = getdate();
        $id=intval($_GET['id']);
        if(!$id){

            $this->error('数据传入失败！');
        }
        $where['id'] = $id;
        $up['status'] = 0;
        $up['update_time'] = $time[0];
        $data = D('roominfo')->where($where)->save($up);
        if($data){
          $this->success('删除成功');
        }
          $this->error('删除失败');


      }

      //编辑
      function edit(){
        $time = getdate();
        if(IS_POST){
             $parem = I('post.');
             $roominfo = M("roominfo"); 
             $file = $_FILES['file'];

             if(isset($file['name']))
            {
                $type = explode('/', $file['type']);
                $dir = './data/upload/Picture/roominfo/';
                if (!is_writable($dir)){
                    chmod($dir, 0777,true);
                }
                        
                $result = move_uploaded_file($file['tmp_name'], $dir . md5($time[0] . $file['name']) . '.' . $type[1]);
                            
                $parem['background'] = substr($dir, 1, (strlen($dir))) . md5($time[0] . $file['name']) . '.' . $type[1];
            }
            
             $parem['update_time'] = $time[0];
             unset($parem['file']);
             //print_r($parem);exit;
             // 根据表单提交的POST数据创建数据对象
             $roominfo->create();
             $data = $roominfo->save($parem); // 根据条件保存修改的数据
             if($data){

                 $this->success('修改成功');
             }
              $this->error('修改失败');
        }
        $parem = I('get.');
        //查询 
        $where['id'] = $parem['id'];
        $data = D('roominfo')->where($where)->find();

     $this->assign("data",$data);
     //$this->assign("classify",$classify);
     $this->display();

        
   }

    //更新房间信息
    public function set_home_info()
    {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];

        if ($title != "")
        {
            $data['title'] = $title;
        }
        if($content!="")
        {
            $data['content'] = $content;
        }
        if($background != "")
        {
            $data['background'] = $background;
        }
        
        $data['id'] = $id; //设置房间ID
        $list = M(""); 
        $list->save($data); 

        if($res != false)
        {
            $this->success("成功");
        } else {
            $this->error("失败");
        }
    }

    /**
     * 添加列表
     */
    public function add_list()
    {
        $list = $_POST['list'];
        $data = array(
            "name"=>$list
        );
        $res = $obj->insert($data);

        if ($res == 1)
        {
            $this->success("成功");
        }else {
            $this->error("失败");
        }
    }

   
}