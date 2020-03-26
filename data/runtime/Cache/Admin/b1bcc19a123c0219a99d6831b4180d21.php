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
input{
    width:500px;
}
</style>
</head>
<body>
	<div class="wrap">
		<ul class="nav nav-tabs">
			<li ><a href="<?php echo U('HomeConfig/edit');?>">房间编辑</a></li>
			
		</ul>
		<form method="post" class="form-horizontal js-ajax-form" enctype="multipart/form-data" action="<?php echo U('HomeConfig/edit');?>">
		   <input type="hidden" name="id" value="<?php echo ($data['id']); ?>">
		  
			<fieldset>

				<div class="control-group">
					<label class="control-label">房间名称</label>
					<div class="controls">
						<input type="text" name="title" value="<?php echo ($data['title']); ?>" >
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">房间简介</label>
					<div class="controls">
						<input type="text" name="info" value="<?php echo ($data['info']); ?>" >
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">

					<label class="control-label">所属分类</label>

					<div class="controls">

						<select id="change_type" name="type" onchange="change_info();">
							<option value="1" <?php if($data['type'] == 1 ): ?>selected<?php endif; ?> >首页</option>
							<option value="2" <?php if($data['type'] == 2 ): ?>selected<?php endif; ?> >频道</option>
						</select>

						<span class="form-required">*</span>

					</div>

				</div>

				<div class="control-group">

					<label class="control-label">所属标签</label>

					<div class="controls">

						<select id="change_label" name="about_label">
							<?php if($data['type'] == 1 ): ?><option value="1" <?php if($data['about_label'] == 1): ?>selected<?php endif; ?> >关注</option>
								<option value="2" <?php if($data['about_label'] == 2): ?>selected<?php endif; ?> >热门</option>
								<option value="3" <?php if($data['about_label'] == 3): ?>selected<?php endif; ?> >最新</option>
								<option value="4" <?php if($data['about_label'] == 4): ?>selected<?php endif; ?> >PK</option>				
							<?php elseif($data['type'] == 2 ): ?>
								<option value="1" <?php if($data['about_label'] == 1): ?>selected<?php endif; ?> >娱乐</option>
								<option value="2" <?php if($data['about_label'] == 2): ?>selected<?php endif; ?> >体育</option>
								<option value="3" <?php if($data['about_label'] == 3): ?>selected<?php endif; ?> >生活</option>
								<option value="4" <?php if($data['about_label'] == 4): ?>selected<?php endif; ?> >搞笑</option><?php endif; ?>

						</select>

						<span class="form-required">*</span>

					</div>

				</div>
				<script>
					function change_info()
					{
						var c1 = $("#change_type").val();
						var str = '';
						if(1 == c1)
						{
							str = '<option value="1">关注</option><option value="2">热门</option><option value="3">最新</option><option value="4">PK</option>';
							$("#change_label").html(str);
						}
						if(2 == c1)
						{
							str = '<option value="1">娱乐</option><option value="2">体育</option><option value="3">生活</option><option value="4">搞笑</option>';
							$("#change_label").html(str);
						}
					}

				</script>
				<div class="control-group">
					<label class="control-label">房间背景图</label>
					<div class="controls">
						<input type="file" name="file" value="<?php echo ($data['background']); ?>" >
						<span class="form-required">*</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">房间人数</label>
					<div class="controls">
						<input type="text" name="number" value="<?php echo ($data['number']); ?>" >
						<span class="form-required">*</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">地址</label>
					<div class="controls">
						<input type="text" name="address" value="<?php echo ($data['address']); ?>" >
						<span class="form-required">*</span>
					</div>
				</div>
				

			</fieldset>
			<div class="form-actions">
				<button type="submit" class="btn btn-primary js-ajax-submit"><?php echo L('EDIT');?></button>
				<a class="btn" href="<?php echo U('HomeConfig/index');?>"><?php echo L('BACK');?></a>
			</div>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
	<script type="text/javascript" src="/public/js/content_addtop.js"></script>
</body>
</html>