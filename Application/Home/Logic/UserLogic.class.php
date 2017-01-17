<?php

namespace Home\Logic;

use Think\Model\RelationModel;
use Think\Page;
use Think\Pageajax;
use Think\AjaxPage;
/**
 * 分类逻辑定义
 * Class CatsLogic
 * @package Home\Logic
 */
class UserLogic extends RelationModel
{
    /*
     * 登陆
     */
    public function login($username,$password){
    	$result = array();
        if(!$username || !$password)
           $result= array('status'=>0,'msg'=>'请填写账号或密码');
        $user = M('user')->where("username='{$username}'")->find();
        if(!$user){
           $result = array('status'=>-1,'msg'=>'账号不存在!');
        }elseif(encrypt($password) != $user['password']){
           $result = array('status'=>-2,'msg'=>'密码错误!');
        }elseif($user['is_lock'] == 1){
           $result = array('status'=>-3,'msg'=>'账号异常已被锁定！！！');
        }else{
            //查询用户信息之后, 查询用户的其他信息，如收藏数，评论数
          
           $result = array('status'=>1,'msg'=>'登陆成功','result'=>$user);
        }
        return $result;
    }
   
    

    /**
     * 注册
     * @param $username  邮箱或手机
     * @param $password  密码
     * @param $password2 确认密码
     * @return array
     */
    public function reg($username,$password,$password2){

        if(!$username || !$password)
            return array('status'=>-1,'msg'=>'请输入用户名或密码');
        
        //$pattern = '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])*(\.([a-z0-9])([-a-z0-9_-])([a-z0-9])+)*$/i'; //邮箱正则表达式
        
        if(!filter_var($username, FILTER_VALIDATE_EMAIL)){
            return array('status'=>-1,'msg'=>'请填写正确的邮箱地址');
        }

        //验证两次密码是否匹配
        if($password2 != $password)
            return array('status'=>-2,'msg'=>'两次输入密码不一致');
        //验证是否存在用户名
        if(get_user_info($username,4))
            return array('status'=>-1,'msg'=>'账号已存在');

        $map['password'] = encrypt($password);
        $map['username'] = $username;
        $map['user_mail'] = $username;
        $map['status'] = 1;
        $map['mail_check'] = 0;
        $map['is_comment'] = 1;
        $map['addtime'] = time();
        $map['createdate'] = createDate();

       
        //$map['token'] = md5(time().mt_rand(1,99999));
        $user_id = M('user')->add($map);
        if(!$user_id)        
            return array('status'=>-1,'msg'=>'注册失败');      
        
        $user = M('user')->where("user_id = {$user_id}")->find();
        return array('status'=>1,'msg'=>'注册成功','result'=>$user);
    }

     /*
      * 获取当前登录用户信息
      */
     public function get_info($user_id){
         if(!$user_id > 0)
             return array('status'=>-1,'msg'=>'缺少参数','result'=>'');
        $user_info = M('user')->where("user_id = {$user_id}")->find();
        if(!$user_info)
            return false;
         
//         $user_info['coupon_count'] = M('coupon_list')->where("uid = $user_id and use_time = 0")->count(); //获取优惠券列表         
//         $user_info['collect_count'] = M('goods_collect')->where(array('user_id'=>$user_id))->count(); //获取收藏数量         
//                                    
//         $user_info['waitPay']     = M('order')->where("user_id = $user_id ".C('WAITPAY'))->count(); //待付款数量
//         $user_info['waitSend']    = M('order')->where("user_id = $user_id ".C('WAITSEND'))->count(); //待发货数量         
//         $user_info['waitReceive'] = M('order')->where("user_id = $user_id ".C('WAITRECEIVE'))->count(); //待收货数量                  
//         $user_info['order_count'] = $user_info['waitPay'] + $user_info['waitSend'] + $user_info['waitReceive'];
         return array('status'=>1,'msg'=>'获取成功','result'=>$user_info);
     }
     
    /*
     * 获取最近一笔订单
     */
    public function get_last_order($user_id){
        $last_order = M('order')->where("user_id = {$user_id}")->order('order_id DESC')->find();
        return $last_order;
    }


    /*
     * 获取订单商品
     */
    public function get_order_goods($order_id){
        $sql = "SELECT og.*,g.original_img FROM __PREFIX__order_goods og LEFT JOIN __PREFIX__goods g ON g.goods_id = og.goods_id WHERE order_id = ".$order_id;
        $goods_list = $this->query($sql);

        $return['status'] = 1;
        $return['msg'] = '';
        $return['result'] = $goods_list;
        return $return;
    }

    /*
     * 获取账户资金记录
     */
    public function get_account_log($user_id,$type=0){
        //查询条件
//        $type = I('get.type',0);
        if($type == 1){
            //收入
            $where = 'user_money > 0 OR pay_points > 0 AND user_id='.$user_id;
        }
        if($type == 2){
            //支出
            $where = 'user_money < 0 OR pay_points < 0 AND user_id='.$user_id;
        }

        $count = M('account_log')->where($where ? $where : 'user_id = '.$user_id)->count();
        $Page = new Page($count,16);
        $logs = M('account_log')->where($where ? $where : 'user_id = '.$user_id)->order('change_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $return['status'] = 1;
        $return['msg'] = '';
        $return['result'] = $logs;
        $return['show'] = $Page->show();

        return $return;
    }

    public function get_activity_remind($user_id) {
        $map = array(
            'user_id'=>$user_id,
            'status'=>1,
            'is_remind'=> 1,
            'is_comet_remind'=> 1,
            );
        
        $count = M('user_activity')->where($map)->count();
        //$page = new nPage($count,8);
        $page = new Page($count,8);
        $show = $page->show();
        //获取我的提醒列表
        $sql = "SELECT c.collect_id,c.add_time,g.id,g.title,g.content,g.type,g.startdate,g.enddate FROM __PREFIX__user_activity c ".
            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
            "WHERE c.user_id = ".$user_id." and c.status =1 and c.is_remind=1 and c.is_comet_remind =1".
            " ORDER BY c.add_time DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = M()->query($sql);
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        $return['show'] = $show;        
        return $return;
    }
    
    /**
     * 获取事件收藏列表
     * @param $user_id  用户id
     */
    public function get_activity_collect($user_id){
        $count = M('user_activity')->where(array('user_id'=>$user_id,'status'=>1))->count();
        $page = new Page($count,8);
        $show = $page->show();
        //获取我的收藏列表
        $sql = "SELECT c.collect_id,c.add_time,c.is_remind,c.is_comet_remind,g.id,g.title,g.content,g.type,g.startdate,g.enddate FROM __PREFIX__user_activity c ".
            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
            "WHERE c.user_id = ".$user_id.
            " ORDER BY c.add_time DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = M()->query($sql);
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        $return['show'] = $show;        
        return $return;
    }

    public function get_ajax_collect($user_id) {
        $count = M('user_activity')->where(array('user_id'=>$user_id,'status'=>1))->count();
        $page = new Pageajax($count,8,'show');
        $show = $page->show();
        //获取我的收藏列表
        $sql = "SELECT c.collect_id,c.add_time,c.is_remind,c.is_comet_remind,g.id,g.title,g.content,g.type,g.startdate,g.enddate FROM __PREFIX__user_activity c ".
            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
            "WHERE c.user_id = ".$user_id.
            " ORDER BY c.add_time DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = M()->query($sql);
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        $return['show'] = $show;        
        return $return;
    }
    
    
    
    /**
     * 获取评论列表
     * @param $user_id 用户id
     * @param $status  状态 0 未评论 1 已评论
     * @return mixed
     */
    public function get_comment($user_id,$status=1){
        if($status == 1){
            //已评论
            $count2 = M('')->query("select count(*) as count from `__PREFIX__comment`  as c inner join __PREFIX__activity as g on c.goods_id = g.goods_id and c.order_id = g.order_id where c.user_id = $user_id");
            $count2 = $count2[0]['count'];
            
            $page = new Page($count2,10);
            $sql = "select c.*,g.*,(select order_sn from  __PREFIX__order where order_id = c.order_id ) as order_sn  from  __PREFIX__comment as c inner join __PREFIX__order_goods as g on c.goods_id = g.goods_id and c.order_id = g.order_id where c.user_id = $user_id order by c.add_time desc LIMIT {$page->firstRow},{$page->listRows}";
        }else{        	
        	$countsql = " select count(1) as comment_count from __PREFIX__order_goods as og
        	left join __PREFIX__order as o on o.order_id = og.order_id where o.user_id = $user_id  and og.is_send = 1 ";
        	$where = '';
        	if($status == 0){
        		$countsql .= $where = " and og.is_comment = 0 ";
        	}
        	$comment = M()->query($countsql);
        	$count1 = $comment[0][comment_count]; // 待评价
            $page = new Page($count1,3);
            $sql =" select *  from __PREFIX__order_goods as og 
            left join __PREFIX__order as o on o.order_id = og.order_id  where o.user_id = $user_id and og.is_send = 1  
            $where order by o.order_id desc  LIMIT {$page->firstRow},{$page->listRows}";            
        }
        
        $show = $page->show();
        $comment_list = M()->query($sql);
        if($comment_list){
        	$return['result'] = $comment_list;
        	$return['show'] = $show; //分页
        	return $return;
        }else{
        	return array();
        }
    }
    
    /**
     * 添加评论
     * @param $activity_id  事件id
     * @param $user_id 用户id
     * @param $username  用户名
     * @return bool
     */
    public function add_comment($add){
        if(!$add[user_id] || !$add[activity_id])
            return array('status'=>-1,'msg'=>'非法操作','result'=>'');
        
        //检查事件是否有效
        $order = M('activity')->where("activity_id = {$add[activity_id]}")->find();
        if($order['status'] != 1)
            return array('status'=>-1,'msg'=>'无效事件','result'=>'');   
        
        $row = M('comment')->add($add);
        if($row)
        {
            //更新事件表状态
            M('activity')->where(array('activity_id'=>$add[activity_id]))->setInc('commentcount',1); // 评论数加一  
            
            return array('status'=>1,'msg'=>'评论成功','result'=>'');
        }
        return array('status'=>-1,'msg'=>'评论失败','result'=>'');
    }

    /**
     * 邮箱或手机绑定
     * @param $email_mobile  邮箱或者手机
     * @param int $type  1 为更新邮箱模式  2 手机
     * @param int $user_id  用户id
     * @return bool
     */
    public function update_email_mobile($email_mobile,$user_id,$type=1){
        //检查是否存在邮件
        if($type == 1)
            $field = 'user_mail';
        if($type == 2)
            $field = 'mobile';
        $condition['user_id'] = array('neq',$user_id);
        $condition[$field] = $email_mobile;

        $is_exist = M('user')->where($condition)->find();
        if($is_exist)
            return false;
        unset($condition[$field]);
        $condition['user_id'] = $user_id;
        $validate = $field.'_validated';
        M('user')->where($condition)->save(array($field=>$email_mobile,$validate=>1));
        return true;
    }

    /**
     * 更新用户信息
     * @param $user_id
     * @param $post  要更新的信息
     * @return bool
     */
    public function update_info($user_id,$post=array()){
        $model = M('user')->where("user_id = ".$user_id);
        $row = $model->setField($post);
        if($row === false)
           return false;
        return true;
    }

    /**
     * 地址添加/编辑
     * @param $user_id 用户id
     * @param $user_id 地址id(编辑时需传入)
     * @return array
     */
    public function add_address($user_id,$address_id=0,$data){
        $post = $data;
        if($address_id == 0)
        {
            $c = M('UserAddress')->where("user_id = $user_id")->count();
            if($c >= 20)
                return array('status'=>-1,'msg'=>'最多只能添加20个收货地址','result'=>'');
        }        
        
        //检查手机格式
        if($post['consignee'] == '')
            return array('status'=>-1,'msg'=>'收货人不能为空','result'=>'');
        if(!$post['province'] || !$post['city'] || !$post['district'])
            return array('status'=>-1,'msg'=>'所在地区不能为空','result'=>'');
        if(!$post['address'])
            return array('status'=>-1,'msg'=>'地址不能为空','result'=>'');
        if(!check_mobile($post['mobile']))
            return array('status'=>-1,'msg'=>'手机号码格式有误','result'=>'');

        //编辑模式
        if($address_id > 0){

            $address = M('user_address')->where(array('address_id'=>$address_id,'user_id'=> $user_id))->find();
            if($post['is_default'] == 1 && $address['is_default'] != 1)
                M('user_address')->where(array('user_id'=>$user_id))->save(array('is_default'=>0));
            $row = M('user_address')->where(array('address_id'=>$address_id,'user_id'=> $user_id))->save($post);
            if(!$row)
                return array('status'=>-1,'msg'=>'操作完成','result'=>'');
            return array('status'=>1,'msg'=>'编辑成功','result'=>'');
        }
        //添加模式
        $post['user_id'] = $user_id;
        
        // 如果目前只有一个收货地址则改为默认收货地址
        $c = M('user_address')->where("user_id = {$post['user_id']}")->count();        
        if($c == 0)  $post['is_default'] = 1;
        
        $address_id = M('user_address')->add($post);
        //如果设为默认地址
        $insert_id = M()->getLastInsID();
        $map['user_id'] = $user_id;
        $map['address_id'] = array('neq',$insert_id);
               
        if($post['is_default'] == 1)
            M('user_address')->where($map)->save(array('is_default'=>0));
        if(!$address_id)
            return array('status'=>-1,'msg'=>'添加失败','result'=>'');
        
        
        return array('status'=>1,'msg'=>'添加成功','result'=>$address_id);
    }



    /**
     * 修改密码
     * @param $user_id  用户id
     * @param $old_password  旧密码
     * @param $new_password  新密码
     * @param $confirm_password 确认新 密码
     */
    public function password($user_id,$old_password,$new_password,$confirm_password,$is_update=true){
        $data = $this->get_info($user_id);
        $user = $data['result'];
        if(strlen($new_password) < 6)
            return array('status'=>-1,'msg'=>'密码不能低于6位字符','result'=>'');
        if($new_password != $confirm_password)
            return array('status'=>-1,'msg'=>'两次密码输入不一致','result'=>'');
        //验证原密码
        if($is_update && ($user['password'] != '' && encrypt($old_password) != $user['password']))
            return array('status'=>-1,'msg'=>'密码验证失败','result'=>'');
        $row = M('user')->where("user_id='{$user_id}'")->save(array('password'=>encrypt($new_password)));
        if(!$row)
            return array('status'=>-1,'msg'=>'修改失败','result'=>'');
        return array('status'=>1,'msg'=>'修改成功','result'=>'');
    }


    
    /**
     * 发送验证码
     * @param $sender 接收人
     * @param $type 发送类型
     * @return json
     */
    public function send_validate_code($sender,$type){
    	$sms_time_out = tpCache('sms.sms_time_out');
    	$sms_time_out = $sms_time_out ? $sms_time_out : 180;
    	//获取上一次的发送时间
    	$send = session('validate_code');
    	if(!empty($send) && $send['time'] > time() && $send['sender'] == $sender){
    		//在有效期范围内 相同号码不再发送
    		$res = array('status'=>-1,'msg'=>'规定时间内,不要重复发送验证码');
    	}
    	$code =  mt_rand(1000,9999);
    	if($type == 'email'){
    		//检查是否邮箱格式
    		if(!check_email($sender))
    			$res = array('status'=>-1,'msg'=>'邮箱码格式有误');
    		$send = sendMmail($sender,'[密码重置]大连海事大学','您好，你的[密码重置]验证码是：'.$code);
    	}else{
    		if(!check_mobile($sender))
    			$res = array('status'=>-1,'msg'=>'手机号码格式有误');
    			//$send = sendSMS($sender,'您好，你的验证码是：'.$code);
                $send = sendSMS($sender,$code);
    	}
    	if($send){
    		$info['code'] = $code;
    		$info['sender'] = $sender;
    		$check['is_check'] = 0;
    		$info['time'] = time() + $sms_time_out; //有效验证时间
    		session('validate_code',$info);
    		$res = array('status'=>1,'msg'=>'验证码已发送，请注意查收');
    	}else{
    		$res = array('status'=>-1,'msg'=>'验证码发送失败,请联系管理员');
    	}
        return $res;
    }
    
    public function check_validate_code($code,$sender){   	
    	$check = session('validate_code');
    	if(empty($check))
    	{
    		$res = array('status'=>0,'msg'=>'请先获取验证码');
    	}elseif($check['time']<time())
    	{
    		$res = array('status'=>0,'msg'=>'验证码已超时失效');
    	}elseif($code!=$check['code'] || $check['sender']!=$sender)
    	{
    		$res = array('status'=>0,'msg'=>'验证失败,验证码有误');
    	}else
    	{
    		$check['is_check'] = 1; //标示验证通过
    		session('validate_code',$check);
    		$res = array('status'=>1,'msg'=>'验证成功');
    	}
    	return $res;
    }

    /**
     * @time 2016/09/01
     * @author dyr
     * 设置用户系统消息已读
     */
    public function setSysMessageForRead()
    {
        $user_info = session('user');
        if (!empty($user_info['user_id'])) {
            $data['status'] = 1;
            M('user_message')->where(array('user_id' => $user_info['user_id'], 'category' => 0))->save($data);
        }
    }
}