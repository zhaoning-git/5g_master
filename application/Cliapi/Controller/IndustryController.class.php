<?php 
/**
 * 行业信息
 */
namespace Cliapi\Controller;

use Think\Controller;

class IndustryController extends ApiController {
  //行业
  public function industry(){
        
		$data = D('industry')->where('status=0')->select();
		if($data){

            $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$data));
		}
		 

	}
	//注册
	public function binding(){

      $parem = I('post.');
      //手机验证码
      if (!D('Verify')->checkVerify($parem['mobile'], 'binding', $parem['sms_code'])) {
          $this->ajaxRet(array('info' => D('Verify')->getError()));
      }
      //检测手机号
      $Phone = Verify_Phone($parem['mobile']);
      if(!$Phone){
        $this->ajaxRet(array('status' => 0, 'info' => '手机号不合法！','data'=>''));
      }
      //检测邮箱
      $isEmail = checkEmail($parem['mailbox']);
      if(!$isEmail){
        $this->ajaxRet(array('status' => 0, 'info' => '邮箱不合法！','data'=>''));
      }
      $User = D('Binding');
      $dit = D('users')->where('id='.$parem['uid'])->find();
      if($dit){
       //如果有绑定账号
      	 $parem['user_id'] = $dit['id'];
      }else{
      	//注册 账号
      	$uid = D('Users')->register($parem['mobile'],$parem['mobile']);
      	if($uid && $uid > 0 && is_numeric($uid)){
            D('Users')->login($uid); //登陆
            
            //此时用户信息获取不能用User()
            $UserInfo = M('Users')->where(array('id'=>$uid))->find();
            $parem['user_id']  = $uid;

      	}elseif($uid === false){
            $this->ajaxRet(array('info' => D('Users')->getError()));
        }
        else {
            $this->ajaxRet(array('info' => D('Users')->getMsgError($uid)));
        }


      }

       $where['user_id'] = $parem['uid'];
       $is_bin = D('Binding')->where($where)->find();
       if($is_bin){
          $this->ajaxRet(array('status' => 0, 'info' => '您已经注册，请登录','data'=>''));
       }
       //删除 code 码
       //unset($parem['sms_code']);
       $data = $User->add($parem);
        if(!$data){
           $this->ajaxRet(array('status' => 0, 'info' => '注册失败','data'=>''));
        }
         $this->ajaxRet(array('status' => 1, 'info' => '注册成功','data'=>''));
        

	}
	//登录
	public function binglogin(){
       $parem = I('post.');
       if(!isset($parem['mobile'])){
         $this->ajaxRet(array('status' => 1, 'info' => '手机号必须','data'=>''));
       }
       if(empty($parem['mobile'])){
         $this->ajaxRet(array('status' => 1, 'info' => '手机号为空','data'=>''));
       }
       if(!isset($parem['sms_code'])){
         $this->ajaxRet(array('status' => 1, 'info' => '验证码必须','data'=>''));
       }
       if(empty($parem['sms_code'])){
         $this->ajaxRet(array('status' => 1, 'info' => '验证码为空','data'=>''));
       }
       //检查手机号
       if (!D('Verify')->checkVerify($parem['mobile'], 'binlogin', $parem['sms_code'])) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
        }
        //查询库
        $where['mobile'] = $parem['mobile'];
        $data = D('Binding')->where($where)->find();
        if(!$data){
           $this->ajaxRet(array('status' => 0, 'info' => '无此账号，请注册','data'=>''));
        }
        $this->ajaxRet(array('status' => 1, 'info' => '登录成功','data'=>$data));

	}
	//联系方式
	public function relation(){
	   $parem = I('post.');
       $User = D('Relation');
       if(!$User->create()){
          // 如果创建失败 表示验证没有通过 输出错误提示信息       
          $this->ajaxRet(array('status' => 0, 'info' => $User->getError(),'data'=>''));
       }
      
       //个人
       if($parem['types'] == 1){
          //手机验证码
         if (!D('Verify')->checkVerify($parem['mobile'], 'relation', $parem['sms_code'])) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
         }
         $data = $User->add($parem);
         if($data){
            $this->ajaxRet(array('status' => 1, 'info' => '我们将尽快与您联系
                 请您保持电话畅通','data'=>''));
         }
            $this->ajaxRet(array('status' => 0, 'info' => '提交失败','data'=>''));
       }
        //企业 判断 企业名称
        if (!D('Verify')->checkVerify($parem['mobile'], 'comption', $parem['sms_code'])) {
            $this->ajaxRet(array('info' => D('Verify')->getError()));
        }
        if(empty($parem['enterprise'])){
           $this->ajaxRet(array('status' => 0, 'info' => '企业名称为空','data'=>''));
        }
        
        
        $data = $User->add($parem);
        if(!$data){
           $this->ajaxRet(array('status' => 0, 'info' => '注册失败','data'=>''));
        }
        $this->ajaxRet(array('status' => 1, 'info' => '注册成功','data'=>''));

	}
	//地区 省市 两级联动 请求参数 type 1 省 2 市
	public function city(){
       $parem = I('get.');
       //验证 type  id
       if(!isset($parem['type'])){
         $this->ajaxRet(array('status' => 0, 'info' => '类型字段必须','data'=>''));
       }
       if(!isset($parem['id'])){
         $this->ajaxRet(array('status' => 0, 'info' => 'id必须','data'=>''));
       }
       $type = isset($_GET['type'])?$_GET['type']:0;//获取请求信息类型 1省 2市 3区
       // $province_num = isset($_GET['pnum'])?$_GET['pnum']:'440000';//根据省编号查市信息
       // $city_num = isset($_GET['cnum'])?$_GET['cnum']:'440100';//根据市编号查区信息
       //查询出 省
       switch ($type) {//根据请求信息类型，组装对应的sql
         case 1://省
           $province = D('city')->where('level=1')->select();
       	 break;
         case 2://市
             if(empty($parem['id'])){
               $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>'市'));
               
             }
               $where['parentId'] = $parem['id'];
               $province = D('city')->where($where)->select();
         
       }
      // $province = D('city')->where('level=1')->select();
       $this->ajaxRet(array('status' => 1, 'info' => '获取成功','data'=>$province));
 

	}
  //热门专家
  public function hotattent(){
    $data = D('specialist')
             ->field('cmf_specialist.id as ids,speciname,portrait.path as portraits')
             ->join('cmf_picture as portrait ON cmf_specialist.portrait = portrait.id')
             ->group('fans')->order('max(fans) desc')
             ->where('cmf_specialist.is_audit=2')
             ->limit('8')//限制8条
                 ->select();
          $data = array_map(function($val){
                  $val['portraits'] = AddHttp($val['portraits']);
                return $val;
            },$data);       
        $this->ajaxRet(array('status' => 1, 'info' =>'获取成功','data'=>$data));
  }
  //获取专家库 类别
  public function specialisttypes(){

    $data = D('specialisttype')->select();
    $this->ajaxRet(array('status' => 1, 'info' =>'获取成功','data'=>$data));
  }
  //点类别 出 专家
  public function category(){
    $parem = I('get.');
    if(empty($parem['spec_type'])){
      $this->ajaxRet(array('status' => 0, 'info' =>'参数为空','data'=>$data));
    }
    $map =  ['is_audit'=>2,'spec_type'=>$parem['spec_type']];
    $data['_list'] = $this->lists('specialist',$map);
   
    foreach ($data['_list'] as $key => $value) {
         $img = D('picture')->where(array('id'=>$value['portrait']))->find();
         $data['_list'][$key]['img'] = AddHttp($img['path']);
    }
    $data['_totalPages'] = $this->_totalPages; //总页数
    $this->ajaxRet(array('status' => 1, 'info' =>'获取成功','data'=>$data));
   
    

  }
  //专家详情
  public function detailescte(){

    //获取专家id  用户id  查看我是否已经关注
    //查询 专家的 名称 粉丝 介绍  发表的方案 调用开奖接口 实时修改 文案的 赌注结果
    $parem = I('get.');
    if(empty($parem['spec_type'])){
       $this->ajaxRet(array('status' => 0, 'info' =>'专家id为空','data'=>''));
    }
    if(!isset($parem['user_id'])){
       $this->ajaxRet(array('status' => 0, 'info' =>'未设置user_id','data'=>''));
    }
    //默认没有关注
    $data = ['is_zhu'=>0,'fans'=>0];
    //查询是否 已经关注
    $user_id = $parem['user_id'];
    $is_zhu = D('specialist_user')->where(array('user_id'=>$user_id,'specialist_id'=>$parem['spec_type']))->find();
    if($is_zhu){
     $data['is_zhu'] = '1';//已关注
    }

    $spe= D('specialist as sp')
         ->field('portrait.path as portraits,fans,small,introduce,speciname,sp.user_id,sp.id as ids')
         ->join('cmf_picture as portrait ON sp.portrait = portrait.id')
         ->where(array('sp.id'=>$parem['spec_type'],'sp.is_audit'=>2))->find();
    //获取发表赛事的id
    $sai = [];
    //查询在售的方案 及时更新比赛结果
    $spepush = D('sppublish')->where(array('id'=>$parem['spec_type'],'is_history'=>0))->select();
     foreach ($spepush as $key => $value) {
       $sai[]= D('MatchResult')->getMatchResult($value['competition']);
     }
     $rqspf = [];
     foreach ($sai as $key => $val) {
     
      //如果比赛完场
       if($val['state'] == '-1'){
          //修改对应赛事的 状态  修改历史方案 谁赢谁输
          //判断谁胜 如果主队大于客队  主队胜
         
          //获取赛事id  查询 正常  让球
          
           $d = D('sppublish')->where(array('id'=>$parem['spec_type'],'competition'=>$val['match_id']))->find();
           $rqspf = D('rqspf')->where(array('id'=>$d['rqspf']))->find();
           $spf = D('spf')->where(array('id'=>$d['spf']))->find();
           //获取 让球个数
           $goal = $rqspf['goal'];
           //主队让球
           if($goal<0){
             //胜
             if(($val['homeScore']-$val['awayScore'])>abs($goal)){
                $sheng = 'rq3';
              }
              //负
              if(($val['homeScore']-$val['awayScore'])<abs($goal)){
                $sheng = 'rq0'; 
              }
              //平
              if(($val['homeScore']-$val['awayScore']) == abs($goal)){
                $sheng = 'rq1';  
              }
             
           }
           //客队让球
           if($goal>0){
              
              if(($val['awayScore']-$val['homeScore'])<$goal){
                $sheng = 'rq3';
              }
              if(($val['awayScore']-$val['homeScore'])==$goal){
                $sheng = 'rq1';
              }
              if(($val['awayScore']-$val['homeScore'])>$goal){
                $sheng = 'rq0';
              }
           }
          //正常比分

           if($val['homeScore'] > $val['awayScore']){
             
             $victory = 'spf3';
           }
           if($val['homeScore'] == $val['awayScore']){
            
             $victory = 'spf1';
           }
           if($val['homeScore'] < $val['awayScore']){
           
            $victory = 'spf0';
           }
          //修改结果
           $rs = D('rqspf')->where(array('id'=>$rqspf['id']))->save(['match'=>$sheng]);
           $ss = D('spf')->where(array('id'=>$spf['id']))->save(['match'=>$victory]);
           //比分结果写入
            D('sppublish')->where(array('id'=>$parem['spec_type']))->save(['homescore'=>$val['homescore'],'awayscore'=>$val['awayscore']]);
           //更新状态 是否是在售 历史
            $affirm = 0;
           //判断是否专家 预测正确
           if($rqspf['selects'] == $rqspf['match']){
              $affirm = 1;//正确
           }
           if($spf['selects'] == $spf['match']){
               $affirm = 1;//正确
           }

           D('sppublish')->where(array('speci_id'=>$spe['user_id'],'competition'=>$val['match_id']))->save(['is_history'=>1,'affirm'=>$affirm]);
       }
     }
    //查询赛事的比赛结果

    // $data = D('MatchResult')->getMatchResult($match_id);
    if($spe){

       //获取粉丝数
       $data['fans'] = $spe['fans'];//粉丝数
       $data['small'] = $spe['small'];//小描述
       $data['introduce'] = $spe['introduce'];//介绍
       $data['speciname'] = $spe['speciname'];//用户名
       $data['img'] = $spe['portraits'];//专家头像

       //查询出在售方案 就是说是 比赛没有结束的
       $sale = D('sppublish')->where(array('speci_id'=>$spe['user_id'],'is_history'=>0))->select();
       $data['sale'] = $sale;

    }
   // $da = D('Jingcai')->getMatch();
    $this->ajaxRet(array('status' => 1, 'info' =>'获取成功','data'=>$data));
  }
  //历史方案
  public function quhistory(){
    //专家id 历史状态
    $parem =I('get.');
    if(empty($parem['speci_id'])){
      $this->ajaxRet(array('status' => 1, 'info' =>'参数为空','data'=>''));
    }
    //查询专家所有文章
   
    $map =  ['speci_id'=>$parem['speci_id'],'is_history'=>1];
    $data['_list'] = $this->lists('sppublish',$map);
    $data['_totalPages'] = $this->_totalPages; //总页数
    $this->ajaxRet(array('status' => 1, 'info' =>'获取成功','data'=>$data));

  }
  //在售方案 详情
  public function saledetails(){
    $parem = I('get.');

    if(empty($parem['deta_id'])){
      $this->ajaxRet(array('status' => 1, 'info' =>'详情id为空','data'=>'')); 
    }
    if(empty($parem['user_id'])){
      $this->ajaxRet(array('status' => 1, 'info' =>'用户id为空','data'=>'')); 
    }
    if(empty($parem['spec_type'])){
      $this->ajaxRet(array('status' => 1, 'info' =>'专家id为空','data'=>'')); 
    }
    $spe= D('specialist as sp')
         ->field('portrait.path as portraits,fans,small,introduce,speciname,sp.user_id,sp.id as ids')
         ->join('cmf_picture as portrait ON sp.portrait = portrait.id')
         ->where(array('sp.id'=>$parem['spec_type'],'sp.is_audit'=>2))->find();
         
    $spe['portraits'] = AddHttp($spe['portraits']);
    $is_zhu = D('specialist_user')->where(array('user_id'=>$parem['user_id'],'specialist_id'=>$parem['spec_type']))->find();
    if($spe && $is_zhu){
       $spe['is_zhu'] = 1;//已关注

    }

    //查询详情 判断是否是在线 历史
    $particulars = D('sppublish')->where(array('id'=>$parem['deta_id']))->find();
    if($particulars){
       $spe['title'] = $particulars['title'];
       $spe['matchtime'] = $particulars['matchtime'];
       $spe['week'] = $particulars['week'];
       $spe['home'] = $particulars['home'];
       $spe['away'] = $particulars['away'];
       $spe['league'] = $particulars['league'];
       //获取让球胜平负  胜平负
       $spe['rqspf'] = D('rqspf')->where(array('id'=>$particulars['rqspf']))->find();

       $spe['spf'] = D('spf')->where(array('id'=>$particulars['spf']))->find();

       //时间戳 转成 时间 
       //开始时间
       //$data = D('MatchResult')->getMatchResult('1814735');
       $startdate = date('Y-m-d H:i:s',$particulars['create_time']);
       //当前时间
       $enddate = date('Y-m-d H:i:s',time());
       $spe['create_time'] = times($startdate,$enddate);
    }
    
    //查询比赛的对方 

    //在售方案
    if($particulars['is_history'] == 0){
       //判断是否购买
       $pay = D('specialist_pay')->where(array('user_id'=>$parem['user_id'],'article_id'=>$parem['deta_id']))->find();
       if(!$pay){
         $spe['pay'] = 0;
         $spe['price'] = $particulars['price'];

       }else{
         $spe['article'] = $particulars['article'];
       }
    }
    //历史方案
    if($particulars['is_history'] == 1){
      $spe['awayscore'] = $particulars['awayscore'];
      $spe['homescore'] = $particulars['homescore'];
    }
     //$s = D('Jingcai')->getMatch();
     $this->ajaxRet(array('status' => 1, 'info' =>'','data'=>$spe)); 
  }

}
