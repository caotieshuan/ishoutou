<?php
// 本类由系统自动生成，仅供测试用途
class BorrowdetailAction extends MCommonAction {

    public function index(){
		$this->assign("bid",intval($_GET['id']));
		$this->display();
    }

    public function borrowdetail(){
		$pre = C('DB_PREFIX');
		$borrow_id = intval($_GET['id']);
		$list = getBorrowInvest($borrow_id,$this->uid);
		
		$this->assign("list",$list);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
/***
    public function repayment(){
		$pre = C('DB_PREFIX');
		$borrow_id = intval($_POST['bid']);
		$sort_order = intval($_POST['sort_order']);
		$vo = M("borrow_info")->field('id')->where("id={$borrow_id} AND borrow_uid={$this->uid}")->find();
		$user = M("borrow_investor")->where("borrow_id = {$vo['id']}")->field("borrow_uid")->select();
	
		if(!is_array($vo)) ajaxmsg("数据有误",0);

		if(true===$res){
			foreach($user as $v) {
			$ret[] = $user['borrow_uid'];
		}
		$ret = implode(",",$ret);
		$phone = M("members")->where("id in({$ret})")->field("id,user_phone")->select();
		foreach($phone as $v) {
			$info = "您收到".$vo['id']."号标的还款";
			$ret = sendsms($v['user_phone'],$info);
		}
		
		$res = borrowRepayment($borrow_id,$sort_order);
		ajaxmsg();
		}
		elseif(!empty($res)) ajaxmsg($res,0);
		else ajaxmsg("还款失败，请重试或联系客服!",0);
    }
	**/
	 public function repayment(){
		$pre = C('DB_PREFIX');
		$borrow_id = intval($_POST['bid']);
		$sort_order = intval($_POST['sort_order']);
		$vo = M("borrow_info")->field('*')->where("id={$borrow_id} AND borrow_uid={$this->uid}")->find();

		/**
		 $capitallist=M('borrow_investor')->where("borrow_id={$borrow_id}")->Field('*')->select();

		 foreach($capitallist as $val){
		 	$repay_money = $val['investor_capital']+$val['investor_interest'];
			 $arr = M("members")->field("user_phone,user_name,$repay_money as repay_money")->where("id = {$val['investor_uid']}")->find();
			 if($arr['user_phone']){
				 $vusernames[] = $arr;
			 }
		 }
		*/
		 $borrowDetail = D('investor_detail');
		 $detailList = $borrowDetail->field('invest_id,investor_uid,capital,interest,interest_fee,borrow_id,total')->where("borrow_id={$borrow_id} AND sort_order={$sort_order}")->select();
		  foreach($detailList as $val){
		 	 $repay_money = $val['capital']+$val['interest'];
			 $arr = M("members")->field("user_phone,user_name,$repay_money as repay_money")->where("id = {$val['investor_uid']}")->find();
			 if($arr['user_phone']){
				 $vusernames[] = $arr;
			 }
		 }
		if(!is_array($vo)) ajaxmsg("数据有误",0);
		$res = borrowRepayment($borrow_id,$sort_order);
		//$this->display("Public:_footer");



		if(true===$res) {
			$smsTxt = (array)FS("Webconfig/smstxt");
			if(false === empty($smsTxt['payback']['enable'])){
				foreach($vusernames as $val){
					$txt = preg_replace(array("/#USERANEM#/","/#ID#/","/#ORDER#/","/#MONEY#/"),array($val['user_name'],$borrow_id,$sort_order,$val['repay_money']),$smsTxt['payback']['content']);
					sendsms($val['user_phone'],$txt);
				}
			}
			ajaxmsg();
		} elseif(!empty($res)) ajaxmsg($res,0);
		else ajaxmsg("还款失败，请重试或联系客服",0);
    }

}