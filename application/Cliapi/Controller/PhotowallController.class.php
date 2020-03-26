<?php

/**
 * Date: 17-10-24
 * Time: 上午10:29
 */
namespace Api\Controller;

use Think\Controller;

class PhotowallController extends MemberController {
    private $totalPages;
    
    function _initialize() {
        parent::_initialize();
    }
    
    //照片墙列表
    function index(){
        $uid = I('post.uid', 0, 'intval')?:$this->uid;
        // 商家则显示商品墙
        $member = User($uid);
        if ($member && $member['is_supplier'] == 1){
            // 商家
            $Map['uid'] = $uid;
            $list = $this->lists('Goods', $Map, 'goods_id desc');
            $_list = array();
            foreach ($list as $key => $item){
                // 获取商品图片集
                if ($item['imgs']){
                    $item['imgs'] = trim($item['imgs'],',');
                    $pictures = M('Picture')->where(array('id'=>array('in',$item['imgs'])))->select();
                    if ($pictures){
                        foreach ($pictures as $picture){
                            // 判断喜欢or不喜欢
                            $praise = D('GoodsLog')->checkLike($item['goods_id'],$picture['id'],$this->uid);
                            if ($praise === 1){
                                $praise = 1;
                            } elseif($praise === 0) {
                                $praise = 0;
                            } else {
                                $praise = 2;
                            }
                            // 获取喜欢和不喜欢的数量
                            $praise_num = D('GoodsLog')->getLikeNum($item['goods_id'],$picture['id'],1);
                            $unpraise_num = D('GoodsLog')->getLikeNum($item['goods_id'],$picture['id'],0);
                            $_list[$key][] = array(
                                'goods_id'  =>  $item['goods_id'],
                                'pid'       =>  $picture['id'],
                                'uid'       =>  $item['uid'],
                                'store_id'  =>  $item['store_id'],
                                'describe'  =>  $item['describe'],
                                'praise'    =>  $praise,
                                'praise_num'    =>  $praise_num,
                                'unpraise_num'  =>  $unpraise_num,
                                'path'      =>  $picture['path'],
                                'addtime'   =>  $item['addtime'],
                                'addtime_txt'   =>  date('Y-m-d',$item['addtime'])
                            );
                        }
                    }
                }
            }
            sort($_list);
        }
        
        else {
            $map['uid'] = $uid?:$this->uid;
            $map['status'] = 1;
            $Pici = array_unique(M('Photowall')->where($map)->order('groups ASC')->getField('groups',true));
            if(empty($Pici)){
                $this->ajaxRet(array('info'=>'获取失败!'));
            }

            $Pici = $this->page_array($Pici);

            if(!empty($Pici)){
                foreach ($Pici as $value){
                    $map['groups'] = $value;
                    $photos = $this->checkPhotowall(M('Photowall')->where($map)->order('id desc')->limit(6)->select());
                    if ($photos){
                        foreach ($photos as &$photo){
                            $photo['video_img_path'] = '';
                            if ($photo['type'] == 2){
                                // 根据视频pid获取缩略图路径
                                $video_img = M('picture')->where(array('id'=>$photo['pid']))->getField('video_img');
                                if ($video_img){
                                    $photo['video_img_path'] = $video_img;
                                }
                            }
                            
                            //喜欢or不喜欢
                            $photo['praise_num'] = D('PhotowallLog')->getLikeNum($photo['id'], $photo['pid'], 1);
                            $photo['unpraise_num'] = D('PhotowallLog')->getLikeNum($photo['id'], $photo['pid'], 0);
                            
                            $where['uid'] = $this->uid;
                            $where['pid'] = $photo['pid'];
                            $where['photowall_id'] = $photo['id'];
                            $photo['praise'] = M('PhotowallLog')->where($where)->getField('like');
                            
                        }
                        $_list[] = $photos;
                    } else {
                        $_list[] = array();
                    }
                }
            }
        }
        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages;//总页数
        $this->ajaxRet(array('status'=>1,'info'=>'获取成功','data'=>$data));
    }
    
    
    //数组分页
    private function page_array($Pici){
        //$Pici = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
        
        $page = I('p',1);//当前页
        
        $totalRows = count($Pici);//总条数(总批次数)
        $listRows = 5;//每页条数(每页5个批次)
        
        $totalPages = ceil($totalRows / $listRows);//总页数
        
        $this->totalPages = $totalPages;
        
        $start = ($page-1)*$listRows; #计算每次分页的开始位置 
        
        $pagedata = array_slice($Pici,$start,$listRows);
        return $pagedata;
    }


    private function checkPhotowall($data){
        foreach ($data as &$value){
            $value['addtime_txt'] = date('Y-m-d',$value['addtime']);
        }
        return $data;
    }

    /**
     * 商品墙喜欢or不喜欢
     */
    public function set_like()
    {
        $data = I('post.');
        if(empty($data) || empty($data['ids'])){
            $this->ajaxRet(array('info'=>'数据不能为空!'));
        }
        if (empty($data['goods_id'])){
            $this->ajaxRet(array('info'=>'参数错误!'));
        }
        $data['type'] = empty($data['type']) ? 0 : 1;//取消or喜欢
        if (D('GoodsLog')->setLike($data['goods_id'],$data['ids'],$this->uid,$data['type'])){
            $this->ajaxRet(array('status'=>1,'info'=>'成功'));
        } else {
            $this->ajaxRet(array('info'=>D('GoodsLog')->getError()));
        }
    }

    //添加照片墙
    function addPhoto(){
        $data = json_decode(I('post.data'),true);

        if(empty($data)){
            $this->ajaxRet(array('info'=>'数据不能为空!'));
        }
        
        foreach ($data as $value){
            $value['uid'] = $this->uid;
            $id = D('Photowall')->Insert($value);
            if($id && is_numeric($id)){
                $ids[] = $id;
            }
        }
        
        if(!empty($ids)){
            $map['id'] = array('in',$ids);
            $map['groups'] = 0;
            $Maxpici = D('Photowall')->getPici($this->uid);
            if(M(Photowall)->where($map)->setField('groups',($Maxpici+1))){
                $this->ajaxRet(array('status'=>1,'info'=>'照片墙发布成功'));
            }
        }
        
        $this->ajaxRet(array('info'=>D('Photowall')->getError()));
    }

    /**
     * 删除商品墙
     */
    public function del_goods_pics()
    {
        $ids = I('post.ids');
        $goods_id = I('goods_id',0,'intval');
        $goods = D('Goods')->getOne($goods_id);
        if (!$goods){
            $this->ajaxRet(array('info'=>"参数错误"));
        }
        if ($goods['uid'] == $this->uid){
            $this->ajaxRet(array('info'=>"无权删除"));
        }
        if(D('Goods')->Del($goods_id,$ids) === true){
            $this->ajaxRet(array('status'=>1,'info'=>'商品墙删除成功!'));
        }else{
            $this->ajaxRet(array('info'=>D('Photowall')->getError()));
        }
    }
    
    //删除照片墙
    function del(){
        $ids = I('post.ids');
        if(D('Photowall')->Del($ids) === true){
            $this->ajaxRet(array('status'=>1,'info'=>'成功!'));
        }else{
            $this->ajaxRet(array('info'=>D('Photowall')->getError()));
        }
    }
    
    //照片墙喜欢or不喜欢
    //$data['photowall_id'] 照片墙ID
    //$data['pid'] 照片ID
    //$data['like'] 喜欢or不喜欢 0:不喜欢  1:喜欢
    function setPhotolike(){
        $data = I('post.');
        $data['uid'] = $this->uid;
        if(D('PhotowallLog')->Insert($data)){
            $this->ajaxRet(array('status'=>1,'info'=>'照片墙删除成功!'));
        }else{
            $this->ajaxRet(array('info'=>D('PhotowallLog')->getError()));
        }
    }
    
    
    
}
