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
<script src="/public/layui/layui.js"></script>
<link rel="stylesheet" type="text/css" href="/public/layui/css/layui.css">
    <div class="wrap">
        <ul class="nav nav-tabs">
            <li><a href="<?php echo U('Jifenduihuan/indexlist');?>">商品列表</a></li>
            <li><a href="<?php echo U('Jifenduihuan/index');?>">商品添加</a></li>
            <li class="active"><a >sku库存</a></li>
        </ul>
        <table class="layui-table" lay-data="{ id:'test3'}" lay-filter="test3">
            <thead>
            <tr>
                <th lay-data="{field:'id', width:80, sort: true}">ID</th>
                <th lay-data="{field:'huohao', width:120, sort: true}">货号</th>
                <th lay-data="{field:'shu'}">属性</th>
                <th lay-data="{field:'ku', width:80, edit: 'text'}">库存</th>
                <th lay-data="{field:'gold_coin', edit: 'text', minWidth: 100}">金币价格</th>
                <th lay-data="{field:'silver_coin', sort: true, edit: 'text'}">银币价格</th>
            </tr>
            </thead>
            <tbody>
            <?php if(is_array($info)): foreach($info as $key=>$vo): ?><tr>
                    <td align="center"><?php echo ($vo['id']); ?></td>
                    <td><?php echo ($vo['huohao']); ?></td>
                    <td>
                        <?php if(is_array($vo['shu'])): foreach($vo['shu'] as $key=>$voo): echo ($voo['shuzhi']); endforeach; endif; ?>
                    </td>
                    <td><?php echo ($vo['ku']); ?></td>
                    <td class="click"><?php echo ($vo['gold_coin']); ?></td>
                    <td class="click"><?php echo ($vo['silver_coin']); ?></td>

                </tr><?php endforeach; endif; ?>
            </tbody>
        </table>


    </div>
    <script src="/public/js/common.js"></script>

</body>
</html>
<script>
    $(function () {
        layui.use('table', function(){
            var table = layui.table;

            //监听单元格编辑
            table.on('edit(test3)', function(obj){
                var value = obj.value //得到修改后的值
                    ,data = obj.data.id //得到所在行所有键值
                    ,field = obj.field; //得到字段
                $.ajax({
                    url: "<?php echo U('Jifenduihuan/jiEdit');?>",
                    data:{value:value,field:field,id:data},
                    method:'post',
                    async:false,
                    dataType:'json',
                    success:function (res) {
                        if(res.status == 1){
                            layer.msg('[ID: '+ data +'] ' + field + ' 字段更改为：'+ value);
                        }
                    }
                })

                //
            });
        });
    })

</script>