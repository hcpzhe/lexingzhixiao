<?php
// 会员留言
class MessageModel extends Model {
	
	protected $_validate	=	array(
		array('content','require','内容不能为空'),
		array('status',array(-1,0,1),'留言状态非法',self::VALUE_VALIDATE,'in'),//-1-删除 0-禁用 1-正常
	);

	protected $_auto		=	array(
		array('send_time','time',self::MODEL_INSERT,'function'),
	);

}
