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
<style type="text/css">
  .hoverSTTTTTT{
    background-color: #2eafbb;
  }
</style>
</head>
<body>
    <div class="wrap">
        <ul class="nav nav-tabs">
            <li class="active"><a >商品列表</a></li>
            <li><a href="<?php echo U('Jifenduihuan/index');?>">1</a></li>
        </ul>
        <form method="post" class="js-ajax-form" action="">
            

            <table class="table table-hover table-bordered">
                <thead>
                    
                </thead>
                <tbody class="ceshi">

                <?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr  >
                          <td style="color: red"><?php echo ($vo['attribute_name']); ?></td>
                          <?php if(is_array($vo['attrs'])): foreach($vo['attrs'] as $key=>$vos): ?><!--  <td  onClick="att('<?php echo ($vos['shuzhi']); ?>')"><input type="" class="btn btn-primary " value=""> -->
                               
                                <td  class ="sd"><a onClick="att(this,'<?php echo ($vos['shuzhi']); ?>')" data-id="d12" class="btn"><?php echo ($vos['shuzhi']); ?></a></td> 
                               
                           </td><?php endforeach; endif; ?>
                      </tr><?php endforeach; endif; ?>
                <div>
                    
                </div>
                </tbody>
            </table>
            <div class="pagination"><?php echo ($page); ?></div>
           
        </form>
      
    </div>
  <div class="wrap">
        <div class="layui-row">
            
            <div class="layui-col-md3">
                <table  class="table table-hover table-bordered">
                   <tr class="ss">
                        <?php if(is_array($data)): foreach($data as $key=>$vo): ?><td style="width: 100%" class="items"><?php echo ($vo['attribute_name']); ?></td><?php endforeach; endif; ?>
                        <td style="left: 1000px">库存</td>
                        <td style="width: 100%">银币</td>
                        <td style="width: 100%">金币</td>
                   </tr>
                    <tr class="op">

                    </tr>
                   
                </table>
                
            </div>
        </div>
  </div>
    
    <script src="/public/js/common.js"></script>
    <script src="/public/layui/layui.js"></script>
    <link rel="stylesheet" type="text/css" href="/public/layui/css/layui.css">
</body>
</html>
<script type="text/javascript">
    let shu = []; 
    let  su = 3;
    let sku = 0;
    function att(obj,shuzhi){
     

       $(obj).eq($(this).index()).css('background', '#2eafbb').siblings().css('background', '#fff');
      
        
      
        shu.push(shuzhi);
          var option = '<td>'+'<input type="text" >'+'</td><td>'+'<input type="text" >'+'</td><td>'+'<input type="text" >'+'</td>'
         for(var j = 0, len = shu.length; j < len; j++) {
          var newcell = shu[j];
          option += '<td style="width: 100px" class="item">'+newcell+'</td>'; //默认值
           if(sku <= su){
             //option +='<tr>'+23+'</tr>';
           //  $(".op").html(option);
          }

          $(".op").html(option);
        }
          su++;
        
         
       
    }
     //获取长度
     let chu =  $(".ss").children(".items").length;
     sku = Number(su) + Number(chu);
    
      
    // console.log(shu) 
</script>