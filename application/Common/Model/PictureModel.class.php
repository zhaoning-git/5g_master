<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Core\Model;
use Think\Model;
use Think\Upload;

/**
 * 图片模型
 * 负责图片的上传
 */

class PictureModel extends Model{
    /**
     * 自动完成
     * @var array
     */
    protected $_auto = array(
        array('status', 1, self::MODEL_INSERT),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 文件上传
     * @param  array  $files   要上传的文件列表（通常是$_FILES数组）
     * @param  array  $setting 文件上传配置
     * @param  string $driver  上传驱动名称
     * @param  array  $config  上传驱动配置
     * @return array           文件上传成功后的信息
     */
    public function upload($files, $setting, $driver = 'Local', $config = null){
        /* 上传文件 */
        $setting['callback'] = array($this, 'isFile');
		$setting['removeTrash'] = array($this, 'removeTrash');
        $Upload = new Upload($setting, $driver, $config);

        foreach ($files as $key => $file) {
            $ext = strtolower($file['ext']);
            if(in_array($ext, array('jpg','jpeg','bmp','png'))){
                hook('dealPicture',$file['tmp_name']);
            }
        }

        $info   = $Upload->upload($files);

        if($info){ //文件上传成功，记录文件信息
            foreach ($info as $key => &$value) {
                /* 已经存在文件记录 */
                if(isset($value['id']) && is_numeric($value['id'])){
                    continue;
                }

                /* 记录文件信息 */
                if(strtolower($driver)=='sae'){
                    $value['path'] = $config['rootPath'].'Picture/'.$value['savepath'].$value['savename']; //在模板里的url路径
                }else{
                    if(strtolower($driver) != 'local'){
                        $value['path'] =$value['url'];
                    }
                    else{
                        $value['path'] = (substr($setting['rootPath'], 1).$value['savepath'].$value['savename']);	//在模板里的url路径
                    }

                }

                $value['type'] = strtolower($driver);
				$value['uid'] = is_login();
                if($this->create($value) && ($id = $this->add())){
                    $value['id'] = $id; 
                } else {
                    //TODO: 文件上传成功，但是记录文件信息失败，需记录日志
                    unset($info[$key]);
                }
            }

            foreach($info as &$t_info){
                if($t_info['type'] =='local'){
                    $t_info['path']=fixAttachUrl($t_info['path']);
                }
                else{
                    $t_info['path']=$t_info['path'];
                }


            }
          /*  dump(getRootUrl());
            dump($info);
            exit;*/

            return $info; //文件上传成功
        } else {
            $this->error = $Upload->getError();
            return false;
        }
    }

    /**
     * 下载指定文件
     * @param  number  $root 文件存储根目录
     * @param  integer $id   文件ID
     * @param  string   $args     回调函数参数
     * @return boolean       false-下载失败，否则输出下载文件
     */
    public function download($root, $id, $callback = null, $args = null){
        /* 获取下载文件信息 */
        $file = $this->find($id);
        if(!$file){
            $this->error = '不存在该文件！';
            return false;
        }

        /* 下载文件 */
        switch ($file['location']) {
            case 0: //下载本地文件
                $file['rootpath'] = $root;
                return $this->downLocalFile($file, $callback, $args);
            case 1: //TODO: 下载远程FTP文件
                break;
            default:
                $this->error = '不支持的文件存储类型！';
                return false;

        }

    }

    /**
     * 检测当前上传的文件是否已经存在
     * @param  array   $file 文件上传数组
     * @return boolean       文件信息， false - 不存在该文件
     */
    public function isFile($file){
        if(empty($file['md5'])){
            throw new \Exception('缺少参数:md5');
        }
        /* 查找文件 */
		$map = array('md5' => $file['md5'],'sha1'=>$file['sha1'],);
        return $this->field(true)->where($map)->find();
    }

    /**
     * 下载本地文件
     * @param  array    $file     文件信息数组
     * @param  callable $callback 下载回调函数，一般用于增加下载次数
     * @param  string   $args     回调函数参数
     * @return boolean            下载失败返回false
     */
    private function downLocalFile($file, $callback = null, $args = null){
        if(is_file($file['rootpath'].$file['savepath'].$file['savename'])){
            /* 调用回调函数新增下载数 */
            is_callable($callback) && call_user_func($callback, $args);

            /* 执行下载 */ //TODO: 大文件断点续传
            header("Content-Description: File Transfer");
            header('Content-type: ' . $file['type']);
            header('Content-Length:' . $file['size']);
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            }
            readfile($file['rootpath'].$file['savepath'].$file['savename']);
            exit;
        } else {
            $this->error = '文件已被删除！';
            return false;
        }
    }

	/**
	 * 清除数据库存在但本地不存在的数据
	 * @param $data
	 */
	public function removeTrash($data){
		$this->where(array('id'=>$data['id'],))->delete();
	}


    
	/**
	 * 远程下载远程图片文件到本地并入库
	 * @param $data
	 */
		public function GetPicure($url, $backurl = false){
	
			$dir = './Uploads/Picture/' . date('Y-m-d'); 
			$dir2 = '/Uploads/Picture/' . date('Y-m-d');
			$dir3 = 'Uploads/Picture/' . date('Y-m-d'); 
			
			if(!file_exists($dir2)){
				mkdir($dir, 0777, true);
			}
			
			$FileName = time().'_'.rand(1,9999).'.jpg';
				
			$Path = $dir.'/'.$FileName;
			$Path2 = $dir2.'/'.$FileName;
			$Path3 = $dir3.'/'.$FileName;

			$img = file_get_contents($url);
            
			$is_have = $this->isFile(array('md5'=>md5($img),'sha1'=>sha1($img)));
			
			if($is_have){
				return $backurl?array('id'=>$is_have['id'],'path'=>$is_have['path']):$is_have['id'];
			}

			if(!file_put_contents($Path3, $img)){
				$this->error = '抓取远程文件失败';
				return false;
			}
			
			$save['uid'] = 0;
			$save['name'] = md5($img).'.jpg';
			$save['type'] = 'local';
			$save['path'] = $Path2;
			$save['md5'] = md5($img);
			$save['sha1'] = sha1($img);
			$save['status'] = 1;
			$save['create_time'] = time();
		
	
			$ret = M('Picture')->add($save);
			if($ret!==false){
				return $backurl?array('id'=>$ret,'path'=>$save['path']):$ret;
			}else{
				$this->error = '文件入库失败';
				return false;
			}
	}

}
