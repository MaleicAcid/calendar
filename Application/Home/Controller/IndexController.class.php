<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {


    public function index() {
//        $a = array("h1", "h2", "h3");
//        $b = array("c1", "c2", "c3");
//
//        foreach ($a as $key => $value) {
//            echo $value . $b[$key];
//        }

        $this->display();

    }


    public function login(){
        $user = D("User");
        if(IS_POST){
            $old=I('post.');
            $old['password'] =md5($old['password']);
            $result = $user->where($old)->select();

            if(!empty($result)){
                $this->assign('result', $result);
                session('user',$result);

                $this->display("login_success");
            }else{

                $this->assign('login_error','用户名或密码错误');
                $this->display("index");
            }


        }else{
            $this->assign('error', $user->getError());
            $this->assign('old', I('post.'));
            $this->display("index");
            $this->display("login_success");
        }
    }
    public function register(){
        $user = D("User");
        if(IS_POST){
           if($user->create() && $user->add()) {
               $this->assign('success', success);
               $this->display("login_success");
           }else{

               $this->assign('error', $user->getError());
               $this->assign('old', I('post.'));
               $this->display("index");
           }
        }
    }

    public function remindAction() {

            $pass = I('pass');
        if ($pass != C('AUTH_CODE') && !IS_CLI) {
            die;
        }
        ini_set('max_execution_time', '57');
        $preTime=time()-40;
        $behTime = time()+40;
        $userActivity = M('user_activity');
        $where = array(
            'status'=> 1,
          'is_remind'=> 1,
          'remind_time' => array('between',array($preTime,$behTime)),
          //'remind_time' => array('EGT',$preTime),
        );
        $fieldSql = 'collect_id,user_id,activity_id,remind_time';

        $remindInfo = $userActivity->where($where)->field($fieldSql)->select();
        if(!$remindInfo){
            echo "no";
            die;
        }else{
            //var_dump($remindInfo);
            foreach ($remindInfo as $k => &$val) {
                $val['user_mail'] = getUserMail($val['user_id']);
                $val['remind_time'] = getRemindTime($val['remind_time'],$val['activity_id']);
                $val['activity_url'] = getActivityUrl($val['activity_id']);
                $val['activity_title'] = getActivityTitle($val['activity_id']);
                unset($val['user_id']);
                //unset($val['remind_time']);
                unset($val['activity_id']);
            }
            //var_dump($remindInfo);die;
            $res = $this->sendRemindMail($remindInfo);

            foreach ($remindInfo as $key => &$val) {
                if($res[$key] == true){
                    $val['is_remind'] = 2;//2标识发送成功

                    unset($val['user_mail']);
                    unset($val['remind_time']);
                    unset($val['activity_url']);
                    unset($val['activity_title']);
                }else{
                    $val['is_remind'] = 3;//3标识发送失败
                    unset($val['user_mail']);
                    unset($val['remind_time']);
                    unset($val['activity_url']);
                    unset($val['activity_title']);
                }
            }
            //var_dump($remindInfo);die;

            foreach($remindInfo as $key => &$val){
                //var_dump($key);
                $saveRes = $userActivity->save($val);
                var_dump($saveRes);
            }
            //$saveRes = $userActivity->save($remindInfo); // 根据条件更新记录
            die;

        }

    }

    public function sendRemindMail($remindInfo) {
        $res =array();$i=0;
        foreach ($remindInfo as $k => $val) {
            //$to, $title, $content
            $title = '大连海事大学——您收藏的事件['.$val['activity_title']."]即将开始";
            $content = "尊敬的用户,您收藏的事件[<a href = ".$val['activity_url'].">".$val['activity_title']."</a>]".","."<a href = ".$val['activity_url'].">"."地址:".$val['activity_url']."</a>"."</br>"."即将于".$val['remind_time']."开始，请注意参加";
            $res[$i++] = sendMail($val['user_mail'],$title,$content);
        }
        return $res;
    }

    public function sendBrowserRemind($param) {

    }
}