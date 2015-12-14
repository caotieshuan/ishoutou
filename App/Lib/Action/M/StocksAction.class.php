<?php
// 本类由系统自动生成，仅供测试用途
/*	股权配资月类控制器
 *	
 *	@time:2015/5/20
 */
	//月月盈
class StocksAction extends HCommonAction {
	
	public function month(){
		//echo "erieur";die;
		$lever = D("SharesLever")->getMonthLever();
		$term_config = D("SharesType")->getMonthtermConfig();
		$money_config = D("SharesType")->getMonthmoneyConfig();
		if(get_holiday_data('shares_holiday') == '1' || date('w',time()) == 6 || date('w',time()) == 0 || date('H',time()) >= 14){
			$this->assign('holiday',1);
		}else{
			$this->assign('holiday',0);
		}
		//配资排行榜
		
		$shares_list = M('shares_apply a')->field("a.shares_money,a.add_time,a.duration,m.user_name")->join("lzh_members m ON m.id = a.uid")->where("a.type_id = 2 AND a.status in(2,3,6)")->order("a.id DESC")->select();
		
		$this->assign("shares_list",$shares_list);
		$this->assign("count",count($shares_list));
		$this->assign("uid",$this->uid);
		$this->assign("term",$term_config);
		$this->assign("min_money",$money_config[0]);
		$this->assign("max_money",$money_config[1]);
		$this->assign("lever",$lever);
		//var_dump($lever['0']['open_ratio']);die;
		$this->display();
	}


	//提交配资申请
	public function postdata(){
		
		$member_money = M('member_money')->where("uid = {$this->uid}")->find();

		/* //判断是否实名认证
		$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		if($ids!=1){
			echo jsonmsg('您还未完成身份验证,请先进行实名认证！',0);exit;
		} */
		//判断是否手机认证
		/*$phones = M('members_status')->getFieldByUid($this->uid,'phone_status');
		if($phones!=1){
			echo jsonmsg('您还未手机认证,请先进行手机认证！',0);exit;
		}*/
		$term_config = D("SharesType")->getMonthtermConfig();
		foreach($term_config as $k=>$v) {
			$overterm_config[] = $k;
		}
		$money_config = D("SharesType")->getMonthmoneyConfig();

		if($this->_post("principal") > $money_config[1] || $this->_post("principal") <$money_config[0]) {
			echo jsonmsg("数据有误1",0);exit;
		}elseif(!in_array($this->_post('lever_id'),$overterm_config)) {
			echo jsonmsg("数据有误2",0);exit;
		}elseif($this->_post("duration") < 1 || $this->_post("duration") > 24) {
			echo jsonmsg("数据有误3",0);exit;
		}elseif(($_POST['trading_time'] > 2) || ($_POST['trading_time'] < 1)) {
			echo jsonmsg("数据有误4",0);exit;
		}
		$uid = $this->uid;
		$money = D("SharesApply")->where("uid = {$uid} and status = 1")->sum("principal + one_manage_fee");
		$all_money = $money + $this->_post("principal");
		if($all_money > ($member_money['account_money'] + $member_money['back_money'])) {
			echo jsonmsg('您的可用余额不足以支付您所有的配资申请费用,请等待审核完成或进行充值！',0);exit;
		}
		//执行添加
	
		$ret = D("SharesApply")->addMonthStock();
		if($ret){
			echo jsonmsg('恭喜配资成功！',1);exit;
		}else{
			echo jsonmsg('恭喜配资失败！',0);exit;
		}
		
	}

	//确认配资页面渲染
	public function monthpayment() {
		$money = M('member_money')->where("uid = {$this->uid}")->find();
		$this->assign("money",$money);
		$this->display();
	}
	
	//根据期限以及金额获取利率
	public function getrate() {
		$month = $this->_get("term");
		$pzje = $this->_get("pzje");
		echo getrateratio($month,$pzje);
	}
	
}