<?php
// 全局设置
class CapitalallAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
		$map=array();
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['add_time'] = array("between",$timespan);
			$search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));	
			$search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}


		$list = M("member_moneylog")->field('type,sum(affect_money) as money')->where($map)->group('type')->select();
		$row=array();
		$name = C('MONEY_LOG');
		foreach($list as $v){
			$row[$v['type']]['money']= ($v['money']>0)?$v['money']:$v['money']*(-1);
			$row[$v['type']]['name']= $name[$v['type']];
		}
	

		
		$add_time = $map['add_time'];
		$map1['deadline'] = $add_time;
		$map1['status'] = array("in","7,3,4,5");
		$map2['deadline'] = $add_time;
		$map2['status'] = array("in","3,4,5");
		//逾期
		$row['expired']['money'] = M('investor_detail')->where($map1)->sum('capital');
		$row['expired']['re_money'] = M('investor_detail')->where($map2)->sum('capital');
		//逾期		
		//会员统计
		$mm = M('members')->count("id");
		$row['mm']['name']= '会员数';
		$row['mm']['num']= $mm;

		$ms_phone = M('members_status')->where("phone_status=1")->count("uid");
		$ms_id = M('members_status')->where("id_status=1")->count("uid");
		$ms_video = M('members_status')->where("video_status=1")->count("uid");
		$ms_face = M('members_status')->where("face_status=1")->count("uid");
		$ms_vip = M('members')->where("user_leve=1 AND time_limit>".time())->count("id");
		$row['mm']['name']= '会员数';
		$row['mm']['num']= $mm;
		$row['mm']['ms_phone']= $ms_phone;
		$row['mm']['ms_id']= $ms_id;
		$row['mm']['ms_video']= $ms_video;
		$row['mm']['ms_face']= $ms_face;
		$row['mm']['ms_vip']= $ms_vip;		
		$field = "sum(investor_capital) as investor_capital,sum(investor_interest) as investor_interest,sum(receive_capital) as receive_capital,sum(receive_interest) as receive_interest,sum(reward_money) as reward_money, sum(invest_fee) as invest_fee";
		$transfer = M("transfer_borrow_investor")->field($field)->find();
		
		$stock['daycount'] = getCapitalStock('count',1);
		$stock['monthcount'] = getCapitalStock('count',2);
		$stock['cpcount'] = getCapitalStock('count',3);
		
		$stock['daymf'] = getCapitalStock('fee',1);
		$stock['monthmf'] = getCapitalStock('fee',2);
		
		$stock['dayamf'] = getCapitalStock('already_fee',1);
		$stock['monthamf'] = getCapitalStock('already_fee',2);
		$map['way'] = 'llpay';
		$map['status'] = '1';
		//连连充值总额
		$llRecharge = M("member_payonline")->field('sum(money) as money')->where($map)->select();
		$this->assign("llRecharge", $llRecharge[0]);
		//汇潮充值总额
		unset($map['way']);
		$map['way'] = 'ecpss';
		$ecpssRecharge = M("member_payonline")->field('sum(money) as money')->where($map)->select();
		$this->assign("ecpssRecharge", $ecpssRecharge[0]);
		//京东充值总额
		unset($map['way']);
		$map['way'] = 'chinabank';
		$jdRecharge = M("member_payonline")->field('sum(money) as money')->where($map)->select();
		$this->assign("jdRecharge", $jdRecharge[0]);
		//线下充值
		unset($map['way']);
		$map['way'] = 'off';
		$offRecharge = M("member_payonline")->field('sum(money) as money')->where($map)->select();
		$this->assign("offRecharge", $offRecharge[0]);
		$rechargeSum = $llRecharge[0]['money']+$ecpssRecharge[0]['money']+$offRecharge[0]['money']+$jdRecharge[0]['money'];
		$this->assign('rechargeSum',$rechargeSum);
		//充值-提现
		$inoutSum = $rechargeSum-$row['29']['money'];
		$this->assign('inoutSum',$inoutSum);
		$this->assign($stock);
		
		//待收本金利息
    	$collectionSum = M('investor_detail')
        ->field('sum(interest) as interest, sum(capital) as capital,sum(interest_fee) as fee')
        ->where("  status in (6,7)")
        ->find();
        $this->assign('collectionSum',$collectionSum);
        //已还本金 利息
        unset($map['way']);
        unset($map['status']);
        unset($map['add_time']);
        $map['status'] = '2';
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['repayment_time'] = array("between",$timespan);
			$search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));	
			$search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['repayment_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['repayment_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
    	$receiveSum = M('investor_detail')
        ->field('sum(interest) as interest, sum(receive_capital) as capital,sum(interest_fee) as fee,(sum(interest)+sum(receive_capital)) as sumreceive')
        ->where($map)
        ->find();
        //提醒手续费
       	$map['withdraw_status'] =2;
       	unset($map['status']);
       	unset($map['repayment_time']);
       	 if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['deal_time'] = array("between",$timespan);
			$search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));	
			$search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['deal_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['deal_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		$tx = M('member_withdraw')->where($map)->sum("withdraw_fee");
		$row['tx']['name']= '提现手续费';
		$row['tx']['money']= $tx;
        $this->assign('receiveSum',$receiveSum);
       //
        

		$this->assign("transfer", $transfer);
		//会员统计
		$this->assign("search",$search);
		$this->assign('list',$row);
        $this->display();
    }

	
}
?>