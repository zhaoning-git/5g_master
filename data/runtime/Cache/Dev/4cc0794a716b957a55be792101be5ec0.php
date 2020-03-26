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

<!-- /头部 -->


<!-- 主体 -->
<style>
  #add-field{margin-top:-1px}
  .uk-nav-header{text-transform: none;}
</style>
<table class="main-table" width="100%" border="0" style="margin-top:30px;">
  <tr>
    <td valign="top" style="border-right:1px solid #eee;" width="175">
      <div class="hbox" style=" width:175px; min-height:750px;  overflow:auto;">
        <ul class="uk-nav uk-nav-side" data-uk-nav="">
          <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li class="uk-nav-header" style="font-size:17px; font-weight:500"><?php echo ($key); ?></li>
            <?php if(is_array($vo)): $i = 0; $__LIST__ = $vo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?><li class="<?php if(($_GET['id']) == $sub['id']): ?>active<?php endif; ?>">
                <a href="<?php echo U('Php',array('id'=>$sub['id']));?>" style="font-size:13px">
                  <span style="color:#999">└</span> <?php echo ($sub["api_title"]); ?>
                </a> 
              </li><?php endforeach; endif; else: echo "" ;endif; ?>
            <li class="uk-nav-divider"></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
      </div>
      <!--<div style="height:180px"></div>-->
    </td>
    <td style="padding:0px 15px" valign="top">
      <form action="/index.php?g=Dev&amp;m=Index&amp;a=Php&amp;id=252" method="post"  class="uk-form edit-api">
        <a href="<?php echo U('php');?>"  class="uk-button uk-button-success uk-radius" style="margin-bottom:10px">新添加接口</a>
        <div class="uk-panel">
          <div class=" uk-panel-box">
            <table class="uk-table" width="100%">
              <caption>接口基本信息</caption>
              <tr>
                <td width="18%">接口名称：</td>
                <td width="82%"><input type="text" name="api_title" style="width:300px" value="<?php echo ($info["api_title"]); ?>"/></td>
              </tr>
              <tr>
                <td>接口网址：</td>
                <td><input type="text" name="api_url" style="width:350px" value="<?php echo ($info["api_url"]); ?>"/></td>
              </tr>
              <tr>
                <td>接口类型</td>
                <td>
                  <select name="api_type" style="width:90px">
                    <option value="get" <?php if(($info["api_type"]) == "get"): ?>selected="selected"<?php endif; ?>>Get</option>
                    <option value="post" <?php if(($info["api_type"]) == "post"): ?>selected="selected"<?php endif; ?>>Post</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td>需要用户令牌</td>
                <td>
                  <label class="uk-radio-inline">
                    <input type="radio" value="1" name="api_token" <?php if(($info["api_token"]) == "1"): ?>checked<?php endif; ?>>需要
                  </label>
                  <label class="uk-radio-inline">
                    <input type="radio" name="api_token" value="0" <?php if(($info["api_token"]) == "0"): ?>checked<?php endif; ?>>不需要
                  </label>
                </td>
              </tr>
              <tr>
                <td>接口分组</td>
                <td><input type="text" name="api_category" style="width:200px" value="<?php echo ($info["api_category"]); ?>"/></td>
              </tr>
              <tr>
                <td>接口作者</td>
                <td><input type="text" name="api_oauth" style="width:100px"  value="<?php echo ($info["api_oauth"]); ?>"/></td>
              </tr>
              <tr>
                <td>接口说明</td>
                <td><textarea name="api_remark" rows="2" cols="20" style="height: 100px; width: 500px;"><?php echo ($info["api_remark"]); ?></textarea></td>
              </tr>
              
              
              
              
            </table>
          </div>
        </div>
        <div class="uk-panel" style="margin-top:20px">
          <div class=" uk-panel-box">
            <table class="uk-table" width="100%">
              <caption>接口字段管理</caption>
              <thead>
                <tr>
                  <th>字段名</th>
                  <th>类型</th>
                  <th>字段意义</th>
                  <th>演示值</th>
                  <th>必填</th>
                  <th>说明</th>
                  <th>删除</th>
                </tr>
              </thead>
              <tbody  id="field-box">
              <?php if(is_array($info["api_field"])): $i = 0; $__LIST__ = $info["api_field"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                  <!--字段名-->
                  <td>
                    <div class="uk-form-icon">
                      <i class="uk-icon-clone"></i>
                      <input type="text" name="field[name][]" value="<?php echo ($vo["name"]); ?>" class="uk-width-1-1"/>
                    </div>
                  </td>

                  <!--字段类型-->
                  <td>
                    <div class="uk-form-icon">
                      <select name="field[type][]" style="width:90px">
                        <option value="字符串" <?php if(($vo["type"]) == "字符串"): ?>selected<?php endif; ?>>字符串</option>
                        <option value="数字" <?php if(($vo["type"]) == "数字"): ?>selected<?php endif; ?>>数字</option>
                        <option value="邮箱" <?php if(($vo["type"]) == "邮箱"): ?>selected<?php endif; ?>>邮箱</option>
                        <option value="手机" <?php if(($vo["type"]) == "手机"): ?>selected<?php endif; ?>>手机</option>
                        <option value="日期" <?php if(($vo["type"]) == "日期"): ?>selected<?php endif; ?>>日期</option>
                        <option value="文本" <?php if(($vo["type"]) == "文本"): ?>selected<?php endif; ?>>文本</option>
                      </select>
                    </div>
                  </td>

                  <!--字段意义-->
                  <td>
                    <div class="uk-form-icon">
                      <i class="uk-icon-ellipsis-h"></i>
                      <input type="text" name="field[remark][]" value="<?php echo ($vo["remark"]); ?>" class="uk-width-1-1"/>
                    </div>
                  </td>

                  <!--演示值-->
                  <td>
                    <div class="uk-form-icon">
                      <i class="uk-icon-pencil"></i>
                      <input type="text" name="field[value][]" value='<?php echo ($vo["value"]); ?>' class="uk-width-1-1"/>
                    </div>
                  </td>

                  <!--是否必填-->
                  <td>
                    <div class="uk-form-icon" style="width:66px;padding-top:4px;">
                      <label class="uk-radio-inline">
                        <input type="radio" value="1" name="field[is_must][<?php echo ($key); ?>]" <?php if(($vo["is_must"]) == "1"): ?>checked<?php endif; ?>>是
                      </label>
                      <label class="uk-radio-inline">
                        <input type="radio" value="0" name="field[is_must][<?php echo ($key); ?>]" <?php if(($vo["is_must"]) == "0"): ?>checked<?php endif; ?>>否
                      </label>
                    </div>
                  </td>

                  <!--说明-->
                  <td>
                    <div class="uk-form-icon">
                      <textarea name="field[intro][]"><?php echo ($vo["intro"]); ?></textarea>
                    </div>
                  </td>

                  <!--删除字段-->
                  <td>
                    <button type="button" class="uk-button uk-button-danger uk-button-sm uk-round delete-field">
                      <i class="uk-icon-close"></i>
                    </button>
                  </td>
                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
              </tbody>
              <tr>
                <td colspan="4" style="padding:10px; padding-bottom:0px; text-align:center; border:none">
              <?php $inums = empty($info['api_field'])?0:$key+1; ?>
              <button data-inum="<?php echo ($inums); ?>" id="add-field" type="button" class="uk-button uk-button-block uk-button-default">
                <i class="uk-icon-pencil"></i> 添加字段
              </button>
              </td>
              </tr>
            </table>
          </div>
        </div>
        <div class="uk-g" style=" padding-top:20px">
          <?php echo W('Core/Ueditor/editor',array('api_remark','api_remark',$info['api_remark'],'100%','300px'));?>
        </div>
        <div class="uk-g" style="margin-top:10px; overflow:hidden">
          <input type="hidden" name="id" value="<?php echo ($info["id"]); ?>" />
          <button type="submit" data-ajax="post" class="uk-button uk-button-primary uk-fl uk-radius">确定提交</button>
          <?php if(!empty($info["id"])): ?><a href="<?php echo U('App',array('id'=>$info['id']));?>" target="_blank" class="uk-button uk-button-default uk-fl uk-radius" style="margin-left:20px">调试接口</a><?php endif; ?>
          <a href="<?php echo U('DelApi',array('id'=>$info['id']));?>" data-ajax="get" class="uk-button uk-fr uk-button-danger uk-radius" data-info='{"title":"温馨提示","desc":"确定要删除该接口吗？看好了，别删错了","button":"确定"}'>删除接口</a> 
        </div>
      </form>
    </td>
  </tr>
</table>
<div class="uk-cf"></div>

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

<script type="text/javascript">
  $("#add-field").click(function () {
      var inum = $(this).data('inum');
      var html = '';
      html += '<tr>';
      html += ' <td><div class="uk-form-icon"> <i class="uk-icon-clone"></i><input type="text" name="field[name][]"  class="uk-width-1-1"/></div></td>';

      html += ' <td><div class="uk-form-icon">' +
              '<select name="field[type][]" style="width:60px">' +
              '<option value="字符串">字符串</option>' +
              '<option value="数字">数字</option>' +
              '<option value="邮箱">邮箱</option>' +
              '<option value="手机">手机</option>' +
              '<option value="日期">日期</option>' +
              '<option value="文本">文本</option>' +
              '</select>' +
              '</div></td>';

      html += '<td><div class="uk-form-icon"> <i class="uk-icon-ellipsis-h"></i><input type="text" name="field[remark][]"  class="uk-width-1-1"/></div></td>';

      html += '<td><div class="uk-form-icon"> <i class="uk-icon-pencil"></i><input type="text" name="field[value][]"  class="uk-width-1-1"/></div></td>';


      html += '<td><div class="uk-form-icon" style="width:66px;padding-top:4px;">' +
              '<label class="uk-radio-inline"><input type="radio" value="1" name="field[is_must][' + inum + ']"> 是 </label> ' +
              '<label class="uk-radio-inline"><input type="radio" value="0" name="field[is_must][' + inum + ']" checked> 否 </label>' +
              '</div></td>';


<!--说明-->
      html += '<td><div class="uk-form-icon"><textarea name="field[intro][]"></textarea></div></td>';


      html += ' <td> <button type="button" class="uk-button uk-button-danger uk-button-sm uk-round delete-field"><i class="uk-icon-close"></i></button></td>';


      html += '</tr>';
      $(this).data('inum', inum + 1);
      $('#field-box').append(html);
  });

  $(document).on('click', '.delete-field', function () {
      $(this).closest('tr').remove();
  });
</script> 
</body>
</html>