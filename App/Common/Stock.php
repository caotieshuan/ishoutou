<?php 
	
	//$http = "localhost";
	//$the_url = explode(".",$_SERVER['SERVER_NAME']);
	//$the_http = $the_url[1].'.'.$the_url[2];
	
	//if($the_http != $http){
		
	//	echo "您的域名与程序尚未授权，请到<a href='http://www.rongtianxia.com'>http://www.rongtianxia.com</a>进行授权！";die;
	//}
	//货币类型转换为数字
	function replace($string){
		
		$str = str_replace(",","",$string);
		return $str;
	}
	//大于10万转换为数字
	function gtthe($int){
		
		$ret = substr($int,0,-1);
		$run = $ret * 10000;
		return $run;
		
	}
	//分割股票配资设置天数
	function explodeday($str){
		
		$edays = explode('|',$str);
		$from_date = $edays[0];
		$end_date = $edays[1];
		$arr = array();
		for($i = $from_date;$i<=$end_date;$i++){			
			
			$arr[] = $i;
		}
		
		return $arr;
	}
	//json返回
	function jsonmsg($msg,$type){
		
		$Return = array();
		$Return['status'] = $type;
		$Return['msg'] = $msg;
		return json_encode($Return);
	}
	//天天盈配资
	function stockmoney($days,$stock_money,$type,$istoday,$uid){
		
		$glo = M('shares_global')->field('text')->where("code = "."'{$type}'")->find();
		
		$glos = explode('|',$glo['text']);
		
		$guarantee_money = $stock_money / $glos[0];//保证金
		$interest = $stock_money * ($glos[1] / 1000) * $days;//总利息
		$open = $glos[2]/100 * $guarantee_money + $stock_money;//平仓线
		$alert_s = $glos[3]/100 * $guarantee_money + $stock_money;//警戒线
		$one_interest = $stock_money * ($glos[1] / 1000);//计算出一天多少利息

		$daydata = array();
		$daydata['uid'] = $uid;
		$daydata['principal'] = $guarantee_money;
		$daydata['manage_fee'] = $interest;
		$daydata['type_id'] = 1;
		$daydata['lever_id'] = '';
		$daydata['shares_money'] = $stock_money;
		$daydata['order'] = 'PZT_'.time().rand(100,999);;
		$daydata['open'] = $open;
		$daydata['alert'] = $alert_s;
		$daydata['lever_ratio'] = $glos[0];
		$daydata['manage_rate'] = $glos[1];
		$daydata['open_ratio'] = $glos[2];
		$daydata['alert_ratio'] = $glos[3];
		$daydata['surplus_money'] = '';
		$daydata['add_time'] = time();
		$daydata['ip_address'] = get_client_ip();
		$daydata['status'] = 1;
		$daydata['recovery_time'] = '';
		$daydata['already_manage_fee'] = '';
		$daydata['trading_time'] = $istoday;
		$daydata['duration'] = $days;
		$daydata['client_user'] = '';
		$daydata['client_pass'] = '';
		$daydata['one_manage_fee'] = $daydata['manage_fee'] / $daydata['duration'];
		$daydata['total_money'] = $guarantee_money + $stock_money;
		$daydata['u_name'] = session("u_user_name");
		// var_dump($daydata['u_name']);die;
		$daydata['stock_admin_id']= getAdminidByInvitation(session('invitation_code'));
			$ret = M('shares_apply')->add($daydata);
			
			if($ret){
				
				return true;	
			}else{
				
				return false;
			}

	}
	//member_moneylog
	function pzmembermoneylod($affect_money,$uid,$info,$shares_id,$type=50){
		$logusermoney = M('member_money')->where("uid = {$uid}")->find();
		$log = array();
		$log['uid'] = $uid;
		$log['type'] = $type;
		$log['affect_money'] = $affect_money;
		$log['account_money'] = $logusermoney['account_money'];
		$log['back_money'] = $logusermoney['back_money'];
		$log['collect_money'] = $logusermoney['money_collect'];
		$log['freeze_money'] = $logusermoney['money_freeze'];
		$log['info'] = $info;
		$log['add_time'] = time();
		$log['add_ip'] = get_client_ip();
		$log['target_uid'] = 0;
		$log['target_uname'] = '平台运行商';
		$log['shares_id'] = $shares_id;
		$returnlog = M('member_moneylog')->add($log);
		//dump($returnlog);die;
		if($returnlog){
			
			return true;
		}else{
			
			return false;
		}

	}
	//天天盈配资审核 @dong
	function examinembermoney($uid,$guarantee_money,$interest,$order,$id){		//参数 用户id  本金 用户管理费  订单号

		$user_money = M('member_money')->where("uid = {$uid}")->find();
		
		$usermoney = array();
		
		if($user_money['back_money'] > ($guarantee_money + $interest)){
			
			$usermoney['back_money'] = $user_money['back_money'] - $guarantee_money;
			$usermoney['money_freeze'] = $user_money['money_freeze'] + $interest;
		}else{
			$usermoney['account_money'] = ($user_money['back_money'] + $user_money['account_money']) - $guarantee_money - $interest;
			$usermoney['money_freeze'] = $user_money['money_freeze'] + $interest;
		}
		$anti = M('member_money')->where("uid = {$uid}")->save($usermoney);
			
			if($anti){
				//推广奖励
				$recommend_id = M('members')->getFieldByid($uid,"recommend_id");
				if($recommend_id != 0){
					$manage_fee = M('shares_apply')->getFieldByorder($order,"manage_fee");
					promotion($uid,$recommend_id,$manage_fee,$order);	
				}
				$ainfo = $order.'订单支付保证金';
				$iinfo = $order.'订单管理费冻结';
				$areturnlog = pzmembermoneylod($guarantee_money,$uid,$ainfo,$id);
				$ireturnlog = pzmembermoneylod($interest,$uid,$iinfo,$id);
				
				if($ireturnlog && $areturnlog){
					return true;
				}else{
					
					return false;
				}
			}else{
				
				return false;
			}
			
	}
	//推广奖励 @dong
	function promotion($uid,$recommend_id,$manage_fee,$order){
		
		$promotion_incentives = $manage_fee * (5/100);
							
		$recommend_money = M('member_money')->find($recommend_id);					
		$saverecommend = array();
		$saverecommend['account_money'] = $recommend_money['account_money'] + $promotion_incentives;
		
		$recommend_ret = M('member_money')->where("uid = {$recommend_id}")->save($saverecommend);
		
		$user_name = M('members')->getFieldByid($uid,"user_name");
		
		if($recommend_ret){
			
			$rinfo = $user_name.'的'.$order.'号订单，您获得推广奖励'.$promotion_incentives.'元';
			$areturnlog = pzmembermoneylod($promotion_incentives,$recommend_id,$rinfo,$id);
			if($areturnlog){
				
				return true;
			}else{
				
				return false;
			}
		}else{
			
			return false;
		}
		
	}
	
	//月月盈审核	@author:Bob	@time:2015/3/26
	function examinemonth($uid,$principal,$rate,$order,$id){
		$member_money = M('member_money')->where("uid = {$uid}")->find();
		$usermoney = array();
		
		//判断回款资金池资金是否足够支付配资本金与管理费
		if($member_money['back_money'] >= ($principal + $rate)){
			$usermoney['back_money'] = $member_money['back_money'] - $principal - $rate;
		}else{
			$usermoney['account_money'] = ($member_money['back_money'] + $member_money['account_money']) - $principal - $rate;
			$usermoney['back_money'] = 0;
		}
		$ret = M('member_money')->where("uid = {$uid}")->save($usermoney);
		if($ret){
			$pinfo = $order.'订单支付月月盈本金';
			$rinfo = $order.'订单支付月月盈管理费';
			$plogret = pzmembermoneylod($principal,$uid,$pinfo,$id,51);
			$rlogret = pzmembermoneylod($rate,$uid,$rinfo,$id,51);
			if($plogret && $rlogret){
				return true;
			}else{
				return false;
			}
		}
	}
	
	//月月盈扣款	@author:Bob	@time:2015/3/26
	function monthdeduction($id) {
		$apply = M("shares_apply")->find($id);
		//判断是否已到期
		if ( $apply['already_manage_fee'] < $apply['manage_fee'] ) {
			$uid = $apply['uid'];
			$one_rate = $apply['one_manage_fee'];	//单月需要支付的管理费
			$member_money = M('member_money')->where("uid = {$uid}")->find();
			$d_time = strtotime("+30 day",$apply['deduction_time']);
			
			//判断是否到扣款日
			if ( strtotime(date("Y-m-d",$d_time)) <= strtotime(date("Y-m-d",time())) ) {
				
				//判断余额是否足够支付管理费
				if(($member_money['account_money'] + $member_money['back_money']) < $one_rate){
					innermsg($uid,'管理费','可用余额不足！');
					
				//回款资金池资金足够支付管理费
				}elseif($member_money['back_money'] > $one_rate){
					$usermoney['back_money'] = $member_money['back_money'] - $one_rate;
					
				//回款资金池资金不够支付管理费扣除充值资金池
				}else{
					$usermoney['account_money'] = ($member_money['back_money'] + $member_money['account_money']) - $one_rate;
					$usermoney['back_money'] = 0;
				}
				$res = M('member_money')->where("uid = {$uid}")->save($usermoney);
				if($res){
					
					//更新已支付管理费以及扣款时间
					$applydata['already_manage_fee'] = $apply['already_manage_fee'] + $one_rate;
					$applydata['deduction_time'] = time();
					$applyres = M('shares_apply')->where("id = {$id}")->save($applydata);
					if($applyres){
						$info = $apply['order'].'扣除管理费'.$one_rate;
						$log = pzmembermoneylod($one_rate,$uid,$info,$id,51);
						innermsg($uid,'管理费','管理费已扣完！');						
					}
				}
			}
		}
	}
	
	function daydeduction($id){
		
		
		
		$apply = M('shares_apply')->where("id = $id")->find();
		
		$uid = $apply['uid'];
		$one_interest = $apply['manage_fee'] / $apply['duration'];

		if($apply){
				
			//如延期将从可用余额扣除
			if($apply['already_manage_fee'] >= $apply['manage_fee']){
				
				
				
				$usre_money = M('member_money')->where("uid = {$uid}")->find();
				
				if(($usre_money['account_money'] + $usre_money['back_money']) < $one_interest){
					
					innermsg($uid,'管理费','可用余额不足！');die;
				}
				if($usre_money['back_money'] > $one_interest){
			
					$usermoney['back_money'] = $usre_money['back_money'] - $one_interest;
			
				}else{
					$usermoney['account_money'] = ($usre_money['back_money'] + $usre_money['account_money']) - $one_interest;
				
				}
				//$account_money['account_money'] = $usre_money['account_money'] - $one_interest;
				
				$ret = M('member_money')->where("uid = {$uid}")->save($usermoney);
				
				if($ret){
					
					$already_manage_fee['already_manage_fee'] = $apply['already_manage_fee'] + $one_interest;
					$applyret = M('shares_apply')->where("id = {$id}")->save($already_manage_fee);
					
					if($applyret){
						$info = $apply['order'].'延期将从可用余额扣除管理费'.$one_interest;
						$log = pzmembermoneylod($one_interest,$uid,$info,$id);
						innermsg($uid,'管理费','管理费已扣完,将从可用余额扣除！');						
					}

				}
				
				
			}else{
				//不延期将从冻结金额扣除并更新shares_apply标已支付管理费
				$usre_money = M('member_money')->where("uid = {$uid}")->find();
				$money_freeze['money_freeze'] = $usre_money['money_freeze'] - $one_interest;
				
				$ret = M('member_money')->where("uid = {$uid}")->save($money_freeze);
				
				if($ret){
					$already_manage_fee['already_manage_fee'] = $apply['already_manage_fee'] + $one_interest;
					$applyret = M('shares_apply')->where("id = {$id}")->save($already_manage_fee);	
				
			
					if($applyret){
						$info = $apply['order'].'扣除管理费'.$one_interest;
						$log = pzmembermoneylod($one_interest,$uid,$info,$id);
						
						$end_interest = M('shares_apply')->where("id = {$id}")->find();
						if($end_interest['already_manage_fee'] == $end_interest['manage_fee']){
							
							innermsg($uid,'管理费','管理费已扣完');
						}
					
					}
					
				}
				
				
			}
			
		}
		
		
	}
	function innermsg($uid,$title,$content){
		$array = array();
		$array['uid'] = $uid;
		$array['title'] = $title;
		$array['msg'] = $content;
		$array['send_time'] = time();
		$array['status'] = 0;
		return M("inner_msg")->add($array);
	}
	
	/**
	 @param   $tableName   String 	需要获取数据的表名
	 @return    $arr  	  Array 		遍历后的一维数组
	 @author    yh
	 @time        2015/4/2 
	 根据传入的表名查询数据库数据 返回一维数组
	*/	
	function get_cps_trader($tableName){
		$obj = M($tableName);
		$res = $obj -> where('type_id=3')->field('text')->select();
		$arr = array();
		// var_dump($res);die;
		foreach($res as $key => $v){
			foreach ($v as $key => $value) {
				$arr[] = $value;
			}
		}
		return $arr;
	}
	//查询节假日表
	function get_holiday_data($tableName){
		$obj = M($tableName);
		$res = $obj->select();	//获取到节假日
		//判断当前日期是否在节假日中
		$nowtime = time();
		if($nowtime>=$res['from_date'] && $nowtime<=$res['to_date']){	//如果当前时间处在节假日间返回1  不处于节假日之间返回数组
			return '1';
		}else{
			return $res;
		}
	}
	//后台获取我是操盘手数据
	function get_trader_data($tableName,$where,$limitSql){
		$obj = M($tableName);
		$res = $obj->join('lzh_members ON lzh_members.id = lzh_shares_apply.uid')
			 	->field("lzh_shares_apply.principal,lzh_shares_apply.id,lzh_shares_apply.type_id,lzh_shares_apply.lever_ratio,lzh_shares_apply.order,
			 		lzh_shares_apply.shares_money,lzh_shares_apply.open,lzh_shares_apply.alert,lzh_shares_apply.open_ratio,
			 		lzh_shares_apply.alert_ratio,lzh_shares_apply.add_time,lzh_shares_apply.ip_address,lzh_shares_apply.duration,
			 		lzh_shares_apply.total_money,lzh_shares_apply.trading_time,lzh_shares_apply.examine_time,lzh_shares_apply.status,
			 		lzh_shares_apply.client_user,lzh_shares_apply.client_pass,lzh_shares_apply.endtime,lzh_members.user_name,lzh_members.id as uid")
			 	->where($where)->limit($limitSql)->select();
		return $res;
	}
	
	//数字转中文	@author:Bob	@time:2015/3/26
	function numToL($num) {
		if($num>=1000){
			if ($num<10000) {
				return floor($num/1000).'千';
			} else {
				return (floor(($num/10000)*10)/10).'万';
			}
		} else {
			return $num;
		}
	}
	function openedits($id,$counttrader){

		$status = array();
		$status['status'] = 3;
		$status['recovery_time'] = time();
		$applys = M('shares_apply')->where("id = {$id}")->save($status);
		if($applys){
			
			$apply = M('shares_apply')->where("id = {$id}")->find();
		}
		if($apply){
			
			$principal = $apply['principal'];//本金
			$shares_money = $apply['shares_money'];//所获配资金额
			$total_money = $apply['total_money'];

			if($counttrader > $total_money){//判断HOMS总操盘金额比平台配资总金额大，总操盘金额-平台配资总金额，否则平台总配资金额-总操盘金额
				
				$surplus = $counttrader - $total_money;//盈利，退回用户本金加盈利
				
				$manage_fee = $apply['manage_fee'];
				$already_manage_fee = $apply['already_manage_fee'];
				
				
				//当日平仓扣除一天管理费
				if($already_manage_fee == 0){
					$user_money_fee = M('member_money')->where("uid = {$apply['uid']}")->find();
					$one_fee = $manage_fee / $apply['duration'];
					
					$account_money_onefee['money_freeze'] = $user_money_fee['money_freeze'] - $one_fee;
					$onefee = M('member_money')->where("uid = {$apply['uid']}")->save($account_money_onefee);
					
					$already_manage_fees['already_manage_fee'] = $one_fee;
					$apply_fee = M('shares_apply')->where("id = {$id}")->save($already_manage_fees);
					if($onefee && $apply_fee){
						
						$sinfo = $apply['order'].'订单平仓，管理费使用期限1天扣除'.$one_fee.'已增加到可用余额';
						pzmembermoneylod($one_fee,$apply['uid'],$sinfo,$id);
					}
				}
				//退回管理费
				$shares_apply_fee = M('shares_apply')->where("id = {$id}")->find();
				$cha_fee = $shares_apply_fee['manage_fee'] - $shares_apply_fee['already_manage_fee'];
				if($cha_fee != 0){
					$user_money_feet = M('member_money')->where("uid = {$apply['uid']}")->find();
					$account_money_fee['account_money'] = $user_money_feet['account_money'] + $cha_fee;
					$account_money_fee['money_freeze'] = $user_money_feet['money_freeze'] - $cha_fee;
					$fee = M('member_money')->where("uid = {$apply['uid']}")->save($account_money_fee);
					if($fee){
						
						$sinfo = $apply['order'].'订单平仓，管理费退回'.$cha_fee.'已增加到可用余额';
						pzmembermoneylod($cha_fee,$apply['uid'],$sinfo,$apply['id']);
					}
				}
				
				//本金退回
				$user_money = M('member_money')->where("uid = {$apply['uid']}")->find();

				$account_money['account_money'] = $user_money['account_money'] + $surplus;				
				$savemoeny = M('member_money')->where("uid = {$apply['uid']}")->save($account_money);
				
				if($savemoeny){
					
					$sinfo = $apply['order'].'订单平仓，返回盈利'.$surplus.'已增加到可用余额';
					pzmembermoneylod($surplus,$apply['uid'],$sinfo,$apply['id']);
				}
				$user_moneys = M('member_money')->where("uid = {$apply['uid']}")->find();
				$account_moneys['account_money'] = $user_moneys['account_money'] + $principal;				
				$savemoenys = M('member_money')->where("uid = {$apply['uid']}")->save($account_moneys);
				
				
				if($savemoenys){

					$pinfo = $apply['order'].'订单平仓，退回本金'.$principal.'已增加到可用余额';
					pzmembermoneylod($principal,$apply['uid'],$pinfo,$apply['id']);
				}
			}else{
									
				$surplus = $counttrader - $total_money;//亏损，退回用户只有本金
				
				
				$manage_fee = $apply['manage_fee'];
				$already_manage_fee = $apply['already_manage_fee'];
				
				
				//当日平仓扣除一天管理费
				if($already_manage_fee == 0){
					$user_money_fee = M('member_money')->where("uid = {$apply['uid']}")->find();
					$one_fee = $manage_fee / $apply['duration'];
					
					$account_money_onefee['money_freeze'] = $user_money_fee['money_freeze'] - $one_fee;
					$onefee = M('member_money')->where("uid = {$apply['uid']}")->save($account_money_onefee);
					
					$already_manage_fees['already_manage_fee'] = $one_fee;
					$apply_fee = M('shares_apply')->where("id = {$id}")->save($already_manage_fees);
					
					if($onefee && $apply_fee){
						
						$sinfo = $apply['order'].'订单平仓，管理费使用期限1天扣除'.$one_fee.'已增加到可用余额';
						pzmembermoneylod($one_fee,$apply['uid'],$sinfo,$apply['id']);
					}
				}
				//退回管理费
				$shares_apply_fee = M('shares_apply')->where("id = {$id}")->find();
				$cha_fee = $shares_apply_fee['manage_fee'] - $shares_apply_fee['already_manage_fee'];
				
				if($cha_fee != 0){
					$user_money_feet = M('member_money')->where("uid = {$apply['uid']}")->find();
					$account_money_fee['account_money'] = $user_money_feet['account_money'] + $cha_fee;
					$account_money_fee['money_freeze'] = $user_money_feet['money_freeze'] - $cha_fee;
					$fee = M('member_money')->where("uid = {$apply['uid']}")->save($account_money_fee);
					if($fee){
						
						$sinfo = $apply['order'].'订单平仓，管理费退回'.$cha_fee.'已增加到可用余额';
						pzmembermoneylod($cha_fee,$apply['uid'],$sinfo,$apply['id']);
					}
				}
				//本金退回
				$user_money = M('member_money')->where("uid = {$apply['uid']}")->find();	
				
				$account_moneys['account_money'] = $user_money['account_money'] + $principal;				
				$savemoenys = M('member_money')->where("uid = {$apply['uid']}")->save($account_moneys);
				
				if($savemoenys){
					$pinfo = $apply['order'].'订单平仓，退回本金'.$principal.'已增加到可用余额';
					pzmembermoneylod($principal,$apply['uid'],$pinfo,$apply['id']);
					
					
					$user_moneyk = M('member_money')->where("uid = {$apply['uid']}")->find();	
				
					$account_moneysk['account_money'] = $user_moneyk['account_money'] + $surplus;				
					M('member_money')->where("uid = {$apply['uid']}")->save($account_moneysk);
					
					$sinfo = $apply['order'].'订单平仓，亏损'.$surplus.'元';
					pzmembermoneylod($surplus,$apply['uid'],$sinfo,$apply['id']);
					
				}
			}
			
			if($savemoenys){
				
				$arr = array();
				$arr['uid'] = $apply['uid'];
				$arr['shares_id'] = $apply['id'];
				$arr['profit_loss'] = $surplus;
				$arr['add_time'] = time();
				$arr['type_id'] = 1;				
				$shares_record = M('shares_record')->add($arr);
				
				if($shares_record){
					return true;
				}else{
					
					return false;
				}
				
			}
			
		}
	}
	
	//月月盈平仓	@author:Bob	@time:2015/3/27
	function monthopen($id,$counttrader){	//配资id	剩余操盘总金额

		//更新配资记录状态
		$savedata = array();
		$savedata['status'] = 3;
		$savedata['recovery_time'] = time();
		$appret = M('shares_apply')->where("id = {$id}")->save($savedata);
		if($appret){
			$apply = M('shares_apply')->find($id);
			$principal = $apply['principal'];	//本金
			$total_money = $apply['total_money'];	//操盘总金额
			$surplus = $counttrader - $total_money;	//盈亏
			$money = $principal + $surplus;	//退回金额
			$member_money = M('member_money')->where("uid = {$apply['uid']}")->find();
			$savemm['account_money'] = $member_money['account_money'] + $money;
			$ret = M('member_money')->where("uid = {$apply['uid']}")->save($savemm);
			if($ret){
				//记录
				$info = $apply['order'].'订单平仓，剩余金额'.$money.'已增加到可用余额';
				$mlret = pzmembermoneylod($money,$apply['uid'],$info,$apply['id'],51);
				if($mlret) {
					//写入配资记录表
					$arr = array();
					$arr['uid'] = $apply['uid'];
					$arr['shares_id'] = $apply['id'];
					$arr['profit_loss'] = $surplus ;
					$arr['add_time'] = time();
					$arr['type_id'] = 2;				
					$shares_record = M('shares_record')->add($arr);
					if($shares_record){
						return true;
					}else{
						return false;
					}
				}
			}
		}
	}
	
	//免费体验平仓	@author:Bob	@time:2015/3/30
	function freeopen($id,$counttrader){	//配资id	剩余操盘总金额
		//更新配资记录状态
		$savedata = array();
		$savedata['status'] = 3;
		$savedata['recovery_time'] = time();
		$appret = M('shares_apply')->where("id = {$id}")->save($savedata);
		if($appret){
			$apply = M('shares_apply')->find($id);
			$principal = $apply['principal'];	//本金
			$total_money = $apply['total_money'];	//操盘总金额
			$surplus = $counttrader - $total_money;	//盈亏
			if($surplus >= 0) {
				$money = $principal + $surplus;	//退回金额
			}else {
				$money = $principal;
			}
			$member_money = M('member_money')->where("uid = {$apply['uid']}")->find();
			$savemm['account_money'] = $member_money['account_money'] + $money;
			$ret = M('member_money')->where("uid = {$apply['uid']}")->save($savemm);
			if($ret){
				//记录
				$info = $apply['order'].'订单平仓，剩余金额'.$money.'已增加到可用余额';
				$mlret = pzmembermoneylod($money,$apply['uid'],$info,$apply['id'],53);
				if($mlret) {
					//写入配资记录表
					$arr = array();
					$arr['uid'] = $apply['uid'];
					$arr['shares_id'] = $apply['id'];
					$arr['profit_loss'] = $surplus ;
					$arr['add_time'] = time();
					$arr['type_id'] = 4;	
					$shares_record = M('shares_record')->add($arr);
					if($shares_record){
						return true;
					}else{
						return false;
					}
				}
			}
		}
	}
	
			//获取月月盈利息	@author:Bob	@time:2015/3/26
	function getrateratio($duration,$lever) {	//配资期限 所获配资金额
		$rate = M("shares_rateconfig")->where("start_month <= {$duration} and end_month >= {$duration}")->field("rate_config")->find();
		$rate = explode("|",$rate['rate_config']);
		foreach( $rate as $k => $v ){
			$range = explode(",",$v);
			$rlever = $range[0];
			if($rlever == $lever)
			{
				return $range[1];
			}
		}
	}
/* 		//获取月月盈利息	@author:Bob	@time:2015/3/26
	function getrateratio($duration,$shares_money) {	//配资期限 所获配资金额
		$rate = M("shares_rateconfig")->where("start_month <= {$duration} and end_month >= {$duration}")->field("rate_config")->find();
		$rate = explode("|",$rate['rate_config']);
		foreach( $rate as $k => $v ){
			$range = explode(",",$v);
			$money = explode("-",$range[0]);
			if($shares_money >= $money[0] && $shares_money <= $money[1]) {
				return $range[1];
			}
		}
	}
	 */
/* 	//获取月月盈利息	@author:Bob	@time:2015/3/26
	function getrateratio($duration,$lever) {	
	//配资期限 所获配资金额
		$rate = M("shares_rateconfig")->where("start_month <= {$duration} and end_month >= {$duration}")->field("rate_config")->find();
		$rate = explode("|",$rate['rate_config']);
		dump($rate);
		foreach($rate as $k => $v ){
			$range = explode(",",$v);
			$rlever = $range[0];
			//dump($range);die;
			if($rlever == $lever) {
				return $range[1];
			}
		}
	} */
	//免费体验审核 //dong
	function Experience($uid,$guarantee_money,$id){
		
		$user_money = M('member_money')->where("uid = {$uid}")->find();
		
		$usermoney = array();
		
		if($user_money['back_money'] > $guarantee_money){
			
			$usermoney['back_money'] = $user_money['back_money'] - $guarantee_money;
			
		}else{
			
			$usermoney['account_money'] = ($user_money['back_money'] + $user_money['account_money']) - $guarantee_money;
			$usermoney['back_money'] = 0;
			
		}
		$anti = M('member_money')->where("uid = {$uid}")->save($usermoney);
			
			if($anti){
				$ainfo = $order.'订单支付保证金';
				$areturnlog = pzmembermoneylod($guarantee_money,$uid,$ainfo,$id,53);
				
				if($areturnlog){
					return true;
				}else{
					
					return false;
				}
			}
	}
	
	//免费体验平仓  @dong
	function Eopenedits(){
		
		
	}
	//查询用户的可用余额
	function getBalance($tableName,$field,$where){
		$res = M($tableName)->field($field)->where($where)->find();
		if($res){//查询成功
			return $res;
		}else{
			return '0';
		}
	}
	
		


	//999 排除节假日以及周末返回最后截止时间 @author Bob @time:2015/3/31
	function getEndTime($days,$time) {	//天数	开始时间
		for($i = 1 ; $i <= $days ; $i++) {
			$retime = getAfterHld($time);
			$time = $retime;
		}
		return $time;
	}

	
	//↑↑↑↑↑↑↑999他儿子↑↑↑↑↑↑↑
	function getAfterHld($time) {
		
		//判断是否为周6
		if(date("N",$time) == 6) {
			$retime = strtotime("+48 hours",$time);
			$holiday = M("shares_apply")->where("from_date <= {$retime} AND to_date >= {$retime}")->find();
			
			//判断是否在节假日
			if($holiday) {
				return strtotime("+24 hours",$holiday['to_date']);
			}else {
				return strtotime("+48 hours",$time);
			}
			
		//判断是否为周5
		} elseif ( date("N",$time) == 5 ) {
			$retime = strtotime("+72 hours",$time);
			$holiday = M("shares_holiday")->where("from_date <= {$retime} AND to_date >= {$retime}")->find();
			
			//判断是否在节假日
			if($holiday) {
				return strtotime("+24 hours",$holiday['to_date']);
			}else {
				return strtotime("+72 hours",$time);
			}
			
		//周日以及周1-4
		}else {
			$retime = strtotime("+24 hours",$time);
			$holiday = M("shares_holiday")->where("from_date <= {$retime} AND to_date >= {$retime}")->find();
			
			//判断是否在节假日
			if($holiday) {
				return strtotime("+24 hours",$holiday['to_date']);
			}else {
				return strtotime("+24 hours",$time);
			}
		}
	}
	
	//保留2位小数并不四舍五入 @author:Bob	@time:2015/4/1
	function toFloat($num) {	//需处理的数字
		$tmp = explode(".",$num);
		$tmpfnum = substr($tmp[1],0,2);
		$fnum = empty($tmpfnum) ? 00 : $tmpfnum;
		return $tmp[0].".".$fnum;
	}
	
	//获取收益率 @author:Bob	@time:2015/4/1
	function getRetRate($list) {	//配资列表
		foreach($list as $k => $v) {
			$list[$k]['retrate'] = toFloat($v['profit_loss'] / ($v['principal'] + $v['shares_money']) * 100);
		}
		return $list;
	}
	
	//追加保证金 @author:Bob	@time:2015/4/1
	function additional($id,$money,$type) {	//配资id	追加配资金额	类型:天/月/操
		switch($type) {
			//月月盈
			case 2: monthAdditional($id,$money); break;
			//天天盈
			case 1: break;
			//我是操盘手
			case 3:traderAddtional($id,$money);break;
		}
	}
	
	//我是操盘手追加保证金
	function traderAddtional($id,$money) {
		$apply = M("shares_apply")->find($id);
		$all_shares_money = $apply['shares_money'] + $money;
		$apply_manage_fee = $apply['manage_fee'] / $apply['duration'];
		$manage_rate = getrateratio($apply['duration'],$all_shares_money);
		$manage_fee = $manage_rate / 100 * $all_shares_money - $apply_manage_fee;	//追加所产生的管理费
		$add_principal = $money / $apply['lever_ratio'];	//追加所产生的本金
		$uid = $_SESSION['u_id'];
		//查询余额是否充足
		$member_money = M('member_money')->where("uid = {$apply['uid']}")->find();
		if($add_principal > ($member_money['account_money'] + $member_money['back_money'])){
			echo jsonmsg('可用余额不足,请充值！',0);
			die;
		}else{	//扣除用户追加的本金
			if($member_money['back_money'] >= $add_principal){
				$usermoney['back_money'] = $member_money['back_money'] - $add_principal;
			}else{
				$usermoney['account_money'] = ($member_money['back_money'] + $member_money['account_money']) - $add_principal;
				$usermoney['back_money'] = 0;
			}
			$ret = M('member_money')->where("uid = {$uid}")->save($usermoney);
			if($ret){
				$pinfo = $order.'订单支付追加我是操盘手本金';
				$plogret = pzmembermoneylod($add_principal,$uid,$pinfo,$id,52);
			}
		}
		$savedata = array();
		$savedata['principal'] = $add_principal;
		$savedata['shares_money'] = $money;
		$savedata['status'] = 1;
		$savedata['add_time'] = time();
		$savedata['shares_id'] = $id;
		$savedata['u_name'] = $apply['u_name'];
		$savedata['order'] = $apply['order'];
		$savedata['shares_id'] = $id;
		$savedata['type_id'] = 3;
		$ret = M("shares_additional")->add($savedata);
		if($ret) {
			echo jsonmsg('追加申请成功,请等待审核！',1);
		}else {
			echo jsonmsg('追加申请失败！',0);
		}
	}
	


	//月月盈追加保证金 @author:Bob	@time:2015/4/1
	function monthAdditional($id,$money) {	//配资id 追加配资金额
		$apply = M("shares_apply")->find($id);
		$all_shares_money = $apply['shares_money'] + $money;
		$apply_manage_fee = $apply['manage_fee'] / $apply['duration'];
		$manage_rate = getrateratio($apply['duration'],$all_shares_money);
		$manage_fee = $manage_rate / 100 * $all_shares_money - $apply_manage_fee;	//追加所产生的管理费
		$add_principal = $money / $apply['lever_ratio'];	//追加所产生的本金
		
		//查询余额是否充足
		$member_money = M('member_money')->where("uid = {$apply['uid']}")->find();
		if(($manage_fee + $add_principal) > ($member_money['account_money'] + $member_money['back_money'])){
			echo jsonmsg('可用余额不足,请充值！',0);
			die;
		}
		$savedata = array();
		$savedata['principal'] = $add_principal;
		$savedata['shares_money'] = $money;
		$savedata['status'] = 1;
		$savedata['add_time'] = time();
		$savedata['manage_fee'] = $manage_fee;
		$savedata['manage_rate'] = $manage_rate;
		$savedata['shares_id'] = $id;
		$savedata['u_name'] = $apply['u_name'];
		$savedata['order'] = $apply['order'];
		$savedata['duration'] = $apply['already_manage_fee'] / $apply_manage_fee;
		$savedata['type_id'] = 2;
		$ret = M("shares_additional")->add($savedata);
		if($ret) {
			echo jsonmsg('追加申请成功,请等待审核！',1);
		}else {
			echo jsonmsg('追加申请失败！',0);
		}
	}
	
	//888	月月盈追加保证金审核 @author:Bob	@time:2015/4/1
	function examinemonthadd($id) {	//追加申请id
	
		//追加申请数据
		$additional = M("shares_additional")->find($id);
		
		//配资数据
		$apply = M("shares_apply")->find($additional['shares_id']);
		if(savemmoney($apply['id'],$apply['uid'],$additional['principal'],$additional['manage_fee'])) { 
			$savedata['id'] = $additional['shares_id'];	//配资id
			$savedata['principal'] = $apply['principal'] + $additional['principal'];	//本金
			$savedata['shares_money'] = $apply['shares_money'] + $additional['shares_money'];	//所获配资金额
			$savedata['open'] = $savedata['principal'] * $apply['open_ratio'] / 100 * $apply['lever_ratio'] + $savedata['shares_money'];	//平仓线
			$savedata['alert'] = $savedata['principal'] * $apply['alert_ratio'] / 100 * $apply['lever_ratio'] + $savedata['shares_money'];	//警戒线
			$savedata['total_money'] = $savedata['principal'] + $savedata['shares_money'];	//操盘总金额
			$savedata['already_manage_fee'] = $apply['already_manage_fee'] + $additional['manage_fee'];	//已支付管理费
			$surplus = (($apply['manage_fee'] / $apply['duration']) + $additional['manage_fee']) * ($apply['duration'] - ($apply['already_manage_fee'] / ($apply['manage_fee'] / $apply['duration'])));	//剩余需支付的总管理费
			$savedata['manage_fee'] = $savedata['already_manage_fee'] + $surplus;	//总管理费
			$savedata['one_manage_fee'] = $apply['one_manage_fee'] + $additional['manage_fee'];	//单月需要支付的管理费
			$ret = M("shares_apply")->save($savedata);
			if($ret) {
				return true;
			}
		}
	}

	//我是操盘手追加保证金审核 
	function examinetraderadd($id) {
		$additional = M("shares_additional")->find($id);
		$apply = M("shares_apply")->find($additional['shares_id']);
		$savedata['id'] = $additional['shares_id'];
		$savedata['principal'] = $apply['principal'] + $additional['principal'];
		$savedata['shares_money'] = $apply['shares_money'] + $additional['shares_money'];
		$savedata['open'] = $savedata['principal'] * $apply['open_ratio'] / 100 + $savedata['shares_money'];	//用户的平仓线
		$savedata['alert'] = $savedata['principal'] * $apply['alert_ratio'] / 100 + $savedata['shares_money'];	//用户的警戒线
		$savedata['total_money'] = $savedata['principal'] + $savedata['shares_money'];	//用户的总操盘金额
		$ret = M("shares_apply")->save($savedata);
		if($ret) {
			return true;
		}
	}
	//我是操盘手审核未通过  返款
	function checknopass($id){
		$savedata = array();
		$additional = M("shares_additional")->find($id);
		$apply = M("shares_apply")->find($additional['shares_id']);
		$uid = $apply['uid'];
		//查询用户原有本金
		$res = M('member_money')->field('back_money')->where("uid=$uid")->find();
		$savedata['back_money'] = $res['back_money'] + $additional['principal'];
		$ret = M('member_money')->where("uid=$uid")->save($savedata);
		return $ret;
	}
	//我是操盘手申请实盘资金审核未通过 返款
	function supplyenopass($id,$shares_id){
		$savedata = array();
		$supply = M("shares_supply")->find($id);	
		$uid = $supply['uid'];
		$res = M('member_money')->field('back_money')->where("uid=$uid")->find();
		$savedata['back_money'] = $res['back_money'] + $supply['supply_money'];
		$ret = M('member_money')->where("uid=$uid")->save($savedata);
		$rinfo = "我是操盘手申请实盘资金审核未通过";
		pzmembermoneylod($supply['supply_money'],$uid,$rinfo,$shares_id,52);
		return $ret;

	}
	//↑↑↑↑↑↑↑888他儿子↑↑↑↑↑↑↑
	function savemmoney($id,$uid,$principal,$rate,$pinfo="",$rinfo="") {	//配资id  用户id 扣除本金 扣除管理费 本金记录 管理费记录
		//判断回款资金池资金是否足够支付配资本金与管理费
		$member_money = M('member_money')->where("uid = {$uid}")->find();
		if($member_money['back_money'] >= ($principal + $rate)){
			$usermoney['back_money'] = $member_money['back_money'] - $principal - $rate;
		}else{
			$usermoney['account_money'] = ($member_money['back_money'] + $member_money['account_money']) - $principal - $rate;
			$usermoney['back_money'] = 0;
		}
		$ret = M('member_money')->where("uid = {$uid}")->save($usermoney);
		if($ret){
			if($pinfo == "" && $rinfo == "") {
				$pinfo = $order.'订单支付追加月月盈本金';
				$rinfo = $order.'订单支付追加月月盈管理费';
				$plogret = pzmembermoneylod($principal,$uid,$pinfo,$id,51);
				$rlogret = pzmembermoneylod($rate,$uid,$rinfo,$id,51);
				if($plogret && $rlogret){
					return true;
				}else{
					return false;
				}
			}elseif($pinfo != "" && $rinfo == "") {
				$pinfo = $order.$pinfo;
				$plogret = pzmembermoneylod($principal,$uid,$pinfo,$id);
				if($plogret){
				return true;
				}else{
					return false;
				}
			}else {
				$pinfo = $order.$pinfo;
				$rinfo = $order.$rinfo;
				$plogret = pzmembermoneylod($principal,$uid,$pinfo,$id);
				$rlogret = pzmembermoneylod($rate,$uid,$rinfo,$id);
				if($plogret && $rlogret){
					return true;
				}else{
					return false;
				}
			}
		}
	}
	//天天盈追加保证金审核 @author:Bob	@time:2015/4/1
	function dayexaminemonthadd($id) {	//追加申请id
		$additional = M("shares_additional")->find($id);
		$apply = M("shares_apply")->find($additional['shares_id']);
		
		
		if(daysavemmoney($apply['uid'],$additional['principal'],$additional['manage_fee'],$apply['order'],$apply['id'],$additional['new_interest'])) { 
			$savedata['id'] = $additional['shares_id'];
			$savedata['principal'] = $apply['principal'] + $additional['principal'];
			$savedata['shares_money'] = $apply['shares_money'] + $additional['shares_money'];
			$savedata['open'] = $additional['open_ratio'] + $apply['open'];
			$savedata['alert'] = $additional['alert_ratio'] + $apply['alert'];
			$savedata['total_money'] = $savedata['principal'] + $savedata['shares_money'];
	
			$savedata['manage_fee'] = $apply['manage_fee'] - $apply['already_manage_fee'] + $additional['manage_fee'];//总管理费=原本管理费-已出管理费+追加管理费
			$savedata['one_manage_fee'] = $apply['one_manage_fee'] + $additional['new_interest'];//现一天管理费=追加所选一天管理费+原本一天管理费
			
			$ret = M("shares_apply")->save($savedata);
			if($ret) {
				return true;
			}
		}
	}
	function daysavemmoney($uid,$guarantee_money,$interest,$order,$id,$new_interest) {
		$user_money = M('member_money')->where("uid = {$uid}")->find();
		
		$usermoney = array();
		
		if($user_money['back_money'] > ($guarantee_money + $interest)){
			
			$usermoney['back_money'] = $user_money['back_money'] - $guarantee_money;
			$usermoney['money_freeze'] = $user_money['money_freeze'] + $interest - $new_interest;
			
		}else{
			$usermoney['account_money'] = ($user_money['back_money'] + $user_money['account_money']) - $guarantee_money - $interest;
			$usermoney['money_freeze'] = $user_money['money_freeze'] + $interest - $new_interest;
			
		}
		$anti = M('member_money')->where("uid = {$uid}")->save($usermoney);
			
			if($anti){
				$ainfo = $order.'订单支付保证金';
				$iinfo = $order.'订单管理费冻结,已冻结追加管理费！';
				$areturnlog = pzmembermoneylod($guarantee_money,$uid,$ainfo,$id);
				$ireturnlog = pzmembermoneylod($interest,$uid,$iinfo,$id);
				
				if($ireturnlog && $areturnlog){
					return true;
				}else{
					
					return false;
				}
			}
	}
	
	//补充实盘资金申请	@author:Bob	@time:2015/4/2  	@modify:yh  	@time:2045/04/3
	function dosupply($id,$money,$uid,$type_id,$iscut = false) {	//配资id 补充金额 用户id 类型:天/月/操 是否是减少实盘资金
		$member_money = M('member_money')->where("uid = {$uid}")->find();
		$apply = M("shares_apply")->find($id);
		
		//判断余额是否足够支付补充金额
		if($money > ($member_money['account_money'] + $member_money['back_money']) && $iscut==false){
			echo jsonmsg('可用余额不足,请充值！',0);exit;
		}else if($type_id==3 && $iscut==false){
			if($member_money['back_money'] >= $money){
				$usermoney['back_money'] = $member_money['back_money'] - $money;
			}else{
				$usermoney['account_money'] = ($member_money['back_money'] + $member_money['account_money']) - $money;
				$usermoney['back_money'] = 0;
			}
			$ret = M('member_money')->where("uid = {$uid}")->save($usermoney);
			if($ret){
				$pinfo = $apply['order'].'订单支付追加实盘资金';
				$plogret = pzmembermoneylod($money,$uid,$pinfo,$id,52);
			}
		}else if($iscut==true){//判断用户的总操盘资金是否足够
			$total_money = M('shares_apply')->field('shares_money')->where("id=$id")->find();
			if($total_money['shares_money']<$money){
				echo jsonmsg('配资资金小于要减少的实盘资金！',0);exit;
			}
		}
		$data['uid'] = $uid;	//用户id
		$data['supply_money'] = $money;	//补充金额
		$data['shares_id'] = $id;	//配资id
		$data['type_id'] = $type_id;	//类型id
		$data['add_time'] = time();	//添加时间
		if($iscut==false)
			$data['status'] = 1;	//状态
		else
			$data['status'] = 4;	//操盘手减少实盘资金待审核状态
		$data['u_name'] = $apply['u_name'];	//用户名
		$data['order'] = $apply['order'];	//配资订单号
		$ret = M("shares_supply");$ret->add($data);
		if($ret) {
			if($iscut==false){
				echo jsonmsg("申请补充成功,请等待审核",1);exit;
			}else{
				echo jsonmsg("申请减少成功,请等待审核",1);exit;
			}
		} else {
			if($iscut==false){
				echo jsonmsg('申请补充失败！',0);exit;
			}else{
				echo jsonmsg('申请减少失败！',0);exit;
			}
		}
	}

	function tradesupplyexamine($id){	//我是操盘手补充实盘资金
		$supply = M("shares_supply")->find($id);
		$apply = M("shares_apply")->find($supply['shares_id']);
		$savedata['id'] = $apply['id'];
		$savedata['total_money'] = $apply['total_money'] + $supply['supply_money'];
		if(M("shares_apply")->save($savedata)) {
			return true;
		}else {
			return false;
		}
	}
	function tradecutexamine($id){
		$supply = M("shares_supply")->find($id);
		$affect_money = $supply['supply_money'];	//影响金额
		$apply_id = $supply['shares_id'];	//配资id
		$apply = M("shares_apply")->find($apply_id);	
		$uid = $apply['uid']; 	//用户id
		$save_data['total_money'] = $apply['total_money'] - $affect_money;
		$save_data['principal'] = round($save_data['total_money'] / ($apply['lever_ratio'] + 1));
		$save_data['shares_money'] = $save_data['total_money'] - $save_data['principal'];
		$now_principal = $apply['principal'] - $save_data['principal'];
		$save_data['open'] = $save_data['principal'] * $apply['open_ratio'] / 100 +$save_data['shares_money'];	//用户的平仓线
		$save_data['alert'] = $save_data['principal'] * $apply['alert_ratio'] / 100 +$save_data['shares_money'] ;	//用户的警戒线
		if(M('shares_apply')->where("id={$apply_id}")->save($save_data)){
			$find_member_money = M('member_money')->where("uid={$uid}")->field('account_money')->find();	//获取用户的回款资金
			$money_data['account_money'] = $find_member_money['account_money'] + $now_principal;	//返给用户的资金
			$member_money = M('member_money')->where("uid={$uid}")->save($money_data);
			$ainfo = $apply['order']."操盘手减少实盘资金回款";
			pzmembermoneylod($now_principal,$uid,$ainfo,$apply_id ,52);
			if(!$member_money) return false;
			return true;
		}else{
			return false;
		}

	}
	//补充实盘月月盈审核	@author:Bob	@time:2015/4/2
	function supplyexamine($id) { //补充实盘资金id
		$supply = M("shares_supply")->find($id);
		$apply = M("shares_apply")->find($supply['shares_id']);
		$info = "订单支付补充实盘月月盈资金";
		if(savemmoney($apply['id'],$apply['uid'],$supply['supply_money'],0,$info)) {
			$savedata['id'] = $apply['id'];
			$savedata['total_money'] = $apply['total_money'] + $supply['supply_money'];
			if(M("shares_apply")->save($savedata)) {
				return true;
			}else {
				return false;
			}
		}else {
			return false;
		}
	}
	//天天盈资金补充
	function daysupply($id){
		$supply = M("shares_supply")->find($id);
		$apply = M("shares_apply")->find($supply['shares_id']);
		
		if(daysavemoney($apply['uid'],$supply['supply_money'])){
			
			$savedata['id'] = $apply['id'];
			$savedata['principal'] = $apply['principal'] + $supply['supply_money'];
			if(M("shares_apply")->save($savedata)) {
				return true;
			}else {
				return false;
			}
			
		}else{
			
			return false;
		}
		
		
	}
	//天天盈资金补充金额扣除
	function daysavemoney($uid,$money){
		$user_money = M('member_money')->where("uid = {$uid}")->find();
		
		$savamoney = array();
		if($user_money['back_money'] > $money){
			
			$savamoney['back_money'] = $user_money['back_money'] - $money;
		}else{
			$savamoney['account_money'] = ($user_money['back_money'] + $user_money['account_money']) - $money;
			$savamoney['back_money'] = 0;
			
		}
		$ret = M('member_money')->where("uid = {$uid}")->save($savamoney);
		
		if($ret){
			$info = "补充资金已增加".$money;
			$log = pzmembermoneylod($money,$uid,$info);
			
			if($log){
				
				return true;
			}else{
				
				return false;
			}
			
		}else{
			
			return false;
		}
	}
	//天天盈减少实盘金额审核 
	function reduce_thefirm($id) {
		$additional = M("shares_additional")->find($id);
		$apply = M("shares_apply")->find($additional['shares_id']);
		
		if(dayreducesavemmoney($apply['uid'],$apply['principal'] - $additional['principal'],$additional['manage_fee'],$apply['order'],$apply['id'])) { 
			$savedata['id'] = $additional['shares_id'];
			$savedata['principal'] = $additional['principal'];
			$savedata['shares_money'] = $additional['shares_money'];
			$savedata['open'] = $apply['open'] - $additional['open_ratio'];
			$savedata['alert'] = $apply['alert'] - $additional['alert_ratio'];
			$savedata['total_money'] = $savedata['principal'] + $savedata['shares_money'];
			
			$savedata['manage_fee'] = $apply['manage_fee'] - $additional['manage_fee'];//总管理费等于已出管理费-减少实盘管理费
			$savedata['one_manage_fee'] = $additional['new_interest'];
			
			$ret = M("shares_apply")->save($savedata);
			if($ret) {
				return true;
			}
		}
	}
	
	//用户手动申请平仓 @author:Bob	@time:2015/4/2
	function applyeven($id) {	//配资id
		$savedata['is_want_open'] = 1;
		$savedata['id'] = $id;
		$savedata['want_open_time'] = time();
		$ret = M("shares_apply")->save($savedata);
		if($ret) {
			echo jsonmsg("平仓申请成功，请等待处理!",1);exit;
		}else {
			echo jsonmsg("平仓申请失败或已申请并正在处理中!",0);exit;
		}
	}
	
	//平仓申请后台处理	@author:Bob	@time:2015/4/2
	function doapplyeven($id,$counttrader) { //配资id	 剩余金额
		$apply = M("shares_apply")->find($id);
		
		//计算是否还有剩余未付管理费
		$surplus = ($apply['manage_fee'] - $apply['already_manage_fee']) / $apply['one_manage_fee'];
		if($surplus >= 1) {
			
			//执行平仓
			if(monthopen($id,$counttrader)) {
				
				//罚一个月管理费
				$member_money = M("member_money")->where("uid = {$apply['uid']}")->find();
				$savedata['uid'] = $member_money['uid'];
				$savedata['account_money'] = $member_money['account_money'] - $apply['one_manage_fee'];
				$ret = M("member_money")->save($savedata);
				if($ret) {
					$info = $apply['order']."订单提前平仓扣除罚金";
					pzmembermoneylod($apply['one_manage_fee'],$member_money['uid'],$info,$apply['id']);
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			$ret = monthopen($id,$counttrader);
			if($ret) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	//我是操盘手申请平仓处理 @author: yh @time 2015/4/3
	function traderdoapplyeven($id,$counttrader,$total_money,$uid,$principal){	//参数 配资id 剩余金额 用户操盘总金额  本金
		//查询用户回款余额
		$ret =  M('member_money')->field('back_money')->where("uid=$uid")->find();
		$ainfo = '平仓成功返回资金';
		if($counttrader-$total_money >=0){//用户盈利
			if($counttrader-$total_money==0){//不挣不陪，返回本金
				$data['back_money'] = $ret['back_money'] + $principal;		//用户本金加原有的用户金额
				$res = M('member_money');$res->where("uid=$uid")->save($data);
				if(!$res) return false;
				$areturnlog = pzmembermoneylod($principal,$uid,$ainfo,$id,52);
				return true;
			}else if($counttrader-$total_money>0){//用户盈利
				$rae = M('shares_global')->field('text')->where('id=136')->find();
				$userrae = explode('|',$rae['text']);
				$result = $counttrader-$total_money;
				$tmp = $result*($userrae[1]/10);	//用户所得资金
				$data['back_money']= $ret['back_money']+$tmp+$principal;	//用户的原有资金+用户所得资金+本金
				$apply = M('shares_apply')->where("id=$id and type_id=3")->setField('status',3);	//回收完成
				if(!$apply) return false;
				$res = M('member_money')->where("uid=$uid")->save($data);
				if(!$res) return false;
				$areturnlog = pzmembermoneylod($tmp+$principal,$uid,$ainfo,$id,52);
				return true;
			}
		}else{//用户亏损
				$result = abs($counttrader-$total_money);	//亏损的金额
				$principal -= $result;	//返回给用户的本金
				$data['back_money']  = $ret['back_money']+$principal;	//用户的回款资金 = 原有的资金+返回给用户的本金
				$res = M('member_money')->where("uid=$uid")->save($data);
				if(!$res) return false;
				$apply = M('shares_apply')->where("id=$id and type_id=3")->setField('status',3);	//回收完成
				if(!$apply) return false;
				$areturnlog = pzmembermoneylod($principal,$uid,$ainfo,$id,52);
				return true;
		}
	}

	function dayreducesavemmoney($uid,$guarantee_money,$interest,$order,$id,$new_interest) {
		$user_money = M('member_money')->where("uid = {$uid}")->find();
		
		$usermoney = array();
		

			$usermoney['account_money'] = $user_money['account_money'] + $guarantee_money + $interest;
			$usermoney['money_freeze'] = $user_money['money_freeze'] - $interest;

		$anti = M('member_money')->where("uid = {$uid}")->save($usermoney);
			
			if($anti){
				$ainfo = $order.'订单减少实盘金额';
				$iinfo = $order.'订单减少实盘金额冻结金额减少：'.$interest.',已增加到可用余额！';
				$areturnlog = pzmembermoneylod($guarantee_money,$uid,$ainfo,$id);
				$ireturnlog = pzmembermoneylod($interest,$uid,$iinfo,$id);
				
				if($ireturnlog && $areturnlog){
					return true;
				}else{
					
					return false;
				}
			}
	}
	
	function getMoneyLimit($uid) {
		$money_limit = 0;
		$apply_money = M("shares_apply")->where("uid = {$uid} and status = 1")->sum("principal + one_manage_fee");
		if($apply_money != ""){
			$money_limit += $apply_money;

		}
		$apply_id = M("shares_apply")->where("uid = {$uid} and status in(2,6)")->field("id")->select();
		if($apply_id){
			foreach($apply_id as $v) {
				$aids[] = $v['id'];
			}
			$aids = implode(",",$aids);
			$additional_money = M("shares_additional")->where("status = 1 and shares_id in({$aids})")->sum("principal + manage_fee");
			if($additional_money != "") {
				$money_limit += $additional_money;
			}
			
			$supply_money = M("shares_supply")->where("status = 1 and shares_id in({$aids})")->sum("supply_money");
			if($supply_money != "") {
				$money_limit += $supply_money;
			}
		}
		return $money_limit;
	}
	function currentinvest($buy_money,$current_id,$uid){
		$current_info = M('current_info')->find($current_id);
		$data = array();
		$data['have_money'] = $current_info['have_money'] + $buy_money;
		$res = M('current_info')->where("id = {$current_id}")->save($data);
		$vo = M('current_info')->find($current_id);
		if($vo['have_money'] == $vo['current_money']){
			$status = array();
			$status['status'] = 2;
			M('current_info')->where("id = {$current_id}")->save($status);
		}
		if($res){
			
			$user_money = M('member_money')->find($uid);
			$savemoney = array();
			if($buy_money > $user_money['back_money']){
				
				$savamoney['account_money'] = ($user_money['back_money'] + $user_money['account_money']) - $buy_money;
				$savamoney['back_money'] = 0;
				
			}else{
				
				$savamoney['back_money'] = $user_money['back_money'] - $buy_money;
			}
			
			$upmoney = M('member_money')->where("uid = {$uid}")->save($savamoney);
			
			if($upmoney){
				$info = '您加入活期理财'.$buy_money.'元';
				$log = pzmembermoneylod($buy_money,$uid,$info);
				
				if($log){
					
					return true;
				}else{
					
					return false;
				}
				
			}else{
				
				return false;
			}
			
			
		}else{
			
			return false;
		}
		
	}
	
	//活期赎回计算公式方法
	function profit($buy_times,$inerest_rate,$buy_money){
		$buy_time = date("Y-m-d",$buy_times);
		$d_time = date("Y-m-d",time());
		$cha_time = floor((strtotime($d_time)-strtotime($buy_time))/86400);//计算出当前时间与购买时间之差			
		$money = $inerest_rate / 100 / 365 * $buy_money * ($cha_time - 1);
		$money =  round($money,2);
		
		if($money < 0){
			$money =  0;
			return $money;
		}else{
			$money =  round($money,2);
			return $money;
		}
	}
	//已购买天数
	function buy_day($buy_times){
		
		$buy_time = date("Y-m-d",$buy_times);
		$d_time = date("Y-m-d",time());
		$cha_time = floor((strtotime($d_time)-strtotime($buy_time))/86400);//计算出当前时间与购买时间之差
		$day = $cha_time - 1;
		return $day;
		
	}
	//活期理财审核 @dong
	function extraction($buy_money,$interest,$id){
		
		$to = $buy_money + $interest;
		$savestatus = array();
		$savestatus['status'] = 3;
		$status = M('current_investor')->where("id = {$id}")->save($savestatus);
		if($status){
			
			$uid = M('current_investor')->getFieldByid($id,"invest_uid");
			$user_money = M("member_money")->find($uid);
			$savemoney = array();
			$savamoney['account_money'] = $user_money['account_money'] + $to;
			$ret = M('member_money')->where("uid = {$uid}")->save($savamoney);
			if($ret){
				$info = '活期理财提取本金+利息'.$to.'元。';
				$log = pzmembermoneylod($to,$uid,$info);
				if($log){
					
					return true;
				}else{
					
					return false;
				}
			}else{
				
				return false;
			}
		}else{
			
			return false;
		}
		
	}
	
	//根据邀请码获取配资专员id @author:Bob	@time:2015/4/20
	function getAdminidByInvitation($code) {
		return M("ausers")->where("invitation_code = '{$code}'")->getField("id");
	}
	
	//根据ID获取配资专员佣金 @author:Bob	@time:2015/4/20
	function getComById($id) {
		return M("shares_apply")->where("stock_admin_id = {$id} AND status in(2,3,6)")->sum("already_manage_fee");
	}
	
	//生成配资专员邀请码 @dong
	function Getstockcode($aid){
		
		$code = substr(MD5($aid.time()),0,8);
		$url =  "http://".$_SERVER['SERVER_NAME']."?i=".$code;
		return $url;
	}
	//发放记录存表 @author:dong	@time:2015/4/20
	function payment_log($auser_id,$money){
		
		$data = array();
		$data['title'] = '佣金发放';
		$data['content'] = date("Y-m-d H:i:s",time()).'发放佣金共'.$money.'元。';
		$data['money'] = $money;
		$data['auser_id'] = $auser_id;
		
		$ret = M('payment_log')->add($data);
		if($ret){
			
			return true;
		}else{
			return false;
		}
	}
	
	//判断配资是否申请停止操盘 @author:Bob	@time:2015/4/24
	function getWantApply($id) {
		$apply = M("shares_apply")->find($id);
		if($apply['is_want_open'] == 1) {
			return false;
		}else {
			return true;
		}
	}
	
	//获取配资资金统计 @author:Bob	@time:2015/4/27
	function getCapitalStock($type="count",$stype=1) {
		if($type == "count") {
			return M("shares_apply")->where("type_id = {$stype} AND status in ('2,3,6')")->sum("shares_money");
		}elseif($type == "fee") {
			return M("shares_apply")->where("type_id = {$stype} AND status in ('2,3,6')")->sum("manage_fee");
		}elseif($type == "already_fee") {
			return M("shares_apply")->where("type_id = {$stype} AND status in ('2,3,6')")->sum("already_manage_fee");
		}
	}
	
?>