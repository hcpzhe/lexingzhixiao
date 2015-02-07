<?php
class MemberAction extends HomebaseAction {
	

	protected function _initialize(){
		parent::_initialize();
		if (ACTION_NAME != 'viewPwdtwo' && ACTION_NAME != 'checkPwdtwo') {
			if ($_SESSION[C('PWDTWO_KEY')]) {
				//验证过二级密码了
				//unset($_SESSION[C('PWDTWO_KEY')]);
			}else {
				cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
				$this->redirect('Member/viewPwdtwo');//跳转至二级密码验证页面 ~ 验证成功后返回至_currentUrl_
			}
		}
	}
	
	//模板变量已赋值过 $_ME记录了当前用户所有资料
	
	//资料查看页面
	public function info() {
		$model = new Model('Member');
		$map = array('id'=>array('in',$this->_me['parent_id'].','.$this->_me['parent_aid']));
		$memlist = $model->where($map)->getField('id,account,realname');
		$this->assign('memlist',$memlist); //页面用到的会员列表, ID为key索引
		
        // 记录当前列表页的cookie
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	//资料修改接口
	public function update() {
		$data = I('param.');
		$update = array();
		$update['realname'] = $data['realname'];
		$update['tel'] = $data['tel'];
		$update['idcard'] = $data['idcard'];
		$update['address'] = $data['address'];
		$update['bank_account'] = $data['bank_account'];
		$update['bank_card'] = $data['bank_card'];
		$update['bank_name'] = $data['bank_name'];
		$update['bank_address'] = $data['bank_address'];
		$model = new Model('Member');
		if (false ===$model->data($update)->where('id='.MID)->save()) $this->error('资料更新失败,请联系制作人员');
		$this->success('更新成功',cookie(C('CURRENT_URL_NAME')));
	}
	
	public function password(){
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 注册会员页面
	 */
	public function add() {
		$paid = (int)I('paid'); //新会员的节点人
		if ($paid<=0) $this->error('参数非法',cookie(C('CURRENT_URL_NAME')));	
		$ptype = I('ptype') === 'B' ? 'B' : 'A';
		$member_M = new MemberModel();
		$pinfo = $this->_me; //新会员的推荐人
		$painfo = $member_M->findAble($paid);//新会员的节点人
		if (empty($painfo)) $this->error('节点不存在, 请重新选择',cookie(C('CURRENT_URL_NAME')));
		
		/*判断area_type是否被占用*********************************************************/
		$cond = array();
		$cond['parent_aid'] = $painfo['id'];
		$cond['parent_area'] = $ptype;
		$typebool = $member_M->findAble($cond);
		if (!empty($typebool)) $this->error('推荐位已被占用, 请重新选择',cookie(C('CURRENT_URL_NAME')));
		/**************************************************************/
		
		$this->assign('pinfo',$pinfo);//推荐人
		$this->assign('painfo',$painfo);//节点人
		$this->assign('ptype',$ptype);//节点类型 A/B
		
		$randaccount = $member_M->randAccount();
		$this->assign('randaccount',$randaccount);//随机6位可用的用户名
		
		//cookie(C('CURRENT_URL_NAME'), U('Index/info')); //不需要返回至此页面
		$this->display();
	}
	
	/**
	 * 新增接口(注册提交)
	 */
	public function insert() {
		//默认提交为未审核用户
		if (!empty($_POST)){
			if (I('parent_id') != MID) $this->error('非法操作'); //推荐人必须为自己
			$data = I('param.');
			if (empty($data['real_pay'])) $this->error('打款金额不能为空');
			if (empty($data['pay_time'])) $this->error('打款时间不能为空');
			$model = new MemberModel();
			$info = $model->addByMgr($data);
			if ($info !== false){
				$this->success('注册成功，待审核！', U('levelup/lists',array('member_id'=>$info)));//跳转至新会员待审列表
			}
			$this->error($model->getError());
		}else {
			$this->error('非法提交');
		}
	}
	
	/**
	 * 修改密码接口
	 * i=1登录密码; i=2二级密码
	 */
	public function changePwd() {
        if ($_POST['i'] === '1') {
        	//登录密码
        	$pstr = 'password';
        	$opwd = pwdHash($_POST['opwd1']);
        	$npwd = $_POST['pwd1'];
        	$npwdc = $_POST['pwd1c'];
        	
        }elseif ($_POST['i'] === '2') {
        	//取款密码
        	$pstr = 'pwdtwo';
        	$opwd = pwdHash($_POST['opwd2']);
        	$npwd = $_POST['pwd2'];
        	$npwdc = $_POST['pwd2c'];
        }else $this->error('非法提交');
        
        if ($npwd !== $npwdc) $this->error('两次输入的密码不一致');
        
        if ($this->_me[$pstr] != $opwd) $this->error('旧密码不符！');
        
        $mem_M = new Model('Member');
        $map = array();
        $map['id'] = MID;
        $map[$pstr] = pwdHash($npwd);
		if (false === $mem_M->save($map)) $this->error('密码修改错误, 请联系管理员');
		$this->success('密码修改成功！');
	}
	
	
	//二级密码验证页面
	public function viewPwdtwo() {
		$this->display();
	}
	
	//验证二级密码接口
	public function checkPwdtwo() {
		if ($this->_me['pwdtwo'] == pwdHash($_POST['pwdtwo'])) {
			//验证成功后记录session
			$_SESSION[C('PWDTWO_KEY')]	=	true;
			$this->success('二级密码验证成功',cookie(C('CURRENT_URL_NAME')));
		}else {
			unset($_SESSION[C('PWDTWO_KEY')]);
			$this->error('二级密码不正确');
		}
	}

	/**
	 * 会员图谱
	 * 
	 */
	public function atlas(){
		$account = I('account');
		$id = (int)I('id');
		$member_list = array();
		$member_model = new MemberModel();
		if ($id>0) {
			$member_list = $member_model->findAble($id);
			if ($member_list['id'] != MID && false === $member_model->isParentArea(MID, $member_list['id'])) $this->error('没有权限');//判断查询的会员是否在自己的体系中
		}elseif (!empty($account)){
			$member_list = $member_model->findAble(array('account'=>$account));
			if (empty($member_list)) $this->error('无此会员');
			if (false === $member_model->isParentArea(MID, $member_list['id'])) $this->error('没有权限');//判断查询的会员是否在自己的体系中
			$this->assign('search_account',$account);
		}else {
			$member_list = $this->_me;
		}
		$member_list['son_nums'] = $member_model->sonNums($member_list['id']); //直推人数
		$member_list['area_nums'] = $member_model->areaNums($member_list['id']); //推荐体系人数
		
		$this->member($member_model,$member_list['id'],$member_list);
		$this->assign('member_list',$member_list);
		
		cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 会员图谱递归方法
	 */
	protected function member($member_model,$mid,&$member_list,$level=0) {
		//只显示3级图谱
		if ($level >=3) return;
		$level++;
		$where=array();
		$where['parent_aid'] = $mid;
		$where['status'] = '1';
		$where['level'] = array('in','1,2,3,4,5');
		$member_l = $member_model->where($where)->select();	
		foreach ($member_l as $row){
			$row['son_nums'] = $member_model->sonNums($row['id']); //直推人数
			$row['area_nums'] = $member_model->areaNums($row['id']); //推荐体系人数
			
			if ($row['parent_area'] == 'A'){
				$member_list['A'] = $row;
				$this->member($member_model, $row['id'], $member_list['A'], $level);
			
			}else {
				$member_list['B'] = $row;
				$this->member($member_model, $row['id'], $member_list['B'], $level);		
			}
		}
	}
}