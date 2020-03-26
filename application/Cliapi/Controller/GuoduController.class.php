<?php

/**
 * 解决跨域问题
 */
namespace Cliapi\Controller;

use Think\Controller;
class GuoduController extends ApiController{

    public function  __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods:POST,GET");
        header("Access-Control-Allow-Headers:x-requested-with,content-type");
        header("Content-type:text/json;charset=utf-8");
    }
}
