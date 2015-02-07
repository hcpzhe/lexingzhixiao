<?php
//财务中心
class PointsAction extends HomebaseAction {

	protected function _initialize(){
		parent::_initialize();
		if ($_SESSION[C('PWDTWO_KEY')]) {
			//验证过二级密码了
			//unset($_SESSION[C('PWDTWO_KEY')]);
		}else {
			cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
			$this->redirect('Member/viewPwdtwo');//跳转至二级密码验证页面 ~ 验证成功后返回至_currentUrl_
		}
	}
	/**
	 * 积分奖励明细
	 */
	public function listBonus() {
		$bonus_M = New Model('Bonus');
		$map['member_id'] = MID;
		$list = $this->_lists($bonus_M,$map,'create_time desc');
		$this->assign('list',$list); //积分奖励列表
		
		$mem_ids = field_unique($list, 'source_id'); //列表中用到的会员ID
		$model = New Model('Member');
		$map = array('id'=>array('in',$mem_ids));
		$memlist = $model->where($map)->getField('id,account,realname');
		$this->assign('memlist',$memlist); //列表用到的会员列表 ID为key索引
		
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 提现list 筛选列表
	 */
	public function listCash() {
		$status = (int)I('status');
		if ($status > 0) $map['status'] = $status;
		$map['member_id'] = MID;
		$model = New Model('Cash');
		$list = $this->_lists($model,$map,null);
		$this->assign('list',$list); //提现列表
		
		$stat = array(
			'1' => '待审',
			'2' => '拒绝',
			'3' => '通过',
		);
		$this->assign('_stat',$stat);
		
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->viewCash();
	}
	
	/**
	 * 提现详细
	 */
	public function readCash() {
		$id = (int)I('id');
		if ($id <= 0) $this->error('参数非法');
		
		$map['id'] = $id;
		$map['member_id'] = MID;
		$model = New Model('Cash');
		$info = $model->where($map)->find();
		$this->assign('info',$info);
		
		$stat = array(
			'1' => '待审',
			'2' => '拒绝',
			'3' => '通过',
		);
		$this->assign('_stat',$stat);
		
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 提现页面
	 */
	public function viewCash() {
		$model = new CashModel();
		$readycash = $model->getReadyMoney(MID);//用户预提现金额
		$ablecash = $this->_me['points'] - $readycash; //用户可提现最高金额
		$this->assign('readycash',$readycash);
		$this->assign('ablecash',$ablecash);
		
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 提现提交接口
	 */
	public function addCash() {
		$data = I('param.');
		$data = array_merge($data, array('member_id'=>MID,'status'=>'1'));
		$model = new CashModel();
		if (false === $model->create($data)) {
			$this->error($model->getError());
		}
		if (false === $model->addNew()) { //添加提现申请记录
			$this->error($model->getError());
		}
		$this->success('提现请求已经提交, 请通知管理员进行审核!',cookie(C('CURRENT_URL_NAME')));
	}
}