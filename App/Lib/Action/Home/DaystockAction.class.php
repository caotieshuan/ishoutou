<?php
// 本类由系统自动生成，仅供测试用途
class DaystockAction extends HCommonAction {
	public function index(){

		$Match_small = 	M('shares_global')->field('text')->where("code = 'Match_small'")->find();


		//var_dump($Match_small);die;

		$Match_big = 	M('shares_global')->field('text')->where("code = 'Match_big'")->find();
		$lever = M('shares_global')->field('text,code')->where("times_type = 1")->order("order_sn asc")->select();
		//var_dump($lever);die;
		foreach($lever as $k=>$v) {
			$tmp = explode("|",$v['text']);
			$ret[$k]['times'] = $tmp[0];
			$ret[$k]['times_interest'] = $tmp[1];
			$ret[$k]['times_open'] = $tmp[2];
			$ret[$k]['times_alert'] = $tmp[3];
			$ret[$k]['type'] = $v['code'];
		}
		//dump($ret);die;
		
		$this->assign("list",$ret);

		//最小配资金额与最大配资金额渲染
		$this->assign('small',$Match_small['text']);
		$this->assign('big',$Match_big['text']);
		
		if($this->uid){
			
			$uid = $this->uid;
		}else{
			
			$uid = 88;
		}
		//获取当前时间
		$time = time();
		//获取当前的小时数
		$hour = date('H',$time);
		//获取星期中的第几天
		$whatday = date('w',$time);
		//当今天是周末的时候或者今天下午两点半 或者今天是节假日 只能选下个交易日
		$res = get_holiday_data('shares_holiday');
		if($res=='1' || $whatday==6 || $whatday ==0 || $hour >= 14){//如果返回1证明处在节假日之间
			$this->assign('holiday',1);
		}else{
			$this->assign('holiday',0);
		}
		
		//配资排行榜
		
		$shares_list = M('shares_apply a')->field("a.shares_money,a.add_time,a.duration,m.user_name")->join("lzh_members m ON m.id = a.uid")->where("a.type_id = 1 and a.status in(2,3,6)")->order("a.id DESC")->select();
	
		$shares_count = M('shares_apply a')->field("a.shares_money,a.add_time,a.duration,m.user_name")->join("lzh_members m ON m.id = a.uid")->where("a.type_id = 1 and a.status in(2,3,6)")->order("a.id DESC")->count();
		
		$this->assign("shares_list",$shares_list);
		$this->assign("count",$shares_count);
		$this->assign('uid',$uid);
		$this->display();
	}
	public function payment(){
		$money = M('member_money')->where("uid = {$this->uid}")->find();

		
		//获取当前时间
		$time = time();
		//获取当前的小时数
		$hour = date('H',$time);
		//获取星期中的第几天
		$whatday = date('w',$time);
		//当今天是周末的时候或者今天下午两点半 或者今天是节假日 只能选下个交易日
		$res = get_holiday_data('shares_holiday');
		
		if($res=='1' || $whatday==6 || $whatday ==0 || $hour >= 14){//如果返回1证明处在节假日之间
			
			$this->assign('holiday',1);
		}else{
			$this->assign('holiday',0);
		}

		$this->assign("money",$money);
		$this->display();
	}
	public function postdata(){
		
		
		$days = intval($_POST['days']);
		$stock_money = $_POST['stock_money'];
		$type = $_POST['type'];
		$istoday = $_POST['istoday'];
		if(!$istoday){
			
			echo jsonmsg('数据有误！',0);exit;
		}elseif(!$type){
			
			echo jsonmsg('数据有误！',0);exit;
		}elseif($days < 2 || $days > 30){
			
			echo jsonmsg('配资天数有误！',0);exit;
		}elseif($stock_money < 1000){
			
			echo jsonmsg('配资金额小于最小配资金额！',0);exit;
		}
		$uid = $this->uid;
		
		$glo = 	M('shares_global')->field('text')->where("code = "."'{$type}'")->find();
		$glos = explode('|',$glo['text']);
		$guarantee_money = $stock_money / $glos[0];//保证金
		$interest = $stock_money * ($glos[1] / 1000) * $days;//总利息
		$user_money = M('member_money')->where("uid = {$this->uid}")->find();
		
		
		$uid = $this->uid;

		$count = getMoneylimit($this->uid);
		$all_money = $count + $guarantee_money + $interest;
		if($all_money > ($user_money['account_money'] + $user_money['back_money'])) {
			echo jsonmsg('您的可用余额不足以支付您所有的配资申请费用,请等待审核完成或进行充值！',4);exit;
		}
		/*
		 //判断是否实名认证
		$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		if($ids!=1){
			echo jsonmsg('您还未完成身份验证,请先进行实名认证！',2);exit;
		} 
		//判断是否手机认证
		$phones = M('members_status')->getFieldByUid($this->uid,'phone_status');
		if($phones!=1){
			echo jsonmsg('您还未手机认证,请先进行手机认证！',3);exit;
		}
		*/
		
		$ret = stockmoney($days,$stock_money,$type,$istoday,$uid);
		if($ret){

			echo jsonmsg('恭喜配资成功！',1);

		}else{
			echo jsonmsg('Sorry,配资失败！',0);
		}
		
		//dump($daydata);die;
		
		
	}
	public function contract(){
		
		$this->display();
	}
}













