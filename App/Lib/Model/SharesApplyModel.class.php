<?php
/*	股权配资模型类
 *	@author:Bob
 *	@time:2015/3/25
 */
class SharesApplyModel extends Model {
	
		public function addMonthStock() {
		$this->create();
		$lever = D("SharesLever")->getById($this->lever_id);
		$this->type_id = 2;	//类型 2为月月赢盈
		$this->lever_ratio = $lever['lever_ratio'];	//杠杆比率
		$this->shares_money = $this->principal * $this->lever_ratio; //所获配资金
		$this->manage_rate = getrateratio($this->duration,$this->lever_ratio);	//管理费比率
	
		$this->manage_fee = $this->manage_rate / 100 * $this->shares_money * $this->duration;	//总管理费
		$this->one_manage_fee = $this->manage_rate / 100 * $this->shares_money;	//单次管理费
		$this->order = "MPZ".rand(1,999).time();	//订单号
		$this->open = $this->principal * $lever['open_ratio'] /100 * $this->lever_ratio + $this->shares_money;	//平仓线
		$this->alert = $this->principal * $lever['alert_ratio'] /100 * $this->lever_ratio + $this->shares_money;	//警戒线
		$this->open_ratio = $lever['open_ratio'];	//平仓线比率
		$this->alert_ratio = $lever['alert_ratio'];	//警戒线比率
		$this->add_time = time();	//申请时间
		$this->ip_address = get_client_ip();	//申请ip
		$this->status = 1;	//状态
		$this->recovery_time = time();	//回收时间
		$this->already_manage_fee = $this->manage_rate / 100 * $this->shares_money;	//已收管理费
		$this->total_money = $this->principal + $this->shares_money;	//交易总金额
		$this->stock_admin_id = getAdminidByInvitation(session('invitation_code'));
		$this->u_name = session("u_user_name");	//申请用户名
		return $this->add();
		 /*  $ress=$this->add();
		 dump(M()->getlastsql());die;  */
	}
	
	//添加月月盈配资记录
/* 	public function addMonthStock() {
		$this->create();
		//var_dump($this->create());die;
		
		$lever = D("SharesLever")->getById($this->lever_id);
		//var_dump($lever);die;
		$this->type_id = 2;	//类型 2为月月赢盈
		$this->lever_ratio = $lever['lever_ratio'];	//杠杆比率
		$this->shares_money = $this->principal * $this->lever_ratio; //所获配资金
		$this->manage_rate = getrateratio($this->duration,$this->lever_ratio);	//管理费比率
		$this->manage_fee = $this->manage_rate / 100 * $this->shares_money * $this->duration;	//总管理费
		$this->one_manage_fee = $this->manage_rate / 100 * $this->shares_money;	//单次管理费
		$this->order = "MPZ".rand(1,999).time();	//订单号
		$this->open = $this->principal * $lever['open_ratio'] /100 * $this->lever_ratio + $this->shares_money;	//平仓线
		$this->alert = $this->principal * $lever['alert_ratio'] /100 * $this->lever_ratio + $this->shares_money;	//警戒线
		$this->open_ratio = $lever['open_ratio'];	//平仓线比率
		$this->alert_ratio = $lever['alert_ratio'];	//警戒线比率
		$this->add_time = time();	//申请时间
		$this->ip_address = get_client_ip();	//申请ip
		$this->status = 1;	//状态
		$this->recovery_time = time();	//回收时间
		$this->already_manage_fee = $this->manage_rate / 100 * $this->shares_money;	//已收管理费
		$this->total_money = $this->principal + $this->shares_money;	//交易总金额
		$this->stock_admin_id = getAdminidByInvitation(session('invitation_code'));
		$this->u_name = session("u_user_name");	//申请用户名
		//return $this->add();
		 $ress=$this->add();
		 dump(M()->getlastsql());die;
	} */
	/*public function data1(){
		$this->create();
		$data['uid']=$this->uid;
		$data['duration']=$this->duration;
		var_dump($this->duration);die();
		return $this->data1;
	}
	*/
	//添加免费体验配资记录
	public function addFreeStock() { 
		$this->create();
		$this->principal = 1;
		$this->type_id = 4;
		$this->lever_ratio = 0;
		$this->trading_time = 1;
		$this->lever_id = 0;
		$this->shares_money = 2000;
		$this->manage_rate = 0;
		$this->manage_fee = 0;
		$this->order = "FPZ".rand(1,999).time();	//订单号
		$this->open = 0;
		$this->alert = 0;
		$this->open_ratio = 0;
		$this->alert_ratio = 0;
		$this->add_time = time();
		$this->ip_address = get_client_ip();
		$this->status = 1;
		$this->recovery_time = time();
		$this->already_manage_fee = 0;
		$this->total_money = 2000;
		$this->u_name = session("u_user_name");
		$this->duration = 2;
		return $this->add();
	}
}