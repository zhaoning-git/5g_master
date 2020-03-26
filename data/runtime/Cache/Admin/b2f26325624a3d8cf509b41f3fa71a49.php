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
            <li class="active"><a >球队认证列表</a></li>
            
        </ul>
        <form method="post" class="js-ajax-form" action="<?php echo U('Gift/listorders');?>">
            <div class="table-actions">
                <button class="btn btn-primary btn-small js-ajax-submit" type="submit"><?php echo L('SORT');?></button>
            </div>

            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th align="center">ID</th>
                       
                        <th>球队名称</th>
                        <th>姓名</th>
                        <th>身份证号</th>

                        <th>手持身份证</th>
                        <th>个人介绍</th>
                        <th>图片证明</th>
                       
                        <th>手机号</th>
                        <th>用户手机号</th>
                        <th>审核状态</th>
                        <th align="center"><?php echo L('ACTIONS');?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr>
                        <td align="center"><?php echo ($vo["ids"]); ?></td>
                        <td><?php echo ($vo["team"]); ?></td>
                     
                        <td><?php echo ($vo["names"]); ?></td>
                        <td><?php echo ($vo["idnumber"]); ?></td>
                       
                        <td><img src="<?php echo C('url'); echo ($vo["cradselfs"]); ?>" id="thumb1_preview" width="50" style="cursor: hand" /></td>
                         <td><?php echo ($vo["introduction"]); ?></td>
                        <td><img src="<?php echo C('url'); echo ($vo["proves"]); ?>" id="thumb1_preview" width="50" style="cursor: hand" /></td>
                        <td><?php echo ($vo["mobiles"]); ?></td>
                        <td><?php echo ($vo["usermobile"]); ?></td>
                        <?php if($vo['is_audit'] == '1'): ?><td align="center">待审核</td><?php endif; ?>
                        <?php if($vo['is_audit'] == '2'): ?><td align="center">审核通过</td><?php endif; ?>
                    <td align="center">	
                    
                    <a href="<?php echo U('Organicon/temdit',array('id'=>$vo['ids']));?>" >编辑</a>
                    |
                    <a href="<?php echo U('Order/del',array('id'=>$vo.id));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除吗？">删除</a>
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
</body>
</html>