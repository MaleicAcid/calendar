<?php

namespace Home\Controller;

use Home\Logic\UserLogic;
use Think\Page;
use Think\Verify;
use Think\Pageajax;
use Think\AjaxPage;
use Think\AjaxPage2;
use Think\AjaxPage3;

class UserController extends BaseController {

    public $user_id = 0;
    public $user = array();
    protected $mail_valid_time = 7200;//邮箱验证码有效时间为2小时

    public function _initialize() {
        parent::_initialize();
        if (session('?user')) {
            $user = session('user');
            $map = array('user_id' => $user['user_id']);
            $user = M('user')->where($map)->find();
            session('user', $user);  //覆盖session 中的 user               
            $this->user = $user;
            $this->user_id = $user['user_id'];
            $this->assign('user', $user); //存储用户信息
            $this->assign('user_id', $this->user_id);
        } else {
            $nologin = array(
                'login', 'pop_login', 'do_login', 'logout', 'verify', 'set_pwd', 'finished',
                'verifyHandle', 'reg', 'send_sms_reg_code', 'identity', 'check_validate_code',
                'forget_pwd', 'check_captcha', 'check_username', 'send_validate_code',
                'resetPassword','doResetPassword','jihuo',
            );
            if (!in_array(ACTION_NAME, $nologin)) {
                header("location:" . U('Home/User/login'));
                exit;
            }
        }
//        //用户中心面包屑导航
//        $navigate_user = navigate_user();
//        $this->assign('navigate_user', $navigate_user);
    }

    /*
     * 用户中心首页
     */

    public function index() {
        $logic = new UserLogic();
        $user = $logic->get_info($this->user_id);
        $user = $user['result'];
        
        
//        $comment_data = $logic->get_comment($user_id,$status); //获取评论列表
//        $this->assign('comment_page',$comment_data['show']);// 赋值分页输出
//        $this->assign('comment_list',$comment_data['result']);
        
        //$collect_data = $logic->get_activity_collect($this->user_id);
        $collect_data = $this->get_ajax_collect();
        $this->assign('collect_page', $collect_data['show']); // 赋值分页输出
        $this->assign('collect_list', $collect_data['result']);
        
        //$reming_data = $logic->get_activity_remind($this->user_id);
        $remind_data = $this->get_ajax_remind();
        $this->assign('remind_page', $remind_data['show']); // 赋值分页输出
        $this->assign('remind_list', $remind_data['result']);
        
        $comment_data = $this->get_ajax_comment();
        $this->assign('comment_page', $comment_data['show']); // 赋值分页输出
        $this->assign('comment_list', $comment_data['result']);
        
        
        
        
        if($user['mail_check']==0){
            $mail_check_hint = "请前往您的注册邮箱中检查收件,并按提示点击激活";
        }else{
            $mail_check_hint = "邮箱已激活";
        }
        
        //var_dump($mail_check_hint);die;
        
        $this->assign('mail_check_hint', $mail_check_hint);
        $this->assign('user', $user);
        
        
        $this->display();
        
        //$this->cometRemind();
    }
    
    
    public function mindex() {
        $logic = new UserLogic();
        $user = $logic->get_info($this->user_id);
        $user = $user['result'];
        
        $comment_map = array(
            'user_id' => $this->user_id, 
        );
        $comment_number = M('comment')->where($comment_map)->count();
        
        $collect_map = array(
            'user_id' => $this->user_id,
            'status' =>1,
        );
        $collect_number = M('user_activity')->where($collect_map)->count();
        
        $remind_map = array(
            'user_id' => $this->user_id, 
            'is_remind' => array('neq',0),
            'status' =>1,
        );
        $remind_number = M('user_activity')->where($remind_map)->count();
        $this->assign('comment_number', $comment_number);
        $this->assign('collect_number', $collect_number);
        $this->assign('remind_number', $remind_number);
        
        
        $this->display();
    }

    /**
     *  注册
     */
    public function reg() {
        if ($this->user_id > 0)
            header("Location: " . U('Home/User/index'));

        if (IS_POST) {
            $logic = new UserLogic();
            //验证码检验
//            $this->verifyHandle('user_reg');
            $username = I('post.username', '');
            $password = I('post.password', '');
            $password2 = I('post.password2', '');
            $code = I('post.code', '');


            $data = $logic->reg($username, $password, $password2);
            if ($data['status'] != 1) {
                exit(json_encode($data));
                //$this->error($data['msg']);
            }

            //注册完成后先不做session持久化
            session('user', $data['result']);
            setcookie('user_id', $data['result']['user_id'], null, '/');
            setcookie('is_distribut', $data['result']['is_distribut'], null, '/');
            $username = empty($data['result']['username']) ? $username : $data['result']['username'];
            setcookie('uname', $username, null, '/');

//            $cartLogic = new \Home\Logic\CartLogic();
//            $cartLogic->login_cart_handle($this->session_id,$data['result']['user_id']);  //用户登录后 需要对购物车 一些操作
            //生成校验码
            $code = md5(uniqid()); //生成一个唯一的校验码信息
            $tmp = M('user');
            $tmp->setField(array('user_id' => $data['result']['user_id'], 'mail_check_code' => $code, 'mail_check_code_addtime' => time())); //更新校验码到会员记录
            //具体邮件发送
            //sendMail(注册邮箱，标题，内容);
            //$link = "http://web.php41.com/index.php/Home/User/jihuo/user_id/".$data['user_id']."/checkcode/".$code;
            $des_encrypt_user_id = des_encrypt($data['result']['user_id']);
            //var_dump($des_encrypt_user_id);


            $link = rtrim(C('SITE_URL'), '/') . U('User/jihuo', array('user_id' => $des_encrypt_user_id, 'checkcode' => $code));
            sendMail($data['result']['user_mail'], '[账户激活]大连海事大学', "请点击一下超链接，激活您的账号：<a href='" . $link . "' target='_blank'>" . $link . "</a>");

            //$this->success($data['msg'],U('Home/User/index'));
            exit(json_encode($data));
        }

//        $this->assign('regis_smtp_enable',tpCache('smtp.regis_smtp_enable')); // 注册启用邮箱：

        $this->display();
    }

    /**
     *  登录
     */
    public function login() {
        if ($this->user_id > 0) {
            header("Location: " . U('Home/User/index'));
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Home/User/index");
        $this->assign('referurl', $referurl);
        $this->display();
    }

    public function pop_login() {
        if ($this->user_id > 0) {
            header("Location: " . U('Home/User/Index'));
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Home/User/index");
        $this->assign('referurl', $referurl);
        $this->display();
    }

    public function do_login() {
        $username = I('post.username');
        $password = I('post.password');
        $username = trim($username);
        $password = trim($password);

        $verify_code = I('post.verify_code');

//        $verify = new Verify();
//        if (!$verify->check($verify_code, 'user_login')) {
//            $res = array('status' => 0, 'msg' => '验证码错误');
//
//            $this->error('验证码错误！');
//            //exit(json_encode($res));
//        }

        $logic = new UserLogic();
        $res = $logic->login($username, $password);

        if ($res['status'] == 1) {
            $res['url'] = urldecode(I('post.referurl'));
            session('user', $res['result']);
            setcookie('user_id', $res['result']['user_id'], null, '/');
            $username = $res['result']['username'];
            setcookie('uname', urlencode($username), null, '/');
            setcookie('cn', 0, time() - 3600, '/');

            $this->success($res['msg'], U('Admin/Front/rili'));
            exit;
        }
        $this->error($res['msg'], U('Home/User/login'));
        //exit(json_encode($res));
    }

    public function logout() {
        $is_m = I('is_m');
        setcookie('uname', '', time() - 3600, '/');
        setcookie('cn', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');
        session_unset();
        session_destroy();
        if($is_m == 1){
            $this->success("退出成功", U('Admin/Front/rili_m'));
        }else{
            $this->success("退出成功", U('Admin/Front/rili')); 
        }

        //header("location:".U('Home/Index/index'));
        exit;
    }

    //用户邮箱激活
    public function jihuo() {
        $user_id = I('get.user_id');
        $checkcode = I('get.checkcode');
        $des_encrypt_user_id = des_decrypt($user_id);
        //更改user_check=1,user_check_code=null
        $user = D('User');
        //首先需要验证，再激活
        $userinfo = $user->where(array('mail_check' => 0))->find($des_encrypt_user_id);

        if ( ((time()-$userinfo['mail_check_code_addtime']) <= $this->mail_valid_time) && $userinfo['mail_check_code'] === $checkcode) {

            //验证码比较成功就激活
            $z = $user->setField(array('user_id' => $des_encrypt_user_id, 'mail_check' => 1, 'mail_check_code' => 'verified'));
            if ($z) {
                $this->success('帐号激活成功', U('login'), 5);
            } else {
                $this->error('非法操作', U('login'), 5);
            }
        } else {
            $this->error('激活链接过期或账号已激活', U('login'), 5);
        }
    }

    public function resetPassword() {//发送重置密码邮件
        //$user_id = I('user_id');
        $user_mail = I('user_mail');

        $user = M('User');
        //首先验证该用户邮箱是否激活
        $userinfo = $user->where(array('mail_check' => 1,'user_mail'=>$user_mail))->find();
        if ($userinfo) {
            //验证码比较成功就转跳到重置密码页面
            $user_id = $userinfo['user_id'];
            $user_mail = $userinfo['user_mail'];

            $code = md5(uniqid()); //生成一个唯一的校验码信息
            $z = $user->setField(array('user_id' => $user_id, 'mail_check_code' => $code, 'mail_check_code_addtime' => time()));
            if ($z) {
                $des_encrypt_user_id = des_encrypt($user_id);
                $link = rtrim(C('SITE_URL'), '/') . U('User/doResetPassword', array('user_id' => $des_encrypt_user_id, 'checkcode' => $code));
                sendMail($user_mail, '[密码重置]大连海事大学', "请点击一下超链接，在打开的页面中重置密码：<a href='" . $link . "' target='_blank'>" . $link . "</a>");
                $this->success('邮件已发送', U('login'), 5);
            } else {
                $this->error('非法操作', U('login'), 5);
            }
        } else {
            $this->error('操作有误或账号未激活', U('login'), 5);
        }

        //$this->success($data['msg'],U('Home/User/index'));
        exit();
    }

    //用户重置密码
    public function doResetPassword() {//验证邮件密码重置连接
        $user_id = I('get.user_id');
        $checkcode = I('get.checkcode');
        $des_encrypt_user_id = des_decrypt($user_id);
        //更改user_check=1,user_check_code=null
        $user = D('User');
        //首先需要验证，再激活
        $userinfo = $user->where(array('mail_check' => 0))->find($des_encrypt_user_id);



        //var_dump($des_encrypt_user_id);die;

        if (((time()-$userinfo['mail_check_code_addtime']) <= $this->mail_valid_time) && $userinfo['mail_check_code'] === $checkcode) {

            //验证码比较成功就转跳到重置密码页面
            $z = $user->setField(array('user_id' => $des_encrypt_user_id, 'mail_check' => 1, 'mail_check_code' => 'verified'));
            if ($z) {
                $this->success('进入密码重置操作', U('User/set_pwd', array('user_id' => $user_id)), 0);
            } else {
                $this->error('非法操作', U('login'), 5);
            }
        } else {
            $this->error('激活链接过期或账号已激活', U('login'), 5);
        }
    }

    public function set_pwd() {

        $this->assign('user_id',$user_id);
        
        
//        $checkcode = I('get.checkcode');
//    	if(empty($check)){
//    		header("Location:".U('Home/User/forget_pwd'));
//    	}elseif($check['is_check']==0){
//    		$this->error('验证码还未验证通过',U('Home/User/forget_pwd'));
//    	}    	
        if (IS_POST) {
            $password = I('post.password');
            $password2 = I('post.password2');
            $user_id = I('post.user_id');
            $user_id = des_decrypt($user_id);
            if ($password2 != $password) {
                $this->error('两次密码不一致', U('User/forget_pwd'));
            }
            //if ($check['is_check'] == 1) {
                //$user = get_user_info($check['sender'],1);
                //$user = M('users')->where("mobile = '{$check['sender']}' or email = '{$check['sender']}'")->find();
                $z = M('user')->where("user_id=" . $user_id)->save(array('password' => encrypt($password)));
                //session('validate_code',null);
                //header("Location:" . U('Home/User/finished'));
                if($z){
                    $this->success('密码重置成功',U('User/login'),5);
                    exit();
                }else{
                    $this->success('非法操作',U('User/login'),5);
                    exit();
                }
                
             
        }
        $this->display();
    }

    /*
     * 事件收藏
     */

    public function activity_collect() {
        $userLogic = new UserLogic();
        $data = $userLogic->get_activity_collect($this->user_id);
        $this->assign('page', $data['show']); // 赋值分页输出
        $this->assign('lists', $data['result']);
        $this->assign('active', 'activity_collect');
        $this->display();
    }

    /*
     * 删除一个收藏的事件
     */

    public function del_activity_collect() {
        $id = I('get.id');
        if (!$id)
            $this->error("缺少ID参数");
        $row = M('user_activity')->where(array('activity_id' => $id, 'user_id' => $this->user_id, 'status' => 1))->delete();
        if (!$row)
            $this->error("删除失败");
        $this->success('删除成功');
    }

    
    /*
     * 获取评论
     */
    public function comment(){
        $user_id = $this->user_id;
        $status = I('get.status',-1);
        $logic = new UsersLogic();
        $data = $logic->get_comment($user_id,$status); //获取评论列表
        $this->assign('page',$data['show']);// 赋值分页输出
        $this->assign('comment_list',$data['result']);
        $this->assign('active','comment');
        $this->display();
    }
    
    /*
     * 添加评论
     */
    public function add_comment() {
        if (!($this->user_id > 0)) {
            header("location:" . U('Home/User/login'));
            exit;
        }

        $user_info = session('user');
        //$comment_img = serialize(I('comment_img')); // 上传的图片文件            
        $add['acticity_id'] = I('acticity_id');
        $add['user_email'] = $user_info['user_email'];
        //$add['nick'] = $user_info['nickname'];
        $add['username'] = $user_info['username'];

        //$add['content'] = htmlspecialchars(I('post.content'));
        $add['content'] = I('content');
        //$add['img'] = $comment_img;
        $add['addtime'] = time();
        $add['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $add['user_id'] = $this->user_id;
        $logic = new UserLogic();
        //添加评论
        $row = $logic->add_comment($add);
        exit(json_encode($row));
    }

    public function check_captcha() {
        $verify = new Verify();
        $type = I('post.type', 'user_login');
        if (!$verify->check(I('post.verify_code'), $type)) {
            exit(json_encode(0));
        } else {
            exit(json_encode(1));
        }
    }

    public function check_username() {
        $username = I('post.username');
        $map = array(
            'user_mail' => $username,
            'mail_check'=> 1,
        );
        if (!empty($username)) {
            $count = M('user')->where($map)->count();
            exit(json_encode(intval($count)));
        } else {
            exit(json_encode(0));
        }
    }

    public function identity() {
        if ($this->user_id > 0) {
            header("Location: " . U('Home/User/Index'));
        }
        $username = I('post.username');
        $userinfo = array();
        if ($username) {
            $userinfo = M('users')->where("email='$username' or mobile='$username'")->find();
            $userinfo['username'] = $username;
            session('userinfo', $userinfo);
        } else {
            $this->error('参数有误！！！');
        }
        if (empty($userinfo)) {
            $this->error('非法请求！！！');
        }
        unset($user_info['password']);
        $this->assign('userinfo', $userinfo);
        $this->display();
    }

    public function forget_pwd() {
        if ($this->user_id > 0) {
            header("Location: " . U('Home/User/Index'));
        }
        if (IS_POST) {
            $logic = new UsersLogic();
            $username = I('post.username');
            //$code = I('post.code');
            //$new_password = I('post.new_password');
            //$confirm_password = I('post.confirm_password');
            $pass = false;

            //检查是否手机找回
            if (check_mobile($username)) {
                if (!$user = get_user_info($username, 2))
                    $this->error('账号不存在');
                $check_code = $logic->sms_code_verify($username, $code, $this->session_id);
                if ($check_code['status'] != 1)
                    $this->error($check_code['msg']);
                $pass = true;
            }
            //检查是否邮箱
            if (check_email($username)) {
                if (!$user = get_user_info($username, 1))
                    $this->error('账号不存在');
                $check = session('forget_code');
                if (empty($check))
                    $this->error('非法操作');
                if (!$username || !$code || $check['email'] != $username || $check['code'] != $code)
                    $this->error('邮箱验证码不匹配');
                $pass = true;
            }
            if ($user['user_id'] > 0 && $pass)
                $data = $logic->password($user['user_id'], '', $new_password, $confirm_password, false); // 获取用户信息
            if ($data['status'] != 1)
                $this->error($data['msg'] ? $data['msg'] : '操作失败');
            $this->success($data['msg'], U('Home/User/login'));
            exit;
        }
        $this->display();
    }

    //发送验证码
    public function send_validate_code() {
        $type = I('type');
        $send = I('send');
        $logic = new UsersLogic();
        $res = $logic->send_validate_code($send, $type);
        $this->ajaxReturn($res);
    }

    public function check_validate_code() {
        $code = I('post.code');
        $send = I('send');
        $logic = new UsersLogic();
        $res = $logic->check_validate_code($code, $send);
        $this->ajaxReturn($res);
    }

    public function verify() {
        //验证码类型
        $type = I('get.type') ? I('get.type') : 'user_login';
        $config = array(
            'fontSize' => 40,
            'length' => 4,
            'useCurve' => true,
            'useNoise' => false,
        );
        $Verify = new Verify($config);
        $Verify->entry($type);
    }

    public function finished() {
        if ($this->user_id > 0) {
            header("Location: " . U('Home/User/Index'));
        }
        $this->display();
    }
    
    public function removeCollect() {
        $removeList = I('removeList');
        //var_dump($removeList);die;
        //$removeArr = explode(",",$removeList);
        $where['collect_id'] = array('in',$removeList);
        $data['status'] = 0;
        $res = M('user_activity')->where($where)->save($data);
        if($res){
            $return = array(
              'status' =>1,
                'msg' =>'批量删除成功',
            );
        }else{
            $return = array(
              'status' =>-1,
               'msg' =>'批量删除出错',
            );
        }
        
        exit(json_encode($return));
    }
    
    
    public function addRemind() {
        $collect_id = I('collect_id');
        $settime = I('settime');
        if(empty($collect_id)){
            $result =  array('status'=>-1,'msg'=>'缺少参数','result'=>'');
            exit(json_encode($result));
        }
        

        
                
        $avtivityLogic = new \Home\Logic\ActivityLogic();
        $res = $avtivityLogic->get_collect_info($collect_id);
        //var_dump($res['result']['acivity_id']);DIE;
        if($res['status'] == 1){//收藏存在

            $acivity = $avtivityLogic->get_activity_info($res['result']['activity_id']);//
            
            if (empty($settime)) {
                $user_settime = 3600;
            } else {
                $user_settime = strtotime($acivity['result']['startdate']) - strtotime($settime);

            }
            $user_activity = M('user_activity');
            //var_dump($acivity);DIE;
            $map = array(
                'collect_id' => $collect_id,
                'is_comet_remind' => 1,
                'comet_remind_time' => strtotime($acivity['result']['startdate'])-$user_settime,
                'is_remind' => 1,
                'remind_time' => strtotime($acivity['result']['startdate'])-$user_settime,

            );
            //var_dump(strtotime($acivity['result']['startdate']));DIE;
            $z = $user_activity->save($map);
            //var_dump($map);DIE;
            if($z){
               $result = array('status'=>1,'msg'=>'设置提醒成功','result'=>'');
            }else{
                $result =  array('status'=>-1,'msg'=>'设置失败','result'=>'');
            }

        }else{
            $result = array('status'=>-1,'msg'=>'收藏不存在','result'=>'');
        }
        
        exit(json_encode($result));
    }
    
    public function removeRemindArr() {
        $removeList = I('removeList');
        //var_dump($removeList);die;
        //$removeArr = explode(",",$removeList);
        $where['collect_id'] = array('in',$removeList);
        $data['is_remind'] = 0;
        $res = M('user_activity')->where($where)->save($data);
        if($res){
            $return = array(
              'status' =>1,
                'msg' =>'批量删除提醒成功',
            );
        }else{
            $return = array(
              'status' =>-1,
               'msg' =>'批量删除提醒出错',
            );
        }
        
        exit(json_encode($return));
    }
    
    public function removeRemind() {
        $collect_id = I('collect_id');
        if(empty($collect_id)){
            $result =  array('status'=>-1,'msg'=>'缺少参数','result'=>'');
            exit(json_encode($result));
        }
        
                
        $avtivityLogic = new \Home\Logic\ActivityLogic();
        $res = $avtivityLogic->get_collect_info($collect_id);
        //var_dump($res['result']['acivity_id']);DIE;
        if($res['status'] == 1){//收藏存在
            $acivity = $avtivityLogic->get_activity_info($res['result']['activity_id']);//
            $user_activity = M('user_activity');
            //var_dump($acivity);DIE;
            $map = array(
                'collect_id' => $collect_id,
                'is_comet_remind' => 0,
                'comet_remind_time' => '0',
                'is_remind' => 0,
                'remind_time' => '0',

            );
            //var_dump(strtotime($acivity['result']['startdate']));DIE;
            $z = $user_activity->save($map);
            //var_dump($map);DIE;
            if($z){
               $result = array('status'=>1,'msg'=>'取消提醒成功','result'=>'');
            }else{
                $result =  array('status'=>-1,'msg'=>'设置失败','result'=>'');
            }

        }else{
            $result = array('status'=>-1,'msg'=>'收藏不存在','result'=>'');
        }
        
        exit(json_encode($result));
    }
    
    public function cometRemind() {
//
//        $user_activity = M('user_activity');
//        $map = array(
//            'status' => 1,
//            'is_comet_remind' => 1,
//            'user_id' => $this->user_id,
//            
//        );
//        
//        $res = $user_activity->where($map)->find();
//        
//        while (!$res) { // 如果数据文件已经被修改
//            usleep(10000000); // 10s暂停 缓解CPU压力
//            clearstatcache(); //清除缓存信息
//            $res = $user_activity->where($map)->find();
//        }
//        //var_dump($res);die;
//        $user_activity->where('collect_id ='.$res['collect_id'])->setField('is_comet_remind',2);
//        // 返回json数组
//        $response = array();
//        $response['msg'] = 1;
//        //$response['timestamp'] = $currentmodif;
//        echo json_encode($response);
//        //$this->ajaxReturn($response,'ok',1);
//        flush();
//        
        set_time_limit(0);
        ob_start();
        echo str_repeat(' ',4000);
        //ob_flush();
        ob_end_flush();
        flush();
        while (true){
	// 查询用户给客服端发送的数据
        $user_activity = M('user_activity');
        $map = array(
            'status' => 1,
            'is_comet_remind' => 1,
            'user_id' => $this->user_id,
            
        );
        
        $res = $user_activity->where($map)->find();
	if ($res){
		// 将消息设置为已读
                $user_activity->where('collect_id ='.$res['collect_id'])->setField('is_comet_remind',2);
                $res['status'] = 1;
		$json = json_encode($res);
		echo '<script>parent.write_msg(\''.$json.'\');</script>';
		ob_flush();		// 推给apache
		flush();		// 推给浏览器
	}

	sleep(5);
}
    }
    
//    public function ajaxCollect($param) {
//        
//        //统计要查询数据的数量
//        $count = M('user_activity')->where(array('user_id'=>$user_id,'status'=>1))->count();
//        
//        //实例化分页类，传入三个参数，分别是数据总数、每页显示的数据条数、要调用的jQuery ajax方法名
//        $p=new Pageajax($count,8,'collect');
//        //产生分页信息
//        $show=$p->show();
//        //要查询的数据,limit表示每页查询的数量，这里为10条
//        $sql = "SELECT c.collect_id,c.add_time,c.is_remind,c.is_comet_remind,g.id,g.title,g.content,g.type,g.startdate,g.enddate FROM __PREFIX__user_activity c ".
//            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
//            "WHERE c.user_id = ".$user_id.
//            " ORDER BY c.add_time DESC LIMIT {$page->firstRow},{$page->listRows}";
//        $result = M()->query($sql);
//        //assign方法往模板赋值
//        $this->assign('list',$result);
//        $this->assign('page',$show);
//        //ajax返回信息
//        $res["content"] = $this->fetch('User/ajaxCollect');
//        $this->ajaxReturn($res);
//    }
    
        public function get_ajax_collect() {
            $is_ajax = I('is_ajax');
        $user_id = $this->user_id;
            
        $count = M('user_activity')->where(array('user_id'=>$user_id,'status'=>1))->count();
        $page = new AjaxPage($count,8,'show');
        $show = $page->show();
        //获取我的收藏列表
        $sql = "SELECT c.collect_id,c.add_time,c.activity_id,c.is_remind,c.is_comet_remind,g.id,g.title,g.content,g.type,g.startdate,g.enddate FROM __PREFIX__user_activity c ".
            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
            "WHERE  status = 1 and  c.user_id = ".$user_id.
            " ORDER BY c.add_time DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = M()->query($sql);
        $this->dealStrTime($result);
        $this->is_overDue($result);
        //var_dump($result);die;
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;

        $return['show'] = $show;
        if($is_ajax == 1){
            $return["content"] = $this->ajaxCollect($return['result']);
            $this->ajaxReturn($return);    
        }

        return $return;
    }
    
     function ajaxCollect($collect_list) {
         $this->assign('collect_list',$collect_list);
          return $this->fetch('User/ajaxCollect');

    }
    
    public function get_ajax_remind() {
            $is_ajax = I('is_ajax');
        $user_id = $this->user_id;
            $map = array(
                'user_id'=>$user_id,
                'status'=>1,
                'is_remind'=>array('neq',0),
                //'is_comet_remind'=>array('neq',0),
                );
        $count = M('user_activity')->where($map)->count();
        $page = new AjaxPage3($count,8,'show3');
        $show = $page->show();
        //获取我的收藏列表
        $sql = "SELECT c.collect_id,c.add_time,c.activity_id,c.is_remind,c.remind_time,c.is_comet_remind,g.id,g.title,g.content,g.type,g.startdate,g.enddate FROM __PREFIX__user_activity c ".
            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
            //"WHERE c.user_id = ".$user_id." and is_remind !=0 and is_comet_remind !=0 and status =1".
            "WHERE c.user_id = ".$user_id." and is_remind !=0 and status =1".
            " ORDER BY c.add_time DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = M()->query($sql);
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        
        $return['show'] = $show;
        if($is_ajax == 1){
            $return["content"] = $this->ajaxRemind($return['result']);
            $this->ajaxReturn($return);    
        }

        return $return;
    }
    
    function ajaxRemind($collect_list) {
         $this->assign('remind_list',$collect_list);
          return $this->fetch('User/ajaxRemind');

    }
    
    public function get_ajax_comment() {
        $is_ajax = I('is_ajax');
        $user_id = $this->user_id;
            
        $count = M('comment')->where(array('user_id'=>$user_id))->count();
        $page = new AjaxPage2($count,8,'show2');
        $show = $page->show();
        //获取我的收藏列表
        $sql = "SELECT c.id,c.createdate,c.comment,c.activity_id,g.title,g.content,g.type FROM __PREFIX__comment c ".
            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
            "WHERE c.user_id = ".$user_id.
            " ORDER BY c.addtime DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = M()->query($sql);
        $this->getAdminComment($result);
        //var_dump($result);die;
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        
        $return['show'] = $show;
        if($is_ajax == 1){
            $return["content"] = $this->ajaxComment($return['result']);
            $this->ajaxReturn($return);    
        }

        return $return;
    }
    
    public function getAdminComment(&$commentArr) {//获取管理员评论
            $comment = M('comment');
            foreach ($commentArr as $k => &$val) {
                $map = array(
                    'pid' => $val['id'],
                );
                $res = $comment->where($map)->find();
                //var_dump($res);
                if($res){
                    $val['is_reply'] = 1;
                    $val['reply_comment'] = $res['comment'];
                    $val['reply_time'] = $res['createdate'];
                }                          
            }
            return;
    }


    public function dealStrTime(&$Arr) {//处理startdate,enddate
        foreach ($Arr as $k => &$val) {
            //var_dump($res);
                $val['starttime'] =  substr($val['startdate'],0,10);
                $val['endtime'] =  substr($val['enddate'],0,9);
            }

        return;
    }

    public function is_overDue(&$Arr) {//处理startdate,enddate
        foreach ($Arr as $k => &$val) {
            //var_dump($res);
            if(strtotime($val['startdate'])<time()){
                $val['is_overdue'] =  1;
            }else{
                $val['is_overdue'] =  0;
            }

        }

        return;
    }
    
    function ajaxComment($comment_list) {
         $this->assign('comment_list',$comment_list);
          return $this->fetch('User/ajaxComment');

    }
    
    public function editInfo() {
        $user_relname = I('user_relname');
        $user_class = I('user_class');
        $return = array();
        if(empty($user_relname) || empty($user_class)){
            $return = array(
                'status' => -1,
                'msg' => "缺少参数",
            );
            exit(json_encode($return));
        }
        $map = array(
            'user_relname' => $user_relname,
            'user_class' => $user_class,
            'user_id'=> $this->user_id,
        );
        $user = M('user');
        $res = $user->save($map);
        //var_dump($res);die;
        
        if($res){
            $return = array(
                'status' => 1,
                'msg' => "编辑成功",
            );
        }else{
            $return = array(
                'status' => -1,
                'msg' => "编辑失败",
            );
        }
        
        exit(json_encode($return));
        
    }

    /**
     * 用户收藏某一件事件
     * @param type $activity_id
     */
    public function collect_activity($activity_id)
    {
        $activity_id = I('activity_id');
        $activityLogic = new \Home\Logic\ActivityLogic();
        $result = $activityLogic->collect_activity($this->user_id,$activity_id);
        exit(json_encode($result));
    }
    
    
    public function mComment() {
        $comment_data = $this->m_get_ajax_comment();
        $this->assign('comment_page', $comment_data['show']); // 赋值分页输出
        $this->assign('comment_list', $comment_data['result']);
        $this->assign('comment_number', $comment_data['comment_number']);
        $this->display();
    }
    
    public function m_get_ajax_comment() {
        $is_ajax = I('is_ajax');
        $user_id = $this->user_id;
            
        $count = M('comment')->where(array('user_id'=>$user_id))->count();
        $page = new Page($count,8);
        $show = $page->show();
        //获取我的收藏列表
        $sql = "SELECT c.id,c.createdate,c.comment,c.activity_id,g.title,g.content,g.type FROM __PREFIX__comment c ".
            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
            "WHERE c.user_id = ".$user_id.
            " ORDER BY c.addtime DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = M()->query($sql);
        $this->getAdminComment($result);
        //var_dump($result);die;
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        $return['comment_number'] = $count;
        
        $return['show'] = $show;
        if($is_ajax == 1){
//            $data = $this->m_ajaxComment($return['result']);
            $this->assign('comment_list',$return['result']);
            $this->display('m_ajaxComment');
        }

        return $return;
    }
    
    function m_ajaxComment($comment_list) {
         $this->assign('comment_list',$comment_list);
          return $this->fetch('User/m_ajaxComment');

    }

    
    public function mCollect() {
        $collect_data = $this->m_get_ajax_collect();
        $this->assign('collect_page', $collect_data['show']); // 赋值分页输出
        $this->assign('collect_list', $collect_data['result']);
        //$this->assign('collect_number', $comment_data['comment_number']);
        $this->display();
    }
    
    
        public function m_get_ajax_collect() {
            $is_ajax = I('is_ajax');
        $user_id = $this->user_id;
            
        $count = M('user_activity')->where(array('user_id'=>$user_id,'status'=>1))->count();
        $page = new Page($count,8);
        $show = $page->show();
        //获取我的收藏列表
        $sql = "SELECT c.collect_id,c.add_time,c.activity_id,c.is_remind,c.is_comet_remind,g.id,g.title,g.content,g.address,g.type,g.startdate,g.enddate FROM __PREFIX__user_activity c ".
            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
            "WHERE status = 1 and c.user_id = ".$user_id.
            " ORDER BY c.add_time DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = M()->query($sql);
        $this->dealStrTime($result);
        $this->is_overDue($result);
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        
        $return['show'] = $show;
        if($is_ajax == 1){
//            $data = $this->m_ajaxComment($return['result']);
            $this->assign('collect_list',$return['result']);
            $this->display('m_ajaxCollect');
        }

        return $return;
    }
    
    
    public function mRemind() {
        $remind_data = $this->m_get_ajax_remind();
        $this->assign('remind_page', $remind_data['show']); // 赋值分页输出
        $this->assign('remind_list', $remind_data['result']);
        $this->display();
    }
    
    public function m_get_ajax_remind() {
        $is_ajax = I('is_ajax');
        $user_id = $this->user_id;
            $map = array(
                'user_id'=>$user_id,
                'status'=>1,
                'is_remind'=>array('neq',0),
                //'is_comet_remind'=>array('neq',0),
                );
        $count = M('user_activity')->where($map)->count();
        $page = new Page($count,8);
        $show = $page->show();
        //获取我的收藏列表
        $sql = "SELECT c.collect_id,c.add_time,c.activity_id,c.is_remind,c.remind_time,c.is_comet_remind,g.id,g.title,g.content,g.type,g.startdate,g.enddate FROM __PREFIX__user_activity c ".
            "inner JOIN __PREFIX__activity g ON g.id = c.activity_id ".
            //"WHERE c.user_id = ".$user_id." and is_remind !=0 and is_comet_remind !=0 and status =1".
            "WHERE c.user_id = ".$user_id." and is_remind !=0 and status =1".
            " ORDER BY c.add_time DESC LIMIT {$page->firstRow},{$page->listRows}";
        $result = M()->query($sql);
        //$this->getAdminComment($result);
        //var_dump($result);die;
        $return['status'] = 3;
        $return['msg'] = '获取成功';
        $return['result'] = $result;
        
        $return['show'] = $show;
        if($is_ajax == 1){
//            $data = $this->m_ajaxComment($return['result']);
            $this->assign('remind_list',$return['result']);
            $this->display('m_ajaxRemind');
        }else{
            return $return;
        }


    }
        
    public function mInfomation() {
         $this->display();
        
    }
}
