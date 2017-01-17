<?php
/**
 * Created by PhpStorm.
 * User: chenchenghao
 * Date: 2016/12/20
 * Time: 21:30
 */
namespace Admin\Controller;
use Think\Controller;

class FrontController extends Controller{


    /**
     * 获得一段时间内的活动
     */
    public function getMonthActivity(){
        $param = I("post.");
        $startDate = $param['startdate'];
        $endDate = $param['enddate'];
        $type = $param['type'];

        $activity = D("Activity");
        $where = "1=1";
        $Date =  date('Y-m');
        if($type != NUll){
            $where = 'activitydate like '.'\'%'.$Date.'%\''.' and type ='.$type;

        }
        if($type==NULL){
            $where = 'activitydate like '.'\'%'.$Date.'%\'';
            $type="";
        }
        $result = $activity->countMonthActivity($where);



        $data = M()->query("call sp_get_level('$startDate','$endDate','$type')");
        $finalresult = array(
            'count'=>$result,
            'data'=>$data
        );
        $this->ajaxReturn($finalresult);

    }

    //获得按日活动
    public function getDayActivity(){
        $pageSize=10;
        $currentPage=1;
        $param = I("post.");
        $startDate = $param['startdate'];
        if($startDate==null){
            $startDate =  date('Y-m-d');
        }
        $where = 'activitydate =\''.$startDate.'\'';
        if($param['page']>1){
            $currentPage=$param['page'];
        }
        if($param['pageSize']>1){
            $pageSize=$param['pageSize'];
        }
        $activity = D("Activity");
        $result = $activity->getDayActivity($where,$currentPage,$pageSize);
        $totalCount = $activity->getDayCount($where);
        $finalresult = array(
            'data'=>$result,
            'count'=>$totalCount
        );
        $this->ajaxReturn($finalresult);
    }
    //获得按年活动
    public function  getYearActivity(){
        $param = I("post.");
        $startDate = $param['startdate'];
        if($startDate==null){
            $startDate =  date('Y');
        }
        if($startDate != ""){
            $where = 'activitydate like '.'\'%'.$startDate.'%\'';
        }
        $activity = D("Activity");
        $result = $activity->getYearActivity($where);
        $finalresult = array(
            'data'=>$result
        );
        $this->ajaxReturn($finalresult);
    }

    //文章阅读
    public function read(){
        $param = I("post.");
        $id = $param['id'];
        $activity = D("Activity");
        $result = $activity->find($id);
        $result['readcount']=$result['readcount']+1;
        $activity->save($result);
        $finalresult = array(
            'success'=>true
        );
        $this->ajaxReturn($finalresult);

    }
    //查看文章详情
    public function activitydet(){
        $param = I("get.");
        $id = $param['id'];
        if(empty($id)){
            header("location:" . U('Admin/Front/rili'));
            return;
        }
        if(isMobile()){
            header("location:" . U('Admin/Front/activitydetm',array('id'=>$id)));
        }

        $activity = D("Activity");
        $result = $activity->find($id);
        $this->assign("result",$result);
        //评论最新列表
		$comment = D("Comment");
        $where = 'activity_id='.$id;
        $rs = $comment->getCommentByActivityId($where);
        $activity=D("Activity");
		$ac=$activity->find($id);
		//var_dump($ac);die;
		$readcount=$ac['readcount']+1;
		$ac['readcount']=$readcount;
        $activity->save($ac);

        $user = session('user');
        $user_id = $user['user_id'];
        $userActivity = D("UserActivity");
        if($user_id==null){
            $UserWhere =' activity_id='.$id;
        }else{
            $UserWhere ='user_id ='.$user_id.' and activity_id='.$id;
        }
        $userActivityResult = $userActivity->getUserActivity($UserWhere);

        if($userActivityResult==null){
            $this->assign("status",0);
        }else{
            $this->assign("status",$userActivityResult['status']);
        }

		 $this->assign("rs",$rs);
        $this->display("more");
    }
    //获取热门活动
    public function  getHotActivity(){
       $startDate=date('Y-m');
        $activity = D("Activity");
        $where = 'activitydate like '.'\'%'.$startDate.'%\'';
        $result = $activity->getHotActivity($where);
        $this->ajaxReturn($result);
    }

    //获取指定活动的评论
    public function getCommentByActivityId(){
        $param = I("get.");
        $activityid = $param['activityid'];
        $comment = D("Comment");
        $where = 'activity_id='.$activityid;
        $result = $comment->getCommentByActivityId($where);
        $this->ajaxReturn($result);
    }

     //前台页面添加评论
    public function addComment(){
        $param = I("post.");
        $activityid = $param['activity_id'];
        $comment = D("Comment");
		$user = session('user');
		$user_id = $user['user_id'];
		$username = $user['username'];
		if(!$user){
		 $this->redirect('Home/User/login', 0);
		 exit;
		}
			$data['user_id']=$user_id;
			$data['activity_id']=$param['activity_id'];
			$data['ip_address']=get_client_ip();
			$data['comment']=$param['comment'];
			$data['username']=$username;
		    $data['createdate']=date('Y-m-d H:i:s');
        $result = $comment->data($data)->add();
		//增加评论次数
        $activity=D("Activity");
		$ac=$activity->find($activityid);
		$commentcount=$ac['commentcount']+1;
		$ac['commentcount']=$commentcount;
        $activity->save($ac);

        $this->redirect('Front/activitydet', array('id' => $activityid), 0);
    }


    //获得右上角活动
    public function  getRightMonthActivity(){
        $param = I("post.");
        $startDate = $param['startdate'];
        if($startDate==null){
            $startDate =  date('Y-m');
        }
        if($startDate != ""){
            $where = 'activitydate like '.'\'%'.$startDate.'%\'';
        }
        $activity = D("Activity");
        $result = $activity->getYearActivity($where);
        $finalresult = array(
            'data'=>$result
        );
        $this->ajaxReturn($finalresult);
    }


   //手机端--查看文章详情
    public function activitydetm(){
        $param = I("get.");
        $id = $param['id'];
        $activity = D("Activity");
        $result = $activity->find($id);
        $this->assign("result",$result);
        //评论最新列表
		$comment = D("Comment");
        $where = 'activity_id='.$id;
        $rs = $comment->getCommentByActivityId($where);
        $activity=D("Activity");
		$ac=$activity->find($id);
		$readcount=$ac['readcount']+1;
		$ac['readcount']=$readcount;
        $activity->save($ac);

        $user = session('user');
        $user_id = $user['user_id'];
        $userActivity = D("UserActivity");
        if($user_id==null){
            $UserWhere =' activity_id='.$id;
        }else{
            $UserWhere ='user_id ='.$user_id.' and activity_id='.$id;
        }
        $userActivityResult = $userActivity->getUserActivity($UserWhere);

        if($userActivityResult==null){
            $this->assign("status",0);
        }else{
            $this->assign("status",$userActivityResult['status']);
        }

        $this->assign("rs",$rs);
        $this->display("more_m");
    }

    //手机端--前台页面添加评论
    public function addCommentm(){
        $param = I("post.");
        $activityid = $param['activity_id'];

        $comment = D("Comment");
		$user = session('user');
		$user_id = $user['user_id'];
		$username = $user['username'];
		if(!$user){
            $this->redirect('Home/User/login', 0);
		 exit;
		}
			$data['user_id']=$user_id;
			$data['activity_id']=$param['activity_id'];
			$data['ip_address']=get_client_ip();
			$data['comment']=$param['comment'];
			$data['username']=$username;
		    $data['createdate']=date('Y-m-d h:i:s');
        $result = $comment->data($data)->add();
		//增加评论次数
        $activity=D("Activity");
		$ac=$activity->find($activityid);
		$commentcount=$ac['commentcount']+1;
		$ac['commentcount']=$commentcount;
        $activity->save($ac);

        $this->redirect('Front/activitydetm', array('id' => $activityid), 0);
    }
   
   
   //活动关键字模糊搜索列表
    public function  getActivityByKeyword(){
        $param = I("get.");
        $keyword = $param['keyword'];
        if($keyword != ""){
            $where = 'title like '.'\'%'.$keyword.'%\'';
        }
        $activity = D("Activity");
        $result = $activity->getActivityByKeyword($where);
		$this->assign("result",$result);
		if(isMobile()){
        $this->display("search_m");
		}else{
		 $this->display("search");
		}
    }


    public function rili(){
        if(isMobile()){
            $this->display("rili_m");
        }else{
            $this->display("rili");
        }
    }

    public function sendemail(){}
}
?>