<?php
class ArticleAction extends CfmgrbaseAction {

	/**
	 * 文章筛选列表
	 */
	public function lists() {
		$map['status'] = '1';
		$cat = I('category');
		if (!empty($cat)) {
			$encode = mb_detect_encoding($cat, array("UTF-8","GB2312","GBK"));
			if ($encode != "UTF-8"){
				$cat = iconv($encode,"UTF-8",$cat);
			}
			$map['category'] = $cat;
		}
		$model = new CfArticleModel();
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
			$encode = mb_detect_encoding($cat, array("UTF-8","GB2312","GBK"));
			if ($encode != "UTF-8"){
				$cat = iconv($encode,"UTF-8",$cat);
			}
			$map['category'] = $cat;
		} else {
			$map['id'] = $id;
		}
		$map['status'] = '1';
	
		$model = New CfArticleModel();
		$info = $model->where($map)->find();
		$this->assign('info',$info);
		$category_list = array('公告','新闻');
		$this->assign('catlist',$category_list);
		cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 更新文章接口
	 */
	public function update() {
		$id = (int)I('id');
		if ($id<=0) $this->error('参数非法');
	
		$model = new CfArticleModel();
		$model->create();
		$model->where('id='.$id)->save();
		$this->success('更新成功',cookie(C('CURRENT_URL_NAME')));
	}
	
	/**
	 * 新增文章页面
	 */
	public function add() {
		cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 新增文章接口
	 */
	public function insert() {
		$model = new CfArticleModel();
		$data = $model->create();
		$model->add();
		$this->success('添加成功',U('Article/lists?category='.$data['category']));
	}
	
	public function fdelete() {
		$id = (int)I('id');
		if ($id<=0) $this->error('参数非法');
	
		$model = new CfArticleModel();
		$model->where('id='.$id)->delete();
		$this->success('删除成功',cookie(C('CURRENT_URL_NAME')));
	}
	}