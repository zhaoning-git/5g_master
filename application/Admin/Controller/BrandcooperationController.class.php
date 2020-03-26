<?php
    namespace Admin\Controller;
    use Common\Controller\AdminbaseController;
    
    class BrandcooperationController extends AdminbaseController {
        
        public function Brandcooperationshow() {
      
            $whereData=[];
            $data=I('get.');
          
            $num = C('branches');
            $num = 10;
            
            $query = http_build_query($data);
            $whereData['page']=!empty($data['page'])? $data['page'] : 1;
            $whereData['size']=!empty($data['size'])? $data['size'] :$num;
            
            //获取总条数
            $totl = D('Brandcooperation')
                  ->field('name,id,id_number,business_license,organization_code,identity_card,add_time')
                  ->where('status=1')
                  ->count();
           
           
            //总数转换成多少页
            $pageTo=ceil($totl/$num);
            $from=($whereData['page']-1)*$whereData['size'];
          
            $datas = M('Brandcooperation')
                  ->field('name,id,id_number,business_license,organization_code,identity_card,add_time')
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
        
        //编辑
        function edit(){
            $time = getdate();
            if(IS_POST){
                 $parem = I('post.');
                 $Brandcooperation = M("Brandcooperation"); 

                if(!empty($_FILES['business_license']['tmp_name']))
                {
                    $business_license = $this->uploadImg($_FILES['business_license'],1);
                    if(5 < strlen($business_license)) $parem['business_license'] = $business_license;
                }
                else unset($parem['business_license']);

                if(!empty($_FILES['organization_code']['tmp_name']))
                {
                    $organization_code = $this->uploadImg($_FILES['organization_code'],1);
                    if(5 < strlen($organization_code)) $parem['organization_code'] = $organization_code;
                }
                else unset($parem['organization_code']);

                if(!empty($_FILES['identity_card']['tmp_name']))
                {
                    $identity_card = $this->uploadImg($_FILES['identity_card'],1);
                    if(5 < strlen($identity_card)) $parem['identity_card'] = $identity_card;
                }
                else unset($parem['identity_card']);

                
                 $parem['update_time'] = $time[0];
                 unset($parem['file']);
                 //print_r($parem);exit;
                 // 根据表单提交的POST数据创建数据对象
                 $Brandcooperation->create();
                 $data = $Brandcooperation->save($parem); // 根据条件保存修改的数据
                 if($data){

                     $this->success('修改成功');
                 }
                  $this->error('修改失败');
            }
            $parem = I('get.');
            //查询 
            $where['id'] = $parem['id'];
            $data = D('Brandcooperation')->where($where)->find();

         $this->assign("data",$data);
         //$this->assign("classify",$classify);
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
         $data = D('Brandcooperation')->where($where)->save($up);
         if($data){
           $this->success('删除成功');
         }
           $this->error('删除失败');


       }
       
       function add(){
            $time = getdate();
            if(IS_POST){
               $parem = I('post.');
               //$file = $_FILES['file'];

               $parem['add_time'] = $time[0];

               if(!empty($_FILES['business_license']['tmp_name']))
                {
                    $business_license = $this->uploadImg($_FILES['business_license'],1);
                    if(5 < strlen($business_license)) $parem['business_license'] = $business_license;
                }
                else unset($parem['business_license']);

                if(!empty($_FILES['organization_code']['tmp_name']))
                {
                    $organization_code = $this->uploadImg($_FILES['organization_code'],1);
                    if(5 < strlen($organization_code)) $parem['organization_code'] = $organization_code;
                }
                else unset($parem['organization_code']);

                if(!empty($_FILES['identity_card']['tmp_name']))
                {
                    $identity_card = $this->uploadImg($_FILES['identity_card'],1);
                    if(5 < strlen($identity_card)) $parem['identity_card'] = $identity_card;
                }
                else unset($parem['identity_card']);

                /*
               for($i=0;$i<3;$i++)
                {  
                    if(isset($file['name'][$i]))
                    {
                        $type = explode('/', $file['type'][$i]);
                        $dir = './data/upload/Picture/brandcooperation/';
                        if (!is_writable($dir)){
                            chmod($dir, 0777,true);
                        }
                        
                        $result = move_uploaded_file($file['tmp_name'][$i], $dir . md5($time[0] . $file['name'][$i]) . '.' . $type[1]);
                            
                        if(0 == $i) $parem['business_license'] = substr($dir, 1, (strlen($dir))) . md5($time[0] . $file['name'][$i]) . '.' . $type[1];
                        if(1 == $i) $parem['organization_code'] = substr($dir, 1, (strlen($dir))) . md5($time[0] . $file['name'][$i]) . '.' . $type[1];
                        if(2 == $i) $parem['identity_card'] = substr($dir, 1, (strlen($dir))) . md5($time[0] . $file['name'][$i]) . '.' . $type[1];

                    }
                }
                */

                $Brandcooperation = M('Brandcooperation');
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

       private function uploadImg($file, $urltype)
        {
                if(isset($file['name']))
                {
                    $type = explode('/', $file['type']);
                    $dir = './data/upload/Picture/libraryleague/';
                    if (!is_writable($dir)){
                        chmod($dir, 0777,true);
                    }
                            
                    $result = move_uploaded_file($file['tmp_name'], $dir . md5($time[0] . $file['name']) . '.' . $type[1]);
                    
                    if(!$result) return false;

                    $path = substr($dir, 1, (strlen($dir))) . md5($time[0] . $file['name']) . '.' . $type[1];
                    if(1 == $urltype) return AddHttp($path);
                    else return $path;
                }
                else return false;

                
        }
    }
?>