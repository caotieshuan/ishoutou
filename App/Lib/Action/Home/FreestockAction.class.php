<?php
/*	股权配资免费体验控制器
 *	@author:Bob
 *	@time:2015/3/31
 */
class FreestockAction extends HCommonAction{
	/*
	public function _initialize() {
		//赋值UID
 		if(session("u_user_name")){
			$this->uid = session("u_id");
		}
		$this->glob = get_global_setting();
		$this->assign("glo",$this->glob);
	}
	
	*/
	
	//提交免费体验配资申请
	public function postdata(){
		
		$this->glob = get_global_setting();
		
		
		$user_money = M("member_money")->where("uid = {$this->uid}")->find();
		//判断是否满足免费体验资格
		$quota_map['status'] = array("not in","1,4");
		$quota_map['uid'] = $this->uid;
		$quata_num = D("shares_apply")->where("(status not in(1,4) AND uid = {$this->uid}) OR (status = 1 AND type_id = 4 AND uid = {$this->uid})")->count();
		//$quata_num = D("shares_apply")->where(" uid = {$this->uid}")->count();
		//dump($quata_num);die;
		if($quata_num != 0) {
			echo jsonmsg('很抱歉,您不具备免费体验配资资格！',0);exit;
		}
		
		//当天范围
		$today_start = strtotime(date("Y-m-d 00:00:00",time()));
		$today_end = strtotime(date("Y-m-d 23:59:59",time()));
		$free_map = array();
		$free_map['type_id'] = 4;
		$free_map['status'] = array("in","1,2,3");
		$free_map['add_time'] = array("between",array($today_start,$today_end));
		$free_num = D("shares_apply")->where($free_map)->count();
	
	
		
		//判断是否满足免费体验名额
		if($free_num >= $this->glob['free_num']) {
			echo jsonmsg('今日免费体验名额已满,请明天再来！',0);exit;
		}
		//判断用户是否登录
		if(session('u_id')==null){
			echo jsonmsg('您还没有登录，请先登录！',2);exit;
		}
		//判断是否实名认证
		/*$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		if($ids!=1){
			echo jsonmsg('您还未完成身份验证,请先进行实名认证！',2);exit;
		}*/
		//判断是否手机认证
		/*$phones = M('members_status')->getFieldByUid($this->uid,'phone_status');
		if($phones!=1){
			echo jsonmsg('您还未手机认证,请先进行手机认证！',3);exit;
		}*/
		$uid = $this->uid;

		$count = getMoneylimit($this->uid);
		$all_money = $count + 1;
		if($all_money > ($user_money['account_money'] + $user_money['back_money'])) {
			echo jsonmsg('您的可用余额不足以支付您所有的配资申请费用,请等待审核完成或进行充值！',4);exit;
		}
		
		//执行添加
		$_POST['uid'] = $this->uid;
		$ret = D("SharesApply")->addFreeStock();
		if($ret){
			echo jsonmsg('恭喜配资成功！',1);exit;
		}else{
			echo jsonmsg('恭喜配资失败！',0);exit;
		}
		
	}
	
	//确认配资页面渲染
	public function payment() {
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
	
	//配资页面渲染
	public function index(){
		$vo = M('9yue')->order('dateline desc')->find();

		if($vo){
			$vo['art_10'] = $this->fordd($vo['art_10']);
			$vo['art_3'] = $this->fordd($vo['art_3']);
		}
		$this->assign("list",$vo);
		$this->assign("uid",$this->uid);
		$this->display();
	}

	private function fordd($dd){
		$arr = explode("\r\n",$dd);
		foreach($arr as &$v){
			$v = explode('||',$v);
		}
		return ($arr);
	}
}