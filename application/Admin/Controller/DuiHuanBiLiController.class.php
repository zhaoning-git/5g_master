<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

/**
 * 金钱兑换比例
 */
class DuiHuanBiLiController extends AdminbaseController{

    public function index(){
        if (IS_POST) {
            $config = I("post.");

            foreach ($config as $k => $v) {
                $config[$k] = html_entity_decode($v);
            }

            if (M("options")->where("option_name='SetGoldCoinBiLi'")->save(['option_value' => json_encode($config)]) !== false) {

                $key = 'SetGoldCoinBiLi';
                setcaches($key, $config);

                $action = "修改公共设置";
                setAdminLog($action);
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        } else {
            $config = M("options")->where("option_name='SetGoldCoinBiLi'")->getField("option_value");
            $this->assign('config', json_decode($config, true));
            $this->display();
        }
    }


}