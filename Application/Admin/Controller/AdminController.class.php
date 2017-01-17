<?php
namespace Admin\Controller;
class AdminController extends CommonController {
      
	  public function relogin(){
		   $this->display('relogin');
	  }
	  //后台登陆
      public function login(){
        $user = D("Admin");
        if(IS_POST){
            $old=I('post.');
            $old['password'] =md5($old['password']);
            $result = $user->where($old)->select();
            if(!empty($result)){
               session('login_user',$result);
                $this->display("/index");
            }else{
                $this->assign('login_error','用户名或密码错误');
                $this->display("relogin");
            }

        }else{
            $this->assign('error', $user->getError());
            $this->assign('old', I('post.'));
			$this->assign('login_error','用户名或密码错误');
            $this->display("relogin");
        }
    }


   //后台首页
	public function index(){
       $this->display("/index");
    }
   
    //管理员列表
	public function adminList(){
		$pageSize=20;
		$currentPage=1;
		$admin = D("Admin");
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
		$this->display("adminList");

	}

      //添加管理员信息
	  public function addAdmin(){
        $admin = D("Admin");
        if(IS_POST){
           if(!$admin->create()) {
			  $data=array(
				    "statusCode"=>"300",
					 "message"=>"用户名已经存在!",
					 "navTabId"=>"xmlb"
			   );
              $this->ajaxReturn($data);
           }else{
			   $admin->add();
			   $data=array(
				    "statusCode"=>"200",
					 "message"=>"添加成功!",
					 "navTabId"=>"xmlb",
					 "rel"=>"",
					 "callbackType"=>"closeCurrent"
			   );
			   $this->ajaxReturn($data);
           }
        }
    }

    //删除管理员
	public function delAdmin(){
	$param = I('get.');
    $admin = D("Admin");
	$result = $admin->delete($param['userid']);
		$data=array(
						 "statusCode"=>"200",
						 "message"=>"删除成功!",
						 "navTabId"=>"xmlb"
				   );
		$this->ajaxReturn($data);
	
	}



	//编辑管理员信息
	  public function editAdminView(){
        $admin = D("Admin");
		$param = I('get.');
		$userid=$param['userid'];
        $single = $admin->find($userid);
        $this->assign('result',$single);
		$this->display('edit');
    }


      //修改管理员信息
	  public function editAdmin(){
        $admin = D("Admin");
        if(IS_POST){
			$param = I("post.");
			$id=$param['id'];
            $username=$param['username'];
            $realname=$param['realname'];
            $type=$param['type'];
			$data=array(
				"id"=>$id,
			    "username"=>$username,
				"realname"=>$realname,
				"type"=>$type
			);
           if(!$admin->create()) {
			  $data=array(
				    "statusCode"=>"300",
					 "message"=>"用户名已经存在!",
					 "navTabId"=>"xmlb"
			   );
              $this->ajaxReturn($data);
           }else{
			   $admin->save($data);
			   $data=array(
				    "statusCode"=>"200",
					 "message"=>"修改成功!",
					 "navTabId"=>"xmlb",
					 "rel"=>"",
					 "callbackType"=>"closeCurrent"
			   );
			   $this->ajaxReturn($data);
           }
        }
    }

   //修改管理员密码--跳转页
	  public function editPassView(){
        $admin = D("Admin");
		$param = I('get.');
		$userid=$param['userid'];
        $single = $admin->find($userid);
        $this->assign('result',$single);
		$this->display('editpass');
    }

      //修改管理员密码
	  public function editPass(){
        $admin = D("Admin");
        if(IS_POST){
			$param = I("post.");
			$id=$param['id'];
            $password=$param['newpassword'];
			$data=array(
				"id"=>$id,
			    "password"=>$password
			);
           if(!$admin->create()) {
			  $data=array(
				    "statusCode"=>"300",
					 "message"=>"修改失败!",
					 "navTabId"=>"xmlb"
			   );
              $this->ajaxReturn($data);
           }else{
			   $admin->save($data);
			   $data=array(
				    "statusCode"=>"200",
					 "message"=>"修改成功!",
					 "navTabId"=>"xmlb",
					 "rel"=>"",
					 "callbackType"=>"closeCurrent"
			   );
			   $this->ajaxReturn($data);
           }
        }
    }





	 //管理员修改自己的密码--跳转页
	  public function modPassView(){
        $admin = D("Admin");
		$param = I('get.');
		$userid=$param['userid'];
        $single = $admin->find($userid);
        $this->assign('result',$single);
		$this->display('modpass');
    }

      //管理员修改自己的密码
	  public function modPass(){
        $admin = D("Admin");
		$result=session('login_user');
        if(IS_POST){
			$param = I("post.");
			$id=$result[0]['id'];
            $password=md5($param['newpassword']);
			$data=array(
				"id"=>$id,
			    "password"=>$password
			);

			 $rs = $admin->save($data);
           if(false !== $rs) {
			    $data=array(
				    "statusCode"=>"200",
					 "message"=>"修改成功!",
					 "navTabId"=>"xmlb",
					 "callbackType"=>"forward",
					 "forwardUrl"=>"Admin/relogin"
			   );
			   session(null);
              $this->ajaxReturn($data);
           }else{
			    $data=array(
				    "statusCode"=>"300",
					 "message"=>"修改失败!",
					 "navTabId"=>"xmlb"
			   );
			  $this->ajaxReturn($data);
				
                
           }
        }
    }

 //退出
 public function loginout(){
	 session(null);
   $this->display('relogin');
  } 
}

?>