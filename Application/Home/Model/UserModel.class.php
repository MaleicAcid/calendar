<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{
     protected $patchValidate = true; //批量验证
     
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
            'status',
            1,
            self::MODEL_INSERT,
        ),
        array(
            'is_comment',
            1,
            self::MODEL_INSERT,
        )
    );

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
            'password2',
            'password',
            '确认密码不一致',
            self::MUST_VALIDATE,
            'confirm',
            self::MODEL_INSERT
        ) ,

        array(
            'gender',
            '0,1',
            '请不要篡改性别',
            self::VALUE_VALIDATE,
            'in',
            self::MODEL_BOTH
        )
    );

    
        //瞻前顾后机制(顾后)
    protected function _after_insert($data,$options) {
        //判断当前的动作为“注册”并发送邮件
        if($_POST['act']=='regist'){

            //生成校验码
            $code = md5(uniqid()); //生成一个唯一的校验码信息
            $this -> setField(array('user_id'=>$data['user_id'],'mail_check_code'=>$code));//更新校验码到会员记录

            //具体邮件发送
            //sendMail(注册邮箱，标题，内容);
            //$link = "http://web.php41.com/index.php/Home/User/jihuo/user_id/".$data['user_id']."/checkcode/".$code;
            $link = rtrim(C('SITE_URL'),'/').U('User/jihuo',array('user_id'=>$data['user_id'],'checkcode'=>$code));
            sendMail($data['user_email'],'会员注册激活',"请点击一下超链接，激活您的账号：<a href='".$link."' target='_blank'>".$link."</a>");
        }
    }

}

?>