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
<style type="text/css">
body, td, th {
	font-size: 14px;
}
</style>
<body>
<style>
    input{
        width:500px;
    }
    .form-horizontal textarea{
        width:500px;
    }
    .nav-tabs>.current>a{
        color: #95a5a6;
        cursor: default;
        background-color: #fff;
        border: 1px solid #ddd;
        border-bottom-color: transparent;
    }
    .nav li
    {
        cursor:pointer
    }
    .nav li:hover
    {
        cursor:pointer
    }
    .hide{
        display:none;
    }
	  input{width: 190px;}
  </style>
<div class="wrap js-check-wrap">
  <ul class="nav nav-tabs js-tabs-nav">
    <li><a>发起方</a></li>
    <li><a>参与方</a></li>
  </ul>
  <form method="post" class="form-horizontal js-ajax-form" action="<?php echo U('Jingcai/index');?>">
    <div class="js-tabs-content"> 
      <!-- 发起方 -->
      <div>
        <fieldset>
          <div class="control-group">
            <label class="control-label">固定底注范围</label>
            <div class="controls">
              <input type="text" name="post[fq_dizhu_fw_1]" value="<?php echo ($config['fq_dizhu_fw_1']); ?>" style="width:80px;">
              -
              <input type="text" name="post[fq_dizhu_fw_2]" value="<?php echo ($config['fq_dizhu_fw_2']); ?>" style="width:80px;">
              <label class="radio inline"> 
				<input type="radio" value="fw" name="post[fq_dizhu_radio]" 
                <?php if(($config['fq_dizhu_radio']) == "fw"): ?>checked="checked"<?php endif; ?>
                <?php if(empty($config)): ?>checked<?php endif; ?>
                >生效 
		      </label>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">浮动底注%</label>
            <div class="controls">
              <input type="text" name="post[fq_dizhu_fd]" value="<?php echo ($config['fq_dizhu_fd']); ?>" style="width:190px;">
              <label class="radio inline">
				<input type="radio" value="fd" name="post[fq_dizhu_radio]" 
                <?php if(($config['fq_dizhu_radio']) == "fd"): ?>checked="checked"<?php endif; ?>
                >生效 
		      </label>
            </div>
          </div>
		  <!--发起次数-->
          <div class="control-group">
            <label class="control-label">发起次数</label>
            <div class="controls">
              <input type="text" name="post[fq_cs_gd]" value="<?php echo ($config['fq_cs_gd']); ?>" style="width:190px;">
              <label class="radio inline">
			    <input type="radio" value="gd" name="post[fq_cs_radio]" 
                <?php if(($config['fq_cs_radio']) == "gd"): ?>checked="checked"<?php endif; ?>
                <?php if(empty($config)): ?>checked<?php endif; ?>
                >生效 
			  </label>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">浮动次数%</label>
            <div class="controls">
              <input type="text" name="post[fq_cs_fd]" value="<?php echo ($config['fq_cs_fd']); ?>" style="width:190px;">
              <label class="radio inline">
			    <input type="radio" value="fd" name="post[fq_cs_radio]" 
                <?php if(($config['fq_cs_radio']) == "fd"): ?>checked="checked"<?php endif; ?>
                >生效 </label>
            </div>
          </div>
	
		  <!--参与人数-->
          <div class="control-group">
            <label class="control-label">参与人数</label>
            <div class="controls">
              <input type="text" name="post[fq_rs_gd]" value="<?php echo ($config['fq_rs_gd']); ?>" style="width:190px;">
              <label class="radio inline">
			    <input type="radio" value="gd" name="post[fq_rs_radio]" 
                <?php if(($config['fq_rs_radio']) == "gd"): ?>checked="checked"<?php endif; ?>
                <?php if(empty($config)): ?>checked<?php endif; ?>
                >生效 
			  </label>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">浮动人数%</label>
            <div class="controls">
              <input type="text" name="post[fq_rs_fd]" value="<?php echo ($config['fq_rs_fd']); ?>" style="width:190px;">
              <label class="radio inline">
			    <input type="radio" value="fd" name="post[fq_rs_radio]" 
                <?php if(($config['fq_rs_radio']) == "fd"): ?>checked="checked"<?php endif; ?>
                >生效 </label>
            </div>
          </div>
	
	
	
          <div class="control-group">
            <label class="control-label">结算输上限</label>
            <div class="controls">
              <input type="text" name="post[fq_js_shu]" value="<?php echo ($config['fq_js_shu']); ?>">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">结算赢上限</label>
            <div class="controls">
              <input type="text" name="post[fq_js_ying]" value="<?php echo ($config['fq_js_ying']); ?>">
            </div>
          </div>
        </fieldset>
      </div>
      <!-- 参与方 -->
      <div>
        <fieldset>
          <div class="control-group">
            <label class="control-label">固定底注范围</label>
            <div class="controls">
              <input type="text" name="post[cy_dizhu_fw_1]" value="<?php echo ($config['cy_dizhu_fw_1']); ?>" style="width:80px;">
              -
              <input type="text" name="post[cy_dizhu_fw_2]" value="<?php echo ($config['cy_dizhu_fw_2']); ?>" style="width:80px;">
              <label class="radio inline">
				<input type="radio" value="fw" name="post[cy_dizhu_radio]" 
                <?php if(($config['cy_dizhu_radio']) == "fw"): ?>checked="checked"<?php endif; ?>
                <?php if(empty($config)): ?>checked<?php endif; ?>
                >生效 
			  </label>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">浮动底注%</label>
            <div class="controls">
              <input type="text" name="post[cy_dizhu_fd]" value="<?php echo ($config['cy_dizhu_fd']); ?>" style="width:190px;">
              <label class="radio inline">
				<input type="radio" value="fd" name="post[cy_dizhu_radio]" 
                <?php if(($config['cy_dizhu_radio']) == "fd"): ?>checked="checked"<?php endif; ?>
                >生效
		      </label>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">结算输上限</label>
            <div class="controls">
              <input type="text" name="post[cy_js_shu]" value="<?php echo ($config['cy_js_shu']); ?>">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">结算赢上限</label>
            <div class="controls">
              <input type="text" name="post[cy_js_ying]" value="<?php echo ($config['cy_js_ying']); ?>">
            </div>
          </div>
        </fieldset>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary js-ajax-submit"><?php echo L('SAVE');?></button>
    </div>
  </form>
</div>
<script src="/public/js/common.js"></script> 
<script type="text/javascript" src="/public/js/content_addtop.js"></script>
</body>
</html>