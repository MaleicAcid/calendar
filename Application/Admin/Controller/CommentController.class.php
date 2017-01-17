<?php
namespace Admin\Controller;
class CommentController extends CommonController {
  
    //评论列表
	public function commentList(){
		$pageSize=20;
		$currentPage=1;
		$admin = D("Comment");
		$param = I("post.");
		$username=$param["username"];
		if($param['page']>1){
		$currentPage=$param['page'];
		}
		$where = "1=1";
		if($username != ""){
         $where = 'username like '.'\'%'.$username.'%\'';
		}
		$totalCount = $admin->getCount($where);
		$result = $admin->getCommentList($where,$currentPage,$pageSize);//
        $totalPage=intval(($totalCount+$pageSize -1)/$pageSize);
		$this->assign("result",$result);
		$this->assign("totalCount",$totalCount);
		$this->assign("totalPage",$totalPage);
		$this->assign("pageSize",$pageSize);
		$this->assign("currentPage",$currentPage);
		$this->assign("username",$username);
		$this->display("commentlist");

	}


    //删除评论
	public function delComment(){
	$param = I('get.');
    $admin = D("Comment");
	$result = $admin->delete($param['id']);
		$data=array(
						 "statusCode"=>"200",
						 "message"=>"删除成功!",
						 "navTabId"=>"pllb"
				   );
		$this->ajaxReturn($data);
	
	}

   //后台管理员回复
   public function addCommentView(){
        $comment = D("Comment");
		$param = I("get.");
		$result=$comment->find($param['id']);
		$this->assign("result",$result);
        $this->display("add");
   }


 //删除评论
	public function addComment(){
	$param = I('post.');
    $comment = D("Comment");
	$session = session('login_user');
    $username=$session[0]['username'];
    
	$data = array(
	 "ip_address"=>get_client_ip(),
	 "createdate"=>date("Y-m-d h:i:s"),
	 "user_id"=>0,
	 "username"=>$username,
	 "activity_id"=>$param['activity_id'],
	 "pid"=>$param['pid'],
     "comment"=>$param['comment']
	);

	$result = $comment->add($data);
     if($result !== false){
		   $back=array(
				    "statusCode"=>"200",
					 "message"=>"添加成功!",
					 "navTabId"=>"pllb",
					 "rel"=>"",
					 "callbackType"=>"closeCurrent"
			   );
			   $this->ajaxReturn($back);
	 }else{

		 $back=array(
				    "statusCode"=>"300",
					 "message"=>"回复失败!",
					 "navTabId"=>"pllb"
			   );
		$this->ajaxReturn($back);
	 }
		
	}

}

?>