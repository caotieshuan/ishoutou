<?php
// 全局设置
class WithdrawlogAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
		$map=array();
		if($_REQUEST['uid'] && $_REQUEST['uname']){
			$map['w.uid'] = $_REQUEST['uid'];
			$search['uid'] = $map['w.uid'];	
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		
		if($_REQUEST['uname'] && !$search['uid']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		
		if(isset($_REQUEST['status']) && $_REQUEST['status']!=""){
			$map['w.withdraw_status'] = intval($_REQUEST['status']);
			$search['status'] = $map['w.withdraw_status'];	
		}
		
		if($_REQUEST['deal_user']){
			$map['w.deal_user'] = urldecode($_REQUEST['deal_user']);
			$search['deal_user'] = $map['w.deal_user'];	
		}
		
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['w.withdraw_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		//if(session('admin_is_kf')==1)	$map['m.customer_id'] = session('admin_id');
		
		//分页处理
		import("ORG.Util.Page");
		$count = M('member_withdraw w')->join("{$this->pre}members m ON w.uid=m.id")->where($map)->count('w.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		//$field= 'w.*,m.user_name,mi.real_name,w.id,w.uid ';
		//$list = M('member_withdraw w')->field($field)->join("{$this->pre}members m ON w.uid=m.id")->join("{$this->pre}member_info mi ON w.uid=mi.uid")->where($map)->order(' w.id DESC ')->limit($Lsql)->select();
		$field= 'w.*,w.withdraw_money-w.withdraw_fee as success_money,m.user_name,mi.real_name,w.id,w.uid,(mm.account_money+mm.back_money) all_money';
		$list = M('member_withdraw w')->field($field)->join("lzh_members m ON w.uid=m.id")->join("lzh_member_info mi ON w.uid=mi.uid")->join("lzh_member_money mm on w.uid = mm.uid")->where($map)->order(' w.id DESC ')->limit($Lsql)->select();

		$this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
		$this->assign("status",C('WITHDRAW_STATUS'));
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        $this->display();
    }
	
	//编辑
    public function edit() {
        $model = D(ucfirst($this->getActionName()));
        $id = intval($_REQUEST['id']);
        $vo = $model->find($id);
		$vo['uname'] = M("members")->getFieldById($vo['uid'],'user_name');
	 	$listType = C('WITHDRAW_STATUS');
		$this->assign('type_list',$listType);
        $this->assign('vo', $vo);
        $this->display();
    }
	
	public function _doEditFilter($m){
		$m->deal_time=time();
		$m->deal_user=session('adminname');
		
		$vox = M("member_withdraw")->field(true)->find($m->id);
		if($vox['withdraw_status']<>3 && $m->withdraw_status==3){
			$lm = M('members')->getFieldById($vox['uid'],'account_money');
			addInnerMsg($uid,"您的提现申请审核未通过","您的提现申请审核未通过");
			memberMoneyLog($vox['uid'],12,$vox['withdraw_money'],"提现未通过,返还");
		}elseif($vox['withdraw_status']<>2 && $m->withdraw_status==2){
			$um = M('members')->field("user_name,user_phone")->find($vox['uid']);
			addInnerMsg($uid,"您的提现已完成","您的提现已完成");
			memberMoneyLog($vox['uid'],29,-($vox['withdraw_money']),"提现成功，减去冻结资金，到帐金额".($vox['withdraw_money']-intval($_POST['withdraw_fee'])),'0','@网站管理员@');
			SMStip("withdraw",$um['user_phone'],array("#USERANEM#","#MONEY#"),array($um['user_name'],($vox['withdraw_money']-intval($_POST['withdraw_fee']))));
		}elseif($vox['withdraw_status']<>1 && $m->withdraw_status==1){
			addInnerMsg($uid,"您的提现申请已通过","您的提现申请已通过，正在处理中");
		}
		
		return $m;
	}
	
	public function _listFilter($list){
	 	$listType = C('WITHDRAW_STATUS');
		$row=array();
		foreach($list as $key=>$v){
			$v['withdraw_status_num'] = $v['withdraw_status'];
			$v['withdraw_status'] = $listType[$v['withdraw_status']];
			$v['uname'] = M("members")->getFieldById($v['uid'],'user_name');
			$row[$key]=$v;
		}
		return $row;
	}
	
/////////////////////////////////
/**
	*提现成功
	*/
	public function withdraw2()
    {
		$map=array();
		if($_REQUEST['uid'] && $_REQUEST['uname']){
			$map['w.uid'] = $_REQUEST['uid'];
			$search['uid'] = $map['w.uid'];	
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		
		if($_REQUEST['uname'] && !$search['uid']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		
		if($_REQUEST['deal_user']){
			$map['w.deal_user'] = urldecode($_REQUEST['deal_user']);
			$search['deal_user'] = $map['w.deal_user'];	
		}
		
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['w.withdraw_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		//if(session('admin_is_kf')==1)	$map['m.customer_id'] = session('admin_id');
		
		//分页处理
		import("ORG.Util.Page");
		$map['w.withdraw_status'] =2;
		$count = M('member_withdraw w')->join("{$this->pre}members m ON w.uid=m.id")->where($map)->count('w.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		
		//$field= 'w.*,m.user_name,mi.real_name,w.id,w.uid ';
		//$list = M('member_withdraw w')->field($field)->join("{$this->pre}members m ON w.uid=m.id")->join("{$this->pre}member_info mi ON w.uid=mi.uid")->where($map)->order(' w.id DESC ')->limit($Lsql)->select();
		$field= 'w.*,w.withdraw_money-w.withdraw_fee as success_money,m.user_name,mi.real_name,w.id,w.uid,(mm.account_money+mm.back_money) all_money';
		$list = M('member_withdraw w')->field($field)->join("lzh_members m ON w.uid=m.id")->join("lzh_member_info mi ON w.uid=mi.uid")->join("lzh_member_money mm on w.uid = mm.uid")->where($map)->order(' w.id DESC ')->limit($Lsql)->select();
		
		$listType = C('WITHDRAW_STATUS');
		unset($listType[0],$listType[1],$listType[3]);


		$this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
		$this->assign("status",$listType);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("searchurl", 'withdraw2');
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }


	public function export()
	{
		import("ORG.Io.Excel");
		alogs("CapitalAccount", 0, 1, '执行了导出已提现列表操作！');//管理员操作日志
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$map=array();
		if($_REQUEST['uid'] && $_REQUEST['uname']){
			$map['w.uid'] = $_REQUEST['uid'];
			$search['uid'] = $map['w.uid'];
			$search['uname'] = urldecode($_REQUEST['uname']);
		}

		if($_REQUEST['uname'] && !$search['uid']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);
		}

		if($_REQUEST['deal_user']){
			$map['w.deal_user'] = urldecode($_REQUEST['deal_user']);
			$search['deal_user'] = $map['w.deal_user'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['w.withdraw_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];
			$search['money'] = $_REQUEST['money'];
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);
			$search['end_time'] = urldecode($_REQUEST['end_time']);
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;
		}
		//if(session('admin_is_kf')==1)	$map['m.customer_id'] = session('admin_id');

		//分页处理
		import("ORG.Util.Page");
		$map['w.withdraw_status'] =2;
		$count = M('member_withdraw w')->join("{$this->pre}members m ON w.uid=m.id")->where($map)->count('w.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		//$field= 'w.*,m.user_name,mi.real_name,w.id,w.uid ';
		//$list = M('member_withdraw w')->field($field)->join("{$this->pre}members m ON w.uid=m.id")->join("{$this->pre}member_info mi ON w.uid=mi.uid")->where($map)->order(' w.id DESC ')->limit($Lsql)->select();
		$field= 'w.*,w.withdraw_money-w.withdraw_fee as success_money,m.user_name,mi.real_name,w.id,w.uid,(mm.account_money+mm.back_money) all_money,mb.bank_name,mb.bank_num,mb.bank_province,mb.bank_city,mb.bank_address';
		$list = M('member_withdraw w')->field($field)->join("lzh_members m ON w.uid=m.id")->join('lzh_member_banks mb ON w.uid=mb.uid')->join("lzh_member_info mi ON w.uid=mi.uid")->join("lzh_member_money mm on w.uid = mm.uid")->where($map)->order(' w.id DESC ')->select();

		$listType = C('WITHDRAW_STATUS');
		unset($listType[0],$listType[1],$listType[3]);



		$startarr = array(
			0=>'等待审核',
			1=>'审核通过,处理中',
			2=>'已提现',
			3=>'审核未通过'
		);
		$row=array();
		//	$row[0]=array('ID','用户名','真实姓名','总余额','可用余额','冻结金额','待收本息金额','待收本金金额','待收利息金额','待付本息金额','待付本金金额','待付利息金额','待确认投标','待审核提现+手续费','处理中提现+手续费','累计提现手续费','累计充值手续费','累计提现金额','累计充值金额','累计支付佣金','累计投标奖励','净赚利息','净付利息','管理员操作资金');
		$row[0]=array('ID','用户名','真实姓名','提现金额','应到账金额','提现时间','提现状态','处理人','处理时间','处理说明','银行名称','银行支行','银行卡号');
		$i=1;
		foreach($list as $v){
			$row[$i]['uid'] = $v['id'];
			$row[$i]['user_name'] = $v['user_name'];
			$row[$i]['real_name'] = $v['real_name'];
			$row[$i]['withdraw_money'] = $v['withdraw_money'];
			if($v['withdraw_status'] ==3)
			{
				$row[$i]['success_money'] = 0;
			}else
			{
				$row[$i]['success_money'] = $v['success_money'];
			}
			$row[$i]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
			$row[$i]['withdraw_status'] = $startarr[$v['withdraw_status']];
			$row[$i]['deal_user'] = $v['deal_user'] ? $v['deal_user'] : '无';
			$row[$i]['deal_time'] =  date('Y-m-d H:i:s',$v['deal_time']);
			$row[$i]['deal_info'] = $v['deal_info'];
			$row[$i]['bank_name'] = $v['bank_name'];
			$row[$i]['bank_num'] = $v['bank_num'];
			$row[$i]['bank_address'] = $v['bank_address'];
			$i++;
		}


		$xls->addArray($row);
		$xls->generateXML("membersInfo");

	}
	/**
	*提现失败
	*/
	public function withdraw3()
    {
		$map=array();
		if($_REQUEST['uid'] && $_REQUEST['uname']){
			$map['w.uid'] = $_REQUEST['uid'];
			$search['uid'] = $map['w.uid'];	
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		
		if($_REQUEST['uname'] && !$search['uid']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		
		if($_REQUEST['deal_user']){
			$map['w.deal_user'] = urldecode($_REQUEST['deal_user']);
			$search['deal_user'] = $map['w.deal_user'];	
		}
		
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['w.withdraw_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['w.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		//if(session('admin_is_kf')==1)	$map['m.customer_id'] = session('admin_id');
		
		//分页处理
		import("ORG.Util.Page");
		$map['w.withdraw_status'] =3;
		$count = M('member_withdraw w')->join("{$this->pre}members m ON w.uid=m.id")->where($map)->count('w.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		
		//$field= 'w.*,m.user_name,mi.real_name,w.id,w.uid ';
		//$list = M('member_withdraw w')->field($field)->join("{$this->pre}members m ON w.uid=m.id")->join("{$this->pre}member_info mi ON w.uid=mi.uid")->where($map)->order(' w.id DESC ')->limit($Lsql)->select();
		$field= 'w.*,w.withdraw_money-w.withdraw_fee as success_money,m.user_name,mi.real_name,w.id,w.uid,(mm.account_money+mm.back_money) all_money';
		$list = M('member_withdraw w')->field($field)->join("lzh_members m ON w.uid=m.id")->join("lzh_member_info mi ON w.uid=mi.uid")->join("lzh_member_money mm on w.uid = mm.uid")->where($map)->order(' w.id DESC ')->limit($Lsql)->select();

		$listType = C('WITHDRAW_STATUS');
		unset($listType[0],$listType[1],$listType[2]);
		$this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
		$this->assign("status",$listType);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
}
?>