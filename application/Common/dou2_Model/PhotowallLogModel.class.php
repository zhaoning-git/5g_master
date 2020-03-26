<?php

//照片墙喜欢不喜欢
namespace Common\Model;
use Think\Model;

class PhotowallLogModel extends Model {
    
    protected $_validate = array(
        array('uid', 'require', '用户ID不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
        array('photowall_id', 'require', '照片墙ID不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
        array('pid', 'require', '照片ID不能为空',self::MUST_VALIDATE,'regex', self::MODEL_INSERT),
    );
    
    protected $_auto = array(
        array('addtime', NOW_TIME, self::MODEL_INSERT),
    );

    
    //添加 or 设置
    //$data['uid'] 用户ID
    //$data['photowall_id'] 照片墙ID
    //$data['pid'] 照片ID
    //$data['like'] 喜欢or不喜欢 0:不喜欢  1:喜欢
    public function Insert($data=array()){
        $data = $this->create($data, 1);
        if(!$data){
            return false;
        }
        
        $data['like'] = intval($data['like']);
        
        $map['uid'] = $data['uid'];
        $map['pid'] = $data['pid'];
        $map['photowall_id'] = $data['photowall_id'];
        
        $info = $this->where($map)->find();
        
        if(empty($info)){
            if(!$this->add($data)){
                $this->error = $this->getDbError();
                return false;
            }else{
                return true;
            }
        }else{
            $this->error = '您已设置过该照片墙';
            return false;
        }
        
        /*
        if(!empty($info)){
            if(intval($info['like']) != $data['like']){
                $save['uptime'] = time();
                $save['like'] = $data['like'];
                if($this->where(array('id'=>$info['id']))->save($save)){
                    $this->error = $this->getDbError();
                    return false;
                }
            }
        }else{
            if(!$this->add($data)){
                $this->error = $this->getDbError();
                return false;
            }
        }
        
        return true;
        */
    }    
    
    /**
     * 获取喜欢和不喜欢的数量
     * @param $goods_id
     * @param $pid
     * @return int
     */
    public function getLikeNum($photowall_id, $pid, $type)
    {
        if (!$photowall_id || !$pid){
            return 0;
        }
        
        $map['pid'] = $pid;
        $map['photowall_id'] = $photowall_id;
        if ($type){
            $map['like'] = 1;
        } else {
            $map['like'] = 0;
        }
        return $this->where($map)->count();
    }
    
    
}
