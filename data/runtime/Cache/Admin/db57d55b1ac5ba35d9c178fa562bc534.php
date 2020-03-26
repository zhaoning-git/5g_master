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
			<li ><a href="<?php echo U('Levelanchor/index');?>">等级列表</a></li>
			<li class="active"><a >编辑</a></li>
		</ul>
		<form method="post" class="form-horizontal js-ajax-form" action="<?php echo U('Levelanchor/edit_post');?>">
			<fieldset>
				<div class="control-group">
					<label class="control-label">等级</label>
					<div class="controls">
						<input type="text" name="levelid" value="<?php echo ($experlevel['levelid']); ?>">
						<input type="hidden" name="id" value="<?php echo ($experlevel['id']); ?>">
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">等级名称</label>
					<div class="controls">
						<input type="text" name="levelname" value="<?php echo ($experlevel['levelname']); ?>">
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">等级经验上限</label>
					<div class="controls">
						<input type="text" name="level_up" value="<?php echo ($experlevel['level_up']); ?>" maxlength="9">
						<span class="form-required">*</span>
					</div>
				</div>		
                
                <div class="control-group">
					<label class="control-label">图标</label>
					<div class="controls">
                        <input type="hidden" name="thumb" id="thumb2" value="<?php echo ($experlevel['thumb']); ?>">
                        <a href="javascript:void(0);" onclick="flashupload('thumb_images', '附件上传','thumb2',thumb_images,'1,jpg|jpeg|gif|png|bmp,1,,,1','','','');return false;">
                                <?php if($experlevel['thumb'] != ''): ?><img src="<?php echo ($experlevel['thumb']); ?>" id="thumb2_preview" width="50" style="cursor: hand" />
                                    <?php else: ?>
                                            <img src="/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png" id="thumb2_preview" width="50" style="cursor: hand" /><?php endif; ?>
                        </a>
                        <input type="button" class="btn btn-small" onclick="$('#thumb2_preview').attr('src','/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png');$('#thumb2').val('');return false;" value="取消图片"> 图片尺寸： 90 X 45
						<span class="form-required">*</span>
					</div>
				</div>
                
                <div class="control-group">
					<label class="control-label">头像角标</label>
					<div class="controls">
                        <input type="hidden" name="thumb_mark" id="thumb3" value="<?php echo ($experlevel['thumb_mark']); ?>">
                        <a href="javascript:void(0);" onclick="flashupload('thumb_images', '附件上传','thumb3',thumb_images,'1,jpg|jpeg|gif|png|bmp,1,,,1','','','');return false;">
                                <?php if($experlevel['thumb_mark'] != ''): ?><img src="<?php echo ($experlevel['thumb_mark']); ?>" id="thumb3_preview" width="40" style="cursor: hand" />
                                    <?php else: ?>
                                            <img src="/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png" id="thumb3_preview" width="40" style="cursor: hand" /><?php endif; ?>
                        </a>
                        <input type="button" class="btn btn-small" onclick="$('#thumb3_preview').attr('src','/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png');$('#thumb3').val('');return false;" value="取消图片"> 图片尺寸： 40 X 40
						<span class="form-required">*</span>
					</div>
				</div>
	
			</fieldset>
			<div class="form-actions">
				<button type="submit" class="btn btn-primary js-ajax-submit"><?php echo L('EDIT');?></button>
				<a class="btn" href="<?php echo U('Levelanchor/index');?>"><?php echo L('BACK');?></a>
			</div>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
	<script type="text/javascript" src="/public/js/content_addtop.js"></script>
</body>
</html>