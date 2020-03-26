<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <!-- Set render engine for 360 browser -->
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- HTML5 shim for IE8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <![endif]-->

        <link href="/public/simpleboot/themes/<?php echo C('SP_ADMIN_STYLE');?>/theme.min.css" rel="stylesheet">
        <link href="/public/simpleboot/css/simplebootadmin.css" rel="stylesheet">
        <link href="/public/js/artDialog/skins/default.css" rel="stylesheet" />
        <link href="/public/simpleboot/font-awesome/4.7.0/css/font-awesome.min.css"  rel="stylesheet" type="text/css">
        <style>
            .length_3{width: 180px;}
            form .input-order{margin-bottom: 0px;padding:3px;width:40px;}
            .table-actions{margin-top: 5px; margin-bottom: 5px;padding:0px;}
            .table-list{margin-bottom: 0px;}
        </style>
        <!--[if IE 7]>
        <link rel="stylesheet" href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome-ie7.min.css">
        <![endif]-->
        <script type="text/javascript">
        //全局变量
            var GV = {
                DIMAUB: "/",
                JS_ROOT: "public/js/",
                TOKEN: ""
            };
        </script>
        <!-- Le javascript
            ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="/public/js/jquery.js"></script>
        <script src="/public/js/wind.js"></script>
        <script src="/public/simpleboot/bootstrap/js/bootstrap.min.js"></script>
        <?php if(APP_DEBUG): ?><style>
                #think_page_trace_open{
                    z-index:9999;
                }
            </style><?php endif; ?>
    </head>
</head>
<body>
	<style type="text/css">
        *{
            margin: 0;
            padding:0;
        }
       /* .wrap{
            width: 600px;
            margin: 0px auto;
 
        }*/
        .menu{
            width: 600px;
            height: 30px;
            background: cornflowerblue;
            position: sticky;
            top:0px;
        }
        .menu ul li{
            float: left;
            list-style-type: none;
            padding: 0 40px;
        }
        .content ul li img:hover{
            transform: scale(1.2);/*当鼠标移动到图片上时实现放大功能*/
        }
        .content ul li{
            height: 100px;
            overflow: hidden;
            border-bottom: 1px solid lavender;
            background: white;
            list-style-type: none;
            transition-duration: 0.5s;
            margin: 10px 10px 5px 0;
 
        }
        .content ul li:hover{
            background-color: lavender;
            transition-duration: 0.5s;
        }
        .content .left{
            overflow: hidden;/*隐藏溢出图片内容*/
            transition-duration: 0.5s;
            width: 140px;
            height:80px;
            /*background: green;*/
            float: left;
            margin-right:20px;
        }
        .content .right{
            width:400px ;
            float: left;
            /*background: pink;*/
        }
        .right_top{
            height:60px;
        }
        .right_bottom{
            margin_top:50px;
        }
        .right_bottom_left span{
            color: darkgray;
            font-size: 12px;
        }
        .pi{
        	width: 100px;
        	height: 100px;
        	left: 100px;
        	float:right;
        	position: fixed;
        }
    </style>
<div class="wrap">
	<ul class="nav nav-tabs">
		<!-- <li><a href="<?php echo U('Zixun/index');?>">资讯列表</a></li> -->
		<li class="active"><a>爬取数据列表</a></li>
	</ul>
     
	<div class="">
        <a href="<?php echo U('Zixun/paquList',['type'=>'donqiudi']);?>">
          <button type="button" class="layui-btn">懂球帝足球</button>
        </a>
        
        <a href="<?php echo U('Zixun/paquList',['type'=>'leisu']);?>">
          <button type="button" class="layui-btn">雷速足球</button>
        </a>
        <a href="<?php echo U('Zixun/paquList',['type'=>'qqfootball']);?>">
          <button type="button" class="layui-btn">腾讯足球</button>
        </a>
        <a href="<?php echo U('Zixun/paquList',['type'=>'qqbasketball']);?>">
          <button type="button" class="layui-btn">腾讯篮球</button>
        </a>
        
        <a href="<?php echo U('Zixun/paquList',['type'=>'sinafootball']);?>">
          <button type="button" class="layui-btn">新浪足球</button>
        
        <a href="<?php echo U('Zixun/paquList',['type'=>'sinabasketball']);?>">
          <button type="button" class="layui-btn">新浪篮球</button>
        </a>
    </div>
	

	 
	  
</div>
<script src="/public/js/common.js"></script>
<script type="text/javascript" src="/public/js/content_addtop.js"></script>
<script src="/public/layui/layui.js"></script>
<link rel="stylesheet" type="text/css" href="/public/layui/css/layui.css">
<script type="text/javascript">
	var array = [];
	$("button").click(function(){
      $("li").each(function(){
        
        array.push($(this).attr('data_vv'));
      });
      let url = "<?php echo U('zixun/chaku');?>";
          $.ajax({
             url:url,
             data:{
               da:array
             },
             success:function(data){
              
             },
             error:function(data){
               
             }
        });


    });
 	
	// var data = '<?php echo ($data); ?>';
	// console.log(data);
	function dian(e){
		
		

    // $(document).ready(function(){
		$('#content').each(function(){
		console.log(99)
	// })
     
     
  });
		
	}	
	function shan(obj){
		var par = $(obj).parent().parent().remove();
      
	}
</script>
</body>
</html>