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
			<li class="active"><a >消费记录</a></li>
		</ul>
		<?php $type=array("income"=>"收入","expend"=>"支出"); $action=array("sendgift"=>"赠送礼物","sendbarrage"=>"弹幕","loginbonus"=>"登录奖励","buyvip"=>"购买VIP","buycar"=>"购买坐骑","buyliang"=>"购买靓号",'game_bet'=>'游戏下注','game_return'=>'游戏退还','game_win'=>'游戏获胜','game_banker'=>'庄家收益','set_deposit'=>'上庄扣除','deposit_return'=>'下庄退还','roomcharge'=>'房间扣费','timecharge'=>'计时扣费','sendred'=>'发送红包','robred'=>'抢红包','buyguard'=>'开通守护','reg_reward'=>'注册奖励'); ?>
		<form class="well form-search" method="post" action="<?php echo U('Coinrecord/index');?>">
            <?php if($showlevel == 0): ?>代理商：
			<select class="select_2" name="proxyid" id="proxyid">
				<option value="">全部</option>
			</select><?php endif; ?>
            <?php if($showlevel < 2): ?>推广员：
			<select class="select_2" name="promoterid" id="promoterid">
				<option value="">全部</option>
                <?php if(is_array($promoterlist)): $i = 0; $__LIST__ = $promoterlist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v['id']); ?>" <?php if($formget["promoterid"] == $v['id']): ?>selected<?php endif; ?> ><?php echo ($v['user_nicename']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>	
			</select><?php endif; ?>
			收支类型： 
			<select class="select_2" name="type">
				<option value="">全部</option>
				<?php if(is_array($type)): $i = 0; $__LIST__ = $type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($formget["type"] == $key): ?>selected<?php endif; ?> ><?php echo ($v); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>

			</select> &nbsp;&nbsp;
			收支行为： 
			<select class="select_2" name="action">
				<option value="">全部</option>
				<?php if(is_array($action)): $i = 0; $__LIST__ = $action;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($formget["action"] == $key): ?>selected<?php endif; ?> ><?php echo ($v); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
			</select> &nbsp;&nbsp;
			赠送时间：
			<input type="text" name="start_time" class="js-date date" value="<?php echo ($formget["start_time"]); ?>" style="width: 80px;" autocomplete="off">-
			<input type="text" class="js-date date" name="end_time" value="<?php echo ($formget["end_time"]); ?>" style="width: 80px;" autocomplete="off"> &nbsp; &nbsp;
			会员： 
			<input type="text" name="uid" style="width: 200px;" value="<?php echo ($formget["uid"]); ?>" placeholder="请输入会员ID值...">
			主播： 
			<input type="text" name="touid" style="width: 200px;" value="<?php echo ($formget["touid"]); ?>" placeholder="请输入主播ID值...">
			<input type="submit" class="btn btn-primary" value="搜索">
		</form>		
		
		<form method="post" class="js-ajax-form">
	
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th align="center">ID</th>
						<th>收支类型</th>
						<th>收支行为</th>
						<th>会员 (ID)</th>
						<th>主播 (ID)</th>
						<th>行为说明 (ID)</th>
						<th>数量</th>
						<th>总价</th>
						<th>直播id</th>
						<th>时间</th>

						<!-- <th align="center"><?php echo L('ACTIONS');?></th> -->
					</tr>
				</thead>
				<tbody>
					
					<?php if(is_array($lists)): foreach($lists as $key=>$vo): ?><tr>
						<td align="center"><?php echo ($vo["id"]); ?></td>
						<td><?php echo ($type[$vo['type']]); ?></td>
						<td><?php echo ($action[$vo['action']]); ?></td>
						<td><?php echo ($vo['userinfo']['user_nicename']); ?> ( <?php echo ($vo['uid']); ?> )</td>
						<td><?php echo ($vo['touserinfo']['user_nicename']); ?> ( <?php echo ($vo['touid']); ?> )</td>
						<td><?php echo ($vo['giftinfo']['giftname']); ?> ( <?php echo ($vo['giftid']); ?> )</td>
						<td><?php echo ($vo['giftcount']); ?></td>
						<td><?php echo ($vo['totalcoin']); ?></td>
						<td><?php echo ($vo['showid']); ?></td>
	
						<td><?php echo (date("Y-m-d H:i:s",$vo["addtime"])); ?></td>

						<!-- <td align="center">	 -->
							<!-- <a href="<?php echo U('Coinrecord/edit',array('id'=>$vo['id']));?>" >编辑</a> |
							<a href="<?php echo U('Coinrecord/del',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="您确定要删除吗？">删除</a> -->
						<!-- </td> -->
					</tr><?php endforeach; endif; ?>
				</tbody>
			</table>
			<div class="pagination"><?php echo ($page); ?></div>

		</form>
	</div>
	<script src="/public/js/common.js"></script>
    <script>
        var list='<?php echo ($proxylistj); ?>';
        var proxyid='<?php echo ($formget['proxyid']); ?>';
        var promoterid='<?php echo ($formget['promoterid']); ?>';
        var lists='';
        if(list){
            lists=typeof(list)=='object'?list:JSON.parse(list);
        }
        
        
        function cteateProxyid(){
            if(!lists){
                return !1;
            }
            var proxy_html='<option value="">全部</option>';
            for(var k in lists){
                var v=lists[k];
                if(k==proxyid){
                    proxy_html+='<option value="'+v.id+'" selected>'+v.user_nicename+'</option>';
                }else{
                    proxy_html+='<option value="'+v.id+'">'+v.user_nicename+'</option>';
                }
            }
            
            $("#proxyid").html(proxy_html);
            if(proxyid){
                cteatePromoterid();
            }
        }
        
        function cteatePromoterid(){
            if(!lists){
                return !1;
            }
            var promoter_html='<option value="">全部</option>';
            
            if(proxyid){
            var list2=lists[proxyid]['list'];

                for(var k in list2){
                    var v=list2[k];
                    if(k==promoterid){
                        promoter_html+='<option value="'+v.id+'" selected>'+v.user_nicename+'</option>';
                    }else{
                        promoter_html+='<option value="'+v.id+'">'+v.user_nicename+'</option>';
                    }
                    
                }
            }
            $("#promoterid").html(promoter_html);
        }

        cteateProxyid();

        $("#proxyid").change(function(){
            proxyid=$(this).val();
            cteatePromoterid();
        })
        
    </script>
</body>
</html>