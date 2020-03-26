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
    //'TAGLIB_PRE_LOAD' => 'OT\\TagLib\\Article,OT\\TagLib\\Think', 
    'DEFAULT_FILTER' => 'strip_tags', //全局过滤
    /* 主题设置 */
    'DEFAULT_THEME' => 'default', //默认模板主题名称
    'URL_HTML_SUFFIX' => 'json|html',
    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => 'onethink_user', //session前缀
    'COOKIE_PREFIX' => 'onethink_user_', // Cookie前缀 避免冲突


    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__' => __ROOT__ . '/Public',
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__IMG__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/default/img',
        '__CSS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/default/css',
        '__JS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/default/js',
        '__ICO__' => __ROOT__ . '/Public/icon',
    ),
    'TMPL_ACTION_ERROR' => '../../../Common/View/default/Public/error', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => '../../../Common/View/default/Public/success', // 默认成功跳转对应的模板文件
    'P' => 8,
);
