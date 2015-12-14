<?php
class PromoteAction extends ACommonAction
{

	public function index()
	{
		$field = "id,title,contxt,nums,add_time,adminname";

		import("ORG.Util.Page");
		$count = M('promote')->count('id');
		$p = new Page($count, 100);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$list = M('promote')->field($field)->limit($Lsql)->order('id DESC')->select();

		foreach($list as &$val){
			$val['nums'] = M('members')->where(array('tid'=>$val['id']))->count();
		}
		$this->assign('list', $list);
		$this->assign("pagebar", $page);
		$this->display();
	}

	public function lists(){
		$proid = intval($_REQUEST['id']);
		$proinfo = M('promote')->find($proid);
		if(empty($proinfo)){
			$this->error('数据错误');
		}

		$map=array();
		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);
		}
		if($_REQUEST['realname']){
			$map['mi.real_name'] = urldecode($_REQUEST['realname']);
			$search['realname'] = $map['mi.real_name'];
		}
		if($_REQUEST['is_vip']=='yes'){
			$map['m.user_leve'] = 1;
			$map['m.time_limit'] = array('gt',time());
			$search['is_vip'] = 'yes';
		}elseif($_REQUEST['is_vip']=='no'){
			$map['_string'] = 'm.user_leve=0 OR m.time_limit<'.time();
			$search['is_vip'] = 'no';
		}
		if($_REQUEST['is_transfer']=='yes'){
			$map['m.is_transfer'] = 1;
		}elseif($_REQUEST['is_transfer']=='no'){
			$map['m.is_transfer'] = 0;
		}
		if($_REQUEST['ent']=='yes'){
			$map['m.ent'] = 1;
			$search['ent'] = 'no';
		}elseif($_REQUEST['ent']=='no'){
			$map['m.ent'] = 0;
			$search['ent'] = 'no';
		}else{
			$search['ent'] = 'all';
		}

		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
		if($_REQUEST['customer_name']){
			$map['m.customer_id'] = $_REQUEST['customer_id'];
			$search['customer_id'] = $map['m.customer_id'];
			$search['customer_name'] = urldecode($_REQUEST['customer_name']);
		}

		if($_REQUEST['customer_name']){
			$cusname = urldecode($_REQUEST['customer_name']);
			$kfid = M('ausers')->getFieldByUserName($cusname,'id');
			$map['m.customer_id'] = $kfid;
			$search['customer_name'] = $cusname;
			$search['customer_id'] = $kfid;
		}
		//}
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

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);
			$search['end_time'] = urldecode($_REQUEST['end_time']);
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.reg_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;
		}

		$map['m.tid'] = $proid;

		//分页处理
		import("ORG.Util.Page");
		$count = M('members m')->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->count('m.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		$field= 'm.id,m.user_phone,m.ent,m.reg_time,m.user_name,m.customer_name,m.user_leve,m.time_limit,mi.real_name,mm.money_freeze,mm.money_collect,(mm.account_money+mm.back_money) account_money,m.user_email,m.recommend_id,m.is_borrow,m.is_vip';
		$list = M('members m')->field($field)->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->limit($Lsql)->order('m.id DESC')->select();

		$list=$this->_listFilter($list);
		$this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
		$this->assign("lx", array("allmoney"=>'可用余额',"mm.money_freeze"=>'冻结金额',"mm.money_collect"=>'待收金额'));
		$this->assign("list", $list);
		$this->assign("id", $proid);
		$this->assign("proinfo", $proinfo);
		$this->assign("pagebar", $page);
		$this->assign("search", $search);
		$this->assign("query", http_build_query($search));

		$this->display();
	}


	public function _listFilter($list){
		$row=array();
		foreach($list as $key=>$v){
			if($v['recommend_id']<>0){
				$v['recommend_name'] = M("members")->getFieldById($v['recommend_id'],"user_name");
			}else{
				$v['recommend_name'] ="<span style='color:#000'>无推荐人</span>";
			}
			if($v['is_vip']==1){
				$v['is_vip'] = "<span style='color:red'>内部发标专员</span>";
			}else{
				$v['is_vip'] ="个人";
			}
			if(1 == $v['ent']){
				$v['ent'] = 'wap';
			}else{
				$v['ent'] = 'pc';
			}
			($v['user_leve']==1 && $v['time_limit']>time())?$v['user_type'] = "<span style='color:red'>VIP会员</span>":$v['user_type'] = "普通会员";
			$row[$key]=$v;
		}
		return $row;
	}
	public function add(){
		$id = (int)$_GET['id'];
		$data = array();
		if($id){
			$data = M('promote')->find($id);
		}


		if(false === empty($data['id'])){
			$url = U('member/common/register/',array('t'=>$data['id']),'','',true);
			$url .= '<br>'.U('/promotion/index',array('t'=>$data['id']),'','',true);

		}
		$this->assign('data', $data);
		$this->assign('url', $url);
		$this->display();
	}
	public function doSave(){

		$id = (int)$_POST['id'];
		$data = array();
		$data['title'] = htmlspecialchars($_POST['title']);
		//$data['shopname'] = htmlspecialchars($_POST['shopname']);
		$data['contxt'] = htmlspecialchars($_POST['contxt']);

		if(empty($data['title'])){
			$this->error('推广位置必须填写');
		}
		if($id){
			$result = M('promote')->data($data)->where('id='.$id)->save();
		}else{
			$data['add_time'] = time();
			$data['adminname'] = $_SESSION['adminname'];
			$result = M('promote')->data($data)->add();
		}
		if($result){
			$this->success("保存成功", U('/admin/promote/'));
		}else{
			$this->error('保存失败');
		}
	}


	public function export(){

		$proid = (int)$_REQUEST['id'];


		$proinfo = M('promote')->find($proid);

		if(empty($proinfo)){
			$this->error('数据错误');
		}

		import("ORG.Io.Excel");
		alogs("CapitalAccount",0,1,'执行了所有会员资金列表导出操作！');//管理员操作日志
		$map=array();
		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);
		}
		if($_REQUEST['realname']){
			$map['mi.real_name'] = urldecode($_REQUEST['realname']);
			$search['realname'] = $map['mi.real_name'];
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

		$map['m.tid'] = $proid;

		//分页处理
		import("ORG.Util.Page");
		$count = M('members m')->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->count('m.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		$pre = $this->pre;
		$field= 'm.id,m.reg_time,m.user_email,m.user_phone,m.user_name,m.user_type,mi.real_name,mm.money_freeze,mm.money_collect,(mm.account_money+mm.back_money) total_money,mm.account_money,mm.back_money';
		$list = M('members m')->field($field)->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->order("m.id DESC")->select();

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

		$row=array();
		//	$row[0]=array('ID','用户名','真实姓名','总余额','可用余额','冻结金额','待收本息金额','待收本金金额','待收利息金额','待付本息金额','待付本金金额','待付利息金额','待确认投标','待审核提现+手续费','处理中提现+手续费','累计提现手续费','累计充值手续费','累计提现金额','累计充值金额','累计支付佣金','累计投标奖励','净赚利息','净付利息','管理员操作资金');
		$row[0]=array('ID','用户名','会员类型','注册来源','会员邮箱','会员手机','可用余额','冻结金额','待收本息','注册时间');
		$i=1;
		foreach($list as $v){
			$row[$i]['uid'] = $v['id'];
			$row[$i]['card_num'] = $v['user_name'];
			//$row[$i]['card_pass'] = $v['real_name'];
			if($v['user_type'] ==1)
			{
				$row[$i]['user_type'] = '普通会员';
			}else
			{
				$row[$i]['user_type'] = 'VIP会员';
			}

			$row[$i]['user_aaad'] = $proinfo['title'];
			$row[$i]['user_email'] = $v['user_email'];
			$row[$i]['user_phone'] = $v['user_phone'];
			$row[$i]['account_money'] = $v['account_money'];
			$row[$i]['money_freeze'] = $v['money_freeze'];


			$row[$i]['dsbx'] = $v['money_collect'];
			$row[$i]['tjsj']=date('Y-m-d H:i:s',$v['reg_time']);

			$i++;
		}
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("membersInfo");
	}

}

?>
