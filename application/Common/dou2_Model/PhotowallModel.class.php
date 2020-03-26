<?php

//照片墙
namespace Common\Model;
use Think\Model;

class PhotowallModel extends Model {
    
    protected $_validate = array(
        array('uid', 'require', '用户ID不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
    );
    
    protected $_auto = array(
        array('addtime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 动态同步图片到照片墙
     * @param int $uid
     * @param $pic_ids
     * @return bool
     */
    public function show_sync($uid = 0,$pic_ids)
    {
        if (!$uid) return false;
        if (!$pic_ids) return false;
        if(!is_array($pic_ids)){
            $pic_ids = array($pic_ids);
        }
        $max_pici = $this->getPici($uid);
        $max_pici++;
        $now_time = time();
        $data = array();
        foreach ($pic_ids as $id){
            // 获取图片path
            $path = M('picture')->where(array('id'=>$id))->getField('path');
            if ($path){
                $data[] = array(
                    'uid'   =>  $uid,
                    'pid'   =>  $id,
                    'groups'    =>  $max_pici,
                    'path'  =>  $path,
                    'type'  =>  1,
                    'status'    =>  1,
                    'addtime'   =>  $now_time
                );
            }
        }
        if ($data){
            M('photowall')->addAll($data);
        }
        return true;
    }
    
    
    //添加
    //$data['uid'] 用户ID
    //$data['type'] 类型 1:照片 2:视频
    //$data['data'] 照片or视频数据 base64
    public function Insert($data=array()){
        $data['uid'] = intval($data['uid']);
        
        $videoData = $data['data'];
        
        //保存文件 (返回表Picture的ID)
        $data['pid'] = D('Core/File')->Savebase64img($videoData, $data['uid'],'PHOTOWALL_UPLOAD');
        if(!$data['pid']){
            $this->error = D('Core/File')->getError();
            return false;
        }
        
        $data['path'] = M('Picture')->where(array('id'=>$data['pid']))->getField('path');
        $data['type'] = intval($data['type']);
        
        $data = $this->create($data,1);
        if(!$data){
            $this->error = $this->getError();
            return false;
        }
        
        $id = $this->add($data);
        if($id && is_numeric($id)){
            return $id;
        }else{
            $this->error = $this->getError();
            return false;
        }
    }
    
    //删除(支持批量)
    public function Del($ids=array()){
        
        if(stripos($ids,',') !== false){
            $ids = explode(',', $ids);
        }else{
            $ids = (array)$ids;
        }
        
        if(empty($ids)){
            $this->error = '参数错误!';
            return false;
        }
        
        if($this->where(array('id'=>array('in',$ids)))->setField('status',0) !== false){
            return true;
        }else{
            $this->error = $this->getDbError();
            return false;
        }
    }
    
    //获取最大上传批次
    function getPici($uid=''){
        $map['uid'] = intval($uid);
        if(!$map['uid']){
            $this->error = '参数有误!';
            return false;
        }
        $map['status'] = 1;
        
        $max = $this->where($map)->max('groups');
        return $max?$max:1;
    }
    
}