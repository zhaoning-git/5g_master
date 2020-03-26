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

			<li><a href="<?php echo U('Jifenduihuan/indexlist');?>">商品列表</a></li>
			<li><a href="<?php echo U('Jifenduihuan/index');?>">添加商品</a></li>
			<li class="active"><a>评价列表</a></li>
		</ul>
		<form method="post" class="js-ajax-form" >
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th align="center">ID</th>
						<th>商品id</th>
						<th>商品名</th>
						<th>商品属性</th>
						<th>文字评价</th>
						<th>图片评价</th>
						<th>评价用户</th>
						<th>用户头像</th>
						<th>评价时间</th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($info)): foreach($info as $key=>$vo): ?><tr>
						<td align="center"><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($vo['goods_id']); ?></td>
						<td><?php echo ($vo['goods_name']); ?></td>
						<td>
							<?php if(is_array($vo['gui_json'])): foreach($vo['gui_json'] as $key=>$voo): echo ($voo['attribute_name']); ?>  :  <?php echo ($voo['shuzhi']); ?> &nbsp;;&nbsp;<?php endforeach; endif; ?>
						</td>
						<td><?php echo ($vo['evaluate']); ?></td>
						<td><img src="<?php echo ($vo['eva_pic']); ?>" width="50px;" alt="暂无图片评价"></td>
						<td><?php echo ($vo['user_nicename']); ?></td>
						<td><img src="<?php echo ($vo['avatar']); ?>" width="50px;" alt="暂无头像"></td>
						<td><?php echo ($vo['create_time']); ?></td>

					</tr><?php endforeach; endif; ?>
				</tbody>
			</table>
			<div class="pagination"><?php echo ($page); ?></div>

		</form>
	</div>
	<script src="/public/js/common.js"></script>
</body>
</html>