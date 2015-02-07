<?php
//会员
class MemberAction extends AdminbaseAction {
	
	/**
	 * 会员筛选列表. 默认1-5级正常会员
	 * @param  $level		级别  0-5 0-临时会员
	 * @param  $account		帐号
	 * @param  $status		状态
	 */
	public function lists($level=null, $account=null, $status=1, $tpl=null) {
		$model = New MemberModel();
		//查询条件
		$map['status'] = $status;
		
        $id = (int)I('param.id');
        if ($id>0) $map['id'] = $id;
        if(isset($account)){
            $map['account']   =   array('like', '%'.$account.'%');
        }
        if(isset($level) && $level>=0 && $level<=5){
            $map['level']  =   $level;
       		$this->assign('level', $map['level']);  //用于筛选条件的显示
        }else{
            $map['level']  =   array('in', '1,2,3,4,5');
       		$this->assign('level', -1);  //所有级别会员, 用于筛选条件的显示
        }
		
        $list = $this->_lists($model,$map);
        
        $this->assign('list', $list); //会员列表
        $this->assign('status', $map['status']); //用于筛选条件的显示
        $this->assign('account', $account); //用于筛选条件的显示
        $this->meta_title = '会员列表';
        //$this->assign('pagetitle', $this->meta_title);
        
		$mem_ids = field_unique($list, 'parent_id,parent_aid'); //列表中用到的会员ID
		$map = array('id'=>array('in',$mem_ids));
		$memlist = $model->where($map)->getField('id,account,realname');
		$this->assign('memlist',$memlist); //列表用到的会员列表, ID为key索引
        
        // 记录当前列表页的cookie
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
        if (isset($tpl)) $this->display($tpl);
        else $this->display();
	}
	
	/**
	 * 会员详细资料
	 * @param  $id		
	 */
	public function info() {
		$id = (int)I('id');
		if ($id <= 0) $this->error('参数非法');
		
		$map['id'] = $id;
		$model = new Model('Member');
		$info = $model->where($map)->find();
		$this->assign('info',$info);
		
		$map = array('id'=>array('in',$info['parent_id'].','.$info['parent_aid']));
		$memlist = $model->where($map)->getField('id,account,realname');
		$this->assign('memlist',$memlist); //列表用到的会员列表, ID为key索引
		
        // 记录当前列表页的cookie
        cookie(C('CURRENT_URL_NAME'),$_SERVER['REQUEST_URI']);
		$this->display();
	}
	
	/**
	 * 会员资料更新接口
	 * @param  $id		
	 */
	public function update() {
		$id = (int)I('param.id');
		if ($id <= 0) $this->error('参数非法');
		
		$newdata = array();
		$newdata['id'] = I('param.id');
		$newdata['realname'] = I('param.realname');
		$newdata['tel'] = I('param.tel');
		$newdata['idcard'] = I('param.idcard');
		$newdata['address'] = I('param.address');
		$newdata['bank_account'] = I('param.bank_account');
		$newdata['bank_card'] = I('param.bank_card');
		$newdata['bank_name'] = I('param.bank_name');
		$newdata['bank_address'] = I('param.bank_address');
		
		$model = new MemberModel();
		if (false === $model->create($newdata)) $this->error($model->getError());
		if (false === $model->where('id='.$id)->save()) $this->error('更新失败');
		$this->success('更新成功');
	}
	
	/**
	 * 重置用户密码
	 * 传递用户主键信息
	 */
	public function resetPwd($istwo=null){
		$id = (int)I('id');
		if ($id <= 0) $this->error('参数非法');
		
		$member_M = new Model('Member');
		$condition = array('id' => array('eq', $id));
		$field = (isset($istwo)) ? 'pwdtwo' : 'password';
		$member = $member_M->where($condition)->find();
		$list = $member_M->where($condition)->setField($field,pwdHash($member['account']));
		if ($list !== false) {
			$this->success($field.'密码已重置为用户名！',cookie(C('CURRENT_URL_NAME')));
		} else {
			$this->error('密码重置失败，请重试！');
		}
	}
	
	/**
	 * 注册会员页面
	 */
	public function add() {
		$pid = (int)I('pid'); //新会员的推荐人
		$paid = (int)I('paid'); //新会员的节点人
		if ($pid<=0 || $paid<=0) $this->error('参数非法',cookie(C('CURRENT_URL_NAME')));
		$ptype = I('ptype') === 'B' ? 'B' : 'A';
		$member_M = new MemberModel();
		$pinfo = $member_M->findAble($pid);
		if (empty($pinfo)) $this->error('推荐人不存在, 请重新选择',cookie(C('CURRENT_URL_NAME')));
		$painfo = $member_M->findAble($paid);
		if (empty($painfo)) $this->error('节点不存在, 请重新选择',cookie(C('CURRENT_URL_NAME')));
		
		/*判断area_type是否被占用*********************************************************/
		$cond = array();
		$cond['parent_aid'] = $painfo['id'];
		$cond['parent_area'] = $ptype;
		$typebool = $member_M->findAble($cond);
		if (!empty($typebool)) $this->error('推荐位已被占用, 请重新选择'.$member_M->getLastSql(),cookie(C('CURRENT_URL_NAME')));
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
			$model = new MemberModel();
			$data = I('param.');
			$info = $model->addByMgr($data,array('remark'=>'此会员由管理员注册'));
			if ($info !== false){
				$this->success('注册成功，待审核！', U('levelup/lists?member_id='.$info));//跳转至新会员待审列表
			}
			$this->error($model->getError());
		}else {
			$this->error('非法提交');
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
		}elseif (!empty($account)){
			$member_list = $member_model->findAble(array('account'=>$account));
		}else {
			$member_list = $member_model->where('parent_aid=0')->find();
		}
		if (empty($member_list)) $this->error('会员不存在');
		
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