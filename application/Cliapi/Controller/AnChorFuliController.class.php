<?php
/**
 * 主播福利  扶持
 */
namespace Cliapi\Controller;
use Think\Controller;

class AnChorFuliController extends ApiController {
    //直播首页
    function zhiBoIndex(){
        //1关注   2热门    3最新    4PK
        $prefix = C('DB_PREFIX');
        $type = I('type');
        $uid = 27538;
        if($type == 1){
            //根据我关注的人是不是主播
            $info = M('user_relation u')
                ->join("{$prefix}zhibo z on u.relation_uid = z.uid")
                ->join("{$prefix}anchor_ruzhu r on z.anchor_id = r.id")
                ->field('z.id,z.room_no,z.surface,z.person_num num')
                ->where(array('u.uid'=>$uid,'z.status=1'))
                ->order('num desc')
                ->select();
        }
        if($type == 2 || $type == 3){
            if($type == 2){
                $order = 'person_num desc';
            }
            if($type == 3){
                $order = 'begin_time desc';
            }
            $info = M('zhibo')
                ->field('id,room_no,surface,person_num,begin_time')
                ->where(array('status=1'))
                ->order($order)
                ->select();
        }
        echo "<pre>";
        print_r($info);
        echo "</pre>";
//        if($type == 4){
//
//        }
    }



    //点击结束直播
    function endZhibo(){
        $room_no = '123456';
        //获取在线人数
        $personNum = M('Zhibo')->field('person_num,anchor_id')->where(array('room_no'=>$room_no))->find();

        //根据在线人数分成   未完成:兜底礼物额
        $info = D('Anchor')->personNumFenCheng($personNum['person_num'],$personNum['anchor_id'],$room_no);

    }

    //主播考核条件  每月月底调用此方法
    function anchorKaohe(){
        $anchor_id = I('anchor_id');
        $result = D('Anchor')->anchorKaohe($anchor_id);
    }

    //
}