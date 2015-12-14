<?php
class DaystockAction extends ACommonAction{
    public function index(){
		import("ORG.Util.Page");
		$map['type_id'] = 1;
		$map['status'] = 1;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->limit($Lsql)->order("id DESC")->select();
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->display();

    }
	
	public function transaction(){
		
		if($_REQUEST['uname']){
			
			$map['uid'] = M('members')->getFieldByuserName($_REQUEST['uname'],"id");
			
		}
		if($_REQUEST['start_time'] && $_REQUEST['end_time']){
			
			$map['add_time'] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
		}
		
		import("ORG.Util.Page");
		$map['s.type_id'] = 1;
		$map['s.status'] = array("in","2,6");
		$count = M("shares_apply s")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply s")->field("s.*,m.user_phone")->join("lzh_members m ON m.id = s.uid")->where($map)->limit($Lsql)->select();
		
		$this->assign("query", http_build_query($map));
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->display();

	}
	public function export(){
		import("ORG.Io.Excel");
		alogs("DaystockAction",0,1,'执行了所有天天盈交易中导出导出操作！');//管理员操作日志
		$map['s.type_id'] = 1;
		$map['s.status'] = array("in","2,6");
		$list = M("shares_apply s")->field("s.*,m.user_phone")->join("lzh_members m ON m.id = s.uid")->where($map)->select();
		
		$row = array();
		$row[0] = array("ID","用户名","本金","管理费","所获配资金额","订单号","平仓线","警戒线","添加时间","期限");
		$i = 1;
		foreach($list as $key=>$v){
			$row[$i]['id'] = $v['id'];
			$row[$i]['u_name'] = $v['u_name'];
			$row[$i]['principal'] = $v['principal'];
			$row[$i]['manage_fee'] = $v['manage_fee'];
			$row[$i]['shares_money'] = $v['shares_money'];
			$row[$i]['order'] = $v['order'];
			$row[$i]['open'] = $v['open'];
			$row[$i]['alert'] = $v['alert'];
			$row[$i]['add_time'] = date("Y-m-d",$v['add_time']);
			$row[$i]['duration'] = $v['duration'];
			$row[$i]['user_phone'] = $v['user_phone'];
			$i++;
			
		}
		
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("datalistcard");
	}

	public function closed(){
		import("ORG.Util.Page");
		$map['type_id'] = 1;
		$map['status'] = 3;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->limit($Lsql)->select();
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->display();
	}
	
	public function postexamine(){
		
		$id = $_POST['id'];
		$duration = $_POST['duration'];
		$this->assign('id',$id);
		$this->assign('duration',$duration);
	
		echo json_encode($this->fetch());
		//$this->display();
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
		$user_apply = M('shares_apply')->field("uid,principal")->where("id = {$id}")->find();
		$user = M('members m')->field(" m.id,m.user_email,m.user_phone,ms.email_status,ms.phone_status")->join(" lzh_members_status ms ON m.id=ms.uid ")->where(" m.id = '".$user_apply['uid']."' ")->find();
		$account_money = M('member_money')->getFieldByuid($user_apply['uid'],"account_money");
		$back_money = M('member_money')->getFieldByuid($user_apply['uid'],"back_money");
		
		if(($account_money + $back_money) < $user_apply['principal']){
			
			$this->error('申请人可用余额不足！');
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
				$applyfind = M('shares_apply')->where("id = {$id}")->find();
				$vo = examinembermoney($applyfind['uid'],$applyfind['principal'],$applyfind['manage_fee'],$applyfind['order'],$applyfind['id']);
				if($vo){
					alogs("examiney",0,1,'管理员执行了股票配资并填写homs信息操作成功！');
					$this->success('审核成功！');
				}
				
			}else{
				alogs("examiney",0,1,'管理员执行了股票配资并填写homs信息操作失败！');
				$this->error('审核失败！');
			}
			
			
		}elseif($_POST['examiney'] == 4){
			
			$apply = array();
			$apply['status'] = $examiney;
			$apply['examine_time'] = $the_current;
			$apply['info'] = $info;
			// $apply['endtime'] = strtotime(date("Y-m-d",strtotime("$date_the_current + $duration day")));
			$apply['endtime'] = getEndTime($duration,$the_current);
			$ret = M('shares_apply')->where("id = {$id}")->save($apply);
			if($ret){
				
				$info = '很遗憾，您在手投网申请股票配资未通过，原因为'.$info.'！【手投网】';
				sendsms($user['user_phone'],$info);
				
				alogs("examiney",0,1,'管理员执行了股票配资审核不通过操作成功！');
				$this->success('审核不通过操作成功！');
				
			}else{
				
				alogs("examiney",0,1,'管理员执行了股票配资审核不通过操作失败！');
				$this->success('审核不通过操作失败！');
			}
			
			
		}
		
		
	
		
		
		
	}
	public function postedit(){
		
		$id = $_POST['id'];
		
		$apply = M('shares_apply')->field("client_pass,client_user")->where("id = {$id}")->find();		
		$this->assign('apply',$apply);
		$this->assign('id',$id);
		echo json_encode($this->fetch());
	}
	
	public function doedit(){
		
		$id = $_POST['id'];
		
		$data = array();
		$data['client_user'] = $_POST['client_user'];
		$data['client_pass'] = $_POST['client_pass'];
		$ret = M('shares_apply')->where("id = {$id}")->save($data);
		if($ret){
			alogs("doedit",0,1,'管理员执行了股票配资修改homs信息操作成功！');
			$this->success('修改成功！');
		}else{
			
			alogs("doedit",0,1,'管理员执行了股票配资修改homs信息操作失败！');
			$this->error('修改失败！');
		}
	}
	
	public function openedit(){
		
		$id = $_POST['id'];
		$this->assign("id",$id);
		echo json_encode($this->fetch());
	} 
	
	public function opendoedit(){
		
		$id = $_POST['id'];
		$info = $_POST['info'];
		$uid = M('shares_apply')->getFieldByid($id,"uid");
		$user = M('members')->find($uid);
		$counttrader = $_POST['counttrader'];//HOMS总操盘金额
		if($counttrader == ''){
			
			$this->error("HOMS总操盘金额不能为空！");
		}
		if($info == ''){
			
			$this->error("审核说明不能为空！");
		}
		$ret = openedits($id,$counttrader);
		
		if($ret){
			$info = '您在手投网申请停止操盘审核通过！【手投网】';
			sendsms($user['user_phone'],$info);					
			alogs("opendoedit",0,1,'管理员执行了股票配资停止操盘操作成功！');
			$this->success('停止操盘成功！');
		}else{		
			$info = '您在手投网申请平仓审核未通过，原因为'.$info.'！【手投网】';
			sendsms($user['user_phone'],$info);
			alogs("opendoedit",0,1,'管理员执行了股票配资停止操盘操作失败！');
			$this->success('停止操盘失败！');
		}

		
	}
	public function notexamine(){
		import("ORG.Util.Page");
		$map['type_id'] = 1;
		$map['status'] = 4;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->limit($Lsql)->select();
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->display();
	}
	//追加 @Dong
	public function additional(){
		
		import("ORG.Util.Page");
		//$map['s.status'] = 1;
		//$map['l.status'] = array("neq","3");
		$map['s.type_id'] = 1;
		$map['s.is_additional'] = 1;
		$count = M("shares_additional s")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_additional s")->field("s.*")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->order("s.add_time DESC")->limit($Lsql)->select();
		 // dump(M('')->getlastsql());
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
	}
	public function addexamine() {
		$this->assign("id",$this->_post("id"));
		echo json_encode($this->fetch());
	}
	public function doaddexamine() {
		
		
		$shares_id = M('shares_additional')->getFieldByid($_POST['id'],"shares_id");
		$uid = M("shares_apply")->getFieldByid($shares_id,"uid");
		$user = M("members")->find($uid);
		
		if(!getWantApply($shares_id)){
			
			$this->error('该配资已经申请停止操盘，请优先处理停止操盘申请！');
		}
		$info = $_POST['info'];
		if($this->_post("status") == 2) {
			
			$id = $this->_post("id");
			$savedata['id'] = $this->_post("id");
			$savedata['status'] = $this->_post("status");
			$savedata['examine_time'] = time();
			$savedata['info'] = $info;
			$res = M("shares_additional")->save($savedata);
			
			if($res) {
				$ret = dayexaminemonthadd($id);
				if($ret){
					$info = '您在手投网申请追加实盘资金审核通过！【手投网】';
					sendsms($user['user_phone'],$info);
					alogs("addexamine",0,1,'管理员执行了股票配资追加申请审核操作成功！');
					$this->success("审核完成!");
				}
			} else {
				alogs("addexamine",0,1,'管理员执行了股票配资追加申请审核操作失败！');
				$this->error("审核操作失败,请重试!");
			}
		} elseif($this->_post("status") == 4) {
			$savedata['id'] = $this->_post("id");
			$savedata['status'] = $this->_post("status");
			$savedata['info'] = $info;
			$res = M("shares_additional")->save($savedata);
			if($res) {
				$info = '您在手投网申请追加实盘资金审核未通过,原因为'.$info.'【手投网】';
				sendsms($user['user_phone'],$info);
				$this->success("审核完成!");
			} else {
				$this->error("审核操作失败,请重试!");
			}
		} else {
			$this->error("非法请求！");
		}
	}
	public function reduce(){
		
		import("ORG.Util.Page");

		$count = M("shares_additional d")->field("d.*,l.principal as aprincipal,l.shares_money as ashares_money")->join("lzh_shares_apply l ON l.id = d.shares_id")->where("d.type_id and d.is_additional = 2")->count();
		
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_additional d")->field("d.*,l.principal as aprincipal,l.shares_money as ashares_money")->join("lzh_shares_apply l ON l.id = d.shares_id")->where("d.type_id and d.is_additional = 2")->order("d.add_time DESC")->limit($Lsql)->select();
		//dump($list);die;
		$this->assign("list",$list);
		$this->assign("pagebar",$page);

		$this->display();
	}
	
	public function reduceexamine() {
		$this->assign("id",$this->_post("id"));
		echo json_encode($this->fetch());
	}
	
	public function doreduceexamine() {
		$info = $_POST['info'];
		$shares_id = M('shares_additional')->getFieldByid($_POST['id'],"shares_id");
		$uid = M("shares_apply")->getFieldByid($shares_id,"uid");
		if(!getWantApply($shares_id)){
			
			$this->error('该配资已经申请停止操盘，请优先处理停止操盘申请！');
		}
		$user = M("members")->find($uid);
		if($this->_post("status") == 2) {
			
			$id = $this->_post("id");
			$savedata['id'] = $this->_post("id");
			$savedata['status'] = $this->_post("status");
			$savedata['examine_time'] = time();
			$savedata['info'] = $info;
			$res = M("shares_additional")->save($savedata);
			if($res) {
				$ret = reduce_thefirm($id);
				if($ret){
					$info = '您在手投网申请减少实盘资金审核通过！【手投网】';
					sendsms($user['user_phone'],$info);
					alogs("addexamine",0,1,'管理员执行了股票配资追加申请审核操作成功！');
					$this->success("审核完成!");
				}
			} else {
				alogs("addexamine",0,1,'管理员执行了股票配资追加申请审核操作失败！');
				$this->error("审核操作失败,请重试!");
			}
		} elseif($this->_post("status") == 4) {
			$savedata['id'] = $this->_post("id");
			$savedata['status'] = $this->_post("status");
			$savedata['info'] = $info;
			$res = M("shares_additional")->save($savedata);
			if($res) {
				$info = '您在手投网申请追加实盘资金审核未通过，原因为'.$info.'！【手投网】';
				sendsms($user['user_phone'],$info);
				$this->success("审核不通过操作成功！!");
			} else {
				$this->error("审核操作失败,请重试!");
			}
		} else {
			$this->error("非法请求！");
		}
	}
	
	public function extraction(){
		import("ORG.Util.Page");
		$map['status'] = 6;
		$map['type_id'] = 1;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->order("add_time DESC")->limit($Lsql)->select();
      
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
		
	}
	
	public function postextraction(){
		
		$this->assign("id",$_POST['id']);
		echo json_encode($this->fetch());
	}
	
	public function extrationdoedit(){
		
		$id = $_POST['id'];
		$info = $_POST['info'];
		$counttrader = $_POST['counttrader'];
		$total_money = M('shares_apply')->getFieldByid($id,"total_money");
		$uid = M('shares_apply')->getFieldByid($id,"uid");
		$order = M('shares_apply')->getFieldByid($id,"order");
		if(!getWantApply($id)){
			
			$this->error('该配资已经申请停止操盘，请优先处理停止操盘申请！');
		}
		$user = M('members')->find($uid);
		if($_POST['examiney'] == 2){
			if($counttrader < $total_money){
			
			$this->error('HOMS总操盘金额小于所获配资总金额，无盈利,建议审核不通过！');
			}else{
				
				$extration = $counttrader - $total_money;
				
				$user_money = M('member_money')->where("uid = {$uid}")->find();
				
				$savemoney = array();
				$savemoney['account_money'] = $user_money['account_money'] + $extration;
				
				$ret = M('member_money')->where("uid = {$uid}")->save($savemoney);
				$status = array();
				$status['status'] = 2;
				$tr = M('shares_apply')->where("id = {$id}")->save($status);
				if($ret && $tr){
					
					$info = $order."申请提取盈利成功，共".$extration.'元';
					if(pzmembermoneylod($extration,$uid,$info,$id)){
						$info = '您在手投网申请提取盈利审核通过！【手投网】';
						sendsms($user['user_phone'],$info);
						$this->success('处理成功！');
						alogs("extrationdoedit",0,1,'管理员执行了'.$order.'号订单盈利提取审核通过操作成功！');
					}else{
						
						$this->error('处理失败！');
						alogs("extrationdoedit",0,1,'管理员执行了'.$order.'号订单盈利提取审核通过操作失败！');
					}
				}else{
					
					$this->error('资金出错！');
				}
				
			}
			
		}elseif($_POST['examiney'] == 4){
			
				$status = array();
				$status['status'] = 2;
				$tr = M('shares_apply')->where("id = {$id}")->save($status);
				if($tr){

					if(innermsg($uid,'申请提取盈利',$order.'订单HOMS没有盈利！')){
						$info = '您在手投网申请提取盈利审核未通过，原因为'.$info.'！【手投网】';
						sendsms($user['user_phone'],$info);
						$this->success('处理成功！');
						alogs("extrationdoedit",0,1,'管理员执行了'.$order.'号订单盈利提取审核不通过操作成功！');
					}else{
						
						$this->error('处理失败！');
						alogs("extrationdoedit",0,1,'管理员执行了'.$order.'号订单盈利提取审核不通过操作失败！');
					}
				}else{
					
					$this->error('数据有误！');
				}
		}
		
		
		
	}
	//资金补充 @Dong
	public function supply() {
		
		import("ORG.Util.Page");
		//$map['s.status'] = 1;
		//$map['l.status'] = array("neq","3");
		$map['s.type_id'] = 1;
		$count = M("shares_supply s")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_supply s")->field("s.*")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->order("s.add_time DESC")->limit($Lsql)->select();
		//dump(M('')->getlastsql());
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
	}
	public function dosupply() {
		$this->assign("id",$this->_post("id"));
		echo json_encode($this->fetch());
	}
	public function supplyexamine() {
		
		$info = $_POST['info'];
		$uid = M('shares_supply')->getFieldByid($_POST,"uid");
		$user = M("members")->find($uid);
		$shares_id = M('shares_apply')->getFieldByid($this->_post("id"),'shares_id');
		if(!getWantApply($shares_id)){
			
			$this->error('该配资已经申请停止操盘，请优先处理停止操盘申请！');
		}
		
		if($this->_post("status") == 2) {
			$id = $this->_post("id");
			
			$savedata['id'] = $this->_post("id");
			$savedata['status'] = $this->_post("status");
			$savedata['examine_time'] = time();
			$res = M("shares_supply")->save($savedata);
			if($res) {
				$ret = daysupply($id);
				
				if($ret){
					$info = '您在手投网申请资金补充审核通过！【手投网】';
					sendsms($user['user_phone'],$info);
					alogs("supplyexamine",0,1,'管理员执行了股票配资补充实盘申请审核操作成功！');
					$this->success("审核完成!");
				}
			} else {
				alogs("supplyexamine",0,1,'管理员执行了股票配资补充实盘申请审核操作失败！');
				$this->error("审核操作失败,请重试!");
			}
		} elseif($this->_post("status") == 3) {
			$savedata['id'] = $this->_post("id");
			$savedata['status'] = $this->_post("status");
			$savedata['examine_time'] = time();
			$res = M("shares_supply")->save($savedata);
			if($res) {
				$info = '您在手投网申请资金补充审核未通过，原因为'.$info.'！【手投网】';
				sendsms($user['user_phone'],$info);
				$this->success("审核完成!");
			} else {
				$this->error("审核操作失败,请重试!");
			}
		} else {
			$this->error("非法请求！");
		}
	}
	public function opens(){
		import("ORG.Util.Page");
		$map['type_id'] = 1;
		$map['status'] = array("in","2,6");
		$map['is_want_open'] = 1;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->order("add_time DESC")->limit($Lsql)->select();
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->display();
		
	}

}
?>