<?php
class ArticleAction extends HomebaseAction {
	
	/**
	 * 文章筛选列表
	 */
	public function lists() {
		$map['status'] = '1';
		$cat = I('category');
		$map['category'] = $cat;
		$model = new Model('Article');
		$list = $this->_lists($model,$map);
		$this->assign('list',$list);
		$this->assign('category',$cat);
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 文章内容页
	 */
	public function read() {
		$id = (int)I('id');
		if ($id <= 0) {
			$cat = I('category');
			//如果传入category参数
			//取指定分类的第一条数据
			if (empty($cat)) $this->error('参数非法');
			$map['category'] = $cat;
		} else {
			$map['id'] = $id;
		}
		$map['status'] = '1';
		
		$model = New Model('Article');
		$info = $model->where($map)->find();
		
		$this->assign('info',$info);
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
        $this->display();
	}
}