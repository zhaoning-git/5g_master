<?php



namespace Common\Model;



use Common\Model\CommonModel;



//评论回复

class CommentModel extends CommonModel {



    /**

     * 添加评论

     */

    public function addComment($show_id, $uid, $content, $ParentId=0, $data_id = 0 ,$PostTable='Show' ) {

        $data['show_id'] = intval($show_id);

        if(!$data['show_id']){

            $this->error = '帖子ID不正确!';

            return false;

        }

        

        $data['show_uid'] = M($PostTable)->where(array('id'=>$data['show_id']))->getField('uid');

        if(!$data['show_uid']){

            // $this->error = '帖子不存在!';

            // return false;

        }

        

        $data['parent_id'] = intval($ParentId);

        if($data['parent_id'] && !$this->where(array('id'=>$data['parent_id']))->count()){

            $this->error = '评论不存在!';

            return false;

        }

        

        $data['uid'] = intval($uid);

        if(!$data['uid']){

            $this->error = '用户ID不正确!';

            return false;

        }



        $userInfo = User($data['uid'], array('user_nicename','avatar'));

        $data['nickname'] = $userInfo['user_nicename'];

        $data['avatar'] = $userInfo['avatar'];

        if(!empty($data_id)){
            $data['data'] = $data_id;
        }
        

        $data['content'] = $content;

        if(empty($data['content'])){

            $this->error = '评论内容不能为空!';

            return false;

        }

        

        $data['create_time'] = date('Y-m-d H:i:s', NOW_TIME);

        $data['post_table'] = $PostTable;

        $data['status'] = 1;

        $id = $this->add($data);

        if($id){

            Coin($id, 'reply_show');

        }

        return true;

    }



    /**

     * 递归获取评论列表

     */

    function getCommlist($show_id, $parent_id = 0, &$result = array()) {



        $arr = M('Comment')->where(array('show_id' => $show_id, 'parent_id' => $parent_id))->order("create_time desc")->select();

        if (empty($arr)) {

            return array();

        }



        foreach ($arr as $cm) {

            $thisArr = &$result[];

            $cm['data'] = getImgVideoPath($cm['data']);

            $cm["children"] = $this->getCommlist($cm['show_id'], $cm["id"], $thisArr);

            $thisArr = $cm;

        }

        return $result;

    }



    //删除评论

    

    

    

}

