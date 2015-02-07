<?php
return array(
/************** 不使用RBAC,仅作登录认证 *********************/
	'USER_AUTH_ON'			=>true,
	'USER_AUTH_KEY'			=>'cpMember',	// 用户认证SESSION标记
	'USER_AUTH_MODEL'		=>'Member',	// 默认验证数据表模型
	'AUTH_PWD_ENCODER'		=>'md5',	// 用户认证密码加密方式
	'PWDTWO_KEY'			=>'mpwdtwo',	//二级密码认证SESSION标记

	'USER_AUTH_GATEWAY'		=>'Home/Common/login',	// 默认认证网关

	'NOT_AUTH_MODULE'		=>'',		// 默认无需认证模块
	'REQUIRE_AUTH_MODULE'	=>'Member',		// 默认需要认证模块
	'NOT_AUTH_ACTION'		=>'',		// 默认无需认证操作
	'REQUIRE_AUTH_ACTION'	=>'',		// 默认需要认证操作

	'SESSION_OPTIONS'		=>array('name'=>'SITESESSID','path'=>SESSION_PATH.'Home/'),
	'CURRENT_URL_NAME'		=>'lxhzHomeCurtUrl',	//currentUrl的cookie名称

//	'SHOW_RUN_TIME'=>true,			// 运行时间显示
//	'SHOW_ADV_TIME'=>true,			// 显示详细的运行时间
//	'SHOW_DB_TIMES'=>true,			// 显示数据库查询和写入次数
//	'SHOW_CACHE_TIMES'=>true,		// 显示缓存操作次数
//	'SHOW_USE_MEM'=>true,			// 显示内存开销
//	'DB_LIKE_FIELDS'=>'title|remark',


);
