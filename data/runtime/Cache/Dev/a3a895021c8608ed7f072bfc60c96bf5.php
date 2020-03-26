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

  
    <style>
   .iToken{
       font-size:30px; 
       text-align:center; 
       color:#fff; 
       background:#8fce48; 
       border-radius:100%; 
       width:90px; 
       height:90px; 
       line-height:90px; 
       display:inline-block;
    }
    
  .uk-alert {
      position: relative;
      margin-bottom: 20px;
      padding: 15px 29px 15px 15px;
      background: #f8f8f8;
      color: #666;
      display:none;
  }  
    
  .uk-alert-primary {
      background: #d8eafc;
      color: #1e87f0;
  }  
  .uk-label-success {
      background-color: #32d296 !important;
  }
  
  .uk-label-warning {
      background-color: #faa05a !important;
  }
  .uk-label {
      display: inline-block;
      padding: 0 5px;
      background: #1e87f0;
      line-height: 1.5;
      font-size: 12px;
      color: #fff;
      vertical-align: middle;
      white-space: nowrap;
      border-radius: 2px;
  }
  #json{width:100%; height:120px; font-size:14px; line-height:20px; margin-top:20px;}
  <?php if(($info["api_token"]) == "1"): ?>.SignBox{background:#d8eafc;color: #666; border-top:none;border-radius:0px 0px 4px 4px !important;} 
  .SignBox .field-box{border-bottom:none;} 
  .uk-panel-box{border-radius:4px 4px 0px 0px;}<?php endif; ?>
  .uk-nav-header{text-transform: none;}
  </style>

  
  <table class="main-table"  width="100%" border="0" style=" ;margin-top:30px;">
  <tr>
    <td valign="top" style="border-right:1px solid #eee;" width="250">
      <div class="hbox" style=" width:250px; min-height:750px; overflow:auto">
          <ul class="uk-nav uk-nav-side" data-uk-nav="" style="overflow:auto">
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li class="uk-nav-header" style="font-size:17px; font-weight:500"><?php echo ($key); ?></li>
              <?php if(is_array($vo)): $i = 0; $__LIST__ = $vo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?><li class="<?php if(($_GET['id']) == $sub['id']): ?>active<?php endif; ?> ">
                <a href="<?php echo U('App',array('id'=>$sub['id']));?>" style="font-size:13px">
                  <span style="color:#999">└</span> <?php echo ($sub["api_title"]); ?>
                </a>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
              <li class="uk-nav-divider"></li><?php endforeach; endif; else: echo "" ;endif; ?>
          </ul>
        </div>
      <!--<div style="height:180px"></div>-->
    </td>
    <td style="padding:0px 30px" valign="top">
       <?php if(empty($info["id"])): ?><div style=" width:100%; text-align:center;">
          <svg version="1.1" id="clock" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="250px" height="250px" viewBox="50 50 400 500"  xml:space="preserve" style="margin-right:150px;">
        <circle id="face" fill="#F4F3ED" cx="243.869" cy="250.796" r="130.8"/>
        <path id="rim" fill="#383838" d="M243.869,101.184c-82.629,0-149.612,66.984-149.612,149.612c0,82.629,66.983,149.612,149.612,149.612
            S393.48,333.425,393.48,250.796S326.498,101.184,243.869,101.184z M243.869,386.455c-74.922,0-135.659-60.736-135.659-135.659
            c0-74.922,60.737-135.659,135.659-135.659c74.922,0,135.658,60.737,135.658,135.659
            C379.527,325.719,318.791,386.455,243.869,386.455z"/>
        <g id="inner">
            <g opacity="0.2">
                <path fill="#C4C4C4" d="M243.869,114.648c-75.748,0-137.154,61.406-137.154,137.153c0,75.749,61.406,137.154,137.154,137.154
                    c75.748,0,137.153-61.405,137.153-137.154C381.022,176.054,319.617,114.648,243.869,114.648z M243.869,382.56
                    c-72.216,0-130.758-58.543-130.758-130.758s58.542-130.758,130.758-130.758c72.216,0,130.758,58.543,130.758,130.758
                    S316.085,382.56,243.869,382.56z"/>
            </g>
            <g>
                <path fill="#282828" d="M243.869,113.637c-75.748,0-137.154,61.406-137.154,137.153c0,75.749,61.406,137.154,137.154,137.154
                    c75.748,0,137.153-61.405,137.153-137.154C381.022,175.043,319.617,113.637,243.869,113.637z M243.869,381.548
                    c-72.216,0-130.758-58.542-130.758-130.757c0-72.216,58.542-130.758,130.758-130.758c72.216,0,130.758,58.543,130.758,130.758
                    C374.627,323.005,316.085,381.548,243.869,381.548z"/>
            </g>
        </g>
        <g id="markings">
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="243.5" y1="139" x2="243.5" y2="133"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="231.817" y1="139.651" x2="231.19" y2="133.684"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="220.266" y1="141.52" x2="219.018" y2="135.65"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="208.973" y1="144.585" x2="207.119" y2="138.879"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="198.063" y1="148.814" x2="195.623" y2="143.333"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="187.655" y1="154.161" x2="184.655" y2="148.965"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="177.862" y1="160.566" x2="174.335" y2="155.712"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="168.792" y1="167.96" x2="164.778" y2="163.501"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="160.545" y1="176.262" x2="156.087" y2="172.246"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="153.211" y1="185.379" x2="148.358" y2="181.852"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="146.871" y1="195.214" x2="141.675" y2="192.213"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="141.593" y1="205.658" x2="136.112" y2="203.216"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="137.436" y1="216.596" x2="131.729" y2="214.741"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="134.445" y1="227.909" x2="128.576" y2="226.66"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="132.653" y1="239.472" x2="126.685" y2="238.843"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="132.079" y1="251.16" x2="126.079" y2="251.158"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="132.73" y1="262.843" x2="126.762" y2="263.468"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="134.598" y1="274.395" x2="128.729" y2="275.64"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="137.664" y1="285.688" x2="131.958" y2="287.539"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="141.893" y1="296.598" x2="136.412" y2="299.035"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="147.24" y1="307.006" x2="142.043" y2="310.004"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="153.645" y1="316.799" x2="148.791" y2="320.323"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="161.04" y1="325.868" x2="156.58" y2="329.881"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="169.341" y1="334.115" x2="165.325" y2="338.572"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="178.459" y1="341.449" x2="174.931" y2="346.302"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="188.294" y1="347.789" x2="185.292" y2="352.984"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="198.738" y1="353.066" x2="196.295" y2="358.548"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="209.676" y1="357.223" x2="207.82" y2="362.93"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="220.989" y1="360.214" x2="219.739" y2="366.084"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="232.552" y1="362.006" x2="231.922" y2="367.975"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="244.239" y1="362.58" x2="244.237" y2="368.582"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="255.921" y1="361.93" x2="256.547" y2="367.898"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="267.472" y1="360.062" x2="268.719" y2="365.932"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="278.765" y1="356.996" x2="280.619" y2="362.703"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="289.675" y1="352.767" x2="292.116" y2="358.248"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="300.083" y1="347.42" x2="303.083" y2="352.616"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="309.876" y1="341.015" x2="313.403" y2="345.869"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="318.946" y1="333.621" x2="322.96" y2="338.08"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="327.193" y1="325.319" x2="331.651" y2="329.334"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="334.527" y1="316.201" x2="339.38" y2="319.728"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="340.868" y1="306.367" x2="346.063" y2="309.367"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="346.146" y1="295.924" x2="351.626" y2="298.364"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="350.303" y1="284.986" x2="356.008" y2="286.84"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="353.294" y1="273.673" x2="359.162" y2="274.92"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="355.087" y1="262.11" x2="361.052" y2="262.737"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="356" y1="250.5" x2="362" y2="250.5"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="355.355" y1="238.781" x2="361.323" y2="238.153"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="353.489" y1="227.193" x2="359.359" y2="225.945"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="350.422" y1="215.864" x2="356.129" y2="214.01"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="346.188" y1="204.918" x2="351.669" y2="202.477"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="340.833" y1="194.474" x2="346.029" y2="191.474"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="334.415" y1="184.647" x2="339.269" y2="181.12"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="327.004" y1="175.545" x2="331.463" y2="171.529"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="318.684" y1="167.268" x2="322.699" y2="162.807"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="309.543" y1="159.905" x2="313.07" y2="155.049"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="299.684" y1="153.538" x2="302.683" y2="148.34"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="289.212" y1="148.237" x2="291.652" y2="142.753"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="278.245" y1="144.059" x2="280.097" y2="138.351"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="266.9" y1="141.05" x2="268.145" y2="135.179"/>
            <line fill="none" stroke="#3F3F3F" stroke-miterlimit="10" x1="255.302" y1="139.244" x2="255.927" y2="133.275"/>
            <polygon fill="#3F3F3F" points="247.391,133 243.5,141.05 239.609,133 	"/>
            <polygon fill="#3F3F3F" points="188.022,147.021 188.677,155.938 181.283,150.912 	"/>
            <polygon fill="#3F3F3F" points="143.617,188.848 148.643,196.243 139.726,195.588 	"/>
            <polygon fill="#3F3F3F" points="126.074,247.273 134.125,251.165 126.076,255.056 	"/>
            <polygon fill="#3F3F3F" points="140.095,306.644 149.013,305.988 143.988,313.382 	"/>
            <polygon fill="#3F3F3F" points="181.922,351.049 189.318,346.022 188.663,354.938 	"/>
            <polygon fill="#3F3F3F" points="240.349,368.591 244.24,360.54 248.13,368.589 	"/>
            <polygon fill="#3F3F3F" points="299.718,354.569 299.062,345.652 306.457,350.677 	"/>
            <polygon fill="#3F3F3F" points="344.123,312.742 339.096,305.348 348.012,306.002 	"/>
            <polygon fill="#3F3F3F" points="362,254.316 353.951,250.426 362,246.534 	"/>
            <polygon fill="#3F3F3F" points="347.934,194.779 339.018,195.435 344.042,188.04 	"/>
            <polygon fill="#3F3F3F" points="305.984,150.252 298.59,155.277 299.244,146.361 	"/>
            <rect x="282" y="152.98" fill="none" width="17.366" height="27.947"/>
            <text transform="matrix(1 0 0 1 282 174.4307)" fill="#303030" font-family="'Futura-Medium'" font-size="26">1</text>
            <rect x="320.699" y="188.474" fill="none" width="17.202" height="26.267"/>
            <text transform="matrix(1 0 0 1 320.6987 209.9229)" fill="#303030" font-family="'Futura-Medium'" font-size="26">2</text>
            <rect x="335.04" y="238.872" fill="none" width="21.03" height="24.585"/>
            <text transform="matrix(1 0 0 1 335.0396 260.3213)" fill="#303030" font-family="'Futura-Medium'" font-size="26">3</text>
            <rect x="319.699" y="290.242" fill="none" width="17.202" height="23.557"/>
            <text transform="matrix(1 0 0 1 319.6987 311.6914)" fill="#303030" font-family="'Futura-Medium'" font-size="26">4</text>
            <rect x="284.5" y="323.319" fill="none" width="19.212" height="22.511"/>
            <text transform="matrix(1 0 0 1 284.5 344.7695)" fill="#303030" font-family="'Futura-Medium'" font-size="26">5</text>
            <rect x="235.552" y="336.08" fill="none" width="19.938" height="24.15"/>
            <text transform="matrix(1 0 0 1 235.5522 357.5293)" fill="#303030" font-family="'Futura-Medium'" font-size="26">6</text>
            <rect x="189.373" y="322.319" fill="none" width="19.673" height="25.003"/>
            <text transform="matrix(1 0 0 1 189.3726 343.7695)" fill="#303030" font-family="'Futura-Medium'" font-size="26">7</text>
            <rect x="151.066" y="287.539" fill="none" width="17.726" height="25.203"/>
            <text transform="matrix(1 0 0 1 151.0664 308.9883)" fill="#303030" font-family="'Futura-Medium'" font-size="26">8</text>
            <rect x="136.392" y="241.25" fill="none" width="20.696" height="22.348"/>
            <text transform="matrix(1 0 0 1 136.3916 262.6992)" fill="#303030" font-family="'Futura-Medium'" font-size="26">9</text>
            <rect x="149.066" y="191.474" fill="none" width="36.554" height="27.122"/>
            <text transform="matrix(1 0 0 1 149.0664 212.9229)" fill="#303030" font-family="'Futura-Medium'" font-size="26">10</text>
            <rect x="184.967" y="158.518" fill="none" width="36.021" height="27.13"/>
            <text transform="matrix(1 0 0 1 184.9673 179.9668)" fill="#303030" font-family="'Futura-Medium'" font-size="26">11</text>
            <rect x="225.723" y="144.514" fill="none" width="37.029" height="29.25"/>
            <text transform="matrix(1 0 0 1 225.7227 165.9639)" fill="#303030" font-family="'Futura-Medium'" font-size="26">12</text>
        </g>
        <path id="hours" fill="#3A3A3A" d="M242.515,270.21c-0.44,0-0.856-0.355-0.926-0.79l-3.156-19.811c-0.069-0.435-0.103-1.149-0.074-1.588
            l4.038-62.009c0.03-0.439,0.414-0.798,0.854-0.798h0.5c0.44,0,0.823,0.359,0.852,0.798l4.042,62.205
            c0.028,0.439-0.015,1.152-0.097,1.584l-3.712,19.623c-0.082,0.433-0.508,0.786-0.948,0.786H242.515z"/>
        <path id="minutes" fill="#3A3A3A" d="M247.862,249.75l-2.866,24.244c-0.099,1.198-0.498,2.18-1.497,2.179c-0.999,0-1.397-0.98-1.498-2.179
                    l-2.861-24.508c-0.099-1.199,3.479-93.985,3.479-93.985c0.036-1.201-0.117-2.183,0.881-2.183c0.999,0,0.847,0.982,0.882,2.183
                    L247.862,249.75z"/>
        <g id="seconds">
            <line fill="none" stroke="#BF4116" stroke-miterlimit="10" x1="243.5" y1="143" x2="243.5" y2="266"/>
            <circle fill="none" stroke="#BF4116" stroke-miterlimit="10" cx="243.5" cy="271" r="5"/>
            <circle fill="#BF4116" cx="243.5" cy="251" r="3.917"/>
        </g>
        </svg>
          <script src="/application/Dev/Static/clock/snap.svg-min.js"></script>
          <script src="/application/Dev/Static/clock/index.js"></script>
        </div>
        <h3 style=" text-indent:2em;margin-top:2px; line-height:35px">
          <i style="font-size:30px" class="uk-icon-quote-left"></i> 
          这里放这个钟表并没有别的意思，我只是想告诉你：如果你再不点击左侧的接口开始工作，而是继续在这里纠结这个表的时间对不对，那你所剩下的时间就真的不多了
          <i style="font-size:30px; padding:0px; margin-left:-20px" class="uk-icon-quote-right"></i>
        </h3>


       <?php else: ?>
        
          <div class="uk-u-md-12 api-info uk-form">
            <table  width="100%">
            <tr>
              <td width="82%" style="font-size:18px; padding-bottom:10px">
                <?php echo ($info["api_title"]); ?> (<?php echo ($info["api_category"]); ?>)
              </td>
            </tr>
            <tr>
              <td>
                <?php if(($info["api_type"]) == "get"): ?><i style="font-size:30px; text-align:center; color:#fff; background: #996; border-radius:100%; width:90px; height:90px; line-height:90px; display: inline-block; margin-right:30px"><?php echo ($info["api_type"]); ?></i><?php endif; ?>
                <?php if(($info["api_type"]) == "post"): ?><i style="font-size:30px; text-align:center; color:#fff; background:#3dc0f1; border-radius:100%; width:90px; height:90px; line-height:90px; display: inline-block; margin-right:30px"><?php echo ($info["api_type"]); ?></i><?php endif; ?>
                <?php if(($info["api_token"]) == "1"): ?><a href="javascript:showAlert();" title="点击查看Sign获取方法"><i class="iToken">Sign</i></a>
                  <div class="uk-alert-primary uk-alert">
                    <a class="uk-close uk-icon"></a>
                    <p>
                      需携带用户令牌的接口都必须传递以下参数:<br/>
                      <strong>
                        _uid:用户ID<br/>
                        _sign:验证签名<br/>
                      </strong>
                      <hr/>
                      _sign的生成方法: _sign = MD5(_uid.API接口.token);
                      <br/>PS:用户登录(注册后)会获得用户详细信息,里面有字段<strong>token</strong>
                      <br/>PS:API接口即不带域名的API接口访问地址,全部小写,本接口的API接口:<strong><?php echo ($info["api_name"]); ?></strong>
                      <br/>
                      <input type="text" name="Tuid" value="" style="width:60px;" placeholder="_uid">
                      <input type="text" name="Tapi" value="<?php echo ($info["api_name"]); ?>" style="width:150px;" readonly>
                      <input type="text" name="Ttoken" value="" style="width:350px;" placeholder="token">
                      <button type="button" class="uk-button uk-button-block uk-button-default" >
                        <i class="uk-icon-pencil"></i>生成_sign
                      </button>
                      <strong class="TestSignBox"></strong>
                    </p>
                  </div><?php endif; ?>
              </td>
            </tr>
            <tr>
              <td width="82%" style="padding:20px 0">
                <span style="font-size:12px; color:#999">
                  接口请使用<?php echo ($info["api_type"]); ?>请求
                  <?php if(($info["api_token"]) == "1"): ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;需携带用户令牌<?php endif; ?>
                </span>
                <?php if(!empty($info["api_oauth"])): ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                  <span style="font-size:12px; color:#999">负责人:<?php echo ($info["api_oauth"]); ?></span><?php endif; ?>
              </td>
            </tr>
            <tr>
              <td>
                <div class="uk-form-row">
                  <div class="uk-form-icon">
                    <i class="uk-icon-link"></i>
                    <input type="text" id="url" value="<?php echo ($info["api_url"]); ?>" style="width:600px" class="uk-input uk-form-large" />
            <button class="uk-button uk-radius uk-button-default uk-button-large copy-url" data-clipboard-action="copy" data-clipboard-target="#url" id="copy_btn" type="button">
              <i class="uk-icon-copy"></i> 复制
            </button>
                  </div>
                </div>
              </td>
            </tr>
            </table>
          </div>
          <form  class="uk-form">
            <?php if(!empty($info["api_field"])): ?><div class="uk-panel" style="margin-top:20px">
                <div class="uk-panel-box" id="field-box">
                  <?php if(is_array($info["api_field"])): $i = 0; $__LIST__ = $info["api_field"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><ul class="field-box">
                      <li style="color:#aaa">
                        <strong><?php echo ($vo["name"]); ?></strong>
                       (<?php echo ($vo["remark"]); ?>)
                        <!--<span class="appType"><?php echo ($vo["type"]); ?></span>-->
                        <span class="uk-label uk-label-success">必填:<?php echo ($vo[is_must]?'是':'否'); ?></span>
                        <span class="uk-label uk-label-warning"><?php echo ($vo["intro"]); ?></span>
                      </li>
<!--                      <li class="uk-label uk-label-warning" style="width:40%;white-space: normal;"><?php echo ($vo["intro"]); ?> </li>-->
                      
                      <li style="color:#aaa;">预览值：
                      
                        <input type="text" name="<?php echo ($vo["name"]); ?>" value='<?php echo ($vo["value"]); ?>' style="width:200px"/>
                       </li>
                    </ul><?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
                <?php if(($info["api_token"]) == "1"): ?><div class="uk-panel-box SignBox">
                    <ul class="field-box">
                      <li style="color:#aaa">
                        <strong>_uid</strong>  (用户ID)
                      </li>
                      <li style="color:#aaa">参数值：</li>
                      <li><input style="width:100px" type="text" name="_uid" value=""></li>
                    </ul>
                    <ul class="field-box">
                      <li style="color:#aaa">
                        <strong>_sign</strong>  (验证签名)
                      </li>
                      <li style="color:#aaa">参数值：</li>
                      <li><input style="width:260px" type="text" name="_sign" value=""></li>
                    </ul>
                  </div><?php endif; ?>
                <button id="add" type="button" class="uk-button uk-button-block uk-button-default" style="margin-top:10px">
                  <i class="uk-icon-pencil"></i>自定义参数
                </button>
              </div>
            <?php else: ?>
              <div class="uk-panel" style="margin-top:20px">
                <?php if(($info["api_token"]) == "1"): ?><div class="uk-panel-box SignBox">
                    <ul class="field-box">
                      <li style="color:#aaa">
                        <strong>_uid</strong>  (用户ID)
                      </li>
                      <li style="color:#aaa">参数值：</li>
                      <li><input style="width:100px" type="text" name="_uid" value=""></li>
                    </ul>
                    <ul class="field-box">
                      <li style="color:#aaa">
                        <strong>_sign</strong>  (验证签名)
                      </li>
                      <li style="color:#aaa">参数值：</li>
                      <li><input style="width:260px" type="text" name="_sign" value=""></li>
                    </ul>
                  </div><?php endif; ?>
              </div><?php endif; ?>
          
            <button type="submit" id="post" class="uk-button uk-button-primary uk-radius uk-button-large" style="margin-top:20px">调试该接口
            </button>
            <textarea id="json" placeholder="接口返回信息"></textarea>
            <?php if(!empty($info["api_remark"])): ?><div class="uk-panel" style="margin-top:20px">
                <div class=" uk-panel-box">
                  <h3 class="uk-panel-title">接口详细说明</h3>
                  <div style="color:#888; font-size:12px; line-height:15px">
                    
                    <textarea readonly name="" rows="2" cols="20" style="width: 95%; min-height:200px;"><?php echo ($info["api_remark"]); ?></textarea>
                  </div>
                </div>
              </div><?php endif; ?>
          </form><?php endif; ?>
    </td>
  </tr>
  </table>
  
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





  <script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
  <script type="text/javascript">
	$("#post").click(function(){
		
		var data = '&';
		var len = $(".diy-box").length;
		if(len>0){
			$(".diy-box").each(function(index){
				data+=$(this).find('.diy-field').val()+'='+$(this).find('.diy-value').val();
				if(index !== len - 1){
					data+='&';
				}
			});
		}else{
			data = '';	
		}
	 
		
		$.ajax({
			async:true,
			type: "<?php echo ($info["api_type"]); ?>",
			data: $('form.uk-form').find('input,select,textarea').serialize()+data,
			url: $('#url').val(),
			dataType:'json',
			beforeSend:function(){
				OpenLoad();
				$('#json').val('');
			},
			success:function(ret){
				
				if(!ret.info) ret.info = '接口通信成功'
				if(ret.status == 0){
					Alert(ret.info,'error')
				}else if(!ret.status){
					Alert(ret.info,'info')
				}else if(ret.info && ret.status == 1){
					Alert(ret.info,'success')
				}
				
				var Json = JSON.stringify(ret, null, 4);
				$('#json').val(Json);
			},
			complete:function(){
				$('#json').css('height','500px')
				CloseLoad();
			},
			error:function(ret){
				$('#json').val(JSON.stringify(ret, null, 20));
				if(!ret.statusText) ret.statusText = '系统致命错误'
				Alert('系统发生致命错误或返回了非JSON格式数据，错误信息：'+ret.statusText,'error');
			}
		});
		return false;
	}); 
	
	$("#add").click(function(){
		var html = '';
		
		html+='<ul class="field-box diy-box">';
		html+='<li style="color:#aaa"><input type="text" class="diy-field" style="width:150px"/> (自定义字段名)</li>';
		html+='<li style="color:#aaa">参数值：</li>';
		html+='<li><input type="text" class="diy-value" style="width:200px"/></li>';
		html+='</ul>';
		
		$('#field-box').append(html);
	}); 
	
	
	$(".uk-close").click(function(){
		$(".uk-alert").hide();
	});
	
	function showAlert(){
		$(".uk-alert").show();	
	}
	
	$(".uk-alert button").click(function(){
		var uid = $("input[name='Tuid']").val();
		var api = $("input[name='Tapi']").val();
		var token = $("input[name='Ttoken']").val();
		if(uid == '' || uid == 0){
			alert('请输入正确的UID!');
		}else if(api == ''){
			alert('请输入正确的API接口!');
		}else if(token == ''){
			alert('请输入正确的Token!');
		}
		
		$.post("<?php echo U('index/getSign');?>",{'uid':uid,'api':api,'token':token},function(data){
			$(".TestSignBox").html(data.sign);
			$("input[name='_uid']").val(uid);
			$("input[name='_sign']").val(data.sign);
		});
		
		
		
		
	});
	
	$(document).ready(function(){      
			var clipboard = new Clipboard('#copy_btn');    
			clipboard.on('success', function(e) {    
				alert("微信号复制成功",1500);
				e.clearSelection();    
				console.log(e.clearSelection);    
			});    
		});    
  </script> 
    
</body>
</html>