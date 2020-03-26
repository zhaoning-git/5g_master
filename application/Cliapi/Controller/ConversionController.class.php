<?php

/**

 * 商品兑换

 */

namespace Cliapi\Controller;



use Think\Controller;



use Common\Model\Coupon;

header('Access-Control-Allow-Origin: *');//*号代表所有域名
header('Access-Control-Allow-Credentials:true');
header("Access-Control-Allow-Headers: access-token,access_token,Content-Type");

class ConversionController extends MemberController

{

    //加入购物车 商品数量 商店id  属性id 以逗号分割传

    public function trolley(){

      $parem = I('post.');

      if(empty($parem['goods_sum'])){

        $this->ajaxRet(array('status' => 0, 'info' => '商品数量为空','data'=>''));

      }

      if(empty($parem['goods_id'])){

        $this->ajaxRet(array('status' => 0, 'info' => '商店id为空','data'=>''));

      }

      if(empty($parem['property_id'])){

         $this->ajaxRet(array('status' => 0, 'info' => '属性id为空','data'=>''));

      }

      //1  为金币 2 为银币

      if(empty($parem['type'])){

         $this->ajaxRet(array('status' => 0, 'info' => '类型为空','data'=>'')); 

      }

      if(empty($parem['price'])){

          $this->ajaxRet(array('status' => 0, 'info' => '价格为空','data'=>'')); 

      }



      $shujson = explode(',',$parem['property_id']);

      sort($shujson);

      

      $sj = json_encode($shujson);

      

      $d = D('duihuan_trolley')->where(array('goods_id'=>$parem['goods_id'],'user_id'=>$this->uid,'shujson_list'=>$sj))->find();

      if($d){

         $this->ajaxRet(array('status' => 0, 'info' => '该商品已经加入购物车请查看','data'=>'')); 

      }

      $shu = [];

      //转成数组

      $array = explode(',',$parem['property_id']);

      //查询 商品

      $goods = D('duihuan')->field('shopname,jinbi,yingbi,img')->where(array('id'=>$parem['goods_id']))->find();

      if(!$goods){

         $this->ajaxRet(array('status' => 0, 'info' => '无商品','data'=>'')); 

      }

      foreach ($array as $key => $value) {

      	 $shu[] = D('duihuan_attribute as du')

      	         ->field('du.shuzhi,at.attribute_name,du.id as ids')

      	         ->join('cmf_attribute as at ON du.shuzhi_id = at.id')

      	         ->where(array('du.id'=>$value))->find();

      }

       sort($array);



      //重组数据

      $data = [

          'shu'=>json_encode($shu),

          'goods_name' => $goods['shopname'],

          // 'gold' => $goods['jinbi'],

          // 'silver' => $goods['yingbi'],

          'sum' => $parem['goods_sum'],

          'img' => $goods['img'],

          'user_id' => $this->uid,

          'goods_id' => $parem['goods_id'],

          'shu_list' => $parem['property_id'],

          'shujson_list' => json_encode($array),

      ];

      //判断金额类型

      //如果是金币

      if($parem['type'] == 1){

         $data['silver'] = $parem['price'];

         

      }elseif ($parem['type']==2) {

      	$data['gold'] = $parem['price'];

      }else{

      	$this->ajaxRet(array('status' => 1, 'info' => '类型错误','data'=>''));

      }



      //存库

      $d = D('duihuan_trolley')->add($data);

      if($d){

        $this->ajaxRet(array('status' => 1, 'info' => '添加购物车成功','data'=>''));

      }

       $this->ajaxRet(array('status' => 0, 'info' => '添加购物车失败','data'=>''));

      

    }

    //我的购物车

    public function wotrolley(){

    	$id = $this->uid;

    	$map =  ['user_id'=>$id];

        $data['_list'] = $this->lists('duihuan_trolley',$map);

        foreach ($data['_list'] as $key => $value) {

          $data['_list'][$key]['sh'] = json_decode($value['shu'],true);	 

        }

    	$data['_totalPages'] = $this->_totalPages; //总页数

    	$this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));

    }

    //购物车 结算

    public function account(){

       $parem = I('post.');

       // if(!isset($parem['gold'])){

       //    $this->ajaxRet(array('status' => 0, 'info' => '金币总价为空','data'=>''));

       // }

       // if(!isset($parem['silver'])){

       //    $this->ajaxRet(array('status' => 0, 'info' => '银币总价为空','data'=>''));

       // }

       //获取 购物车id

       if(empty($parem['id'])){

          $this->ajaxRet(array('status' => 0, 'info' => 'id为空','data'=>''));

       }

       //创建订单 前提 判断库存是否充足

       //转换成数组

       $array = explode(',',$parem['id']);

       //排序

       //sort($array);

       foreach ($array as $key => $value) {

       	 $shu['_list'][] = D('duihuan_trolley')->where(array('id'=>$value))->find();

       }

        

        //查库存

        foreach ($shu['_list'] as $key => $value) {



        	$shu['zongsilver'] += ($value['silver']*$value['sum']);

        	$shu['zonggold'] += ($value['gold']*$value['sum']);

        	$d = D('sku')->where(array('duihuan_id'=>$value['goods_id'],'shujson_list'=>$value['shujson_list']))->find();



        	if($d['ku'] < $value['sum'] || $d['ku'] == 0){

              $s[] =$value['goods_name'].'库存不足';



        	}

        }

        //

        $order_no = [];

        $orders = $this->order_no();

        $shu['order_no'] =  $orders;

        //查询收货地址 

        $address = D('duihuan_address')->where(array('is_use'=>2,'user_id'=>$this->uid,'is_default'=>2))->find();

        $shu['address'] = $address;

        if(!empty($s)){

           $this->ajaxRet(array('status' => 0, 'info' => '','data'=>$s));

        }

        

        //查询订单里的商品 价格 计算 

        

       // $this->ajaxRet(array('status' => 0, 'info' => '','data'=>$shu));

      

        try {

        	 //创建订单

            $orderss = [

              'order_no' =>$orderss,

              'jinbi' => $shu['zonggold'],

              'yinbi' => $shu['zongsilver'],

              'status' =>1,//待支付

              'user_id' => $this->uid,

              'create_time' => date('Y-m-d H:i:s'),

            ];

        	$order = M('duihuan_order');

        	  

        	  if($order->create()){

                  $result = $order->add($orderss);

                  if($result){

                     //获取主键

                  	 $Id = $result;

                     foreach ($shu['_list'] as $key => $value) {

                     	 $item = [

                           'goods_id'=>$value['goods_id'],

                           'quantity' => $value['sum'],

                           'specification'=>$value['shu_list'],

                           'order_id' => $Id,

                           'jinbi' => $value['gold'],

                           'yinbi' => $value['silver'],

                           'gui_json' =>$value['shu'],

                           'img' => $value['img'],

                           'shopname' => $value['goods_name'],

                     	 ];

                     	 D('order_itme')->add($item);

                     	 //清空购物车

                     	 D('duihuan_trolley')->where(array('id'=>$value['id']))->delete();

                     }

                  }

        	  }

        	  

        	//获取查询订单的id

            $this->ajaxRet(array('status' => 1, 'info' => '创建订单成功','data'=>$shu));

        } catch (\Exception $e) {

         $this->ajaxRet(array('status' => 0, 'info' => '创建订单失败','data'=>''));

        }

       

    }

    public function ordercydf(){

      $parem = input('post.');

      if(empty($parem['order_no'])){

         $this->ajaxRet(array('status' => 0, 'info' => '订单号为空','data'=>''));

      }



    }

    //购物车 删除

    public function trolleydel(){

    	$parem = I('post.');

    	if(empty($parem['id'])){

          $this->ajaxRet(array('status' => 0, 'info' => '购物车id为空','data'=>''));

    	}

    	$id = $this->uid;

    	$del = D('duihuan_trolley')->where(array('id'=>$parem['id'],'user_id'=>$id))->delete();

    	if($del){

           $this->ajaxRet(array('status' => 1, 'info' => '删除成功','data'=>''));

    	}

    	$this->ajaxRet(array('status' => 0, 'info' => '删除失败','data'=>''));

    }

    //提交订单 查库存 减库存 减用户金币 用户购买记录 消费记录

    public function submitorder(){	

      $parem = I('post.');

      if(empty($parem['order_no'])){

        $this->ajaxRet(array('status' => 0, 'info' => '订单号为空','data'=>''));

      } 



      // if(!isset($parem['goldsumprice'])){

      //    $this->ajaxRet(array('status' => 0, 'info' => '金币应付金额为空','data'=>''));

      // }

      // if(!isset($parem['silversumprice'])){

      //    $this->ajaxRet(array('status' => 0, 'info' => '银币应付金额为空','data'=>''));

      // }

      if(empty($parem['address_id'])){

         $this->ajaxRet(array('status' => 0, 'info' => '收货地址为空','data'=>''));

      }

      //查询订单商品的金额 减去优惠的金币 银币



      //查询订单是否已经支付

      $yizf = D('duihuan_order')->where(array('order_no'=>$parem['order_no'],'status'=>1))->find();

      if(!$yizf){

        $this->ajaxRet(array('status' => 0, 'info' => '订单已经使用','data'=>''));

      }

      //查询是否有使用的收货地址

       // $address = D('duihuan_address')->where(array('user_id'=>$this->uid,'is_use'=>2));

       // if(!$address){

       //   $this->ajaxRet(array('status' => 0, 'info' => '没有收货地址','data'=>''));

       // }

       $order_no = D('duihuan_order')->where(array('order_no'=>$parem['order_no']))->find();

       if(!$order_no){

          $this->ajaxRet(array('status' => 0, 'info' => '无订单','data'=>''));

       }



       $order_id = $order_no['id'];

       $order_item = D('order_itme')->where(array('order_id'=>$order_id))->select();



        foreach ($order_item as $key => $value) {



          //转成数组

          $jsonar = explode(',', $value['specification']);

          //排序

          sort($jsonar);

          //转成json

          $shujson_list = json_encode($jsonar);

          $d = D('sku')->where(array('duihuan_id'=>$value['goods_id'],'shujson_list'=>$shujson_list))->find();

          if($d['ku'] < $value['quantity'] || $d['ku'] == 0){

              $s[] =$value['shopname'].'库存不足';

              

          }

          //计算商品优惠金币 只能是金币 

          $parem['goldsumprice'] += ($value['jinbi'] - $value['disjin'])*$value['quantity'];

          $parem['silversumprice'] += ($value['yinbi']*$value['quantity']);

         



        }

        

        if(!empty($s)){

           $this->ajaxRet(array('status' => 0, 'info' => $s,'data'=>''));

        }

        //查询用户金额

        $gold = D('users')->where(array('id'=>$this->uid))->find();

        if(!empty($parem['goldsumprice'])){

          

          if($gold['gold_coin'] < $parem['goldsumprice']){



            $this->ajaxRet(array('status' => 0, 'info' => '金币不足,请充值','data'=>''));

          }

        }

        if(!empty($parem['silversumprice'])){

         

          if($gold['silver_coin'] < $parem['goldsumprice']){

            $this->ajaxRet(array('status' => 0, 'info' => '银币不足,请充值','data'=>''));

          }

        }

        M()->startTrans();

        //扣除用户金额 创建消费表 购买记录表 修改订单状态

        try {

           //获取用户金币

           $jinbi = $gold['gold_coin'];

           $jincha = ($jinbi-$parem['goldsumprice']);

           $yinbi = $gold['silver_coin'];

           $yincha = ($yinbi-$parem['silversumprice']);

           $svr = [

                'gold_coin'=> $jincha,

                'silver_coin' => $yincha,

           ];

           //修改用户的收货地址

           D('duihuan_order')->where(array('order_no'=>$parem['order_no']))->save(['address_id'=>$parem['address_id']]);

           //修改用户

           $users = D('users')->where(array('id'=>$this->uid))->save($svr);

           //创建消费表  

            $consume = [

              'order_no' => $parem['order_no'],

              'consume_time' =>date('Y-m-d H:i:s'),

              'user_id' => $this->uid,

              'consume_jinbi' => $parem['goldsumprice'],

              'consume_yinbi' =>$parem['silversumprice'],

              'consume_yinbi_qian' => $yinbi,

              'consume_jinbi_qian' => $jinbi,

              'consume_yinbi_hou' => $yincha,

              'consume_jinbi_hou' => $jincha,

              'consumtype' => '余额支付',

            ];

            $consum = D('duihuan_consume')->add($consume);

            //修改订单状态 代发货状态

            D('duihuan_order')->where(array('order_no'=>$parem['order_no']))->save(['status'=>2]);

            //修改商品的购买数量

            foreach ($order_item as $key => $value) {

                D('duihuan')->where(array('id'=>$value['goods_id']))->setInc("paymentren",1);

                //用户消费记录

                $da = [

                   'user_id' => $this->uid,

                   'shopname' => $value['shopname'],

                   'jinbi' => $value['jinbi'],

                   'yinbi' => $value['yinbi'],

                   'create_time' => date('Y-m-d H:i:s'),

                   'order_id' => $value['order_id'],

                   'create_ri' => date('Y-m-d'),



                ];

                D('user_xiao')->add($da);

                //减库存

                //转成数组

                $jsonar = explode(',', $value['specification']);

                //排序

                sort($jsonar);

                //转成json

                $shujson_list = json_encode($jsonar);

                $sku = D('sku')->where(array('duihuan_id'=>$value['goods_id'],'shujson_list'=>$shujson_list))->find();

                $chasku = ($sku['ku']-$value['quantity']);

                $ku = [

                  'ku' => $chasku,

                ];

                D('sku')->where(array('duihuan_id'=>$value['goods_id'],'shu_list'=>$value['specification']))->save($ku);



            }

            

          M()->commit();

          $this->ajaxRet(array('status' => 1, 'info' => '支付成功','data'=>''));

        } catch (\Exception $e) {

          M()->rollback();

          $this->ajaxRet(array('status' => 0, 'info' => $e->getMessage(),'data'=>''));

        }

       

       

    }

    

    //金币抵扣券 列表

    public function couponlist(){

       $parem = I('post.');

       $conpon = D('Coupon');

       $bi  = $conpon->myCoupon($this->uid);

       

       $CouponGold = $bi['CouponGold'];

       //查询出未使用的 代币券

       foreach ($CouponGold as $key => $value) {

           if($value['status'] == 0){

              $c[] = $value ;

           }

       }

       $this->ajaxRet(array('status' => 0, 'info' => '','data'=>$c));

    }

    //获取抵扣金额

    public function deduction(){

    	$parem = I('post.');

    	if(empty($parem['id'])){

          $this->ajaxRet(array('status' => 0, 'info' => '抵扣券id为空','data'=>''));

    	}

      if(empty($parem['goods_id'])){

          $this->ajaxRet(array('status' => 0, 'info' => '商品id为空','data'=>''));

      }

      if(empty($parem['order_no'])){

          $this->ajaxRet(array('status' => 0, 'info' => '商品id为空','data'=>''));

      }

    	 $conpon = D('Coupon');

    	 $jin = $conpon->useGold($parem['id'],$this->uid);

       //存入

       $order = D('duihuan_order')->where(array('order_no'=>$parem['order_no']))->find();

       D('order_item')->where(array('order_id'=>$order['id'],'goods_id'=>$parem['goods_id']))->save(['disjin'=>$parem['jin'],'discoun_id'=>$parem['id']]);

    	 $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$jin));

    }

    //添加收货地址

    public function addressadd(){

    	$parem = I('post.');

    	if(empty($parem['dress_name'])){

           $this->ajaxRet(array('status' => 0, 'info' => '收货人为空','data'=>''));

    	}

    	if(empty($parem['mobile'])){

           $this->ajaxRet(array('status' => 0, 'info' => '收货人手机号为空','data'=>''));

    	}

    	if(empty($parem['sheng'])){

           $this->ajaxRet(array('status' => 0, 'info' => '省为空','data'=>''));

    	}

    	if(empty($parem['shi'])){

           $this->ajaxRet(array('status' => 0, 'info' => '市为空','data'=>''));

    	}

    	if(empty($parem['qu'])){

           $this->ajaxRet(array('status' => 0, 'info' => '区为空','data'=>''));

    	}

    	if(empty($parem['detailed'])){

           $this->ajaxRet(array('status' => 0, 'info' => '详细地址为空','data'=>''));

    	}

    	if(empty($parem['is_default'])){

           $this->ajaxRet(array('status' => 0, 'info' => '默认为空','data'=>''));

    	}

    	

    	//

    	$mo = D('duihuan_address')->where(array('is_default'=>2,'user_id'=>$this->uid))->find();

    	if($mo){

    	   //修改之前的默认地址

    		M('duihuan_address')->where(array('id'=>$mo['id'],'is_default'=>2))->save(['is_default'=>1]);

           

    	}

    	

    	$parem['user_id'] = $this->uid;

        $address = M('duihuan_address');

        if($address->create()){

           $data = $address->add($parem);

           if($data){

             $this->ajaxRet(array('status' => 1, 'info' => '添加成功','data'=>''));

           }

        }

        $this->ajaxRet(array('status' => 0, 'info' => '添加失败','data'=>''));

    }

    //删除 收货地址

    public function addressdel(){

    	$parem = I('post.');

    	if(empty($parem['id'])){

          $this->ajaxRet(array('status' => 0, 'info' => 'id为空','data'=>''));

    	}

        $data = D('duihuan_address')->where(array('id'=>$parem['id']))->delete();

        if($data){

          $this->ajaxRet(array('status' => 1, 'info' => '删除成功','data'=>''));

        }

        $this->ajaxRet(array('status' => 0, 'info' => '删除失败','data'=>''));

    }

    //设为默认

    public function setdefault(){

    	$parem = I('post.');

    	if(empty($parem['id'])){

          $this->ajaxRet(array('status' => 0, 'info' => 'id为空','data'=>''));

    	}

    	if(empty($parem['is_default'])){

          $this->ajaxRet(array('status' => 0, 'info' => '默认为空','data'=>''));

    	}

    	if(empty($parem['is_use'])){

          $this->ajaxRet(array('status' => 0, 'info' => '使用为空','data'=>''));

    	}

    	$find = D('duihuan_address')->where(array('id'=>$parem['id']))->find();

    	if(!$find){

           $this->ajaxRet(array('status' => 0, 'info' => '暂无数据','data'=>''));

    	}

        $update = [

             'is_default' => $parem['is_default'],

             'is_use' => $parem['is_use'],



        ];

        //修改之前的默认地址

        M('duihuan_address')->where(array('user_id'=>$this->uid,'is_default'=>2))->save(['is_default'=>1,'is_use'=>1]);



        $up = D('duihuan_address')->where(array('id'=>$parem['id']))->save($update);

        

        if($up){

          $this->ajaxRet(array('status' => 1, 'info' => '操作成功','data'=>''));

        }

        $this->ajaxRet(array('status' => 0, 'info' => '操作失败','data'=>''));



    }

    //我的收货地址

    public function addresslist(){

    	$id = $this->uid;

    	$data = D('duihuan_address')->where('user_id='.$id)->select();

    	if(!$data){

           $this->ajaxRet(array('status' => 0, 'info' => '暂无收货地址','data'=>''));

    	}

    	$this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));

    }

    //修改购物车的商品数量

    public function  trolleydelsum(){

    	$parem = I('post.');

    	if(empty($parem['id'])){

          $this->ajaxRet(array('status' => 0, 'info' => '购物车id为空','data'=>''));

    	}

    	if(empty($parem['sum'])){

          $this->ajaxRet(array('status' => 0, 'info' => '数量为空','data'=>''));

    	}

    	$up = D('duihuan_trolley')->where(array('id'=>$parem['id']))->save(['sum'=>$parem['sum']]);

    	if($up){

           $this->ajaxRet(array('status' => 1, 'info' => '','data'=>''));

    	}

    }

    //我的订单

    public function allorder(){

      

      

      $parem = I('post.');

      if(!isset($parem['type'])){

         $this->ajaxRet(array('status' => 0, 'info' => '请设置类型','data'=>''));

      }

      $array = array("1","2","3","4");

      if(!in_array($parem['type'],$array)){

        $this->ajaxRet(array('status' => 0, 'info' => '传入类型错误','data'=>''));

      }

       $uid = $this->uid;

      

      $where = array('o.user_id'=>$uid,'status'=>$parem['type']);

      //var_dump($parem);die;

     

      $page = !empty($parem['p'])? $parem['p'] : 1;

      $size = !empty($parem['r'])? $parem['r'] : C('p');

      $totl = D('duihuan_order o')->where($where)->count();



      //总数转换成多少页

      $pageTo=ceil($totl/$size);



      $from = ($page-1)*$size; 

        

      $data['_list'] = M('duihuan_order o')

              ->join('cmf_order_itme i on o.id = i.order_id')

              ->field('o.order_no,o.id,o.status,i.quantity,i.jinbi,i.yinbi,i.img,o.create_time,i.disjin,i.shopname,i.goods_id')

              ->where($where) 

              ->limit($from,$size)

              ->select();

      

    

      $data['_totalPages'] = $pageTo;

      $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));

    }

    //我的全部订单

    public function payan(){

      $parem = I('post.');

      

      $uid = $this->uid;

   

      $page = !empty($parem['p'])? $parem['p'] : 1;

      $size = !empty($parem['r'])? $parem['r'] : C('p');

      $totl = D('duihuan_order o')->where('user_id='.$uid)->count();



      //总数转换成多少页

      $pageTo=ceil($totl/$size);



      $from = ($page-1)*$size; 

        

      $data['_list'] = M('duihuan_order o')

              ->join('cmf_order_itme i on o.id = i.order_id')

              ->field('o.order_no,o.id,o.status,i.quantity,i.jinbi,i.yinbi,i.img,o.create_time,i.disjin,i.shopname')

              ->where(array('o.user_id'=>$uid)) 

              ->limit($from,$size)

              ->select();



      

      $data['_totalPages'] = $pageTo;

      $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));



    }

    //礼物积分兑换 礼物兑换都是金币

    public function exchange(){

      $parem = I('post.');

      if(empty($parem['id'])){

         $this->ajaxRet(array('status' => 0, 'info' => '参数id为空','data'=>''));

      }

      if(empty($parem['sumprice'])){

         $this->ajaxRet(array('status' => 0, 'info' => '总金额为空','data'=>''));

      }

      //查询用户金币是否充足

      $user = D('users')->where(array('id'=>$this->uid))->find();

      

      if($user['gold_coin']<$parem['sumprice']){

        $this->ajaxRet(array('status' => 2, 'info' => '金币不足，请充值','data'=>''));

      }

      M()->startTrans();

      try {

          //创建用户购买记录  减去用户金币 创建购买订单

          $jinbi = $user['gold_coin'];

          $cha = ($jinbi-$parem['sumprice']);

          D('users')->where(array('id'=>$this->uid))->save(['gold_coin'=>$cha]);

          $con = [

              'consume_time'=>date('Y-m-d H:i:s'),

              'user_id' => $this->uid,

              'consume_jinbi' => $parem['sumprice'],

              'consume_jinbi_qian' => $jinbi,

              'consume_jinbi_hou' => $cha,

              'order_no' => $this->order_no(),

              'consumtype' => '金币支付',

              'gify_id' => $parem['id'],

          ];

          D('duihuan_consume')->add($con);

          $daoo = explode(',',$parem['id']);

          foreach ($daoo as $key => $value) {

            $da[] = D('duihuan')->where(array('id'=>$value))->find();

          }

           foreach ($da as $key => $value) {

            $daoju = [

                 'user_id' => $this->uid,

                 'dift_id' => $value['id'],

                 'jinbi' => $value['jinbi'],

                 'yinbi' => $value['yinbi'],

                 'create_time' => date('Y-m-d H:i:s'),

                 'is_use' => 0,

              ];

             D('duihuan_daoju')->add($daoju);

             $xiao = [

                 'create_time' => date('Y-m-d H:i:s'),

                 'shopname' => $value['shopname'],

                 'jinbi' => $value['jinbi'],

                 'yinbi' => $value['yinbi'],

                 'create_ri' => date('Y-m-d'),

                 'user_id' => $this->uid,

             ];

             D('user_xiao')->add($xiao);

           }

        M()->commit();

        $this->ajaxRet(array('status' => 1, 'info' => '兑换成功','data'=>''));

      } catch (\Exception $e) {

        M()->rollback();

        $this->ajaxRet(array('status' => 0, 'info' => '兑换失败','data'=>''));

      }

       

    }

    //订单号

    public function order_no(){

      $order_id_main = date('YmdHis') . rand(20000000,99999999);



      $order_id_len = strlen($order_id_main);



      $order_id_sum = 0;



      for($i=0; $i<$order_id_len; $i++){



      $order_id_sum += (int)(substr($order_id_main,$i,1));



      }



      $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);



      return $osn; 

    }

    //消费记录

    public function calendar(){
       $uid = $this->uid;
       $creates = D('user_xiao')->field('create_ri')->where(array('user_id'=>$uid))->select();

      // 数组去重
      $a = array_unique($creates,SORT_REGULAR);

      foreach ($a as $key => $value) {
        $ca[]['time'] = $value['create_ri'];
      }

      $_da['time'] = 0;
      $_da['da'] = 0;
      foreach ($ca as $key => $value) {
          $map =  ['create_ri'=>$value['time'],'user_id'=>$this->uid];
          $xx['_list'][$key]['time'] = $value['time'];
          $xx['_list'][$key]['da'] = D('user_xiao')->where($map)->select();

          $_da['time'] = $value['time'];
          $_da['da'] = $this->user_xiao(D('user_xiao')->where($map)->select());

          $_data[] = $_da;
      }

      $data['_list'] = $_data?:[];
      $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));
    }



    function user_xiao($list){

        foreach($list as &$value){

          $value['jinbi'] = empty($value['jinbi'])?'':$value['jinbi'];

          $value['yinbi'] = empty($value[''])?'':$value['yinbi'];

          $value['order_id'] = empty($value[''])?'':$value['order_id'];

        }

        return $list;

    }







    //特权 道具 积分兑换   金币 银币 都扣除  

    public function prop(){

     $parem = I('post.');

     if(empty($parem['id'])){

        $this->ajaxRet(array('status' => 0, 'info' => '参数id为空','data'=>''));

     }

     if(empty($parem['type'])){

        

        $this->ajaxRet(array('status' => 0, 'info' => '类型为空','data'=>''));

     }

     if(empty($parem['price'])){

        $this->ajaxRet(array('status' => 0, 'info' => '金额为空','data'=>''));

     }  

     if($parem['type']!= 1 && $parem['type']!= 2){

        $this->ajaxRet(array('status' => 0, 'info' => '传入类型不正确','data'=>''));

     }

      //查询用户金币银币是否充足

     $da = D('users')->where(array('id'=>$this->uid))->find();

     //获取金币

     $jinbi = $da['gold_coin'];

     $yinbi = $da['silver_coin'];

     //金币不足

     if($parem['type'] == 1){

      if($jinbi < $parem['price']){

        $this->ajaxRet(array('status' => 2, 'info' => '金币不足,请充值','data'=>''));

      }

      M()->startTrans();

      try {

         //扣除 金币 银币

        $chajinbi = ($jinbi-$parem['price']);

       // $chayinbi = ($yinbi-$parrem['yinbi']);

        //修改

        D('users')->where(array('id'=>$this->uid))->save(['gold_coin'=>$chajinbi]);

        //查询兑换表

        $duihuan = D('duihuan')->where(array('id'=>$parem['id']))->find();

        if(!$duihuan){

           $this->ajaxRet(array('status' => 0, 'info' => '兑换商品不存在','data'=>''));

        }

        //写入道具表

        $daoju = [

                 'user_id' => $this->uid,

                 'dift_id' => $duihuan['id'],

                 'jinbi' => $parem['price'],

                 //'yinbi' => $parem['yinbi'],

                 'create_time' => date('Y-m-d H:i:s'),

                 'is_use' => 0,

              ];

        D('duihuan_daoju')->add($daoju);

        //写入消费表

        $xiao = [

              'create_time'=> date('Y-m-d H:i:s'),

              'shopname' => $duihuan['shopname'],

              'jinbi' => $parem['price'],

              //'yinbi' => $parem['yinbi'],

              'user_id' => $this->uid,

              'create_ri' => date('Y-m-d'),

        ];

        D('user_xiao')->add($xiao);

        M()->commit();

        $this->ajaxRet(array('status' => 1, 'info' => '兑换成功','data'=>''));

     } catch (\Exception $e) {

       M()->rollback();

       $this->ajaxRet(array('status' => 0, 'info' => $e->getMessage(),'data'=>''));

     }



     }

     //银币

     if($parem['type'] == 2){

        if($yinbi < $parem['price']){

        $this->ajaxRet(array('status' => 3, 'info' => '银币不足','data'=>''));

       } 

       M()->startTrans();

     try {

         //扣除 金币 银币

       // $chajinbi = ($jinbi-$parem['jinbi']);

        $chayinbi = ($yinbi-$parem['price']);

        //修改

        D('users')->where(array('id'=>$this->uid))->save(['silver_coin'=>$chayinbi]);

        //查询兑换表

        $duihuan = D('duihuan')->where(array('id'=>$parem['id']))->find();

        if(!$duihuan){

           $this->ajaxRet(array('status' => 0, 'info' => '兑换商品不存在','data'=>''));

        }

        //写入道具表

        $daoju = [

                 'user_id' => $this->uid,

                 'dift_id' => $duihuan['id'],

                // 'jinbi' => $parem['jinbi'],

                 'yinbi' => $parem['price'],

                 'create_time' => date('Y-m-d H:i:s'),

                 'is_use' => 0,

              ];

        D('duihuan_daoju')->add($daoju);

        //写入消费表

        $xiao = [

              'create_time'=> date('Y-m-d H:i:s'),

              'shopname' => $duihuan['shopname'],

             // 'jinbi' => $parem['jinbi'],

              'yinbi' => $parem['price'],

              'user_id' => $this->uid,

              'create_ri' => date('Y-m-d'),

        ];

        D('user_xiao')->add($xiao);

        M()->commit();

        $this->ajaxRet(array('status' => 1, 'info' => '兑换成功','data'=>''));

      } catch (\Exception $e) {

       M()->rollback();

       $this->ajaxRet(array('status' => 0, 'info' => $e->getMessage(),'data'=>''));

      }

     }

    

     

     

    

     



    }

    //优惠兑换 银币兑换

    public function disale(){

      $parem = I('post.');

      if(empty($parem['id'])){

        $this->ajaxRet(array('status' => 0, 'info' => '参数id为空','data'=>''));

      }

      if(empty($parem['yinbi'])){

        $this->ajaxRet(array('status' => 0, 'info' => '银币为空','data'=>''));

      }

      //查询用户银币是否充足

     $da = D('users')->where(array('id'=>$this->uid))->find();

     //获取银币

    

     $yinbi = $da['silver_coin'];

     if($yinbi < $parem['yinbi']){

        $this->ajaxRet(array('status' => 3, 'info' => '银币不足','data'=>''));

     }

     M()->startTrans();

     try {

         //扣除 金币 银币

      

        $chayinbi = ($yinbi-$parrem['yinbi']);

        //修改

        D('users')->where(array('id'=>$this->uid))->save(['silver_coin'=>$chayinbi]);

        //查询兑换表

        $duihuan = D('duihuan')->where(array('id'=>$parem['id']))->find();

        if(!$duihuan){

           $this->ajaxRet(array('status' => 0, 'info' => '兑换商品不存在','data'=>''));

        }

        //写入道具表

        $daoju = [

                 'user_id' => $this->uid,

                 'dift_id' => $duihuan['id'],

              

                 'yinbi' => $parem['yinbi'],

                 'create_time' => date('Y-m-d H:i:s'),

                 'is_use' => 0,

              ];

        D('duihuan_daoju')->add($daoju);

        //写入消费表

        $xiao = [

              'create_time'=> date('Y-m-d H:i:s'),

              'shopname' => $duihuan['shopname'],

              

              'yinbi' => $parem['yinbi'],

              'user_id' => $this->uid,

              'create_ri' => date('Y-m-d'),

        ];

        D('user_xiao')->add($xiao);

        M()->commit();

        $this->ajaxRet(array('status' => 1, 'info' => '兑换成功','data'=>''));

     } catch (\Exception $e) {

       M()->rollback();

       $this->ajaxRet(array('status' => 0, 'info' => $e->getMessage(),'data'=>''));

     }





    }

}