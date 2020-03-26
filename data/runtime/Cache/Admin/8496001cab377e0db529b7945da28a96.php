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
			<li class="active"><a >记录</a></li>

		</ul>
		<form class="well form-search" name="form1" method="post" style="float:left" action="">
            信息类型：
			<select class="select_2" name="type">
				<option value="">全部</option>
                <?php if(is_array($msg_type)): $i = 0; $__LIST__ = $msg_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($formget["type"] == $key): ?>selected<?php endif; ?> ><?php echo ($v); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>		
			</select>
			提交时间：
			<input type="text" name="start_time" class="js-date date" id="start_time" value="<?php echo ($formget["start_time"]); ?>" style="width: 80px;" autocomplete="off">-
			<input type="text" class="js-date date" name="end_time" id="end_time" value="<?php echo ($formget["end_time"]); ?>" style="width: 80px;" autocomplete="off"> &nbsp; &nbsp;
			关键字： 
			<input type="text" name="keyword" style="width: 200px;" value="<?php echo ($formget["keyword"]); ?>" placeholder="请输入接收账号...">
			<input type="button" class="btn btn-primary" value="搜索" onclick="form1.action='<?php echo U('Sendcode/index');?>';form1.submit();"/>
			<input type="button" class="btn btn-primary" style="background-color: #1dccaa;" value="导出" onclick="form1.action='<?php echo U('Sendcode/export');?>';form1.submit();"/>	
		</form>	
    	
		<form method="post" class="js-ajax-form" >

		
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th align="center">ID</th>
						<th>信息类型</th>
						<th>接收账号</th>
						<th>信息内容</th>
						<th>提交时间</th>
						<!-- <th align="center"><?php echo L('ACTIONS');?></th> -->
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($lists)): foreach($lists as $key=>$vo): ?><tr>
						<td align="center"><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($msg_type[$vo['type']]); ?></td>
						<td><?php echo ($vo['account']); ?></td>
						<td><?php echo ($vo['content']); ?></td>
						<td><?php echo (date("Y-m-d H:i:s",$vo["addtime"])); ?></td>
						<!-- <td align="center">	 -->
 							<!-- <a href="<?php echo U('Gift/edit',array('id'=>$vo['id']));?>" >编辑</a> | -->
							<!-- <a href="<?php echo U('Sendcode/del',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除吗？">删除</a>  -->
						<!-- </td> -->
					</tr><?php endforeach; endif; ?>
				</tbody>
			</table>
			<div class="pagination"><?php echo ($page); ?></div>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
</body>
</html>