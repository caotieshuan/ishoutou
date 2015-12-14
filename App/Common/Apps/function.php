<?php
//获取借款列表
function getBorrowList($parm=array()){
	if(empty($parm['map'])) return;
	$map= $parm['map'];
	$orderby= $parm['orderby'];
	if($parm['pagesize']){
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->where($map)->count('b.id');
		$p = new Page($count, $parm['pagesize']);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		$Lsql="{$parm['limit']}";
	}
	$pre = C('DB_PREFIX');
	$suffix=C("URL_HTML_SUFFIX");
	$field = "b.id,b.borrow_name,b.borrow_type,b.reward_type,b.borrow_times,b.borrow_status,b.borrow_money,b.borrow_use,b.repayment_type,b.borrow_interest_rate,b.borrow_duration,b.collect_time,b.add_time,b.province,b.has_borrow,b.has_vouch,b.city,b.area,b.reward_type,b.reward_num,b.password,m.user_name,m.id as uid,m.credits,m.customer_name,b.is_tuijian,b.deadline,b.danbao,b.borrow_info,b.risk_control,b.stock_type";
	$list = M('borrow_info b')->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->order($orderby)->limit($Lsql)->select();
	$areaList = getArea();
	foreach($list as $key=>$v){
		$list[$key]['location'] = $areaList[$v['province']].$areaList[$v['city']];
		$list[$key]['biao'] = $v['borrow_times'];
		$list[$key]['need'] = $v['borrow_money'] - $v['has_borrow'];
		$list[$key]['leftdays'] = getLeftTime($v['collect_time']);
		$list[$key]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
		$list[$key]['vouch_progress'] = getFloatValue($v['has_vouch']/$v['borrow_money']*100,2);
		$list[$key]['burl'] = MU("Home/invest","invest",array("id"=>$v['id'],"suffix"=>$suffix));
		
		
		//新加
		$list[$key]['lefttime']=$v['collect_time']-time();
				
		if($v['deadline']==0){
			$endTime = strtotime(date("Y-m-d",time()));
			if($v['repayment_type']==1) {
				$list[$key]['repaytime'] = strtotime("+{$v['borrow_duration']} day",$endTime);
			}else {
				$list[$key]['repaytime'] = strtotime("+{$v['borrow_duration']} month",$endTime);
			}
		}else{
			$list[$key]['repaytime']=$v['deadline'];//还款时间
		}

		$list[$key]['publishtime']=$v['add_time']+60*60*24*3;//预计发标时间=添加时间+1天
		
		if($v['danbao']!=0 ){
			$danbao = M('article')->field("id,title")->where("type_id =7 and id ={$v['danbao']}")->find();
			$list[$key]['danbao']=$danbao['title'];//担保机构
		}else{
			$list[$key]['danbao']='暂无担保机构';//担保机构
		}
		
	}
	
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	return $row;
}
//获取企业直投借款列表
function getTBorrowList($parm =array())
{
	if(empty($parm['map'])) return;
	$map = $parm['map'];
	$orderby = $parm['orderby'];
	//dump($parm['pagesize']);die;
	if($parm['pagesize'])
	{
		
		import( "ORG.Util.Page" );
		$count = M("transfer_borrow_info b")->where($map)->count("b.id");
		$p = new Page($count, $parm['pagesize']);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
	}else{
		
		$page = "";
		$Lsql = "{$parm['limit']}";
		
	}
	$pre = C("DB_PREFIX");
	$suffix =C("URL_HTML_SUFFIX");
	//dump($suffix);die;
	$field = "b.id,b.borrow_name,b.borrow_status,b.borrow_money,b.repayment_type,b.min_month,b.transfer_out,b.transfer_back,b.transfer_total,b.per_transfer,b.borrow_interest_rate,b.borrow_duration,b.increase_rate,b.reward_rate,b.deadline,b.is_show,m.province,m.city,m.area,m.user_name,m.id as uid,m.credits,m.customer_name,b.borrow_type,b.b_img,b.add_time,b.collect_day,b.danbao,b.stock_type";
$list = M("transfer_borrow_info b")->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->order($orderby)->limit($Lsql)->select();
	$areaList = getarea();//(国家、省、市、县。。)
	//dump($areaList);die;
	foreach($list as $key => $v)
	{
		//($list是android那边需要的数据)
		$list[$key]['location'] = $areaList[$v['province']].$areaList[$v['city']];
		$list[$key]['progress'] = getfloatvalue( $v['transfer_out'] / $v['transfer_total'] * 100, 2);
		$list[$key]['need'] = getfloatvalue(($v['transfer_total'] - $v['transfer_out'])*$v['per_transfer'], 2 );
		$list[$key]['burl'] = MU("Home/invest_transfer", "invest_transfer",array("id" => $v['id'],"suffix" => $suffix));	
		
		$temp=floor(("{$v['collect_day']}"*3600*24-time()+"{$v['add_time']}")/3600/24);
		$list[$key]['leftdays'] = "{$temp}".'天以上';
		$list[$key]['now'] = time();
		$list[$key]['borrow_times'] = count(M('transfer_borrow_investor') -> where("borrow_id = {$list[$key]['id']}") ->select());
		if($v['danbao']!=0 ){
			$list[$key]['danbaoid'] = intval($v['danbao']);
			$danbao = M('article')->field('id,title')->where("type_id=7 and id={$v['danbao']}")->find();
			$list[$key]['danbao']=$danbao['title'];//担保机构
		}else{
			$list[$key]['danbao']='暂无担保机构';//担保机构
		}	
	}
	$row = array();
	$row['list'] = $list;
	
	$row['page'] = $page;
	
	return $row;
}
// //手机专用
// function getleixing($map){
	
// 	if($map['borrow_type']==2) $str=4;//担保标
// 	elseif($map['borrow_type']==3) $str=5;//秒还标
// 	elseif($map['borrow_type']==4) $str=6;//净值标
// 	elseif($map['borrow_type']==1) $str=3;//信用标
// 	elseif($map['borrow_type']==5) $str=7;//抵押标
// 	return $str;
// } 
//获取借款列表
// function getMemberDetail($uid){
// 	$pre = C('DB_PREFIX');
// 	$map['m.id'] = $uid;
// 	//$field = "*";
// 	$list = M('members m')->field(true)->join("{$pre}member_banks mbank ON m.id=mbank.uid")->join("{$pre}member_contact_info mci ON m.id=mci.uid")->join("{$pre}member_house_info mhi ON m.id=mhi.uid")->join("{$pre}member_department_info mdpi ON m.id=mdpi.uid")->join("{$pre}member_ensure_info mei ON m.id=mei.uid")->join("{$pre}member_info mi ON m.id=mi.uid")->join("{$pre}member_financial_info mfi ON m.id=mfi.uid")->where($map)->limit($Lsql)->find();
// 	return $list;
// }
//获取充值记录
function getChargeLog($map,$size,$limit=10,$order){
	if(empty($map['uid'])) return;
	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('member_payonline')->where($map)->count('id');
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		$Lsql="{$parm['limit']}";
	}
	$status_arr =array('充值未完成','充值成功','签名不符','充值失败');
	$list = M('member_payonline')->where($map)->order($order)->limit($Lsql)->select();
	foreach($list as $key=>$v){
		$list[$key]['status'] = $status_arr[$v['status']];
	}
	
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	$map['status'] = 1;
	$row['success_money'] = M('member_payonline')->where($map)->sum('money');
	$map['status'] = array('neq','1');
	$row['fail_money'] = M('member_payonline')->where($map)->sum('money');
	return $row;
}
function getWithDrawLog($map,$size,$limit=10,$order){
	if(empty($map['uid'])) return;
	$page="";
	$Lsql=$limit;
	$status_arr =array('提交失败','提交成功');
	$list = M('member_withdraw')->where($map)->order($order)->limit($Lsql)->select();
	foreach($list as $key=>$v){
		$list[$key]['status'] = $status_arr[$v['withdraw_status']];
	}
	
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	$map['status'] = 1;
	$row['success_money'] = M('member_payonline')->where($map)->sum('money');
	$map['status'] = array('neq','1');
	$row['fail_money'] = M('member_payonline')->where($map)->sum('money');
	return $row;
}
function getTenderList($map,$size,$limit,$order){
	$pre = C('DB_PREFIX');
	$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
	//if(empty($map['i.investor_uid'])) return;
	if(empty($map['investor_uid'])) return;
	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_investor i')->where($map)->count('i.id');
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		// $Lsql="{$parm['limit']}";
		$Lsql=$limit;
	}
	
	$type_arr =$Bconfig['BORROW_TYPE'];
	/////////////////////////视图查询 fan 20130522//////////////////////////////////////////
	$Model = D("TenderListView");
	$list=$Model->field(true)->where($map)->order($order)->group('id')->limit($Lsql)->select();
	////////////////////////视图查询 fan 20130522//////////////////////////////////////////
	foreach($list as $key=>$v){
		//if($map['i.status']==4){
		if($map['status']==4){
			$list[$key]['total'] = ($v['borrow_type']==3)?"1":$v['borrow_duration'];
			$list[$key]['back'] = $v['has_pay'];
			$vx = M('investor_detail')->field('deadline')->where("borrow_id={$v['borrowid']} and status=7")->order("deadline ASC")->find();
			$list[$key]['repayment_time'] = $vx['deadline'];
		}
	}

	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	$row['total_money'] = M('borrow_investor i')->where($map)->sum('investor_capital');
	$row['total_num'] = $count;
	return $row;
}

function getMoneyLog_wap( $map, $size ,$order)
{
				if ( empty( $map['uid'] ) )
				{
								return;
				}
				// if ( $size )
				// {
				// 				import( "ORG.Util.Page" );
				// 				$count = M( "member_moneylog" )->where( $map )->count( "id" );
				// 				$p = new Page( $count, $size );
				// 				$page = $p->show( );
				// 				$Lsql = "{$p->firstRow},{$p->listRows}";
				// }
				$list = M( "member_moneylog" )->where( $map )->order($order)->limit($size)->select();
				//echo M( "member_moneylog" )->getLastSql();die;
				$type_arr = c( "MONEY_LOG" );
				foreach ( $list as $key => $v )
				{
					$list[$key]['type'] = $type_arr[$v['type']];
				}
				$row = array( );
				 $row['list'] = $list;
				// $row['page'] = $page;
				return $row;
}


//////////////////////////////企业直投 管理模块开始  /////////////////////////////
function getTTenderList($map,$size,$limit,$order)
{
	$pre = C("DB_PREFIX");
	$Bconfig = require(C("APP_ROOT")."Conf/borrow_config.php");
	if(empty($map['i.investor_uid']))
	{
		return;
	}
	if($size)
	{
		import( "ORG.Util.Page" );
		$count = M("transfer_borrow_investor i")->where($map)->count("i.id");
		$p = new Page($count,$size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
	}else{
		$page = "";
		// $Lsql = "{$parm['limit']}";
		$Lsql=$limit;
	}
	$type_arr = $Bconfig['BORROW_TYPE'];
	$field = "i.*,i.add_time as invest_time,m.user_name as borrow_user,b.borrow_duration,b.borrow_interest_rate,b.add_time as borrow_time,b.borrow_money,b.borrow_name,m.credits";
	//"i.id DESC"
	$list = M("transfer_borrow_investor i")->field($field)->where($map)->join("{$pre}transfer_borrow_info b ON b.id=i.borrow_id")->join( "{$pre}members m ON m.id=b.borrow_uid")->order($order)->limit($Lsql)->select();
	foreach($list as $key => $v )
	{
		if($map['i.status'] == 4 )
		{
			$list[$key]['total'] = $v['borrow_type'] == 3 ? "1" : $v['borrow_duration'];
			$list[$key]['back'] = $v['has_pay'];
		}
	}
	$row = array();
	$row['list'] = $list;
	$row['page'] = $page;
	$row['total_money'] = M("transfer_borrow_investor i")->where($map)->sum("investor_capital");
	$row['total_num'] = $count;
	return $row;
}


function repaymentList($borrow_id, $sort_order, $type=1)
    {
        $pre = C('DB_PREFIX');
        $loanconfig = FS("Webconfig/loanconfig"); 
        $detail = array();
        
        $borrowDetail = D('investor_detail');
        $binfo = M("borrow_info")->field("id,borrow_uid, borrow_type, borrow_money, borrow_duration,repayment_type,has_pay,total,deadline, borrow_status")->find($borrow_id);
        $b_member=M('members')->field("user_name")->find($binfo['borrow_uid']);
        if($binfo['has_pay']>=$sort_order) ajaxmsg("本期已还过，不用再还",0);
        if( $binfo['has_pay'] == $binfo['total'])  ajaxmsg("此标已经还完，不用再还",0);
        if( ($binfo['has_pay']+1)<$sort_order) ajaxmsg("对不起，此借款第".($binfo['has_pay']+1)."期还未还，请先还第".($binfo['has_pay']+1)."期",0) ;
        if( $binfo['deadline']>time() && $type==2)  ajaxmsg("此标还没逾期，不用代还",0); 
        
        $accountMoney_borrower = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
        
        $voxe = $borrowDetail
                    ->field('sort_order,sum(capital) as capital, sum(interest) as interest,sum(interest_fee) as interest_fee,deadline,substitute_time')
                    ->where("borrow_id={$borrow_id} and sort_order={$sort_order} and pay_status=1")
                    ->group('sort_order')
                    ->find();
        
        if($voxe['deadline'] < time()){//此标已逾期
            $is_expired = 1; 
            $expired_days = getExpiredDays($voxe['deadline']);
            $expired_money = getExpiredMoney($expired_days,$voxe['capital'],$voxe['interest']); // 预期管理费
            $call_fee = getExpiredCallFee($expired_days,$voxe['capital'],$voxe['interest']); // 催收费用
            //逾期的相关计算
        }else{
            $is_expired = 0;
            $expired_days = 0;
            $expired_money = 0;
            $call_fee = 0;
        }       
        $detail['is_expired'] = $is_expired;
        //逾期的相关计算 start
        $detail['expired_days'] = $expired_days;
        $detail['expired_money'] = $expired_money;
        $detail['call_fee'] = $call_fee;
        //逾期的相关计算 end
     
        if($type==1 && $binfo['borrow_type']<>3 && ($accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'])<($vo['capital']+$vo['interest']+$expired_money+$call_fee)) 
        $this->error("帐户可用余额不足，本期还款共需".($voxe['capital']+$voxe['interest']+$expired_money+$call_fee)."元，请先充值");
        
        $vo = $borrowDetail
                    ->field('invest_id, investor_uid, sort_order,capital, interest, interest_fee , deadline,substitute_time')
                    ->where("borrow_id={$borrow_id} and sort_order={$sort_order} and pay_status=1")
                    ->select();
       
        foreach($vo as $k=>$v){
            if($v['substitute_time'] > 0){   //已代还 将资金给网站
                $v['qdd_marked'] = $loanconfig['pfmmm'];  
            }else { // 没有待还将资金还给投资人
                $escrow = M('escrow_account')->field('qdd_marked')->where("uid={$v['investor_uid']}")->find();
                $v['qdd_marked'] = $escrow['qdd_marked'];
            
            
            }
            $detail['list'][$k] = $v;
        }
//print_R($detail);exit;
        return $detail;
        
    }
	
	
	
function mcgetBorrowList($map,$size,$limit,$order){
	if(empty($map['borrow_uid'])) return;
	
	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info')->where($map)->count('id');
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		$Lsql=$limit;
	}
	
	$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
	$status_arr =$Bconfig['BORROW_STATUS_SHOW'];
	$type_arr =$Bconfig['REPAYMENT_TYPE'];
	//$list = M('borrow_info')->where($map)->order('id DESC')->limit($Lsql)->select();
	/////////////使用了视图查询操作 fans 2013-05-22/////////////////////////////////
	$Model = D("BorrowView");
	$list=$Model->field(true)->where($map)->order($order)->group('id')->limit($limit)->select();

	/////////////使用了视图查询操作 fans 2013-05-22/////////////////////////////////
	foreach($list as $key=>$v){
		$list[$key]['status'] = $status_arr[$v['borrow_status']];
		$list[$key]['repayment_type_num'] = $v['repayment_type'];
		$list[$key]['repayment_type'] = $type_arr[$v['repayment_type']];
		$list[$key]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
		if($map['borrow_status']==6){
			$vx = M('investor_detail')->field('deadline')->where("borrow_id={$v['id']} and status=7")->order("deadline ASC")->find();
			$list[$key]['repayment_time'] = $vx['deadline'];
		}
		if($map['borrow_status']==5 || $map['borrow_status']==1){
			$vd = M('borrow_verify')->field(true)->where("borrow_id={$v['id']}")->find();
			$list[$key]['dealinfo'] = $vd;
		}
	}
	
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	//$map['status'] = 1;
	//$row['success_money'] = M('member_payonline')->where($map)->sum('money');
	//$map['status'] = array('neq','1');
	//$row['fail_money'] = M('member_payonline')->where($map)->sum('money');
	return $row;
}
       function appgetmoney($money)
      {
      if($money>=10000 && $money<=100000000){
      $res = getFloatValue(($money/10000),2)."万";
      }else if($money>=100000000){
      $res = getFloatValue(($money/100000000),2)."亿";
      }else{
      $res = getFloatValue($money,0);
        }
       return $res;
      }

?>