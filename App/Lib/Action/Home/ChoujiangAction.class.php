<?php
// 抽奖使用
class ChoujiangAction extends HCommonAction {
	public function login(){
		$returnData = array();
		$isphone = false;
		if($this->isMobile($_GET['username'])){
			$isphone = true;
			$data['user_phone'] = text($_GET['username']);
			$data['user_pass'] = md5($_GET['password']);
		}else{
			$data['user_name'] = text($_GET['username']);
			$data['user_pass'] = md5($_GET['password']);
		}
		$vo = M('members')->field('id,user_name,user_email,user_pass,is_ban')->where($data)->find();

		if(empty($vo) && true === $isphone){
			unset($data['user_phone']);
			$data['user_name'] = text($_GET['username']);
			$vo = M('members')->field('id,user_name,user_email,user_pass,is_ban')->where($data)->find();
		}

		if(empty($vo)){
			$returnData['login'] = false;

		}else{
			$returnData['login'] = true;
			$uid = $vo['id'];
			$members_status = M('members_status')->field('id_status,phone_status,email_status')->where("uid ={$uid}")->find();
			if($members_status['id_status'] == 1){
            	$returnData['verify'] = true;
			}else{
				$returnData['verify'] = false;
			}
			$minfo =getMinfo($vo['id'],true);
			$money_collect = $minfo['money_collect'];
			$invest_money = $this->getInvestMoney($vo['id']);
			if(empty($money_collect)){
				$money_collect = "0.00";
			}
			$returnData['collection'] = $money_collect;
			$returnData['lend'] = $money_collect;
			echo json_encode($returnData);
		}
	}
	//验证是否是手机号
	public function isMobile($m){
		return preg_match("/^1[0-9]{10}$/",$m);
	}
	function getInvestMoney($uid){
		$pre = C('DB_PREFIX');
		$start = strtotime(date("Y-m-d").' 00:00:00');
		$end = strtotime(date("Y-m-d H:i:s "));
		$vm = M("borrow_investor m")->field('SUM(m.investor_capital) investmoney')->join("{$pre}borrow_info mm ON mm.id=m.borrow_id")->where("m.investor_uid={$uid} and m.add_time>{$start} and m.add_time<={$end} and mm.borrow_status = 6")->find();
		return $vm;
	}
}