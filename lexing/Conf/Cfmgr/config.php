<?php
return array(
/************** RBAC **********************/
	'USER_AUTH_ON'			=>true,
	'USER_AUTH_TYPE'		=>1,		// 默认认证类型 1 登录认证 2 实时认证
	'USER_AUTH_KEY'			=>'cpAdmin',	// 用户认证SESSION标记
    'ADMIN_AUTH_KEY'		=>'administrator',
	'ADMIN_AUTHS'			=>array('admin','administrator'),//超管用户名
	'USER_AUTH_MODEL'		=>'CfUser',	// 默认验证数据表模型
	'AUTH_PWD_ENCODER'		=>'md5',	// 用户认证密码加密方式

	'USER_AUTH_GATEWAY'		=>'Cfmgr/Common/login',	// 默认认证网关
	'NOT_AUTH_MODULE'		=>'Common',		// 默认无需认证模块
	'REQUIRE_AUTH_MODULE'	=>'',		// 默认需要认证模块
	'NOT_AUTH_ACTION'		=>'',		// 默认无需认证操作
	'REQUIRE_AUTH_ACTION'	=>'',		// 默认需要认证操作

    'GUEST_AUTH_ON'         => false,    // 是否开启游客授权访问
    'GUEST_AUTH_ID'         =>0,     // 游客的用户ID

//	'SHOW_RUN_TIME'=>true,			// 运行时间显示
//	'SHOW_ADV_TIME'=>true,			// 显示详细的运行时间
//	'SHOW_DB_TIMES'=>true,			// 显示数据库查询和写入次数
//	'SHOW_CACHE_TIMES'=>true,		// 显示缓存操作次数
//	'SHOW_USE_MEM'=>true,			// 显示内存开销
//    'DB_LIKE_FIELDS'=>'title|remark',

	
	'SESSION_OPTIONS'		=>array('name'=>'SITESESSID','path'=>SESSION_PATH.'Cfmgr/'),
	'CURRENT_URL_NAME'		=>'lxhzCfmgrCurtUrl',	//currentUrl的cookie名称
);
