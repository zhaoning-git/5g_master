<?php
/**
 * 球队 类别
 */
namespace Cliapi\Controller;
use Think\Controller;

class classifyController extends MemberController{
    public function _initialize() {
        parent::_initialize();
    }

    //分类
    function classifylist_(){
        $user = D('Club_type')->where('status=0')->select();
        $user['is_zhu'] = 0;

        //查询该用户有没有关注过球队列表
        $data = D('user_team')->where('uid='.$this->uid)->select();
        if($data){
            $user['is_zhu'] = 1;//说明该用户有关注的球队 前台跳页面
        }

        if($user){
            $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$user));
        }

        $this->ajaxRet(array('status' => 0, 'info' => '无数据','data'=>''));
    }


    //联赛杯赛简称 球队分类 (球队)
    function classifylist(){
        $Hot['leagueId'] = 0;
        $Hot['nameChsShort'] = '热门';

        $list = M('LibraryLeague')->where(['is_yesguanzhu'=>1])->field('leagueId,nameChsShort')->select();
        if(!empty($list)){
            $list = array_merge([0=>$Hot], $list);
            
            foreach($list as &$value){
                $value['ItemList'] = $this->Item($value['leagueid']);
            }

            $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>['_list'=>$list]));
        }else{
            $this->ajaxRet(array('status' => 0, 'info' => '无数据','data'=>''));
        }
    }

    //获取球队
    public function Item($leagueId){
        if(!$leagueId){
            $map['is_hot'] = 1;
        }else{
            $map['leagueId'] = $leagueId;
        }
        
        $list = M('LibraryTeam')->where($map)->field('teamId,leagueId,nameEn,nameChs,logo')->select();
        return $list;
    }


    //获取球员
    function player(){
        //参数 event_id 类别id

        //接受参数
        $parem = I('get.');
        $event_id = $parem['event_id'];

        //查询 球员
        $where = ['event_id'=>$event_id,'softdel'=>1];
        $data = D('Ball_team')->where($where)->select();

        // var_dump($event_id);die;
        $data['is_zhu'] = 0;

        //查询该用户有没有关注过球队列表
        $datas = D('user_team')->where('uid='.$this->uid)->select();

        if($datas){
            $data['is_zhu'] = 1;//说明该用户有关注的球队 前台跳页面
        }

        if($data){
            $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$data));
        }
        $this->ajaxRet(array('status' => 0, 'info' => '暂无球员','data'=>''));
    }


    //设置&&取消 主队 (球队)
    function attention_team(){
        $status = I('post.status');
        $team_id = I('post.team_id');
        
        if(!$team_id){
            $this->ajaxRet(array('status'=>0,'info'=>'请选择要操作的球队'));
        }
        if($team_id == -1){
            $this->ajaxRet(array('status'=>1,'info'=>'还没想好'));
        }

        M('UserTeam')->where(['uid'=>$this->uid])->setField('is_zhu',0);

        if($status == 1){
            M('UserTeam')->where(['uid'=>$this->uid,'team_id'=>$team_id])->setField('is_zhu',1);
        }
        $this->ajaxRet(array('status'=>1,'info'=>'主队设置成功'));
    }



    //设置主队
    function attention_team_(){
        $team_id = I('post.');
        $uid =  $this->uid;
        if($team_id['team_id']=='null'){
            $this->ajaxRet(array('status'=>0,'info'=>'请选择要关注的球队'));
        }

        if($team_id['team_id'] == 0){
            $this->ajaxRet(array('status'=>1,'info'=>'还没想好'));
        }

        $status = M('UserTeam')->where(array('uid'=>$uid,'is_zhu'=>1))->find();
        if($status){
            $this->ajaxRet(array('status'=>0,'info'=>'您已经有主队！'));
        }

        $where = ['uid'=>$this->uid,'team_id'=>$team_id['team_id']];

        $res = M('user_team')->where($where)->save(['is_zhu'=>1]);

        if($res){

            $this->ajaxRet(array('status'=>1,'info'=>'主队设置成功','data'=> $res));

        }

        $this->ajaxRet(array('status'=>0,'info'=>'主队设置失败','data'=> ''));

    }

    //主队 选好了
    public function choice(){
        $parem = I('post.');
        if(empty($parem['team_id'])){
            $this->ajaxRet(array('status'=>0,'info'=>'请选择球队','data'=> ''));
        }

        //转化成数组
        $array = explode(',', $parem['team_id']);

        //循环插入
        $User = M('UserTeam');

        $User->create();
        $datas = [];

        foreach ($array as $key => $value) {
            if(!M('UserTeam')->where(['team_id'=>$value])->count()){
                $data['uid'] = $this->uid;
                $data['team_id'] = $value;
                $data['is_zhu'] = 0;
                $data['attention_time'] = time();
                $d = M('UserTeam')->add($data);
            }
        }

        if($d){
            $data = $this->myGuanzhu();
            $this->ajaxRet(array('status'=>1,'info'=>'操作成功','data'=>$data));
        }
        $this->ajaxRet(array('status'=>0,'info'=>'操作失败'));
    }

    //我的关注列表
    public function interestlist(){
        $data = $this->myGuanzhu();
        if($data){
            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$data));
        }else{
            $this->ajaxRet(array('status'=>0,'info'=>'获取失败'));
        }
    }

    private function myGuanzhu(){
        $PREFIX = C('DB_PREFIX');
        $map['ut.uid'] = $this->uid;
        $map['ut.is_zhu'] = 1;

        $data['zhudui'] = M('UserTeam ut')
            ->join($PREFIX.'library_team as lt  ON ut.team_id = lt.teamId')
            ->where($map)
            ->field('lt.nameChs,lt.logo, ut.is_zhu, ut.team_id')
            ->find();

        $map['ut.is_zhu'] = 0;
        $data['_list'] = M('UserTeam ut')
            ->join($PREFIX.'library_team as lt  ON ut.team_id = lt.teamId')
            ->where($map)
            ->field('lt.nameChs,lt.logo, ut.is_zhu, ut.team_id')
            ->select();
        return $data;
    }


    //默认展示热门
    public function hot(){

        $data = D('User_team')

            ->field('team_id,count(team_id),teams.name as teamname,pi.path')

            ->join('cmf_ball_team as teams  ON cmf_user_team.team_id = teams.id')

            ->join('cmf_picture as pi ON teams.img=pi.id')

            ->group('team_id')->order('count(team_id) desc')

            ->limit('21')//限制21条

            ->select();



        $this->ajaxRet(array('status'=>0,'info'=>'操作成功','data'=> $data));

    }

    //我的关注   设为主队
    public function attention(){
        //设为主队 传值 球队id  设为主队
        //首先查询该用户有没有已经设置的主队 如果有替换
        $parem = I('post.');

        if(empty($parem['team_id'])){

            $this->ajaxRet(array('status'=>0,'info'=>'参数为空','data'=> ''));

        }

        $up = M('user_team');

        $uo['is_zhu'] = 1;

        $where = ['uid'=>$this->uid,'is_zhu'=>1];

        $wheres = ['uid'=>$this->uid,'team_id'=>$parem['team_id']];

        $teams = M('user_team')->where($where)->find();



        if($teams){

            //修改

            $uos['is_zhu'] = 0;

            try {

                $data = $up->where('id='.$teams['id'])->save($uos);



                $d = $up->where($wheres)->save($uo);

            } catch (\Exception $e) {

                $this->ajaxRet(array('status'=>1,'info'=>'操作失败','data'=> ''));

            }



            $this->ajaxRet(array('status'=>1,'info'=>'操作成功','data'=> ''));





        }

        //如果没有任何主队

        $up->where($wheres)->save($uo);

        if($up){

            $this->ajaxRet(array('status'=>1,'info'=>'操作成功','data'=> ''));

        }

        $this->ajaxRet(array('status'=>0,'info'=>'操作失败','data'=> ''));





    }

    //我的关注 取消关注
    public function unfollow(){

        $parem = I('post.');

        if(empty($parem['team_id'])){

            $this->ajaxRet(array('status'=>0,'info'=>'参数为空','data'=> ''));

        }

        $where = ['uid'=>$this->uid,'team_id'=>$parem['team_id']];

        $data = D('user_team')->where($where)->delete();

        if($data){

            $this->ajaxRet(array('status'=>1,'info'=>'操作成功','data'=> ''));

        }

        $this->ajaxRet(array('status'=>0,'info'=>'操作失败','data'=> ''));

    }

    //取消主队
    public function unfozhu(){

        $parem = I('post.');

        if(empty($parem['team_id'])){

            $this->ajaxRet(array('status'=>0,'info'=>'参数为空','data'=> ''));

        }

        $where = ['uid'=>$this->uid,'team_id'=>$parem['team_id']];

        $data = D('user_team')->where($where)->save(['is_zhu'=>0]);

        if($data){

            $this->ajaxRet(array('status'=>1,'info'=>'操作成功','data'=> ''));

        }

        $this->ajaxRet(array('status'=>0,'info'=>'操作失败','data'=> ''));

    }


    //我的关注 关注更多 get
    public function inmore(){

        //查询出该用户 所有关注的球队 前台展示

        $where = ['uid'=>$this->uid];

        $data = D('user_team')

            ->field('team_id')

            ->where($where)->select();

        $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$data));

    }

    //热门球员
    function hot_mem(){

      $prefix = C('DB_PREFIX');

      $info = M('ball_mem m')

        ->join("{$prefix}picture p on p.id = m.headimg")

        ->field('m.id,m.name,p.url')

        ->order('m.hot desc')

        ->select();

      $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=> $info));

}

    //查询球队下的球员
    function team_mem(){
        $teamId = I('team_id');
        if(!$teamId){
            $this->ajaxRet(array('status'=>0, 'info'=>'球队ID不正确'));
        }
        $data['_list'] = D('Match')->player($teamId);
        $this->ajaxRet(array('status'=>1,'info'=>'球员资料','data'=>$data));
    }



    //搜索球队
    function search(){

        $prefix = C('DB_PREFIX');

        $name = I('post.name');

        if(empty($name)){

            $this->error = '搜索条件不能为空';

            return false;

        }

        $info = M('ball_team b')

            ->join("{$prefix}picture p on b.img = p.id")

            ->field('b.id,b.name,p.path')

            ->where(array('b.name'=>['like',"%$name%"]))

            ->select();

        if(empty($info)){

            $this->ajaxRet(array('status'=>0,'info'=>'暂无数据'));

        }else{

            $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$info));

        }

    }



    //管理球队
    function guanli(){
        $uid = $this->uid;
    }

    //球队 - 赛程
    public function agenda(){
        $TeamId = I('team_id');
        if(!$TeamId){
            $this->ajaxRet(array('status'=>0, 'info'=>'球队ID不正确'));
        }
        $data['_list'] = D('Match')->TeamSacheng($TeamId);
        $this->ajaxRet(array('status'=>1,'info'=>'球队赛程','data'=>$data));
    }

}