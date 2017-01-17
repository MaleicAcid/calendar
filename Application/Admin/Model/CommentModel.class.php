<?php
namespace Admin\Model;
use Think\Model;
class CommentModel extends Model {
	//获取评论列表
	 public function getCommentList($where,$currentPage=1,$pageSize=20){
	  return  $rs = $this->where($where)->order('createdate desc')->limit(($currentPage-1)*$pageSize.','.$pageSize*$currentPage)->select();
	 }
     //获取总行数
	 public function getCount($where){
		 return  $rs = $this->where($where)->count();
	 }

	 public function getCommentByActivityId($where){
         return $rs = $this->order('createdate desc')->where($where)->select();
     }

	  protected $_auto = array(
        array(
            'createdate',
            'date',
            self::MODEL_INSERT,
            'function',
            array('Y-m-d H:i:s')
        )
		);
}

?>