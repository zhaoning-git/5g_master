<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.thinkphp.cn>
// +----------------------------------------------------------------------

/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */
return array(

    // 预先加载的标签库
    //'TAGLIB_PRE_LOAD'     =>    'OT\\TagLib\\Article,OT\\TagLib\\Think',
        
    /* 主题设置 */
    'DEFAULT_THEME' =>  'default',  // 默认模板主题名称

    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX' => 'ocenter_', // 缓存前缀
    'DATA_CACHE_TYPE'   => 'File', // 数据缓存类型


    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
	'__STATIC__' => __ROOT__ . '/Public/static',
        '__IMG__' => __ROOT__ . '/application/Dev'   . '/Static/img',
        '__CSS__' => __ROOT__ . '/application/Dev'   . '/Static/css',
        '__JS__' => __ROOT__ . '/application/Dev'  . '/Static/js',
        '__UIKIT__' => __ROOT__ . '/application/Dev/Static/uikit',
        '__CLOCK__' => __ROOT__ . '/application/Dev/Static/clock',
    ),
	
    'TMPL_ACTION_ERROR' => MODULE_PATH . 'View/default/Public/error.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => MODULE_PATH . 'View/default/Public/success.html', // 默认成功跳转对应的模板文件

);
