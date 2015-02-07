<?php
/**
 * 用户中心首页
 */
class IndexAction extends HomebaseAction {
	
	function index() {
		$this->display();
	}
	
	function welcome() {
		$model = new Model('Article');
		$map['category'] = '用户欢迎页';
		$map['status'] = '1';
		$info = $model->where($map)->find();
		$this->assign('info',$info);
		
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
}