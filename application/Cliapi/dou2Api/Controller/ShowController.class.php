<?php

/**
 * Date: 17-08-08
 * Time: 下午5:04
 */

namespace Api\Controller;

use Think\Controller;

class ShowController extends MemberController {

    function _initialize() {
        parent::_initialize();
    }

    //个人动态列表
    function index() {
        $map['uid'] = I('uid', 0, 'intval')? : $this->uid;;
        $map['status'] = 1;

        $_list = $this->lists('Show', $map, 'is_top desc,top_time desc,addtime DESC');
        
        $_list = D('Show')->Showlist($_list);
        
        if (empty($_list)) {
            $_list = array();
            $this->_totalPages = 0;
        }
        
        $data['showCount'] = M('Show')->where($map)->count();
        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //发布个人动态
    function addShow() {
        $data = I('post.');
        $data['uid'] = $this->uid;

        if (D('Show')->Insert($data) === true) {
            //同步到照片墙
            if ($data['sync_photowall'] == 1 && $data['img']) {
                $img_ids = explode(',', trim($data['img']));
                D('Photowall')->show_sync($this->uid, $img_ids);
            }
            
            //同步到商品
            if($data['sync_goods'] == 1 && $data['img']){
                $Goods['uid'] = $data['uid'];
                
                //商品描述
                $Goods['describe'] = $data['content'];
                
                //商品图片 多个用,分割,和商品描述不能同时为空
                $Goods['imgs'] = $data['img'];
                
                $Goods['is_show'] = 0;
                $goods_id = D('Goods')->addGoods($Goods);
            }
            
            $this->ajaxRet(array('status' => 1, 'info' => '个人动态发布成功'));
        } else {
            $this->ajaxRet(array('info' => D('Show')->getError()));
        }
    }

    //转发动态
    //$data['old_id'] 原动态ID
    //$data['zcontent'] 内容
    function zfShow() {
        $data = I('post.');
        $data['uid'] = $this->uid;

        if (D('Show')->zInsert($data) === true) {
            $this->ajaxRet(array('status' => 1, 'info' => '个人动态转发成功'));
        } else {
            $this->ajaxRet(array('info' => D('Show')->getError()));
        }
    }

    //获取关注用户或好友的动态
    //关注类型 1:好友 2:关注 
    function getShow() {
        $type = I('post.type', '', 'intval');

        if (!empty($type)) {
            $map['type'] = $type;
        }

        $map['uid'] = $this->uid;

        $show = M('UserRelation')->where($map)->getField('relation_uid', true);
        if (!empty($show)) {
            $show = array_unique($show);
            $_list = $this->lists('Show', array('uid' => array('in', $show), 'is_open' => 1), 'addtime DESC');
            if (!empty($_list)) {
                foreach ($_list as &$value) {
                    $value['user_flower'] = User($value['uid'], 'flower');
                    if (!empty($value['img'])) {
                        //是否点赞
                        $where['show_id'] = $value['id'];
                        $where['uid'] = $this->uid;
                        $where['type'] = 'praise';
                        $is_praise = M('ShowLog')->where($where)->getField('status');
                        $value['is_praise'] = $is_praise? : 0;

                        $imgs = explode(',', $value['img']);
                        $value['imgs'] = M('Picture')->where(array('id' => array('in', $imgs)))->field('id,path')->select();
                    }
                }

                $mydistance = User($this->uid, array('lng', 'lat'));
                $data['_list'] = D('Show')->Showlist($_list, $mydistance);
                $data['_totalPages'] = $this->_totalPages; //总页数
                $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
            }
        }

        $this->ajaxRet(array('info' => '数据不存在'));
    }

    /**
     * 取消收藏
     */
    public function unset_favor() {
        $data = I('post.');
        if (!empty($data['id'])) {
            $this->ajaxRet(array('info' => '参数错误'));
        }
        $map['uid'] = $this->uid;
        $map['show_id'] = $data['id'];
        $map['type'] = 'favor';
        $res = M('show_log')->where($map)->delete();
        if ($res === false) {
            $this->ajaxRet(array('info' => '操作失败'));
        } else {
            $this->ajaxRet(array('status' => 1, 'info' => '成功'));
        }
    }

    //点赞or送花
    //$data['id'] Show表ID(个人动态ID)
    //$data['field'] praise:点赞 read:阅读 flower:送花
    function setShow() {
        $data = I('post.');

        if (D('Show')->setShow($data['id'], $this->uid, $data['field']) === true) {
            switch ($data['field']) {
                case 'praise':
                    $info = '点赞';
                    break;
                case 'cons':
                    $info = '踩';
                    break;
                case 'read':
                    $info = '阅读';
                    break;
                case 'flower':
                    $info = '送花';
                    break;
                case 'favor':
                    $info = '收藏';
                    break;
                case 'dislike':
                    $info = '不感兴趣';
                    break;
                case '':
                    $info = '投诉';
                    break;
                default :
                    $info = '';
            }

            $this->ajaxRet(array('status' => 1, 'info' => $info . '成功'));
        } else {
            $this->ajaxRet(array('info' => D('Show')->getError()));
        }
    }

    //收藏列表
    function favor() {
        $map['uid'] = $this->uid;
        $map['type'] = 'favor';

        $ids = M('ShowLog')->where($map)->getField('show_id', true);

        if (empty($ids)) {
            $this->ajaxRet(array('info' => '您还没收藏动态!'));
        }

        $where['id'] = array('in', $ids);
        $where['is_open'] = 1;

        $_list = $this->lists('Show', $where, 'addtime DESC');
        
        $_list = D('Show')->Showlist($_list);
        if (empty($_list)) {
            $_list = array();
            $this->_totalPages = 0;
        }
        
        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
        exit;
        if (!empty($_list)) {
            foreach ($_list as &$value) {
                $value['user_flower'] = User($value['uid'], 'flower');
                if (!empty($value['img'])) {
                    //是否点赞
                    $where['show_id'] = $value['id'];
                    $where['uid'] = $this->uid;
                    $where['type'] = 'praise';
                    $is_praise = M('ShowLog')->where($where)->getField('status');
                    $value['is_praise'] = $is_praise? : 0;

                    $imgs = explode(',', $value['img']);
                    $value['imgs'] = M('Picture')->where(array('id' => array('in', $imgs)))->field('id,path')->select();
                    // 获取用户名和头像
                    $member = M('ucenter_member')
                            ->alias('um')
                            ->field('um.username,m.nickname')
                            ->join('__MEMBER__ m ON m.uid = um.id')
                            ->where(array('um.id' => $value['uid']))
                            ->find();
                    if ($member) {
                        $value['username'] = $member['username'];
                        $value['nickname'] = $member['nickname'];
                    } else {
                        $value['username'] = '';
                        $value['nickname'] = '';
                    }
                    $avatar = M('avatar')->where(array('uid' => $value['uid']))->getField('headimgurl');
                    if ($avatar) {
                        $value['avatar'] = '/Uploads/Avatar' . $avatar;
                    } else {
                        $value['avatar'] = '';
                    }
                }
            }
        }



        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //被收藏
    function befavor(){
        $uid = I('uid', 0, 'intval')?:$this->uid;
        $map['status'] = 1;
        $map['type'] = 'favor';
        $map['show_uid'] = $uid;
        $ids = M('ShowLog')->where($map)->getField('show_id',true);

        $_map['status'] = 1;
        $_map['uid'] = $uid;
        
        $_list = $this->lists('Show', $_map, 'is_top desc,top_time desc,addtime DESC');
        $_list = D('Show')->Showlist($_list);
        if (empty($_list)) {
            $_list = array();
            $this->_totalPages = 0;
        }
        
        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }
    
    
    /**
     * 设置动态置顶
     */
    public function set_top() {
        $id = I('id', 0, 'intval');
        $is_top = I('is_top', 0, 'intval'); //0取消置顶 1置顶
        if (!$id)
            $this->ajaxRet(array('info' => '设置失败!'));
        if (!D('Show')->setTop($this->uid, $id, $is_top)) {
            $this->ajaxRet(array('info' => '设置失败!'));
        } else {
            $this->ajaxRet(array('status' => 1, 'info' => '成功'));
        }
    }

    /**
     * 删除动态
     */
    public function del_top() {
        $id = I('id', 0, 'intval');
        if (!$id)
            $this->ajaxRet(array('info' => '设置失败!'));
        if (!D('Show')->delTop($this->uid, $id)) {
            $this->ajaxRet(array('info' => '设置失败!'));
        } else {
            $this->ajaxRet(array('status' => 1, 'info' => '成功'));
        }
    }

    //谁看过我
    public function lookme() {
        //类型:1:主页 2:动态 3:照片墙
        $map['type'] = I('type', 1, 'intval');

        $map['uid'] = $this->uid;

        $model = M('Lookme')->group('look_uid');
        $list = $this->lists($model, $map, 'num DESC', array(), 'uid,look_uid,count(id) as num');

        if (!empty($list)) {
            foreach ($list as $value) {
                $_data = User($value['look_uid']);
                $_data['hx_username'] = md5($value['look_uid']);
                $_Result[] = $_data;
            }
        }

        $data['_list'] = $_Result;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
    }

    //设置谁看过我
    public function setLookme() {
        $post = I('post.');
        $data['uid'] = intval($post['uid']);
        $data['look_uid'] = $this->uid;
        $data['type'] = intval($post['type']);
        $data['addtime'] = time();

        if (!$data['uid'] || !$data['type']) {
            $this->ajaxRet(array('info' => '参数有误!'));
        }

        if (M('Lookme')->add($data)) {
            $this->ajaxRet(array('status' => 1, 'info' => '设置成功'));
        } else {
            $this->ajaxRet(array('info' => M('Lookme')->getDbError()));
        }
    }

    //网红值
    public function Celebrity() {
        $uid = I('uid', 0, 'intval')? : $this->uid;

        $data = D('Show')->Celebrity($uid);

        //超越代码开始
        $userInfo = User($uid);

        $map['id'] = array('neq', $uid);
        $map['region_id'] = $userInfo['region_id'];
        if ($userInfo['is_supplier'] == 1) {
            $map['is_supplier'] = 1;
            $data['typeName'] = '商户';
        } else {
            $data['typeName'] = '用户';
        }

        //地区名称
        $data['regionName'] = $userInfo['cityname'];

        //总数
        $all = M('UcenterMember')->where($map)->count();

        //超越的数量
        $map['celebrity'] = array('LT', $userInfo['celebrity']);
        $surpass = M('UcenterMember')->where($map)->count();

        $data['surpass'] = round($surpass / $all, 2) * 100;
        $this->ajaxRet(array('status' => 1, 'info' => '成功', 'data' => $data));
    }

    //谁@过我
    public function Atme(){
        $uid = I('uid', 0, 'intval')? : $this->uid;
        $map['uid'] = $uid;
        $map['type'] = 'at';
        $ids = M('ShowLog')->where($map)->getField('show_id',true);
        
        $_list = $this->lists('Show', array('id'=>array('in', $ids)), 'is_top desc,top_time desc,addtime DESC');
        
        $_list = D('Show')->Showlist($_list);
        
        if (empty($_list)) {
            $_list = array();
            $this->_totalPages = 0;
        }
        
        $data['count'] = count($ids);
        $data['_list'] = $_list;
        $data['_totalPages'] = $this->_totalPages; //总页数
        $this->ajaxRet(array('status' => 1, 'info' => '获取成功', 'data' => $data));
        
    }
    
    
    
}
