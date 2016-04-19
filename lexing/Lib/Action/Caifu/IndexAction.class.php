<?php
/**
 * 用户中心首页
 */
class IndexAction extends CaifubaseAction {
	
	function index() {
		$this->display();
	}
	
	function welcome() {
		$model = new CfArticleModel();
		$map['category'] = '用户欢迎页';
		$map['status'] = '1';
		$info = $model->where($map)->find();
		$this->assign('info',$info);
		
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
}