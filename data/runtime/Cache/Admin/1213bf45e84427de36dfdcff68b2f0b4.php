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
			<li class="active"><a>资讯列表</a></li>
			<li><a href="<?php echo U('Zixun/addMessage');?>">添加资讯</a></li>
		</ul>
		<form method="post" class="js-ajax-form" >
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th align="center">ID</th>
						<th width="150px;">标题</th>
<!--						<th width="450px;">内容</th>-->
<!--						<th>作者</th>-->
						<th>认证</th>
						<th>阅读量</th>
						<th>点赞数量</th>
						<th>收藏数量</th>
						<th>发表时间</th>
						<th>是否是爬取的数据</th>
						<th align="center"><?php echo L('ACTIONS');?></th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr>
						<td align="center"><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($vo['title']); ?></td>
<!--						<td><?php echo ($vo['content']); ?></td>-->
<!--						<td><?php echo ($vo['uname']); ?></td>-->
						<td>
							<?php if($vo['sqs_status'] == 1): ?>官方认证
							<?php elseif($vo['sqs_status'] == 2): ?>大神认证
							<?php elseif($vo['sqs_status'] == 3): ?>作者认证
							<?php else: ?>未认证<?php endif; ?>
						</td>
						<td><?php echo ($vo['reading']); ?></td>
						<td><?php echo ($vo['praise']); ?></td>
						<td><?php echo ($vo['favor']); ?></td>
						<td><?php echo (date("Y-m-d H:i:s",$vo["addtime"])); ?></td>
						<td>
							<?php if($vo['identifshi'] == 1): ?>√
								<?php else: ?>
								×<?php endif; ?>
						</td>
						<td align="center">
						    <a href="<?php echo U('Zixun/detail',array('id'=>$vo['id']));?>" >查看详情</a>
						    | <a href="<?php echo U('Zixun/authSqs',array('id'=>$vo['id']));?>" >认证</a>
						    | <a href="<?php echo U('Zixun/del',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除该帖子？">删除</a>
						</td>
					</tr><?php endforeach; endif; ?>
				</tbody>
			</table>
			<div class="pagination"><?php echo ($page); ?></div>

		</form>
	</div>
	<script src="/public/js/common.js"></script>
</body>
</html>