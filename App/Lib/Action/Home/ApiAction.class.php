<?php
// 本类由系统自动生成，仅供测试用途
class ApiAction extends HCommonAction {
	public function uc(){
		require C("APP_ROOT")."Lib/Uc/uc.php";
	}
	public function login(){
		$returnData = array(
			'login'  => false,'verify'  =>  false,'collection'=>0,'lend'=>0
			);
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
			 $pre = C('DB_PREFIX');
			$memberstatus = M("members m")->field("m.id,m.user_leve,m.time_limit,m.pin_pass,s.id_status,s.phone_status,s.email_status,s.video_status,s.face_status,m.user_phone")->join("{$pre}members_status s ON s.uid=m.id")->where("m.id={$uid}")->find();
			if(empty($memberstatus['user_phone'])||$memberstatus['id_status'] !=1){
            	$returnData['verify'] = false;
			}else{
				$returnData['verify'] = true;
			}
			$minfo =getMinfo($vo['id'],true);
			$money_collect = $minfo['money_collect'];
			$invest_money = $this->getInvestMoney($vo['id']);
			$invest_money = $invest_money['investmoney'];
			if(empty($money_collect)){
				$money_collect = "0.00";
			}
			if(empty($invest_money)){
				$invest_money="0.00";
			}
			$returnData['collection'] = $money_collect;
			$returnData['lend'] = $invest_money;
		}
		echo json_encode($returnData);
	}
	//验证是否是手机号
	public function isMobile($m){
		//return preg_match("/^1[0-9]{10}$/",$m);
		return false;
	}
	function getInvestMoney($uid){
		$pre = C('DB_PREFIX');
		$start = strtotime(date("Y-m-d").' 00:00:00');
		$end = strtotime(date("Y-m-d H:i:s "));
		$vm = M("borrow_investor m")->field('SUM(m.investor_capital) investmoney')->join("{$pre}borrow_info mm ON mm.id=m.borrow_id")->where("m.investor_uid={$uid} and m.add_time>{$start} and m.add_time<={$end} and mm.borrow_status = 6")->find();
		return $vm;
	}
	function isBirth(){
		$now = date("md");
		$pre = C('DB_PREFIX');
		$phone = M("member_info i")->field("i.cell_phone,m.user_name")->join("{$pre}members_status s ON s.uid=i.uid")->join("{$pre}members m ON m.id=s.uid")->where(" CONCAT(SUBSTR(i.idcard,11,4)) = {$now} AND s.id_status=1 ")->select();
		$file = fopen("/tmp/birthday.txt", "a");
		fwrite($file, '今天：'.$now."\r\n");
		$smsTxt = (array)FS("Webconfig/smstxt");
			if(false === empty($smsTxt['birthday']['enable'])){
				foreach($phone as $val){
					$txt = $smsTxt['birthday']['content'];
					sendsms($val['cell_phone'],$txt); 
					fwrite($file, date("Y-m-d H:i:s ").'：'.$val['user_name'].' '.$val['cell_phone'].' 发送：'.$txt."\r\n");

				}
			}
		fclose($file);
	}
}