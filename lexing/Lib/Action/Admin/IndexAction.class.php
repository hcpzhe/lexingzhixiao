<?php
/**
 * 管理中心首页
 */
class IndexAction extends AdminbaseAction {
	
	function index() {
		/**
		 * 待权限加入之后, 赋值角色权限
		 */
		$model = new Model('User');
		$info = $model->find(UID);
		$this->assign('uinfo',$info);
		$this->display();
	}
	
	function welcome() {
		/**
		 * 显示会员总数, 待激活会员数, 待审核报单中心数, 待审核提现数
		 */
		$member_model = New Model('Member');
		//会员总数
		$m_total = $member_model -> where('status>0') -> count();
		//待激活会员数
		$m_active = $member_model -> where('status=2') -> count();
		//待审核报单中心
		$m_baodan = $member_model -> where('status=3') -> count();
		
		//待审核提现数
		$cash_model = New Model('Cash');
		$c_audit = $cash_model -> where('status=2') ->count();
		
		$this->assign('m_total',$m_total);
		$this->assign('m_active',$m_active);
		$this->assign('m_baodan',$m_baodan);
		$this->assign('c_audit',$c_audit);
		
        cookie(C('CURRENT_URL_NAME'), __SELF__);
		$this->display();
		
	}
	/**
	 * 修改密码页面
	 */
	public function password(){
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	// 更换密码
	public function changePwd() {
		//对表单提交处理进行处理或者增加非表单数据
		if ($_POST['password'] != $_POST['repassword']) $this->error('两次输入密码不一致');
		if (empty($_POST['password']) || empty($_POST['oldpassword'])) $this->error('密码不能为空');
		$map	=	array();
		$map['password']= pwdHash($_POST['oldpassword']);
		$map['id'] = UID;
		
		//检查用户
		$User	=   M("User");
		if(!$User->where($map)->field('id')->find()) {
			$this->error('旧密码不符！');
		}else {
			$User->password	=	pwdHash($_POST['password']);
			$User->where('id='.UID)->save();
			$this->success('密码修改成功！');
		 }
	}
}