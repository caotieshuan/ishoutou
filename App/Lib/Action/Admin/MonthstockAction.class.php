<?php
/*	股权配资月类控制器
 *	@author:Bob
 *	@time:2015/3/26
 */
class MonthstockAction extends ACommonAction{
	
	//平仓
	public function openedit(){
		$id = $_POST['id'];
		$this->assign("id",$id);
		echo json_encode($this->fetch());
	}
	
	//提取收益
	public function postextraction(){
		
		$this->assign("id",$_POST['id']);
		$this->display();
	}
	
	
	public function export(){
		import("ORG.Io.Excel");
		alogs("MonthstockAction",0,1,'执行了所有月月盈交易中导出导出操作！');//管理员操作日志
		$map['s.type_id'] = 2;
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
	
	
	//提取收益
	public function extrationdoedit(){
		$id = $_POST['id'];
		if(!getWantApply($id)){
			
			$this->error('该配资已经申请停止操盘，请优先处理停止操盘申请！');
		}
		$counttrader = $_POST['counttrader'];
		$total_money = M('shares_apply')->getFieldByid($id,"total_money");
		$uid = M('shares_apply')->getFieldByid($id,"uid");
		$order = M('shares_apply')->getFieldByid($id,"order");
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
				$status['info'] = $this->_post("info");
				$tr = M('shares_apply')->where("id = {$id}")->save($status);
				if($ret && $tr){
					
					$info = $order."申请提取盈利成功，共".$extration.'元';
					if(pzmembermoneylod($extration,$uid,$info,$id,51)){
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
				$status['info'] = $this->_post("info");
				$tr = M('shares_apply')->where("id = {$id}")->save($status);
				if($tr){
					$cont = $order."订单HOMS没有盈利";
					if(innermsg($uid,'申请提取盈利',$cont)){
						
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
	
	//提取收益
	public function extraction(){
		import("ORG.Util.Page");
		//$map['status'] = 6;
		$map['type_id'] = 2;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->limit($Lsql)->order('status desc')->select();
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
		
	}
	
	//提前平仓
	public function applyeven() {
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = 2;	//状态为什么为2 @author：yh @time:2015/5/16
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
		echo json_encode($this->fetch());
	}
	
	//提前平仓 
	public function doexapplyeven() {
		$id = $this->_post("id");
		$counttrader = $this->_post("counttrader");
		if($this->_post("status") == 0) {
			$ret = M("shares_apply")->where("id = {$id}")->setField("is_want_open",0);
			if($ret) {
				echo jsonmsg("处理完成",1);exit;
			}
		} elseif($this->_post("status") == 1) {
			$ret = doapplyeven($id,$counttrader);
			if($ret) {
				echo jsonmsg("处理完成",1);exit;
			}
		}
	}
	
	//补充实盘资金
	public function supplyexamine() {
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
				$ret = supplyexamine($id,$this->_post("info"));
				if($ret){
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
				$this->success("审核完成!");
			} else {
				$this->error("审核操作失败,请重试!");
			}
		} else {
			$this->error("非法请求！");
		}
	}
	
	//补充实盘资金
	public function dosupply() {
		$this->assign("id",$this->_post("id"));
		echo json_encode($this->fetch());
	}
	
	//补充实盘资金
	public function supply() {
		import("ORG.Util.Page");
		//$map['s.status'] = 1;
		$map['s.type_id'] = 2;
		$map['l.status'] = array("neq","3");
		$count = M("shares_supply s")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_supply s")->field("s.id,s.supply_money,s.order,s.add_time,s.u_name,s.status")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->limit($Lsql)->order('s.status asc,add_time desc')->select();
		//dump($list);die;
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
	}
	
	//追加保证金
	public function doaddexamine() {
		$shares_id = M('shares_apply')->getFieldByid($this->_post("id"),'shares_id');
		if(!getWantApply($shares_id)){
			
			$this->error('该配资已经申请停止操盘，请优先处理停止操盘申请！');
		}
		if($this->_post("status") == 2) {
			$id = $this->_post("id");
			$savedata['id'] = $this->_post("id");
			$savedata['status'] = $this->_post("status");
			$savedata['examine_time'] = time();
			$res = M("shares_additional")->save($savedata);
			if($res) {
				$ret = examinemonthadd($id,$this->_post("info"));
				if($ret){
					alogs("addexamine",0,1,'管理员执行了股票配资追加申请审核操作成功！');
					$this->success("审核完成!");
				}
			} else {
				alogs("addexamine",0,1,'管理员执行了股票配资追加申请审核操作失败！');
				$this->error("审核操作失败,请重试!");
			}
		} elseif($this->_post("status") == 3) {
			$savedata['id'] = $this->_post("id");
			$savedata['status'] = $this->_post("status");
			$savedata['examine_time'] = time();
			$res = M("shares_additional")->save($savedata);
			if($res) {
				$this->success("审核完成!");
			} else {
				$this->error("审核操作失败,请重试!");
			}
		} else {
			$this->error("非法请求！");
		}
	}
	
	//追加保证金
	public function addexamine() {
		$this->assign("id",$this->_post("id"));
		echo json_encode($this->fetch());
	}
	
	//追加保证金
	public function additional() {
		import("ORG.Util.Page");
		//$map['status'] = 1;
		$map['s.type_id'] = 2;
		$map['l.status'] = array("neq","3");
		$count = M("shares_additional s")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_additional s")->join("lzh_shares_apply l ON l.id = s.shares_id")->where($map)->field('s.*')->limit($Lsql)->select();
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
	}
	
	//平仓
	public function opendoedit(){
		$id = $this->_post('id');
		$counttrader = $this->_post('counttrader');//HOMS总操盘金额
		$ret = monthopen($id,$counttrader,$this->_post("info"));
		if($ret){				
			alogs("opendoedit",0,1,'管理员执行了股票配资平仓操作成功！');
			$this->success('平仓成功！');
		}else{			
			alogs("opendoedit",0,1,'管理员执行了股票配资平仓操作失败！');
			$this->error('平仓失败！');
		}
	}
	
	//审核
	public function postedit(){
		$id = $this->_post('id');
		$apply = M('shares_apply')->field("client_pass,client_user")->where("id = {$id}")->find();		
		$this->assign('apply',$apply);
		$this->assign('id',$id);
		echo json_encode($this->fetch());
	}
	
	//审核
	public function doedit(){
		$id = $this->_post('id');
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
	
	//审核配资申请页面渲染
	public function examine() {
		$this->assign("id",$this->_post("id"));
		echo json_encode($this->fetch());
	}
	
	//审核配资申请操作
	public function doexamine() {
		if($this->_post("status") == 2) {
			//发送客户端账号密码
			$id = $this->_post("id");
			//$uid = M('shares_apply')->where("id = {$id}")->getField('uid');
			$user_apply = M('shares_apply')->field("uid,principal")->where("id = {$id}")->find();
			$order = M("shares_apply")->where("id = {$id}")->getField("order");
			$user = M('members m')->field(" m.id,m.user_email,m.user_phone,ms.email_status,ms.phone_status ")->join(" lzh_members_status ms ON m.id=ms.uid ")->where(" m.id = {$user_apply['uid']}")->find();
			$account_money = M('member_money')->getFieldByuid($user_apply['uid'],"account_money");
			$back_money = M('member_money')->getFieldByuid($user_apply['uid'],"back_money");
			if(($account_money + $back_money) < $user_apply['principal']){
				
				$this->error('申请人可用余额不足！');
			}
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
					

						//推广奖励
						$recommend_id = M('members')->getFieldByid($uid,"recommend_id");
						if($recommend_id != 0){
							$manage_fee = M('shares_apply')->getFieldByorder($order,"manage_fee");
							promotion($uid,$recommend_id,$manage_fee,$order);	
						}
						$savedata['id'] = $this->_post("id");
						$savedata['client_user'] = $homsuser['homsuser'];
						$savedata['client_pass'] = $homsuser['homspass'];
						$savedata['status'] = $this->_post("status");
						$savedata['examine_time'] = time();
						$savedata['deduction_time'] = time();
						$savedata['info'] = $this->_post("info");
						$res = M("shares_apply")->save($savedata);
						if($res) {
							$apply = M('shares_apply')->where("id = {$savedata['id']}")->find();
							$rate = $apply['manage_fee'] / $apply['duration'];
							$ret = examinemonth($apply['uid'],$apply['principal'],$rate,$apply['order'],$apply['id']);
							if($ret){
								alogs("examiney",0,1,'管理员执行了股票配资并填写homs信息操作成功！');
								$this->success("审核完成!");
							}
						} else {
							alogs("examiney",0,1,'管理员执行了股票配资并填写homs信息操作失败！');
							$this->error("审核操作失败,请重试!");
						}
					}
					
					
				}else{
					
					$this->error("HOMS账号已经用完，请及时录入！");
				}
				
					
				
		} elseif($this->_post("status") == 4) {
			$savedata['id'] = $this->_post("id");
			$savedata['status'] = $this->_post("status");
			$savedata['examine_time'] = time();
			$res = M("shares_apply")->save($savedata);
			if($res) {
				$this->success("审核完成!");
			} else {
				$this->error("审核操作失败,请重试!");
			}
		} else {
			$this->error("非法请求！");
		}
	}
	
	//待审核列表
	public function waitexamine() {
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = 1;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->limit($Lsql)->select();
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
	}
	
	//交易中列表
	public function transaction() {
		if($_REQUEST['uname']){
			
			$map['uid'] = M('members')->getFieldByuserName($_REQUEST['uname'],"id");
			
		}
		if($_REQUEST['start_time'] && $_REQUEST['end_time']){
			
			$map['add_time'] = array(array('gt',strtotime($_REQUEST['start_time'])),array('lt',strtotime($_REQUEST['end_time'])));
		}
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = 2;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->limit($Lsql)->select();
		$benji = M("shares_apply")->where($map)->limit($Lsql)->sum('principal');
		$gulifei= M("shares_apply")->where($map)->limit($Lsql)->sum('manage_fee');
		$peizi= M("shares_apply")->where($map)->limit($Lsql)->sum('shares_money');
		//dump($benji);die;
		$this->assign("query", http_build_query($map));
		$this->assign("list",$list);
		$this->assign("benji",$benji);
		$this->assign("gulifei",$gulifei);
		$this->assign("peizi",$peizi);
		$this->assign("pagebar",$page);
		$this->display();
	}
	
	//已平仓列表
	public function alreadyopen() {
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = 3;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->limit($Lsql)->select();
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
	}
	
	//审核未通过列表
	public function examinenop() {
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = 4;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->limit($Lsql)->select();
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
	}
	
	//杠杆配置
    public function configindex(){	
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$count = M("shares_lever")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_lever")->where($map)->limit($Lsql)->select();
		$status = array("","开放","关闭","不开放");
		$this->assign("status",$status);
		$this->assign('list',$list);
		$this->assign('pagebar',$page);
        $this->display();
    }
	
	//利率配置
	public function configrate() {
		if($this->isPost()) {
			foreach($_POST['start'] as $k=>$v) {
				$savedata['start_month'] = $v;
				$savedata['end_month'] = $_POST['end'][$k];
				$savedata['rate_config'] = $_POST['rate'][$k];
				$savedatas[] = $savedata;
			}
			M("shares_rateconfig")->where(true)->delete();
			$res = M("shares_rateconfig")->addAll($savedatas);
			if($res) {
				$this->success("修改配置成功!");
			}else {
				$this->error("修改配置失败或未修改!");
			}
		} else {
			$data = M("shares_rateconfig")->field("start_month,end_month,rate_config")->select();
			$this->assign("list",$data);
			$this->display();
		}
		
	}
	
	//范围配置
	public function configmonth() {
		if($this->isPost()) {
			$month = array();
			$money = array();
			$month[] = $this->_post('frommonth');
			$month[] = $this->_post('tomonth');
			$money[] = $this->_post('frommoney');
			$money[] = $this->_post('tomoney');
			$data['term'] = implode("|",$month);
			$data['money'] = implode("|",$money);
			$res = M("shares_type")->data($data)->where("type = 2")->save();
			if($res) {
				$this->success("修改成功！");
			}else {
				$this->error("修改失败或者数据未改变！");
			}
		} else {
			$term = explode('|',D("shares_type")->getFieldByType("2","term"));
			$money = explode('|',D("shares_type")->getFieldByType("2","money"));
			$this->assign("frommonth",$term['0']);
			$this->assign("tomonth",$term['1']);
			$this->assign("frommoney",$money['0']);
			$this->assign("tomoney",$money['1']);
			$this->display();
		}
	}
	
	//新增杠杆
	public function addlever() {
		if($this->isPost()) {
			$model = D("shares_lever");
			$model->create();
			$model->type_id = 2;
			$res = $model->add();
			if($res) {
				$this->success("新增成功！",__URL__."/configindex");
			} else {
				$this->error("新增失败！");
			}
		} else {
			$this->assign("status",1);
			$this->display("editlever");
		}
	}
	
	//修改杠杆
	public function editlever() {
		if($this->isPost()) {
			$savedata['id'] = $this->_post('id');
			$savedata['lever_ratio'] = $this->_post('lever_ratio');
			$savedata['open_ratio'] = $this->_post('open_ratio');
			$savedata['alert_ratio'] = $this->_post('alert_ratio');
			$savedata['status'] = $this->_post('status');
			$res = M("shares_lever")->save($savedata);
			if($res) {
				$this->success("修改成功！");
			} else {
				$this->error("修改失败或者数据未改变！");
			}
		} else {
			if($this->_get('id')) {
				$data = M("shares_lever")->find($this->_get('id'));
				$this->assign($data);
				$this->display();
			}else {
				$this->error("非法访问");
			}
		}
	}
}
?>
