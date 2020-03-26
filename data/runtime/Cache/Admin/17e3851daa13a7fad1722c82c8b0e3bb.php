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
			<li class="active"><a>认证列表</a></li>
		</ul>

		<form method="post" class="js-ajax-form tihuan">
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th align="center">ID</th>
						<th>主播名称</th>
						<th>上传的图像</th>
						<th>是否合格</th>
						<th>上传时间</th>
						<th>操作时间</th>
						<th align="center"><?php echo L('ACTIONS');?></th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr>
						<td align="center"><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($vo['name']); ?></td>
						<td><img src="<?php echo ($vo['url']); ?>" style="height:20px"></td>
						<td>
							<?php if($vo['status'] == 1): ?>待审核
								<?php elseif($vo['status'] == 2): ?>
								合格
								<?php else: ?>
								已驳回<?php endif; ?>
						</td>
						<td><?php echo (date("Y-m-d H:i:s",$vo["create_time"])); ?></td>
						<td><?php echo (date("Y-m-d H:i:s",$vo["pass_time"])); ?></td>
						<td align="center">
							<a href="<?php echo U('Zhubo/ShenHe',array('id'=>$vo['id']));?>" >审核</a>
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
				url: "<?php echo U('Clubs/goSearch');?>",
				data:{name:name},
				method:'post',
				async:false,
				dataType:'json',
				success:function (res) {
					var str = '';
					str +="<table><thead><tr><th>ID</th><th>俱乐部名称</th><th>俱乐部头像</th><th>俱乐部等级</th><th>所拥有的金币</th><th>所拥有的银币</th></tr></thead><tbody>";

					$.each(res.data,function(i,n){
						str +="<tr>"
								+ "<td>"+n['id']+"</td>"
								+ "<td>"+n['name']+"</td>"
								+ "<td><img src='"+n['url']+"'width='50px;'></td>"
								+ "<td>"+n['level']+"</td>"
								+ "<td>"+n['gold_coin']+"</td>"
								+ "<td>"+n['silver_coin']+"</td>"
								+ "</tr>";
					});
					str +="</tbody></table>";
					$('.table-hover').html(str);
				}
			})
		})
	})
</script>