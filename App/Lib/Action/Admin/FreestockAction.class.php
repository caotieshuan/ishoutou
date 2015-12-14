<?php
class FreestockAction extends ACommonAction{
    public function index(){
		import("ORG.Util.Page");
		$map['type_id'] = 4;
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
		import("ORG.Util.Page");
		$map['type_id'] = 4;
		$map['status'] = 2;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply")->where($map)->limit($Lsql)->select();
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->display();

	}

	public function closed(){
		import("ORG.Util.Page");
		$map['type_id'] = 4;
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
		$this->display();
	}
	
	public function examiney(){
		
		$id = $_POST['id'];
		$duration = $_POST['duration'];
		$homsuser = $_POST['homsuser'];
		$homspassword = $_POST['homspassword'];
		
		if(empty($_POST['examiney'])){
			
			$this->error('审核状态不可为空！');
			
		}else{
			
			$examiney = $_POST['examiney'];
		}
		
		if($examiney == 4) {
			$savedata['status'] = $examiney;
			$falseret = M("shares_apply")->where("id = {$id}")->save($savedata);
			if($falseret) {
				alogs("examiney",0,1,'管理员执行了股票配资审核操作！');
				$this->success('审核成功！');
			}
		}else {
		
		//发送客户端账号密码
		$user_apply = M('shares_apply')->field("uid,principal")->where("id = {$id}")->find();
		$user = M('members m')->field(" m.id,m.user_email,m.user_phone,ms.email_status,ms.phone_status ")->join(" lzh_members_status ms ON m.id=ms.uid ")->where(" m.id = '".$user_apply['uid']."' ")->find();
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
					}
					
				}else{
					
					$this->error('HOMS账号不足！');
				}
				
				
		
			
			$the_current = time();
			$date_the_current = date("Y-m-d",$the_current);
			$apply = array();
			$apply['client_user'] = $homsuser['homsuser'];
			$apply['client_pass'] = $homsuser['homspass'];
			$apply['status'] = $examiney;
			$apply['examine_time'] = $the_current;
			$apply['endtime'] = strtotime(date("Y-m-d",strtotime("$date_the_current + 1 day")));
			$ret = M('shares_apply')->where("id = {$id}")->save($apply);
			if($ret){
				$applyfind = M('shares_apply')->where("id = {$id}")->find();
				$vo = Experience($applyfind['uid'],$applyfind['principal'],$applyfind['id']);
				if($vo){
					alogs("examiney",0,1,'管理员执行了股票配资并填写homs信息操作成功！');
					$this->success('审核成功！');
				}
				
			}else{
				alogs("examiney",0,1,'管理员执行了股票配资并填写homs信息操作失败！');
				$this->error('审核失败！');
			}
		}
		
		
		
	}
	public function postedit(){
		
		$id = $_POST['id'];
		
		$apply = M('shares_apply')->field("client_pass,client_user")->where("id = {$id}")->find();		
		$this->assign('apply',$apply);
		$this->assign('id',$id);
		$this->display();
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
		$this->display();
	}
	
	public function opendoedit(){
		
		$id = $_POST['id'];
		$counttrader = $_POST['counttrader'];//HOMS总操盘金额
		$ret = freeopen($id,$counttrader);
		
		if($ret){
								
			alogs("opendoedit",0,1,'管理员执行了股票配资平仓操作成功！');
			$this->success('平仓成功！');
		}else{			
			alogs("opendoedit",0,1,'管理员执行了股票配资平仓操作失败！');
			$this->error('平仓失败！');
		}

		
	}
	public function notexamine(){
		import("ORG.Util.Page");
		$map['type_id'] = 4;
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
	
	
}
?>