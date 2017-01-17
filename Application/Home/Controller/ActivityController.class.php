<?php
namespace Home\Controller;

use Home\Logic\CartLogic;
use Home\Logic\ActivityLogic;
use Think\AjaxPage;
use Think\Page;
use Think\Verify;
class ActivityController extends BaseController {
    public function index(){      
        $this->display();
    }

    /**
     * 事件咨询ajax分页
     */
    public function ajax_consult(){        
        $activity_id = I("activity_id",'0');        
        $consult_type = I('consult_type','0'); // 0全部咨询  1 事件咨询 2 支付咨询 3 配送 4 售后
                 
        $where = " parent_id = 0 and activity_id = $activity_id";
        if($consult_type > 0)
            $where .= " and consult_type = $consult_type ";
        
        $count = M('ActivityConsult')->where($where)->count();        
        $page = new AjaxPage($count,5);
        $show = $page->show();        
        $list = M('ActivityConsult')->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        $replyList = M('ActivityConsult')->where("parent_id > 0")->order("id desc")->select();
        
        $this->assign('consultCount',$count);// 事件咨询数量
        $this->assign('consultList',$list);// 事件咨询
        $this->assign('replyList',$replyList); // 管理员回复
        $this->assign('page',$show);// 赋值分页输出        
        $this->display();        
    }
    
    /**
     * 事件评论ajax分页
     */
    public function ajaxComment(){        
        $activity_id = I("activity_id",'0');        
        $commentType = I('commentType','1'); // 1 全部 2好评 3 中评 4差评
        if($commentType==5){
        	$where = "is_show = 1 and  activity_id = $activity_id and parent_id = 0 and img !='' ";
        }else{
        	$typeArr = array('1'=>'0,1,2,3,4,5','2'=>'4,5','3'=>'3','4'=>'0,1,2');
        	$where = "is_show = 1 and  activity_id = $activity_id and parent_id = 0 and ceil((deliver_rank + activity_rank + service_rank) / 3) in($typeArr[$commentType])";
        }
        $count = M('Comment')->where($where)->count();                
        
        $page = new AjaxPage($count,5);
        $show = $page->show();        
        $list = M('Comment')->where($where)->order("add_time desc")->limit($page->firstRow.','.$page->listRows)->select();
        $replyList = M('Comment')->where("is_show = 1 and  activity_id = $activity_id and parent_id > 0")->order("add_time desc")->select();
        
        foreach($list as $k => $v){
            $list[$k]['img'] = unserialize($v['img']); // 晒单图片            
        }        
        $this->assign('commentlist',$list);// 事件评论
        $this->assign('replyList',$replyList); // 管理员回复
        $this->assign('page',$show);// 赋值分页输出        
        $this->display();        
    }    
    

    
    /**
     * 用户收藏某一件事件
     * @param type $activity_id
     */
    public function collect_activity($activity_id)
    {
        $activity_id = I('activity_id');
        $activityLogic = new \Home\Logic\ActivityLogic();        
        $result = $activityLogic->collect_activity(cookie('user_id'),$activity_id);
        exit(json_encode($result));
    }
    

    /**
     * 事件搜索列表页
     */
    public function search()
    {
       //C('URL_MODEL',0);
        $filter_param = array(); // 帅选数组                        
        $id = I('get.id',0); // 当前分类id 
        $brand_id = I('brand_id',0);         
//        $sort = I('sort','goods_id'); // 排序
//        $sort_asc = I('sort_asc','asc'); // 排序
        $sort = 'addtime'; // 排序
        $sort_asc = 'desc'; // 排序
        $price = I('price',''); // 价钱
        
        $start_price = trim(I('start_price','0')); // 输入框价钱
        $end_price = trim(I('end_price','0')); // 输入框价钱
        if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱
        $q = urldecode(trim(I('q',''))); // 关键字搜索
        empty($q) && $this->error('请输入搜索词');


        $id && ($filter_param['id'] = $id); //加入帅选条件中                       
        $brand_id  && ($filter_param['brand_id'] = $brand_id); //加入帅选条件中        
        $price  && ($filter_param['price'] = $price); //加入帅选条件中
        $q  && ($_GET['q'] = $filter_param['q'] = $q); //加入帅选条件中
        
        $activityLogic = new \Home\Logic\ActivityLogic(); // 前台商品操作逻辑类
             
        $where1 = array(
            'title'=>array('like','%'.$q.'%'),
            'content'=>array('like','%'.$q.'%'),
            '_logic' => 'or'
        );
        
        $where = array(
            'status' => 1,
            '_complex' => $where1,
            '_logic' => 'and'
        );

        if($id)//事件分类id
        {
            $cat_id_arr = getCatGrandson ($id);
            $where['cat_id'] = array('in',implode(',', $cat_id_arr));
        }
        
        $search_goods = M('activity')->where($where)->getField('id,type_id');
        $filter_goods_id = array_keys($search_goods);
        $filter_cat_id = array_unique($search_goods); // 分类需要去重
        if($filter_cat_id)        
        {
            $cateArr = M('avtivitytype')->where("id in(".implode(',', $filter_cat_id).")")->select();            
            $tmp = $filter_param;
            foreach($cateArr as $k => $v)            
            {
                $tmp['id'] = $v['id'];
                $cateArr[$k]['href'] = U("/Home/Activity/search",$tmp);                            
            }                
        }                        
        // 过滤帅选的结果集里面找商品        
        if($brand_id || $price)// 品牌或者价格
        {
            $goods_id_1 = $activityLogic->getGoodsIdByBrandPrice($brand_id,$price); // 根据 品牌 或者 价格范围 查找所有商品id    
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_1); // 获取多个帅选条件的结果 的交集
        }
        
        $filter_menu  = $activityLogic->get_filter_menu($filter_param,'search'); // 获取显示的帅选菜单
        $filter_price = $activityLogic->get_filter_price($filter_goods_id,$filter_param,'search'); // 帅选的价格期间         
        $filter_brand = $activityLogic->get_filter_brand($filter_goods_id,$filter_param,'search',1); // 获取指定分类下的帅选品牌        
                                
        $count = count($filter_goods_id);
        $page = new Page($count,20);
        if($count > 0)
        {
            $goods_list = M('activity')->where("stauts=1 and id in (".  implode(',', $filter_goods_id).")")->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows)->select();
            $filter_goods_id2 = get_arr_column($goods_list, 'id');
            //if($filter_goods_id2)
            //$goods_images = M('goods_images')->where("goods_id in (".  implode(',', $filter_goods_id2).")")->select();       
        }    
                
        $this->assign('goods_list',$goods_list);  
        $this->assign('goods_images',$goods_images);  // 相册图片
        $this->assign('filter_menu',$filter_menu);  // 帅选菜单
        $this->assign('filter_brand',$filter_brand);  // 列表页帅选属性 - 商品品牌
        $this->assign('filter_price',$filter_price);// 帅选的价格期间
        $this->assign('cateArr',$cateArr);
        $this->assign('filter_param',$filter_param); // 帅选条件
        $this->assign('cat_id',$id);
        $this->assign('page',$page);// 赋值分页输出
        $this->assign('q',I('q'));
        C('TOKEN_ON',false);
        $this->display();
    }
    
    

    
}