<?php
// 网站配置模型
class ConfigModel extends Model {
	
	protected $_validate	=	array(
		array('name','require','配置名称必须'),
		array('name','','配置名称已经存在',self::EXISTS_VALIDATE,'unique'),
		
		array('title','require','配置标题必须'),
	);

	/**
	 * 获取网站配置列表数组
	 */
	function getHash() {
		$list = $this->getField('name,cfgval');
		return $list;
	}
}
