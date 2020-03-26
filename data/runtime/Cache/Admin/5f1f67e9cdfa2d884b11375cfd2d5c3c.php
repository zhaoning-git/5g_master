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
<!-- <head>
    <meta charset="utf-8">
    <title></title>
    <style>
        .container {
            width: 60%;
            margin: 10% auto 0;
            background-color: #f0f0f0;
            padding: 2% 5%;
            border-radius: 10px
        }

        ul {
            padding-left: 20px;
        }

            ul li {
                line-height: 2.3
            }

        a {
            color: #20a53a
        }
    </style>
</head> --><body>
  <div class="wrap">
    <ul class="nav nav-tabs">

      <li class="active"><a >商品添加</li>

    </ul>
    <form method="post" class="form-horizontal js-ajax-form" action="<?php echo U('Jifenduihuan/up');?>" enctype="multipart/form-data">


        <div class="control-group">
          <label class="control-label">商品名</label>
          <div class="controls">
            <input type="text" name="shopname" value="">

            <span class="form-required">*</span>
          </div>
        </div>
         <div class="control-group">
          <label class="control-label">排序</label>
          <div class="controls">
            <input type="text" name="sort" value="">

            <span class="form-required">*</span>
          </div>
        </div>
         <div class="control-group">
          <label class="control-label">商品类型</label>
          <div class="controls">
          <select style="min-width: 100px;float: left;" name="type" class="area-select" id='address' >
	                <option value="3">礼物</option>
				          <option value="1" >特权 </option>
					        <option value="2">道具</option>
					        <option value="4">虚拟物品</option>
					        <option value="5">实物</option>
					        <!-- <option value="6">限量</option> -->
			    </select>
           <select class="area-select" id="city" name="paymen">
             <option>请选择</option>
           </select>
		<!-- 	 <select id="select1" style="min-width: 100px;display: none;float: left;" name="llll">
			 	<?php if(is_array($cmf_coupon)): foreach($cmf_coupon as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["title"]); ?></option><?php endforeach; endif; ?>


			</select>
			<select id="select2" style="min-width: 100px;display: none;float: left;" name="dddd">
	            	<?php if(is_array($cmf_coupontow)): foreach($cmf_coupontow as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["title"]); ?></option><?php endforeach; endif; ?>

		    </select> -->
          <span class="form-required">*</span>
          </div>
        </div>
         <div class="control-group">
          <label class="control-label">商品图片</label>
          <div class="controls">
            <input type="file" name="file" multiple>
       </div>
        </div>
        
       <!--  <div class="control-group">
          <label class="control-label">描述</label>
          <div class="controls">
            <input type="text" name="miaoshu" value="">
    
            <span class="form-required" >*</span>
          </div>
        </div> -->

        <div class="control-group">
            <label class="control-label">详情</label>
            <div class="controls">
              <script type="text/plain" id="content" name="miaoshu"></script>                
            </div>
        </div>

         <div class="control-group">
          <label class="control-label">类型（仅添加实物时使用）</label>
          <div class="controls">
           <select style="min-width: 100px;float: left;" name="cat_id" class="area-select" id='address' >
            <option value="0">请选择</option>
            <?php if(is_array($cat)): foreach($cat as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["type"]); ?></option><?php endforeach; endif; ?>
          </select>
    
            <span class="form-required" >*</span>
          </div>
        </div>
        
        <div class="control-group">
          <label class="control-label">金币</label>
          <div class="controls">
            <input type="text" name="jinbi" value="">
            <span class="form-required">*可兑换的金币</span>
          </div>
        </div>
        
          <div class="control-group">
          <label class="control-label">银币</label>
          <div class="controls">
            <input type="text" name="yingbi" value="">
            <span class="form-required">*</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label">新品首发</label>
          <div class="controls">
            是 <input type="radio" name="product" value="1">
            否 <input type="radio" name="product" value="0" checked>
            <span class="form-required">*</span>
          </div>
        </div>
         <div class="control-group">
          <label class="control-label">热门推荐</label>
          <div class="controls">
            是 <input type="radio" name="hots" value="1">
            否 <input type="radio" name="hots" value="0" checked>
            <span class="form-required">*</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label">商品推荐</label>
          <div class="controls">
            是 <input type="radio" name="recom" value="1">
            否 <input type="radio" name="recom" value="0" checked>
            <span class="form-required">*</span>
          </div>
        </div>
          <div class="control-group">
          <label class="control-label">限量首发</label>
          <div class="controls">
            是 <input type="radio" name="quantity" value="1">
            否 <input type="radio" name="quantity" value="0" checked>
            <span class="form-required">*</span>
          </div>
        </div>
      <div class="form-actions">
          <button type="submit" class="" value="设置">设置</button>
      </div>
    </form>
  </div>

</body>
<script src="/public/layui/layui.js"></script>
<script type="text/javascript" src="/public/js/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/public/js/ueditor/ueditor.all.min.js"></script>
    
<link rel="stylesheet" type="text/css" href="/public/layui/css/layui.css">
  <script>
    //编辑器
        editorcontent = new baidu.editor.ui.Editor();
        editorcontent.render('content');
        try {
          editorcontent.sync();
        } catch (err) {}
  
// function jump(dd){
// 	if(dd=='特权'){
// //	 	alert(dd)
// 	 	document.getElementById("select1").style.display='block';
// 	 	document.getElementById("select2").style.display='none';
// 	}else if(dd=='道具'){
// //	 	alert(dd)
// 	 	document.getElementById("select2").style.display='block';
// 	 	document.getElementById("select1").style.display='none';
// 	}else{
// 		document.getElementById("select1").style.display='none';
// 		document.getElementById("select2").style.display='none';
// 	}

// }

 $(function(){
    $(".sku").hide();
    //初始化数据
    var url = "<?php echo U('Jifenduihuan/dong');?>";
    $("#address").change(function(){ //监听下拉列表的change事件
        var address = $(this).val(); //获取下拉列表选中的值
        //发送一个post请求
        $.ajax({
            type:'post',
            url:url,
            data:{key:address},
            dataType:'json',
            success:function(data){ //请求成功回调函数
                var status = data.status; //获取返回值
                var address = data.data;
                //var address = JSON.parse(addres);

                if(status == 200){ //判断状态码，200为成功
                    var option = '';
                    for(var i=0;i<address.length;i++){ //循环获取返回值，并组装成html代码
                        var IDkkk = address[i].title;
                        var id = address[i].id;
                        option +='<option value="'+id+'">'+IDkkk+'</option>';
                    }
                }else{
                    var option = '<option>请选择</option>'; //默认值
                }
                $("#city").html(option); //js刷新第二个下拉框的值
            },
        });

    });
});
</script>
</html>