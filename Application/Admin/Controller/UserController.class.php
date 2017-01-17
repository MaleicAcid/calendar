<?php
namespace Admin\Controller;

class UserController extends CommonController {
   
    //用户列表
	public function userList(){
		$pageSize=20;
		$currentPage=1;
		$admin = D("User");
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
		$result = $admin->getUserList($where,$currentPage,$pageSize);//
        $totalPage=intval(($totalCount+$pageSize -1)/$pageSize);
		$this->assign("result",$result);
		$this->assign("totalCount",$totalCount);
		$this->assign("totalPage",$totalPage);
		$this->assign("pageSize",$pageSize);
		$this->assign("currentPage",$currentPage);
		$this->assign("username",$username);
		$this->display("userlist");

	}

      

    //删除用户
	public function delUser(){
	$param = I('get.');
    $admin = D("User");
	$result = $admin->delete($param['userid']);
		$data=array(
						 "statusCode"=>"200",
						 "message"=>"删除成功!",
						 "navTabId"=>"xmlb"
				   );
		$this->ajaxReturn($data);
	
	}
  
 
   //禁止评论
	public function noComment(){
	$param = I('get.');
    $admin = D("User");
	$data= array(
    "user_id"=>$param['userid'],
	"is_comment"=>0
	);
	$result = $admin->save($data);
		$back=array(
						 "statusCode"=>"200",
						 "message"=>"禁用成功!",
						 "navTabId"=>"pllb"
				   );
		$this->ajaxReturn($back);
	
	}


	//开启评论
	public function okComment(){
	$param = I('get.');
    $admin = D("User");
	$data= array(
    "user_id"=>$param['userid'],
	"is_comment"=>1
	);
	$result = $admin->save($data);
		$back=array(
						 "statusCode"=>"200",
						 "message"=>"启用成功!",
						 "navTabId"=>"pllb"
				   );
		$this->ajaxReturn($back);
	
	}
       

}

?>