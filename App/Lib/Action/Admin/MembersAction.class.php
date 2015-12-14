<?php
// 全局设置
class MembersAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
		$map=array();
		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		if($_REQUEST['mobile']){
			$map['m.user_phone'] = array("like",urldecode($_REQUEST['mobile'])."%");
			$search['mobile'] = urldecode($_REQUEST['mobile']);
		}
		if($_REQUEST['realname']){
			$map['mi.real_name'] = urldecode($_REQUEST['realname']);
			$search['realname'] = $map['mi.real_name'];	
		}
		if($_REQUEST['idcard']){
			$map['mi.idcard'] = urldecode($_REQUEST['idcard']);
			$search['idcard'] = $map['mi.idcard'];	
		}
		if($_REQUEST['is_vip']=='yes'){
			$map['m.user_leve'] = 1;
			$map['m.time_limit'] = array('gt',time());
			$search['is_vip'] = 'yes';	
		}elseif($_REQUEST['is_vip']=='no'){
			$map['_complex'] = 'm.user_leve=0 OR m.time_limit<'.time();
			$search['is_vip'] = 'no';	
		}
		if($_REQUEST['is_transfer']=='yes'){
			$map['m.is_transfer'] = 1;
		}elseif($_REQUEST['is_transfer']=='no'){
			$map['m.is_transfer'] = 0;
		}
		if($_REQUEST['is_pc']=='yes'){
			$map['m.ent'] = 1;
			$search['is_pc'] = $_REQUEST['is_pc'];
		}elseif($_REQUEST['is_pc']=='no'){
			$search['is_pc'] = $_REQUEST['is_pc'];
			$map['m.ent'] = 0;
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_name']){
				$map['m.recommend_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name']){
				$cusname = urldecode($_REQUEST['customer_name']);
				//$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$Retemp = M('members')->field("id")->where("user_name = '{$cusname}'")->find();
				$kfid = $Retemp['id'];
				$map['m.recommend_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['lx']) && isset($_REQUEST['money'])){
			if($_REQUEST['money']=='0'){
				$search_money = '0.00';
			}else{
				$search_money = $_REQUEST['money'];
			}
			if($_REQUEST['lx']=='allmoney'){
				if($_REQUEST['bj']=='gt'){
					$bj = '>';
				}else if($_REQUEST['bj']=='lt'){
					$bj = '<';
				}else if($_REQUEST['bj']=='eq'){
					$bj = '=';
				}
				$map['_string'] = "(mm.account_money+mm.back_money) ".$bj.$search_money;
			}else{
				$map[$_REQUEST['lx']] = array($_REQUEST['bj'],$search_money);
			}
			$search['bj'] = $_REQUEST['bj'];	
			$search['lx'] = $_REQUEST['lx'];	
			$search['money'] = $search_money;	
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("between",$timespan);
			$search['start_time'] = $_REQUEST['start_time'];	
			$search['end_time'] = $_REQUEST['end_time'];	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.reg_time'] = array("gt",$xtime);
			$search['start_time'] = $_REQUEST['start_time'];	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("lt",$xtime);
			$search['end_time'] = $_REQUEST['end_time'];	
		}
		//分页处理
		import("ORG.Util.Page");
		$count = M('members m')->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->count('m.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		$field= 'm.id,m.user_phone,m.reg_time,m.ent,m.user_name,m.customer_name,m.user_leve,m.time_limit,mi.real_name,mm.money_freeze,mm.money_collect,(mm.account_money+mm.back_money) account_money,m.user_email,m.recommend_id,m.is_borrow,m.is_vip';
		$list = M('members m')->field($field)->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->limit($Lsql)->order('m.id DESC')->select();
		
		$list=$this->_listFilter($list);
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("lx", array("allmoney"=>'可用余额',"mm.money_freeze"=>'冻结金额',"mm.money_collect"=>'待收金额'));
        $this->assign("pcwap", array("no"=>'只看pc',"yes"=>'只看wap'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));

        $this->display();
    }

	//每个借款标的投资人记录
	public function loanlist(){
		$map=array();
		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);
		}
		if($_REQUEST['mobile']){
			$map['m.user_phone'] = array("like",urldecode($_REQUEST['mobile'])."%");
			$search['mobile'] = urldecode($_REQUEST['mobile']);
		}

		if($_REQUEST['real_name']){
			$map['mi.real_name'] = array("like",urldecode($_REQUEST['real_name'])."%");
			$search['real_name'] = urldecode($_REQUEST['real_name']);
		}

		if($_REQUEST['borrow_id']){
			$map['bi.borrow_id'] = intval($_REQUEST['borrow_id']);
			$search['borrow_id'] = intval($_REQUEST['borrow_id']);
		}

		if($_REQUEST['idcard']){
			$map['mi.idcard'] = array("like",urldecode($_REQUEST['idcard'])."%");
			$search['idcard'] = urldecode($_REQUEST['idcard']);
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);
			$search['end_time'] = urldecode($_REQUEST['end_time']);
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.reg_time'] = array("gt",$xtime);
			$search['start_time'] = urldecode($_REQUEST['start_time']);
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("lt",$xtime);
			$search['end_time'] = urldecode($_REQUEST['end_time']);
		}
		//投资时间
		if(!empty($_REQUEST['invest_start_time']) && !empty($_REQUEST['invest_end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['invest_start_time'])).",".strtotime(urldecode($_REQUEST['invest_end_time']));
			$map['bi.add_time'] = array("between",$timespan);
			$search['invest_start_time'] = urldecode($_REQUEST['invest_start_time']);
			$search['invest_end_time'] = urldecode($_REQUEST['invest_end_time']);
		}elseif(!empty($_REQUEST['invest_start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['invest_start_time']));
			$map['bi.add_time'] = array("gt",$xtime);
			$search['invest_start_time'] = urldecode($_REQUEST['invest_start_time']);
		}elseif(!empty($_REQUEST['invest_end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['invest_end_time']));
			$map['bi.add_time'] = array("lt",$xtime);
			$search['invest_end_time'] = urldecode($_REQUEST['invest_end_time']);
		}
		if($_REQUEST['is_pc']=='yes'){
			$map['bi.ent'] = 1;
			$search['is_pc'] = $_REQUEST['is_pc'];
		}elseif($_REQUEST['is_pc']=='no'){
			$search['is_pc'] = $_REQUEST['is_pc'];
			$map['bi.ent'] = 0;
		}

		//$map['bi.borrow_id'] = $borrow_id;
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_investor bi')->join("{$this->pre}members m ON m.id=bi.investor_uid")->join("{$this->pre}member_info mi ON mi.uid=bi.investor_uid")->where($map)->count('bi.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'bi.id bid,bi.ent,b.id,bi.investor_capital,mi.real_name,mi.idcard,bi.investor_interest,bi.invest_fee,bi.add_time,bi.is_auto,m.user_name,m.id mid,m.user_phone,b.borrow_duration,b.repayment_type,m.customer_name,b.borrow_type,b.borrow_name';
		$list = M('borrow_investor bi')->field($field)->join("{$this->pre}members m ON m.id=bi.investor_uid")->join("{$this->pre}member_info mi ON mi.uid=bi.investor_uid")->join("{$this->pre}borrow_info b ON b.id=bi.borrow_id")->where($map)->limit($Lsql)->order("bi.id DESC")->select();
		$list = $this->_listLoanFilter($list);

		//dump($list);exit;
		$this->assign("list", $list);
		$this->assign("search", $search);
		$this->assign("query", http_build_query($search));
		$this->assign("pcwap", array("no"=>'只看pc',"yes"=>'只看wap'));
		$this->assign("pagebar", $page);
		$this->display();
	}

	public function exportloan(){
		ini_set("memory_limit","-1");
	set_time_limit (0);
		import("ORG.Io.Excel");

		alogs("CapitalAccount",0,1,'执行了会员投资列表导出操作！');//管理员操作日志
		$map=array();
		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);
		}
		if($_REQUEST['mobile']){
			$map['m.user_phone'] = array("like",urldecode($_REQUEST['mobile'])."%");
			$search['mobile'] = urldecode($_REQUEST['mobile']);
		}

		if($_REQUEST['real_name']){
			$map['mi.real_name'] = array("like",urldecode($_REQUEST['real_name'])."%");
			$search['real_name'] = urldecode($_REQUEST['real_name']);
		}

		if($_REQUEST['idcard']){
			$map['mi.idcard'] = array("like",urldecode($_REQUEST['idcard'])."%");
			$search['idcard'] = urldecode($_REQUEST['idcard']);
		}
		if($_REQUEST['borrow_id']){
			$map['bi.borrow_id'] = intval($_REQUEST['borrow_id']);
			$search['borrow_id'] = intval($_REQUEST['borrow_id']);
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("between",$timespan);
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.reg_time'] = array("gt",$xtime);
			$search['start_time'] = $_REQUEST['start_time'];
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("lt",$xtime);
			$search['end_time'] = $_REQUEST['end_time'];
		}
		//投资时间
		if(!empty($_REQUEST['invest_start_time']) && !empty($_REQUEST['invest_end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['invest_start_time'])).",".strtotime(urldecode($_REQUEST['invest_end_time']));
			$map['bi.add_time'] = array("between",$timespan);
			$search['invest_start_time'] = ($_REQUEST['invest_start_time']);
			$search['invest_end_time'] = ($_REQUEST['invest_end_time']);
		}elseif(!empty($_REQUEST['invest_start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['invest_start_time']));
			$map['bi.add_time'] = array("gt",$xtime);
			$search['invest_start_time'] = ($_REQUEST['invest_start_time']);
		}elseif(!empty($_REQUEST['invest_end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['invest_end_time']));
			$map['bi.add_time'] = array("lt",$xtime);
			$search['invest_end_time'] = ($_REQUEST['invest_end_time']);
		}
		if($_REQUEST['is_pc']=='yes'){
			$map['bi.ent'] = 1;
			$search['is_pc'] = $_REQUEST['is_pc'];
		}elseif($_REQUEST['is_pc']=='no'){
			$search['is_pc'] = $_REQUEST['is_pc'];
			$map['bi.ent'] = 0;
		}
		//分页处理

		$field= 'bi.id bid,bi.ent,b.id,bi.investor_capital,mi.real_name,mi.idcard,bi.investor_interest,bi.invest_fee,bi.add_time,bi.is_auto,m.user_name,m.id mid,m.user_phone,b.borrow_duration,b.repayment_type,m.customer_name,b.borrow_type,b.borrow_name';
		$list = M('borrow_investor bi')->field($field)->join("{$this->pre}members m ON m.id=bi.investor_uid")->join("{$this->pre}member_info mi ON mi.uid=bi.investor_uid")->join("{$this->pre}borrow_info b ON b.id=bi.borrow_id")->where($map)->order("bi.id DESC")->select();
		$list = $this->_listLoanFilter($list);


		$row=array();
		$row[0]=array('来源','标ID','用户名','真实姓名','身份证号','手机号','标题','投资金额','应得利息','投资期限','投资成交管理费','还款方式','标种类型','投标方式','投资时间');
		$i=1;
		foreach($list as $v){
			if($v['ent'] =='0'){
				$row[$i]['ent'] = 'pc';
			}else{
				$row[$i]['ent'] = 'wap';
			}
			$row[$i]['mid'] = $v['id'];
			$row[$i]['user_name'] = $v['user_name'];

			$row[$i]['real_name'] = $v['real_name'];
			$row[$i]['idcard'] = $v['idcard'];
			$row[$i]['user_phone'] = $v['user_phone'];

			$row[$i]['borrow_name'] = $v['borrow_name'];
			$row[$i]['investor_capital'] = $v['investor_capital'];
			$row[$i]['investor_interest'] = $v['investor_interest'];
			$row[$i]['borrow_duration'] = $v['borrow_duration'].($v['repayment_type_num'] == 1 ? '天':'个月');
			$row[$i]['invest_fee'] = $v['invest_fee'];
			$row[$i]['repayment_type'] = $v['repayment_type'];
			$row[$i]['borrow_type'] = $v['borrow_type'];
			$row[$i]['is_auto'] = $v['is_auto'];
			$row[$i]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
			$i++;
		}
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("membersInfo");
	}
	public function _listLoanFilter($list){
		session('listaction',ACTION_NAME);
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$listType = $Bconfig['REPAYMENT_TYPE'];
		$BType = $Bconfig['BORROW_TYPE'];
		$row=array();
		$aUser = get_admin_name();
		foreach($list as $key=>$v){
			$v['repayment_type_num'] = $v['repayment_type'];
			$v['repayment_type'] = $listType[$v['repayment_type']];
			$v['borrow_type'] = $BType[$v['borrow_type']];
			if($v['deadline']) $v['overdue'] = getLeftTime($v['deadline']) * (-1);
			if($v['borrow_status']==1 || $v['borrow_status']==3 || $v['borrow_status']==5){
				$v['deal_uname_2'] = $aUser[$v['deal_user_2']];
				$v['deal_uname'] = $aUser[$v['deal_user']];
			}

			$v['last_money'] = $v['borrow_money']-$v['has_borrow'];//新增剩余金额
			if($v['is_auto']==1){
				$v['is_auto']="自动投标";
			}else{
				$v['is_auto']="手动投标";
			}

			$row[$key]=$v;
		}
		return $row;
	}


    public function edit() {
        $model = D(ucfirst($this->getActionName()));
		setBackUrl();
        $id = intval($_REQUEST['id']);
        $vo = $model->find($id);
		$vx = M('member_info')->where("uid={$id}")->find();
		if(!is_array($vx)){
			M('member_info')->add(array("uid"=>$id));
		}else{
			foreach($vx as $key=>$vxe){
				$vo[$key]=$vxe;
			}
		}
		
		///////////////////////
		$vb = M('member_banks')->where("uid={$id}")->find();
		if(!is_array($vb)){
			M('member_banks')->add(array("uid"=>$id));
		}else{
			foreach($vb as $key=>$vbe){
				$vo[$key]=$vbe;
			}
		}
		
		//////////////////////
        $this->assign('vo', $vo);
		$this->assign("utype", C('XMEMBER_TYPE'));
		$this->assign("bank_list",$this->gloconf['BANK_NAME']);
        $this->display();
    }
	
	//添加数据
    public function doEdit() {
		require_once "./config.inc.php";
		//require "./uc_client/client.php";
        $model = D(ucfirst($this->getActionName()));
        $model2 = M("member_info");
		$model3 = M("member_banks");
		
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        if (false === $model2->create()) {
            $this->error($model2->getError());
        }
		if (false === $model3->create()) {
            $this->error($model3->getError());
        }
		
		$model->startTrans();
        if(!empty($model->user_pass)){
			//$ucresult = uc_user_edit($model->user_name,"", $model->user_pass,"",1);
			$model->user_pass=md5($model->user_pass);
		}else{
			unset($model->user_pass);
		}
        if(!empty($model->pin_pass)){
			$model->pin_pass=md5($model->pin_pass);
		}else{
			unset($model->pin_pass);
		}
		
		$model->user_phone = $model2->cell_phone;
		$model3->add_ip = get_client_ip();
		$model3->add_time = time();
		
		$aUser = get_admin_name();
		$kfid = $model->customer_id;
		$model->customer_name = $aUser[$kfid];
		$result = $model->save();
		$result2 = $model2->save();
		$result3 = $model3->save();
		
        //保存当前数据对象
        if ($result || $result2 || $result3) { //保存成功
			$model->commit();
			alogs("Members",0,1,'成功执行了会员信息资料的修改操作！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__);
            $this->success(L('修改成功'));
        } else {
			alogs("Members",0,0,'执行会员信息资料的修改操作失败！');//管理员操作日志
			$model->rollback();
            //失败提示
            $this->error(L('修改失败'));
        }
    }
	
    public function info()
    {	
		if($_GET['user_name']) $search['m.user_name'] = text($_GET['user_name']);
		else $search=array();
		$list = getMemberInfoList($search,10);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);
        $this->assign("search", $search);
        $this->display();
    }
	
    public function infowait()
    {	
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		if($_GET['user_name']) $search['m.user_name'] = text($_GET['user_name']);
		else $search=array();
		$list = getMemberApplyList($search,10);
		
		$this->assign("aType",$Bconfig['APPLY_TYPE']);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);
        $this->display();
    }
	
    public function viewinfo()
    {	
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$this->assign("aType",$Bconfig['APPLY_TYPE']);
		setBackUrl();
		$id = intval($_GET['id']);
		$vx = M('member_apply')->field(true)->find($id);
		$uid = $vx['uid'];
		$vo = getMemberInfoDetail($uid);
		$this->assign("vx",$vx);
		$this->assign("vo",$vo);
		$this->assign("id",$id);
        $this->display();
    }
	
    public function viewinfom()
    {	
		$id = intval($_GET['id']);
		$vo = getMemberInfoDetail($id);
		$this->assign("vo",$vo);
        $this->display();
    }

	public function doEditCredit(){
		$id = intval($_POST['id']);
		$uid = intval($_POST['uid']);
		$data['id'] = $id;
		$data['deal_info'] = text($_POST['deal_info']);
		$data['apply_status'] = intval($_POST['apply_status']);
		$data['credit_money'] = floatval($_POST['credit_money']);
		$newid = M('member_apply')->save($data);
		
		if($newid){
			//审核通过后资金授信改动
			if($data['apply_status']==1){
				$vx = M('member_apply')->field(true)->find($id);
				$umoney = M('member_money')->field(true)->find($vx['uid']);
				
				$moneyLog['uid'] = $vx['uid'];
				if($vx['apply_type']==1){
					$moneyLog['credit_limit'] = floatval($umoney['credit_limit']) + $data['credit_money'];
					$moneyLog['credit_cuse'] = floatval($umoney['credit_cuse']) + $data['credit_money'];
				}elseif($vx['apply_type']==2){
					$moneyLog['borrow_vouch_limit'] = floatval($umoney['borrow_vouch_limit']) + $data['credit_money'];
					$moneyLog['borrow_vouch_cuse'] = floatval($umoney['borrow_vouch_cuse']) + $data['credit_money'];
				}elseif($vx['apply_type']==3){
					$moneyLog['invest_vouch_limit'] = floatval($umoney['invest_vouch_limit']) + $data['credit_money'];
					$moneyLog['invest_vouch_cuse'] = floatval($umoney['invest_vouch_cuse']) + $data['credit_money'];
				}
				
				if(!is_array($umoney))	M('member_money')->add($moneyLog);
				else M('member_money')->where("uid={$vx['uid']}")->save($moneyLog);
			}//审核通过后资金授信改动
			alogs("Members",0,1,'成功执行了会员资料通过后资金授信改动的审核操作！');//管理员操作日志
			$this->success("审核成功",__URL__."/infowait".session('listaction'));
		}else{
			alogs("Members",0,0,'执行会员资料通过后资金授信改动的审核操作失败！');//管理员操作日志
			$this->error("审核失败");
		}
	}
	
    public function moneyedit()
    {
		setBackUrl();
		$this->assign("id",intval($_GET['id']));
		$this->display();
    }
	
    public function doMoneyEdit()
    {
		$id = intval($_POST['id']);
		$uid = $id;
		$info = text($_POST['info']);
		$done=false;
		if(floatval($_POST['account_money'])!=0){
			$done=memberMoneyLog($uid,71,floatval($_POST['account_money']),$info);
		}
		if(floatval($_POST['money_freeze'])!=0){
			$done=false;
			$done=memberMoneyLog($uid,72,floatval($_POST['money_freeze']),$info);
		}
		if(floatval($_POST['money_collect'])!=0){
			$done=false;
			$done=memberMoneyLog($uid,73,floatval($_POST['money_collect']),$info);
		}
		//记录
		
        $this->assign('jumpUrl', __URL__."/index".session('listaction'));
		if($done){
			alogs("Members",0,1,'成功执行了会员余额调整的操作！');//管理员操作日志
			$this->success("操作成功");
		}else{
			alogs("Members",0,0,'执行会员余额调整的操作失败！');//管理员操作日志
			$this->error("操作失败");
		}
    }
	
    public function creditedit()
    {
		setBackUrl();
		$this->assign("id",intval($_GET['id']));
		$this->display();
    }
	
    public function doCreditEdit()
    {
		$id = intval($_POST['id']);
		
		$umoney = M('member_money')->field(true)->find($id);
		if(intval($_POST['credit_limit'])!=0){
			$moneyLog['uid'] = $id;
			$moneyLog['credit_limit'] = floatval($umoney['credit_limit']) + floatval($_POST['credit_limit']);
			$moneyLog['credit_cuse'] = floatval($umoney['credit_cuse']) + floatval($_POST['credit_limit']);
			if(!is_array($umoney))	$newid = M('member_money')->add($moneyLog);
			else $newid = M('member_money')->where("uid={$id}")->save($moneyLog);
		}
		if(intval($_POST['borrow_vouch_limit'])!=0){
			$moneyLog=array();
			$moneyLog['uid'] = $id;
			$moneyLog['borrow_vouch_limit'] = floatval($umoney['borrow_vouch_limit']) + floatval($_POST['borrow_vouch_limit']);
			$moneyLog['borrow_vouch_cuse'] = floatval($umoney['borrow_vouch_cuse']) + floatval($_POST['borrow_vouch_limit']);
			if(!is_array($umoney) && !$newid)	$newid = M('member_money')->add($moneyLog);
			else $newid = M('member_money')->where("uid={$id}")->save($moneyLog);
		}
		if(intval($_POST['invest_vouch_limit'])!=0){
			$moneyLog=array();
			$moneyLog['uid'] = $id;
			$moneyLog['invest_vouch_limit'] = floatval($umoney['invest_vouch_limit']) + floatval($_POST['invest_vouch_limit']);
			$moneyLog['invest_vouch_cuse'] = floatval($umoney['invest_vouch_cuse']) + floatval($_POST['invest_vouch_limit']);
			if(!is_array($umoney) && !$newid)	$newid = M('member_money')->add($moneyLog);
			else $newid = M('member_money')->where("uid={$id}")->save($moneyLog);
		}
		
		//修改会员信用等级积分（E级->AAA级）
		$userCredits = M('members')->field(true)->find($id);
		if(intval($_POST['credits'])!=0){
			$moneyLog=array();
			$moneyLog['id'] = $id;
			$moneyLog['credits'] = intval($userCredits['credits'])+intval($_POST['credits']);
			if(!is_array($userCredits) && !$newid)	$newid = M('members')->add($moneyLog);
			else $newid = M('members')->where("id={$id}")->save($moneyLog);
		}
		
        $this->assign('jumpUrl', __URL__."/index".session('listaction'));
		if($newid){
			alogs("Members",0,1,'成功执行了会员授信调整的操作！');//管理员操作日志
			$this->success("操作成功");
		}else{
			alogs("Members",0,0,'执行会员授信调整的操作失败！');//管理员操作日志
			$this->error("操作失败");
		}
    }
	
	
	public function _listFilter($list){
		$row=array();
		foreach($list as $key=>$v){
			if($v['recommend_id']<>0){
				$v['recommend_name'] = M("members")->getFieldById($v['recommend_id'],"user_name");
			 }else{
				$v['recommend_name'] ="无推荐人";
			 }
			 if($v['is_vip']==1){
				$v['is_vip'] = "<span style='color:red'>内部发标专员</span>";
			 }else{
				$v['is_vip'] ="个人";
			 }
			 if($v['time_limit']=='0'){
			 	$v['user_type'] = '普通会员';
			 }else{
				($v['user_leve']==1 && $v['time_limit']>time())?$v['user_type'] = "<span style='color:red'>VIP会员</span>":$v['user_type'] = "普通会员";
			}
			//($v['user_leve']==1 && $v['time_limit']>time())?$v['user_type'] = "<span style='color:red'>VIP会员</span>":$v['user_type'] = "普通会员";
			$row[$key]=$v;
		}
		return $row;
	}

	public function cards(){
		$pre = C('DB_PREFIX');
		$map=array();

		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);
		}
		if($_REQUEST['idcard']){
			$map['s.card_no'] = array("like",urldecode($_REQUEST['idcard'])."%");
			$search['idcard'] = urldecode($_REQUEST['idcard']);
		}
		if($_REQUEST['realname']){
			$map['mi.real_name'] = urldecode($_REQUEST['realname']);
			$search['realname'] = $map['mi.real_name'];
		}

		if($_REQUEST['is_status']=='yes'){
			$map['s.status'] = 1;
			$search['is_status'] = $_REQUEST['is_status'];
		}elseif($_REQUEST['is_status']=='no'){
			$search['is_status'] = $_REQUEST['is_status'];
			$map['s.status'] = 0;
		}else{
			$map['s.status'] = 1;
		}


		import("ORG.Util.Page");
		$count = M('llpayinfo s')->join("{$pre}members m ON m.id=s.uid")->join("{$pre}member_info mi ON mi.uid=s.uid")->where($map)->count('s.uid');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M('llpayinfo s')->field('s.*,mi.up_time,mi.real_name,mi.idcard,m.user_name,m.ent')->join("{$pre}members m ON m.id=s.uid")->join("{$pre}member_info mi ON mi.uid=s.uid")->where($map)->order("s.createdate DESC")->limit($Lsql)->select();

		$this->assign("search",$search);
		$this->assign("list",$list);
		$this->assign("is_status", array("yes"=>'已认证',"no"=>'未认证'));
		$this->assign("pagebar",$page);
		$this->display();
	}

	public function delcards(){
		$id = (int)$_GET['id'];
		$username = M('members')->where(array('id'=>$id))->getField('user_name');
		$newid = M('llpayinfo')->where(array('uid'=>$id))->delete();
		if($newid){
			alogs("Members",0,1,'删除了会员！'.$username.'&nbsp;(UID='.$id.')的银行卡信息');//管理员操作日志
			$this->success("操作成功");
		}else{
			$this->error("操作失败");
		}
	}

	public function getusername(){
		$uname = M("members")->getFieldById(intval($_POST['uid']),"user_name");
		if($uname) exit(json_encode(array("uname"=>"<span style='color:green'>".$uname."</span>")));
		else exit(json_encode(array("uname"=>"<span style='color:orange'>不存在此会员</span>")));
	}
	
	 public function idcardedit() {
        $model = D(ucfirst($this->getActionName()));
		setBackUrl();
        $id = intval($_REQUEST['id']);
        $vo = $model->find($id);
		$vx = M('member_info')->where("uid={$id}")->find();
		if(!is_array($vx)){
			M('member_info')->add(array("uid"=>$id));
		}else{
			foreach($vx as $key=>$vxe){
				$vo[$key]=$vxe;
			}
		}
        $this->assign('vo', $vo);
		$this->assign("utype", C('XMEMBER_TYPE'));
        $this->display();
    }
	
	//添加身份证信息
    public function doIdcardEdit() {
        $model = D(ucfirst($this->getActionName()));
        $model2 = M("member_info");
		
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        if (false === $model2->create()) {
            $this->error($model->getError());
        }
		
		$model->startTrans();
		/////////////////////////////
		if(!empty($_FILES['imgfile']['name'])){
			$this->fix = false;
			//设置上传文件规则
			$this->saveRule = 'uniqid';
			//$this->saveRule = date("YmdHis",time()).rand(0,1000)."_".$model->id;
			$this->savePathNew = C('ADMIN_UPLOAD_DIR').'Idcard/';
			$this->thumbMaxWidth = C('IDCARD_UPLOAD_H');
			$this->thumbMaxHeight = C('IDCARD_UPLOAD_W');
			$info = $this->CUpload();
			$data['card_img'] = $info[0]['savepath'].$info[0]['savename'];
			$data['card_back_img'] = $info[1]['savepath'].$info[1]['savename'];
			
			if($data['card_img']&&$data['card_back_img']){ 
				$model2->card_img=$data['card_img'];
				$model2->card_back_img=$data['card_back_img'];
			}
		}
		///////////////////////////
		$result = $model->save();
		$result2 = $model2->save();

        //保存当前数据对象
        if ($result || $result2) { //保存成功
			$model->commit();
			alogs("Members",0,1,'成功执行了会员身份证代传的操作！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success('修改成功','/admin/members/index.html');
        } else {
			$model->rollback();
			alogs("Members",0,0,'执行会员身份证代传的操作失败！');//管理员操作日志
            //失败提示
            $this->error('修改失败','/admin/members/index.html');
        }
    }
	///////////////////////////////////	
	
public function export(){
	ini_set("memory_limit","-1");
	set_time_limit (0);
		import("ORG.Io.Excel");
		alogs("CapitalAccount",0,1,'执行了所有会员资金列表导出操作！');//管理员操作日志
		$map=array();
		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);
		}
		if($_REQUEST['mobile']){
			$map['m.user_phone'] = array("like",urldecode($_REQUEST['mobile'])."%");
			$search['mobile'] = urldecode($_REQUEST['mobile']);
		}
		if($_REQUEST['realname']){
			$map['mi.real_name'] = urldecode($_REQUEST['realname']);
			$search['realname'] = $map['mi.real_name'];
		}
		if($_REQUEST['idcard']){
			$map['mi.idcard'] = urldecode($_REQUEST['idcard']);
			$search['idcard'] = $map['mi.idcard'];	
		}
		if($_REQUEST['is_vip']=='yes'){
			$map['m.user_leve'] = 1;
			$map['m.time_limit'] = array('gt',time());
			$search['is_vip'] = 'yes';	
		}elseif($_REQUEST['is_vip']=='no'){
			$map['_complex'] = 'm.user_leve=0 OR m.time_limit<'.time();
			$search['is_vip'] = 'no';	
		}
		if($_REQUEST['customer_name']){
				$map['m.recommend_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name']){
				$cusname = urldecode($_REQUEST['customer_name']);
				//$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$Retemp = M('members')->field("id")->where("user_name = '{$cusname}'")->find();
				$kfid = $Retemp['id'];
				$map['m.recommend_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['lx']) && !empty($_REQUEST['money'])){
			if($_REQUEST['lx']=='allmoney'){
				if($_REQUEST['bj']=='gt'){
					$bj = '>';
				}else if($_REQUEST['bj']=='lt'){
					$bj = '<';
				}else if($_REQUEST['bj']=='eq'){
					$bj = '=';
				}
				$map['_string'] = "(mm.account_money+mm.back_money) ".$bj.$_REQUEST['money'];
			}else{
				$map[$_REQUEST['lx']] = array($_REQUEST['bj'],$_REQUEST['money']);
			}
			$search['bj'] = $_REQUEST['bj'];
			$search['lx'] = $_REQUEST['lx'];
			$search['money'] = $_REQUEST['money'];
		}


		

		if($_REQUEST['is_pc']=='yes'){
			$map['m.ent'] = 1;
			$search['is_pc'] = $_REQUEST['is_pc'];
		}elseif($_REQUEST['is_pc']=='no'){
			$search['is_pc'] = $_REQUEST['is_pc'];
			$map['m.ent'] = 0;
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("between",$timespan);
			$search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));	
			$search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.reg_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}


		//分页处理
		import("ORG.Util.Page");
		$count = M('members m')->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->count('m.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		$pre = $this->pre;
		//$field= 'm.id,m.recommend_id,m.tid,m.reg_time,m.user_email,m.user_phone,m.user_name,m.user_type,mi.idcard,mi.real_name,mm.money_freeze,mm.money_collect,(mm.account_money+mm.back_money) total_money,mm.account_money,mm.back_money';
		//$list = M('members m')->field($field)->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->order("m.id DESC")->select();
		$field= 'm.id,m.user_phone,m.reg_time,m.ent,m.user_name,m.customer_name,m.user_leve,m.time_limit,mi.idcard,m.ent,m.tid,mi.real_name,mm.money_freeze,mm.money_collect,(mm.account_money+mm.back_money) account_money,m.user_email,m.recommend_id,m.is_borrow,m.is_vip';
		$list = M('members m')->field($field)->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->order("m.id DESC")->select();
		
		$list=$this->_listFilter($list);
/**
		foreach($list as $key=>$v){
			$uid = $v['id'];
			//$umoney = M('members')->field('account_money,reward_money')->find($uid);

			//待确认投标
			$investing = M()->query("select sum(investor_capital) as capital from {$pre}borrow_investor where investor_uid={$uid} AND status=1");

			//待收金额
			$invest = M()->query("select sum(investor_capital-receive_capital) as capital,sum(reward_money) as jiangli,sum(investor_interest-receive_interest) as interest from {$pre}borrow_investor where investor_uid={$uid} AND status =4");
			//$invest = M()->query("SELECT sum(capital) as capital,sum(interest) as interest FROM {$pre}investor_detail WHERE investor_uid={$uid} AND `status` =7");
			//待付金额
			$borrow = M()->query("select sum(borrow_money-repayment_money) as repayment_money,sum(borrow_interest-repayment_interest) as repayment_interest from {$pre}borrow_info where borrow_uid={$uid} AND borrow_status=6");


			$withdraw0 = M('member_withdraw')->where("uid={$uid} AND withdraw_status=0")->sum('withdraw_money');//待提现
			$withdraw1 = M('member_withdraw')->where("uid={$uid} AND withdraw_status=1")->sum('withdraw_money');//提现处理中
			$withdraw2 = M('member_withdraw')->where("uid={$uid} AND withdraw_status=2")->sum('withdraw_money');//已提现

			$withdraw3 = M('member_withdraw')->where("uid={$uid} AND withdraw_status=0")->sum('second_fee');//待提现手续费
			$withdraw4 = M('member_withdraw')->where("uid={$uid} AND withdraw_status=1")->sum('second_fee');//处理中提现手续费


			$borrowANDpaid = M()->query("select status,sort_order,borrow_id,sum(capital) as capital,sum(interest) as interest from {$pre}investor_detail where borrow_uid={$uid} AND status in(1,2,3)");
			$investEarn = M('borrow_investor')->where("investor_uid={$uid} and status in(4,5,6)")->sum('receive_interest');
			$investPay = M('borrow_investor')->where("investor_uid={$uid} status<>2")->sum('investor_capital');
			$investEarn1 = M('borrow_investor')->where("investor_uid={$uid} and status in(4,5,6)")->sum('invest_fee');//投资者管理费

			$payonline = M('member_payonline')->where("uid={$uid} AND status=1")->sum('money');

			//累计支付佣金
			$commission1 = M('borrow_investor')->where("investor_uid={$uid}")->sum('paid_fee');
			$commission2 = M('borrow_info')->where("borrow_uid={$uid} AND borrow_status in(2,4)")->sum('borrow_fee');

			$uplevefee = M('member_moneylog')->where("uid={$uid} AND type=2")->sum('affect_money');
			$adminop = M('member_moneylog')->where("uid={$uid} AND type=7")->sum('affect_money');

			$txfee = M('member_withdraw')->where("uid={$uid} AND withdraw_status=2")->sum('second_fee');
			$czfee = M('member_payonline')->where("uid={$uid} AND status=1")->sum('fee');

			$interest_needpay = M()->query("select sum(borrow_interest-repayment_interest) as need_interest from {$pre}borrow_info where borrow_uid={$uid} AND borrow_status=6");
			$interest_willget = M()->query("select sum(investor_interest-receive_interest) as willget_interest from {$pre}borrow_investor where investor_uid={$uid} AND status=4");

			$interest_jiliang =M('borrow_investor')->where("borrow_uid={$uid}")->sum('reward_money');//累计支付投标奖励

			$moneylog = M("member_moneylog")->field("type,sum(affect_money) as money")->where("uid={$uid}")->group("type")->select();
			$listarray=array();
			foreach($moneylog as $vs){
				$listarray[$vs['type']]['money']= ($vs['money']>0)?$vs['money']:$vs['money']*(-1);
			}


			//$money['kyxjje'] = $umoney['account_money'];//可用现金金额
			$money['kyxjje'] = $v['account_money'];//可用现金金额
			$money['dsbx'] = floatval($invest[0]['capital']+$invest[0]['interest']);//待收本息
			$money['dsbj'] = $invest[0]['capital'];//待收本金
			$money['dslx'] = $invest[0]['interest'];//待收利息
			$money['dfbx'] = floatval($borrow[0]['repayment_money']+$borrow[0]['repayment_interest']);//待付本息
			$money['dfbj'] = $borrow[0]['repayment_money'];//待付本金
			$money['dflx'] = $borrow[0]['repayment_interest'];//待付利息
			$money['dxrtb'] = $investing[0]['capital'];//待确认投标

			$money['dshtx'] = $withdraw0+$withdraw3;//待审核提现
			$money['clztx'] = $withdraw1+$withdraw4;//处理中提现

			//$money['jzlx'] = $investEarn;//净赚利息
			$money['jzlx'] = $investEarn-$investEarn1;//净赚利息
			$money['jflx'] = $borrowANDpaid[0]['interest'];//净付利息
			$money['ljjj'] = $umoney['reward_money'];//累计收到奖金
			$money['ljhyf'] = $uplevefee;//累计支付会员费
			$money['ljtxsxf'] = $txfee;//累计提现手续费
			$money['ljczsxf'] = $czfee;//累计充值手续费
			$money['total_2'] = $money['jzlx']-$money['jflx']-$money['ljhyf']-$money['ljtxsxf']-$money['ljczsxf'];

			$money['ljtzje'] = $investPay;//累计投资金额
			$money['ljjrje'] = $borrowANDpaid[0]['borrow_money'];//累计借入金额
			$money['ljczje'] = $payonline;//累计充值金额
			$money['ljtxje'] = $withdraw2;//累计提现金额
			$money['ljzfyj'] = $commission1 + $commission2;//累计支付佣金
			$money['glycz'] = $listarray['7']['money'];//管理员操作资金
		//
			$money['dslxze'] = $interest_willget[0]['willget_interest'];//待收利息总额
			$money['dflxze'] = $interest_needpay[0]['need_interest'];//待付利息总额
			$money['ljtbjl'] = $listarray['20']['money'];//累计投标奖励

			$list[$key]['xmoney'] = $money;

		}
**/
		$row=array();
	//	$row[0]=array('ID','用户名','真实姓名','总余额','可用余额','冻结金额','待收本息金额','待收本金金额','待收利息金额','待付本息金额','待付本金金额','待付利息金额','待确认投标','待审核提现+手续费','处理中提现+手续费','累计提现手续费','累计充值手续费','累计提现金额','累计充值金额','累计支付佣金','累计投标奖励','净赚利息','净付利息','管理员操作资金');
		$row[0]=array('ID','用户名','真实姓名','身份证号','会员类型','会员邮箱','会员手机','可用余额','冻结金额','待收本息','注册时间','推荐人','来源','渠道ID');
		$i=1;
		foreach($list as $v){
			     $row[$i]['uid'] = $v['id'];
				$row[$i]['user_name'] = $v['user_name'];
				$row[$i]['card_pass'] = $v['real_name'];
				$row[$i]['idcard'] = $v['idcard'];
				/**
				if($v['user_type'] ==1)
				{
					$row[$i]['user_type'] = '普通会员';
				}else
				{
					$row[$i]['user_type'] = 'VIP会员';
				}
				*/
				$row[$i]['user_type']  =  strip_tags($v['user_type']);
				$row[$i]['user_email'] = $v['user_email'];
				$row[$i]['user_phone'] = $v['user_phone'];
				$row[$i]['account_money'] = number_format($v['account_money'], 2);
				$row[$i]['money_freeze'] = number_format($v['money_freeze'],2);


				$row[$i]['dsbx'] = number_format($v['money_collect'],2);
			$row[$i]['tjsj']=date('Y-m-d H:i:s',$v['reg_time']);
		$row[$i]['recommend_name']=$v['recommend_name'];
		if($v['ent']==1){
			$row[$i]['ent']='wap';
		}elseif($v['ent']==0){
			$row[$i]['ent']='pc';
		}else {
			$row[$i]['ent']='';
		}
		$row[$i]['tid']=$v['tid'];
				$i++;
			}
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("membersInfo".date('YmdHi'));
	}
	 public function userlog()
    {
		$map=array();
		if($_REQUEST['uname']){
			$map['ms.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		if($_REQUEST['mobile']){
			$map['ms.user_phone'] = array("like",urldecode($_REQUEST['mobile'])."%");
			$search['mobile'] = urldecode($_REQUEST['mobile']);
		}
		if($_REQUEST['promo_name']){
			$map['ms.tid'] = urldecode($_REQUEST['promo_name']);
			$search['promo_name'] = urldecode($_REQUEST['promo_name']);
		}else if($_REQUEST['promo_name'] == '0'){
			$map['ms.tid'] = 0;
			$search['promo_name'] = ($_REQUEST['promo_name']);
		}
		if($_REQUEST['is_pc']=='yes'){
			$map['l.logintype'] = 'wap';
			$search['is_pc'] = $_REQUEST['is_pc'];
		}elseif($_REQUEST['is_pc']=='no'){
			$search['is_pc'] = $_REQUEST['is_pc'];
			$map['l.logintype'] = 'pc';
		}
		if($_REQUEST['customer_name']){
			$cusname = urldecode($_REQUEST['customer_name']);
			//$kfid = M('ausers')->getFieldByUserName($cusname,'id');
			$Retemp = M('members')->field("id")->where("user_name = '{$cusname}'")->find();
			$kfid = $Retemp['id'];
			$map['ms.recommend_id'] = $kfid;
			$search['customer_name'] = $cusname;	
			$search['customer_id'] = $kfid;	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['l.add_time'] = array("between",$timespan);
			$search['start_time'] = $_REQUEST['start_time'];	
			$search['end_time'] = $_REQUEST['end_time'];	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['l.add_time'] = array("gt",$xtime);
			$search['start_time'] = $_REQUEST['start_time'];	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['l.add_time'] = array("lt",$xtime);
			$search['end_time'] = $_REQUEST['end_time'];	
		}
		//分页处理
		import("ORG.Util.Page");
		$count = M('member_login l')->join("{$this->pre}members ms ON ms.id=l.uid")->join("{$this->pre}member_money mm ON mm.uid=l.uid")->join("{$this->pre}member_info mi ON mi.uid=l.uid")->where($map)->count('l.id');

		$p = new Page($count, C('ADMIN_PAGE_SIZE'));	

		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";	
		//分页处理
		$field= 'ms.id,ms.user_phone,l.add_time,ms.ent,ms.tid,ms.user_name,mi.real_name,mm.money_freeze,mm.money_collect,(mm.account_money+mm.back_money) account_money,ms.recommend_id,l.logintype';
		$list = M('member_login l')->field($field)->join("{$this->pre}members ms ON ms.id=l.uid")->join("{$this->pre}member_money mm ON mm.uid=l.uid")->join("{$this->pre}member_info mi ON mi.uid=l.uid")->where($map)->limit($Lsql)->order('l.id DESC')->select();
		$list=$this->_listFilterLog($list);
        $this->assign("pcwap", array("no"=>'只看pc',"yes"=>'只看wap'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));

        $this->display();
    }
    public function _listFilterLog($list){
		$row=array();
		foreach($list as $key=>$v){
			if($v['recommend_id']<>0){
				$v['recommend_name'] = M("members")->getFieldById($v['recommend_id'],"user_name");
			 }else{
				$v['recommend_name'] ="无推荐人";
			 }
			$row[$key]=$v;
		}
		return $row;
	}
	public function userlogexport(){
		ini_set("memory_limit","-1");
		set_time_limit (0);
		import("ORG.Io.Excel");
		alogs("CapitalAccount",0,1,'执行了所有会员资金列表导出操作！');//管理员操作日志
		$map=array();
		if($_REQUEST['uname']){
			$map['ms.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		if($_REQUEST['mobile']){
			$map['ms.user_phone'] = array("like",urldecode($_REQUEST['mobile'])."%");
			$search['mobile'] = urldecode($_REQUEST['mobile']);
		}
		if($_REQUEST['promo_name']){
			$map['ms.tid'] = urldecode($_REQUEST['promo_name']);
			$search['promo_name'] = urldecode($_REQUEST['promo_name']);
		}else if($_REQUEST['promo_name'] == '0'){
			$map['ms.tid'] = 0;
			$search['promo_name'] = ($_REQUEST['promo_name']);
		}
		if($_REQUEST['is_pc']=='yes'){
			$map['l.logintype'] = 'wap';
			$search['is_pc'] = $_REQUEST['is_pc'];
		}elseif($_REQUEST['is_pc']=='no'){
			$search['is_pc'] = $_REQUEST['is_pc'];
			$map['l.logintype'] = 'pc';
		}
		
			
		if($_REQUEST['customer_name']){
			$cusname = urldecode($_REQUEST['customer_name']);
			$Retemp = M('members')->field("id")->where("user_name = '{$cusname}'")->find();
			$kfid = $Retemp['id'];
			$map['ms.recommend_id'] = $kfid;
			$search['customer_name'] = $cusname;	
			$search['customer_id'] = $kfid;	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['l.add_time'] = array("between",$timespan);
			$search['start_time'] = $_REQUEST['start_time'];	
			$search['end_time'] = $_REQUEST['end_time'];	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['l.add_time'] = array("gt",$xtime);
			$search['start_time'] = $_REQUEST['start_time'];	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['l.add_time'] = array("lt",$xtime);
			$search['end_time'] = $_REQUEST['end_time'];	
		}


		//分页处理
		import("ORG.Util.Page");
		$count = M('member_login l')->join("{$this->pre}members ms ON ms.id=l.uid")->join("{$this->pre}member_money mm ON mm.uid=l.uid")->join("{$this->pre}member_info mi ON mi.uid=l.uid")->where($map)->count('l.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		$field= 'ms.id,ms.user_phone,l.add_time,ms.ent,ms.tid,ms.user_name,mi.real_name,mm.money_freeze,mm.money_collect,(mm.account_money+mm.back_money) account_money,ms.recommend_id,l.logintype';
		$list = M('member_login l')->field($field)->join("{$this->pre}members ms ON ms.id=l.uid")->join("{$this->pre}member_money mm ON mm.uid=l.uid")->join("{$this->pre}member_info mi ON mi.uid=l.uid")->where($map)->order('l.id DESC')->select();
		$list=$this->_listFilterLog($list);
		$row=array();
		$row[0]=array('ID','登录时间','登录方式','用户名','真实姓名','手机号','推荐人','可用余额','冻结金额','待收本息','渠道ID');
		$i=1;
		foreach($list as $v){
			     $row[$i]['uid'] = $v['id'];
				$row[$i]['logintime'] = date('Y-m-d H:i:s',$v['add_time']);;
				$row[$i]['logintype'] = $v['logintype'];
				$row[$i]['user_name'] = $v['user_name'];
				$row[$i]['real_name'] = $v['real_name'];
				$row[$i]['user_phone'] = $v['user_phone'];
				$row[$i]['recommend_name']=$v['recommend_name'];

				$row[$i]['account_money'] = number_format($v['account_money'], 2);
				$row[$i]['money_freeze'] = number_format($v['money_freeze'],2);
				$row[$i]['dsbx'] = number_format($v['money_collect'],2);
				$row[$i]['tid']=$v['tid'];

				$i++;
			}
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("userlog".date('YmdHi'));
	}
}
?>