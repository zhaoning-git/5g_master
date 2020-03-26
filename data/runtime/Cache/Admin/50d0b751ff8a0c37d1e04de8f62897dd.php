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
			<li class="active"><a>反馈列表</a></li>
			<li><a href="<?php echo U('Fankui/add');?>">添加反馈</a></li>
		</ul>
		<form class="js-ajax-form">
			<input type="text" name="name" class='name' placeholder="请输入反馈用户进行查询">
			<input type="button" id="btn" value="点击搜索">
			<li><a href="<?php echo U('Fankui/index');?>">返回</a></li>
		</form>
		<form method="post" class="js-ajax-form tihuan">
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th align="center">ID</th>
						<th>反馈用户</th>
						<th>反馈内容</th>
						<th>联系方式</th>
						<th>处理情况</th>
						<th>提交时间</th>
						<th align="center"><?php echo L('ACTIONS');?></th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr>
						<td align="center"><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($vo['user_nicename']); ?></td>
						<td><?php echo ($vo['content']); ?></td>
						<td><?php echo ($vo['mobile']); ?></td>
						<td>
							<?php if($vo['status'] == 0): ?>未处理
								<?php else: ?>
								已处理<?php endif; ?>
						</td>
						<td><?php echo (date("Y-m-d H:i:s",$vo["create_time"])); ?></td>
						<td align="center">
						    <a href="<?php echo U('Fankui/edit',array('id'=>$vo['id']));?>" >处理</a>
							|<a href="<?php echo U('Fankui/del',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除该俱乐部吗？">删除</a>
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
<script>
	$(function () {
		$("#btn").click(function () {
			var name = $(".name").val();
			$.ajax({
				url: "<?php echo U('Fankui/goSearch');?>",
				data:{name:name},
				method:'post',
				async:false,
				dataType:'json',
				success:function (res) {
					var str = '';
					str +="<table><thead><tr><th>ID</th><th>反馈用户</th><th>反馈内容</th><th>联系方式</th><th>处理状态</th></tr></thead><tbody>";

					$.each(res.data,function(i,n){
						str +="<tr>"
								+ "<td>"+n['id']+"</td>"
								+ "<td>"+n['user_nicename']+"</td>"
								+ "<td>"+n['content']+"</td>"
								+ "<td>"+n['mobile']+"</td>"
								+ "<td>"+n['status']+"</td>"
								+ "</tr>";
					});
					str +="</tbody></table>";
					$('.table-hover').html(str);
				}
			})
		})
	})
</script>