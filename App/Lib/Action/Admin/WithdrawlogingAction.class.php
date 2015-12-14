<?php
// 全局设置
class WithdrawlogingAction extends ACommonAction
{
	/**
	+----------------------------------------------------------
	 * 默认操作 提现处理中
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
		$map['w.withdraw_status'] =1;
		$count = M('member_withdraw w')->join("{$this->pre}members m ON m.id=w.uid")->where($map)->count('w.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'w.*,m.user_name,mi.real_name,w.id,w.uid,(mm.account_money+mm.back_money) all_money';
		$list = M('member_withdraw w')->field($field)->join("lzh_members m ON w.uid=m.id")->join("lzh_member_info mi ON w.uid=mi.uid")->join("lzh_member_money mm on w.uid = mm.uid")->where($map)->order(' w.id DESC ')->limit($Lsql)->select();


		foreach($list as &$val){
			$val['success_money'] = $val['withdraw_money']-$val['withdraw_fee'];
			$val['second_fee'] = $val['withdraw_fee'];
		}


		$listType = C('WITHDRAW_STATUS');
		unset($listType[0],$listType[2],$listType[3]);
		$this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
		$this->assign("list", $list);
		$this->assign("status",$listType);
		$this->assign("pagebar", $page);
		$this->assign("search", $search);
		$this->assign("query", http_build_query($search));

		$this->display();
	}

	//编辑
	public function edit() {
		$model = M('member_withdraw');
		$id = intval($_REQUEST['id']);
		$vo = $model->find($id);
		//$vo['uname'] = M("members")->getFieldById($vo['uid'],'user_name');
		$listType = C('WITHDRAW_STATUS');
		unset($listType[0],$listType[1]);
		$this->assign('type_list',$listType);
		$field= 'w.*,m.user_name,mi.real_name,w.id,w.uid,(mm.account_money+mm.back_money) all_money,mb.bank_num,mb.bank_province,mb.bank_city,mb.bank_address,mb.bank_name';
		$list = M('member_withdraw w')->field($field)->join("lzh_members m ON w.uid=m.id")->join("lzh_member_info mi ON w.uid=mi.uid")->join("lzh_member_money mm on w.uid = mm.uid")->join("lzh_member_banks mb on w.uid = mb.uid")->where("w.id=$id")->order(' w.id ASC ')->limit($Lsql)->select();
		foreach($list as $v){
			$vo['uname'] =$v['user_name'];
			$vo['real_name'] = $v['real_name'];
			$vo['bank_num'] =$v['bank_num'];
			$vo['bank_province'] = $v['bank_province'];
			$vo['bank_city'] =$v['bank_city'];
			$vo['bank_address'] = $v['bank_address'];
			$vo['bank_name'] =$v['bank_name'];
			$vo['all_money'] =$v['all_money'];
			$vo['withdraw_fee'] =$v['withdraw_fee'];
		}
		$vo['success_money'] = $vo['withdraw_money']-$vo['withdraw_fee'];
		//////////////////////////////////////
		$this->assign('vo', $vo);
		$this->display();
	}

	public function doEdit() {
		$model = D("member_withdraw");
		$status = intval($_POST['withdraw_status']);
		$id = intval($_POST['id']);
		$deal_info = $_POST['deal_info'];
		$secondfee = floatval($_POST['withdraw_fee']);

		$info = $model->field('add_time')->where("id={$id} and (withdraw_status=2 or withdraw_status=3)")->find();
		if($info['add_time']){
			$this->error("此提现复审已处理过，请不要重复处理！");
		}
		if (false === $model->create()) {
			$this->error($model->getError());
		}
		//保存当前数据对象
		$model->withdraw_status = $status;
		$model->deal_info = $deal_info;
		$model->deal_time=time();
		$model->deal_user=session('adminname');
		////////////////////////
		$field= 'w.*,w.id,w.uid,(mm.account_money+mm.back_money) all_money';
		$vo = M("member_withdraw w")->field($field)->join("lzh_member_money mm on w.uid = mm.uid")->find($id);
		$um = M('members')->field("user_name,user_phone")->find($vo['uid']);
		if($vo['withdraw_status']<>3 && $status==3){
			addInnerMsg($vo['uid'],"您的提现申请审核未通过","您的提现申请审核未通过，处理说明：".$deal_info);

			//memberMoneyLog($vo['uid'],12,$vo['withdraw_money'],"提现未通过,返还,其中提现金额：".$vo['withdraw_money']."元，手续费：".$vo['second_fee']."元",'0','@网站管理员@',$vo['second_fee']);
			SMStip("nowithdraw",$um['user_phone'],array("#USERANEM#","#MONEY#"),array($um['user_name'],($vo['withdraw_money'])));

			memberMoneyLog($vo['uid'],12,$vo['withdraw_money'],"提现未通过,返还",'0','@网站管理员@',0,$vo['withdraw_back_money']);
			$model->success_money = 0;

		}else if($vo['withdraw_status']<>2 && $status==2){
			addInnerMsg($vo['uid'],"您的提现已完成","您的提现已完成");
			// 统一为：都从当笔提现金额中扣除手续费
			/*if( ($vo['all_money'] - $vo['second_fee'])<0 ){
				memberMoneyLog($vo['uid'],29,-($vo['withdraw_money']-$vo['second_fee']),"提现成功,扣除实际手续费".$vo['second_fee']."元，减去冻结资金，到帐金额".($vo['withdraw_money']-$vo['second_fee'])."元",'0','@网站管理员@',0,-$vo['second_fee']);
				$model->success_money = $vo['withdraw_money'];
			//	SMStip("withdraw",$um['user_phone'],array("#USERANEM#","#MONEY#"),array($um['user_name'],($vo['withdraw_money']-$vo['second_fee'])));
			}else{
				memberMoneyLog($vo['uid'],29,-($vo['withdraw_money']),"提现成功,扣除实际手续费".$vo['second_fee']."元，减去冻结资金，到帐金额".($vo['withdraw_money'])."元",'0','@网站管理员@');
				$model->success_money = $vo['withdraw_money'];
				//SMStip("withdraw",$um['user_phone'],array("#USERANEM#","#MONEY#"),array($um['user_name'],$vo['withdraw_money']));
			}*/
			$res = memberMoneyLog($vo['uid'],29,-($vo['withdraw_money']),"提现成功：扣除手续费".$secondfee."元，实到帐金额".($vo['withdraw_money']-$secondfee)."元",'0','@网站管理员@',0,-$secondfee);


			if($res){
				$bankInfo = M('member_banks')->where('uid='.$vo['uid'])->find();
				$model->bankcard = $bankInfo['bank_num'];
			}

			$model->success_money = $vo['withdraw_money'];


			//SMStip("withdraw",$um['user_phone'],array("#USERANEM#","#MONEY#"),array($um['user_name'],($vo['withdraw_money']-$vo['second_fee'])));
			SMStip("withdraw",$um['user_phone'],array("#USERANEM#","#MONEY#"),array($um['user_name'],($vo['withdraw_money'])));
		}elseif($vo['withdraw_status']<>1 && $status==1){
			addInnerMsg($vo['uid'],"您的提现申请已通过","您的提现申请已通过，正在处理中");
			// 统一为：都从当笔提现金额中扣除手续费
			/*if($vo['all_money']  <=$secondfee ){
				
				//memberMoneyLog($vo['uid'],36,-($vo['withdraw_money']),"提现申请已通过，扣除实际手续费".$secondfee."元，到帐金额".($vo['withdraw_money'])."元",'0','@网站管理员@',-$secondfee);
				
				memberMoneyLog($vo['uid'],12,$vo['withdraw_money'],"提现未通过,返还",'0','@网站管理员@',0,$vo['withdraw_back_money']);
				$model->success_money = $vo['withdraw_money']-$secondfee;
			}else{
				memberMoneyLog($vo['uid'],36,-$vo['withdraw_money'],"提现申请已通过，扣除实际手续费".$secondfee."元，到帐金额".($vo['withdraw_money'])."元",'0','@网站管理员@',-$secondfee);
				$model->success_money = $vo['withdraw_money'];
			}*/
			memberMoneyLog($vo['uid'],36,-($secondfee),"提现申请已通过，扣除手续费".$secondfee."元，到帐金额".($vo['withdraw_money']-$secondfee)."元",'0','@网站管理员@',-$secondfee);
			$model->success_money = $vo['withdraw_money']-$secondfee;


			$model->withdraw_fee = $vo['withdraw_fee'];
			$model->second_fee = $secondfee;
		}
		//////////////////////////
		$result = $model->save();

		if ($result) { //保存成功
			alogs("withdraw",$id,$status,$deal_info);//管理员操作日志
			//成功提示

			$this->assign('jumpUrl', __URL__);
			$this->success(L('修改成功'));
		} else {
			alogs("withdraw",$id,$status,'提现处理操作失败！');//管理员操作日志
			//$this->assign("waitSecond",10000);
			//失败提示
			$this->error(L('修改失败'));
		}


		$vm = M("member_moneylog")->field("info")->where("uid = {$vo['uid']} and type=29")->limit(1)->order('id desc')->select();
		//sendsms($um['user_phone'],$vm[0]['info']."【友情提醒】");

	}

	public function _listFilter($list){
		$listType = C('WITHDRAW_STATUS');
		$row=array();
		foreach($list as $key=>$v){
			$v['withdraw_status'] = $listType[$v['withdraw_status']];
			$v['uname'] = M("members")->getFieldById($v['uid'],'user_name');
			$v['real_name'] = M("member_info")->getFieldById($v['uid'],'real_name');
			$row[$key]=$v;
		}
		return $row;
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
		$map['w.withdraw_status'] =1;
		$count = M('member_withdraw w')->join("{$this->pre}members m ON w.uid=m.id")->where($map)->count('w.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		//$field= 'w.*,m.user_name,mi.real_name,w.id,w.uid ';
		//$list = M('member_withdraw w')->field($field)->join("{$this->pre}members m ON w.uid=m.id")->join("{$this->pre}member_info mi ON w.uid=mi.uid")->where($map)->order(' w.id DESC ')->limit($Lsql)->select();
		$field= 'w.*,w.withdraw_money-w.withdraw_fee as success_money,m.user_name,m.user_phone,mi.real_name,w.id,w.uid,(mm.account_money+mm.back_money) all_money,mb.bank_name,mb.bank_num,mb.bank_province,mb.bank_city,mb.bank_address';
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
		//$row[0]=array('ID','用户名','真实姓名','提现金额','应到账金额','提现时间','提现状态','处理人','处理时间','处理说明','银行名称','银行支行','银行卡号');
		$row[0]=array('序号','银行','省','市','支行','用户名','姓名','卡号','提现金额','到账金额','电话','备注');
		$i=1;
		foreach($list as $v){
			$row[$i]['uid'] = $v['id'];
			$row[$i]['bank_name'] = $v['bank_name'];
			$row[$i]['bank_province'] = $v['bank_province'];
			$row[$i]['bank_city'] = $v['bank_city'];
			$row[$i]['bank_address'] = $v['bank_address'];
			$row[$i]['user_name'] = $v['user_name'];
			$row[$i]['real_name'] = $v['real_name'];
			$row[$i]['bank_num'] = $v['bank_num'];
			$row[$i]['withdraw_money'] = $v['withdraw_money'];
			if($v['withdraw_status'] ==3)
			{
				$row[$i]['success_money'] = 0;
			}else
			{
				$row[$i]['success_money'] = $v['success_money'];
			}
			//$row[$i]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
			//$row[$i]['withdraw_status'] = $startarr[$v['withdraw_status']];
			//$row[$i]['deal_user'] = $v['deal_user'] ? $v['deal_user'] : '无';
			//$row[$i]['deal_time'] =  date('Y-m-d H:i:s',$v['deal_time']);
			$row[$i]['user_phone'] = $v['user_phone'];
			$row[$i]['deal_info'] = $v['deal_info'];
			$i++;
		}


		$xls->addArray($row);
		$xls->generateXML("membersInfo");

	}







}
?>