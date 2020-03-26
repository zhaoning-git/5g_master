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
  .levelname{margin-top: 5px;font-size: 1.2em;color: red; font-weight:bold;}
  .valBox{width: 50px;}
</style>
<body>
  <div class="wrap">
    <ul class="nav nav-tabs">
      <li class="active"><a>添加特权</a></li>
    </ul>
    <form method="post" class="form-horizontal js-ajax-form" action="<?php echo U('Userlevel/addUserpriv');?>">
      <fieldset>
        
        <div class="control-group">
          <label class="control-label">等级名称:</label>
          <div class="controls levelname">
            <?php echo ($info["name"]); ?>
          </div>
        </div>
        
        <label class="control-label">请选择特权:</label>
        <?php if(is_array($priv)): $i = 0; $__LIST__ = $priv;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="control-group">
            <div class="controls">
                <label class="checkbox inline" for="priv_<?php echo ($vo["id"]); ?>">
                  <input type="checkbox" name="priv[<?php echo ($vo["id"]); ?>]" value="<?php echo ($vo["id"]); ?>" id="priv_<?php echo ($vo["id"]); ?>" <?php echo ($vo["checked"]); ?> ><?php echo ($vo["priv_title"]); ?>
                </label>
                <?php if(!empty($vo["value"])): ?><input type="text" name="value[<?php echo ($vo["id"]); ?>]" value="<?php echo ((isset($vo["values"]) && ($vo["values"] !== ""))?($vo["values"]):0); ?>" class="valBox"><?php endif; ?>
            </div>
          </div><?php endforeach; endif; else: echo "" ;endif; ?>
        
        
        
      </fieldset>
      <div class="form-actions">
        <input type="hidden" name="id" value="<?php echo ($info["id"]); ?>">
        <button type="submit" class="btn btn-primary js-ajax-submit"><?php echo L('ADD');?></button>
        <a class="btn" href="<?php echo U('Userlevel/index');?>"><?php echo L('BACK');?></a>
      </div>
    </form>
  </div>
  <script src="/public/js/common.js"></script>
  <script type="text/javascript" src="/public/js/content_addtop.js"></script>
  
  <script>
  	
	
  </script>
  
  
  
</body>
</html>