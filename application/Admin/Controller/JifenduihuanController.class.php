<?php



/**

 *

 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

use function Sodium\library_version_major;



class JifenduihuanController extends AdminbaseController {

    protected $experlevel_model;



    function _initialize() {

        parent::_initialize();

        $this->experlevel_model = D("Common/Experlevel");

    }



    // 上传图片

    function updateimg($file,$dzi=''){

        $namearr=$file['name'];

        $tnamearr=$file['tmp_name'];

        $ddd=date("Y-m-dH",time());

        $dir=$dzi.$ddd;

        if (!file_exists($dir)){

            mkdir($dir,0777,true);

        } else{

        }

        $arr='';



        foreach($namearr as $k=>$v){

            $rand=time().rand(100000000, 999999999);

            $houzui=".".substr(strrchr($v, '.'), 1);

            // var_dump($dir.'/'.$rand.'.'.$houzui);

            // exit;



            if(move_uploaded_file($tnamearr[$k],$dir.'/'.$rand.$houzui)){







                $arr='http://5g.appyunwei.cn/public/tupian'.'/'.$ddd.'/'.$rand.$houzui.','.$arr;

            }

            //var_dump();-



        }

        //http://47.98.97.133/public/tupian/1574071975631066830.jpg,http://47.98.97.133/public/tupian/1574071975435415113.jpg,

        return $arr;

    }



    function del(){

        $id=intval($_GET['id']);

        $arr=array('sta'=>0);

        $dd=M('duihuan')->where("id='{$id}'")->save($arr);

        if($dd){



            $this->success('删除成功！');

        }else{

            $this->error('删除失败！');



        }

    }

    //商品列表

    function indexlist(){

        

        //兑换商城列表  duihuan

       
         $show_model = M('duihuan');
         $count = M('duihuan')->where(array('type'=>'实物'))->count();
         $page = $this->page($count, 20);
          
         $page = $this->page($count, 20);
         $dd=M('duihuan')
                 ->where("sta=1")
                 ->limit($page->firstRow . ',' . $page->listRows)
                 ->order('id desc')
                 ->where(array('type'=>'实物'))
                 ->order('sort asc')
                 ->select();

         
        foreach($dd as $k=>&$v){

            $cmf_coupon=M('coupon')->where("id=".$v['typefenid'])->find();

            $v['zifenlei']=$cmf_coupon['title'];

            $pieces = explode(",", $v['img']);

            $v['img']=$pieces[0];



            //判断是否为实物

            $is_shi = M('duihuan')->field('type')->where(array('id'=>$v['id']))->find();

            if($is_shi['type'] == '实物'){

                $huohao = M('sku')->field('huohao')->where(array('duihuan_id'=>$v['id']))->find();

                $v['huohao'] =$huohao['huohao'];

                $v['sku_status'] = 1;

            }


            // var_dump($cmf_coupon);die;
            //获取商品分类   衣服还是鞋子 等等

            $is_type = M('cat')->field('type')->where(array('id'=>$v['cat_id']))->find();

            $v['typename'] = $is_type['type'];

        }

        
        
        $this->assign('lists',$dd);
        $this->assign('page',$page->show('Admin'));
        $this->display();

    }

    //商品添加

    function index(){



        $cmf_coupon=M('coupon')->where('shop_type=1')->select();

        $cmf_coupontow=M('coupon')->where('shop_type=2')->select();

        $cat = D('cat')->select();



        $dd=M('duihuan')->find();

        $this->assign('cmf_coupon',$cmf_coupon);

        $this->assign('cmf_coupontow',$cmf_coupontow);

        $this->assign('duihuan',$dd);

        $this->assign('cat',$cat);

        $this->display();

    }

    //联动

    function dong(){

        $key = $_POST['key']; //获取值

        //查询

        $address = D('coupon')->where(array('shop_type'=>$key))->select();

        // $address[1] = array('成都','绵阳','德阳');

        // $address[2] = array('石家庄','唐山','秦皇岛');

        // $address[3] = array('长沙','株洲','湘潭');

        if(!empty($address)){ //有值，组装数据

            $result['status'] = 200;

            $result['data'] = $address;

        }else{ //无值，返回状态码220

            $result['status'] = 220;

        }

        echo json_encode($result); //返回JSON数据



    }



    //查看sku库存

    function ku(){

        $id=intval($_GET['id']);

        $info = M('sku')->where(array('duihuan_id'=>$id))->select();



        foreach ($info as $key =>&$val){

            $info[$key]['shujson']  = json_decode($val['shujson_list']);



        }

        foreach ($info as $key =>$val){

           foreach ($val['shujson'] as $ke=>$va){

               $info[$key]['shu'][] = M('duihuan_attribute')->field('shuzhi')->where(array('id'=>$va))->find();

           }



        }

        $this->assign('info',$info);

        $this->display();

    }



    //sku即点即改

    function jiEdit(){

        $value = I('value');  //修改后的值

        $id = I('id');       //所在行的id

        $field = I('field'); //字段



        $res = M('Sku')->where(array('id'=>$id))->save(array($field=>$value));

        if($res){

            $this->ajaxReturn(array('status'=>1,'info'=>'修改成功'));

        }else{

            $this->ajaxReturn(array('status'=>0,'data'=>'修改失败'));

        }



    }



    //添加执行

    function up(){



        $parem = I('post.');
        //var_dump($parem);die;
        $files = $_FILES['file'];



        //  $img=$this->updateimg($_FILES['file'],'/www/web/dq_ta01_com/public_html/public/tupian/');

        $img = $this->shangtu($files);

     

        if(empty($parem['shopname'])){

         $this->error('商品名称为空');

        }

        // if(empty($parem['jinbi'])){

        //  $this->error('金币为空');

        // }

        // if(empty($parem['yingbi'])){

        //  $this->error('银币为空');

        // }

        if($parem['type'] == 1){

            $parem['type']= '特权';

        }

        if($parem['type'] == 2){

            $parem['type'] = '道具';

        }

        if($parem['type'] == 3){

            $parem['type'] = '礼物';

        }

        if($parem['type'] == 4){

            $parem['type'] =  '虚拟物品';

        }

        if($parem['type'] == 5){

            $parem['type'] = '实物';

            //根据传值的分类id   查询实物分类名  查询分类id

            $couponInfo = M('coupon')->field('title')->where(array('id'=>$parem['paymen']))->find();

            $catid = M('cat')->where(array('type'=>$couponInfo['title']))->find();



//            //生成货号

//            $huohao = $this->huohao();

        }



      



        $data=array(

            'jinbi'=>$parem['jinbi'],

            'yingbi'=>$parem['yingbi'],

            'typefenid'=>$parem['paymen'],

            'type'=>$parem['type'],

            'img'=>$img,

            'sta'=>1,

            'shopname'=>$parem['shopname'],

            'miaoshu'=>$parem['miaoshu'],

            'cat_id' => $parem['cat_id'],
            'sort'=> $parem['sort'],

        );

        // if(empty($catid)){
        //      $data['cat_id'] = 0;
          

        // }else{

           
        //       $data['cat_id'] = $catid['id'];

        // }

       // var_dump($data);die;

//        if(!empty($huohao)){

//            $data['huohao'] = $huohao;

//        }



        $dd=M('duihuan')->add($data);

        if($dd){

            $this->success('设置成功！',U('jifenduihuan/indexlist'));

        }else{

            $this->error('设置失败！');

        }



    }

    //商品列表 编辑

    function edit(){



        //兑换商城列表

        $id=intval($_GET['id']);

        $findduihuan=M('duihuan')->where("id={$id}")->find();

        $img=explode(",", $findduihuan['img']);



        $cmf_coupon=M('coupon')->where('shop_type=1')->select();

        $cmf_coupontow=M('coupon')->where('shop_type=2')->select();



        $this->assign('img',$img);

        $this->assign('cmf_coupon',$cmf_coupon);

        $this->assign('cmf_coupontow',$cmf_coupontow);

        $this->assign('findduihuan',$findduihuan);



        $this->display();



    }

    //图片上传

    public function shangtu($files){

         $upload = new \Think\Upload();// 实例化上传类

            $upload->maxSize =     3145728 ;// 设置附件上传大小

            $upload->exts    =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型

            $upload->autoSub = true; // 开启子目录保存 并以日期（格式为Ymd）为子目录

            $upload->subName = array('date','Ymd');

            $upload->rootPath =  './data/upload/showData/';                       // 设置根路径

            $upload->savePath =  '';     // 设置附件上传根目录

            $upload->saveName = array('uniqid',''); // 设置附件上传（子）目录

            // 上传文件

            $info   =   $upload->uploadOne($files);

          //  $img =addHttp("/data/upload/showData/".$info['savepath'].$info['savename']);

            $img ="/data/upload/showData/".$info['savepath'].$info['savename'];

            return $img;

    }

    //商品修改

    function xiugai(){

        $id = I('post.id');

        $jinbi = I('post.jinbi');

        $yingbi = I('post.yingbi');

        $shopname= I('post.shopname');

        $type= I('post.type');

        $product = I('post.product');

        $hots = I('post.hots');

        $miaoshu= I('post.miaoshu');

        if($type=='特权'){

            $typefenid=I('post.llll');

        }elseif($type=='道具'){



            $typefenid=I('post.dddd');

        }else{

            $typefenid='';

        }



        if(is_array($_FILES['file']['name'])){

            $upload = new \Think\Upload();// 实例化上传类

            $upload->maxSize =     3145728 ;// 设置附件上传大小

            $upload->exts    =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型

            $upload->autoSub = true; // 开启子目录保存 并以日期（格式为Ymd）为子目录

            $upload->subName = array('date','Ymd');

            $upload->rootPath =  './data/upload/showData/';                       // 设置根路径

            $upload->savePath =  '';     // 设置附件上传根目录

            $upload->saveName = array('uniqid',''); // 设置附件上传（子）目录

            // 上传文件

            $info   =   $upload->uploadOne($_FILES['file']);
            // $img =addHttp("/data/upload/showData/".$info['savepath'].$info['savename']);

            $img ="/data/upload/showData/".$info['savepath'].$info['savename'];
//            $img=$this->updateimg($_FILES['file'],'/www/web/dq_ta01_com/public_html/public/tupian/');

            if($info){

                $data=array(

                    'id'=>$id,

                    'jinbi'=>$jinbi,

                    'yingbi'=>$yingbi,

                    'typefenid'=>$typefenid,

                    'type'=>$type,

                    'img'=>$img,

                    'sta'=>1,

                    'shopname'=>$shopname,

                    'miaoshu'=>$miaoshu,

                    'product' => $product,

                    'hots' =>$hots,

                );

            }else{

                $data=array(

                    'id'=>$id,

                    'jinbi'=>$jinbi,

                    'yingbi'=>$yingbi,

                    'typefenid'=>$typefenid,

                    'type'=>$type,

                    'sta'=>1,

                    'shopname'=>$shopname,

                    'miaoshu'=>$miaoshu,

                     'product' => $product,

                     'hots' =>$hots,

                );

            }



        }else{

            $data=array(

                'id'=>$id,

                'jinbi'=>$jinbi,

                'yingbi'=>$yingbi,

                'typefenid'=>$typefenid,

                'type'=>$type,

                'sta'=>1,

                'shopname'=>$shopname,

                'miaoshu'=>$miaoshu,

                'miaoshu'=>$miaoshu,

                'product' => $product,

                'hots' =>$hots,

            );





        }


        $dd=M('duihuan')->where("id='{$id}'")->save($data);

        if($dd){

            $this->success('设置成功！',U('jifenduihuan/indexlist'));

        }else{

            $this->error('设置失败！');

        }

    }



    //评价

    function evalist(){

        $goods_id=intval($_GET['id']);

        $page = $this->page(M('duihuan_eva')->count(), 10);

        $prefix = C('DB_PREFIX');

        $info = M('duihuan_eva e')

            ->join("{$prefix}users u on u.id = e.uid")

            ->join("{$prefix}duihuan_order o on e.order_no = o.order_no")

            ->field('e.id,e.goods_id,e.evaluate,e.eva_pic,e.create_time,u.avatar,u.user_nicename,o.id as oid')

            ->where(array('e.goods_id'=>$goods_id))

            ->limit($page->firstRow . ',' . $page->listRows)

            ->select();



        foreach ($info as $k=>&$v){

            $goodsname = M('duihuan')->field('shopname')->where(array('id'=>$v['goods_id']))->find();

            $v['goods_name'] = $goodsname['shopname'];



            $detailInfo = M('order_itme')->field('gui_json')->where(array('goods_id'=>$v['goods_id'],'order_id'=>$v['oid']))->select();

            foreach ($detailInfo as $key=>&$val){

                $v['gui_json'] = json_decode($val['gui_json'],true);

            }

            $v['eva_pic'] = getImgVideo($v['eva_pic']);

            unset($v['oid']);

        }



        $this->assign('info',$info);

        $this->assign("page", $page->show('Admin'));

        $this->display();

    }

    //商品属性 列表

    public function shux(){

        $data = D('attribute bute')

                ->field('bute.attribute_name,cat.type,bute.id')

                ->join('cmf_cat cat ON cat.id = bute.cat_id')

                ->select();

        $this->assign('cat',$data);

        $this->display();

    }

    //商品属性 添加

    public function addshux(){

        if(IS_POST){

           $parem = I('post.');

           if(empty($parem['attribute_name'])){

              $this->error('属性参数为空');

           }

           if(empty($parem['cat_id'])){

              $this->error('类型为空');

           }

           //添加库

           $data  = D('attribute')->add($parem);

           if(!$data){

             $this->error('添加失败');

           }

           $this->success('添加成功');

        }

        $cat = D('cat')->select();

        $this->assign('cat',$cat);

        $this->display(); 



    }

    //属性删除

    public function shuxdel(){

      $parem = I('get.');

      $del = D('attribute')->where(array('id'=>$parem['id']))->delete();

      if($del){

        $this->success('删除成功');

      }

      $this->error('删除失败');

    }

    //属性编辑

    public function shuxedit(){

        if(IS_POST){

           $parem = I('post.');

           $up = [

              'attribute_name' => $parem['attribute_name'],

              'cat_id' => $parem['cat_id'],

           ];

           $up = D('attribute')->where(array('id'=>$parem['id']))->save($up);

           if($up){

             $this->success('修改成功');

           }

           $this->error('修改失败');

        }

        $parem = I('get.');

        $up = D('attribute')->where(array('id'=>$parem['id']))->find();

        $cat = D('cat')->select();

        $this->assign('up',$up);

        $this->assign('cat',$cat);

        $this->display();



    }

    //商品类型

    public function typeslit(){

        // $da = '2,10,11,23,90';

        // $da2 = '11,90,10,23,2';



        // $a1=explode(',', $da);

        // $a2 = explode(',',$da2);

        // sort($a1);

        // sort($a2);



        // if($a1 == $a2){

        //   echo '相等';die;

        // }

        // echo '不相等';die;

        // print_r($result);

        //   die;

       $data = D('cat')->select(); 

       $this->assign('data',$data);

       $this->display();

    }

    //商品类型添加

    public function typeadd(){

        if(IS_POST){

           $parem = I('post.');

           

           $data = D('cat')->add($parem);

           if($data){

             $this->success('修改成功'); 

           }

           $this->error('修改失败');

        }

        $this->display();

    }

    //商品类型删除

    public function typedel(){

        $parem = I('get.');

        $del = D('cat')->where(array('id'=>$parem['id']))->delete();

        if($del){

          $this->success('删除成功');

        }

        $this->error('删除失败');

    }

    //商品类型修改

    public function typeedit(){

        if(IS_POST){

           $parem = I('post.');

           $up = [

              'type' => $parem['type'],

              

           ];

           $up = D('cat')->where(array('id'=>$parem['id']))->save($up);

           if($up){

             $this->success('修改成功');

           }

           $this->error('修改失败');

        }

        $parem = I('get.');

        $up = D('cat')->where(array('id'=>$parem['id']))->find();

       

        $this->assign('up',$up);

        

        $this->display();



    }

    //配置sku

    public function peisku(){

        $parem = I('get.');

        //获取分类

        $cat = D('duihuan')->where(array('id'=>$parem['id']))->find();

        if(!$cat){

           $this->error('无商品');

        }

        $cat_id = $cat['cat_id'];

        // //查询分类

        // $fenlei = D('cat')->where(array('id'=>$cat_id))->find();

        // if(!$fenlei){

        //    $this->error('无分类');

        // }

        $attr = D('attribute')->where(array('cat_id'=>$cat_id))->select();

        foreach ($attr as $key => $value) {

           $attr[$key]['attrs'] = D('duihuan_attribute')->where(array('shuzhi_id'=>$value['id'],'duihuan_id'=>$parem['id']))->select();

        }

       //var_dump($attr);die;

        $this->assign('data',$attr);

        $this->display();

    }

    //隐私设置

    public function privacy(){

        $data = D('privacy')->where('status=1')->select();

        $this->assign('data',$data);

        $this->display();

    }

    //隐私添加

    public function privacyadd(){

            $time = getdate();
            if(IS_POST){

                $page = I("post.post");
                $parem = I('post.');
               //$file = $_FILES['file'];

                $parem['add_time'] = $time[0];

                $parem['content'] = htmlspecialchars_decode($page['post_content']);
                unset($parem['post']);
              
                $privacy = M('privacy');

                $result = $privacy->add($parem); // 写入数据到数据库 


                if(0 < $result){
                    // 如果主键是自动增长型 成功后返回值就是最新插入的值
                    $this->success('添加成功');
                }
                $this->error('添加失败');


            }

            $this->display();


    }

    //隐私编辑
    public function privacyedit(){
        $time = getdate();
        if(IS_POST){
            $parem = I('post.');
            $page = I("post.post");
            $privacy = M("privacy"); 

            $parem['content'] = htmlspecialchars_decode($page['post_content']);
            unset($parem['post']);

             $parem['update_time'] = $time[0];

             // 根据表单提交的POST数据创建数据对象

             $where = array("id"=>$parem['id']);
             //$privacy->create();
             $data = $privacy->where($where)->save($parem); // 根据条件保存修改的数据

             //print_r($privacy->_sql());exit;
                   
             if($data){

                 $this->success('修改成功');
             }
              $this->error('修改失败');
        }
        $parem = I('get.');
        //查询 
        $where['id'] = $parem['id'];
        $data = D('privacy')->where($where)->find();

        $this->assign("data",$data);
        //$this->assign("classify",$classify);
        $this->display();


    }

    //商品实物排序

    public function shiindex (){

    $data['list'] = $this->fenindd('shiwu');

     $data['typess'] = 1;



    $this->assign('data',$data);

    $this->display();

        //查询所有实物的商品



    }

    //特权排序

    public function teindex(){

        $data['list'] = $this->fenindd('te');

        $data['typess'] = 2;

       

        $this->assign('data',$data);

        $this->display('shiindex');

    }

    //限量排序

    public function xianindex(){

       $data['list'] = $this->fenindd('xian');

        $data['typess'] = 3;

       

        $this->assign('data',$data);

        $this->display('shiindex'); 

    }

    //道具排序

    public function daoindex(){

         $data['list'] = $this->fenindd('dao');

        $data['typess'] = 4;

       

        $this->assign('data',$data);

        $this->display('shiindex'); 

    }

    //虚拟物品排序

    public function xuindex(){

       $data['list'] = $this->fenindd('xu');

        $data['typess'] = 5;

       

        $this->assign('data',$data);

        $this->display('shiindex'); 

    }

    //礼物排序

    public function liindex(){

        $data['list'] = $this->fenindd('li');

        $data['typess'] = 6;

       

        $this->assign('data',$data);

        $this->display('shiindex'); 

    }

    //新品排序

    public function xinindex(){



        $data['list'] = $this->fenindd('xin');

        $data['typess'] = 7;

       

        $this->assign('data',$data);

        $this->display('shiindex'); 

    }

     //新品排序

    public function hotindex(){



        $data['list'] = $this->fenindd('hot');

        $data['typess'] = 7;

       

        $this->assign('data',$data);

        $this->display('shiindex'); 

    }

    public  function fenindd($type){

      if(empty($type))

         $this->error('参数错误');

      //查询实物的商品

      

      if($type == 'shiwu'){

         $ids = '实物';

      }

       //特权

      if($type == 'te'){

         $ids = '特权';

      }

      if($type == 'xian'){

         $ids = '限量';

      }

      if($type == 'dao'){

         $ids = '道具';

      }

       if($type == 'xu'){

         $ids = '虚拟物品';

      }

       if($type == 'li'){

         $ids = '礼物';

      }



       $dd = D('duihuan')->where(array('type'=>$ids,'sta'=>1))->order('sort asc')->select();

       //新品首发

       if($type == 'xin'){

         $ids = '实物';

         $dd = D('duihuan')->where(array('type'=>$ids,'sta'=>1,'product'=>1))->order('sort asc')->select();

        }

        //新品首发

        if($type == 'hot'){

         $ids = '实物';

         $dd = D('duihuan')->where(array('type'=>$ids,'sta'=>1,'hots'=>1))->order('sort asc')->select();

        }

        foreach($dd as $k=>&$v){

            $cmf_coupon=M('coupon')->where("id=".$v['typefenid'])->find();

            $v['zifenlei']=$cmf_coupon['title'];

            $pieces = explode(",", $v['img']);

            $v['img']=$pieces[0];

             

            //判断是否为实物

            $is_shi = M('duihuan')->field('type')->where(array('id'=>$v['id']))->find();

            if($is_shi['type'] == $ids){

                $huohao = M('sku')->field('huohao')->where(array('duihuan_id'=>$v['id']))->find();

                $v['huohao'] =$huohao['huohao'];

                $v['sku_status'] = 1;

            }



            //获取商品分类   衣服还是鞋子 等等

            $is_type = M('cat')->field('type')->where(array('id'=>$v['cat_id']))->find();

            $v['typename'] = $is_type['type'];

        }

        return $dd;

     

    }
    //用户协议
    public function ynoindex(){

        $data = M('agreement')->select();
        foreach ($data as $key => $value) {
            $data[$key]['content'] = htmlspecialchars_decode($value['content']);
        }
        $this->assign('list',$data);

        $this->display(); 
    }
    //用户协议删除
    public  function yondel(){

        $id=intval($_GET['id']);

       // $arr=array('status'=>0);
    
        $dd = M('agreement')->where(array('id'=>$id))->delete();

        if($dd){



            $this->success('删除成功！');

        }else{

            $this->error('删除失败！');



        }
    }
    //用户协议添加
    public  function yonadd(){
           if(IS_POST){
            //查询是否有数据
            $da  = M('agreement')->count();
          
            if($da >=1){
              
              $this->error('请勿重复添加',U('jifenduihuan/ynoindex'));
              
            }
        
           $parem = I('post.');

           if(empty($parem['content'])){

              $this->error('内容参数为空');

           }

           if($parem['status'] == ''){

              $this->error('状态参数为空');

           }

           //添加库

           $data  = D('agreement')->add($parem);

           if(!$data){

             $this->error('添加失败');

           }

           $this->success('添加成功',U('jifenduihuan/ynoindex'));

        }

        $this->display(); 


    }
    //用户协议修改 查询
    public function yongfid(){
      $parem = I('get.');
      $data = M('agreement')->where(array('id'=>$parem['id']))->find();
      $data['content'] = htmlspecialchars_decode($data['content']);
      $this->assign('data',$data);
      $this->display();

    }
    //用户协议修改
    public function yong_post(){
        $parem = I('post.');
        if(empty($parem['content'])){

              $this->error('内容参数为空');

           }

        if($parem['status'] == ''){

              $this->error('状态参数为空');

        }
        $data = [
          'content' => $parem['content'],
          'status' => $parem['status'],

        ];

        $da  =  M('agreement')->where(array('id'=>$parem['id']))->save($data);
        if($da){
           $this->success('修改成功',U('jifenduihuan/ynoindex'));
        }
        $this->error('修改失败');

    }

}

