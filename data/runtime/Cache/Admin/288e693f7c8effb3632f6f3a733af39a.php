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
	<div class="wrap">
		<ul class="nav nav-tabs">
			<li ><a href="<?php echo U('Guide/index');?>">列表</a></li>
			<li class="active"><a >添加</a></li>
		</ul>
		<form method="post" class="form-horizontal js-ajax-form" action="<?php echo U('Guide/add_post');?>">
			<fieldset>
                <input type="hidden" name="type" value="<?php echo ($type); ?>">
				<div class="control-group">
					<label class="control-label">序号</label>
					<div class="controls">
						<input type="text" name="orderno" value="0">
						<span class="form-required"></span>
					</div>
				</div>
                <?php if($type == 0): ?><div class="control-group">
					<label class="control-label">图片</label>
					<div class="controls">
                        <input type="hidden" name="thumb" id="thumb2" value="">
                        <a href="javascript:void(0);" onclick="flashupload('thumb_images', '附件上传','thumb2',thumb_images,'1,jpg|jpeg|gif|png,1,,,1','','','');return false;">
                                <img src="/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png" id="thumb2_preview" width="135" style="cursor: hand" />
                        </a>
                        <input type="button" class="btn btn-small" onclick="$('#thumb2_preview').attr('src','/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png');$('#thumb2').val('');return false;" value="取消图片">
						<span class="form-required"></span>
					</div>
				</div><?php endif; ?>
                <?php if($type == 1): ?><div class="control-group">
					<label class="control-label">视频</label>
					<div class="controls">
                        <input type="text" value="" style="width:500px;">  <input type="file" name="file">
                        <input type="button" class="btn btn-small" onclick="$('#thumb3').val('');return false;" value="取消"> 仅限MP4格式 10M以内
						<span class="form-required"></span>
					</div>
				</div><?php endif; ?>

                <div class="control-group">
					<label class="control-label">链接</label>
					<div class="controls">
						<input type="text" name="href" value="">点击打开的页面链接 http开头
						<span class="form-required"></span>
					</div>
				</div>
			</fieldset>
			<div class="form-actions">
				<button type="submit" class="btn btn-primary js-ajax-submit"><?php echo L('ADD');?></button>
				<a class="btn" href="<?php echo U('guide/index');?>"><?php echo L('BACK');?></a>
			</div>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
	<script type="text/javascript" src="/public/js/content_addtop.js"></script>
    <script>
        (function(){
            $("#swftype label.radio").on('click',function(){
                var v=$("input",this).val();
                var b=$("#swftype_bd_"+v);
                b.siblings().hide();
                b.show();
            })
        })()
    </script>
</body>
</html>