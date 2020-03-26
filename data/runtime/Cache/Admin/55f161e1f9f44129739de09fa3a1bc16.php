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
<link rel="stylesheet" type="text/css" href="/public/layui/css/layui.css">
<style>
	.bottmBox{width: 80%; display: block;}
	.pagination{float: left;}
</style>
</head>
<body>
	<div class="wrap">
		<ul class="nav nav-tabs">
			<li <?php echo ($index); ?>><a href="<?php echo U('Clubs/index');?>">俱乐部列表</a></li>
			<li ><a href="<?php echo U('Clubs/add_club');?>">创建俱乐部</a></li>
			<li><a href="<?php echo U('Clubs/mem_add');?>">加入俱乐部</a></li>
			<li <?php echo ($Hotchat); ?>><a href="<?php echo U('Clubs/Hotchat');?>">热聊俱乐部</a></li>
			<li <?php echo ($Gf); ?>><a href="<?php echo U('Clubs/Gf');?>">官方俱乐部</a></li>
			<li <?php echo ($Hot); ?>><a href="<?php echo U('Clubs/Hot');?>">热门推荐</a></li>
		</ul>

		<form class="js-ajax-form">
			<input type="text" name="name" class='name' placeholder="请输入俱乐部名称进行查询">
			<input type="button" id="btn" value="点击搜索">
			<li><a href="<?php echo U('Clubs/index');?>">返回</a></li>
		</form>

		<form method="post" class="js-ajax-form tihuan">
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th align="center">ID</th>
						<th>俱乐部名称</th>
						<th>俱乐部头像</th>
						<th>俱乐部等级</th>
						<th>是否热门</th>
						<th>所属标签分类</th>
						<th>俱乐部状态</th>
						<th>所拥有的金币</th>
						<th>所拥有的银币</th>
						<th>创建时间</th>
						<th>修改时间</th>
						<th>排序</th>
						<th align="center"><?php echo L('ACTIONS');?></th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr>
							<td align="center"><?php echo ($vo["id"]); ?></td>
							<td><?php echo ($vo['name']); ?></td>
							<td><img src="<?php echo ($vo['url']); ?>" style="height:20px"></td>
							<td><?php echo ($vo['level']); ?></td>
							<td>
								<?php if($vo['is_hot'] == 0): ?>×
									<?php else: ?>
									√<?php endif; ?>
							</td>
							<td><?php echo ($vo['typename']); ?></td>
							<td>
								<?php if($vo['is_del'] == 0): ?>正在运行
									<?php else: ?>
									已删除<?php endif; ?>
							</td>
							<td><?php echo ($vo['gold_coin']); ?></td>
							<td><?php echo ($vo['silver_coin']); ?></td>
							<td><?php echo (date("Y-m-d H:i:s",$vo["create_time"])); ?></td>
							<td><?php echo (date("Y-m-d H:i:s",$vo["update_time"])); ?></td>
							<td><input type="text" name="sort" value="<?php echo ($vo["sort"]); ?>" style="width: 40px;" data-id="<?php echo ($vo["id"]); ?>"></td>
							<td align="center">
								<a href="<?php echo U('Clubs/xiugai',array('id'=>$vo['id']));?>" >编辑</a>
								<a href="<?php echo U('Clubs/edit',array('id'=>$vo['id']));?>" >审核</a>
									<?php if($vo['is_del'] == '0'): ?>|<a href="<?php echo U('Clubs/del',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除该俱乐部吗？">删除</a>
									<?php else: ?>
										|<a href="<?php echo U('Clubs/enable',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="您确定要启用该俱乐部吗？">恢复</a><?php endif; ?>
							</td>
						</tr><?php endforeach; endif; ?>
					<tr>
						<td colspan="11"><div class="pagination"><?php echo ($page); ?></div></td>
						<td class="noborder" style="border-left: none;border-right: none;border-bottom: none;">
							<div class="layui-btn layui-btn-normal sort">排序</div>
						</td>
						<td style="border-left: none;border-right: none;border-bottom: none;"></td>
					</tr>
				</tbody>

			</table>
			<div class="bottmBox">
				
				
		    </div>

		</form>

	</div>

	<script src="/public/js/common.js"></script>
    
</body>

</html>

<script>
    var strings = '';
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
		});

		$(".sort").click(function(){
			var numArr = new Array();
			var cid = new Array();
			$('input[name="sort"]').each(function(){
				// numArr[$(this).data('id')] = $(this).val();
				numArr.push($(this).val());//添加至数组
				cid.push($(this).data('id'));
			});
			
			
			
			$.post("<?php echo U('Clubs/upSort');?>",{'sort':numArr,'cid':cid},function(res){

			});
			console.log(cid);
            console.log(numArr);
		});
	})

</script>