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
			<li class="active"><a >列表</a></li>
			<li ><a href="<?php echo U('Loginbonus/index2');?>">记录</a></li>
			<!-- <li><a href="<?php echo U('Loginbonus/add');?>">添加</a></li> -->
		</ul>

			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th align="center">ID</th>
						<th>登录天数</th>
						<th>奖励<?php echo ($configpub['name_coin']); ?></th>
						<th align="center"><?php echo L('ACTIONS');?></th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($lists)): foreach($lists as $key=>$vo): ?><tr>
						<td align="center"><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($vo['day']); ?></td>
						<td><?php echo ($vo['coin']); ?></td>
						<td align="center">	
							<a href="<?php echo U('Loginbonus/edit',array('id'=>$vo['id']));?>" >编辑</a>
							 <!-- |
							<a href="<?php echo U('Loginbonus/del',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除吗？">删除</a> -->
						</td>
					</tr><?php endforeach; endif; ?>
				</tbody>
			</table>
			<div class="pagination"><?php echo ($page); ?></div>

	</div>
	<script src="/public/js/common.js"></script>
</body>
</html>