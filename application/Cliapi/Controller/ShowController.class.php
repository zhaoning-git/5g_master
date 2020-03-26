<?php

/**
 * Date: 17-08-08
 * Time: 下午5:04
 */

namespace Cliapi\Controller;

use Think\Controller;

class ShowController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }

    //个人帖子列表
    function index() {
        $map['uid'] = I('uid', 0, 'intval') ?: $this->uid;
        $map['status'] = 1;

        $_list = $this->lists('Show', $map, 'is_top desc,top_time desc,addtime DESC');

        $_list = D('Show')->Showlist($_list);

        if (empty($_list)) {
            $_list = array();
            $this->_totalPages = 0;
        }

        $data['showCount'] = M('Show')->where($map)->count();
        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //发帖
    function addShow() {
        $data = I('post.');
        $data['uid'] = $this->uid;

        if (D('Show')->Insert($data) === true) {
            $this->ajaxRet(array('status' => 1, 'info' => '帖子发布成功'));
        } else {
            $this->ajaxRet(array('info' => D('Show')->getError()));
        }
    }

    //转发帖子
    //$data['old_id'] 原帖子ID
    //$data['zcontent'] 内容
    function zfShow() {
        $data = I('post.');
        $data['uid'] = $this->uid;

        if (D('Show')->zInsert($data) === true) {
            $this->ajaxRet(array('status' => 1, 'info' => '个人帖子转发成功'));
        } else {
            $this->ajaxRet(array('info' => D('Show')->getError()));
        }
    }

    //获取关注用户或好友的帖子
    //关注类型 1:好友 2:关注 
    function getShow() {
        $type = I('post.type', '', 'intval');

        if (!empty($type)) {
            $map['type'] = $type;
        }

        $map['uid'] = $this->uid;

        $show = M('UserRelation')->where($map)->getField('relation_uid', true);
        
        if (!empty($show)) {
            $show = array_unique($show);
            $_list = $this->lists('Show', array('uid' => array('in', $show)), 'addtime DESC');
            if (!empty($_list)) {
                $data['_list'] = D('Show')->Showlist($_list);
                $data['_totalPages'] = $this->_totalPages; //总页数
                $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
            }
        }

        $this->ajaxRet(array('info' => '数据不存在'));
    }

    /**
     * 取消收藏
     */
    public function unset_favor() {
        $data = I('post.');
        if (empty($data['id'])) {
            $this->ajaxRet(array('info' => '参数错误'));
        }
        $map['uid'] = $this->uid;
        $map['show_id'] = $data['id'];
        $map['type'] = 'favor';
        $res = M('show_log')->where($map)->delete();

        if ($res === false) {
            $this->ajaxRet(array('info' => '操作失败'));
        } else {
            $this->ajaxRet(array('status' => 1, 'info' => '成功'));
        }
    }

    //点赞or送花
    //$data['id'] Show表ID(个人帖子ID)
    //$data['field'] praise:点赞 read:阅读 flower:送花
    function setShow() {
        $data = I('post.');

        if (D('Show')->setShow($data['id'], $this->uid, $data['type']) === true) {
            switch ($data['type']) {
                case 'praise':
                    $info = '点赞';
                    break;
                case 'cons':
                    $info = '踩';
                    break;
                case 'read':
                    $info = '阅读';
                    break;
                case 'flower':
                    $info = '送花';
                    break;
                case 'favor':
                    $info = '收藏';
                    break;
                case 'dislike':
                    $info = '不感兴趣';
                    break;
                case 'complaint':
                    $info = '投诉';
                    break;
                default :
                    $info = '';
            }

            $this->ajaxRet(array('status' => 1, 'info' => $info . '成功'));
        } else {
            $this->ajaxRet(array('info' => D('Show')->getError()));
        }
    }

    //收藏列表
    function favor() {
        $map['uid'] = $this->uid;
        $map['type'] = 'favor';

        $ids = M('ShowLog')->where($map)->getField('show_id', true);

        if (empty($ids)) {
            $this->ajaxRet(array('info' => '您还没收藏帖子!'));
        }

        $where['id'] = array('in', $ids);

        $_list = $this->lists('Show', $where, 'addtime DESC');

        $_list = D('Show')->Showlist($_list);
        if (empty($_list)) {
            $_list = array();
            $this->_totalPages = 0;
        }

        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
        exit;
        if (!empty($_list)) {
            foreach ($_list as &$value) {
                $value['user_flower'] = User($value['uid'], 'flower');
                if (!empty($value['img'])) {
                    //是否点赞
                    $where['show_id'] = $value['id'];
                    $where['uid'] = $this->uid;
                    $where['type'] = 'praise';
                    $is_praise = M('ShowLog')->where($where)->getField('status');
                    $value['is_praise'] = $is_praise ?: 0;

                    $imgs = explode(',', $value['img']);
                    $value['imgs'] = M('Picture')->where(array('id' => array('in', $imgs)))->field('id,path')->select();
                    // 获取用户名和头像
                    $member = M('ucenter_member')
                            ->alias('um')
                            ->field('um.username,m.nickname')
                            ->join('__MEMBER__ m ON m.uid = um.id')
                            ->where(array('um.id' => $value['uid']))
                            ->find();
                    if ($member) {
                        $value['username'] = $member['username'];
                        $value['nickname'] = $member['nickname'];
                    } else {
                        $value['username'] = '';
                        $value['nickname'] = '';
                    }
                    $avatar = M('avatar')->where(array('uid' => $value['uid']))->getField('headimgurl');
                    if ($avatar) {
                        $value['avatar'] = '/Uploads/Avatar' . $avatar;
                    } else {
                        $value['avatar'] = '';
                    }
                }
            }
        }



        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //被收藏
    function befavor() {
        $uid = I('uid', 0, 'intval') ?: $this->uid;
        $map['status'] = 1;
        $map['type'] = 'favor';
        $map['show_uid'] = $uid;
        $ids = M('ShowLog')->where($map)->getField('show_id', true);

        $_map['status'] = 1;
        $_map['uid'] = $uid;

        $_list = $this->lists('Show', $_map, 'is_top desc,top_time desc,addtime DESC');
        $_list = D('Show')->Showlist($_list);
        if (empty($_list)) {
            $_list = array();
            $this->_totalPages = 0;
        }

        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    /**
     * 设置帖子置顶
     */
    public function set_top() {
        $id = I('id', 0, 'intval');
        $is_top = I('is_top', 0, 'intval'); //0取消置顶 1置顶
        if (!$id)
            $this->ajaxRet(array('info' => '设置失败!'));
        if (!D('Show')->setTop($this->uid, $id, $is_top)) {
            $this->ajaxRet(array('info' => '设置失败!'));
        } else {
            $this->ajaxRet(array('status' => 1, 'info' => '成功'));
        }
    }


    //谁看过我
    public function lookme() {
        //类型:1:主页 2:帖子 3:照片墙
        $map['type'] = I('type', 1, 'intval');

        $map['uid'] = $this->uid;

        $model = M('Lookme')->group('look_uid');
        $list = $this->lists($model, $map, 'num DESC', array(), 'uid,look_uid,count(id) as num');

        if (!empty($list)) {
            foreach ($list as $value) {
                $_data = User($value['look_uid']);
                $_data['hx_username'] = md5($value['look_uid']);
                $_Result[] = $_data;
            }
        }

        $data['_list'] = $_Result;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //设置谁看过我
    public function setLookme() {
        $post = I('post.');
        $data['uid'] = intval($post['uid']);
        $data['look_uid'] = $this->uid;
        $data['type'] = intval($post['type']);
        $data['addtime'] = time();

        if (!$data['uid'] || !$data['type']) {
            $this->ajaxRet(array('info' => '参数有误!'));
        }

        if (M('Lookme')->add($data)) {
            $this->ajaxRet(array('status' => 1, 'info' => '设置成功'));
        } else {
            $this->ajaxRet(array('info' => M('Lookme')->getDbError()));
        }
    }

    //网红值
    public function Celebrity() {
        $uid = I('uid', 0, 'intval') ?: $this->uid;

        $data = D('Show')->Celebrity($uid);

        //超越代码开始
        $userInfo = User($uid);

        $map['id'] = array('neq', $uid);
        $map['region_id'] = $userInfo['region_id'];
        if ($userInfo['is_supplier'] == 1) {
            $map['is_supplier'] = 1;
            $data['typeName'] = '商户';
        } else {
            $data['typeName'] = '用户';
        }

        //地区名称
        $data['regionName'] = $userInfo['cityname'];

        //总数
        $all = M('UcenterMember')->where($map)->count();

        //超越的数量
        $map['celebrity'] = array('LT', $userInfo['celebrity']);
        $surpass = M('UcenterMember')->where($map)->count();

        $data['surpass'] = round($surpass / $all, 2) * 100;
        $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data));
    }

    //谁@过我
    public function Atme() {
        $uid = I('uid', 0, 'intval') ?: $this->uid;
        $map['uid'] = $uid;
        $map['type'] = 'at';
        $ids = M('ShowLog')->where($map)->getField('show_id', true);

        $_list = $this->lists('Show', array('id' => array('in', $ids)), 'is_top desc,top_time desc,addtime DESC');

        $_list = D('Show')->Showlist($_list);

        if (empty($_list)) {
            $_list = array();
            $this->_totalPages = 0;
        }

        $data['count'] = count($ids);
        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //发表回复
    public function addComment(){
        $data = I('post.');
        if(D('Comment')->addComment($data['show_id'], $this->uid, $data['content'], $data['parent_id'], $data['data'], 'Show')){
            $this->ajaxRet(array('status'=>1, 'info'=>'发表成功'));
        }else{
            $this->ajaxRet(array('info'=>D('Comment')->getError()));
        }
    }
    
    //获取评论列表
    public function getCommlist(){
        
        $input = I('post.');

        if(!$input['show_id']){
            $this->ajaxRet(0, 'ping_id is empty');
        }

        $map['show_id'] = $input['show_id'];
        $map['status'] = 1;
        $map['post_table'] = 'Show';
        $page = $input['page'] ? intval($input['page']) : 1;
        $row = 15;

        $count = M('Comment')->where($map)->count();
        if(!$count){
            $this->ajaxRet(0, '暂时没有评论');
        }
        $list = M('Comment')->where($map)->page($page. ',' . $row)->field('*, UNIX_TIMESTAMP(create_time) as time')->order('create_time desc')->select();

        foreach ($list as $k => $v) {
            $list[$k]['time'] = friendlyDate($v['time']);
            $list[$k]['data'] = current(json_decode(getImgVideoPath($v['data']), true));
            $list[$k]['avatar'] = AddHttp($this->sconfig['head_default_pic']);
        }

        if(!$list){
            $this->ajaxRet(0, '没有评论了');
        }

        $this->ajaxRet(array('data'=>$list ,'status'=>1 ,'is_last'=>($row * $page >= $count) ? 1 : 0 ));
    }

     //获取评论列表
     public function zanCommlist(){
        
        $input = I('post.');

        if(!$input['ping_id']){
            $this->ajaxRet(0, 'ping_id is empty');
        }
         
        $map['id'] = $input['ping_id'];
 
        if(session('zan_' . $input['ping_id'])){
            $list = M('Comment')->where($map)->setDec('zan');
            session('zan_' . $input['ping_id'], null);
        }else{
            $list = M('Comment')->where($map)->setInc('zan');
            session('zan_' . $input['ping_id'], true);
        }
 
        $this->ajaxRet(1, '点赞成功');
    }
    
    //成功分享朋友圈
    public function ShareFriend(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        if(!$data['show_id']){
            $this->ajaxRet(0, '分享的内容ID不能为空');
        }
        if(!$data['type']){
            $this->ajaxRet(0, '分享的类型不能为空!');
        }
        
        $map['uid']  = $this->uid;
        $map['show_id'] = $data['show_id'];
        $map['type'] = $data['type'];
        $info = M('ShareFriend')->where($map)->find();
        
        if(empty($info)){
            $data['num'] = 1;
            $data['create_time'] = NOW_TIME;
            $Result = M('ShareFriend')->add($data);
            if($Result){
                Coin($Result, 'share_friends');
            }
        }else{
            $up['num']     = array('exp', "num+1");
            $up['up_time'] = NOW_TIME;
            $Result = M('ShareFriend')->where(array('id'=>$info['id']))->save($up);
        }
        if($Result){
            $this->ajaxRet(1, '成功');
        }
        
    }
    // 我的通知-回复我的
    public function replywod(){
      $parem = I('post.');
      if(empty($parem['type'])){

        $this->ajaxRet(array('status'=>0, 'info'=>'类型为空'));
      }
      if($parem['type']!='reply' && $parem['type']!='aide' && $parem['zrwo']){
        $this->ajaxRet(array('status'=>0, 'info'=>'传入类型格式错误')); 
      }
      $page = !empty($parem['p'])? $parem['p'] : 1;
      $size = !empty($parem['r'])? $parem['r'] : C('p');

      // //查询总条数
      // $user = D('comment')->where(array('show_uid'))->count();
      //回复我的
      if($parem['type']=='reply'){
        $where = array('show_uid'=>$this->uid);
        $totl = D('comment')->where($where)->count();

        //总数转换成多少页
        $pageTo=ceil($totl/$size);

        $from = ($page-1)*$size;

        //组装数据

        $rewo['_list'] = D('comment as comm')
               ->field('comm.avatar,comm.create_time as create_times,comm.content as contents,comm.nickname as nicknames,comm.show_id as shid,shows.title as titles,comm.read_state')
               ->join('cmf_show as shows ON shows.id = comm.show_id')
               ->where($where)
               ->order('create_time desc')
               ->limit($from,$size)
               ->select();
        $rewo['_totalPages'] = $pageTo;
        foreach ($rewo['_list'] as $key => $value) {
           $rewo['_list'][$key]['avatar'] = AddHttp($value['avatar']);
        }
        $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $rewo));
        
      } 
        //赞我的
        if($parem['type'] == 'zrwo'){
    
          $data = $this->aizr('praise',$page,$size);
          foreach ($data['_list'] as $key => $value) {
           $data['_list'][$key]['zan'] = $value['user_nicename'].'赞我的'.$value['titles'];
           $data['_list'][$key]['addtimes'] = date("Y-m-d H:i",$value['addtimes']);
          }
          $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data));
        } 
        //@我的
        if($parem['type'] == 'aide'){

          $data = $this->aizr('at',$page,$size);
          foreach ($data['_list'] as $key => $value) {
           $data['_list'][$key]['rte'] = $value['user_nicename'].'@'.'了我';
           $data['_list'][$key]['addtimes'] = date("Y-m-d H:i",$value['addtimes']);
          }
          $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data));

        }       
  
    }
    public function aizr($type,$page,$size){
        $where = array('logs.show_uid'=>$this->uid,'logs.type'=>$type,'logs.status'=>1);
        $totl = D('show_log logs')->where($where)->count();

        //总数转换成多少页
        $pageTo=ceil($totl/$size);

        $from = ($page-1)*$size;
        $data['_list'] = D('show_log logs')
            ->field('shows.title as titles,users.avatar as usersavatar,users.user_nicename,logs.addtime as addtimes,logs.read_state')
            ->join('cmf_users as users ON users.id = logs.uid')
             ->join('cmf_show as shows ON shows.id = logs.show_id')
             ->where($where)
             ->order('logs.addtime desc')
             ->limit($from,$size)
             ->select();
              $data['_totalPages'] = $pageTo;
        return $data;
        // $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data));
    }
    //阅读历史
    public function wocollect(){
        $page = !empty($parem['p'])? $parem['p'] : 1;
        $size = !empty($parem['r'])? $parem['r'] : C('p');
        $where = array('uid'=>$this->uid,'type'=>'read');
        $totl = D('show_log')->where($where)->count();

        //总数转换成多少页
        $pageTo=ceil($totl/$size);

        $from = ($page-1)*$size;

        $data['_list'] = D('show_log log')
               ->field('shows.title,shows.id as showid,shows.data,shows.addtime as showaddtime,types.name as typenames,log.addtime as shoutime')
               ->join('cmf_show as shows ON shows.id = log.show_id')
               ->join('cmf_club_type as types ON types.id = shows.sel_type')
               ->where(array('log.uid'=>$this->uid,'log.type'=>'read'))
               ->limit($from,$size)
               ->select(); 
        foreach ($data['_list'] as $key => $value) {
            $img = explode(',',$value['data']);
            // $imgs = D('Picture')->field('path')->where(array('id'=>$img))->find();  
            $data['_list'][$key]['img'] = $img;
            //帖子发表时间
            $data['_list'][$key]['showaddtime'] = date('Y-m-d',$value['showaddtime']);
            //收藏时间
            $data['_list'][$key]['shoutime'] = date('Y-m-d',$value['shoutime']);
        }  
        //循环查 路径
        foreach ($data['_list'] as $key => $value) {
            foreach ($value['img'] as $k => $val) {

               $data['_list'][$key]['imgs'][] =  D('Picture')->field('id,path')->where(array('id'=>$val))->find(); 
               unset($data['_list'][$key]['img']);
            }
               
        }   
       
        $data['_totalPages'] = $pageTo;      

        $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data));

    }
    //视频收藏
    public function wovideos(){

        $parem = I('post.');
        $data = $this->ziviwd('favor',3,$parem['p'],$parem['r']);
        $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data));
    } 
    //资讯收藏
    public function zxmation(){

        $parem = I('post.');
        $data = $this->ziviwd('favor',2,$parem['p'],$parem['r']);
        $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data)); 


    }
    public function ziviwd($type,$showtype,$p,$s){
        $page = !empty($p)? $p : 1;
        $size = !empty($s)? $s : C('p');
        $where = array('uid'=>$this->uid,'type'=>$type);
        $totl = D('show_log')->where($where)->count();

        //总数转换成多少页
        $pageTo=ceil($totl/$size);

        $from = ($page-1)*$size;

        $data['_list'] = D('show_log log')

           ->field('shows.addtime as showaddtime,log.addtime as shoutime,shows.reading,shows.title,shows.data')

           ->join('cmf_show as shows ON shows.id = log.show_id')
           ->where(array('log.type'=>$type,'log.uid'=>$this->uid,'shows.type'=>$showtype))
           ->limit($from,$size)
           ->select();
           foreach ($data['_list'] as $key => $value) {
            $img = explode(',',$value['data']);
            $data['_list'][$key]['img'] = $img;
             //帖子发表时间
            $data['_list'][$key]['showaddtime'] = date('Y-m-d',$value['showaddtime']);
            //收藏时间
            $data['_list'][$key]['shoutime'] = date('Y-m-d',$value['shoutime']);  
           }
        //循环查 路径
        foreach ($data['_list'] as $key => $value) {
            foreach ($value['img'] as $k => $val) {

               $data['_list'][$key]['imgs'][] =  D('Picture')->field('id,path')->where(array('id'=>$val))->find(); 
               unset($data['_list'][$key]['img']);
            }
               
        }     
        $data['_totalPages'] = $pageTo;  
        //$this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data));
        return $data;   

    }
    //我的喜欢  关注数量 粉丝数量  发表的文章数量
    public function wolike(){
          //关注数量
          $Number = D('UserRelation')->where(array('uid'=>$this->uid,'type'=>2))->count();
          if(!$Number){
             $Number = 0;
          }
          //获取粉丝数量
          $fens =  D('UserRelation')->fans($this->uid);

          if(!$fens){
            $fens = 0;
          }
          //发表
          $fabiao = D('show')->where(array('uid'=>$this->uid,'status'=>1))->count();
          if(!$fabiao){

          }
          //查询头像
          $to = D('users')->where(array('id'=>$this->uid))->find();

          $data = [
             'number' => $Number,
             'fensi' => $fens,
             'fabiao' => $fabiao,
             'name' => $to['user_nicename'],
             'path' => $to['avatar'],

          ];
          $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data));
    }
    
}
