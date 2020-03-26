<?php
if(file_exists("data/conf/db.php")){
	$db=include "data/conf/db.php";
}else{
	$db=array();
}
if(file_exists("data/conf/config.php")){
	$runtime_config=include "data/conf/config.php";
}else{
    $runtime_config=array();
}

if (file_exists("data/conf/route.php")) {
    $routes = include 'data/conf/route.php';
} else {
    $routes = array();
}

$configs= array(
        "LOAD_EXT_FILE"=>"extend",
        'UPLOADPATH' => 'data/upload/',
        //'SHOW_ERROR_MSG'        =>  true,    // 显示错误信息
        'SHOW_PAGE_TRACE'		=> false,
        'TMPL_STRIP_SPACE'		=> true,// 是否去除模板文件里面的html空格与换行
        'THIRD_UDER_ACCESS'		=> false, //第三方用户是否有全部权限，没有则需绑定本地账号
        /* 标签库 */
        'TAGLIB_BUILD_IN' => THINKCMF_CORE_TAGLIBS,
        'MODULE_ALLOW_LIST'  => array('Admin','Portal','Asset','Api','User','Wx','Comment','Qiushi','Tpl','Topic','Install','Bug','Better','Pay','Cas','Home','Appapi'), //允许访问模块列表
        'TMPL_DETECT_THEME'     => false,       // 自动侦测模板主题
        'TMPL_TEMPLATE_SUFFIX'  => '.html',     // 默认模板文件后缀
        'DEFAULT_MODULE'        =>  'Portal',  // 默认模块
        'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
        'DEFAULT_ACTION'        =>  'index', // 默认操作名称
        'DEFAULT_M_LAYER'       =>  'Model', // 默认的模型层名称
        'DEFAULT_C_LAYER'       =>  'Controller', // 默认的控制器层名称
        
        'DEFAULT_FILTER'        =>  'htmlspecialchars', // 默认参数过滤方法 用于I函数...htmlspecialchars
        
        'LANG_SWITCH_ON'        =>  true,   // 开启语言包功能
        'DEFAULT_LANG'          =>  'zh-cn', // 默认语言
        'LANG_LIST'				=>  'zh-cn,en-us,zh-tw',
        'LANG_AUTO_DETECT'		=>  false,
        
        'VAR_MODULE'            =>  'g',     // 默认模块获取变量
        'VAR_CONTROLLER'        =>  'm',    // 默认控制器获取变量
        'VAR_ACTION'            =>  'a',    // 默认操作获取变量
        
        'APP_USE_NAMESPACE'     =>   true, // 关闭应用的命名空间定义
        'APP_AUTOLOAD_LAYER'    =>  'Controller,Model', // 模块自动加载的类库后缀
        
        'SP_TMPL_PATH'     		=> 'themes/',       // 前台模板文件根目录
        'SP_DEFAULT_THEME'		=> 'simplebootx',       // 前台模板文件
        'SP_TMPL_ACTION_ERROR' 	=> 'error', // 默认错误跳转对应的模板文件,注：相对于前台模板路径
        'SP_TMPL_ACTION_SUCCESS' 	=> 'success', // 默认成功跳转对应的模板文件,注：相对于前台模板路径
        'SP_ADMIN_STYLE'		=> 'flat',
        'SP_ADMIN_TMPL_PATH'    => 'admin/themes/',       // 各个项目后台模板文件根目录
        'SP_ADMIN_DEFAULT_THEME'=> 'simplebootx',       // 各个项目后台模板文件
        'SP_ADMIN_TMPL_ACTION_ERROR' 	=> 'Admin/error.html', // 默认错误跳转对应的模板文件,注：相对于后台模板路径
        'SP_ADMIN_TMPL_ACTION_SUCCESS' 	=> 'Admin/success.html', // 默认成功跳转对应的模板文件,注：相对于后台模板路径
        'TMPL_EXCEPTION_FILE'   => SITE_PATH.'public/exception.html',
        
        'AUTOLOAD_NAMESPACE' => array('plugins' => './plugins/'), //扩展模块列表
        
        'ERROR_PAGE'            =>'',//不要设置，否则会让404变302
        
        'VAR_SESSION_ID'        => 'session_id',
        
        "UCENTER_ENABLED"		=>0, //UCenter 开启1, 关闭0
        "COMMENT_NEED_CHECK"	=>0, //评论是否需审核 审核1，不审核0
        "COMMENT_TIME_INTERVAL"	=>60, //评论时间间隔 单位s
        
        /* URL设置 */
        'URL_CASE_INSENSITIVE'  => true,   // 默认false 表示URL区分大小写 true则表示不区分大小写
        'URL_MODEL'             => 0,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
        // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式，提供最好的用户体验和SEO支持
        'URL_PATHINFO_DEPR'     => '/',	// PATHINFO模式下，各参数之间的分割符号
        'URL_HTML_SUFFIX'       => '',  // URL伪静态后缀设置
        
        'VAR_PAGE'				=>"p",
        
        'URL_ROUTER_ON'			=> true,
        'URL_ROUTE_RULES'       => $routes,
        		
        /*性能优化*/
        'OUTPUT_ENCODE'			=>true,// 页面压缩输出
        
        'HTML_CACHE_ON'         =>    false, // 开启静态缓存
        'HTML_CACHE_TIME'       =>    60,   // 全局静态缓存有效期（秒）
        'HTML_FILE_SUFFIX'      =>    '.html', // 设置静态缓存文件后缀

				
        'TMPL_PARSE_STRING'=>array(
        	'/Public/upload'=>'/data/upload',
        	'__UPLOAD__' => __ROOT__.'/data/upload/',
        	'__STATICS__' => __ROOT__.'/statics/',
        ),
    
    /* 图片上传相关配置 */
    'PICTURE_UPLOAD' => array(
        'mimes' => '', //允许上传的文件MiMe类型
        'maxSize' => 2 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,mp4,mov,flv,ogv,webm,ts,3gp,avi,wmv', //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './data/upload/Picture/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => true, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), 
    
    'UsersCoinRecordActionName' => array(
        'user_signin_reward' => '签到奖励',
        'user_signin_reward_lx'=> '连续签到奖励',
        'recharge_gold_coin' => '充值金币',
        'roomcharge' => '房间充值',
        'loginbonus' => '登录奖励',
        'reg_reward' => '注册奖励',
        'invite_friend' => '邀请好友',
        'jingcai_deposit' => '发起竞猜押金',
        'jing_cai_reward' => '竞猜有奖',
        'jingcai_canyu' => '参与竞猜扣银币',
        'jing_cai_bets' => '发起竞猜扣除银币',
        'fq_jing_cai_ying' => '发起竞猜赢银币',
        'fq_jing_cai_shu' => '发起竞猜输银币',
        'cy_jing_cai_ying' => '参与竞猜赢银币',
        'cy_jing_cai_shu' => '参与竞猜输银币',
        'fq_getcy_frozen_betting' => '发起竞猜者获得参与者下注金额',
        'fq_fanhui_frozen_bets' => '返回发起者剩余冻结底注',
        '' => '',
        '' => '',
        
        
        'bind_wx' => '绑定微信',
        'bind_qq' => '绑定QQ',
        'bind_email' => '绑定邮箱',
        'bind_weibo' => '绑定微博',
        
        'realname_verify' => '实名认证',
        'bind_bank_card' => '绑定银行卡',
        'facescan_verify' => '刷脸认证',
        'share_friends' => '分享朋友圈',
        'post_show' => '发主题帖奖励',
        'reply_show' => '发回复贴奖励',
        'del_show' => '被删贴扣除',
        'online_time' => '在线时长',
        '' => '兑换商城物品',
        'live_play' => '直播',
        'look_live_play' => '观看直播',
        
        'luck_lottery' => '幸运轮盘',
        'gold_box' => '参加黄金宝箱',
        'cj_gold_box' => '参加黄金宝箱',
        'red_pack' => '神秘红包',
        
        '' => '',
        '' => '',
        '' => '',
        '' => '',
        '' => '',
        '' => '',
        '' => '',
        '' => '',
    ),
    
    //参加黄金宝箱消耗银币数量
    'CJ_GoldBox_Coin' => 100,
    //发起竞猜押金最低数量
    'JINGCAI_DEPOSIT' => 10000,
    
);

return  array_merge($configs,$db,$runtime_config);
