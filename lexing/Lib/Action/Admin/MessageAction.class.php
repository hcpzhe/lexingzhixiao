<?php
//会员
class MessageAction extends AdminbaseAction {
	
	/**
	 * 所有会员留言
	 */
	public function lists() {
		$model = new Model('Message');
		$map = array();
		$list = $this->_lists($model,$map);
        $this->assign('list', $list); //会员留言
		
		$mem_ids = field_unique($list, 'member_id'); //列表中用到的会员ID
		
		if (!empty($mem_ids)) {
			$member_M = new Model('Member');
			$map = array('id'=>array('in',$mem_ids));
			$memlist = $member_M->where($map)->getField('id,account,realname');
		}else $memlist = array();
		$this->assign('memlist',$memlist); //列表用到的会员列表, ID为key索引
		
        // 记录当前列表页的cookie
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
         $this->display();
	}
	
	/**
	 * 会员留言详细
	 * @param  $id		
	 */
	public function info() {
		$id = (int)I('id');
		if ($id <= 0) $this->error('参数非法');
		$model = new Model('Message');
		$info = $model->where('id='.$id)->find();
		$this->assign('info',$info);       //留言信息
	    $model_m = new Model('Member');
		$memlist = $model_m->where('id='.$info['member_id'])->getField('id,account,realname,tel,address');
		$this->assign('memlist',$memlist); //列表用到的会员信息
		
        // 记录当前列表页的cookie
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 会员留言 回复、信息更新 接口
	 * @param  $id		
	 */
	public function update() {
		$id = (int)I('param.id');
		if ($id <= 0) $this->error('参数非法');
		$newdata = array();
		$newdata['id'] = I('param.id');
		$newdata['reply'] = I('param.reply');
		$newdata['status'] = I('param.status');
		$model = new MessageModel();
		if (false === $model->create($newdata)) $this->error($model->getError());
		if (false === $model->where('id='.$id)->save()) $this->error('更新失败');
		$this->success('更新成功');
	}
	

	
}