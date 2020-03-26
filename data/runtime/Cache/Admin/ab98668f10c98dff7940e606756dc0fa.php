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

            <li class="active"><a >商品列表</a></li>

            <li><a href="<?php echo U('Jifenduihuan/index');?>">商品添加</a></li>

            <li><a href="<?php echo U('Jifenduihuan/shiindex');?>">商品实物排序</a></li>
            <li><a href="<?php echo U('Jifenduihuan/teindex');?>">特权排序</a></li>
            <li><a href="<?php echo U('Jifenduihuan/xianindex');?>">限量排序</a></li>
            <li><a href="<?php echo U('Jifenduihuan/daoindex');?>">道具排序</a></li>
            <li><a href="<?php echo U('Jifenduihuan/xuindex');?>">虚拟物品排序</a></li>
            <li><a href="<?php echo U('Jifenduihuan/liindex');?>">礼物排序</a></li>
             <li><a href="<?php echo U('Jifenduihuan/xinindex');?>">实物新品排序</a></li>
            <li><a href="<?php echo U('Jifenduihuan/hotindex');?>">实物热门排序</a></li>
            


        </ul>

        <form method="post" class="js-ajax-form" action="<?php echo U('Gift/listorders');?>">

            <div class="table-actions">

                <button class="btn btn-primary btn-small js-ajax-submit" type="submit"><?php echo L('SORT');?></button>

            </div>



            <table class="table table-hover table-bordered">

                <thead>

                    <tr>

                        <th align="center">ID</th>

                        <th>商品名称</th> 

                        <th>货号</th>

                        <th>商品类型</th>

                        <th>分类</th>

                        <th>是否支持新品首发/热门/推荐</th>

                        <th>下属类型</th>

                        <th>商品图片</th>

                      

                        <th>sku库存</th>

                        <th>商品价格(金币)</th>

                        <th>商品价格(银币)</th>



                        <th align="center"><?php echo L('ACTIONS');?></th>

                    </tr>

                </thead>

                <tbody>

                <?php if(is_array($lists)): foreach($lists as $key=>$vo): ?><tr>

                        <td align="center"><?php echo ($vo["id"]); ?></td>

                        <td><?php echo ($vo['shopname']); ?></td>

                        <td><?php echo ($vo['huohao']); ?></td>

                        <td><?php echo ($vo['type']); ?></td>

                        <td><?php echo ($vo['typename']); ?></td>

                        <td>

                            


                            <?php if($vo['product'] == 1): ?>是<?php endif; ?>

                            <?php if($vo['product'] == 0): ?>否<?php endif; ?>|
                             <?php if($vo['hots'] == 1): ?>是<?php endif; ?>

                            <?php if($vo['hots'] == 0): ?>否<?php endif; ?>|
                             <?php if($vo['recom'] == 1): ?>是<?php endif; ?>

                            <?php if($vo['recom'] == 0): ?>否<?php endif; ?>



                        </td>

                        <td><?php echo ($vo['zifenlei']); ?></td>

                        <td><img width="25" height="25" src="<?php echo ($vo['img']); ?>" /></td>

                     

                        <td>

                            <?php if($vo['sku_status'] == 1): ?><a href="<?php echo U('Jifenduihuan/ku',array('id'=>$vo['id']));?>" >查看sku库存</a>

                                <elseif/><?php endif; ?>

                        </td>

                     

                        <td><?php echo ($vo['jinbi']); ?></td>

                        <td><?php echo ($vo['yingbi']); ?></td>

                  <td align="center">

                    

                    <a href="<?php echo U('Jifenduihuan/edit',array('id'=>$vo['id']));?>" >编辑</a>

                    | <a href="<?php echo U('Jifenduihuan/evalist',array('id'=>$vo['id']));?>" >查看评价</a>

                    |

                    <a href="<?php echo U('Jifenduihuan/peisku',array('id'=>$vo['id']));?>" >配置sku</a>

                    |

                    <a href="<?php echo U('Jifenduihuan/del',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除吗？">删除</a>



                  </td>

                    </tr><?php endforeach; endif; ?>

                </tbody>

            </table>

            <div class="pagination"><?php echo ($page); ?></div>

            <div class="table-actions">

                <button class="btn btn-primary btn-small js-ajax-submit" type="submit"><?php echo L('SORT');?></button>

            </div>
              
        </form>



    </div>

    <script src="/public/js/common.js"></script>

    <script src="/public/layui/layui.js"></script>

    <link rel="stylesheet" type="text/css" href="/public/layui/css/layui.css">

</body>

</html>