<?php



// +----------------------------------------------------------------------

// | OneThink [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------

// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.

// +----------------------------------------------------------------------

// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>

// +----------------------------------------------------------------------



namespace Common\Model;



use Think\Model;

use Think\Upload;



/**

 * 文件模型

 * 负责文件的下载和上传

 */

class FileModel extends Model {



    /**

     * 文件模型自动完成

     * @var array

     */

    protected $_auto = array(

        array('create_time', NOW_TIME, self::MODEL_INSERT),

    );



    /**

     * 文件模型字段映射

     * @var array

     */

    protected $_map = array(

        'type' => 'mime',

    );



    /**

     * 文件上传

     * @param  array  $files   要上传的文件列表（通常是$_FILES数组）

     * @param  array  $setting 文件上传配置

     * @param  string $driver  上传驱动名称

     * @param  array  $config  上传驱动配置

     * @return array           文件上传成功后的信息

     */

    public function upload($files, $setting, $driver = 'Local', $config = null) {



        /* 上传文件 */

        $setting['callback'] = array($this, 'isFile');

        $setting['removeTrash'] = array($this, 'removeTrash');

        $Upload = new Upload($setting, $driver, $config);

        $info = $Upload->upload($files);

        /* 设置文件保存位置 */

        $this->_auto[] = array('location', 'ftp' === strtolower($driver) ? 1 : 0, self::MODEL_INSERT);



        if ($info) { //文件上传成功，记录文件信息

            foreach ($info as $key => &$value) {

                /* 已经存在文件记录 */

                if (isset($value['id']) && is_numeric($value['id'])) {

                    $value['path'] = substr($setting['rootPath'], 1) . $value['savepath'] . $value['savename']; //在模板里的url路径

                    continue;

                }



                $value['path'] = substr($setting['rootPath'], 1) . $value['savepath'] . $value['savename']; //在模板里的url路径

                /* 记录文件信息 */

                $value['uid'] = is_login();

                $value['store_id'] = Store();

                if ($this->create($value) && ($id = $this->add())) {

                    $value['id'] = $id;

                } else {

                    //TODO: 文件上传成功，但是记录文件信息失败，需记录日志

                    unset($info[$key]);

                }

            }

            return $info; //文件上传成功

        } else {

            $this->error = $Upload->getError();

            return false;

        }

    }



    /**

     * 检测当前上传的文件是否已经存在

     * @param  array   $file 文件上传数组

     * @return boolean       文件信息， false - 不存在该文件

     */

    public function isFile($file) {

        if (empty($file['md5'])) {

            throw new \Exception('缺少参数:md5');

        }

        /* 查找文件 */

        $map = array('md5' => $file['md5'], 'sha1' => $file['sha1'],);

        return $this->field(true)->where($map)->find();

    }



    /**

     * 下载本地文件

     * @param  array    $file     文件信息数组

     * @param  callable $callback 下载回调函数，一般用于增加下载次数

     * @param  string   $args     回调函数参数

     * @return boolean            下载失败返回false

     */

    private function downLocalFile($file, $callback = null, $args = null) {

        if (is_file($file['rootpath'] . $file['savepath'] . $file['savename'])) {

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

            readfile($file['rootpath'] . $file['savepath'] . $file['savename']);

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

    public function removeTrash($data) {

        $this->where(array('id' => $data['id'],))->delete();

    }



    //base64上传图片时获取文件类型和图片文件主体数据

    public function Base64Type($string){

        $arr = explode(',',$string);

        $imgString = $arr[1];

        $types = explode(';', $arr[0]);

        

        switch ($types[0]){

            case 'data:image/gif':

                $type = 'gif';

                break;

            case 'data:image/png':

                $type = 'png';

                break;

            case 'data:image/jpeg':

                $type = 'jpg';

                break;

            case 'data:image/jpg':

                $type = 'jpg';

                break;

            case 'data:image/x-icon':

                $type = 'icon';

                break;

            case 'data:video/x-flv':

                $type = 'flv';

                break;

            case 'data:video/mp4':

                $type = 'mp4';

                break;

            case 'data:video/ogg':

                $type = 'ogv';

                break;

            case 'data:video/webm':

                $type = 'webm';

                break;

            case 'data:video/MP2T':

                $type = 'ts';

                break;

            case 'data:video/3gpp':

                $type = '3gp';

                break;

            case 'data:video/quicktime':

                $type = 'mov';

                break;

            case 'data:video/x-msvideo':

                $type = 'avi';

                break;

            case 'data:video/x-ms-wmv':

                $type = 'wmv';

                break;

            

            default :

                $this->error = '不支持的文件类型!';

                return false;

        }

        

        return array('type'=>$type,'data'=>$imgString);

    }



    //base64上传图片的保存

    //$data 图片BASE64数据流

    public function Savebase64img($data, $uid, $pic_driver='AVATAR_UPLOAD') {
        $data = $this->Base64Type($data);
        if(!$data){
            return false;
        }

        $string = $data['data'];
        $type = $data['type'];
        if (strlen($string) <= 0) {
            $this->error = '文件数据错误!';
            return false;
        }

        $pic_driver = C($pic_driver);
        $filecontent = base64_decode($string);

        if (strlen($filecontent) > $pic_driver['maxSize']) {
            $this->error = '上传文件大小超过限制!';
            return false;
        }

        //检查文件是否存在
        $map = array('md5' => md5($filecontent), 'sha1' => sha1($filecontent));
        $Picture = M('Picture')->field(true)->where($map)->find();
        if(!empty($Picture)){
            if (file_exists('.' . $Picture['path'])) {//本地存在该文件直接返回
                return intval($Picture['id']);
            } else{ //本地不存在该文件,则删除数据库记录
                M('Picture')->where(array('id' => $Picture['id'],))->delete();
            }
        }

        $exts = is_array($pic_driver['exts']) ? $pic_driver['exts'] : explode(',', $pic_driver['exts']);
        if (!in_array($type, $exts)) {
            $this->error = '未允许的上传文件类型!';
            return false;
        }

        $name = uniqid().date('md');
        $filename = $name. '.' . $type;

        //保存目录
        $savePath = empty($pic_driver['savePath']) ? $uid : $pic_driver['savePath'];
        $path = $pic_driver['rootPath'] . $savePath . '/';
        if ($this->mkdir($path) === true) {
            if (!is_writable($path)) {
                $this->error = '上传目录 ' . $savePath . ' 不可写！';
                return false;
            } else {
                $pathfile = $path . $filename;
                if (file_put_contents($pathfile, $filecontent)) {
                    //保存到表Picture
                    $data['uid'] = $uid;
                    $data['name'] = $filename;
                    $data['path'] = substr($pathfile, 1);
                    $data['md5'] = md5($filecontent);
                    $data['sha1'] = sha1($filecontent);
                    $data['status'] = 1;
                    $data['create_time'] = time();
                    $pid = M('Picture')->add($data);
                    if ($pid > 0){
                        // 上传成功 - 如为视频，生成缩略图
                        if (!in_array($type,array('gif','png','jpg','icon'))){
                            $video_img_name = $name.'.jpg';
                            $video_img_path = dirname(realpath($pathfile)).'/'.$video_img_name;
                            $this->getVideoCover(realpath($pathfile),1,$video_img_path);
                            if (file_exists($video_img_path)){
                                // 存数据表
                                M('picture')->where(array('id'=>$pid))->save(array('video_img'=>dirname($pathfile).'/'.$video_img_name));
                            }
                        }
                        return $pid;
                    } else {
                        return M('Picture')->getError();
                    }
                } else {
                    $this->error = '写入文件失败';
                    return false;
                }
            }

        } else {

            $this->error = '目录'.$savePath.'创建失败！';

            return false;

        }

    }



    private function mkdir($dir) {

        if (is_dir($dir)) {

            return true;

        }



        if (mkdir($dir, 0777, true)) {

            return true;

        } else {

            return false;

        }

    }



    /**

     * 生成视频缩略图

     * @param $file

     * @param int $time

     * @param string $name

     */

    private function getVideoCover($file,$time = 1,$name = '') {

//        $str = "ffmpeg -i ".$file." -y -f mjpeg -ss 3 -t ".$time." -s 320x240 ".$name;

        $str = "/monchickey/ffmpeg/bin/ffmpeg -i ".$file." -y -f mjpeg -ss 1 -t ".$time." ".$name;

//        $str = "touch $name";

//        file_put_contents('./debug.log',$str."\r\n",FILE_APPEND);

        $result = system($str);

    }

    

    

    

    

}

