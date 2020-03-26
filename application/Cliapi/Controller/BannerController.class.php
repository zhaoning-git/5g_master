<?php

/**

 * 商城banner 图

 */

namespace Cliapi\Controller;



use Think\Controller;

header('Access-Control-Allow-Origin: *');//*号代表所有域名
header('Access-Control-Allow-Credentials:true');
header("Access-Control-Allow-Headers: access-token,access_token,Content-Type");

class BannerController extends ApiController

{ 
  

  //查询banner图

  public function banner(){

   $parem = I('post.');



   if(empty($parem['banner_type'])){



      $this->ajaxRet(array('status' => 1, 'info' => '类型为空','data'=>''));



   }

    //查询

    $data = D('banner as ban')

             ->field('portrait.path as portraits')

             ->join('cmf_picture as portrait ON ban.img = portrait.id')

             ->where(array('ban.banner_type'=>$parem['banner_type']))

             ->select();

    $datas=array_map(function($val){

                     $val['portraits'] = AddHttp($val['portraits']);

                    return $val;

               },$data);         

    $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$datas));

  }

  //获取特权

  public function privilege(){



    $where['typefenid'] = array('in',);//cid在这个数组中，

  	$data['_list'] = D('duihuan')

  	         ->field('shopname,img,yingbi,jinbi,id,sort')

  	         ->where(array('type'=>'特权','sta'=>1))

             ->where(array('typefenid'=>['in',['9','10','11']]))
             ->order('sort asc')
  	         ->select();

     $data['ta'] =  D('duihuan')

             ->field('shopname,img,yingbi,jinbi,id,sort')

             ->where(array('type'=>'特权','sta'=>1))

             ->where(array('typefenid'=>['not in',['9','10','11']]))
             ->order('sort asc')
             ->select();      

  	$this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$data));

  }

  //获取道具

  public function prop(){

  	 $map =  ['sta'=>1,'type'=>'道具'];

     $data['_list'] = $this->lists('duihuan',$map);

     

     foreach ($data['_list'] as $key => $value) {

     	 $datas['_list'][$key]['shopname'] = $value['shopname'];

     	 $datas['_list'][$key]['jinbi'] = $value['jinbi'];

     	 $datas['_list'][$key]['yingbi'] = $value['yingbi'];

     	 $datas['_list'][$key]['img'] = AddHttp($value['img']);

       $datas['_list'][$key]['id'] = $value['id'];
       $datas['_list'][$key]['sort'] = $value['sort'];


     }
      $timeKey =  array_column(  $datas['_list'], 'sort'); //取出数组中status的一列，返回一维数组
       array_multisort($timeKey, SORT_ASC,$datas['_list']);//排序，根据$status 
     $datas['_totalPages'] = $this->_totalPages;

     $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$datas));

   

  }

  //优惠区

  // 

  public function telephone(){

  	 //查询话费专区

  	 $hua = D('coupon')->where(array('title'=>'话费专区'))->find();

  	 $map =  ['sta'=>1,'type'=>'虚拟物品','typefenid'=>$hua['id']];

  	 $da = $this->lists('duihuan',$map);

  	 foreach ($da as &$value) {

     	 $datas['_list'][$key]['shopname'] = $value['shopname'];

     	 $datas['_list'][$key]['jinbi'] = $value['jinbi'];

     	 $datas['_list'][$key]['yingbi'] = $value['yingbi'];

     	 $datas['_list'][$key]['img'] = $value['img'];

       $datas['_list'][$key]['id'] = $value['id'];

     }

     $datas['_list'] = $da;
  	 $datas['_totalPages'] = $this->_totalPages;

  	 $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$datas));

  }

  //会员专区

  public function membera(){

    //查询话费专区

  	 $hua = D('coupon')->where(array('title'=>'会员专区'))->find();

  	 $map =  ['sta'=>1,'type'=>'虚拟物品','typefenid'=>$hua['id']];

  	 $data['_list'] = $this->lists('duihuan',$map);

  	 foreach ($data['_list'] as $key => $value) {

     	 $datas['_list'][$key]['shopname'] = $value['shopname'];

     	 $datas['_list'][$key]['jinbi'] = $value['jinbi'];

     	 $datas['_list'][$key]['yingbi'] = $value['yingbi'];

     	 $datas['_list'][$key]['img'] = $value['img'];

       $datas['_list'][$key]['id'] = $value['id'];

     }

  	 $datas['_totalPages'] = $this->_totalPages;

  	 $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$datas));

  }

  //优惠券专区

  public function discount(){

    //查询话费专区

  	 $hua = D('coupon')->where(array('title'=>'优惠券专区'))->find();

  	 $map =  ['sta'=>1,'type'=>'虚拟物品','typefenid'=>$hua['id']];

  	 $data['_list'] = $this->lists('duihuan',$map);

  	 foreach ($data['_list'] as $key => $value) {

     	 $datas['_list'][$key]['shopname'] = $value['shopname'];

     	 $datas['_list'][$key]['jinbi'] = $value['jinbi'];

     	 $datas['_list'][$key]['yingbi'] = $value['yingbi'];

     	 $datas['_list'][$key]['img'] = $value['img'];

       $datas['_list'][$key]['id'] = $value['id'];

     }

  	 $data['_totalPages'] = $this->_totalPages;

  	 $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$data));



  }

  //获取实物

  public function material(){

      $map =  ['sta'=>1,'type'=>'实物'];

      $data['_list'] = $this->lists('duihuan',$map);

      foreach ($data['_list'] as $key => $value) {

      	$datas['_list'][$key]['id'] = $value['id'];

      	$datas['_list'][$key]['shopname'] = $value['shopname'];

      	 $datas['_list'][$key]['jinbi'] = $value['jinbi'];

     	 $datas['_list'][$key]['yingbi'] = $value['yingbi'];

     	 $datas['_list'][$key]['miaoshu'] = $value['miaoshu'];

     	 $datas['_list'][$key]['paymentren'] = $value['paymentren'];

     	 $datas['_list'][$key]['img'] = AddHttp($value['img']);
       $datas['_list'][$key]['sort'] = $value['sort'];



      	 // $img = explode(',',$value['figure_img']);



      	 // foreach ($img as $ke => $val) {

      	 // 	$img = D('picture as pi')->field('pi.path as portraits')

      	 // 	                     ->where(array('id'=>$val))->find();

      	 // 	  $datas['_list'][$key]['img'][]  = $img;                   

      	 // }





      }
       $timeKey =  array_column(  $datas['_list'], 'sort'); //取出数组中status的一列，返回一维数组
       array_multisort($timeKey, SORT_ASC,$datas['_list']);//排序，根据$status 
      $datas['_totalPages'] = $this->_totalPages;

      $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$datas));

  }

  //获取礼物

  public function gift(){

  	 $map =  ['sta'=>1,'type'=>'礼物'];

      $data['_list'] = $this->lists('duihuan',$map);

      foreach ($data['_list'] as $key => $value) {

      	 $datas['_list'][$key]['id'] = $value['id'];

      	 $datas['_list'][$key]['shopname'] = $value['shopname'];

      	 $datas['_list'][$key]['jinbi'] = $value['jinbi'];

     	 $datas['_list'][$key]['yingbi'] = $value['yingbi'];

     	 $datas['_list'][$key]['miaoshu'] = $value['miaoshu'];

     	

     	 $datas['_list'][$key]['img'] = $value['img'];
       $datas['_list'][$key]['sort'] = $value['sort'];
       
      	

      }
      
      $datas['_totalPages'] = $this->_totalPages;
      $timeKey =  array_column(  $datas['_list'], 'sort'); //取出数组中status的一列，返回一维数组
       array_multisort($timeKey, SORT_ASC,$datas['_list']);//排序，根据$status 
      $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$datas));

  }

  //商城 新品首发

   public function newproduct(){



   	  $map =  ['sta'=>1,'type'=>'实物','product'=>1];

      $data['_list'] = $this->lists('duihuan',$map);



      foreach ($data['_list'] as $key => $value) {

      	$datas['_list'][$key]['id'] = $value['id'];

      	$datas['_list'][$key]['shopname'] = $value['shopname'];

      	$datas['_list'][$key]['jinbi'] = $value['jinbi'];

     	  $datas['_list'][$key]['yingbi'] = $value['yingbi'];

     	  $datas['_list'][$key]['img'] = AddHttp($value['img']);
        $datas['_list'][$key]['sort'] = $value['sort'];
         //sort($datas['_list'][$key]['sort']);
        
      }

     //  asort($data['_list']);
        $timeKey =  array_column(  $datas['_list'], 'sort'); //取出数组中status的一列，返回一维数组
       array_multisort($timeKey, SORT_ASC,$datas['_list']);//排序，根据$status 

      $datas['_totalPages'] = $this->_totalPages;

      $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$datas));

   }

   //商城 热门推荐

   public function hosts(){

     $map =  ['sta'=>1,'type'=>'实物','hots'=>1];

     // $data['_list'] = $this->lists('duihuan',$map);

      $data['_list'] = D('duihuan')->where(array('type'=>'实物','sta'=>1,'hots'=>1))->order('sort asc')->select();
      //$this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$data));
      

      foreach ($data['_list'] as $key => $value) {

      	$datas['_list'][$key]['id'] = $value['id'];

      	$datas['_list'][$key]['shopname'] = $value['shopname'];

      	$datas['_list'][$key]['jinbi'] = $value['jinbi'];

     	 $datas['_list'][$key]['yingbi'] = $value['yingbi'];

     	 $datas['_list'][$key]['img'] = AddHttp($value['img']);
       $datas['_list'][$key]['sort'] = $value['sort'];

      }

       $timeKey =  array_column(  $datas['_list'], 'sort'); //取出数组中status的一列，返回一维数组
       array_multisort($timeKey, SORT_ASC,$datas['_list']);//排序，根据$status 

     // $datas['_totalPages'] = $this->_totalPages;

      $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$datas));



   }

   //实物 详情

   public function matdetails(){

   	 $parem = I('post.');

   	 if(empty($parem['id'])){

        $this->ajaxRet(array('status' => 0, 'info' => '详情id为空','data'=>''));

   	 }

   	 $find = D('duihuan')->where(array('id'=>$parem['id'],'sta'=>1))->find();

   	 //查询详情图

   	 if(!$find){

        $this->ajaxRet(array('status' => 0, 'info' => '无商品','data'=>''));

   	 }

   	 $img = $find['figure_img'];

   	 //转成数组

   	 $imgarray = explode(',',$img);

   	 foreach ($imgarray as $key => $value) {
          $_im = M('picture')->where(array('id'=>$value))->getField('path');
          if(!empty($_im)){
            $im[] = AddHttp($_im);
            $find['img'] = $im;
          }else{
            $find['img'] = '';
          }
   	 }
      if(empty($find['sort'])){
        $find['sort'] = 0;
      }
      //查询出推荐的
      $find['recom'] = M('duihuan')->field('id,img,jinbi,shopname,yingbi')->where(array('sta'=>1,'recom'=>1,'type'=>'实物'))->limit(3)->order('sort asc')->select();
      foreach ($find['recom'] as $key => $value) {
          $find['recom'][$key]['img'] = AddHttp($value['img']);
      }
      $find['inform'] = M('inform')->field('content')->where(array('status'=>1))->find();
   	 $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$find));

    }

    //实物  选择规格

    public function fication(){

      $parem = I('post.');

      if(empty($parem['goods_id'])){

        $this->ajaxRet(array('status' => 0, 'info' => '商品id为空','data'=>''));

      }

      //查询宝贝的总库存

      $repertory  = D('sku')->where(array('duihuan_id'=>$parem['goods_id']))->sum('ku');

      $data['repertory'] = $repertory;

      //查询商品详情  价格 头像

      $goods = D('duihuan')->where(array('id'=>$parem['goods_id']))->find();

      if(!$goods){

        $this->ajaxRet(array('status' => 0, 'info' => '无商品','data'=>''));

      }

      $data['img'] = $goods['img'];

      $data['shopname'] = $goods['shopname'];

      $data['id'] = $goods['id'];
      $data['jinbi'] = $goods['jinbi'];
      $data['yingbi'] = $goods['yingbi'];

      //查询出商品对应的属性

      //首次获取 商品的类型 获取商品所属类型的属性，查询商品属性关系表

      $cat_id = $goods['cat_id'];

      //查询属性表

      $shu = D('attribute')->where(array('cat_id'=>$cat_id))->select();

      

      //查询商品属性表

      foreach ($shu as $key => $value) {

      	 $shu[$key]['specification'] = D('duihuan_attribute as att')->field('shuzhi,att.img as imgs,att.shu_price,att.id,att.silver')

      	            ->where(array('duihuan_id'=>$parem['goods_id'],'shuzhi_id'=>$value['id']))->select();

      }

       $data['shu'] = $shu;

      $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));

    }

    //商品 点击属性 切换 注意再存属性的时候注意

    public function cuts(){

      //获取 商品id  属性id (以逗号分割)  

      $parem = I('post.');

      if(empty($parem['goods_id'])){

      	 $this->ajaxRet(array('status' => 0, 'info' => '商品id为空d','data'=>''));



      }

      if(empty($parem['property'])){

        $this->ajaxRet(array('status' => 0, 'info' => '属性id为空','data'=>''));

      }

      //查询出 配置好的

      $sku = D('sku')

            ->field('ku,silver_coin,gold_coin')

            ->where(array('duihuan_id'=>$parem['goods_id'],'shu_list'=>$parem['property']))->find();

      if(!$sku){

         $this->ajaxRet(array('status' => 0, 'info' => '无sku','data'=>$sku));

      }

      



      // $imsp = explode(',', $parem['property']);

      // sort($imsp);

      // foreach ($sku as $key => $value) {

      // 	 $shu_list[$key]['shu_list'] = explode(',', $value['shu_list']); 

      // 	 $shu_list[$key]['id'] = $value['id'];

      // }

      // $id = [];



      $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$sku));

    }

    //设置 关于我们

    public function regard(){

      $data = D('regard')->find();

      $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));

    }

    //获取商品通知

    public function statements(){



       $data = D('duihuan_tong')->where(array('status'=>0))->select();

       $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));



    }



    //商品评价

    public function evaluate(){

        $data['uid'] = I('post._uid');

        $data['goods_id'] = I('goods_id');

        $data['order_no'] = I('order_no');



        if(empty($data['goods_id']) || empty($data['order_no'])){

            $this->ajaxRet(array('status'=>0,'info'=>'请先选择对应商品进行再评价'));

        }



        $prefix = C('DB_PREFIX');

        $info = M('duihuan_order o')        //根据单号和商品id查询出唯一一条数据

            ->join("{$prefix}order_itme i on i.order_id = o.id")

            ->where(array('o.order_no'=>$data['order_no'],'i.goods_id'=>$data['goods_id']))

            ->find();
        
        if($info['status'] != 4){

            $this->ajaxRet(array('status'=>0,'info'=>'请确认收货后再评价哦'));

        }



        $data['evaluate'] = I('evaluate');

        $data['eva_pic'] = I('eva_pic');

        $data['create_time'] = time();



        if(empty($data['evaluate']) && empty($data['eva_pic'])){

            $this->ajaxRet(array('status'=>0,'info'=>'请输入您对该商品的评价'));

        }



        $evaInfo = M('duihuan_eva')->where(array('order_no'=>$data['order_no'],'goods_id'=>$data['goods_id'],'uid'=>$data['uid']))->find();

        if(!empty($evaInfo)){

            $this->ajaxRet(array('status'=>0,'info'=>'您已对该商品作出评价'));

        }



        $res = M('duihuan_eva')->add($data);

        if($res){

            $this->ajaxRet(array('status'=>1,'info'=>'评价成功','data'=>$data));

        }



    }



    //商品评价列表

    public function evaList(){

        $goods_id = I('post.goods_id');
        $type = I('post.');
         if(empty($goods_id)){
            $this->ajaxRet(array('status'=>0,'info'=>'商品id为空','data'=>''));
        }
        // if(empty($type['type'])){
        //     $this->ajaxRet(array('status'=>0,'info'=>'类型参数为空','data'=>''));
        // }
       // if($type['type'] =='parem'){
           //  $prefix = C('DB_PREFIX');

           //  $info = M('duihuan_eva e')

           //  ->join("{$prefix}users u on u.id = e.uid")

           //  ->join("{$prefix}duihuan_order o on e.order_no = o.order_no")

           //  ->field('e.id,e.goods_id,e.evaluate,e.eva_pic,e.create_time,u.avatar,u.user_nicename,o.id as oid,e.uid')

           //  ->where(array('e.goods_id'=>$goods_id))
           // // ->limit(3)
           //  ->select();
        // }
        // if($type['type'] == 'brenfd'){
            $page = !empty(I('p'))? I('p') : 1;
            $size = !empty(I('r'))? I('r') : 1;
            $from = ($page-1)*$size;
            
            $prefix = C('DB_PREFIX');

            $info['_list'] = M('duihuan_eva e')

            ->join("{$prefix}users u on u.id = e.uid")

            ->join("{$prefix}duihuan_order o on e.order_no = o.order_no")

            ->field('e.id,e.goods_id,e.evaluate,e.eva_pic,e.create_time,u.avatar,u.user_nicename,o.id as oid,e.uid')

            ->where(array('e.goods_id'=>$goods_id))
            ->limit($from,$size)
            ->select();
            // $totl = count($info);
            $totl = M('duihuan_eva e')->where(array('e.goods_id'=>$goods_id))->count();
            //总数转换成多少页
            $pageTo=ceil($totl/$size);
            $info['_totalPages'] = $pageTo;
        // }
        

        
        
        // $this->ajaxRet(array('status'=>1,'info'=>'获取评价列表成功','data'=>$info));

        foreach ($info['_list'] as $k=>&$v){
            $info['_list'][$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            $detailInfo = M('order_itme')->field('gui_json')->where(array('goods_id'=>$v['goods_id'],'order_id'=>$v['oid']))->select();

            foreach ($detailInfo as $key=>&$val){

                $shuzhi = json_decode($val['gui_json']);

                $v['shuxing'] = $shuzhi;

            }

            $v['eva_pic'] = getImgVideo($v['eva_pic']);
            $v['avatar'] = AddHttp($v['avatar']);
            unset($v['oid']);

        }

        

        $this->ajaxRet(array('status'=>1,'info'=>'获取评价列表成功','data'=>$info));

    }

    //查询历史搜索记录 

    public function seeks(){

      $parem = I('post.');

      $redis = new \Redis();

      $redis->connect('127.0.0.1',6379);



      $len = $redis->llen($parem['_uid']);

        //缓存去重

      for ($i=0; $i <$len ; $i++) { 

           $user_names[] = $redis->lindex($parem['_uid'],$i);

      }

      $data = array_unique($user_names);

      $datas = array_slice($data,0,10);

      // if(!$data){

      //   $this->ajaxRet(array('status'=>0,'info'=>'历史搜索为空','data'=>''));

      // }

       $this->ajaxRet(array('status'=>1,'info'=>'','data'=>$datas));



    }

    //大家都在搜 用户自己的历史记录

    public function onslife(){

      $parem = I('post.');

      $redis = new \Redis();

      $redis->connect('127.0.0.1',6379);
      $redis->auth('yunbao.com');
      $len = $redis->llen('all');

      $lens = $redis->llen($parem['_uid']);

        //缓存去重

      for ($i=0; $i <$len ; $i++) { 

           //大家都在搜

           $user_names[] = $redis->lindex('all',$i);

           

      }



      for ($i=0; $i <$lens ; $i++) { 

           //用户自己的搜索记录

           $uid[] = $redis->lindex($parem['_uid'],$i);

      }

       // 获取去掉重复数据的数组

      // $unique_arr = array_unique ( $user_names );

      // $arr = array_diff_assoc ($user_names,$unique_arr );

      $arr = array_count_values($user_names); 

      arsort($arr); 

      $data = array_slice($arr ,0 , 10);

      $datas = array_keys($data);



      $uids = array_unique($uid);

      $datauid = array_slice($uids,0,10);



      $arrs['uid'] = $datauid;

      $arrs['all'] = $datas;



      $this->ajaxRet(array('status'=>1,'info'=>'','data'=>$arrs));



    }

    

    //搜索商品

    public function reisos(){

     //模糊查询

      $parem = I('post.');  

      $map['shopname'] = array('like','%'.$parem['shopname'].'%');

      $map['type']  ='实物';
    


      $data['_list'] = $this->lists('duihuan',$map);
      if(!empty($data['_list'])){
        foreach($data['_list'] as &$value){
          $value['img'] = AddHttp($value['img']);
        }
      }



      $data['_totalPages'] = $this->_totalPages; //总页数

      if(!empty($parem['shopname'] && $data)){

        //存入缓$parem['shopname']);

        // $redis = S($parem['_uid'],$re,2678400);

        $redis = new \Redis();
  
        $redis->connect('127.0.0.1',6379);
         $redis->auth('yunbao.com');
        //存储公共

         $redis->lpush('all',$parem['shopname']);

         $redis->lpush($parem['_uid'],$parem['shopname']);

        //print_r($data);die;



      }

      $this->ajaxRet(array('status'=>1,'info'=>'','data'=>$data));

      

      

    }

    //设置 - 隐私政策

    public function privacy(){



      $data = D('privacy')->where(array('status'=>1))->find();



      $this->ajaxRet(array('status'=>1,'info'=>'','data'=>$data));



    }

    //设置- 用户协议

    public function agreement(){

      $data = D('agreement')->where(array('status'=>1))->find();
      $data['content'] = htmlspecialchars_decode($data['content']);


      $this->ajaxRet(array('status'=>1,'info'=>'','data'=>$data));

    }

    //redis 发布订阅

    public function fabu(){

     $redis = new \Redis();



    // 第一个参数为redis服务器的ip,第二个为端口



    $res = $redis->connect('127.0.0.1', 6379);



    // test为发布的频道名称,hello,world为发布的消息



    $res = $redis->publish('test','hello,world');

    var_dump($res);

      

    }

    public function din(){

      //订阅  

      //echo time();

      ini_set('default_socket_timeout', -1);  //不超时  

      $redis = new \Redis();  

      $redis->connect('127.0.0.1', 6379);  

      $result = $redis->subscribe(array('test'), function ($instance,$channelName,$message){  

         echo $channelName, "==>", $message;

    }); 

      //echo $result;   

      //var_dump($result);

      //die;

      

    }

    //搜索 俱乐部

    function search(){

        $name = I('name');

        

        $info = D('Clubs')->Search($name);

        if($info !== false){

            $this->ajaxRet(array('status'=>1,'info'=>'搜索结果获取成功','data'=>$info));

        }else{

            $this->ajaxRet(array('info' => D('Clubs')->getError()));

        }

    }
    //实物 详情里推荐
    public  function recomtus(){
      

    }

   

}