<?php
return array(
	//'配置项'=>'配置值'
    
    
    'AUTH_CODE' => "WA4BCHWGcCwBh8MyolEBIJjKtQq8wXWf", //安装完毕之后不要改变，否则所有密码都会出错
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '139.196.225.214', // 服务器地址
    'DB_NAME'               =>  'rili',          // 数据库名
    'DB_USER'               =>  'dataread',      // 用户名
    'DB_PWD'                =>  '459105408',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'cal_',    // 数据库表前缀
    'DB_PARAMS'          	=>  array(), // 数据库连接参数
   /* 'DB_DEBUG'  			=>  TRUE, // 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存*/
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    'URL_MODEL'                  =>     2,
    'DEFAULT_TIMEZONE'=>'Asia/Shanghai',
  'TMPL_PARSE_STRING' => array(
    '__PUBLIC__' => __ROOT__ . '/Public',
    '__JS__' => __ROOT__ . '/Public/js',
    '__CSS__' => __ROOT__ . '/Public/css',
    '__IMAGE__' => __ROOT__ . '/Public/image',
      'sql_mode'=>'only_full_group_by'

)
);