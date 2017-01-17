<?php
namespace Admin\Model;
use Think\Model;
class UserModel extends Model {
	//获取用户列表
	 public function getUserList($where,$currentPage=1,$pageSize=20){
	  return  $rs = $this->where($where)->order('user_id')->limit(($currentPage-1)*$pageSize.','.$pageSize*$currentPage)->select();
	 }
     //获取总行数
	 public function getCount($where){
		 return  $rs = $this->where($where)->count();
	 }
    protected $patchValidate = true; //批量验证
}

?>