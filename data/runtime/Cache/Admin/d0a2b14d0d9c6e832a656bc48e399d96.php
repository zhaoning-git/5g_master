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
			<li><a href="<?php echo U('Zixun/indexInfo');?>">资讯列表</a></li>
			<li class="active"><a>添加资讯及俱乐部发帖</a></li>
		</ul>
		<form method="post" class="form-horizontal js-ajax-form" action="<?php echo U('Zixun/add_message_do');?>" enctype="multipart/form-data">
			<fieldset>
				<div class="control-group">
					<label class="control-label">用户</label>
					<div class="controls">
						<select class="area-select" name="uid">
							<option value="0">请选择</option>
							<?php if(is_array($userInfo)): foreach($userInfo as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["id"]); ?>----<?php echo ($vo["user_nicename"]); ?></option><?php endforeach; endif; ?>

						</select>
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">标题</label>
					<div class="controls">
						<input type="text" name="title">
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">资讯整体类型</label>
					<div class="controls">
						<select class="area-select" id="bigType" name="index_mol">
							<option value="0">请选择</option>
							<option value="1">足球</option>
							<option value="2">篮球</option>
							<option value="4">俱乐部</option>
						</select>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">资讯标签类型</label>
					<div class="controls">
						<select class="area-select" id="type" name="sel_type">
							<option>请选择</option>
						</select>
						<span class="form-required">*</span>
					</div>
				</div>

				<div class="control-group clubs" style="display: none">
					<label class="control-label">哪个俱乐部发出的</label>
					<div class="controls">
						<select class="area-select" id="is_club" name="is_club">
							<?php if(is_array($clubInfo)): foreach($clubInfo as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; ?>
						</select>
						<span class="form-required">*</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">内容</label>
					<div class="controls">
						<input type="text" name="content">
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">话题</label>
					<div class="controls">
						<input type="text" name="theme">
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">资源图片</label>
					<div class="controls">
						<button type="button" class="layui-btn" id="test2">上传</button>
						<blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 10px;">
							预览图：
							<div class="layui-upload-list" id="demo2"></div>
							<input type="hidden" name="data" class="layui-input">
						</blockquote>
					</div>
				</div>

			</fieldset>
			<div class="form-actions">
				<button type="submit" class="btn btn-primary js-ajax-submit"><?php echo L('ADD');?></button>
				<a class="btn" href="<?php echo U('Zixun/index');?>"><?php echo L('BACK');?></a>
			</div>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
	<script type="text/javascript" src="/public/js/content_addtop.js"></script>
	<script src="/public/layui/layui.js"></script>
	<link rel="stylesheet" type="text/css" href="/public/layui/css/layui.css">
</body>
</html>
<script>
	$(function(){
		var url = "<?php echo U('Zixun/biaoQian');?>";
		$("#bigType").change(function () {
			var options=$("#bigType option:selected").val();  //获取选中的项
			if(options == 4){
				$(".clubs").show();//显示div
			}else{
				$(".clubs").hide();//显示div
			}
			$.ajax({
				type:'post',
				url:url,
				data:{mol:options},
				dataType:'json',
				success:function(data){ //请求成功回调函数
					var info = data.data;

					if(data.status == 1){ //判断状态码，200为成功
						var option = '';
						for(var i=0;i<info.length;i++){ //循环获取返回值，并组装成html代码
							var name = info[i].name;
							var id = info[i].id;
							option +='<option value="'+id+'">'+name+'</option>';
						}
					}else{
						var option = '<option>请选择</option>'; //默认值
					}

					$("#type").html(option); //js刷新第二个下拉框的值

				},
			});
		});

		//多图片上传
		layui.use('upload', function(){
			var $ = layui.jquery
					,layer = layui.layer
					,upload = layui.upload;

			//多图片上传
			upload.render({
				elem: '#test2'
				,url:'<?php echo U("Zixun/uploadPic");?>'
				,multiple: true
				,before: function(obj){
					obj.preview(function(index, file, result){
						// $('#demo2').append('<div><img width="100px;" src="'+ result +'" alt="'+ file.name +'" class="layui-upload-img"><span class="close">×</span></div>')
					});
				}
				,done: function(res){
					//上传完毕
					$('#demo2').append('<div><img width="100px;" pid="'+res.pid+'" aaa="'+ res.src +'" src="/data/upload/showData/'+res.src+'" class="layui-upload-img"><span class="close">×</span></div>');
					var pic_url =$('[name=data]').val()+','+res.pid;
					$('[name=data]').val(pic_url);
					// layer.msg(res.msg);
				}
			});
			$(document).on('click','.close',function(){
				var url=$(this).prev().attr('aaa');
				var arr=$('[name=data]').val().split(',');
				//数组去空
				var arr2 = $.grep(arr, function(n) {return $.trim(n).length > 0;});
				//被删除的图片 id
				var del_id=$(this).prev().attr('pid');
				$.ajax({
					type:'post',
					url:'<?php echo U("Zixun/delPic");?>',
					data:{key:url},
					dataType:'json',
					success:function(data){ //请求成功回调函数
						layer.msg(data.info);
							// console.log(data);
					},
				});
				for(var i=0;i<arr2.length;i++) {
					//判断元素是否存在于数组中  存在则删除
					var is_exit = $.inArray(del_id,arr2);
					if(is_exit !== -1) {
						var new_val = arr.splice(is_exit, 1).join();
						$('[name=data]').val(new_val);
					}
				}
				$(this).parent('div').remove();
			});

		})
	});

</script>