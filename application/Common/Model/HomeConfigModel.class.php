<?php
    namespace Common\Model;
    use Common\Model\CommonModel;
    class HomeConfigModel extends CommonModel{        
        protected function _before_write(&$data) {
            parent::_before_write($data);
        }
    }

?>