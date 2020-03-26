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

class LibraryTeamController extends AdminbaseController{

    //列表
    public function index()
    {
        $whereData=[];
        $data=I('get.');

        $num = C('branches');
        $num = 10;
          
        $query = http_build_query($data);
        $whereData['page']=!empty($data['page'])? $data['page'] : 1;
        $whereData['size']=!empty($data['size'])? $data['size'] :$num;

        if(IS_POST){
            if('' != $_POST['keyword']) $where = "nameChs like '%" . $_POST['keyword'] . "%'";
        }
        if(isset($data['keyword'])) $where = "nameChs like '%" . $data['keyword'] . "%'";
        //if(!isset($where)) unset($where);
            
        //获取总条数
        $totl = D('library_team')
            ->field('id,teamid,nameChs,areaCn,is_hot,logo')
            ->where($where)
            ->count();     
           
        //总数转换成多少页
        $pageTo=ceil($totl/$num);
        $from=($whereData['page']-1)*$whereData['size'];
          
        $datas = M('library_team')
            ->field('id,teamid,nameChs,areaCn,is_hot,logo')
            ->where($where)
            ->limit($from,$whereData['size'])
            ->select();
        $this->assign([
            'data'=>$datas,
            'pageTo'=>$pageTo,
            'dang' =>$whereData['page'],
            'query'=> $query,

        ]);

        $this->assign('keyword', $_REQUEST['keyword']);
        $this->display();
    }

      //编辑
      function edit(){
        $time = getdate();
        if(IS_POST){
             $parem = I('post.');
             $library_team = M("library_team"); 
             $file = $_FILES['file'];

             if(isset($file['name']))
            {
                $type = explode('/', $file['type']);
                $dir = './data/upload/Picture/libraryteam/';
                if (!is_writable($dir)){
                    chmod($dir, 0777,true);
                }
                        
                $result = move_uploaded_file($file['tmp_name'], $dir . md5($time[0] . $file['name']) . '.' . $type[1]);
                            
                $parem['logo'] = AddHttp(substr($dir, 1, (strlen($dir))) . md5($time[0] . $file['name']) . '.' . $type[1]);
            }
            
             $parem['uptime'] = $time[0];
             unset($parem['file']);
//

            $where['id'] = $parem['id'];
             // 根据表单提交的POST数据创建数据对象
             $library_team->create();
             $data = $library_team->where($where)->save($parem); // 根据条件保存修改的数据
             //print_r($library_team->_sql());exit;

             //$library_team->find(1);
             //echo $library_team->getLastSql();

             if($data){

                 $this->success('修改成功');
             }
              $this->error('修改失败');
        }
        $parem = I('get.');
        //查询 
        $where['id'] = $parem['id'];
        $data = D('library_team')->where($where)->find();

     $this->assign("data",$data);
     //$this->assign("classify",$classify);
     $this->display();

        
   }

   
}