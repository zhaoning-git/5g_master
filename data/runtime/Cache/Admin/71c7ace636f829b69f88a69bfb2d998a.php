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
			<li class="active"><a href="<?php echo U('Advertising/industry');?>">列表</a></li>
			<li><a href="<?php echo U('Advertising/industryadd');?>">添加</a></li>
		</ul>
		<form method="post" class="js-ajax-form" action="<?php echo U('Chargerules/listorders');?>">
		  <div class="table-actions">
				<button class="btn btn-primary btn-small js-ajax-submit" type="submit"><?php echo L('SORT');?></button>
			</div>
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
					 
						<th align="center">ID</th>
						<th>行业名称</th>
						
						

						<th align="center"><?php echo L('ACTIONS');?></th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr>
					 
						<td align="center"><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($vo['industry']); ?></td>
						
						<td align="center">	
							<?php if($vo['status'] == '1'): ?><a>已删除</a> 
     							   <a href="<?php echo U('Advertising/industryrecover',array('id'=>$vo['id']));?>"  class="js-ajax-dialog-btn" data-msg="您确定要恢复吗？">恢复</a><?php endif; ?>
   						    <?php if($vo['status'] == '0'): ?><a href="<?php echo U('Advertising/industrydel',array('id'=>$vo['id']));?>"  class="js-ajax-dialog-btn" data-msg="您确定要删除吗？">删除</a><?php endif; ?>
							
						</td>
					</tr><?php endforeach; endif; ?>
				</tbody>
			</table>
			<div class="pagination"><?php echo ($page); ?></div>
				<div class="table-actions">
				<button class="btn btn-primary btn-small js-ajax-submit" type="submit"><?php echo L('SORT');?></button>
			</div>
		</form>
		 <div id="laypage"></div>
	</div>
	<script src="/public/js/common.js"></script>
	<script type="text/javascript" src="/public/laypage/1.2/laypage.js"></script>
</body>
</html>
<script type="text/javascript">
	

</script>