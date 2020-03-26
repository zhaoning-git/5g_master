<?php
/**
 * Created by PhpStorm.
 * User: baozhi
 * Date: 2017/11/14
 * Time: 9:41
 */
namespace Common\Model;
use Think\Model;

class GoodsLogModel extends Model
{
    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 喜欢or不喜欢
     * @param int $goods_id
     * @param $ids
     * @param $uid
     * @param $type
     * @return bool
     */
    public function setLike($goods_id = 0,$ids,$uid,$type)
    {
        if (!$goods_id || !$ids || !$uid){
            $this->error = '参数有误!';
            return false;
        }
        $goods = M('goods')->where(array('goodsd_id'=>$goods_id))->find();
        if (!$goods){
            $this->error = '参数有误!';
            return false;
        }
        if ($goods['uid'] == $uid){
            $this->error = '不能操作自己的商品!';
            return false;
        }
        if(stripos($ids,',') !== false){
            $ids = explode(',', trim($ids,','));
        }else{
            $ids = (array)$ids;
        }
        // 过滤已操作过(喜欢or不喜欢)的图片
        $map['goods_id'] = $goods_id;
        $map['uid'] = $uid;
        $filter_ids = array();
        foreach ($ids as $id){
            $map['pid'] = $id;
            $have = $this->where($map)->find();
            // === 有操作过的不能重复操作
            if ($have){
                $this->error = '请勿重复操作';
                return false;
            }
            if (!$have){
                $filter_ids[] = $id;
            }
        }
        $data = array();
        $now_time = time();
        foreach ($filter_ids as $id){
            $data[] = array(
                'goods_id'  =>  $goods_id,
                'pid'       =>  $id,
                'uid'       =>  $uid,
                'type'      =>  $type,
                'add_time'  =>  $now_time
            );
        }
        if ($data){
            $res = $this->addAll($data);
            if ($res === false){
                $this->error = '失败';
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    /**
     * 判断是否喜欢
     * @param $goods_id
     * @param $pid
     * @param $uid
     * @return bool|int
     */
    public function checkLike($goods_id,$pid,$uid)
    {
        if (!$goods_id || !$pid || !$uid){
            $this->error = '参数错误';
            return false;
        }
        $map['goods_id'] = $goods_id;
        $map['uid'] = $uid;
        $map['pid'] = $pid;
        $like = $this->where($map)->find();
        if ($like && $like['type'] == 1){
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 获取喜欢和不喜欢的数量
     * @param $goods_id
     * @param $pid
     * @return int
     */
    public function getLikeNum($goods_id,$pid,$type)
    {
        if (!$goods_id || !$pid){
            return 0;
        }
        $map['goods_id'] = $goods_id;
        $map['pid'] = $pid;
        if ($type){
            $map['type'] = 1;
        } else {
            $map['type'] = 0;
        }
        return $this->where($map)->count();
    }
}