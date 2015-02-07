<?php

class CashAction extends AdminbaseAction {

	function _initialize(){
		parent::_initialize();
		$stat = array(
			'1' => '待审',
			'2' => '拒绝',
			'3' => '通过',
		);
		$this->assign('_stat',$stat);
	}
	
	/**
	 * 记录筛选列表
	 */
	public function lists($status=1) {
		$model = New Model('Cash');
		//查询条件
		$map['status'] = $status;
		
        $id = (int)I('param.id');
        if ($id>0) $map['id'] = $id;
        $list = $this->_lists($model,$map);
        
		$mem_ids = field_unique($list, 'member_id'); //列表中用到的会员ID
		$map = array('id'=>array('in',$mem_ids));
		$member_M = New Model('Member');
		$memlist = $member_M->where($map)->getField('id,account,realname');
		
        $this->assign('list', $list); //记录列表
        $this->assign('status', $map['status']); //用于筛选条件的显示
		$this->assign('memlist',$memlist); //列表用到的会员列表, ID为key索引
        
        // 记录当前列表页的cookie
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
        $this->display();
	}
	
	/**
	 * 记录详情
	 */
	public function read() {
		$id = (int)I('id');
		if ($id <= 0) $this->error('参数非法');
		
		$map['id'] = $id;
		$model = new Model('Cash');
		$info = $model->where($map)->find();
		
		$member_M = New Model('Member');
		$meminfo = $member_M->find($info['member_id']);
		
		$this->assign('info',$info);
		$this->assign('meminfo',$meminfo);
		
        // 记录当前列表页的cookie
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 通过审核接口
	 */
	public function passCheck() {
		$id = (int)I('id');
		
		if ($id <= 0) $this->error('参数非法');
		$model = New CashModel();
		
		//$model->remark = I('remark'); 
		if (false===$model->passCheck($id)) {
			$this->error($model->getError());
		}
		$this->success('审核成功',cookie(C('CURRENT_URL_NAME')));
	}
	
	/**
	 * 拒绝审核接口
	 */
	public function denyCheck() {
		//建议, 拒绝的时候给出页面, 让管理员填入拒绝原因,存入remark字段
		$id = (int)I('id');
		
		if ($id <= 0) $this->error('参数非法');
		$model = New CashModel();
		
		//$model->remark = I('remark'); 
		if (false===$model->denyCheck($id)) {
			$this->error($model->getError());
		}
		$this->success('拒绝成功',cookie(C('CURRENT_URL_NAME')));
	}
}