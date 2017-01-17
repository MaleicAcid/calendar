<?php
namespace Admin\Model;
use Think\Model;
class ColorModel extends Model {
	//获取颜色列表
	 public function getColorList($where,$currentPage=1,$pageSize=20){
	  return  $rs = $this->where($where)->order('createdate desc')->limit(($currentPage-1)*$pageSize.','.$pageSize*$currentPage)->select();
	 }
     //获取总行数
	 public function getCount($where){
		 return  $rs = $this->where($where)->count();
	 }
	 
    protected $patchValidate = true; //批量验证

    protected $_validate = array(
        array(
            'color',
            'require',
            '颜色值必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_INSERT
        ) ,

        array(
            'color',
            '',
            '颜色值已经添加',
            self::EXISTS_VALIDATE,
            'unique',
            self::MODEL_INSERT
        ) ,
        array(
            'color',
            'require',
            '颜色名称必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        ) ,
       
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
            'createdate',
            'date',
            self::MODEL_INSERT,
            'function',
            array('Y-m-d H:i:s')
        ) ,
        array(
            'creator',
            'system',
            self::MODEL_INSERT,
        )
    );
}

?>