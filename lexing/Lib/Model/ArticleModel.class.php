<?php
// 文章模型
class ArticleModel extends Model {
	
	protected $_validate	=	array(
		array('title','require','标题不能为空'),
		
		array('status',array(-1,0,1),'文章状态非法',self::VALUE_VALIDATE,'in'),//-1-删除 0-禁用 1-正常
	);

	protected $_auto		=	array(
		array('create_time','time',self::MODEL_INSERT,'function'),
		array('update_time','time',self::MODEL_BOTH,'function'),
	);

}
