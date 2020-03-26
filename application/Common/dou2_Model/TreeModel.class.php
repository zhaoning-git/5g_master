<?php
namespace Common\Model;

/**
 * 生成多层树状下拉选框的工具模型
 */
class TreeModel {
	/**
     * 把返回的数据集转换成Tree
     * @access public
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     */
	public function toTree($list=null, $pk='id',$pid = 'pid',$child = '_child'){
		
		if(null === $list) {
            // 默认直接取查询返回的结果集合
			$list   =   &$this->dataList;
		}
        // 创建Tree
		$tree = array();
		if(is_array($list)) {
            // 创建基于主键的数组引用
			$refer = array();

			foreach ($list as $key => $data) {
				$_key = is_object($data)?$data->$pk:$data[$pk];
				$refer[$_key] =& $list[$key];
			}
			foreach ($list as $key => $data) {
			
                // 判断是否存在parent
				$parentId = is_object($data)?$data->$pid:$data[$pid];
				$is_exist_pid = false;
				foreach($refer as $k=>$v){
					
					if($parentId==$k){
						$is_exist_pid = true;
						break;
					}
				}
				if ($is_exist_pid) {
					if (isset($refer[$parentId])) {
						
						$parent =& $refer[$parentId];
						$parent[$child][] =& $list[$key];
					}
				} else {
					$tree[] =& $list[$key];
				}
			}
		}
		return $tree;
	}


	

	/**
	 * 将格式数组转换为树
	 * @param array $list
	 * @param integer $level 进行递归时传递用的参数
	 */
	private $formatTree; //用于树型数组完成递归格式的全局变量
	private function _toFormatTree($list,$level=0,$title = 'title') {	
		foreach($list as $key=>$val){
			$tmp_str=str_repeat("&nbsp;",$level*2);
			$tmp_str.="└";

			$val['level'] = $level;
			$val['title_show'] =$level==0?$val[$title]."&nbsp;":$tmp_str.$val[$title]."&nbsp;";
				// $val['title_show'] = $val['id'].'|'.$level.'级|'.$val['title_show'];
			if(!array_key_exists('_child',$val)){
				array_push($this->formatTree,$val);
			}else{
				$tmp_ary = $val['_child'];
				unset($val['_child']);
				array_push($this->formatTree,$val);
				   $this->_toFormatTree($tmp_ary,$level+1,$title); //进行下一层递归
				}
			}
			return;
		}

		public function toFormatTree($list,$title = 'title',$pk='id',$pid = 'pid',$root = 0){ 
			$list = list_to_tree($list,$pk,$pid,'_child',$root);
			$this->formatTree = array();
			$this->_toFormatTree($list,0,$title);
			return $this->formatTree;
		}


	
	
	/**
	 * 获取分类 专为zTree插件提供
	 * return model 模型名称
	 * for_templaet 是否将全部分类默认输出
	 * start_id 起始ID
	 * @author  
	 */
	public function ForzTree($model,$for_templaet = false,$start_id = 0){
		
		$Model = M($model);
		
		if($start_id){
			$map['_string'] = "FIND_IN_SET($start_id,tree)";
		}
		
		$map['store_id'] = is_login();
		$map['status'] = array('egt',0);
		
		if(!$for_templaet){
			if(array_key_exists('id',$_REQUEST)) {
				$pid = $_REQUEST['id'];
			}
			$pid || $pid = 0;
			
			$map['pid'] = $pid;
			$list  = $Model->where($map)->field('id,title,pid,sort,status')->order('sort asc')->select(); 	
	
			foreach ($list as $k => $v) {
				$child = $Model->where(array('status'=>array('egt',0),'pid'=>$v['id']))->field('id')->find();
				$list[$k]['isParent'] = ($child) ? ('true') : ('false');
			}
			
		}else{
			$arrList = M($model)->where($map)->field('id,title,pid,sort,status')->order('sort asc')->select();
			$data = D('Tree')->toTree($arrList);	
			$list = json_encode($data,true);
		}
		return $list;
    }
	
	
			
	 /**
	 * 更新分类tree字段
	 * @return id 指定分类id
	 * @return target_id 要拖拽的目标id
	 * @author  
	 */
	 public function UpdateTree($model) {
		 
		$Model = M($model);
		$list = $this->toTree($Model->where(array('status'=>array('egt',0)))->field('id,title,pid,sort')->order('sort asc')->select());
		$arr = $this->toFormatTree($list);
		foreach($arr as $k =>$v){
			$TopTree = $Model->where(array('id'=>$v['pid']))->getField('tree');
			if(!$TopTree){
				$arr[$k]['tree'] = $v['id'];	
			}else{
				$arr[$k]['tree'] = $TopTree.','.$v['id'];
			}
			$Model->where(array('id'=>$v['id']))->save(array('tree'=>$arr[$k]['tree']));
		}
		return true;
	 }
	  
	  
	 	
	/**
	 * 获取某分类下所有子分类
	 * model 模型名称
	 * id 指定分类id下的子分类
	 * is_str 是否返回用逗号分隔字符串
	 * @author  
	 */
	  public function SubCategory($model, $id, $is_str = true) {
		if(strstr($id,',')){
			return $id;
		}
		$Model = M($model);
		$cids = $Model->where("FIND_IN_SET($id,tree)")->field('id')->select();
		if($is_str){
			return implode(',',array_column($cids,'id'));
		}else{
			return $cids;
		}
	 }
	
	 
	
}
?>
