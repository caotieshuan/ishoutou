<?php
	class StoperationAction extends ACommonAction{
		public function index(){
			import("ORG.Util.Page");
			$count = M("shares_apply")->where("type_id=3 and status=1 or status=5")->count();
			$p = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			//获取操盘手未审核列表
			$res = get_trader_data("shares_apply","lzh_shares_apply.type_id=3 And lzh_shares_apply.status=1 OR lzh_shares_apply.status=5 ORDER BY lzh_shares_apply.status DESC",$Lsql);
			$this->assign('res',$res);
			$this->assign('page',$page);// 赋值分页输出
			$this->display();
		}
		public function dealing(){
			if($_REQUEST['uname']){
				$map['uid'] = M('members')->getFieldByuserName($_REQUEST['uname'],"id");
			}
			if($_REQUEST['start_time'] && $_REQUEST['end_time']){
			
				$map['add_time'] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
			}
			import("ORG.Util.Page");
			$map['type_id'] = 3;
			$map['status'] = 2;
			$count = M("shares_apply")->where($map)->count();
			$p = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$list = M("shares_apply")->where($map)->limit($Lsql)->select();
			//获取操盘手交易中列表
			//$res = get_trader_data("shares_apply","lzh_shares_apply.type_id=3 And lzh_shares_apply.status=2 ORDER BY  lzh_shares_apply.endtime ASC",$Lsql);
			foreach ($list as $key => $value) {
				$list[$key]['endtime'] = $value['add_time']+$value['duration']*24*3600;
			}
			//dump($list);
			$this->assign('page',$page);// 赋值分页输出
			$this->assign('res',$list);
			$this->display();
		}
		public function closehouse(){
			import("ORG.Util.Page");
			$map['type_id'] = 3;
			$map['status'] = 3;
			$count = M("shares_apply")->where($map)->count();
			$p = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			//获取操盘手以平仓列表
			$res = get_trader_data("shares_apply","lzh_shares_apply.type_id=3 And lzh_shares_apply.status=3",$Lsql);
			$this->assign('res',$res);
			$this->assign('page',$page);// 赋值分页输出
			$this->display();
		}
		public function postexamine(){
			/*$id = $_POST['id'];
			$duration = $_POST['duration'];
			$this->assign('id',$id);
			$this->assign('duration',$duration);
			echo json_encode($this->fetch());
			$this->assign('data',$_POST);
			$this->display();*/

			$id = $_POST['id'];
			$duration = $_POST['duration'];
			$this->assign('id',$id);
			$this->assign('duration',$duration);
			echo json_encode($this->fetch());
		}
		public function examiney(){
			
			$id = $_POST['id'];
			$duration = $_POST['duration'];
			$homsuser = $_POST['homsuser'];
			$homspassword = $_POST['homspassword'];
			$info = $_POST['info'];
			
			if(empty($_POST['examiney'])){
				
				$this->error('审核状态不可为空！');
				
			}else{
				
				$examiney = $_POST['examiney'];
			}
		
			if($_POST['examiney'] == 2){
				
				//发送客户端账号密码
				
					$homsuser = M('homsuser')->where("status = 0 and uid = 0")->order("id DESC")->find();
					
					if($homsuser){
						
						$info = '您在手投网股票配资平台申请配资成功，您的HOMS账号为：'.$homsuser['homsuser'].'，密码为：'.$homsuser['homspass'].'，请妥善保管，不要泄露他人！【手投网】';
						$ret = sendsms($user['user_phone'],$info);
						if($ret){
							
							$savedata = array();
							$savedata['status'] = 1;
							$savedata['uid'] = $user['id'];
							M('homsuser')->where("id = {$homsuser['id']}")->save($savedata);
						}
					}else{
						
						$this->error("HOMS账号已经用完，请及时录入！");
					}
				$the_current = time();
				$date_the_current = date("Y-m-d",$the_current);
				$apply = array();
				$apply['client_user'] = $homsuser['homsuser'];
				$apply['client_pass'] = $homsuser['homspass'];
				$apply['status'] = $examiney;
				$apply['examine_time'] = $the_current;
				$apply['info'] = $info;
				// $apply['endtime'] = strtotime(date("Y-m-d",strtotime("$date_the_current + $duration day")));
				$apply['endtime'] = getEndTime($duration,$the_current);
				$ret = M('shares_apply')->where("id = {$id}")->save($apply);
				if($ret){
					/* $applyfind = M('shares_apply')->where("id = {$id}")->find();
					$vo = examinembermoney($applyfind['uid'],$applyfind['principal'],$applyfind['manage_fee'],$applyfind['order'],$applyfind['id']);
					if($vo){ */
						alogs("examiney",0,1,'管理员执行了股票配资并填写homs信息操作成功！');
						$this->success('审核成功！');
					//}
					
				}else{
					alogs("examiney",0,1,'管理员执行了股票配资并填写homs信息操作失败！');
					$this->error('审核失败！');
				}
				
				
			}elseif($_POST['examiney'] == 4){		
				//审核不通过退回本金
				$user_apply = M('shares_apply')->field("uid,principal")->where("id = {$id}")->find();
				$uid = $user_apply['uid'];
				$principal = $user_apply['principal'];
				$back_money = M('member_money')->field('back_money')->where("uid=$uid")->find();
				$money['back_money'] = $back_money['back_money']+$principal;	//用户原有的余额+本金
				$updatem = M('member_money')->field('back_money')->where("uid=$uid")->save($money);
				if($updatem){
					$apply = array();
					$apply['status'] = $examiney;
					$apply['examine_time'] = $the_current;
					$apply['info'] = $info;
					// $apply['endtime'] = strtotime(date("Y-m-d",strtotime("$date_the_current + $duration day")));
					$apply['endtime'] = getEndTime($duration,$the_current);
					$ret = M('shares_apply')->where("id = {$id}")->save($apply);
					if($ret){
						$ainfo = $order.'管理员审核未通过，退还保证金';
						pzmembermoneylod($principal,$uid,$ainfo,$id,52);
						$info = '很遗憾，您在手投网申请股票配资未通过，原因为'.$info.'！【手投网】';
						sendsms($user['user_phone'],$info);
						
						alogs("examiney",0,1,'管理员执行了股票配资审核不通过操作成功！');
						$this->success('审核不通过操作成功！');
						
					}else{
						
						alogs("examiney",0,1,'管理员执行了股票配资审核不通过操作失败！');
						$this->error('审核不通过操作失败！');
					}
				}else{
					$this->error('审核不通过操作失败！');
				}

			}
		}
		public function sendaccount(){
			$this->assign('data',$_POST);
			$this->display();
		}
/*		public function sendAccPass(){
			if(isset($_POST)){
				//获取新的账号密码更新数据库
				$data = array();
				$data ['client_user']= $_POST['client_user'];		//获取用户客户端账号
				$data ['client_pass']= $_POST['client_pass'];		//获取用户客户端密码
				$data['status'] = 2;	//状态为2 为审核通过
				$id = $_POST['id'];	//获取订单id
				$result=M('shares_apply')->field('duration')->where("id=$id")->find();	//获取用户的使用期限
				$data['endtime'] = getEndTime($result['duration'],time());
				$data['examine_time'] = time();	//审核时间
				$upifro =  M("shares_apply")->where("id=$id")->save($data);	//更新用户订单信息
				$uid = $_POST['uid'];	//获取用户id
				$principal = $_POST['principal'];	//获取用户的本金
				

				
					//发送客户端账号密码
					$user_apply = M('shares_apply')->field("uid")->where("id = {$id}")->find();
					$user = M('members m')->field(" m.id,m.user_email,m.user_phone,ms.email_status,ms.phone_status ")->join(" lzh_members_status ms ON m.id=ms.uid ")->where(" m.id = '".$user_apply['uid']."' ")->find();
					
					$homsuser = M('homsuser')->where("status = 0 and uid = 0")->order("id DESC")->find();
								
					$info = '您在手投网股票配资平台申请配资成功，您的HOMS账号为：'.$homsuser['homsuser'].'，密码为：'.$homsuser['homspass'].'，请妥善保管，不要泄露他人！【手投网】';
					$ret = sendsms($user['user_phone'],$info);
					if($ret){
						
						$savedata = array();
						$savedata['status'] = 1;
						$savedata['uid'] = $user['id'];
						M('homsuser')->where("id = {$homsuser['id']}")->save($savedata);
						alogs("sendaccounts",0,1,'管理员执行了发送homs信息操作成功！');
						$this->success('发送成功');
					}else{
						alogs("sendaccounts",0,1,'管理员执行了发送homs信息操作成功！');
						$this->error('发送失败，请重试!');
						
					}
			}else{
				alogs("sendaccount",0,1,'管理员执行了发送homs信息操作失败！');
				$this->error('发送失败，请重试!');
			}
		}*/
		public function closedeal(){
			$this->assign('data',$_POST);
			$this->display();
		}

		public function closeAction(){
			$total_money = $_POST['total_monery'];	//用户总操盘金额
			$surplus_money = $_POST['surplus_money'];	//用户操盘后的剩余金额
			$principal = $_POST['principal'];	//用户的本金
			$id = $_POST['id'];	//订单id
			$uid = $_POST['uid'];	//用户id
			$result = $surplus_money-$total_monery;
			$data = array();
			//查询该用户的回款资金
			
			$ret = traderdoapplyeven($id,$surplus_money,$total_money,$uid,$principal);
			if($ret) {
				$this->success("处理完成!");
			} else {
				$this->error("处理失败!");
			}
			
		}
		public function additional() {
			import("ORG.Util.Page");
			//$map['status'] = 1;
			$map['type_id'] = 3;
			$count = M("shares_additional")->where($map)->count();
			$p = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$list = M("shares_additional")->where($map)->limit($Lsql)->order('status asc')->select();
			$this->assign("list",$list);
			$this->assign("pagebar",$page);
			$this->display();
		}
		public function doaddexamine() {
			$shares_id = M('shares_additional')->getFieldByid($_POST['id'],"shares_id");
			if(!getWantApply($shares_id)){
				
				$this->error('该配资已经申请停止操盘，请优先处理停止操盘申请！');
			}
			if($this->_post("status") == 2) {	//审核通过步骤
				$id = $this->_post("id");
				$savedata['id'] = $this->_post("id");
				$savedata['status'] = $this->_post("status");
				$savedata['examine_time'] = time();
				$res = M("shares_additional")->save($savedata);
				if($res) {
					$ret = examinetraderadd($id);
					if($ret){
						alogs("addexamine",0,1,'管理员执行了股票配资追加申请审核操作成功！');
						$this->success("审核完成!");
					}
				} else {
					alogs("addexamine",0,1,'管理员执行了股票配资追加申请审核操作失败！');
					$this->error("审核操作失败,请重试!");
				}
			} elseif($this->_post("status") == 4) {
				$id = $this->_post("id");
				$ret = checknopass($id);//退回本金
				if($ret){	//退回成功
					$savedata['id'] = $this->_post("id");
					$savedata['status'] = $this->_post("status");
					$res = M("shares_additional")->save($savedata);
					if($res) {
						$this->success("审核完成!");
					} else {
						$this->error("审核操作失败,请重试!");
					}
				}else{
					$this->error("审核操作失败,请重试!");
				}
				
			} else {
				$this->error("非法请求！");
			}
		}
	
		public function addexamine() {
			$this->assign("id",$this->_post("id"));
			$this->display();
		}
		public function supply() {
			import("ORG.Util.Page");
			//$map['s.status'] = 1;
			$map['s.type_id'] = 3;
			$map['l.status'] = array("neq","3");
			$count = M("shares_supply s")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->count();
			$p = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$list = M("shares_supply s")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->field("s.*")->limit($Lsql)->order('s.status asc')->select();
			$this->assign("list",$list);
			$this->assign("pagebar",$page);
			$this->display();
		}
		public function dosupply() {
			$this->assign("id",$this->_post("id"));
			$this->assign("shares_id",$this->_post('shares_id'));
			$this->display();
		}
		public function supplyexamine() {
			$shares_id = M('shares_additional')->getFieldByid($_POST['id'],"shares_id");
			$uid = M("shares_apply")->getFieldByid($shares_id,"uid");
			if(!getWantApply($shares_id)){
				
				$this->error('该配资已经申请停止操盘，请优先处理停止操盘申请！');
			}
			if($this->_post("status") == 2) {//审核通过
				$id = $this->_post("id");
				$savedata['id'] = $this->_post("id");
				$savedata['status'] = $this->_post("status");
				$savedata['examine_time'] = time();
				$res = M("shares_supply")->save($savedata);
				if($res) {
					$ret = tradesupplyexamine($id);
					if($ret){
						alogs("supplyexamine",0,1,'管理员执行了股票配资补充实盘申请审核操作成功！');
						$this->success("审核完成!");
					}
				} else {
					alogs("supplyexamine",0,1,'管理员执行了股票配资补充实盘申请审核操作失败！');
					$this->error("审核操作失败,请重试!");
				}
			} elseif($this->_post("status") == 3) {//审核未通过
				/**
					审核未通过返回给用户补充的实盘资金
				*/
				$id = $this->_post("id");
				$shares_id = $this->_post("shares_id");
				if(supplyenopass($id,$shares_id)){
					$savedata['id'] = $this->_post("id");
					$savedata['status'] = $this->_post("status");
					$savedata['examine_time'] = time();
					$res = M("shares_supply")->save($savedata);
					if($res) {
						$this->success("审核完成!");
					} else {
						$this->error("审核操作失败,请重试!");
					}
				}else{
					$this->error("审核操作失败,请重试!");
				}
				
			} else {
				$this->error("非法请求！");
			}
		}
		public function applyeven() {
			import("ORG.Util.Page");
			$map['type_id'] = 3;
			$map['status'] = 2;
			$map['is_want_open'] = 1;
			$count = M("shares_apply")->where($map)->count();
			$p = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$list = M("shares_apply")->where($map)->limit($Lsql)->select();
			$this->assign("list",$list);
			$this->assign("pagebar",$page);
			$this->display();
		}
		//提前平仓
		public function doapplyeven() {
			$this->assign("id",$this->_post("id"));
			$this->assign("status",$this->_post("status"));
			$this->assign("total_money",$this->_post("total_money"));
			$this->assign("uid",$this->_post("uid"));
			$this->assign('principal',$this->_post('principal'));
			$this->display();
		}
		//提前平仓
		public function doexapplyeven() {
			$id = $this->_post("id");
			$total_money = $this->_post('total_money');
			$counttrader = $this->_post("counttrader");
			$uid = $this->_post("uid");
			$principal = $this->_post('principal');
			if($this->_post("status") == 0) {	//平仓申请不通过
				$ret = M("shares_apply")->where("id = {$id}")->setField("is_want_open",0);
				if($ret) {	
					$this->success("处理完成!");
				}
			} elseif($this->_post("status") == 1) {
				$ret = traderdoapplyeven($id,$counttrader,$total_money,$uid,$principal);	//参数 配资id 剩余金额 用户操盘总金额  用户本金
				if($ret) {
					$this->success("处理完成!");
				} else {
					$this->error("处理失败!");
				}
			}
		}
		public function cutsupply(){
			import("ORG.Util.Page");
			$map['s.type_id'] = 3;
			//$map['s.status'] = 4;
			$map['l.status'] = array("neq","3");
			$count = M("shares_supply s")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->count();
			$p = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$list = M("shares_supply s")->join("lzh_shares_apply l ON l.id = s.shares_id")->field('s.*')->where($map)->limit($Lsql)->order('s.status asc')->select();
			$this->assign("list",$list);
			$this->assign("pagebar",$page);
			$this->display();
		}		
	          public	function cutdosupply(){
	          		$this->assign('id',$_POST['id']);
	          		$this->assign('money',$_POST['money']);
	          		$this->display();
	          }
	          public function cutsupplyexamine(){
				  $shares_id = M('shares_additional')->getFieldByid($_POST['id'],"shares_id");
		$uid = M("shares_apply")->getFieldByid($shares_id,"uid");
		if(!getWantApply($shares_id)){
			
			$this->error('该配资已经申请停止操盘，请优先处理停止操盘申请！');
		}
	          		if($this->_post("status") == 2) {//审核通过
				$id = $this->_post("id");
				$money = $this->_post('money');
				$savedata['id'] = $this->_post("id");
				$savedata['status'] = $this->_post("status");
				$savedata['examine_time'] = time();
				$res = M("shares_supply")->save($savedata);
				if($res) {
					$ret = tradecutexamine($id);
					if($ret){
						alogs("supplyexamine",0,1,'管理员执行了股票配资减少实盘申请审核操作成功！');
						$this->success("审核完成!");
					}
				} else {
					alogs("supplyexamine",0,1,'管理员执行了股票配资减少实盘申请审核操作失败！');
					$this->error("审核操作失败,请重试!");
				}
			} elseif($this->_post("status") == 3) {//审核未通过
				$id = $this->_post("id");
				if(supplyenopass($id)){
					$savedata['id'] = $this->_post("id");
					$savedata['status'] = $this->_post("status");
					$savedata['examine_time'] = time();
					$res = M("shares_supply")->save($savedata);
					if($res) {
						$this->success("审核完成!");
					} else {
						$this->error("审核操作失败,请重试!");
					}
				}else{
					$this->error("审核操作失败,请重试!");
				}
				
			} else {
				$this->error("非法请求！");
			}
	          }
	}
?>
