<?php
//开启调试模式
define('APP_DEBUG', true);

define('THINK_PATH', './ThinkPHP/');

//定义项目名称
define('APP_NAME', 'lexing');

//定义项目路径
define('APP_PATH', './lexing/');

//define ( 'RUNTIME_PATH', './Runtime/' );

//定义公共目录路径
define('PUBLIC_PATH', './Public/');
if (!is_dir(PUBLIC_PATH)) mkdir(PUBLIC_PATH);

//定义公共目录路径
define('SESSION_PATH', './Session/');
if (!is_dir(SESSION_PATH)) mkdir(SESSION_PATH);

//session存储路径生成
$path = SESSION_PATH.'Admin/';
if (!is_dir($path)) mkdir($path);

$path = SESSION_PATH.'Home/';
if (!is_dir($path)) mkdir($path);

//KindEditor编辑器内容 所用图片上传路径
$path = PUBLIC_PATH.'Kindattached/';
if (!is_dir($path)) mkdir($path);

////项目_upload文件上传路径
//$path = APP_PATH.'Public/Uploads/';
//if (!is_dir($path)) mkdir($path);

//加载框架入口文件
require './ThinkPHP/ThinkPHP.php';
