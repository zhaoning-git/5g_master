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

</head>

<body>

  <div class="wrap">

    <ul class="nav nav-tabs">



      <li class="active"><a >积分兑换设置</li>

     

    </ul>

    <form method="post" class="form-horizontal js-ajax-form" action="<?php echo U('Jifenduihuan/xiugai');?>" enctype="multipart/form-data">

 

       

        <div class="control-group">

          <label class="control-label">商品名</label>

          <div class="controls">

            <input type="text" name="shopname" value="<?php echo ($findduihuan["shopname"]); ?>">

            <input type="hidden" name="id" value="<?php echo ($findduihuan["id"]); ?>">

  

            <span class="form-required">*</span>

          </div>

        </div>

         <div class="control-group">

          <label class="control-label">商品类型</label>

          <div class="controls">

          <select style="min-width: 100px;float: left;" name="type" onchange="jump(value)">

	                <option value="礼物" <?php if($findduihuan['type'] == '礼物'): ?>selected<?php endif; ?> >礼物</option>

				    <option value="特权"  <?php if($findduihuan['type'] == '特权'): ?>selected<?php endif; ?> >特权 </option>

					<option value="道具" <?php if($findduihuan['type'] == '道具'): ?>selected<?php endif; ?> >道具</option>

					<option value="虚拟物品" <?php if($findduihuan['type'] == '虚拟物品'): ?>selected<?php endif; ?> >虚拟物品</option>

					<option value="实物" <?php if($findduihuan['type'] == '实物'): ?>selected<?php endif; ?> >实物</option>

					<!-- <option value="限量" <?php if($findduihuan['type'] == '限量'): ?>selected<?php endif; ?> >限量</option> -->

			</select>

			 <select id="select1"    <?php if($findduihuan['type'] == '特权'): ?>style="min-width: 100px;float: left;" <?php else: ?> style="min-width: 100px;display: none;float: left;"<?php endif; ?>  name="llll">

			 	

			 	<?php if(is_array($cmf_coupon)): foreach($cmf_coupon as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>" <?php if($findduihuan['typefenid'] == $vo['id']): ?>selected<?php endif; ?> ><?php echo ($vo["title"]); ?></option><?php endforeach; endif; ?>   

				 

		

			</select>

			<select id="select2" <?php if($findduihuan['type'] == '道具'): ?>style="min-width: 100px;float: left;" <?php else: ?> style="min-width: 100px;display: none;float: left;"<?php endif; ?> name="dddd">

	            	<?php if(is_array($cmf_coupontow)): foreach($cmf_coupontow as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>" <if condition="$findduihuan['typefenid'] eq $vo['id']"><?php echo ($vo["title"]); ?></option><?php endforeach; endif; ?>   

				 

		    </select>

            <span class="form-required">*</span>

          </div>

        </div>

        

        <div class="control-group">

          <label class="control-label">商品图片</label>

          

          <div class="controls">

          	<?php if(is_array($img)): foreach($img as $key=>$vo): ?><img src="<?php echo ($vo); ?>" width="10%"><?php endforeach; endif; ?> 

        

          </div>

          

        </div>

        

        

        

         <div class="control-group">

          <label class="control-label">修改图片</label>

          <div class="controls">

            <input type="file" name="file[]" multiple="multiple">

       </div>

        </div>

        

        

       <!--  <div class="control-group">

          <label class="control-label">描述</label>

          <div class="controls">

            <input type="text" name="miaoshu" value="<?php echo ($findduihuan["miaoshu"]); ?>">

    

            <span class="form-required" >*</span>

          </div>

        </div> -->

          <div class="control-group">
            <label class="control-label">详情</label>
            <div class="controls">
              <script type="text/plain" id="content" name="miaoshu"><?php echo ($findduihuan["miaoshu"]); ?></script>                
            </div>
        </div>

        

        <div class="control-group">

          <label class="control-label">金币</label>

          <div class="controls">

            <input type="text" name="jinbi" value="<?php echo ($findduihuan["jinbi"]); ?>">

            <span class="form-required">*</span>

          </div>

        </div>

          <div class="control-group">

          <label class="control-label">新品首发</label>

          <div class="controls">

         <td> 是 <input type="radio"  name="product"  value="1" <?php if($findduihuan['product']==1) echo 'checked' ?> ></td>





           <td> 否 <input type="radio"  name="product"  value="0" <?php if($findduihuan['product']==0) echo 'checked' ?> ></td>

         <!--    <span class="form-required">*</span> -->



          </div>

        </div>

           

          

           

          



        

       

        <div class="control-group">

          <label class="control-label">热门推荐</label>

          <div class="controls">

         <td> 是 <input type="radio"  name="hots"  value="1" <?php if($findduihuan['hots']==1) echo 'checked' ?> ></td>





           <td> 否 <input type="radio"  name="hots"  value="0" <?php if($findduihuan['hots']==0) echo 'checked' ?> ></td>

         <!--    <span class="form-required">*</span> -->



          </div>

        </div>


        <div class="control-group">

          <label class="control-label">商品推荐</label>

          <div class="controls">

         <td> 是 <input type="radio"  name="recom"  value="1" <?php if($findduihuan['recom']==1) echo 'checked' ?> ></td>
           <td> 否 <input type="radio"  name="recom"  value="0" <?php if($findduihuan['recom']==0) echo 'checked' ?> ></td>

         <!--    <span class="form-required">*</span> -->



          </div>

        </div>

          <div class="control-group">

          <label class="control-label">限量首发</label>

          <div class="controls">

           <td> 是 <input type="radio"  name="quantity"  value="1" <?php if($findduihuan['quantity']==1) echo 'checked' ?> ></td>
           
           <td> 否 <input type="radio"  name="quantity"  value="0" <?php if($findduihuan['quantity']==0) echo 'checked' ?> ></td>

         <!--    <span class="form-required">*</span> -->



          </div>

        </div>

          <div class="control-group">

          <label class="control-label">银币</label>

          <div class="controls">

            <input type="text" name="yingbi" value="<?php echo ($findduihuan["yingbi"]); ?>">

            <span class="form-required">*</span>

          </div>

        </div>



        

        

        <!--<div class="control-group">

          <label class="control-label">说明</label>

          <div class="controls">

            <textarea name="remark" rows="2" cols="20" id="remark" class="inputtext" style="height: 100px; width: 500px;"><?php echo ($info['remark']); ?></textarea>

          </div>

        </div>-->

        

  

      <div class="form-actions">

       

     

          <button type="submit" class="" value="设置">设置</button>

     

      

      </div>



    </form>

  </div>



</body>
<script type="text/javascript" src="/public/js/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/public/js/ueditor/ueditor.all.min.js"></script>
  <script>
  //编辑器
        editorcontent = new baidu.editor.ui.Editor();
        editorcontent.render('content');
        try {
          editorcontent.sync();
        } catch (err) {}
        
function jump(dd){

	if(dd=='特权'){

//	 	alert(dd)

	 	document.getElementById("select1").style.display='block';

	 	document.getElementById("select2").style.display='none';

	}else if(dd=='道具'){

//	 	alert(dd)

	 	document.getElementById("select2").style.display='block';

	 	document.getElementById("select1").style.display='none';

	}else{

		document.getElementById("select1").style.display='none';

		document.getElementById("select2").style.display='none';

	}

}

</script>

</html>