<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
<link rel="stylesheet" href="/application/Dev/Static/css/uikit.gradient.css"/>
<link rel="stylesheet" href="/application/Dev/Static/css/mobile.ui.css">
<link rel="stylesheet" href="/application/Dev/Static/css/app.css">
<title><?php echo ($title); ?></title>
<script src="/application/Dev/Static/js/jquery.min.js"></script>
<script type="text/javascript">
	var ThinkPHP = window.Think = {
		"ROOT": "", //当前网站地址
		"APP": "/index.php", //当前项目地址
		"PUBLIC": "/public", //项目公共目录地址
		"DEEP": "<?php echo C('URL_PATHINFO_DEPR');?>", //PATHINFO分割符
		"MODEL": "",
		"VAR": ["<?php echo C('VAR_MODULE');?>", "<?php echo C('VAR_CONTROLLER');?>", "<?php echo C('VAR_ACTION');?>"],
		'URL_MODEL': "/index.php/Dev",
		'MODULE_PATH': "<?php echo MODULE_PATH;?>",
		'IMG': '/application/Dev/Static/img',
		'IMG_UPLOAD_URL': "<?php echo U('Core/File/uploadPicture');?>",
		'IMG_UPLOAD_SIZE': "<?php echo (C("PICTURE_UPLOAD.maxSize")); ?>",
		'IMG_UPLOAD_EXT': '<?php echo (C("PICTURE_UPLOAD.exts")); ?>'
		 
	}
</script>
</head>
<body>
	<!-- 头部 -->
  	<nav class="tm-navbar uk-navbar uk-navbar-attached">
  <div class="uk-container uk-container-center">
      <a class="uk-navbar-brand uk-hidden-small" href="<?php echo U('Index/index');?>" style="color:#fff; font-size:25px">
      <i class="uk-icon-code" style="font-size:20px; text-align:center; color:#fff; background:#3dc0f1; border-radius:100%; width:40px; height:40px; line-height:40px"></i> ApiTools
      </a>

      <ul class="uk-navbar-nav uk-hidden-small">
        <?php if(session('ok') == 'php'){ ?> <li <?php if(strtolower(ACTION_NAME) == strtolower('Php')){ ?> class="uk-active" <?php } ?>><a href="<?php echo U('Php');?>">接口发布</a></li>  <?php } ?>
        <li <?php if(ACTION_NAME == App): ?>class="uk-active"<?php endif; ?>><a href="<?php echo U('App');?>">接口调试</a></li>
        <li <?php if(ACTION_NAME == Debug): ?>class="uk-active"<?php endif; ?>><a href="<?php echo U('Debug');?>">接口日志</a></li>
        <li <?php if(ACTION_NAME == WebSocket): ?>class="uk-active"<?php endif; ?>><a href="<?php echo U('WebSocket');?>">WebSocket</a></li>
        <li><a href="###">待续...</a></li>
      </ul>


  </div>
</nav>

    <div style="text-align:center; margin-top:120px"><i class="uk-icon-code" style="font-size:100px; color:#fff; background:#3dc0f1; border-radius:100%; width:200px; height:200px; line-height:200px"></i></div>
    <div style="text-align:center; margin-top:26px; font-size:25px; color:#555">接口发布工具</div>
    
    <form  action="/index.php?g=Dev&amp;m=Index&amp;a=index" class="uk-form" method="post" style="text-align:center; width:380px; margin:0 auto; margin-top:30px;">
    
    <div class="uk-form-row">
    <div class="uk-form-icon">
        <i class="uk-icon-key"></i>
        <input type="text" name="password" class="uk-form-large" style="width:380px;" placeholder="请瞎蒙一个管理密码">
    </div>
    </div>
    <div class="uk-form-row" style=" overflow:hidden">
    <div style="float:left; color:#888; margin-top:10px">忘记密码，那也没有办法，我们基本都靠蒙</div> <button data-ajax="post" class="uk-button uk-button-primary uk-button-large" type="submit" style="float:right">登陆</button>
    </div>
    </form>
	<!-- /主体 -->

	<!-- 底部 -->
	
<div style="height:180px"></div>
<div style="width:100%; height:100px; background:#333; position:fixed; bottom:0px;left:0px; color:#999;text-align:center; z-index:999999"><table width="100%" border="0">
  <tr>
    <td height="100" style=" padding:0px 20px; "> 这只是一个管理接口的工具，你之所以看到这一行文字，纯粹是为了装饰，凑字数 <br />顺带一提：本程序版权归我所有，如果侵权我也没招儿</td>
  </tr>
</table>
</div>



<script src="/application/Dev/Static/js/uikit.js"></script>
<script src="/application/Dev/Static/js/mobile.ui.js"></script>
<script>
//$('.hbox').css('height',$('.uk-form').height()+'px').animate({scrollTop:$('.uk-nav-side .active').offset().top-500});
</script>





	<!-- /底部 -->
</body>
</html>