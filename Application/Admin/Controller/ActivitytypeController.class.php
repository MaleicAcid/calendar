<?php
namespace Admin\Controller;
class ActivityTypeController extends CommonController {
  
    //活动类别
	public function activityTypeList(){
		$pageSize=20;
		$currentPage=1;
		$admin = D("Activitytype");
		$param = I("post.");
        $type=$param["type"];
		if($param['page']>1){
		$currentPage=$param['page'];
		}
		$where = "1=1";
		if($type != ""){
         $where = 'type like '.'\'%'.$type.'%\'';
		}
		$totalCount = $admin->getCount($where);
		$result = $admin->getActivitytypeList($where,$currentPage,$pageSize);//
        $totalPage=intval(($totalCount+$pageSize -1)/$pageSize);
		$this->assign("result",$result);
		$this->assign("totalCount",$totalCount);
		$this->assign("totalPage",$totalPage);
		$this->assign("pageSize",$pageSize);
		$this->assign("currentPage",$currentPage);
		$this->assign("type",$type);
		$this->display("activitytypelist");

	}


    //删除评论
	public function delActivityType(){
	$param = I('get.');
    $admin = D("Activitytype");
	$result = $admin->delete($param['id']);
		$data=array(
						 "statusCode"=>"200",
						 "message"=>"删除成功!",
						 "navTabId"=>"lblb"
				   );
		$this->ajaxReturn($data);
	
	}

    //添加活动类别
    public function addActivityType(){
        $admin = D("Activitytype");
        $result=session('login_user');
        $creator = $result[0]['username'];
        if(IS_POST){
            if(!$admin->create()) {
                $data=array(
                    "statusCode"=>"300",
                    "message"=>"用户名已经存在!",
                    "navTabId"=>"lblb"
                );
                $this->ajaxReturn($data);
            }else{
                $admin->creator=$creator;
                $admin->add();
                $data=array(
                    "statusCode"=>"200",
                    "message"=>"添加成功!",
                    "navTabId"=>"lblb",
                    "rel"=>"",
                    "callbackType"=>"closeCurrent"
                );
                $this->ajaxReturn($data);
            }
        }
    }

}

?>