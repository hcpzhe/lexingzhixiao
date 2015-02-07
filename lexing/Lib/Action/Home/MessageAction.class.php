<?php
class MessageAction extends HomebaseAction {
	
	/**
	 * 留言页面 
	 */
	public function add() {
		$model = new Model('Message');
		$map['status'] = '1';
		$map['member_id'] = MID;
		$list = $this->_lists($model,$map);
		$this->assign('list',$list);
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
        $this->display();
	}
	
	/**
	 * 新增留言接口
	 */
	public function insert() {
		$data['member_id']  = (int)I('member_id');
		$data['send_time'] = time();
		$model = new MessageModel();		
		$data = $model->create();		
		$model->add($data);		
		$this->success('添加成功',U('Message/lists?member_id='.$data['member_id']));
	}
	/**
	 * 留言列表
	 */
	public function lists($member_id=null) {
		$model = new Model('Message');
		$map['status'] = '1';
		$map['member_id'] = (int)I('member_id');
		$list = $this->_lists($model,$map);
		$this->assign('list',$list);
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);		
		$this->display();
	}
		
}