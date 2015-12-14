<?php
    /**
    * 手机版(wap)默认首页
    * @author  张继立  
    * @time 2014-02-24
    */
    class IndexAction extends HCommonAction
    {
		public function _initialize() {
		//赋值UID
 		if(session("u_user_name")){
			$this->uid = session("u_id");
		}
		$this->glob = get_global_setting();
		$this->assign("glo",$this->glob);
	    }
	
        public function index()
        {
			$this->display();
		}
		//下载软件
		public function download(){
			$this->display();
		}
		//免费体验
		public function payment(){
			$money = M('member_money')->where("uid = {$this->uid}")->find();
			$mo=$money['account_money'] + $money['back_money'];
			
		    $this->assign("money",$money);
		    $this->display();
			
		}
		
		//提交免费体验配资申请
	public function postdata(){
		$user_money = M("member_money")->where("uid = {$this->uid}")->find();
		//判断是否满足免费体验资格
		$quota_map['status'] = array("not in","1,4");
		$quota_map['uid'] = $this->uid;
		$quata_num = D("shares_apply")->where("(status not in(1,4) AND uid = {$this->uid}) OR (status = 1 AND type_id = 4 AND uid = {$this->uid})")->count();
		
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
		//dump($free_num);die;//(1)
		
		
		//判断是否满足免费体验名额
		if($free_num >= $this->glob['free_num']) {
			echo jsonmsg('今日免费体验名额已满,请明天再来！',0);exit;
		}
		//判断用户是否登录
		if(session('u_id')==null){
			echo jsonmsg('您还没有登录，请先登录！',2);exit;
		}
		
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
	
      
	}
?>