<?php
namespace Admin\Controller;

class ColorController extends CommonController {
    
   
    //管理员列表
	public function colorList(){
		$pageSize=20;
		$currentPage=1;
		$admin = D("Color");
		$param = I("post.");
		$username=$param["color"];
		if($param['page']>1){
		$currentPage=$param['page'];
		}
		$where = "1=1";
		if($username != ""){
         $where = 'color like '.'\'%'.$username.'%\'';
		}
		$totalCount = $admin->getCount($where);
		$result = $admin->getColorList($where,$currentPage,$pageSize);//
        $totalPage=intval(($totalCount+$pageSize -1)/$pageSize);
		$this->assign("result",$result);
		$this->assign("totalCount",$totalCount);
		$this->assign("totalPage",$totalPage);
		$this->assign("pageSize",$pageSize);
		$this->assign("currentPage",$currentPage);
		$this->assign("username",$username);
		$this->display("colorList");

	}

      //添加管理员信息
	  public function addColor(){
        $color = D("Color");
        if(IS_POST){
           if(!$color->create()) {
			  $data=array(
				    "statusCode"=>"300",
					 "message"=>"颜色已经存在!",
					 "navTabId"=>"yslb"
			   );
              $this->ajaxReturn($data);
           }else{
			   $color->add();
			   $data=array(
				    "statusCode"=>"200",
					 "message"=>"添加成功!",
					 "navTabId"=>"yslb",
					 "rel"=>"",
					 "callbackType"=>"closeCurrent"
			   );
			   $this->ajaxReturn($data);
           }
        }
    }

    //删除颜色
	public function delColor(){
	$param = I('get.');
    $color = D("Color");
	$result = $color->delete($param['id']);
		$data=array(
						 "statusCode"=>"200",
						 "message"=>"删除成功!",
						 "navTabId"=>"yslb"
				   );
		$this->ajaxReturn($data);
	
	}



	//编辑颜色
	  public function editColorView(){
        $color = D("Color");
		$param = I('get.');
		$id=$param['id'];
        $single = $color->find($id);
        $this->assign('result',$single);
		$this->display('edit');
    }


      //修改颜色
	  public function editColor(){
        $color = D("Color");
        if(IS_POST){
			$param = I("post.");
			$id=$param['id'];
            $col=$param['color'];
            $colorname=$param['colorname'];
			$data=array(
				"id"=>$id,
			    "color"=>$col,
                "colorname"=>$colorname
			);
           if(!$color->create()) {
			  $data=array(
				    "statusCode"=>"300",
					 "message"=>"颜色已经存在!",
					 "navTabId"=>"yslb"
			   );
              $this->ajaxReturn($data);
           }else{
			   $color->save($data);
			   $data=array(
				    "statusCode"=>"200",
					 "message"=>"修改成功!",
					 "navTabId"=>"yslb",
					 "rel"=>"",
					 "callbackType"=>"closeCurrent"
			   );
			   $this->ajaxReturn($data);
           }
        }
    }

}

?>