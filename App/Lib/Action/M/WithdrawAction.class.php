<?php
header("Content-Type: text/html;charset=utf-8");
class WithdrawAction extends MCommonAction {

    public function index(){
		
		$bank=M('member_banks')->where("uid={$this->uid}")->find();
		if(!is_array($bank)){
		echo "<script> 
			alert('您还未绑定银行账号');
			window.location.href='/member/bank#fragment-1';
			
			 
     </script>";
		}
		
		$this->display();
    }
	public function tx(){
		$pre=C('DB_PREFIX');
		$field = "m.user_name,m.user_phone,(mm.account_money+mm.back_money) all_money,mm.account_money,mm.back_money,i.real_name,b.bank_num,b.bank_name,b.bank_address,b.bank_province,b.bank_city";
		$vo = M('members m')->field($field)->join("{$pre}member_info i on i.uid = m.id")->join("{$pre}member_money mm on mm.uid = m.id")->join("{$pre}member_banks b on b.uid = m.id")->where("m.id={$this->uid}")->find();
		if(empty($vo['bank_num'])) $data['html'] = '<script type="text/javascript">alert("您还未绑定银行帐户，请先绑定");window.location.href="'.__APP__.'/member/Capital/index";</script>';
		
            $borrow_info = M("borrow_info")
                        ->field("sum(borrow_money+borrow_interest+borrow_fee) as borrow, sum(repayment_money+repayment_interest) as also")
                        ->where("borrow_uid = {$this->uid} and borrow_type=4 and borrow_status in (0,2,4,6,8,9,10)")
                        ->find();
           // $vo['all_money'] -= $borrow_info['borrow'] + $borrow_info['also'];
			$this->assign( "vo",$vo);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
	public function hktx(){
		$pre=C('DB_PREFIX');
		$field = "m.user_name,m.user_phone,(mm.account_money+mm.back_money) all_money,mm.account_money,mm.back_money,i.real_name,b.bank_num,b.bank_name,b.bank_address,b.bank_province,b.bank_city";
		$vo = M('members m')->field($field)->join("{$pre}member_info i on i.uid = m.id")->join("{$pre}member_money mm on mm.uid = m.id")->join("{$pre}member_banks b on b.uid = m.id")->where("m.id={$this->uid}")->find();
		if(empty($vo['bank_num'])) $data['html'] = '<script type="text/javascript">alert("您还未绑定银行帐户，请先绑定");window.location.href="'.__APP__.'/member/Capital/index";</script>';
		
            $borrow_info = M("borrow_info")
                        ->field("sum(borrow_money+borrow_interest+borrow_fee) as borrow, sum(repayment_money+repayment_interest) as also")
                        ->where("borrow_uid = {$this->uid} and borrow_type=4 and borrow_status in (0,2,4,6,8,9,10)")
                        ->find();
           // $vo['all_money'] -= $borrow_info['borrow'] + $borrow_info['also'];
			$this->assign( "vo",$vo);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
    public function withdraw(){
		
		
		$pre = C('DB_PREFIX');
		$money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
		$amount = floatval($_POST['Amount']);  
		// if($amount > $money_info['account_money']){
			// $this->error('可用余额资金余额不足!');
		// }
        $amount > ($money_info['account_money']+$money_info['back_money']) &&  $this->error('提现金额超过了可用资金金额！');
		$tx['uid']=$this->uid;
		$tx['add_ip']=get_client_ip();
		$tx['add_time']=time();
		$tx['withdraw_money']= $amount;
		$nid=M('member_withdraw')->add($tx);
		
		$field = "m.user_name,m.user_phone,(mm.account_money+mm.back_money) all_money,mm.account_money,mm.back_money,i.real_name,b.bank_num,b.bank_name,b.bank_address,b.bank_province,b.bank_city";
		$vo = M('members m')->field($field)->join("{$pre}member_info i on i.uid = m.id")->join("{$pre}member_money mm on mm.uid = m.id")->join("{$pre}member_banks b on b.uid = m.id")->where("m.id={$this->uid}")->find();
		if(empty($vo['bank_num'])) $data['html'] = '<script type="text/javascript">alert("您还未绑定银行帐户，请先绑定");window.location.href="'.__APP__.'/M/Capital/index";</script>';
		else{
			$tqfee = explode( "|", $this->glo['fee_tqtx']);
			$fee[0] = explode( "-", $tqfee[0]);
			$fee[1] = explode( "-", $tqfee[1]);
			$fee[2] = explode( "-", $tqfee[2]);
			$this->assign( "fee",$fee);
            $borrow_info = M("borrow_info")
                        ->field("sum(borrow_money+borrow_interest+borrow_fee) as borrow, sum(repayment_money+repayment_interest) as also")
                        ->where("borrow_uid = {$this->uid} and borrow_type=4 and borrow_status in (0,2,4,6,8,9,10)")
                        ->find();
            $vo['all_money'] -= $borrow_info['borrow'] + $borrow_info['also'];
				
			$vo['OrderNo']=$nid;
		
          
		}
		//dump($vo);die;
		$va=M('escrow_account')->where("uid={$this->uid}")->find();
	
	$submitdata['WithdrawMoneymoremore']=$va['qdd_marked'];
	if($nid)$submitdata['OrderNo']=date("YmdHi").$nid;
	$submitdata['CardNo']=$vo['bank_num'];
	$submitdata['CardType']=0;//(0.借记卡 1.信用卡)
	$submitdata['BankCode']=$vo['bank_name'];//银行代码
	$submitdata['BranchBankName']='';//auto_charset($vo['bank_name']);//
	$submitdata['Province']=$vo['bank_province'];
	$submitdata['City']=$vo['bank_city'];
	$submitdata['FeePercent']=0;//$vo[''];
	$submitdata['Amount']= $amount;
	//$submitdata['txtPassword']=$vo[''];//提现密码
	$submitdata['PlatformMoneymoremore']=$va['platform_marked'];
	$submitdata['Remark1']='';
		//dump($submitdata);exit;
		import("ORG.Loan.Escrow");
        $loan = new Escrow();
		
		
		//dump($submitdata);die;
        $data =  $loan->wapwithdraw($submitdata);
		//dump($data);die;
        $form =  $loan->setForm($data, 'withdraw');
        echo $form;
        exit;
    }
	
 public function hkwithdraw(){
		
		
		$pre = C('DB_PREFIX');
		$money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
		$amount = floatval($_POST['Amount']); 
		if($amount > $money_info['back_money']){
			$this->error('回款资金余额不足');
		}
        $amount > ($money_info['account_money']+$money_info['back_money']) &&  $this->error('提现金额超过了可用资金金额！');//被注释了
		$tx['uid']=$this->uid;
		$tx['add_ip']=get_client_ip();
		$tx['add_time']=time();
		$tx['withdraw_money']= $amount;
		$nid=M('member_withdraw')->add($tx);
		
		$field = "m.user_name,m.user_phone,(mm.account_money+mm.back_money) all_money,mm.account_money,mm.back_money,i.real_name,b.bank_num,b.bank_name,b.bank_address,b.bank_province,b.bank_city";
		$vo = M('members m')->field($field)->join("{$pre}member_info i on i.uid = m.id")->join("{$pre}member_money mm on mm.uid = m.id")->join("{$pre}member_banks b on b.uid = m.id")->where("m.id={$this->uid}")->find();
		if(empty($vo['bank_num'])) $data['html'] = '<script type="text/javascript">alert("您还未绑定银行帐户，请先绑定");window.location.href="'.__APP__.'/member/bank#fragment-1";</script>';
		else{
			$tqfee = explode( "|", $this->glo['fee_tqtx']);
			$fee[0] = explode( "-", $tqfee[0]);
			$fee[1] = explode( "-", $tqfee[1]);
			$fee[2] = explode( "-", $tqfee[2]);
			$this->assign( "fee",$fee);
            $borrow_info = M("borrow_info")
                        ->field("sum(borrow_money+borrow_interest+borrow_fee) as borrow, sum(repayment_money+repayment_interest) as also")
                        ->where("borrow_uid = {$this->uid} and borrow_type=4 and borrow_status in (0,2,4,6,8,9,10)")
                        ->find();
            $vo['all_money'] -= $borrow_info['borrow'] + $borrow_info['also'];
				
			$vo['OrderNo']=$nid;
		
          
		}
		$va=M('escrow_account')->where("uid={$this->uid}")->find();
	
	$submitdata['WithdrawMoneymoremore']=$va['qdd_marked'];
	if($nid)$submitdata['OrderNo']=date("YmdHi").$nid;
	$submitdata['CardNo']=$vo['bank_num'];
	$submitdata['CardType']=0;//(0.借记卡 1.信用卡)
	$submitdata['BankCode']=$vo['bank_name'];//银行代码
	$submitdata['BranchBankName']='';//auto_charset($vo['bank_name']);//
	$submitdata['Province']=$vo['bank_province'];
	$submitdata['City']=$vo['bank_city'];
	$submitdata['FeePercent']=100;//$vo[''];
	$submitdata['Amount']= $amount;
	//$submitdata['txtPassword']=$vo[''];//提现密码
	$submitdata['PlatformMoneymoremore']=$va['platform_marked'];
	$submitdata['Remark1']='';
		//dump($submitdata);exit;
		import("ORG.Loan.Escrow");
        $loan = new Escrow();

        $data =  $loan->withdraws($submitdata);
        $form =  $loan->setForm($data, 'withdraw');
        echo $form;
        exit;
    }
	
	
	
	
	
	public function backwithdraw(){
		$id = intval($_GET['id']);
		$map['withdraw_status'] = 0;
		$map['uid'] = $this->uid;
		$map['id'] = $id;
		$vo = M('member_withdraw')->where($map)->find();
		if(!is_array($vo)) ajaxmsg('',0);
		///////////////////////////////////////////////
		$field = "(mm.account_money+mm.back_money) all_money,mm.account_money,mm.back_money";
		$m = M('member_money mm')->field($field)->where("mm.uid={$this->uid}")->find();
		////////////////////////////////////////////////////
		$newid = M('member_withdraw')->where($map)->delete();
		if($newid){
			$res = memberMoneyLog($this->uid,5,$vo['withdraw_money'],"撤消提现",'0','@网站管理员@',0,$vo['withdraw_back_money']);
		}
		if($res) ajaxmsg();
		else ajaxmsg("",0);
	}

    public function withdrawlog(){
		if($_GET['start_time']&&$_GET['end_time']){
			$_GET['start_time'] = strtotime($_GET['start_time']." 00:00:00");
			$_GET['end_time'] = strtotime($_GET['end_time']." 23:59:59");
			
			if($_GET['start_time']<$_GET['end_time']){
				$map['add_time']=array("between","{$_GET['start_time']},{$_GET['end_time']}");
				$search['start_time'] = $_GET['start_time'];
				$search['end_time'] = $_GET['end_time'];
			}
		}

		$map['uid'] = $this->uid;
		$list = getWithDrawLog($map,15);
		//dump($list);exit;
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);
		
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    
    /**
    * 托管账户转账
    * 只提交订单不处理，需要后台审核后才能处理是否转账
    * @author 张继立  2014-04-30
    */
    public function transfer()
    {
        
    }

	/**
	*提现返回前台
	*
	*/
	public function withdrawreturn(){
		//dump($_POST);exit;
		$WithdrawMoneymoremore = $_POST["WithdrawMoneymoremore"];
	$PlatformMoneymoremore = $_POST["PlatformMoneymoremore"];
	$LoanNo = $_POST["LoanNo"];
	$OrderNo = $_POST["OrderNo"];
	$Amount = $_POST["Amount"];
	$FeePercent = $_POST["FeePercent"];
	$Fee = $_POST["Fee"];
	$FreeLimit = $_POST["FreeLimit"];
	$RandomTimeStamp = $_POST["$RandomTimeStamp"];
	$Remark1 = $_POST["$Remark1"];
	$Remark2 = $_POST["$Remark2"];
	$Remark3 = $_POST["$Remark3"];
	$ResultCode = $_POST["ResultCode"];
	$SignInfo = $_POST["SignInfo"];
	
	$dataStr = $WithdrawMoneymoremore.$PlatformMoneymoremore.$LoanNo.$OrderNo.$Amount.$FeePercent.$Fee.$FreeLimit.$RandomTimeStamp.$Remark1.$Remark2.$Remark3.$ResultCode;
	import("ORG.Loan.Escrow");
     
        $loan = new Escrow();
		
	if($this->antistate == 0)
	{
		$dataStr = strtoupper(md5($dataStr));
		
	}
	 
	
	 $msg = L($_REQUEST['ResultCode']);
	if($_REQUEST['ResultCode']==88){
		$msg = "提现成功";
	}
		$this->success($msg,"/member/Capital/index");
	
	}
	
	/**
	*提现返回后台
	*
	*/


	public function withdrawsnotify(){
	$WithdrawMoneymoremore = $_POST["WithdrawMoneymoremore"];
	$PlatformMoneymoremore = $_POST["PlatformMoneymoremore"];
	$LoanNo = $_POST["LoanNo"];
	$OrderNo = $_POST["OrderNo"];
	$Amount = $_POST["Amount"];
	$FeePercent = $_POST["FeePercent"];
	$Fee = $_POST["Fee"];
	$FreeLimit = $_POST["FreeLimit"];
	$RandomTimeStamp = $_POST["$RandomTimeStamp"];
	$Remark1 = $_POST["$Remark1"];
	$Remark2 = $_POST["$Remark2"];
	$Remark3 = $_POST["$Remark3"];
	$ResultCode = $_POST["ResultCode"];
	$SignInfo = $_POST["SignInfo"];
	
	$dataStr = $WithdrawMoneymoremore.$PlatformMoneymoremore.$LoanNo.$OrderNo.$Amount.$FeePercent.$Fee.$FreeLimit.$RandomTimeStamp.$Remark1.$Remark2.$Remark3.$ResultCode;
	
	if($this->getAntiState== 1)
	{
		$dataStr = strtoupper(md5($dataStr));
	}
	
	$SignInfo=$this->rsa->sign($dataStr);
	$verifySignature = $this->rsa->verify($dataStr,$SignInfo);
	echo "后台通知:".$verifySignature;
	echo "<br>";
	echo "返回码:".$ResultCode;
		if($verifySignature==true){
			if($ResultCode==88){
			$done=$this->withdrawDone(1,$OrderNo);
			}
		}else{
		$this->error('签名错误');
		}
		if($done==true){
		echo 'SUCCESS';
		}
	}
	private function withdrawDone($status,$nid,$oid){
		$done = false;
		$withdrawlog = D('member_withdraw');
		if($this->locked) return false;
		$this->locked = true;
		switch($status){
			case 1:
				$updata['status'] = $status;
				$updata['tran_id'] = text($oid);
				$vo = M('member_withdraw')->field('uid,money,fee,status')->where("nid='{$nid}'")->find();
				if($vo['status']!=0 || !is_array($vo)) return;
				$xid = $withdrawlog->where("uid={$vo['uid']} AND nid='{$nid}'")->save($updata);
				
				$tmoney = floatval($vo['money'] - $vo['fee']);
				memberMoneyLog($this->uid,4,-$withdraw_money,"提现,默认自动扣减手续费".$fee."元",'0','@网站管理员@',0);
					MTip('chk6',$this->uid);
					
				//if(!$newid){
				//	$updata['status'] = 0;
				//	$Moneylog->where("uid={$vo['uid']} AND nid='{$nid}'")->save($updata);
				//	return false;
				//}
				//$vx = M("members")->field("user_phone,user_name")->find($vo['uid']);
				//SMStip("payonline",$vx['user_phone'],array("#USERANEM#","#MONEY#"),array($vx['user_name'],$vo['money']));
			break;
			case 2:
				$updata['status'] = $status;
				$updata['tran_id'] = text($oid);
				$xid = $withdrawlog->where("uid={$vo['uid']} AND nid='{$nid}'")->save($updata);
			break;
			case 3:
				$updata['status'] = $status;
				$xid = $withdrawlog->where("uid={$vo['uid']} AND nid='{$nid}'")->save($updata);
			break;
		}
		
		if($status>0){
			if($xid) $done = true;
		}
		$this->locked = false;
		return $done;
	}

}