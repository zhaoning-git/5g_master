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
			<li class="active"><a>列表</a></li>
		</ul>
		
		<?php $action=array("1"=>"智勇三张","2"=>"海盗船长","3"=>"转盘","4"=>"开心牛仔","5"=>"二八贝"); $status=array("0"=>"进行中","1"=>"正常结束","2"=>"主播关闭","3"=>"意外结束"); $type=array("0"=>"否","1"=>"是"); ?>
		<form class="well form-search" method="post" action="<?php echo U('Game/index');?>">
			游戏类型： 
			<select class="select_2" name="action">
				<option value="">全部</option>
				<?php if(is_array($action)): $i = 0; $__LIST__ = $action;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($formget["action"] == $key): ?>selected<?php endif; ?> ><?php echo ($v); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>

			</select> &nbsp;&nbsp;
			
			主播： 
			<input type="text" name="keyword" style="width: 200px;" value="<?php echo ($formget["keyword"]); ?>" placeholder="请输入主播id...">
			<input type="submit" class="btn btn-primary" value="搜索">
		</form>	
		<form method="post" class="js-ajax-form" action="">
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th align="center">ID</th>
						<th>游戏类型</th>
						<th>主播（ID）</th>
						<th>开始时间</th>
						<th>结束时间</th>
						<th>游戏状态</th>
						<th>系统干预</th>
						<!-- <th>人工干预</th> -->
						<th align="center"><?php echo L('ACTIONS');?></th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($lists)): foreach($lists as $key=>$vo): ?><tr>
						<td align="center"><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($action[$vo['action']]); ?></td>
						<td><?php echo ($vo['userinfo']['user_nicename']); ?> ( <?php echo ($vo['liveuid']); ?> )</td>
						<td><?php echo (date("Y-m-d H:i:s",$vo["starttime"])); ?></td>
						<td>
							<?php if($vo['status'] == '0'): ?>进行中
							<?php else: ?>
								<?php echo (date("Y-m-d H:i:s",$vo["endtime"])); endif; ?>
						</td>
						<td><?php echo ($status[$vo['state']]); ?></td>
						<td><?php echo ($type[$vo['isintervene']]); ?></td>
						<!-- <td><?php echo ($type[$vo['intervene_admin']]); ?></td> -->
						<td align="center">	
							<a href="<?php echo U('Game/index2',array('gameid'=>$vo['id'],'result'=>$vo['result']));?>" >详情</a>
						</td>
					</tr><?php endforeach; endif; ?>
				</tbody>
			</table>
		</form>
		<div class="pagination"><?php echo ($page); ?></div>
	</div> 
	<script src="/public/js/common.js"></script>
</body>
</html>