<?php

namespace Common\Model;

use Think\Model;

class GoodsModel extends Model {
    
    function _initialize() {
        parent::_initialize();
    }
    
    //添加商品
    function addGoods($data=array()){
        $data['uid'] = intval($data['uid']);
        if(!$data['uid']){
            $this->error = '参数有误!';
            return false;
        }
        
        $store = D('Store')->getStore($data['uid']);
        if(!$store){
            $this->error = D('Store')->getError();
            return false;
        }elseif($store['status'] != 1){
            if($store['status'] == 0){
                $this->error = '店铺等待审核!';
                return false;
            }elseif($store['status'] == -1){
                $this->error = '店铺已关闭!';
                return false;
            }if($store['status'] == 2){
                $this->error = '店铺审核未通过!';
                return false;
            }
        }
        
        
        if(empty($data['describe']) && empty($data['imgs'])){
            $this->error = '请介绍一下您的商品!';
            return false;
        }
        
        $data['addtime'] = time();
        $data['store_id'] = $store['id'];
        
        $goods_id = $this->add($data);
        
        if($goods_id){
            //同步到动态
            // 修改为，总是插入到show表,为了共用点赞功能 2017/11/13
            if($data['is_show'] == 1){
                $Show['uid'] = $data['uid'];
                $Show['title'] = $data['title'];
                $Show['content'] = $data['describe'];
                $Show['img'] = $data['imgs'];
                $Show['goods_id'] = $goods_id;
                $Show['is_open'] = 1;
                D('Show')->Insert($Show);
            }

            return true;
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }
            
    
    function getOne($id, $field=true, $new=false){
        $id = intval($id);
        if(!$id){
            $this->error = '商品ID参数错误!';
            return false;
        }
        $key = 'GoodsInfo_'.$id;
        
        if($new === true){
            S($key,null);
        }
        
        $goodsInfo = S($key);
        
        if(empty($goodsInfo)){
            $goodsInfo = M('Goods')->where(array('id'=>$id))->find();
            if(empty($goodsInfo)){
                $this->error = '商品不存在!';
                return false;
            }else{
                S($key,$goodsInfo);
            }
        }
        
        if($field === true){
            return $goodsInfo;
        }else{
            return $goodsInfo[$field];
        }
    }

    //删除(支持批量)
    public function Del($goods_id = 0,$ids=array()){

        if(stripos($ids,',') !== false){
            $ids = explode(',', $ids);
        }else{
            $ids = (array)$ids;
        }

        if(empty($ids) || !$goods_id){
            $this->error = '参数错误!';
            return false;
        }

        sort($ids);

        $goods_imgs = $this->where(array('goods_id'=>$goods_id))->getField('imgs');

        if ($goods_imgs){
            $goods_imgs = explode(',',trim($goods_imgs,','));
            sort($goods_imgs);
        }
        if ($ids == $goods_imgs){
            // 删除商品
            if($this->where(array('goods_id'=>$goods_id))->delete() !== false){
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        } else {
            // 删除单张图片
            $goods_imgs_new = array_diff($goods_imgs,$ids);
            $goods_imgs_new = implode(',',$goods_imgs_new).',';
            if($this->where(array('goods_id'=>$goods_id))->save(array('imgs'=>$goods_imgs_new)) !== false){
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }


    }
}

