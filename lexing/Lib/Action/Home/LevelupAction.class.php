<?php

class LevelupAction extends HomebaseAction {

	protected function _initialize(){
		parent::_initialize();
		if ($_SESSION[C('PWDTWO_KEY')]) {
			//验证过二级密码了
			//unset($_SESSION[C('PWDTWO_KEY')]);
		}else {
			cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
			$this->redirect('Member/viewPwdtwo');//跳转至二级密码验证页面 ~ 验证成功后返回至_currentUrl_
		}
		$stat = array(
			'1' => '待审',
			'2' => '拒绝',
			'3' => '通过',
		);
		$this->assign('_stat',$stat);
	}
	
	/**
	 * 升级记录筛选列表
	 * 状态 1-待审核, 2-拒绝, 3-通过
	 */
	public function lists($status=1,$member_id=null) {
		$map = array(); $model = new Model('Levelup'); $member_M = New MemberModel();
		$map['status'] = $status;
		if (isset($member_id) && $member_id >0) {
			//限定, 只能查询 由我注册的新会员的审核记录
			$target = $member_M->findAble(array('id'=>$member_id,'level'=>'0'));//目标用户
			if ($target['parent_id'] != MID) $this->error('没有权限'); 
			$map['member_id'] = $target['id'];
			$map['level_bef'] = '0'; //限定 只能查看  新会员的审核记录
		}elseif ($member_id == 'my') {
			//查看所有 由我注册的新会员的审核记录
			$tmpcond = array();
			$tmpcond['parent_id'] = MID;
			$tmpcond['level'] = '0';
			$tmpcond['status'] = '1';
			
			$target = $member_M->where($tmpcond)->getField('id',true);//目标用户
			$map['member_id'] = (empty($target)) ? '0' : array('in',$target);
			unset($map['status']);
		}else {
			$map['member_id'] = MID;
		}
		
		$this->assign('status',$map['status']);
		
		$list = $this->_lists($model,$map,'level_bef desc,id desc');
		$this->assign('list',$list);
		
		$mem_ids = field_unique($list, 'member_id,rec_id'); //列表中用到的会员ID
		$map = array('id'=>array('in',$mem_ids));
		$memlist = $member_M->where($map)->getField('id,account,realname');
		$this->assign('memlist',$memlist); //列表中用到的会员列表, ID为key索引
		
        // 记录当前列表页的cookie
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
        $this->display();
	}
	
	/**
	 * 我要升级页面  先验证二级密码
	 * 收款账户显示的为公司账户, 由公司审核后,受益人获取积分
	 */
	public function levelup() {
		if ($this->_me['level'] >= $this->_cfgs['maxlevel']) $this->error('您已经达到最高级别了!', U('Index/welcome'));
		
		$need_pts = get_shouldpay($this->_me['level'], $this->_cfgs['basepoints']);//所需积分
		$cash_M = New CashModel();
		$ready_pts = $cash_M->getReadyMoney(MID);
		$canuse_pts = $this->_me['points']-$ready_pts; //可用积分
		$paypoints = ($need_pts <= $canuse_pts) ? true : false; //是否可以使用积分升级
		
		$this->assign('need_money',$need_pts);
		$this->assign('paypoints',$paypoints);
		$this->assign('canuse_pts',$canuse_pts);
		
		$levelup_M = New LevelupModel();
		$rec_id = $levelup_M->getRec(MID);//受益人ID
		$member_M = New Model('Member');
		$rec_info = $member_M->find($rec_id);
		$this->assign('rec_info',$rec_info);//受益人信息
		
        // 记录当前列表页的cookie
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 付款升级接口
	 */
	public function payToup() {
		$model = New LevelupModel();
		$data = I('param.');
		$data = array_merge($data, array('member_id'=>MID));
		$data['type'] = '1';
		$model->create($data);
		if (false === $model->addRecord()) { //添加升级记录
			$this->error($model->getError());
		}
		$this->success('升级请求已经提交, 请通知管理员进行审核!',cookie(C('CURRENT_URL_NAME')));
	}
	
	/**
	 * 积分升级接口
	 */
	public function pointsToup() {
		//判断   所需积分 >= 余额-预提现积分
		if ($this->_me['level'] >= $this->_cfgs['maxlevel']) $this->error('您已经达到最高级别了!');
		
		$need_pts = get_shouldpay($this->_me['level'], $this->_cfgs['basepoints']);//所需积分
		$cash_M = New CashModel();
		$ready_pts = $cash_M->getReadyMoney(MID);
		if ($need_pts > $this->_me['points']-$ready_pts) $this->error('积分余额不足, 无法使用积分升级');
		
		$levelup_M = New LevelupModel();
		$data = array();
		$data['member_id'] = MID;
		$data['type'] = 2;
		$data['remark'] = I('remark');
		$levelup_M->create($data);
		if (false === $levelup_M->addRecord()) { //添加升级记录
			$this->error($levelup_M->getError());
		}
		$this->success('升级成功, 请刷新网站!',cookie(C('CURRENT_URL_NAME')));
	}
}