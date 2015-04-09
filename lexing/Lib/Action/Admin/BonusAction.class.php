<?php

class BonusAction extends AdminbaseAction {
	
	/**
	 * 升级记录筛选列表
	 */
	public function lists($account=null) {
		$model = New Model('Bonus');
		$member_M = new Model('Member');
		
		$memeber_map = array(); $bouns_map = array();
		if(isset($account)){
			$memeber_map['account'] = $account;
			$bouns_map['member_id'] = $member_M->where($memeber_map)->getField('id');
		}
		$list = $this->_lists($model,$bouns_map,'create_time desc');
		$this->assign('list', $list); //记录列表
		
		$mem_ids = field_unique($list, 'member_id,source_id'); //列表中用到的会员ID
		$map = array('id'=>array('in',$mem_ids));
		$memlist = $member_M->where($map)->getField('id,account,realname');
		$this->assign('memlist',$memlist); //列表用到的会员列表, ID为key索引
		
		// 记录当前列表页的cookie
		cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
}