<?php



/**

 * 订单管理

 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class OrderController extends AdminbaseController {

    //订单列表

	 function indexx(){

         $show_model = M('duihuan_order');
         $count = $show_model->count();
         $page = $this->page($count, 20);

	     $prefix = C('DB_PREFIX');

	     $info = M('duihuan_order o')

             ->join("{$prefix}users u on o.user_id = u.id")

             ->field('o.id,o.order_no,o.jinbi,o.yinbi,o.status,u.user_nicename')

             ->where(array('is_del'=>1))

             ->limit($page->firstRow . ',' . $page->listRows)

             ->select();



	     $this->assign('info',$info);

         $this->assign('page',$page->show('Admin'));
         
	     $this->display();

	 }



	 //订单详情

    function orderDetail(){

        $prefix = C('DB_PREFIX');

	     $order_id = $_GET['order_id'];

	     $data = M('order_itme i')

             ->join("{$prefix}duihuan_order o on o.id = i.order_id")

             ->field('i.id,i.order_id,o.order_no,i.jinbi,i.yinbi,i.gui_json,i.img,i.shopname,i.goods_id,o.user_id,o.address_id,o.is_beizhu')

             ->where(array('i.order_id'=>$order_id))

             ->select();



	     foreach ($data as $k=>&$v){

	         $v['gui_json'] = json_decode($v['gui_json'],true);

	         $eva_status = M('duihuan_eva')

                 ->where(array('order_no'=>$v['order_no'],'uid'=>$v['user_id'],'goods_id'=>$v['goods_id']))

                 ->find();

             if(!empty($eva_status)){

                 $v['eva_status'] = 1;

             }else{

                 $v['eva_status'] = 0;

             }



             //收货地址

             $address = M('duihuan_address')->where(array('id'=>$v['address_id']))->find();

             if(empty($v['is_beizhu'])){

                 $address['beizhu'] = '无备注';

             }else{

                 $address['beizhu'] = $v['is_beizhu'];

             }







         }



        $this->assign('data',$data);

        $this->assign('address',$address);

        $this->display();

    }



    //删除订单

    function delOrder(){

	     $order_no = $_GET;

         //判断是否付款  若未付款可删除订单

        $is_fu = M('duihuan_order')->where(array('order_no'=>$order_no))->find();

        echo "<pre>";

        var_dump($is_fu);

        echo "</pre>";

        if($is_fu['status'] == 1){        //未付款

            $res = M('duihuan_order')->where(array('order_no'=>$order_no))->save(array('is_del'=>2));

            if($res){

                $action="删除订单：{$is_fu['id']}";

                setAdminLog($action);

                $this->success('删除成功');

            }

        }else{

            $this->error = '用户已付款,此订单不能删除';

        }

    }



    //添加备注信息

    function addBeizhu(){

	     $order_no = $_POST['oid'];

	     $content = $_POST['content'];

	     if(empty($content)){

	         $this->error = '备注消息不能为空';

         }

	     $info = M('duihuan_order')->where(array('order_no'=>$order_no))->save(array('is_beizhu'=>$content));

         $this->ajaxReturn(array('status'=>1,'data'=>$info));

    }



}