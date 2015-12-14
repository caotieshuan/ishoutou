<?php
// 本类由系统自动生成，仅供测试用途
class AgreementAction extends MCommonAction {
	
 public function downfile(){
		$per = C('DB_PREFIX');
		$borrow_config = require C("APP_ROOT")."Conf/borrow_config.php";

		$invest_id=intval($_GET['id']);

	 	$list = M('investor_detail')->field(true)->where(array('invest_id'=>$invest_id))->select();

		 foreach($list as &$val){
			 $val['intotal'] = $val['capital']+$val['interest'];
		 }

		//$borrow_id=intval($_GET['id']);

		$iinfo = M('borrow_investor i')->field('i.id,i.borrow_id,i.investor_capital,i.investor_interest,i.deadline,i.investor_uid,i.add_time,m.user_name,m.id as user_id')->join("lzh_members m ON m.id = i.investor_uid")->where("(i.investor_uid={$this->uid} OR i.borrow_uid={$this->uid}) AND i.id={$invest_id}")->find();

		$binfo = M('borrow_info')->field('id,total,capital_name,capital_card,repayment_type,borrow_duration,borrow_uid,borrow_type,borrow_use,borrow_money,full_time,add_time,borrow_interest_rate,deadline,second_verify_time,danbao,borrow_name,danbao,capital_name,capital_card')->find($iinfo['borrow_id']);


	 	$mBorrow = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('m.id,mi.real_name,m.user_name,mi.idcard,mi.real_name')->where("m.id={$binfo['borrow_uid']}")->find();
 		//$loan = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name,mi.idcard,mi.real_name')->where("m.id={$binfo['capital_uid']}")->find();

		$mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name,mi.idcard,mi.real_name')->where("m.id={$iinfo['investor_uid']}")->find();
		if(!is_array($iinfo)||!is_array($binfo)||!is_array($mBorrow)||!is_array($mInvest)) exit;


		$detail = M('investor_detail d')->field('d.repayment_time,d.borrow_id,d.investor_uid,d.borrow_uid,d.capital,sum(d.capital+d.interest-d.interest_fee) benxi,d.total')->where("d.borrow_id={$iinfo['borrow_id']} and d.invest_id ={$iinfo['id']}")->group('d.investor_uid')->find();


		//$detailinfo = M('investor_detail d')->join("{$per}borrow_investor bi ON bi.id=d.invest_id")->join("{$per}members m ON m.id=d.investor_uid")->field('d.borrow_id,d.investor_uid,d.borrow_uid,d.capital,sum(d.capital+d.interest-d.interest_fee) benxi,d.total,m.user_name,bi.investor_capital,bi.add_time')->where("d.borrow_id={$iinfo['borrow_id']} and d.invest_id ={$iinfo['id']}")->group('d.investor_uid')->find();
		$detailinfo = M('investor_detail d')->field('d.borrow_id,d.investor_uid,d.borrow_uid,(d.capital+d.interest-d.interest_fee) benxi,d.capital,d.interest,d.interest_fee,d.sort_order,d.deadline')->where("d.borrow_id={$iinfo['borrow_id']} and d.invest_id ={$iinfo['id']}")->select();

		$time = M('borrow_investor')->field('id,add_time')->where("borrow_id={$iinfo['borrow_id']} order by add_time asc")->limit(1)->find();
		
		if($binfo['repayment_type']==1){
			$deadline_last = strtotime("+{$binfo['borrow_duration']} day",$time['add_time']);
		}else{
			$deadline_last = strtotime("+{$binfo['borrow_duration']} month",$time['add_time']);
		}
		$this->assign('deadline_last',$deadline_last);
		$this->assign('detail',$detail);

		$type1 = $this->gloconf['BORROW_USE'];
		$binfo['borrow_use'] = $type1[$binfo['borrow_use']];
		$ht=M('hetong')->field('hetong_img,name,dizhi,tel')->find();
		$this->find=M("article")->where("id=".$binfo['danbao'])->field('title,art_keyword,art_writer')->find();
		$this->assign("ht",$ht);
		$type = $borrow_config['REPAYMENT_TYPE'];
		//echo $binfo['repayment_type'];
		$binfo['repayment_name'] = $type[$binfo['repayment_type']];

		$iinfo['repay'] = getFloatValue(($iinfo['investor_capital']+$iinfo['investor_interest'])/$binfo['borrow_duration'],2);

		$iinfo['investor_capitaly']=cny($iinfo['investor_capital']);

		//$binfo['borrow_interest_rate']=substr(cny($binfo['borrow_interest_rate']), 0, -3);
		//dump($binfo['borrow_interest_rate']);die;
		$this->assign("bid","bytp2pD");
		//print_r($type);

	 	$glo = get_global_setting();

	 	$this->assign('fee_invest_manage',$glo['fee_invest_manage']);

		$this->assign('time',$time);
		$this->assign('list',$list);
		//$this->assign('loan',$loan);
		$this->assign('iinfo',$iinfo);
		$this->assign('binfo',$binfo);
		$this->assign('mBorrow',$mBorrow);
		$this->assign('mInvest',$mInvest);
		$detail_list = M('investor_detail')->field(true)->where("invest_id={$invest_id}")->select();

		$HTML = '';
		foreach($detailinfo as $rows){
		
			$HTML .= '<tr><td>' . date('Y年m月d日', $rows['deadline']) . '</td><td>'. ($rows['interest']-$rows['interest_fee']) .'</td><td>'. $rows['capital'] .'</td></tr>';
		}
		$this->assign('HTML', $HTML);
		$this->assign("detail_list",$detail_list);
		//$this->assign("REPAYMENT_TYPE",$borrow_config['REPAYMENT_TYPE']);
		if(isset($_GET['dl'])){ // 带有flag标记并且为dl
			Vendor('Mpdf.mpdf');
			$mpdf=new mPDF('UTF-8','A4','','',15,15,44,15);
			$mpdf->useAdobeCJK = true; 
			$mpdf->SetAutoFont(AUTOFONT_ALL);
			$mpdf->SetDisplayMode('fullpage');	
			$mpdf->SetAutoFont();
			$mpdf->SetHTMLFooter(">>{PAGENO}<<");
			$mpdf->WriteHTML($this->fetch('index'));		
			$mpdf->Output('invest.pdf','D');
			exit;
		}

		$this->display('index');
	
    }
	
	 public function downliuzhuanfile(){
		$per = C('DB_PREFIX');
		$borrow_config = require C("APP_ROOT")."Conf/borrow_config.php";
		$type = $borrow_config['REPAYMENT_TYPE'];

		$invest_id=intval($_GET['id']);
		
		$iinfo = M("transfer_borrow_investor")->field(true)->where("investor_uid={$this->uid} AND id={$invest_id}")->find();

		$binfo = M('transfer_borrow_info')->field(true)->find($iinfo['borrow_id']);
		$tou =  M('transfer_investor_detail')->where(" borrow_id={$iinfo['borrow_id']} AND investor_uid={$this->uid} ")->find();
		
		$mBorrow = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name')->where("m.id={$binfo['borrow_uid']}")->find();
		$mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name')->where("m.id={$iinfo['investor_uid']}")->find();
		
		if(!is_array($tou)) $mBorrow['real_name'] = hidecard($mBorrow['real_name'],5);

		$binfo['repayment_name'] = $type[$binfo['repayment_type']];

		$this->assign("bid","LZBHT-".str_repeat("0",5-strlen($binfo['id'])).$binfo['id']);
		
		$detailinfo = M('transfer_investor_detail d')->join("{$per}transfer_borrow_investor bi ON bi.id=d.invest_id")->join("{$per}members m ON m.id=d.investor_uid")->field('d.borrow_id,d.investor_uid,d.borrow_uid,d.capital,sum(d.capital+d.interest-d.interest_fee) benxi,d.total,m.user_name,bi.investor_capital,bi.add_time')->where("d.borrow_id={$iinfo['borrow_id']} and d.invest_id ={$iinfo['id']}")->group('d.investor_uid')->find();
		
		$time = M('transfer_borrow_investor')->field('id,add_time')->where("borrow_id={$iinfo['borrow_id']} order by add_time asc")->limit(1)->find();
		
		$deadline_last = strtotime("+{$binfo['borrow_duration']} month",$time['add_time']);
		
		$this->assign('deadline_last',$deadline_last);
		$this->assign('detailinfo',$detailinfo);

		$type1 = $this->gloconf['BORROW_USE'];
		$binfo['borrow_use'] = $type1[$binfo['borrow_use']];



		$type = $borrow_config['REPAYMENT_TYPE'];
		//echo $binfo['repayment_type'];
		$binfo['repayment_name'] = $type[$binfo['repayment_type']];

		$iinfo['repay'] = getFloatValue(($iinfo['investor_capital']+$iinfo['investor_interest'])/$binfo['borrow_duration'],2);
		$iinfo['investor_capital']=cny($iinfo['investor_capital']);
		$binfo['borrow_interest_rate']=substr(cny($binfo['borrow_interest_rate']), 0, -3);
		
		
		$this->assign('iinfo',$iinfo);
	//dump($binfo);die;
		$this->assign('binfo',$binfo);
		$this->assign('mBorrow',$mBorrow);
		$this->assign('mInvest',$mInvest);

		$detail_list = M('transfer_investor_detail')->field(true)->where("invest_id={$invest_id}")->select();
		$this->assign("detail_list",$detail_list);

		$ht=M('hetong')->field('hetong_img,name,dizhi,tel')->find();
		$this->assign("ht",$ht);

		
		if(isset($_GET['dl'])){ // 带有flag标记并且为dl
			Vendor('Mpdf.mpdf');
			$mpdf=new mPDF('UTF-8','A4','','',15,15,44,15);
			$mpdf->useAdobeCJK = true; 
			$mpdf->SetAutoFont(AUTOFONT_ALL);
			$mpdf->SetDisplayMode('fullpage');	
			$mpdf->SetAutoFont();
			$mpdf->SetHTMLFooter(' >>{PAGENO}<<');
			$mpdf->WriteHTML($this->fetch('transfer'));		
			$mpdf->Output('zgtrbao.pdf','I');
			exit;
		}

		$this->display("transfer");
    }


}