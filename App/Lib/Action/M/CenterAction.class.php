<?php
  class CenterAction extends HCommonAction {
	   public function center_zfmm(){
		  
		   //echo "38457845";die;
		   $maprow = array();
            if(!empty($_GET['rate'])){
                $searchMap['borrow_interest_rate'] = array("lt",$_GET['rate']);
            }
            $searchMap['borrow_status']=array("in",'2,4,6,7,3'); 
            $parm['map'] = $searchMap;
            $parm['pagesize'] = 2;
            $sort = "desc";
            $parm['orderby']="b.borrow_status ASC,b.id DESC";
            $list = getBorrowListk($parm);
			
			//$this->assign('id', $list['id']);
			$id=$_GET['id'];
			$this->assign("id",$id);
		  // echo "dfjdhfj";die;
		   $this->display("center_zfmm");
	   }
	   
	    public function changepin(){
		$old = md5($_POST['oldpwd']);
		$newpwd1 = md5($_POST['newpwd1']);
		
		
		$c = M('members')->where("id={$this->uid}")->find();
		//var_dump($c);die;
		
		//$c = M('members')->where("id=$uid")->find();
		
		//var_dump($c);die;
		if($old==$newpwd1){
			//ajaxmsg("设置失败，请勿让新密码与老密码相同。",0);
			//$this->error("设置失败，请勿让新密码与老密码相同。",0);
			//var_dump("设置失败，请勿让新密码与老密码相同。",0);
			echo "0";
		}
		//var_dump($c['pin_pass']);die;
		if($c['pin_pass']==""){
			//echo "empty";die;
			if($c['user_pass'] == $old){
				$newid = M('members')->where("id={$this->uid}")->setField('pin_pass',$newpwd1);
				//$newid = M('members')->where("id=$uid")->setField('pin_pass',$newpwd1);
				if($newid) echo "1";//ajaxmsg();
				else echo "3";
				//echo "3";//ajaxmsg("设置失败，请重试",0);
			}else{
				//ajaxmsg("原支付密码(即登录密码)错误，请重试",0);
				echo "4";
			}
		}else{
			//echo "cunzai";die;
			if($c['pin_pass'] == $old){
				$newid = M('members')->where("id={$this->uid}")->setField('pin_pass',$newpwd1);
				if($newid) echo "1";//ajaxmsg();
				else echo "5";//ajaxmsg("设置失败，请重试",0);
			}else{
				//ajaxmsg("原支付密码错误，请重试",0);
				echo "6";
			}
		}
    }
	
	public function center_zfmmque(){
		//echo "irueir";die;
		$this->display();
	}
	//充值页面
	public function cz_online(){
		//echo "fiuirutirt";die;
		$this->display();
		
	}
	
	//充值执行
	public function dopostalipay() {
		//var_dump($_POST);die;
		$savedata['uid'] = $this->uid;
		$savedata['money'] = $this->_post("money");
		$savedata['ali_name'] = $this->_post("ali_name");
		$savedata['add_time'] = time();
		$savedata['status'] = 1;
		$savedata['u_name'] = session("u_user_name");
		$ret = M("member_alipay")->add($savedata);
		if($ret) {
			$res['msg'] = "充值申请成功,请等待审核!";
			$res['status'] = 1;
			echo json_encode($res);
			exit;
		}else {
			$res['msg'] = "充值申请失败,请重试!";
			$res['status'] = 0;
			echo json_encode($res);
			exit;
		}
	}

	//配资成功之后显示的详细信息

	public function pz_order(){
		
		//echo "843785845";die;
		$maprow = array();
		$searchMap['status']=array("in",'1,4,6,7,3'); 
		$parm['map'] = $searchMap;
		$parm['pagesize'] = 2;
        $sort = "desc";
        $parm['orderby']="b.status ASC,b.id DESC";
        $list = getOrder($parm);
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php"; 
		//var_dump($list);die;
		if($this->isAjax()){
				
                $str ='';
                foreach($list['list'] as $vb){
					
					$tims=date('Y-m-d H:i:s',$vb[add_time]);
				    $str.="<div class='box'>";
                    $str.="<p class='tit'><a href='#'>$vb[id]</a></p>";
                    $str.="<table cellpadding='0' cellspacing='0' border='0' class='table'>"; 
					$str.="<tbody>";
                    $str.="<tr>";
                    $str.="<td>用户名：</td>";
					$str.="<td>$vb[user_name]</td>";
					$str.="<td>本金：</td>";
					$str.="<td>$vb[principal]</td>";
					$str.="</tr><tr>";
					$str.="<td>&emsp;管理费：</td>";
					$str.="<td>$vb[manage_fee]</td>";
					$str.="<td>所获配资金额：</td>";
					$str.="<td>$vb[shares_money]</td>";
					$str.="</tr><tr>";
					$str.=" <td>订单号：</td>";
					$str.="<td>$vb[order]</td>";
					$str.=" <td>平仓线：</td>";
					$str.="<td>$vb[open]</td>";
					$str.="</tr><tr>";
					$str.="<td>警戒线：</td>";
					$str.="<td>$vb[alert]</td>";
					$str.="<td>添加时间：</td>";
					$str.="<td>$tims</td>";
					$str.="</tr><tr>";
					$str.="<td>期限：</td>";
					$str.="<td>$vb[duration]</td>";
					$str.="</tr></tbody></table>";
					$str.="<p class='sub'>";
					$str.="<strong class='strong' style='font-size: 12px'>操盘中</strong>";
					$str.="<a class='btn-a fr' style='font-size: 11px' href='javascript:void(0);' onclick='look(($vb[id]),($vb[client_user]),($vb[client_pass]));'>查看HOME账号</a></p></div>";
					
					
					
					
	  
					
                }
				
			echo $str;
            }else{
				
                $this->assign('list', $list);
                $this->assign('Bconfig', $Bconfig);
                $this->display(); 
            }
			
			
		
		
		
		

	}
	
	//我要提现
	public function tk_online(){
		if(!$this->uid) $this->qingxiana();
		$minfo =getMinfo($this->uid,true);
		$vobank = M("member_banks")->field(true)->where("uid = {$this->uid} and bank_num !=''")->find();
		
		$pre = C('DB_PREFIX');
		$field = "m.user_name,m.user_phone,(mm.account_money+mm.back_money) all_money,mm.account_money,mm.back_money,i.real_name,b.bank_num,b.bank_name,b.bank_address";
		$vo = M('members m')->field($field)->join("{$pre}member_info i on i.uid = m.id")->join("{$pre}member_money mm on mm.uid = m.id")->join("{$pre}member_banks b on b.uid = m.id")->where("m.id={$this->uid}")->find();
		$this->assign("vo",$vo);
		$this->assign("vobank",$vobank);
		$this->assign("minfo",$minfo);
		$this->display();
	}
	public function qingxiana(){
		
		
		$this->display("aa");
	}
	
	
	 public function withdraw(){
		
		//echo "3483748";die;
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
	
	public function validate(){
		$pre = C('DB_PREFIX');
		$withdraw_money = floatval($_POST['amount']);
		$pwd = md5($_POST['pwd']);
		$vo = M('members m')->field('mm.account_money,mm.back_money,m.user_leve,m.time_limit')->join("{$pre}member_money mm on mm.uid = m.id")->where("m.id={$this->uid} AND m.pin_pass='{$pwd}'")->find();
        $borrow_info = M("borrow_info")
                        ->field("sum(borrow_money+borrow_interest+borrow_fee) as borrow, sum(repayment_money+repayment_interest) as also")
                        ->where("borrow_uid = {$this->uid} and borrow_type=4 and borrow_status in (0,2,4,6,8,9,10)")
                        ->find();
		if(!is_array($vo)) ajaxmsg("",0);
        $borrow_money = $vo['account_money']+$vo['back_money']-($borrow_info['borrow']+$borrow_info['also']);
        if($borrow_money < $withdraw_money){
            ajaxmsg("存在净值标借款".($borrow_info['borrow']+$borrow_info['also'])."元未还，账户余额提现不足",2);
        }
		if(($vo['account_money']+$vo['back_money'])<$withdraw_money) ajaxmsg("提现额大于帐户余额",2);
		$reward_zhuce = M("members")->field("reward_zhuce")->where("id = {$this->uid}")->find();
		
		
		if($withdraw_money <= $reward_zhuce['reward_zhuce']){
		
			ajaxmsg("账户余额不足！您有".$reward_zhuce['reward_zhuce']."为注册奖励，不可提现！",2);
		}
		
		$start = strtotime(date("Y-m-d",time())." 00:00:00");
		$end = strtotime(date("Y-m-d",time())." 23:59:59");
		$wmap['uid'] = $this->uid;
		$wmap['withdraw_status'] = array("neq",3);
		$wmap['add_time'] = array("between","{$start},{$end}");
		$today_money = M('member_withdraw')->where($wmap)->sum('withdraw_money');	
		$today_time = M('member_withdraw')->where($wmap)->count('id');	
		
		$tqfee = explode("|",$this->glo['fee_tqtx']);
		$fee[0] = explode("-",$tqfee[0]);
		$fee[1] = explode("-",$tqfee[1]);
		$fee[2] = explode("-",$tqfee[2]);
		
		$one_limit = $fee[2][0]*10000;
		if($withdraw_money<100 ||$withdraw_money>$one_limit) ajaxmsg("单笔提现金额限制为100-{$one_limit}元",2);
		$today_limit = $fee[2][1]/$fee[2][0];
		if($today_time>$today_limit){
					$message = "一天最多只能提现{$today_limit}次";
					ajaxmsg($message,2);
		}
		
		if(1==1 || $vo['user_leve']>0 && $vo['time_limit']>time()){
			
		//////////////////////////////////////////
			$itime = strtotime(date("Y-m", time())."-01 00:00:00").",".strtotime( date( "Y-m-", time()).date("t", time())." 23:59:59");
			$wmapx['uid'] = $this->uid;
			$wmapx['withdraw_status'] = array("neq",3);
			$wmapx['add_time'] = array("between","{$itime}");
			$times_month = M("member_withdraw")->where($wmapx)->count("id");
			
			$tqfee1 = explode("|",$this->glo['fee_tqtx']);
			$fee1[0] = explode("-",$tqfee1[0]);
			$fee1[1] = explode("-",$tqfee1[1]);
			if(($withdraw_money-$vo['back_money'])>=0){
				$maxfee1 = ($withdraw_money-$vo['back_money'])*$fee1[0][0]/1000;
				if($maxfee1>=$fee1[0][1]){
					$maxfee1 = $fee1[0][1];
				}
				
				$maxfee2 = $vo['back_money']*$fee1[1][0]/1000;
				if($maxfee2>=$fee1[1][1]){
					$maxfee2 = $fee1[1][1];
				}
				
				$fee = $maxfee1+$maxfee2;
				$money = $withdraw_money-$vo['back_money'];
			}else{
				$fee = $vo['back_money']*$fee1[1][0]/1000;
			}
			
			if($withdraw_money <= $vo['back_money'])
			{
				$message = "您好，您申请提现{$withdraw_money}元，小于目前的回款总额{$vo['back_money']}元，因此无需手续费，确认要提现吗？";
			}else{
				$message = "您好，您申请提现{$withdraw_money}元，其中有{$vo['back_money']}元在回款之内，无需提现手续费，另有{$money}元需收取提现手续费{$fee}元，确认要提现吗？";
			}
			ajaxmsg( "{$message}", 1 );
			
			if(($today_money+$withdraw_money)>$fee[2][1]*10000){
					$message = "单日提现上限为{$fee[2][1]}万元。您今日已经申请提现金额：{$today_money}元,当前申请金额为:{$withdraw_money}元,已超出单日上限，请您修改申请金额或改日再申请提现";
					ajaxmsg($message,2);
			}
			
		//////////////////////////////////////////////
				
		}else{//普通会员暂未使用
		 
				if(($today_money+$withdraw_money)>300000){
					$message = "您是普通会员，单日提现上限为30万元。您今日已经申请提现金额：$today_money元,当前申请金额为:$withdraw_money元,已超出单日上限，请您修改申请金额或改日再申请提现";
					ajaxmsg($message,2);
				}
				$tqfee = $this->glo['fee_pttx'];
				$fee = getFloatValue($tqfee*$withdraw_money/100,2);
				
				if( ($vo['account_money']-$withdraw_money - $fee)<0 ){
					$message = "您好，您申请提现{$withdraw_money}元，提现手续费{$fee}元将从您的提现金额中扣除，确认要提现吗？";
				}else{
					$message = "您好，您申请提现{$withdraw_money}元，提现手续费{$fee}元将从您的帐户余额中扣除，确认要提现吗？";
				}
				ajaxmsg("{$message}",1);
		}
	}
	
	
	
	public function actwithdraw(){
		$pre = C('DB_PREFIX');
		$withdraw_money = floatval($_POST['amount']);
		$pwd = md5($_POST['pwd']);
		$vo = M('members m')->field('mm.account_money,mm.back_money,(mm.account_money+mm.back_money) all_money,m.user_leve,m.time_limit')->join("{$pre}member_money mm on mm.uid = m.id")->where("m.id={$this->uid} AND m.pin_pass='{$pwd}'")->find();
		if(!is_array($vo)) ajaxmsg("",0);
		if($vo['all_money']<$withdraw_money) ajaxmsg("提现额大于帐户余额",2);
		$start = strtotime(date("Y-m-d",time())." 00:00:00");
		$end = strtotime(date("Y-m-d",time())." 23:59:59");
		$wmap['uid'] = $this->uid;
		$wmap['withdraw_status'] = array("neq",3);
		$wmap['add_time'] = array("between","{$start},{$end}");
		$today_money = M('member_withdraw')->where($wmap)->sum('withdraw_money');	
		$today_time = M('member_withdraw')->where($wmap)->count('id');	
		$tqfee = explode("|",$this->glo['fee_tqtx']);
		$fee[0] = explode("-",$tqfee[0]);
		$fee[1] = explode("-",$tqfee[1]);
		$fee[2] = explode("-",$tqfee[2]);
		$one_limit = $fee[2][0]*10000;
		if($withdraw_money<100 ||$withdraw_money>$one_limit) ajaxmsg("单笔提现金额限制为100-{$one_limit}元",2);
		$today_limit = $fee[2][1]/$fee[2][0];
		if($today_time>=$today_limit){
					$message = "一天最多只能提现{$today_limit}次";
					ajaxmsg($message,2);
		}
		
		if(1==1 || $vo['user_leve']>0 && $vo['time_limit']>time()){
			if(($today_money+$withdraw_money)>$fee[2][1]*10000){
				$message = "单日提现上限为{$fee[2][1]}万元。您今日已经申请提现金额：{$today_money}元,当前申请金额为:{$withdraw_money}元,已超出单日上限，请您修改申请金额或改日再申请提现";
				ajaxmsg($message,2);
			}
			$itime = strtotime(date("Y-m", time())."-01 00:00:00").",".strtotime( date( "Y-m-", time()).date("t", time())." 23:59:59");
			$wmapx['uid'] = $this->uid;
			$wmapx['withdraw_status'] = array("neq",3);
			$wmapx['add_time'] = array("between","{$itime}");
			$times_month = M("member_withdraw")->where($wmapx)->count("id");
			
		
			$tqfee1 = explode("|",$this->glo['fee_tqtx']);
			$fee1[0] = explode("-",$tqfee1[0]);
			$fee1[1] = explode("-",$tqfee1[1]);
			if(($withdraw_money-$vo['back_money'])>=0){
				$maxfee1 = ($withdraw_money-$vo['back_money'])*$fee1[0][0]/1000;
				if($maxfee1>=$fee1[0][1]){
					$maxfee1 = $fee1[0][1];
				}
				
				$maxfee2 = $vo['back_money']*$fee1[1][0]/1000;
				if($maxfee2>=$fee1[1][1]){
					$maxfee2 = $fee1[1][1];
				}
				
				$fee = $maxfee1+$maxfee2;
				$money = $withdraw_money-$vo['back_money'];
			}else{
				//$fee = $vo['back_money']*$fee1[1][0]/1000;
				$fee = $withdraw_money*$fee1[1][0]/1000;
				if($fee>=$fee1[1][1]){
					$fee = $fee1[1][1];
				}
			}
			
			
			
			if(($vo['all_money']-$withdraw_money - $fee)<0 ){
			
				//$withdraw_money = ($withdraw_money - $fee);
				$moneydata['withdraw_money'] = $withdraw_money;
				$moneydata['withdraw_fee'] = $fee;
				$moneydata['second_fee'] = $fee;
				$moneydata['withdraw_status'] = 0;
				$moneydata['uid'] =$this->uid;
				$moneydata['add_time'] = time();
				$moneydata['add_ip'] = get_client_ip();
				$newid = M('member_withdraw')->add($moneydata);
				if($newid){
					memberMoneyLog($this->uid,4,-$withdraw_money,"提现,默认自动扣减手续费".$fee."元",'0','@网站管理员@',0);
					MTip('chk6',$this->uid);
					ajaxmsg("恭喜，提现申请提交成功",1);
				} 
				
			}else{
				$moneydata['withdraw_money'] = $withdraw_money;
				$moneydata['withdraw_fee'] = $fee;
				$moneydata['second_fee'] = $fee;
				$moneydata['withdraw_status'] = 0;
				$moneydata['uid'] =$this->uid;
				$moneydata['add_time'] = time();
				$moneydata['add_ip'] = get_client_ip();
				$newid = M('member_withdraw')->add($moneydata);
				if($newid){
					//memberMoneyLog($this->uid,4,-$withdraw_money,"提现,默认自动扣减手续费".$fee."元",'0','@网站管理员@',-$fee);
					memberMoneyLog($this->uid,4,-$withdraw_money,"提现,默认自动扣减手续费".$fee."元",'0','@网站管理员@');
					MTip('chk6',$this->uid);
					ajaxmsg("恭喜，提现申请提交成功",1);
				} 
			}
			ajaxmsg("对不起，提现出错，请重试",2);
		}else{//普通会员暂未使用
				if(($today_money+$withdraw_money)>300000){
					$message = "您是普通会员，单日提现上限为30万元。您今日已经申请提现金额：$today_money元,当前申请金额为:$withdraw_money元,已超出单日上限，请您修改申请金额或改日再申请提现";
					ajaxmsg($message,2);
				}
				$tqfee = $this->glo['fee_pttx'];
				$fee = getFloatValue($tqfee*$withdraw_money/100,2);
				
				if( ($vo['account_money']-$withdraw_money - $fee)<0 ){
				
					$withdraw_money = ($withdraw_money - $fee);
					$moneydata['withdraw_money'] = $withdraw_money;
					$moneydata['withdraw_fee'] = $fee;
					$moneydata['withdraw_status'] = 0;
					$moneydata['uid'] =$this->uid;
					$moneydata['add_time'] = time();
					$moneydata['add_ip'] = get_client_ip();
					$newid = M('member_withdraw')->add($moneydata);
					if($newid){
						memberMoneyLog($this->uid,4,-$withdraw_money - $fee,"提现,自动扣减手续费".$fee."元");
						MTip('chk6',$this->uid);
						ajaxmsg("恭喜，提现申请提交成功",1);
					} 
				}else{
					$moneydata['withdraw_money'] = $withdraw_money;
					$moneydata['withdraw_fee'] = $fee;
					$moneydata['withdraw_status'] = 0;
					$moneydata['uid'] =$this->uid;
					$moneydata['add_time'] = time();
					$moneydata['add_ip'] = get_client_ip();
					$newid = M('member_withdraw')->add($moneydata);
					if($newid){
						memberMoneyLog($this->uid,4,-$withdraw_money,"提现,自动扣减手续费".$fee."元",'0','@网站管理员@',-$fee);
						MTip('chk6',$this->uid);
						ajaxmsg("恭喜，提现申请提交成功",1);
					} 
				}
				ajaxmsg("对不起，提现出错，请重试",2);
		}
	}
	
	
	
	
	
	//绑定银行卡
	public function tk_bank_card(){
		//$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
      
            /*if(!M('escrow_account')->where("uid={$this->uid} and account <>''")->count('uid')){
               $data['html'] = '<style type="text/css"> .error_msg{padding:20px; font-size:17px; color:#333333;} .error_msg a{color:#1D53BF; font-weight: bold;}  </style>
               <div class="error_msg">你还未绑定托管账户，请先绑定托管账户:马上<a href="'.U('/M/bank/bindingAccount').'" >绑定托管账户</a></div>'; 
              $this->display();
            }
			*/
			
			$voinfo = M("member_info")->field('idcard,real_name')->find($this->uid);
			$vobank = M("member_banks")->field(true)->where("uid = {$this->uid} and bank_num !=''")->find();
			
			
			$vobank['bank_province'] = M('area')->getFieldByName("{$vobank['bank_province']}",'id');
			
			$vobank['bank_city'] = M('area')->getFieldBycityName("{$vobank['bank_city']}",'id');
			
			
			
			$this->assign("voinfo",$voinfo);
			
			
			$this->assign("vobank",$vobank);
			//dump($this->gloconf['BANK_NAME']);die;
			//$this->assign("bank_list",$this->gloconf['BANK_NAME']);
			$this->assign("bank_list",C('bank'));
			$this->assign('edit_bank', $this->glo['edit_bank']);
			
			//$data['html'] = $this->fetch();
		
		$this->display();
		
		
	}
	
	public function getarea(){
		$rid = intval($_GET['rid']);
		
		if(empty($rid)) return;
		$map['reid'] = $rid;
		$alist = M('area')->field('id,name')->order('sort_order DESC')->where($map)->select();
		
		if(count($alist)===0){
			$str="<option value=''>--该地区下无下级地区--</option>\r\n";
		}else{
			
			foreach($alist as $v){
				$str.="<option value='{$v['id']}'>{$v['name']}</option>\r\n";
			}
		}
		$data['option'] = $str;
		$res = json_encode($data);
		echo $res;
	}	
	
	
	public function bindbank(){
       //echo "834983498";die;
	    $bank_info = M('member_banks')->field("uid, bank_num")->where("uid=".$this->uid)->find();
	   
		!$bank_info['uid'] && $data['uid'] = $this->uid;
		$data['bank_num'] = text($_POST['account']);
		$data['bank_name'] = text($_POST['bankname']);
		$data['bank_address'] = text($_POST['bankaddress']);
		$data['bank_province'] = text($_POST['province']);
		$data['bank_city'] = text($_POST['cityName']);
		$data['add_ip'] = get_client_ip();
		$data['add_time'] = time();
		if($bank_info['uid']){
			
			/////////////////////新增银行卡修改锁定开关 开始 20130510 fans///////////////////////////
			/*if(intval($this->glo['edit_bank'])!= 1 && $bank_info['bank_num']){
				ajaxmsg("为了您的帐户资金安全，银行卡已锁定，如需修改，请联系客服", 0 );
			}
			*/
			/////////////////////新增银行卡修改锁定开关 结束 20130510 fans///////////////////////////
			
			$newid = M('member_banks')->where("uid=".$this->uid)->save($data);
			
		}else{
			
			$newid = M('member_banks')->add($data);
		}
		if($newid){
			MTip('chk2',$this->uid);
			ajaxmsg();
		}
		else ajaxmsg('操作失败，请重试',0);
	}
	
	//提款记录
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
	   //dump($list);die;
		$this->assign('search',$search);
		$this->assign("list",$list);
		$this->assign("pagebar",$list['page']);
		
		//$data['html'] = $this->fetch();
		//exit(json_encode($data));
		$this->display();
	}
	//撤销提现
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
			$res = memberMoneyLog($this->uid,5,$vo['withdraw_money'],"撤消提现",'0','@网站管理员@');
		}
		if($res) ajaxmsg();
		else ajaxmsg("",0);
	}
	
	
	

	
	
	  
  }
?>