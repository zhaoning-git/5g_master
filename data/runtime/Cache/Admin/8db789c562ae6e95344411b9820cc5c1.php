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
        <li class="active"><a >订单列表</a></li>
        <li><a href="<?php echo U('Order/add');?>">添加订单</a></li>
    </ul>

    <form method="post" class="js-ajax-form tihuan">
        <table class="table table-hover table-bordered">
            <thead>
            <tr>
                <th align="center">ID</th>
                <th>订单编号</th>
                <th>所用金币</th>
                <th>所用银币</th>
                <!-- <th>分类</th> -->
                <th>用户</th>
                <th>状态</th>
                <th align="center"><?php echo L('ACTIONS');?></th>
            </tr>
            </thead>
            <tbody>
            <?php if(is_array($info)): foreach($info as $key=>$vo): ?><tr>
                    <td align="center"><?php echo ($vo["id"]); ?></td>
                    <td><?php echo ($vo["order_no"]); ?></td>
                    <td><?php echo ($vo["jinbi"]); ?></td>
                    <td><?php echo ($vo["yinbi"]); ?></td>
                    <td><?php echo ($vo["user_nicename"]); ?></td>
                    <td>
                        <?php if($vo['status'] == 1): ?>待付款
                            <?php elseif($vo['status'] == 2): ?>
                            待发货
                            <?php elseif($vo['status'] == 3): ?>
                            待收货
                            <?php elseif($vo['status'] == 4): ?>
                            待评价,具体请查看订单详情<?php endif; ?>
                    </td>
                    <td align="center">
                       <a href="<?php echo U('Order/orderDetail',array('order_id'=>$vo['id']));?>" >订单详情</a>
                        |
                       <a href="<?php echo U('Order/delOrder',array('order_no'=>$vo['order_no']));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除吗？">删除订单</a>

                    </td>
                </tr><?php endforeach; endif; ?>
            </tbody>
        </table>

        <div class="layui-collapse">
            <div class="pagination"><?php echo ($page); ?></div>
            <div class="table-actions">
                <button class="btn btn-primary btn-small js-ajax-submit" type="submit"><?php echo L('SORT');?></button>
            </div>
        </div>
    </form>
</div>
<script src="/public/js/common.js"></script>
</body>
</html>
<script src="https://code.jquery.com/jquery.js"></script>