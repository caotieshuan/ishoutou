<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends HCommonAction {
	public function cleanall()
    {
		alogs("Global",0,1,'执行了所有缓存清除操作！');//管理员操作日志
		$dirs	=	array(C('APP_ROOT').'Runtime');
		foreach($dirs as $value)
		{
			rmdirr($value);
			echo "<div style='border:2px solid green; background:#f1f1f1; padding:20px;margin:20px;width:800px;font-weight:bold;color:green;text-align:center;'>\"".$value."\" 目录下缓存清除成功! </div> <br /><br />";
			@mkdir($value,0777,true);
		}
	}
	public function index(){
		session('invitation_code',$_GET['i']);
		$per = C('DB_PREFIX');
	    $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		//网站公告
		$parm['type_id'] =43;
		$parm['limit'] =7;
		$this->assign("noticeList",getArticleList($parm));
		unset($parm);
		//网站公告
		
		//新闻
		$parm['type_id'] = 2;
		$parm['limit'] =6;
		$this->assign("newsList",getArticleList($parm));
		unset($parm);
		//新闻
		///////////////散标列表开始//////////////
		$searchMap = array();
		$searchMap['b.borrow_status']=array("in",'2,4,6,7');
		$searchMap['b.is_tuijian']=array("in",'0,1');
		$parm=array();
		$parm['map'] = $searchMap;
		$parm['limit'] =5;
		$parm['orderby']="b.borrow_status ASC,b.id DESC";
		$listBorrow = getBorrowList($parm);
		$this->assign("listBorrow",$listBorrow);
		///////////////散标列表结束//////////////
		///////////////累计会员人数////////////
		$members = M('members')->count();
		$this->assign("members",$members);//会员总数
		///////////////累计会员人数////////////
		///////////////累计配资人数////////////
		//$member_num = M("members")->count();

		//$this->assign("member_num",$member_num);
		///////////////累计配资人数////////////
		
	
	
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$this->assign("Bconfig",$Bconfig);
			//////天天盈//////////////
		$searchMap = array();
	    $searchMap['b.borrow_status']=array("in",'2,4,6,7');
		$searchMap['b.stock_type']=array("in",'1,2,3,4');
		//$searchMap['b.is_tuijian']=array("in",'0,1');
		$parm=array();
		$parm['map'] = $searchMap;
		$parm['limit'] =5;
		$parm['orderby']="b.borrow_status asc ,b.first_verify_time desc";
		$dayslists = getBorrowList($parm);
		$this->assign("dayslists",$dayslists);
		$progress = '';
		if($dayslists['list']){

			foreach($dayslists['list'] as $val){
				$progress[]=(int)$val['progress'];
			}
		}
		$this->assign('progress',$progress ? json_encode($progress) : '');
		/**
		//月月盈
	   	$searchMap1 = array();
	   	$searchMap1['b.borrow_status']=array("in",'2,4,6,7');
		$searchMap1['b.stock_type']=array("eq",'2');
		//$searchMap['b.is_tuijian']=array("in",'0,1');
		$parm1=array();
		$parm1['map'] = $searchMap1;
		$parm1['limit'] =2;
		$parm1['orderby']="b.borrow_status asc ,b.first_verify_time desc";
		$monthlists = getBorrowList($parm1);	

		$this->assign("monthlists",$monthlists);
		**/
		///////////////累计配资金额////////////

		$borrow_sum = M("borrow_info")->where(array('borrow_status'=>array("in",'6,7,9,10')))->sum("borrow_money");

		///////////////为客户赚取收益////////////
		$investor_profit = M("borrow_info")->where(array('borrow_status'=>array("in",'6,7,9,10')))->sum("borrow_interest");
		$this->assign("investor_profit", $investor_profit);
		///////////////为客户赚取收益////////////

		$this->assign("borrow_sum",$borrow_sum);
		///////////////累计配资金额////////////
		
		///////////////配资盈利列表////////////
		//$shares_list = M("shares_record r")->join("lzh_shares_apply a ON a.id = r.shares_id")->where("r.profit_loss > 0")->field("r.profit_loss,a.principal,a.shares_money,a.u_name")->order("r.add_time DESC")->limit(7)->select();
		//$this->assign("shares_list",getRetRate($shares_list));
		///////////////配资盈利列表////////////
		
		///媒体报道
		$mediaslist = M("media")->where(" is_show  = 1 ")->order(" link_order desc ")->limit(14)->select();
	  	$this->assign("mediaslist",$mediaslist);
		
		///////////////配资列表////////////
		//$shares_apply = M("shares_apply")->where("status in(2,3,6)")->field("u_name,shares_money,examine_time")->order("examine_time DESC")->limit(20)->select();
		//echo M()->getLastSql();exit;
		//$this->assign("shares_apply",$shares_apply);
		///////////////配资列表////////////

		///////////////企业直投列表开始  fan 2013-10-21//////////////
		$parm = array();
		$searchMap = array();
		$searchMap['b.is_show'] = array('in','0,1');
		$searchMap['b.borrow_status'] = array('neq','3');
		$parm['map'] = $searchMap;
		$parm['limit'] = 3;
		$parm['orderby'] = "b.is_tuijian desc,b.is_show desc,b.progress asc";
		$listTBorrow = getTBorrowList($parm);
		$this->assign("listTBorrow",$listTBorrow);
		///////////////企业直投列表结束  fan 2013-10-21//////////////


		if(ListMobile()){
			if($this->uid && M('members')->where('id='.$this->uid.' and ent=1')->count()){
				$redbag = M('redbag')->order('id desc')->where('status=1')->find();//判断活动是否存在
				$rid = $redbag['id'];
				if($rid){
					$usered = M('redbag_list')->where('uid='.$this->uid.' and pid='.$rid)->count();//判断是否领过红包
					if(!$usered){
						$redinfo = M('redbag_list')->order('id asc')->where('uid=0 and pid='.$rid.' and status=1')->find();//判断是否还有剩余红包
						if($redinfo){
							$this->assign('isredbag',true);
						}
					}
				}
			}
		}


		//if($_GET['debug']){
			$this->display();
		//}else{
		//	$this->display('indexv');
		//}
			exit;
		/****************************募集期内标未满,自动流标 新增 2013-03-13*************************\***/
		//流标返回
		$mapT = array();
		$mapT['collect_time']=array("lt",time());
		$mapT['borrow_status'] = 2;
		$tlist = M("borrow_info")->field("id,borrow_uid,borrow_type,borrow_money,first_verify_time,borrow_interest_rate,borrow_duration,repayment_type,collect_day,collect_time")->where($mapT)->select();
		if(empty($tlist)) exit;
		foreach($tlist as $key=>$vbx){
		$borrow_id=$vbx['id'];
		//流标
		$done = false;
		$borrowInvestor = D('borrow_investor');
		$binfo = M("borrow_info")->field("borrow_type,borrow_money,borrow_uid,borrow_duration,repayment_type")->find($borrow_id);
		$investorList = $borrowInvestor->field('id,investor_uid,investor_capital')->where("borrow_id={$borrow_id}")->select();
		M('investor_detail')->where("borrow_id={$borrow_id}")->delete();
		if($binfo['borrow_type']==1) $limit_credit = memberLimitLog($binfo['borrow_uid'],12,($binfo['borrow_money']),$info="{$binfo['id']}号标流标");//返回额度
		$borrowInvestor->startTrans();
		
		$bstatus = 3;
		$upborrow_info = M('borrow_info')->where("id={$borrow_id}")->setField("borrow_status",$bstatus);
		//处理借款概要
		$buname = M('members')->getFieldById($binfo['borrow_uid'],'user_name');
		//处理借款概要
		if(is_array($investorList)){
		$upsummary_res = M('borrow_investor')->where("borrow_id={$borrow_id}")->setField("status",$type);
		foreach($investorList as $v){
		MTip('chk15',$v['investor_uid']);//sss
		$accountMoney_investor = M("member_money")->field(true)->find($v['investor_uid']);
		$datamoney_x['uid'] = $v['investor_uid'];
		$datamoney_x['type'] = ($type==3)?16:8;
		$datamoney_x['affect_money'] = $v['investor_capital'];
		$datamoney_x['account_money'] = ($accountMoney_investor['account_money'] + $datamoney_x['affect_money']);//投标不成功返回充值资金池
		$datamoney_x['collect_money'] = $accountMoney_investor['money_collect'];
		$datamoney_x['freeze_money'] = $accountMoney_investor['money_freeze'] - $datamoney_x['affect_money'];
		$datamoney_x['back_money'] = $accountMoney_investor['back_money'];
		
		//会员帐户
		$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
		$mmoney_x['money_collect']=$datamoney_x['collect_money'];
		$mmoney_x['account_money']=$datamoney_x['account_money'];
		$mmoney_x['back_money']=$datamoney_x['back_money'];
		
		//会员帐户
		$_xstr = ($type==3)?"复审未通过":"募集期内标未满,流标";
		$datamoney_x['info'] = "第{$borrow_id}号标".$_xstr."，返回冻结资金";
		$datamoney_x['add_time'] = time();
		$datamoney_x['add_ip'] = get_client_ip();
		$datamoney_x['target_uid'] = $binfo['borrow_uid'];
		$datamoney_x['target_uname'] = $buname;
		$moneynewid_x = M('member_moneylog')->add($datamoney_x);
		if($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
		}
		}else{
		$moneynewid_x = true;
		$bxid=true;
		$upsummary_res=true;
		}
		
		if($moneynewid_x && $upsummary_res && $bxid && $upborrow_info){
		$done=true;
		$borrowInvestor->commit();
		}else{
		$borrowInvestor->rollback();
		}
		if(!$done) continue;
		

		
		
		MTip('chk11',$vbx['borrow_uid'],$borrow_id);
		$verify_info['borrow_id'] = $borrow_id;
		$verify_info['deal_info_2'] = text($_POST['deal_info_2']);
		$verify_info['deal_user_2'] = 0;
		$verify_info['deal_time_2'] = time();
		$verify_info['deal_status_2'] = 3;
		if($vbx['first_verify_time']>0) M('borrow_verify')->save($verify_info);
		else  M('borrow_verify')->add($verify_info);
		
		$vss = M("members")->field("user_phone,user_name")->where("id = {$vbx['borrow_uid']}")->find();
		SMStip("refuse",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$verify_info['borrow_id']));
		//@SMStip("refuse",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$verify_info['borrow_id']));
		//updateBinfo
		$newBinfo=array();
		$newBinfo['id'] = $borrow_id;
		$newBinfo['borrow_status'] = 3;
		$newBinfo['second_verify_time'] = time();
		$x = M("borrow_info")->save($newBinfo);
		}
		/****************************募集期内标未满,自动流标 新增 2013-03-13****************************/
    }	

/**
快速借款
*/
	function k_loan()
	{
		
		$form['name']=$_POST['name'];
		$form['money']=$_POST['money'];
		$form['phone']=$_POST['phone'];
		$form['add_time']=time();		
		$form=$this->gl($form);
		$model=M($_POST['model']);
		$flog=$this->yz($form);
			$sms=text($_POST['sms']);
			if(empty($_POST['sms']))
			{
				echo "手机验证码不能为空";
				die;
			}else if($sms != session('code_temp'))
			{
				echo "输入的验证码不正确";
				die;
			}

		if($flog == 1)
		{
			if($model->add($form))
			{
				echo "1";
			}
		}

	}

/**
快速投资


	function k_invest()
	{
		$model=M('k_invest');
		$form=$this->gl($model->create());
		$flog=$this->yz($form);
		$model->add_time=time();
		if($flog == 1)
		{
			if($model->add())
			{
				$this->success("申请成功!");
			}else
			{
				$this->error("申请失败！请重试！");
			}
		}
	}
*/


/**
去掉两边的空格
*/
protected function gl($form)
	{
		$array=array();
		foreach($form as $k=>$v)
		{
			$array[$k]=trim($v);
		}
		return $array;
	}



	protected function yz($form)
	{
			
			$flog=1;

			//dump();die;
/**
验证姓名		
*/
		if(empty($form['name']))
		{
			echo "姓名不能为空";
			//$this->error("姓名不能为空");
			$flog=0;
			die;
		}else if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$form['name']) == '')
		{
			echo "姓名格式不正确";
			//$this->error("姓名格式不正确");
			$flog=0;
			die;
		}
		
/**
手机验证		
*/
		if(empty($form['phone']))
		{
			echo "手机号不能为空";
			//$this->error("手机号不能为空");
			$flog=0;
			die;
		}else if(preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#',$form['phone']) == 0)
		{
			echo "您输入的手机号格式不正确";
			//$this->error("您输入的手机号格式不正确");
			$flog=0;
			die;
		}

/**
验证金额
*/
		if(empty($form['money']))
		{
			echo "金额不能为空";
			//$this->error("金额不能为空");
			$flog=0;
			die;

		}
		if(!ereg("^\+?[1-9][0-9]*$",$form['money']))
		{
			echo "输入金额的格式不正确";
			//$this->error("输入金额的格式不正确");
			$flog=0;
			die;
		}
		return $flog;

	}

/**
获取手机验证码
*/

		function phone()
		{
			$smsTxt = FS("Webconfig/smstxt");
			$smsTxt = de_xie($smsTxt);
			 if(preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#',$_POST['phone']) == 0)
			{
				echo "3";
				die;
			}else
			{
				$phone = text($_POST['phone']);
			}
			$code = rand_string_reg(6, 1, 2);
			$datag = get_global_setting();
			$is_manual = $datag['is_manual'];
			if ($is_manual == 0) 
				{ 
			$res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array(session('u_user_name'), $code), $smsTxt['verify_phone']));
				}
			//	dump(session('code_temp'));

			if($res)
			{
				echo "1";

			}else
			{
				echo "0";
			}

		}

		public function download(){
			$this->display();
		}
		public function qq(){
			
			$this->display();
		}
  }
	
