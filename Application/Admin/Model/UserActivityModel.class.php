<?php
namespace Admin\Model;
use Think\Model;
class UserActivityModel extends Model {
    protected  $tableName = "user_activity";

	 public function getUserActivity($where){
         return $rs = $this->where($where)->find();
     }

}

?>