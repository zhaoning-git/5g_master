<?php

/**
 * 专家
 * 
 */

namespace Cliapi\Controller;

use Think\Controller;

class SpecialistController extends MemberController {
	//关注专家
	public function attention(){

		$parem = I('post.');
		//获取专家id 
		if(empty($parem['spe_id'])){
           $this->ajaxRet(array('status' => 0, 'info' =>'参数为空','data'=>''));
		}
		//关注专家 修改专家粉丝
		$id = $this->uid;
		$User = D('specialist_user');
		$da = [
              'specialist_id'=>$parem['spe_id'],
              'user_id' => $id,
              'create_time' => date('Y-m-d H:i:s'),
		];
		$ud = $User->where(array('specialist_id'=>$parem['spe_id'],'user_id' => $id))->find();
		if($ud){
           $this->ajaxRet(array('status' => 0, 'info' =>'你已经关注过了！','data'=>''));
		}
		try {

			$data = $User->add($da);
			//修改专家的粉丝数
			M('Specialist')->where('id='.$parem['spe_id'])->setInc("fans",1);
		} catch (\Exception $e) {
			$this->ajaxRet(array('status' => 0, 'info' =>'关注失败','data'=>''));
		}
		
		
        $this->ajaxRet(array('status' => 1, 'info' =>'关注成功','data'=>''));
	
		

	}
	//取消关注
	public function offatten(){
		//判断已经关注了
		$parem = I('post.');
		if(empty($parem['spe_id'])){
           $this->ajaxRet(array('status' => 0, 'info' =>'参数为空','data'=>''));
		}
		$User = D('specialist_user');
		$where = [
              'specialist_id'=>$parem['spe_id'],
              'user_id' => $this->uid,
		];
		$ud = $User->where($where)->find();
		if(!$ud){
           $this->ajaxRet(array('status' => 0, 'info' =>'你还没有关注怎么取消！','data'=>''));
		}
		//删除数据  去掉粉丝
		try {
			$del = $User->where($where)->delete();
		    M('Specialist')->where('id='.$parem['spe_id'])->setDec("fans",1);
		} catch (\Exception $e) {
		   $this->ajaxRet(array('status' => 0, 'info' =>'取消失败','data'=>'')); 
		}
		 $this->ajaxRet(array('status' => 1, 'info' =>'取消关注成功','data'=>''));

	}
	//我关注的专家列表
	public function attentionwolist(){

		$parem = I('get.');
		//var_dump($parem);die;
		$uid = $this->uid;
		$page = !empty($parem['p'])? $parem['p'] : 1;
		$size = !empty($parem['r'])? $parem['r'] : C('p');
        $totl = D('specialist_user')->where('cmf_specialist_user.user_id='.$uid)->count();

        //总数转换成多少页
        $pageTo=ceil($totl/$size);

        $from = ($page-1)*$size; 
        
		$data = D('specialist_user')
		        ->field('sp.speciname as spname,sp.id as spid,portrait.path as portraits')
		        ->join('cmf_specialist as sp ON cmf_specialist_user.specialist_id = sp.id')
		        ->join('cmf_picture as portrait ON sp.portrait = portrait.id')
		        ->where('cmf_specialist_user.user_id='.$uid)
		        ->limit($from,$size)
		        ->select();
		
		$data['_totalPages'] = $pageTo;   
		$a=array_map(function($val){
                  $val['portraits'] = AddHttp($val['portraits']);
                return $val;
            },$data);  
		if($data){
            $this->ajaxRet(array('status' => 1, 'info' =>'获取成功','data'=>$a));
		}

	}
	
	//专家认证 提交审核
	public function certification(){
     $parem = I('post.');

     $User = D('Specialist');
    
     if(!$User->create()){
             // 如果创建失败 表示验证没有通过 输出错误提示信息       
        $this->ajaxRet(array('status' => 0, 'info' =>$User->getError(),'data'=>''));
     }
      $bramd = D('Specialist')->where(array('user_id'=>$this->uid,'is_audit'=>2))->find();
      if($bramd){
        $this->ajaxRet(array('status' => 0, 'info' =>'你已经是专家，请勿在此提交','data'=>''));
      }
      $id =  $this->uid;
      $where['user_id'] = $id;
      $data = D('Specialist')->where($where)->find();
      if($data && $data['is_audit'] = 1){
          $this->ajaxRet(array('status' => 0, 'info' =>'您已经提交过审核，请勿重复提交','data'=>''));
      }  
     //用户id
     $parem['user_id'] = $this->uid;
     //审核状态 默认 1 待审核
     $parem['is_audit'] = 1;
     $data = $User->add($parem);
     if(!$data){
        $this->ajaxRet(array('status' => 0, 'info' => '创建失败','data'=>''));
     }
        $this->ajaxRet(array('status' => 1, 'info' => '创建成功,等待审核','data'=>''));
   
	}
	// 专家发表文案
	public function publish(){
      $parem =  I('post.');
      if(empty($parem['title'])){
        $this->ajaxRet(array('status' => 0, 'info' => '标题为空','data'=>''));
      }
      
      if(empty($parem['price'])){
        $this->ajaxRet(array('status' => 0, 'info' => '支付金额为空','data'=>''));
      }
      if(empty($parem['competition'])){
        $this->ajaxRet(array('status' => 0, 'info' => '比赛为空','data'=>''));
      }
      if(empty($parem['article'])){
        $this->ajaxRet(array('status' => 0, 'info' => '文章为空','data'=>''));
      }
      if(empty($parem['game'])){
        $this->ajaxRet(array('status' => 0, 'info' => '结果为空','data'=>''));
      }
      //1 为正常比赛 2 为让球赛程
      if(empty($parem['state'])){
        $this->ajaxRet(array('status' => 0, 'info' => '比赛状态为空','data'=>''));
      }
      //查询是否已经发表
      $alreadys = D('sppublish')->where(array('speci_id'=>$this->uid,'competition'=>$parem['competition']))->find();
       if($alreadys){
        $this->ajaxRet(array('status' =>0, 'info' => '您已经发表过改赛事的文案！','data'=>''));
       }
      $parem['create_time'] = time();
      $parem['speci_id'] = $this->uid;

      //获取赛事id查询出 比赛详情
      $sai_id = $parem['competition'];
      $da = [];
      $metchs = [];
      $data = D('Jingcai')->getMatch();
      //$this->ajaxRet(array('status' => 1, 'info' => '发表成功','data'=>$data));
      $sq = $data['odds'];
      $metch = $data['match'];
      foreach ($sq as $key => $value) {
         if($key == $sai_id){
           $da= $value;
         }
      }
      foreach ($metch as $key => $value) {
      	if($key == $sai_id){
          $metchs = $value;
      	} 
      }
             //如果选择是正常赛事 
      
      
      //插入 让球 表 获取id
      $rqs = [
         'goal' => $da['rqspf']['goal'],
         'rq3' => $da['rqspf']['rq3'],
         'rq1' => $da['rqspf']['rq1'],
         'rq0' => $da['rqspf']['rq0'],
         'stop' => $da['rqspf']['stop'],
      ];
      
      if($parem['state'] == 2){
        $rqs['selects'] = $parem['game'];
      }

      $rq = M('rqspf')->add($rqs);

      $rqid = M('rqspf')->getLastInsID();
      //插入 
      $spfs =  [
         
         'spf3' => $da['spf']['spf3'],
         'spf1' => $da['spf']['spf1'],
         'spf0' => $da['spf']['spf0'],
         'stop' => $da['spf']['stop'],
      ];

      if($parem['state'] == 1){
        $spfs['selects'] = $parem['game'];
      }

      $spf = M('spf')->add($spfs);
      $spfid = M('spf')->getLastInsID();
      //插入赛事 双方
      $parem['matchTime'] = $metchs['matchTime'];
      $parem['week'] = $metchs['week'];
      $parem['home'] = $metchs['home'];
      $parem['away'] = $metchs['away'];
      $parem['league'] = $metchs['league'];
      $parem['isTurn'] = $metchs['isTurn'];
      $parem['rqspf'] = $rqid;
      $parem['spf'] = $spfid;
      $data = M('sppublish')->add($parem);
      if($data){
        $this->ajaxRet(array('status' => 1, 'info' => '发表成功','data'=>''));
      }
      
     
     
	}
	// //获取比赛结果
	// public function matchs($match_id){
	// 	$parem = I('get.');
	// 	$match_id = $parem['match_id'];
	// 	$data = D('MatchResult')->getMatchResult($match_id);
	// 	return $data;
	// }
	//根据赛事 id 返回比赛
	public function game(){
		//获取比赛id
		$parem = I('get.');
		if(empty($parem['competition'])){
           $this->ajaxRet(array('status' => 1, 'info' => '赛事为空','data'=>''));
		}		
        $metchs = [];
        $data = D('Jingcai')->getMatch();
        $sai_id = $parem['competition'];
     
        $metch = $data['odds'];
     
        foreach ($metch as $key => $value) {
      	  if($key == $sai_id){
            $metchs = $value;
      	  } 
        }
        $data = [
              'spf' =>$metchs['spf'],
              'rqspf' =>$metchs['rqspf'],
        ];
        $this->ajaxRet(array('status' => 1, 'info' => '','data'=>$data));

	}
	//支付 专家方案详情
	public function detailspay(){
        
		$parem = I('post.');
		$id = $this->uid;

		// if(empty($parem['article_id'])){
  //          $this->ajaxRet(array('status' => 0, 'info' => '文章id为空','data'=>''));
 	// 	}
 		if(empty($parem['gold_coin'])){
            $this->ajaxRet(array('status' => 0, 'info' => '金币为空','data'=>''));
 		}
 		//查询用户金币余额
 		$yu = D('users')->where(array('id'=>$id))->find();
 		if($yu['gold_coin'] < $parem['gold_coin']){
           $this->ajaxRet(array('status' => 0, 'info' => '金币余额不足，请充值','data'=>''));
 		}
 		$this->ajaxRet(array('status' => 1, 'info' => '可以购买','data'=>''));
 		//写入用户消费表
 		//开启事务
 		//M()->startTrans();

        

	}
	public function payplay(){
        $parem = I('post.');
        $id = $this->uid;
        if(empty($parem['article_id'])){
           $this->ajaxRet(array('status' => 0, 'info' => '文章id为空','data'=>''));
 		}

 		if(empty($parem['gold_coin'])){
            $this->ajaxRet(array('status' => 0, 'info' => '金币为空','data'=>''));
 		}
        //查询文章 获取 专家
        $wen = D('sppublish')->where(array('id'=>$parem['article_id']))->find();
        if(!$wen){
          $this->ajaxRet(array('status' => 0, 'info' => '无文章','data'=>''));
        }
        $specialist_id = $wen['speci_id'];
 		$yu = D('users')->where(array('id'=>$id))->find();
 		
 		if($yu['gold_coin'] < $parem['gold_coin']){
           $this->ajaxRet(array('status' => 0, 'info' => '金币余额不足，请充值','data'=>''));
 		}
 		//查询是否已经购买过此文章
 		$gopu = D('specialist_pay')->where(array('user_id'=>$id,'article_id'=>$parem['article_id']))->find();
 		if($gopu){
          $this->ajaxRet(array('status' => 0, 'info' => '您已经购买过此文章！','data'=>''));
 		}
 		//用户消费表  购买表
 		M()->startTrans();
        try {
        	$gold = $yu['gold_coin']-$parem['gold_coin'];
        	//修改用户金币
        	D('users')->where(array('id'=>$id))->save(['gold_coin'=>$gold]);
        	//写入消费表
        	$con = [
                'user_id' => $id,
                'consume_time' => date('Y-m-d H:i:s'),
                'consume_price' => $parem['gold_coin'],
                'payment' => '余额金币支付',
                'article_id' => $parem['article_id'],
                'purpose' => '购买专家文案',
        	];
        	D('user_consume')->add($con);
        	//写入购买记录表
        	$pat = [
               'specialist_id' => $specialist_id,
               'article_id' => $parem['article_id'],
               'money' => $parem['gold_coin'],
               'create_time' => date('Y-m-d H:i:s'),
               'user_id' =>$id,
               'order_no' => order_no(),
        	];
            D('specialist_pay')->add($pat);

        	M()->commit(); 
        	 $this->ajaxRet(array('status' => 1, 'info' => '购买成功','data'=>''));
        } catch (\Exception $e) {
        	M()->rollback();
        	$this->ajaxRet(array('status' => 0, 'info' => '购买失败','data'=>''));
        }
	}
}