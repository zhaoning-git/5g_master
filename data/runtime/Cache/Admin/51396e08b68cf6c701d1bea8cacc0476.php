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
        <li class="active"><a>订单详情</a></li>
    </ul>

    <form method="post" class="js-ajax-form tihuan">
        <table class="table table-hover table-bordered">
            <thead>
            <tr>
                <th align="center">ID</th>
                <th>订单号</th>
                <th>商品名</th>
                <th>商品图</th>
                <th>所用金币</th>
                <th>所用银币</th>
                <th>参数</th>
                <th>状态</th>
            </tr>
            </thead>
            <tbody>
            <?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr class="oid" oid="<?php echo ($vo["order_no"]); ?>">
                    <td align="center"><?php echo ($vo["id"]); ?></td>
                    <td><?php echo ($vo["order_no"]); ?></td>
                    <td><?php echo ($vo["shopname"]); ?></td>
                    <td><img src="<?php echo ($vo['img']); ?>" width="50px;" alt="暂无图片"></td>
                    <td><?php echo ($vo["jinbi"]); ?></td>
                    <td><?php echo ($vo["yinbi"]); ?></td>
                    <td>
                        <?php if(is_array($vo['gui_json'])): foreach($vo['gui_json'] as $key=>$voo): echo ($voo['attribute_name']); ?>  :  <?php echo ($voo['shuzhi']); ?> &nbsp;;&nbsp;<?php endforeach; endif; ?>
                    </td>

                    <td>
                        <?php if($vo['eva_status'] == 1): ?>已评价
                            <?php elseif($vo['eva_status'] == 0): ?>
                            待评价<?php endif; ?>
                    </td>
                </tr><?php endforeach; endif; ?>
            </tbody>
        </table>
        <h3>收货地址信息</h3>
        <table class="table table-hover table-bordered">
            <thead>
            <tr>
                <th>收货人</th>
                <th>手机号</th>
                <th>省</th>
                <th>市</th>
                <th>区</th>
                <th>详细地址</th>
                <th>备注信息</th>
                <th align="center"><?php echo L('ACTIONS');?></th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo ($address['dress_name']); ?></td>
                    <td><?php echo ($address['mobile']); ?></td>
                    <td><?php echo ($address['sheng']); ?></td>
                    <td><?php echo ($address['shi']); ?></td>
                    <td><?php echo ($address['qu']); ?></td>
                    <td><?php echo ($address['detailed']); ?></td>
                    <td>
                        <div id="zhu"> <?php echo ($address['beizhu']); ?></div>
                    </td>
                    <td align="center">
                        <div id="btn" style="cursor:pointer;"><h5>备注订单信息</h5></div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="layui-collapse">
            <div class="pagination"><?php echo ($page); ?></div>
        </div>
    </form>
</div>
<script src="/public/js/common.js"></script>
</body>
</html>
<script src="https://code.jquery.com/jquery.js"></script>
<script>
    $(function () {
        $("#btn").click(function () {
            var content = prompt("输入");
            var oid = $(".oid").attr('oid');
            if (content !=='') {
                $.ajax({
                    url: "<?php echo U('Order/addBeizhu');?>",
                    data:{content:content,oid:oid},
                    method:'post',
                    async:false,
                    dataType:'json',
                    success:function (res) {
                        if(res.status == 1){
                            alert('备注成功');
                            window.location.reload();//刷新当前页面.
                        }
                    }
                })
            }

        });

    })
</script>