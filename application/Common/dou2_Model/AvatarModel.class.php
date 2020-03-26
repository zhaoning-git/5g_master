<?php

/**
 * 头像处理
 * 开发者: 
 * 创建日期: 16-4-7
 */

namespace Common\Model;

use Think\Model;

require_once('ThinkPHP/Library/Vendor/PHPImageWorkshop/ImageWorkshop.php');

use PHPImageWorkshop\Core\ImageWorkshopLayer;
use PHPImageWorkshop\ImageWorkshop;

class AvatarModel extends Model {

    protected $_auto = array(array('create_time', NOW_TIME, self::MODEL_INSERT));

    //读取头像
    public function getAvatar($uid = 0, $size = 256) {

        $avatar = M('Avatar')->where(array('uid' => $uid))->getField('headimgurl');

        if ($avatar) {
            if (strstr($avatar, 'http://') || strstr($avatar, 'https://')) {
                return $avatar;
            } else {
                if (filesize('./Uploads/Avatar' . $avatar) <= 0) {
                    //文件不存在,或大小为0,则返回默认头像
                    return '/Public/img/default_avatar_thumb_'.$size.'_'.$size.'.jpg';
                }
                if ($size) {
                    return $this->getImageUrlByPath('./Uploads/Avatar' . $avatar, $uid, $size);
                } else {
                    return '/Uploads/Avatar' . $avatar; //返回头像原图
                }
            }
        } else {
            return $this->getImageUrlByPath('./Public/img/default_avatar.jpg', $uid, $size);
        }
    }

    //头像剪裁
    private function getImageUrlByPath($path, $uid, $size) {
        if (S('AvatarCache_' . $uid . $size)){
            return S('AvatarCache_' . $uid . $size);
        }
            
        //TODO 重新开启缩略
        $thumb = getThumbImage($path, $size, $size, 0, true);
        //$path = Host() . getRootUrl() . $thumb['src'];
        $path = $thumb['src'];
        
        if(substr($path, 0, 1) == '.'){
            $path = substr($path,1,strlen($path));
        }
        
        S('AvatarCache_' . $uid . $size, $path);
        return $path;
    }

    //将头像保存到本地
    public function saveAvatar($uid, $url) {
        mkdir('./Uploads/Avatar/' . $uid, 0777, true);
        $img = curl_file_get_contents($url);
        $filename = './Uploads/Avatar/' . $uid . '/crop.jpg';
        file_put_contents($filename, $img);
        $data['headimgurl'] = '/' . $uid . '/crop.jpg';
        $data['uid'] = $uid;
        $data['create_time'] = time();
        $data['status'] = 1;

        $ret = $this->add($data);

        return $ret;
    }

}
