<?php

namespace Common\Model;

use Think\Model;

/**
 * 模型
 */
class StoreModel extends Model {

    function _initialize() {
        parent::_initialize();
    }

    //完善商店资料
    function Insert($data){
        unset($data['status'],$data['accountlog_id']);
        $data['uid'] = intval($data['uid']);
        if(!$data['uid']){
            $this->error = '参数有误!';
            return false;
        }

        $userInfo = User($data['uid'],true,true);

        if(!$userInfo['agents_id']){
            $this->error = '用户所属代理商错误!';
            return false;
        }

        if(empty($data['address'])){
            $this->error = '店铺地址不能为空!';
            return false;
        }

        if(empty($data['map'])){
            $this->error = '获取经纬度失败!';
            return false;
        }

        // 单独存经纬度
        $map = explode(',',$data['map']);
        list($data['lng'],$data['lat']) = $map;

        // 更新用户角色
        if(empty($data['supplier_role'])){
            $this->error = '请选择账户角色!';
            return false;
        }

        if(empty($data['supplier_type'])){
            $this->error = '请选择店铺类型!';
            return false;
        }

        $res = M('ucenter_member')->where(array('id'=>$data['uid']))->save(array('supplier_role'=>$data['supplier_role'],'supplier_type'=>$data['supplier_type']));

        if ($res === false){
            $this->ajaxRet(array('info'=>'失败，请重试'));
        }

        unset($data['supplier_role']);

        $info = $this->getStore($data['uid']);

        //修改
        if(is_array($info)){
            //判断店铺审核状态
            if($info['license'] != $data['license'] && $info['status'] != 0){
                //$this->error = '店铺已审核!无法修改营业执照';
                //return false;
            }

            if($info['title'] != $data['title'] && $this->where(array('title'=>$data['title']))->count()){
                $this->error = '店铺名称已存在!';
                return false;
            }

            $data['region_id'] =  $userInfo['region_id'];
            if( $this->where(array('id'=>$info['id']))->save($data) !== false){
                CleanUser($data['uid']);
                CleanStore(array('id'=>$info['id']));
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }

        //添加
        else{
            if(empty($data['title'])){
                $this->error = '店铺名称不能为空!';
                return false;
            }

            $data['agents_id'] =  $userInfo['agents_id'];
            $data['region_id'] =  $userInfo['region_id'];
//            $data['supplier_type'] = $data['supplier_type'];
            $data['addtime'] = time();
            if($this->add($data)){
                CleanUser($data['uid']);
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }
    }
    
    //完善商店资料
    function Insert_bak($data){
        unset($data['status'],$data['accountlog_id']);
        $data['uid'] = intval($data['uid']);
        if(!$data['uid']){
            $this->error = '参数有误!';
            return false;
        }
        
        $userInfo = User($data['uid'],true,true);
        
        if(!$userInfo['is_supplier']){
            $this->error = '用户不是商家!';
            return false;
        }
        
        if(!$userInfo['agents_id']){
            $this->error = '用户所属代理商错误!';
            return false;
        }
        
        if(empty($data['address'])){
            $this->error = '店铺地址不能为空!';
            return false;
        }
        
        $info = $this->getStore($data['uid']);
        
        //修改
        if(is_array($info)){
            //判断店铺审核状态
            if($info['license'] != $data['license'] && $info['status'] != 0){
                //$this->error = '店铺已审核!无法修改营业执照';
                //return false;
            }
            
            if($info['title'] != $data['title'] && $this->where(array('title'=>$data['title']))->count()){
                $this->error = '店铺名称已存在!';
                return false;
            }
            
            $data['region_id'] =  $userInfo['region_id'];
            if( $this->where(array('id'=>$info['id']))->save($data) !== false){
                CleanUser($data['uid']);
                CleanStore(array('id'=>$info['id']));
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }
        
        //添加
        else{
            if(empty($data['title'])){
                $this->error = '店铺名称不能为空!';
                return false;
            }       
            
            $data['agents_id'] =  $userInfo['agents_id'];
            $data['region_id'] =  $userInfo['region_id'];
            $data['supplier_type'] = $userInfo['supplier_type'];
            $data['addtime'] = time();
            if($this->add($data)){
                CleanUser($data['uid']);
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }
    }
    
    //获取用户的商店
    function getStore($uid='', $u=1, $new=false){
        $uid = intval($uid);
        if(!$uid){
            $this->error = '参数有误!';
            return false;
        }
        
        $key = 'getStore_'.$uid;
        
        if($new === true){
            S($key,null);
        }
        
        $info = S($key);
        
        if(empty($info)){
            $info = $this->where(array('uid'=>$uid))->find();
            $info = $this->info($info);
            if($info === false){
                return false;
            }
            S($key, $info);
        }
        
        if($u){
            $info['userInfo'] = User($info['uid']);
        }

        return $info;
    }
    
    //根据店铺ID获取商店
    function getOne($id='', $new=false){
        $id = intval($id);
        if(!$id){
            $this->error = '参数错误!';
            return false;
        }
        
        $key = 'StoreGetOne_'.$id;
        
        if($new === true){
            S($key,null);
        }
        
        $info = S($key);
        if(empty($info)){
            $info = $this->where(array('id'=>$id))->find();
            $info = $this->info($info);
            if($info === false){
                return false;
            }
            S($key, $info);
        }
        return $info;
    }


    private function info($info){
        if(empty($info)){
            $this->error = '店铺不存在!或还未完善店铺资料.';
            return false;
        }
        
        if($info['card_img']){
            $info['card_img_path'] = M('Picture')->where(array('id'=>$info['card_img']))->getField('path');
        }
        if($info['license']){
            $info['license_img_path'] = M('Picture')->where(array('id'=>$info['license']))->getField('path');
        }
        
        $info['supplier_type_name'] = M('supplier_type')->where(array('id'=>$info['supplier_type']))->getField('name');
        $info['username'] = M('UcenterMember')->where(array('id'=>$info['uid']))->getField('username');
        $info['cityname'] = cityName($info['region_id']);
        
        return $info;
    }

    //店铺认证
    function Verify($uid){
        $userInfo = User($uid,array('money','is_supplier'),true);
        
        if($userInfo['is_supplier'] != 1){
            $this->error = '您不是商家!';
            return false;
        }else{
            $Store = M('Store')->where(array('uid'=>$uid))->find();
            if(empty($Store)){
                $this->error = '店铺不存在!或还未完善店铺资料';
                return false;
            }elseif($Store['status'] == -1){
                $this->error = '店铺已关闭!';
                return false;
            }elseif($Store['status'] == 2){
                $this->error = '店铺审核未通过!';
                return false;
            }
            
            elseif($Store['is_privilege'] == 1){
                $this->error = '店铺已认证!';
                return false;
            }elseif($Store['accountlog_id'] && !$Store['is_privilege']){
                $this->error = '店铺认证费用已支付!请等待管理员审核';
                return false;
            }
        }
        
        //认证费用
        $money = C('STOREVERIFYFEE');
        
        if($userInfo['money'] < $money){
            $this->error = '您的现金不足!';
            return false;
        }
        $Result = D('AccountLog')->addlog($uid, $money, 7);
        if(!$Result){
            D('AccountLog')->getError();
            return false;
        }else{
            return true;
        }
    }
    
    //审核认证
    function checkVerify($StoreId='', $status='',$data=''){
        $StoreId = intval($StoreId);
        if(!$StoreId){
            $this->error = '参数有误!';
            return false;
        }
        
        $Store = $this->where(array('id'=>$StoreId))->find();
        if(empty($Store)){
            $this->error = '店铺不存在!或还未完善店铺资料.';
            return false;
        }
        
        if($Store['status'] && $status==0){
            $this->error = '店铺已审核!';
            return false;
        }
        
        if(intval($status) == 1 && $Store['accountlog_id']){
            $save['is_privilege'] = 1;
            $save['ver_time'] = time();
        }
        
        $save['status'] = intval($status);
        
        if($this->where(array('id'=>$StoreId))->save($save) !== false){
            // 若通过，修改申请人is_supplier字段状态值
            if ($status == 1){
                M('ucenter_member')->where(array('id'=>$Store['uid']))->setField('is_supplier',1);
            }
            CleanStore(array('id'=>$Store['id']));
            return true;
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }

}
