<?php
return array(
	'DEFAULT_TIMEZONE'			=>	'PRC', //时区
	'URL_PATHINFO_DEPR'			=>	'-', //参数之间的分割符号  默认是'/'
    'URL_MODEL'                 =>  2, // 1:PATHINFO  2:rewrite 如果你的环境不支持PATHINFO 请设置为3
    'APP_GROUP_LIST'            =>  'Home,Admin',
    'DEFAULT_GROUP'             =>  'Home',
    'SHOW_PAGE_TRACE'           =>  false,//显示调试信息
    
    'DB_TYPE'                   =>  'mysql',
    'DB_HOST'                   =>  'localhost',
    'DB_NAME'                   =>  'lexingzhixiao',
    'DB_USER'                   =>  'root',
    'DB_PWD'                    =>  'root',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'lx_',

//	'TOKEN_ON'					=>	TRUE,
//	'TOKEN_NAME'				=>	'__hash__',

/* RBAC 在每个单独分组中设置
	'USER_AUTH_ON'				=>	true,		// 开启登录验证
	'USER_AUTH_TYPE'			=>	1,			// 默认认证类型 1 登录认证 2 实时认证
	'USER_AUTH_KEY'				=>	'authId',	// 用户认证SESSION标记
	'USER_PW_PREFIX'			=>	'pgj6hd', 	//用户密码前缀
	'NOT_AUTH_MODULE'			=>	'Public',	// 默认无需认证模块
	'USER_AUTH_GATEWAY'			=>	'/Public/login',	// 默认认证网关
	'GUEST_AUTH_ON'				=>	false,	    // 是否开启游客授权访问
*/

//	'TMPL_ACTION_ERROR'			=>	TMPL_PATH.'dispatch_jump.tpl', // 错误跳转对应的模板文件
//	'TMPL_ACTION_SUCCESS'		=>	TMPL_PATH.'dispatch_jump.tpl', // 成功跳转对应的模板文件
	'USER_PW_PREFIX'			=>	'aedb80', //用户密码前缀
	'LIST_ROWS'					=>	15,	//每页的条数
);
