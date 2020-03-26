<?php
    namespace Cliapi\Controller;

    use Think\Controller;
    
    class BrandcooperationController extends ApiController {
        
        public function Brandcooperation() {
            $name = $_REQUEST['name'];
            $id_number = $_REQUEST['id_number'];
      
            $this->brand_model = D("Common/Brandcooperation");
            
            $flag = 1;
            if('' == $name || '' == $id_number)
            {
                //echo -200;  //身份证号或姓名为空
                $flag = 0;
                $return_arr['success'] = false;
                $return_arr['status'] = -200;
                $return_arr['message'] = '身份证号码或姓名为空';
            }
            
            if(1 == $flag)
            {
                //存储图盘
                $time = getdate();
                $file = $_REQUEST['file'];
             
                $data['business_license'] = '';
                $data['organization_code'] = '';
                $data['identity_card'] = '';

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
                    else
                    {
                        $return_arr['success'] = false;
                        $return_arr['status'] = -201;
                        if(0 == $i)
                        {
                            $return_arr['message'] .= '营业执照,';    
                        }
                        if(1 == $i)
                        {
                            $return_arr['message'] .= '组织机构代码,';
                        } 
                        if(2 == $i)
                        {
                            $return_arr['message'] .= '法人身份证,';
                        }  
                    }
                }
                
                $data['name'] = $name;
                $data['id_number'] = $id_number;
                $data['add_time'] = $time[0];
                $data['update_time'] = 0;
                
                $this->brand_model->add($data);
                
                if(false !== $return_arr['success'])
                {
                    $return_arr = array(
                        "success" => true,
                        "status" => 1,
                        "message" => "提交成功"
                    );
                }
                else
                {
                    $return_arr['message'] = substr($return_arr['message'], 0, (strlen($return_arr['message'])-1)) . "上传失败";
                }
            }
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            
            exit;
            
            
            
        }
    }
?>