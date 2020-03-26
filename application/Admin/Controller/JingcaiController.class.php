<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

//绔炵寽璁剧疆
class JingcaiController extends AdminbaseController {

    public function index() {
        if (IS_POST) {
            $config = I("post.post");
            
            foreach ($config as $k => $v) {
                $config[$k] = html_entity_decode($v);
            }

            if (M("options")->where("option_name='ConfigJingcai'")->save(['option_value' => json_encode($config)]) !== false) {

                $key = 'getConfigJingcai';
                setcaches($key, $config);

                $action = "修改公共设置";
                setAdminLog($action);
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        } else {
            //print_r(getcaches('getConfigJingcai'));exit;
            $config = M("options")->where("option_name='ConfigJingcai'")->getField("option_value");
            $this->assign('config', json_decode($config, true));
            $this->display();
        }
    }

}
