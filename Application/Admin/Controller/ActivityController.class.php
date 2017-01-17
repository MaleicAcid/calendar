<?php
namespace Admin\Controller;


class ActivityController extends CommonController {
    /**
     * 后台获取 活动列表
     */
    public function  activityList(){
        $pageSize=20;
        $currentPage=1;
        $activity = D("Activity");
        $param = I("post.");
        $title=$param["title"];
        if($param['page']>1){
            $currentPage=$param['page'];
        }
        $where = "1=1";
        if($title != ""){
            $where = 'title like '.'\'%'.$title.'%\'';
        }
        $totalCount = $activity->getCount($where);
        $result = $activity->getUserList($where,$currentPage,$pageSize);//
        $totalPage=intval(($totalCount+$pageSize -1)/$pageSize);
        $this->assign("result",$result);
        $this->assign("totalCount",$totalCount);
        $this->assign("totalPage",$totalPage);
        $this->assign("pageSize",$pageSize);
        $this->assign("currentPage",$currentPage);
        $this->assign("title",$title);
        $this->display("activitylist");
    }


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
        if($type != NUll){
            $where = 'type ='.$type;
        }
        $result = $activity->countMonthActivity($where);
       if($type==NULL){
           $type="";
       }
       $data = M()->query("call sp_get_level('$startDate','$endDate','$type')");
       $finalresult = array(
           'count'=>$result,
           'data'=>$data
       );
        $this->ajaxReturn($finalresult);

    }

    /**
     * 跳转到 add页面
     */
    public function add(){
        $activitytype = D("Activitytype");
        $color = D("Color");
        $aResult = $activitytype->select();
        $cResult = $color->select();
        $this->assign('typelist',$aResult);
        $this->assign('colorlist',$cResult);
        $this->display();
    }

    public function addActivity(){
        $activity = D("Activity");

        if(IS_POST){

            if(!$activity->create()) {
                $data=array(
                    "statusCode"=>"300",
                    "message"=>"用户名已经存在!",
                    "navTabId"=>"hdlb"
                );
                $this->ajaxReturn($data);
            }else{
                $param = I("post.");
                $activitydate = $param['startdate'];
                $activity->activitydate=substr($activitydate,0,10);

                $activity->add();
                $data=array(
                    "statusCode"=>"200",
                    "message"=>"添加成功!",
                    "navTabId"=>"hdlb",
                    "rel"=>"",
                    "callbackType"=>"closeCurrent"
                );
                $this->ajaxReturn($data);
            }
        }
    }
    /*
     * 删除活动
     */
    public function delActivity(){
        $param = I('get.');
        $activity = D("Activity");
        $result = $activity->delete($param['activityid']);
        $data=array(
            "statusCode"=>"200",
            "message"=>"删除成功!",
            "navTabId"=>"hdlb"
        );
        $this->ajaxReturn($data);
    }


    //编辑活动信息
    public function editActivityView(){
        $activity = D("Activity");
        $param = I('get.');
        $activityid=$param['activityid'];
        $single = $activity->find($activityid);

        $activitytype = D("Activitytype");
        $color = D("Color");
        $aResult = $activitytype->select();
        $cResult = $color->select();
        $this->assign('typelist',$aResult);
        $this->assign('colorlist',$cResult);

        $this->assign('result',$single);
        $this->display('edit');
    }
    //修改活动信息
    public function editActivity(){
        $activity = D("Activity");
        if(IS_POST){
            $param = I("post.");
            $id=$param['id'];
            $content=$param['content'];
            $title=$param['title'];
            $startdate=$param['startdate'];
            $enddate=$param['enddate'];
            $level=$param['level'];
            $color=$param['color'];
            $unit=$param['unit'];
            $people=$param['people'];
            $address=$param['address'];
            $telephone=$param['telephone'];
            $type=$param['type'];
            $activitydate=substr($startdate,0,10);
            $data=array(
                "id"=>$id,
                "content"=>$content,
                "title"=>$title,
                "startdate"=>$startdate,
                "enddate"=>$enddate,
                "level"=>$level,
                "color"=>$color,
                "unit"=>$unit,
                "people"=>$people,
                "address"=>$address,
                "telephone"=>$telephone,
                "type"=>$type,
                "activitydate"=>$activitydate

            );
            if(!$activity->create()) {
                $data=array(
                    "statusCode"=>"300",
                    "message"=>"保存异常!",
                    "navTabId"=>"hdlb"
                );
                $this->ajaxReturn($data);
            }else{
                $activity->save($data);
                $data=array(
                    "statusCode"=>"200",
                    "message"=>"修改成功!",
                    "navTabId"=>"hdlb",
                    "rel"=>"",
                    "callbackType"=>"closeCurrent"
                );
                $this->ajaxReturn($data);
            }
        }
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
        if($param['pageSize']>0){
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

}