<?php
namespace Admin\Model;
use Think\Model;
class ActivityModel extends Model {

    public function getUserList($where,$currentPage=1,$pageSize=20){
        return  $rs = $this->where($where)->order('activitydate desc,level desc')->limit(($currentPage-1)*$pageSize.','.$pageSize*$currentPage)->select();
    }

    public function getCount($where){
        return  $rs = $this->where($where)->count();
    }
	public function countMonthActivity($where){
        return $rs = $this->field('activitydate,count(activitydate) as count')->where($where)->group('activitydate')->having('count(activitydate)>3')->select();
    }

    public function  getDayActivity($where,$currentPage=1,$pageSize=10){

        return $rs = $this->where($where)->limit(($currentPage-1)*$pageSize.','.$pageSize*$currentPage)->select();
    }
    public function getDayCount($where){
        return  $rs = $this->where($where)->count();
    }

    public function getYearActivity($where){
        return $rs = $this->field('activitydate,count(activitydate) as count')->where($where)->group('activitydate')->select();
    }

    public function getHotActivity($where){
        return $rs = $this->where($where)->order('readcount desc')->limit(10)->select();
    }

	 public function getActivityByKeyword($where){
        return $rs = $this->where($where)->order('startdate desc')->limit(10)->select();
    }

    protected $patchValidate = true; //批量验证

    protected $_validate = array(
        array(
            'title',
            'require',
            '标题必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        ) ,
        array(
            'content',
            'require',
            '内容必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        ) ,
        array(
            'startdate',
            'require',
            '开始时间必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        ) ,
        array(
            'enddate',
            'require',
            '结束时间必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        ) ,
        array(
            'level',
            'require',
            '活动等级必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        ) ,
        array(
            'color',
            'require',
            '活动颜色必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        ) ,
        array(
            'unit',
            'require',
            '举办方必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        ) ,
        array(
            'address',
            'require',
            '举办地点必须填写',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        ) ,

        array(
            'type',
            'require',
            '活动类型
            ',
            self::EXISTS_VALIDATE,
            'regex',
            self::MODEL_BOTH
        )
    );
    protected $_auto = array(
        array(
            'addtime',
            'time',
            self::MODEL_INSERT,
            'function'
        )
    );

}

?>