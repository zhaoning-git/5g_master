<?php
/*
 * @Author: your name
 * @Date: 2020-03-04 13:41:11
 * @LastEditTime: 2020-03-04 17:53:32
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \Controller\AdScorting.class.php
 */
namespace Cliapi\Controller;

use Think\Controller;

class HomeConfigController extends ApiController {

    // 房间后台页面
    public function roominfo()
    {
        $where['status'] = 1;
        $id=intval($_REQUEST['id']);
        $type=intval($_REQUEST['type']);
        $about_label=intval($_REQUEST['about_label']);
        if(isset($id) && 0 != $id) {
            $where['id'] = $id;
        }
        if(isset($type) && 0 != $type) {
            $where['type'] = $type;
        }
        if(isset($type) && isset($about_label) && 0 != $type && 0 != $about_label) {
            $where['about_label'] = $about_label;
        }
       
        $datas = M('roominfo')
            ->field('id,type,about_label,title,info,background,number,address,add_time,update_time')
            ->where($where)
            ->select();

        if(!$datas)
        {
            $arr = array(
                "status" => 0,
                "data" => array("_list"=>null),
                "message" => "直播房间信息不存在"
            );

        }
        else{

            foreach($datas as $k => &$v)
            {
                $v['background'] = AddHttp($v['background']);
            }


            $arr = array(
                "status" => 1,
                "data" => array("_list"=>$datas),
                "message" => "提取直播房间信息成功"
            );
        }

        echo json_encode($arr, JSON_UNESCAPED_UNICODE);

        exit;
    }

    
   
}