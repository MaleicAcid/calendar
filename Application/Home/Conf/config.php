<?php
return array(
	//'配置项'=>'配置值'
        'LOAD_EXT_FILE' =>'common',
        'LOAD_EXT_CONFIG' => 'crons',//加载扩展配置文件
    //'LOAD_EXT_FILE' =>'function',
    
        //前台
    'CSS_URL' => __ROOT__ . '/Public/css/',
    'JS_URL' =>  __ROOT__ . '/Public/js/',
    'IMG_URL' => __ROOT__ . '/Public/image/',
    
        'LAYOUT_ON' => true,
    'LAYOUT_NAME' => 'Public/layout',
    
        //定义网站的域名地址,方便拼接
    'SITE_URL'  => 'http://www.rili.com/',
    
    //发送邮件配置
    'mailFrom' => 'myjobikedou@163.com',
    'mailFromName'=> '大连海事就业网',
    'mailHost' => 'smtp.163.com',  //发送邮件的服务协议地址
    'mailUsername'=> 'myjobikedou@163.com',
    'mailPassword'=> 'MJikedou1000sq',

);