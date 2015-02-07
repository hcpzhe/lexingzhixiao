<?php
//免登录控制器
class CommonAction extends Action {
	
	protected $_cfgs = NULL;
	
	protected function _initialize() {
		$model = New ConfigModel();
		$this->_cfgs = $model->getHash();
		$this->assign('_CFG', $this->_cfgs);
	}
	
	public function _empty() {
		$this->redirect('Index/index');
	}
	
	public function login() {
		$this->display();
	}
	
	/**
	 * 登录提交验证
	 */
	function checkLogin() {
		if(empty($_POST['account'])) {
			$this->error('帐号错误！');
		}elseif (empty($_POST['password'])){
			$this->error('密码必须！');
		}
		elseif (empty($_POST['verify'])){
			$this->error('验证码必须！');
		}
	
		//生成认证条件
		$map			= array();
		$map['account']	= $_POST['account'];
		$map["status"]	= array('gt',0);
		if(session('verify') != md5($_POST['verify'])) {
			$this->error('验证码错误！');
		}
		import ( 'ORG.Util.RBAC' );
		$authInfo = RBAC::authenticate($map);
		//使用用户名、密码和状态的方式进行认证
		if(false === $authInfo) {
			$this->error('帐号不存！');
		}elseif ($authInfo['status'] == '2'){
			$this->error('帐号已禁用！');
		}elseif ($authInfo['status'] < 0){
			$this->error('帐号已被删除！');
		}else {
			if($authInfo['password'] != pwdHash($_POST['password'])) {
				$this->error('密码错误！');
			}
			$_SESSION[C('USER_AUTH_KEY')]	=	$authInfo['id'];
			$_SESSION['email']	=	$authInfo['email'];
			$_SESSION['nickname']		=	$authInfo['nickname'];
			$_SESSION['lastLoginTime']		=	$authInfo['last_login_time'];
			$_SESSION['login_count']	=	$authInfo['login_count'];
			
			//超级管理员
			if(in_array($authInfo['account'], C('ADMIN_AUTHS'))) {
				$_SESSION[C('ADMIN_AUTH_KEY')]		=	true;
			}
			
			//保存登录信息
			$User	=	M('User');
			$ip		=	get_client_ip();
			$time	=	time();
			$data = array();
			$data['id']	=	$authInfo['id'];
			$data['last_login_time']	=	$time;
			$data['login_count']	=	array('exp','login_count+1');
			$data['last_login_ip']	=	$ip;
			$User->save($data);

			// 缓存访问权限
			RBAC::saveAccessList();
			$this->success('登录成功！', U('Index/index'));
		}
	}
	/**
	 * 注销接口
	 */
	function logout() {
		if(isset($_SESSION[C('USER_AUTH_KEY')])) {
			unset($_SESSION[C('USER_AUTH_KEY')]);
			unset($_SESSION);
			session_destroy();
			$this->success('登出成功！', U('Index/index'));
		}else {
			$this->error('已经登出！', U('Index/index'));
		}
	}
	
	/**
	 * 验证码接口
	 */
	public function verify() {
		$type	 =	 isset($_GET['type'])?$_GET['type']:'gif';
		import('ORG.Util.Image');
		Image::buildImageVerify(4,1,$type);
	}
	
	
}