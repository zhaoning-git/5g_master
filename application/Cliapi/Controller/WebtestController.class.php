<?php

namespace Cliapi\Controller;
class WebtestController extends ApiController {

    function _initialize() {
        parent::_initialize();
    }
    
    //查看服务器红包数量
    function Redpack(){
        if(D('LotteryRedpack')->WorkerMan(183)){
            $this->ajaxRet(1, '成功');
        }else{
            $this->ajaxRet(0, D('LotteryRedpack')->getError());
        }
    }

}
