<?php

/**
 * 城市
 * 开发者: 
 * 创建日期: 16-4-7
 */
namespace Common\Model;

use Think\Model;

class DistrictModel extends Model {
    
    //已开通城市列表
    function cityList($defaultCity=0){
        $map['status'] = 1;
        $map['id'] = array('neq',$defaultCity);
        return $this->where($map)->field('id,title')->select();
    }
    
    //地区下的所有店铺
    function Store($Region_id=''){
        $Region_id = intval($Region_id);
        if(!$Region_id){
            $this->error = '参数有误!';
            return false;
        }
        $map['status'] = 1;
        $map['region_id'] = $Region_id;
        $_list = M('Store')->where($map)->select();
        
        return $_list;
    }
    
    //地区下的所有用户
    function User($Region_id=''){
        $Region_id = intval($Region_id);
        if(!$Region_id){
            $this->error = '参数有误!';
            return false;
        }
        
        $map['status'] = 1;
        $map['region_id'] = $Region_id;
        $_list = M('UcenterMember')->where($map)->select();
        return $_list;
    }
    
    //编辑地区
    function Update($data=array()){
        if(empty($data)){
            $this->error = '参数有误!';
            return false;
        }
        
        //编辑
        if(isset($data['id'])){
            $id = intval($data['id']);
            if($id == C('DEFAULTCITY')){
                $this->error = '默认地区禁止编辑!';
                return false;
            }
            
            $info = $this->where(array('id'=>$id))->find();
            if(empty($info)){
                $this->error = '地区不存在!';
                return false;
            }
            
            if($info['status'] == 1 && $data['status'] == 0){
                if(D('Agents')->cityAgentsid($info['id']) != C('DEFAULTAGENTS')){
                    $this->error = '该地区的代理商还未关闭,请先禁用代理商!';
                    return false;
                }
            }

            if($info['title'] != $data['title'] && $this->where(array('title'=>$data['title']))->count()){
                $this->error = '地区已存在!';
                return false;
            }
            
            if($this->where(array('id'=>$info['id']))->save($data) !== false){
                S('CityName_'.$info['id'], NULL);
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }
        
        //添加
        else{
            if($this->where(array('title'=>$data['title']))->count()){
                $this->error = '地区已存在!';
                return false;
            }
            
            if($this->add($data)){
                return true;
            }else{
                $this->error = $this->getDbError();
                return false;
            }
        }
    }
    
    //地区名称
    function getCityName($regionID){
        $regionID = intval($regionID);
        $Key = 'CityName_'.$regionID;
        $Name = S($Key);
        if(empty($Name)){
            $Name = $this->where(array('id'=>$regionID))->getField('title');
            if($Name){
                S($Key, $Name);
            }
        }
        return $Name;
    }
    
    
}