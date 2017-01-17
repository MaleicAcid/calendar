<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model {
	//获取用户列表
	 public function getUserList($where,$currentPage=1,$pageSize=20){
	  return  $rs = $this->where($where)->order('createdate desc')->limit(($currentPage-1)*$pageSize.','.$pageSize*$currentPage)->select();
	 }
     //获取总行数
	 public function getCount($where){
		 return  $rs = $this->where($where)->count();
	 }
	 
    protected $patchValidate = true; //批量验证

    protected $_validate = array(
        array(
            'username',
            'require',
            '用户名必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_INSERT
        ) ,

        array(
            'username',
            '',
            '用户名被别人占用了',
            self::EXISTS_VALIDATE,
            'unique',
            self::MODEL_INSERT
        ) ,
        array(
            'password',
            'require',
            '密码必须填写'
        ) ,
        
        array(
            'type',
            '0,1',
            '请不要篡改用户类型',
            self::VALUE_VALIDATE,
            'in',
            self::MODEL_BOTH
        )
    );
	//创建时间
    function createDate(){
        return date("Y-m-d h:i:s");
    }
    //修改时间
	 function updateDate(){
        return date("Y-m-d h:i:s");
    }

    protected $_auto = array(
        array(
            'password',
            'md5',
            self::MODEL_BOTH,
            'function'
        ) ,
        array(
            'createdate',
            'date',
            self::MODEL_INSERT,
            'function',
            array('Y-m-d H:i:s')
        ) ,
		 array(
            'updatedate',
            'date',
            self::MODEL_BOTH,
            'function',
            array('Y-m-d H:i:s')
        ) ,
        array(
            'status',
            0,
            self::MODEL_INSERT,
        ),
        array(
            'creator',
            'system',
            self::MODEL_INSERT,
        )
    );
}

?>