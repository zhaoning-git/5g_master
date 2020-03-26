<?php if (!defined('THINK_PATH')) exit();?><!--
 * @Author: your name
 * @Date: 2019-12-26 10:36:34
 * @LastEditTime: 2020-03-04 18:09:09
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \Controller\index.html
 -->
<!doctype html>
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

			<li class="active"><a >房间列表</a></li>

			<li><a href="<?php echo U('HomeConfig/add');?>">添加房间</a></li>

		</ul>

        <form class="well form-search" name="form1" method="post" action="">

            	<?php echo ($giftinfo['giftname']); ?>

		</form>

		<form method="post" class="js-ajax-form">

			<table class="table table-hover table-bordered">

				<thead>

					<tr>

						<th align="center">ID</th>

						<th>房间名称</th>

						<th>房间简介</th>

						<th>所属分类</th>

						<th>所属标签</th>

						<th>房间人数</th>

						<th>地址</th>

						<th>背景图</th>

						<th>添加时间</th>

						<th>修改时间</th>

						<th align="center"><?php echo L('ACTIONS');?></th>

					</tr>

				</thead>

				<tbody>

					<?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr>

						<td align="center"><?php echo ($vo['id']); ?></td>

						<td><?php echo ($vo['title']); ?></td>
						
						<td><?php echo ($vo['info']); ?></td>

						<td><?php if($vo['type'] == 1): ?>首页<?php elseif($vo['type'] == 2): ?>频道<?php else: ?>未选择<?php endif; ?></td>

						<td><?php if($vo['type'] == 1): if($vo['about_label'] == 1): ?>关注<?php endif; ?>
								<?php if($vo['about_label'] == 2): ?>热门<?php endif; ?>
								<?php if($vo['about_label'] == 3): ?>最新<?php endif; ?>
								<?php if($vo['about_label'] == 4): ?>PK<?php endif; ?>							
							<?php elseif($vo['type'] == 2): ?>
								<?php if($vo['about_label'] == 1): ?>娱乐<?php endif; ?>
								<?php if($vo['about_label'] == 2): ?>体育<?php endif; ?>
								<?php if($vo['about_label'] == 3): ?>生活<?php endif; ?>
								<?php if($vo['about_label'] == 4): ?>搞笑<?php endif; endif; ?>
						</td>

						<td><?php echo ($vo['number']); ?></td>
						<td><?php echo ($vo['address']); ?></td>
                        <td><img style="max-height: 60px;" src="<?php echo ($vo['background']); ?>" alt="房间背景图" /></td>

						<td><?php echo (date('Y-m-d H:i:s',$vo['add_time'])); ?></td>
						
						<td><?php echo (date('Y-m-d H:i:s',$vo['update_time'])); ?></td>

						<td align="center">	

							<a href="<?php echo U('HomeConfig/edit',array('id'=>$vo['id']));?>" >编辑</a>

							 |

                            <a href="<?php echo U('HomeConfig/del',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除吗？">删除</a>

						</td>

					</tr><?php endforeach; endif; ?>

				</tbody>

			</table>

			<div class="pagination"></div>

		</form>

		<div id="laypage"></div>

	</div>

	<script src="/public/js/common.js"></script>
    <script type="text/javascript" src="/public/laypage/1.2/laypage.js"></script>

</body>

</html>
<script type="text/javascript">
    var url = "<?php echo U('HomeConfig/index');?>";
     laypage({
                 cont: 'laypage',//指向存放分页的容器，值可以是容器ID 
                 pages: <?php echo ($pageTo); ?>,//数据总数
                 skin:'#5F8878',//皮肤
                 curr: '<?php echo ($dang); ?>',//当前页
                 jump: function ( e, first) {
                     if (!first) {
                          
                         location.href=url+'&page='+e.curr;
                         


                    }
                }
            });

</script>