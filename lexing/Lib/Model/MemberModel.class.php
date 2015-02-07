<?php
/*
 * MUST_VALIDATE	必须验证 不管表单是否有设置该字段
 * VALUE_VALIDATE	值不为空的时候才验证
 * EXISTS_VALIDATE	表单存在该字段就验证   (默认)
 */
// 用户模型
class MemberModel extends Model {
	protected $_validate	=	array(
		array('account','/^\w{4,16}$/i','会员编号格式错误，字母或数字 4-16位'),//  \w等价于[A-Za-z0-9_]
		array('account','require','会员编号必须'),
		array('account','','会员编号已经存在',self::EXISTS_VALIDATE,'unique'),
		
		array('password','require','登录密码必须'),
		array('repassword','require','确认登录密码必须'),
		array('repassword','password','登录确认密码不一致',self::EXISTS_VALIDATE,'confirm'),
		
		array('pwdtwo','require','二级密码必须'),
		array('repwdtwo','require','二级取款密码必须'),
		array('repwdtwo','pwdtwo','二级确认密码不一致',self::EXISTS_VALIDATE,'confirm'),
		
		array('parent_id','require','推荐人必须',self::MUST_VALIDATE ,'regex',self::MODEL_INSERT),
		array('parent_aid','require','节点必须',self::MUST_VALIDATE ,'regex',self::MODEL_INSERT),
		array('parent_area','require','节点位置必须',self::MUST_VALIDATE,'regex',self::MODEL_INSERT),
		array('parent_area',array('A','B'),'节点位置非法',self::MUST_VALIDATE,'in',self::MODEL_INSERT),
		
		array('level',array(0,1,2,3,4,5),'级别非法',self::EXISTS_VALIDATE,'in',self::MODEL_UPDATE), //更新时 存在字段 验证
		
		array('realname','require','真实姓名不能为空'),
		
		array('tel','require','联系电话必须'),
		//array('tel','/((\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)/','联系电话格式不正确'),
		array('idcard','require','身份证号必须'),
		array('idcard','/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}(\d|x|X)$/','请填写真实的18位身份证号码'),
		array('idcard','chkIdcard','身份证号已经存在',self::EXISTS_VALIDATE,'callback'),
		
		array('status',array('-1','0','1'),'用户状态非法',self::VALUE_VALIDATE,'in'),//-1-删除 0-禁用 1-正常
	);

	protected $_auto		=	array(
		array('password','pwdHash',self::MODEL_INSERT,'function'),
		array('pwdtwo','pwdHash',self::MODEL_INSERT,'function'),
		array('level','0',self::MODEL_INSERT), //默认级别-0 临时会员
		array('create_time','time',self::MODEL_INSERT,'function'),
		
		array('status','1',self::MODEL_BOTH,'function'), //保持用户状态永远为正常
	);
	
	/**
	 * 新增用户
	 * @param $data 用于create的数据
	 */
	public function addByMgr($data=array(),$leveldata=array()) {
		/*
		 * 管理员从后台     新增会员
		 * 新增会员的status=1, level=0, 并在levelup表中插入待审核的升级记录
		 */
		$data = (empty($data)) ? I('param.') : $data;
		$data['level'] = 0;
		$new_mem = $this->create($data);
		if (false === $new_mem) return false;
		
		$this->startTrans();
		$return = $this->add();
		if (false === $return) {
			$this->rollback();
			return false;
		}
		
		$levelup_M = new LevelupModel(); $config_M = new Model('Config');
		$leveldata['member_id'] = $return;
		$leveldata['level_bef'] = 0;
		$leveldata['level_aft'] = 1;
		$leveldata['should_pay'] = (int)$config_M->where("`name`='basepoints'")->getField('cfgval');
		$leveldata['real_pay'] = $data['real_pay'];
		$leveldata['pay_time'] = $data['pay_time'];
		$leveldata['rec_id'] = $new_mem['parent_id'];//受益人ID
		$leveldata['status'] = 1;//待审
		$leveldata['type'] = 1;//付款升级
		if (false === $levelup_M->create($leveldata)) {
			$this->error = $levelup_M->getError();
			$this->rollback();
			return false;
		}
		if (false === $levelup_M->addRecord()) {
			$this->error = $levelup_M->getError();
			$this->rollback();
			return false;
		}
		$this->commit();
		return $return;
	}

	/**
	 * 返回$id用户有几个直推下级
	 * @param int $id 要判断的用户ID
	 */
	public function sonNums($id) {
		$condition = array();
		$condition['parent_id'] = $id;
		$condition['status'] = '1';
		$condition['level'] = array('in','1,2,3,4,5');
		
		return $this->where($condition)->count();
	}
	
	/**
	 * 返回$id用户推荐体系的人数
	 * @param int $id 要判断的用户ID
	 */
	public function areaNums($id,$nums=0) {
		$return = $nums; $condition = array();
		$condition['parent_aid'] = $id;
		
		$condition['parent_area'] = 'A';
		$son_A = $this->findAble($condition);
		if ($son_A !== false && !empty($son_A)) {
			$return++;
			$return = $this->areaNums($son_A['id'],$return);
		}
		
		$condition['parent_area'] = 'B';
		$son_B = $this->findAble($condition);
		if ($son_B !== false && !empty($son_B)) {
			$return++;
			$return = $this->areaNums($son_B['id'],$return);
		}
		
		return $return;
	}
	
	/**
	 * 获取用户升级应付金额
	 * @param  $mid
	 * @param  $basepoints
	 */
	public function getShould($mid , $basepoints) {
		$info = $this->find($mid);
		return get_shouldpay($info['level'], $basepoints);
	}
	
	/**
	 * 查找 状态正常员&非临时 的会员
	 * @param PKID或者array $options
	 * @return array
	 */
	public function findAble($options=array()) {
		$where = array();
		if (is_numeric($options) || is_string($options)) {
			$where[$this->getPk()]  =   $options;
		}else {
			$where = $options;
		}
		
		if ($where['status']===null||$where['status']===''||!in_array($where['status'], array(-1,0,1))) $where['status'] = '1';
		if ($where['level']===null||$where['level']===''||!in_array($where['level'], array(0,1,2,3,4,5))) $where['level'] = array('in','1,2,3,4,5');
		return $this->where($where)->find();
	}
	
		
	/**
	 * 新增数据前, 验证 parent_area 和 parent_area_type
	 */
	protected function _before_insert($data, $options) {
		if (false === $this->chkParent($data)) {
			return false;
		}elseif (false === $this->chkParentArea($data)) {
			return false;
		}else {
			return true;
		}
	}
	
	/**
	 * 检测推荐人,节点人 是否存在
	 */
	public function chkParent($data) {
		$condition = array();
		$condition['status'] = '1';
		$condition['level'] = array('in','1,2,3,4,5');
		$condition['id'] = $data['parent_aid'];
		$num = $this->where($condition)->count();
		if ($num <= 0) {
			$this->error = '节点人不存在';
			return false;
		}
		$condition['id'] = $data['parent_id'];
		$num = $this->where($condition)->count();
		if ($num <= 0) {
			$this->error = '推荐人不存在';
			return false;
		}
		return true;
	}
	/**
	 * 节点位置检测合法性
	 */
	public function chkParentArea($data) {
		if ($data['parent_area'] != 'A' && $data['parent_area'] != 'B') {
			$this->error = '节点位置不合法';
			return false;
		}
		$condition = array();
		$condition['status'] = '1';
		$condition['level'] = array('in','1,2,3,4,5');
		$condition['parent_aid'] = $data['parent_aid'];
		$condition['parent_area'] = $data['parent_area'];
		$num = $this->where($condition)->count();
		if ($num > 0) {
			$this->error = '节点位置已被占用';
			return false;
		}
		return true;
	}
	
	/**
	 * 判断是否在推荐体系中
	 * @param $pid 父源ID,在此会员推荐体系中进行核查
	 * @param $sonid 被检查的会员ID
	 */
	public function isParentArea($pid, $sonid) {
		$condition = array();
		$condition['parent_aid'] = $pid;
		$condition['status'] = '1';
		$condition['level'] = array('in','1,2,3,4,5');
		$ids = $this->where($condition)->getField('id',true);
		if (empty($ids)) {
			//检查完了, 没有了
			return false;
		}elseif (in_array($sonid, $ids)) {
			//找到了
			return true;
		}else {
			$ids = array('in',$ids);
			return $this->isParentArea($ids, $sonid);
		}
	}
	
	/**
	 * 验证身份证号码的唯一性
	 * @param string $idcard
	 * @return bool
	 */
	public function chkIdcard($idcard) {
		$map = array();
		$map['status'] = array('neq','-1'); //被删除的会员除外
		$map['idcard'] = $idcard;
		$rs = $this->where($map)->find();
		if (empty($rs)) return true;
		else return false;
	}
	
	/**
	 * 返回可用的6位随机帐号
	 */
	public function randAccount($seed=array()) {
		if (empty($seed)) $seed = range(100000, 999999);
		shuffle($seed);
		
		$max = count($seed) - 1;
		$seed_k = mt_rand(0, $max);
		
		$map = array();
		$map['account'] = $seed[$seed_k];
		
		$rs = $this->where($map)->find();
		if (empty($rs)) return $map['account'];
		else {
			unset($seed[$seed_k]);
			return $this->randAccount($seed);
		}
	}
}
