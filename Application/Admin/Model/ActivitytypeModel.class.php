<?php
namespace Admin\Model;
use Think\Model;
class ActivitytypeModel extends Model {
	//获取评论列表
	 public function getActivitytypeList($where,$currentPage=1,$pageSize=20){
	  return  $rs = $this->where($where)->order('createdate desc')->limit(($currentPage-1)*$pageSize.','.$pageSize*$currentPage)->select();
	 }
     //获取总行数
	 public function getCount($where){
		 return  $rs = $this->where($where)->count();
	 }


    protected $patchValidate = true; //批量验证

    protected $_validate = array(
        array(
            'type',
            'require',
            '类别必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_INSERT
        ) ,

        array(
            'type',
            '',
            '类别',
            self::EXISTS_VALIDATE,
            'unique',
            self::MODEL_INSERT
        )


    );
    //创建时间
    function createDate(){
        return date("Y-m-d h:i:s");
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