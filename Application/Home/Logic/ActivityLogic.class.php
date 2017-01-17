<?php

namespace Home\Logic;

use Think\Model\RelationModel;

/**
 * 分类逻辑定义
 * Class CatsLogic
 * @package Home\Logic
 */
class ActivityLogic extends RelationModel
{

    /**
     * @param array $activity_id_arr
     * @param $filter_param
     * @param $action
     * @param int $mode 0  返回数组形式  1 直接返回result
     * @return array
     * 获取事件列表页帅选属性
     */
    public function get_filter_attr($activity_id_arr = array(), $filter_param, $action, $mode = 0)
    {
        $activity_id_str = implode(',', $activity_id_arr);
        $activity_id_str = $activity_id_str ? $activity_id_str : '0';
        $activity_attr = M('activity_attr')->where("activity_id in($activity_id_str) and attr_value != ''")->select();
        // $activity_attr = M('activity_attr')->where("attr_value != ''")->select();
        $activity_attribute = M('activity_attribute')->getField('attr_id,attr_name,attr_index');
        if (empty($activity_attr)) {
            if ($mode == 1) return array();
            return array('status' => 1, 'msg' => '', 'result' => array());
        }
        $list_attr = $attr_value_arr = array();
        $old_attr = $filter_param['attr'];
        foreach ($activity_attr as $k => $v) {
            // 存在的帅选不再显示
            if (strpos($old_attr, $v['attr_id'] . '_') === 0 || strpos($old_attr, '@' . $v['attr_id'] . '_'))
                continue;
            if ($activity_attribute[$v['attr_id']]['attr_index'] == 0)
                continue;
            $v['attr_value'] = trim($v['attr_value']);
            // 如果同一个属性id 的属性值存储过了 就不再存贮
            if (in_array($v['attr_id'] . '_' . $v['attr_value'], $attr_value_arr[$v['attr_id']]))
                continue;
            $attr_value_arr[$v['attr_id']][] = $v['attr_id'] . '_' . $v['attr_value'];

            $list_attr[$v['attr_id']]['attr_id'] = $v['attr_id'];
            $list_attr[$v['attr_id']]['attr_name'] = $activity_attribute[$v['attr_id']]['attr_name'];

            // 帅选参数
            if (!empty($old_attr))
                $filter_param['attr'] = $old_attr . '@' . $v['attr_id'] . '_' . $v['attr_value'];
            else
                $filter_param['attr'] = $v['attr_id'] . '_' . $v['attr_value'];

            $list_attr[$v['attr_id']]['attr_value'][] = array('key' => $v['attr_id'], 'val' => $v['attr_value'], 'attr_value' => $v['attr_value'], 'href' => urldecode(U("Activity/$action", $filter_param, '')));
            //unset($filter_param['attr_id_'.$v['attr_id']]);
        }
        if ($mode == 1) return $list_attr;
        return array('status' => 1, 'msg' => '', 'result' => $list_attr);
    }



    /**
     * 获取某个事件的评论统计
     * 全部评论数  好评数 中评数  差评数
     */
    public function commentStatistics($activity_id)
    {
        $c1 = M('Comment')->where("is_show = 1 and  activity_id = $activity_id and parent_id = 0 and  ceil((deliver_rank + activity_rank + service_rank) / 3) in(4,5)")->count(); // 好评
        $c2 = M('Comment')->where("is_show = 1 and  activity_id = $activity_id and parent_id = 0 and  ceil((deliver_rank + activity_rank + service_rank) / 3) in(3)")->count(); // 中评
        $c3 = M('Comment')->where("is_show = 1 and  activity_id = $activity_id and parent_id = 0 and  ceil((deliver_rank + activity_rank + service_rank) / 3) in(1,2)")->count(); // 差评

        $c0 = $c1 + $c2 + $c3; // 所有评论        
        $rate1 = ceil($c1 / ($c1 + $c2 + $c3) * 100); // 好评率
        $rate2 = ceil($c2 / ($c1 + $c2 + $c3) * 100); // 中评率
        //$rate3 = 100 - ($rate1 + $rate2); // 差评率
        $rate3 = ceil($c3 / ($c1 + $c2 + $c3) * 100); // 差评率

        return array('c0' => $c0, 'c1' => $c1, 'c2' => $c2, 'c3' => $c3, 'rate1' => $rate1, 'rate2' => $rate2, 'rate3' => $rate3);
    }

    /**
     * 事件收藏
     * @param type $user_id 用户id
     * @param type $activity_id 事件id
     * @return type
     */
    public function collect_activity($user_id, $activity_id)
    {
        if (!is_numeric($user_id) || $user_id <= 0){
            return array('status' => -1, 'msg' => '未登录', 'result' => array());
        }
        //return array('status' => -1, 'msg' => '未登录', 'result' => array());
        //$count = M('Activity')->where("activity_id = $activity_id")->count();
        //if($count == 0)  return array('status'=>-2,'msg'=>'收藏的事件不存在','result'=>array());
        $count = M('user_activity')->where("user_id = $user_id and activity_id = $activity_id")->find();
        if ($count){
            if($count['status'] ==1 ){
                $data=array('status' => 0, 'add_time' => time(),'updatedate'=>time());
                M('user_activity')->where("collect_id = ".$count['collect_id'])->save($data);
                return array('status' => -3, 'msg' => '收藏已取消', 'result' => array());
            }else{
                $data=array('status' => 1, 'add_time' => time(),'updatedate'=>time());
                M('user_activity')->where("collect_id = ".$count['collect_id'])->save($data);
                return array('status' => 1, 'msg' => '事件收藏成功', 'result' => array());
            }
        }
        M('user_activity')->add(array('activity_id' => $activity_id, 'status' => 1 , 'user_id' => $user_id,'updatedate'=>time(),'is_remind' => 0,'is_comet_remind' => 0, 'add_time' => time()));
        return array('status' => 1, 'msg' => '事件收藏成功!', 'result' => array());
    }

     public function get_activity_info($activity_id){
         if(!$activity_id > 0)
             return array('status'=>-1,'msg'=>'缺少参数','result'=>'');
        $activity_info = M('activity')->where("id = {$activity_id}")->find();
        if(!$activity_info)
            return false;
         
//         $user_info['coupon_count'] = M('coupon_list')->where("uid = $user_id and use_time = 0")->count(); //获取优惠券列表         
//         $user_info['collect_count'] = M('goods_collect')->where(array('user_id'=>$user_id))->count(); //获取收藏数量         
//                                    
//         $user_info['waitPay']     = M('order')->where("user_id = $user_id ".C('WAITPAY'))->count(); //待付款数量
//         $user_info['waitSend']    = M('order')->where("user_id = $user_id ".C('WAITSEND'))->count(); //待发货数量         
//         $user_info['waitReceive'] = M('order')->where("user_id = $user_id ".C('WAITRECEIVE'))->count(); //待收货数量                  
//         $user_info['order_count'] = $user_info['waitPay'] + $user_info['waitSend'] + $user_info['waitReceive'];
         return array('status'=>1,'msg'=>'获取成功','result'=>$activity_info);
     }
     
          public function get_collect_info($collect_id) {
        if (!($collect_id > 0))
            return array('status' => -1, 'msg' => '缺少参数', 'result' => '');
        $map = array(
            'collect_id'=> $collect_id,
        );
        $collect_info = M('user_activity')->where($map)->find();
        if (!$collect_info)
            return false;

//         $user_info['coupon_count'] = M('coupon_list')->where("uid = $user_id and use_time = 0")->count(); //获取优惠券列表         
//         $user_info['collect_count'] = M('goods_collect')->where(array('user_id'=>$user_id))->count(); //获取收藏数量         
//                                    
//         $user_info['waitPay']     = M('order')->where("user_id = $user_id ".C('WAITPAY'))->count(); //待付款数量
//         $user_info['waitSend']    = M('order')->where("user_id = $user_id ".C('WAITSEND'))->count(); //待发货数量         
//         $user_info['waitReceive'] = M('order')->where("user_id = $user_id ".C('WAITRECEIVE'))->count(); //待收货数量                  
//         $user_info['order_count'] = $user_info['waitPay'] + $user_info['waitSend'] + $user_info['waitReceive'];
        return array('status' => 1, 'msg' => '获取成功', 'result' => $collect_info);
    }

}

 