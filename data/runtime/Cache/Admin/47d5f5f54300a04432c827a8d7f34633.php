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
            <li class="active"><a href="<?php echo U('Brandcooperation/Brandcooperationshow');?>">列表</a></li>
            <li><a href="<?php echo U('Brandcooperation/add');?>">添加</a></li>
        </ul>
        <form method="post" class="js-ajax-form" action="<?php echo U('Chargerules/listorders');?>">
          <div class="table-actions">
                <button class="btn btn-primary btn-small js-ajax-submit" type="submit"><?php echo L('SORT');?></button>
            </div>
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                     
                        <th align="center">ID</th>
                        <th>姓名</th>
                        <th>身份证号码</th>
                        <th>营业执照</th>
                        <th>组织机构代码</th>
                        <th>法人身份证</th>
                        <th>创建时间</th>
                        

                        <th align="center"><?php echo L('ACTIONS');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(is_array($data)): foreach($data as $key=>$vo): ?><tr>
                     
                        <td align="center"><?php echo ($vo["id"]); ?></td>
                        <td><?php echo ($vo['name']); ?></td>
                        <td><?php echo ($vo['id_number']); ?></td>
                        <td><img style="max-height: 60px;" src="<?php echo ($vo['business_license']); ?>" alt="营业执照" /></td>
                        <td><img style="max-height: 60px;" src="<?php echo ($vo['organization_code']); ?>" alt="组织机构代码" /></td>
                        <td><img style="max-height: 60px;" src="<?php echo ($vo['identity_card']); ?>" alt="法人身份证" /></td>
                        <td><?php echo (date('Y-m-d H:i:s',$vo['add_time'])); ?></td>
                        
                        <td align="center">    
                            <a href="<?php echo U('Brandcooperation/edit',array('id'=>$vo['id']));?>" >编辑</a>
                            <a href="<?php echo U('Brandcooperation/del',array('id'=>$vo['id']));?>"  class="js-ajax-dialog-btn" data-msg="您确定要删除吗？">删除</a>
                        </td>
                    </tr><?php endforeach; endif; ?>
                </tbody>
            </table>
            <div class="pagination"><?php echo ($page); ?></div>
                <div class="table-actions">
                <button class="btn btn-primary btn-small js-ajax-submit" type="submit"><?php echo L('SORT');?></button>
            </div>
        </form>
         <div id="laypage"></div>
    </div>
    <script src="/public/js/common.js"></script>
    <script type="text/javascript" src="/public/laypage/1.2/laypage.js"></script>
</body>
</html>
<script type="text/javascript">
    var url = "<?php echo U('Brandcooperation/Brandcooperationshow');?>";
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