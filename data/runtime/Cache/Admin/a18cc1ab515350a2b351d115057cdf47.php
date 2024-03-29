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
			<li class="active"><a href="<?php echo U('slidecat/index');?>"><?php echo L('ADMIN_SLIDECAT_INDEX');?></a></li>
			<li><a href="<?php echo U('slidecat/add');?>"><?php echo L('ADMIN_SLIDECAT_ADD');?></a></li>
		</ul>
		<form method="post" class="js-ajax-form" action="<?php echo U('term/listorders');?>">
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th width="50">ID</th>
						<th><?php echo L('NAME');?></th>
						<th><?php echo L('CATEGORY_KEY');?></th>
						<th><?php echo L('DESCRIPTION');?></th>
						<th width="120"><?php echo L('ACTIONS');?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>0</td>
						<td><?php echo L('DEFAULT_CATEGORY');?></td>
						<td>APP_</td>
						<td><?php echo L('DEFAULT_CATEGORY');?></td>
						<td><?php echo L('NOT_ALLOWED_EDIT');?></td>
					</tr>
					<?php if(is_array($slidecats)): foreach($slidecats as $key=>$vo): ?><tr>
						<td><?php echo ($vo["cid"]); ?></td>
						<td><?php echo ($vo["cat_name"]); ?></td>
						<td><?php echo ($vo["cat_idname"]); ?></td>
						<td><?php echo ($vo["cat_remark"]); ?></td>
						<td>
							<?php if($vo['cid'] != 1): ?><a href="<?php echo U('slidecat/edit',array('id'=>$vo['cid']));?>"><?php echo L('EDIT');?></a>|
								<a href="<?php echo U('slidecat/delete',array('id'=>$vo['cid']));?>" class="js-ajax-delete"><?php echo L('DELETE');?></a>
							<?php else: ?>
								不允许修改<?php endif; ?>
						</td>
					</tr><?php endforeach; endif; ?>
				</tbody>
			</table>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
</body>
</html>