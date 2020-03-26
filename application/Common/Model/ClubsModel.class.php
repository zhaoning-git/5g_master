<?php
//俱乐部
namespace Common\Model;

use Think\Exception;
use Think\Model;

class ClubsModel extends Model{
    //创建俱乐部
    function Insert($data)
    {
        if (empty($data['name'])) {
            $this->error = '俱乐部昵称不能为空!';
            return false;
        }

        if(empty($data['type'])){
            $this->error = '请选择俱乐部的类别！';
            return false;
        }

        if(empty($data['headimg'])){
            $this->error = '请为您的俱乐部选择头像！';
            return false;
        }

        if(empty($data['longitude']) || empty($data['latitude'])){
            $this->error = '请获取经度和纬度！';
            return false;
        }

        $typeData = M('club_type')->where(array('id'=>$data['type'],'type'=>4))->find();
        if(empty($typeData)){
            $this->error = '请选择正确的俱乐部类别！';
            return false;
        }

        //可创建俱乐部的个数
        $add_res = 100;//D('UserLevel')->getUserPriv($data['uid'], 2);
        $club_length = count(M('clubs')->where(array('uid'=>$data['uid']))->select());
        if($club_length >= $add_res){
            $this->error = '您创建俱乐部的个数已达上限';
            return false;
        }

        $info = M('clubs')->where(array('name' => $data['name']))->find();
        if (!empty($info)) {
            $this->error = '俱乐部名称已存在！';
            return false;
        } else {
            $data['create_time'] = time();
            $data['update_time'] = time();

            //处理上传头像
            if(!is_numeric($data['headimg'])){
                $upload = new \Think\Upload();// 实例化上传类
                $info   =   $upload->uploadOne($_FILES['headimg']);
                $info['uid'] = $data['uid'];
                $data['headimg'] = M('picture')->add($info);
            }


            $add = M('clubs')->add($data);
            $club_id = M()->getLastInsID();

            //处理成员问题
            if(!empty($data['mem'])){
                $data['mem'] = $data['uid'] . ',' . $data['mem'];
                $club_mem = explode(',', $data['mem']);
                for ($i = 0; $i < count($club_mem); $i++) {
                    $memData[] = ['cid' => $club_id, 'mid' => $club_mem[$i],'status'=>1,'add_time'=>time()];
                }
                $club_mem_res = M('club_mem')->addAll($memData);
            }else{
                $memData = ['cid'=>$club_id,'mid'=>$data['uid'],'status'=>1,'add_time'=>time()];
                $club_mem_res = M('club_mem')->add($memData);
            }

            if(!empty($add) && !empty($club_mem_res)){
                $data['cid'] = $club_id;
                $data['img_path'] = getImgVideo($data['headimg']);
                return $data;
            }

        }
    }

    //加入俱乐部
    function InJect($data){
        $club_id = $data['club_id'];
        $mem_id = $data['mem_id'];
        if(empty($club_id) || empty($mem_id)){
            $this->error = '请选择要加入的俱乐部或成员';
            return false;
        }

        $club=M("clubs")->where(array('id'=>$club_id))->find();

        if(empty($club)){
            $this->error='该俱乐部不存在';
            return false;
        }
        if($club['is_del']=='1'){
            $this->error='该俱乐部已被删除或禁用，不能添加成员';
            return false;
        }

        $user_mem = M('users')->where(array('id'=>$mem_id))->find();
        if(empty($user_mem)){
            $this->error = '您选择的用户已被删除或不存在，请重新选择';
            return false;
        }
        $isexist = M("club_mem")->where(array('mid'=>$mem_id,'cid'=>$club_id))->find();
        if($isexist){
            switch($isexist['status']){
                case "0":
                    $info = '您已申请，待审核';
                    break;
                case "1":
                    $info = '该用户已在俱乐部中';
                    break;
                case "2":
                    $info ='该用户已申请进入俱乐部';
                    break;
                case "3":
                    $info ='该用户已被拒绝进入';
                    break;
                default:
                    break;
            }
            $this->error = $info;
            return false;
        }

        $prefix= C("DB_PREFIX");
        $list = M('club_mem m')
            ->join("{$prefix}clubs c on m.cid = c.id")
            ->where(array('m.cid'=>$club_id,'m.status'=>1))
            ->select();

        foreach ($list as $k=>$v){
            $level = $v['level'];
        }

        $addData = [
            'cid'=>$club_id,
            'mid'=>$mem_id,
            'status'=>0,
            'add_time'=>time()
        ];

        $length = count($list);

        //人数上限
        $max_person = M('club_upgrade')->where(array('level'=>$level))->find();
        if($length >= $max_person['max_person']){
            $this->error = '俱乐部人数已达上限';
            return false;
        }
        $return_id = M('club_mem')->where(array('id'=>$club_id))->add($addData);
        if($return_id){
            return $addData;
        }
    }

    //加入俱乐部  筛选数据
    function joinTypeClub($type){
        if($type == 'list' || $type == 'index'){       //推荐 || 排名
            $info = $this->clubOrder();
        }

        if($type == 'hobby'){                   //兴趣
            $info = M('clubs')
                ->field('id,name,headimg,level,create_time,is_ball')
                ->where(array('type=10021','is_del=0'))
                ->select();
            foreach ($info as $k=>&$v){
                $v['headimg'] = getImgVideo($v['headimg']);
            }
        }
        foreach ($info as $k=>&$v){
            //获取人数
            $length = $this->getClubPerson($v['id']);
            $v['person_count'] = $length;
            //容纳人数
            $max_person = M('club_upgrade')->field('max_person')->where(array('level'=>$v['level']))->find();
            $v['max_person'] = $max_person['max_person'];
        }
        return $info;
    }

    //管理中心
    function manageCenter($type,$uid){
        $prefix= C("DB_PREFIX");
        if(empty($type)){
            $this->error = '请选择参数';
            return false;
        }

        if($type == 'join'){
            $info = M('club_mem m')
                ->join("{$prefix}clubs c on m.cid = c.id")
                ->field('c.id,c.name,c.headimg,c.is_ball,c.level,c.create_time')
                ->where(array('m.mid'=>$uid,'c.is_del=0'))
                ->select();
            foreach ($info as $k=>&$v){
                $v['headimg'] = getImgVideo($v['headimg']);
            }
        }

        if($type == 'list'){
            $info = $this->clubOrder();
        }

        if($type == 'my'){
            $info = M('clubs')
                ->field('id,name,level,headimg,is_ball,create_time')
                ->where(array('uid'=>$uid,'is_del=0'))->select();
            foreach ($info as $k=>&$v){
                $v['headimg'] = getImgVideo($v['headimg']);
            }
        }
        foreach ($info as $k=>&$v){
            //获取人数
            $length = $this->getClubPerson($v['id']);
            $v['person_count'] = $length;
            //容纳人数
            $max_person = M('club_upgrade')->field('max_person')->where(array('level'=>$v['level']))->find();
            $v['max_person'] = $max_person['max_person'];
        }
        return $info;
    }

    //获取加入俱乐部的人数
    function getClubPerson($cid){
        $length = count(M('club_mem')->where(array('cid'=>$cid,'status=1'))->select());
        return $length;
    }

    //搜索
    function Search($name){
        if(empty($name)){
            $this->error = '搜索条件不能为空';
            return false;
        }
        $info = M('clubs')
            ->field('id,name,headimg,level')
            ->where(array('name'=>['like',"%$name%"],'is_del=0'))
            ->order("level desc")
            ->select();

        foreach ($info as $k=>&$v){
            $v['headimg'] = getImgVideo($v['headimg']);
            $length = count(M('club_mem')->where(array('cid'=>$v['id'],'status=1'))->select());
            $v['person'] = $length;
        }

        if(empty($info)){
            $this->error = '无搜索结果';
            return false;
        }
        else{
            return $info;
        }
    }

    //获取俱乐部热聊分类下的数据
    function hotInfo($type){
        if(empty($type)){
            $this->error = '请选择要查看的类型';
            return false;
        }

        $arr = [1,2,3,4];
        if(!in_array($type,$arr)){
            $this->error = '参数错误';
            return false;
        }

        //1推荐   2排名    3位置     4兴趣
        if($type == 1 || $type == 2){     //推荐
            $info = M('group_chat')
                ->field('id,name,image')
                ->where(array('status'=>1))
                ->order('rand()')
                ->limit(4)
                ->select();
        }

        if($type == 4){     //兴趣
            $info = M('GroupChat')
                ->field('id,name,image')
                ->where(array('tags'=>'兴趣','status'=>1))
                ->select();
        }

        foreach ($info as $k=>&$v){
            $v['image'] = AddHttp($v['image']);
            $pInfo = D('GroupChat')->getInfo($v['id']);
            $length = count($pInfo['userlist']);
            $v['person_count'] = $length;
        }

        if($type == 3){         //位置
            $jingdu = I('jingdu');
            $weidu = I('weidu');
            $info = $this->hotQun($jingdu,$weidu);
            unset($info['jingdu']);
            unset($info['weidu']);
        }

        return $info;

    }

    /**
     * 俱乐部排名
     */
    function clubOrder(){
        $clubInfo = M('clubs')
            ->field('id,name,headimg,create_time,is_ball,level')
            ->where(array('is_del=0'))
            ->order('level desc')
            ->select();

       foreach ($clubInfo as $k=>&$v){
           $v['headimg'] = getImgVideo($v['headimg']);
           $v['person_count'] = count(M('club_mem')->where(array('cid'=>$v['id'],'status=1'))->select());
       }

        $cmf_arr = array_column($clubInfo, 'person_count');
        $cmf_time = array_column($clubInfo, 'create_time');
        array_multisort($cmf_arr, SORE_DESC, $cmf_time, SORT_DESC, $clubInfo);
        return $clubInfo;
    }

    //俱乐部头衔
    function Title($id,$club_id){
        if(empty($club_id)){
            $this->error = '参数错误';
            return false;
        }
        $is_one = M('clubs')->where(array('id'=>$club_id))->find();
        if($is_one['uid']==$id){        //如果是创建者，等级为1，即董事长
            $type = ['level'=>1];
            return $type;
        }else{
            $titleInfo = M('send_coin')->field('send_gold,send_silver')->where(array('don_id'=>$id,'club_id'=>$club_id))->find();
            if(empty($titleInfo)){
                return $type = ['level'=>8];        //如果查询不到，代表还没有捐赠过，默认等级为8
            }
            $send_gold = $titleInfo['send_gold'];
            $send_silver = $titleInfo['send_silver'];
            $type = $this->judgeTitle($send_gold,$send_silver);
            return $type;
        }

    }

    //俱乐部升级
    function Rank($id){
        if(empty($id)){
            $this->error = '参数错误';
            return false;
        }
        $clubInfo = M('clubs')->where(array('id'=>$id))->find();
        $level = $clubInfo['level'];
        $gold_count = $clubInfo['gold_coin'];
        $silver_count = $clubInfo['silver_coin'];

        $update_level = $this->judegRank($gold_count,$silver_count,$level);
        if($update_level == 0){
            $this->error = '可升级的金币/银币不足！';
            return false;
        }else{
            $res = M('clubs')->where(array('id'=>$id))->save(array('level'=>$update_level));
            if($res){
                return true;
            }
        }
    }

    //捐赠金币、银币
    function donCoin($data){
        if(empty($data['club_id'])){
            $this->error = '请选择要捐赠的俱乐部';
            return false;
        }
        if(empty($data['type'])){
            $this->error = '请选择捐赠的类型（金币/银币）';
            return false;
        }

        if(empty($data['coinSum'])){
            $this->error = '请选择捐赠的数量';
            return false;
        }

        $userInfo = M('users')->where(array('id'=>$data['don_id']))->find();    //根据捐赠者id
        $clubInfo = M('clubs')->where(array('id'=>$data['club_id']))->find();
        $sendInfo = M('send_coin')->where(array('don_id'=>$data['don_id'],'club_id'=>$data['club_id']))->find();

        //type 1是金币，2是银币
        if($data['type']==1){
            if($userInfo['gold_coin'] < $data['coinSum']){
                $this->error = '您的金币不足，请充值之后再捐赠';
                return false;
            }

            $user_gold_coin = $userInfo['gold_coin'] - $data['coinSum'];
            $club_gold_coin = $clubInfo['gold_coin'] + $data['coinSum'];
            $data['last_send_time'] = time();

            if(empty($sendInfo)){
                $data['send_gold'] = $data['coinSum'];
                $res = M('send_coin')->add($data);
            }
            if($sendInfo['send_gold'] !==0 ){
                $send_gold = $sendInfo['send_gold']+$data['coinSum'];
                $res = M('send_coin')->where(array('id'=>$sendInfo['id']))->save(array('send_gold'=>$send_gold));
            }

            $res = M('users')->where(array('id'=>$data['don_id']))->save(array('gold_coin'=>$user_gold_coin,'send_gold_coin'=>$data['coinSum']));
            $res = M('clubs')->where(array('id'=>$data['club_id']))->save(array('gold_coin'=>$club_gold_coin));
            return true;
        }
        else{
            if($userInfo['silver_coin'] < $data['coinSum']){
                $this->error = '您的银币不足，请充值之后再捐赠';
                return false;
            }
            $user_silver_coin = $userInfo['silver_coin'] - $data['coinSum'];
            $club_silver_coin = $clubInfo['silver_coin'] + $data['coinSum'];
            $data['last_send_time'] = time();

            if(empty($sendInfo)){
                $data['send_silver'] = $data['coinSum'];
                $res = M('send_coin')->add($data);
            }
            if($sendInfo['send_silver'] !==0 ){
                $send_silver = $sendInfo['send_silver']+$data['coinSum'];
                $res = M('send_coin')->where(array('id'=>$sendInfo['id']))->save(array('send_silver'=>$send_silver));
            }

            $res = M('users')->where(array('id'=>$data['don_id']))->save(array('silver_coin'=>$user_silver_coin,'send_silver_coin'=>$data['coinSum']));
            $res = M('clubs')->where(array('id'=>$data['club_id']))->save(array('silver_coin'=>$club_silver_coin));
            return true;
        }
    }

    /**
     * @param $gold
     * @param $silver
     * @return int
     * 根据捐赠的金币、银币数量获得头衔
     */
    function judgeTitle($send_gold,$send_silver){   //传进来的金币，银币
        echo $send_gold;echo "<br>";
        echo $send_silver;
        $info = M('club_title')->order('level desc')->select();
        $data = array();
        $length = count($info);

        if($length>0){
            foreach ($info as $k => $v){
                if($send_gold>=$v['send_gold_coin'] && $send_silver>=$v['send_silver_coin']){
                    $data['level'] = $v['level'];
                    $data['title_name'] = $v['title_name'];
                }

            }
        }

        return $data;
    }

    /**
     * @param $gold_count
     * @param $silver_count
     * @return int
     * 根据金币、银币升级俱乐部
     */
    function judegRank($gold_count,$silver_count,$level){
        $rankInfo = M('club_upgrade')->where(array('level'=>$level+1))->find();

        $need_gold = $rankInfo['need_gold_coin'];
        $need_silver = $rankInfo['need_silver_coin'];

        if($gold_count>=$need_gold && $silver_count>=$need_silver){
            $update_level = $level+1;
        }
        return $update_level;
    }

    //我的关注
    function isAttention($uid)
    {
        $prefix = C('DB_PREFIX');
        $userInfo = M('users')->where(array('id'=>$uid))->find();
        if($userInfo == ''){
            $this->error = '此用户不存在';
            return false;
        }
       $info = M('attention_club a')
           ->join("{$prefix}clubs c on a.club_id = c.id")
           ->field('a.club_id,c.headimg,c.name,c.create_time')
           ->where(array('a.user_id'=>$uid,'c.is_del=0'))
           ->select();
        foreach ($info as $k=>&$v){
            $v['headimg'] = getImgVideo($v['headimg']);
        }

        if(!empty($info)){
            return $info;
        }else{
            $this->error = '您还没有关注任何俱乐部哦';
            return false;
        }
    }

    //点击关注
    function goAttention($data){
        $uid = $data['user_id'];
        $cid = $data['club_id'];
        if(empty($cid)){
            $this->error = '请选择要关注的俱乐部';
            return false;
        }

        $info = M('attention_club')->where(array('user_id'=>$uid,'club_id'=>$cid))->find();
        if(!empty($info)){
            $this->error = '您已关注该俱乐部';
            return false;
        }

        $data['add_time'] = time();
        $res = M('attention_club')->add($data);

        //构造返回信息
        $uInfo = M('users')->field('user_nicename')->where(array('id'=>$uid))->find();
        $cInfo = M('clubs')->field('name')->where(array('id'=>$cid))->find();
        $returnData = [
            'uid'=>$uid,
            'uname'=>$uInfo['user_nicename'],
            'cid'=>$cid,
            'cname'=>$cInfo['name'],
            'time'=>$data['add_time']
        ];
        if($res){
            return $returnData;
        }
    }

    //当前热门
    function nowHot(){
        $info = M('clubs')
            ->field('id,name,headimg,create_time')
            ->where(array('is_hot'=>1,'is_del'=>0))
            ->order('sort desc')
            ->select();
        foreach ($info as $k=>&$v){
            $v['headimg'] = getImgVideo($v['headimg']);
        }
        return $info;
    }

    /**
     * 根据标签类别筛选俱乐部
     */
    function clubFilter($type){
        if(empty($type)){
            $this->error = '请选择条件进行筛选';
            return false;
        }

        $verify = M('club_type')->field('type')->where(array('id'=>$type))->find();
        if($verify['type'] !=4){
            $this->error = '请选择正确的参数';
            return false;
        }
        $info = M('clubs')
            ->field('id,name,headimg,create_time')
            ->where(array('type'=>$type,'is_del=0'))->select();
        foreach ($info as $k=>&$v){
            $v['headimg'] = getImgVideo($v['headimg']);
        }

        if(empty($info)){
            $this->error = '该标签下还没有数据哦';
            return false;
        }else{
            return $info;
        }

    }

    //我附近的
    function myNearby($myJingDu,$myWeiDu){
        if(empty($myJingDu) && empty($myWeiDu)){
            $this->error = '请确定自己的位置';
            return false;
        }
        $all = M('clubs')
            ->field('id,name,create_time,level,headimg,longitude,latitude,is_ball')
            ->select();

        foreach ($all as $k=>&$v){
            $v['headimg'] = getImgVideo($v['headimg']);
            $person = count(M('club_mem')->where(array('cid'=>$v['id']))->select());
            $max_person = M('club_upgrade')->field('max_person')->where(array('level'=>$v['level']))->find();
            $v['distance'] = round($this->getDistance($myWeiDu,$myJingDu,$v['latitude'],$v['longitude']));
            $v['person'] = $person;
            $v['max_person'] = $max_person['max_person'];
        }

        $sortArr = [];
        foreach ($all as $key=>$val){
            $sortArr[] = $val['distance'];
        }
        array_multisort($sortArr,SORT_ASC,$all);
        return $all;
    }

    //附近的热聊
    function hotQun($myJingDu,$myWeiDu){
        if(empty($myJingDu) && empty($myWeiDu)){
            $this->error = '请确定自己的位置';
            return false;
        }
        $all = M('GroupChat')
            ->field('id,name,image,jingdu,weidu')
            ->where('status=1')
            ->select();

        foreach ($all as $k=>&$v){
            $v['image'] = AddHttp($v['image']);
            $pInfo = D('GroupChat')->getInfo($v['id']);
            $length = count($pInfo['userlist']);
            $v['person_count'] = $length;
            $v['distance'] = round($this->getDistance($myWeiDu,$myJingDu,$v['jingdu'],$v['weidu']));
        }

        $sortArr = [];
        foreach ($all as $key=>$val){
            $sortArr[] = $val['distance'];
        }
        array_multisort($sortArr,SORT_ASC,$all);
        return $all;
    }


    /**
     * 两个地点之间的距离
     */
    function getDistance($lat1, $lng1, $lat2, $lng2){
        //$lat 维度
        //$lng 精度
        //将角度转为狐度
        $radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;
        $b=$radLng1-$radLng2;
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137;
        return $s;

    }


}
