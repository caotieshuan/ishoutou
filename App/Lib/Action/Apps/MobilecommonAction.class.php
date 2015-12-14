<?php
class MobilecommonAction extends MMCommonAction {
    var $notneedlogin=true;


	public function login(){
		$name = $_POST['name'];
		$password = $_POST['password'];
		//$android['password']=$password;
		//$android['android']=$name;
        //$suoid = M("android")->add($android);
		$content = array();
		$content['name']= $name;
		$content['password']= $password;
		echo json_encode($content);
    }
	
	//登录
	/*public function actlogin(){
		
		$jsoncode = file_get_contents("php://input");
		
		$arr = array();
		$arr = json_decode($jsoncode,true);
		
		(false!==strpos($arr['sUserName'],"@"))?$data['user_email'] = text($arr['sUserName']):$data['user_name'] = text($arr['sUserName']);
		$vo = M('members')->field('id,user_name,user_email,user_pass,is_ban')->where($data)->find();
		
		if($vo['is_ban']==1) ajaxmsg("dd您的帐户已被冻结，请联系客服处理！",0);
		if(is_array($vo)){
				
			if($vo['user_pass'] == md5($arr['sPassword']) ){//本站登录成功
				
				$this->_memberlogin($vo['id']);
				//alogs("login",'','1',session("u_id")."登录成功");
				$arr2 = array();
	            $arr2['type'] = 'actlogin';
				$arr2['deal_user'] = $vo['user_name'];
	            $arr2['tstatus'] = '1';
				$arr2['deal_time'] = time();
	            $arr2['deal_info'] = $vo['user_name']."app登录成功";
	            $newid = M("auser_dologs")->add($arr2);
				
				$mess = array();
			    $mess['uid'] = intval(session("u_id"));
				$mess['username'] = $vo['user_name'];
				//$mess['phone'] = intval(session("u_user_phone"));
				$mess['head'] = get_avatar($mess['uid']);//头像
				$minfo = getMinfo($mess['uid'],true);
				$mess['credits'] = getLeveIco($minfo['credits'],3);//会员等级
				$membermoney = M("member_money")->field(true)->where("uid={$mess['uid']}")->find();
				if(is_array($membermoney)){
					$mess['mayuse'] = $membermoney['account_money']+$membermoney['back_money'];//可用	
			        $mess['freeze'] = $membermoney['money_freeze'];//冻结
			        $mess['collect'] = $membermoney['money_collect'];//代收
					$mess['total'] = $mess['mayuse']+$mess['freeze']+$mess['collect'];//总额
				}else{
				    $mess['total'] = 0;
			        $mess['mayuse'] = 0;
			        $mess['freeze'] = 0;
			        $mess['collect'] = 0;
				}
			    

			    $pre = C('DB_PREFIX');
				$vo = M("members m")->field("m.user_email,m.user_phone,m.id,m.user_leve,m.time_limit,m.pin_pass,s.id_status,s.phone_status,s.email_status,s.video_status,s.face_status")->join("{$pre}members_status s ON s.uid=m.id")->where("m.id={$mess['uid']}")->find();
				$str = "";
				if($vo['id_status']==1){
					$mess['id_status']=1;
					$vm = M("member_info")->field('idcard,real_name')->find($mess['uid']);
					$mess['real_name']=$vm['real_name'];
					$mess['idcard']=hidecard($vm['idcard'],1);
				}elseif($vo['id_status']==3){
					$mess['id_status']=2;
				}else{
					$mess['id_status']=0;
				} 
				if($vo['phone_status']==1) {
					$mess['phone_status']=1;
					$mess['phone']=hidecard($vo['user_phone'],2);
				}else{ 
					$mess['phone_status']=0;
				}
				if($vo['email_status']==1) {
					$mess['email_status']=1;
					$mess['email']=$vo['user_email'];
				}else{ 
					$mess['email_status']=0;
				}
				if(M('escrow_account')->where("uid={$this->uid}")->count('uid')){
		        	// ajaxmsg('您已经绑定了托管账户，无需重复绑定',0);
		        	$mess['escrow']=1;
		        }else{
		        	$mess['escrow']=0;
		        }
		        $user_qdd = M('escrow_account')->field("invest_auth,secondary_percent")->where("uid=".$this->uid)->find();
				if($user_qdd['invest_auth'] == 0 || $user_qdd['secondary_percent'] == 0)
				{
					$mess['escrow_auth']=0;
				}else{
					$mess['escrow_auth']=1;
				}
				$vobank = M("member_banks")->field(true)->where("uid = {$this->uid} and bank_num !=''")->find();
		        if(empty($vobank['bank_num'])){
		        	$mess['bank_status']=0;
		        }else{
		        	$mess['bank_status']=1;
		        }
		        $money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
				$mess['common_money']=$money_info['account_money'];
				$money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
				$mess['back_money']=$money_info['back_money'];
				ajaxmsg($mess);
			}else{//本站登录不成功
//			    $arr2 = array();
//	            $arr2['type'] = 'actlogin';
//				$arr2['deal_user'] = $vo['user_name'];
//	            $arr2['tstatus'] = '1';
//				$arr2['deal_time'] = time();
//	            $arr2['deal_info'] = $vo['user_name']."登录密码错误，登录失败_".$jsoncode;
//	            $newid = M("auser_dologs")->add($arr2);
				
				ajaxmsg("kk用户名或者密码错误！",0);
			}

		}else {

				ajaxmsg("kk用户名或者密码错误！",0);
		}
	}
	*/
	
	//登录
	public function actlogin(){
		$jsoncode = file_get_contents("php://input");
		
		$arr = array();
		$arr = json_decode($jsoncode,true);
		
		
		setcookie('LoginCookie','',time()-10*60,"/");
		//uc登录
		require_once "./config.inc.php";
		require "./uc_client/client.php";
		//uc登录
		
		
		list($uid, $username, $password, $email) = uc_user_login(text($arr['username']), $arr['password']);
		
		(false!==strpos($arr['username'],"@"))?$data['user_email'] = text($arr['username']):$data['user_name'] = text($arr['username']);
		$vo = M('members')->field('id,user_name,user_email,user_pass,is_ban')->where($data)->find();
		
		if($vo['is_ban']==1) ajaxmsg("您的帐户已被冻结，请联系客服处理！",0);
		if($uid > 0) {
			if(!$vo) {
				$regdata['txtUser'] = text($arr['username']);
				$regdata['txtPwd'] = text($arr['password']);
				$regdata['txtEmail'] = $email;
				$newuid = $this->ucreguser($regdata);
				 
				if(is_numeric($newuid) && $newuid > 0){
					//用户登录成功，设置 Cookie，加密直接用 uc_authcode 函数，用户使用自己的函数
					//setcookie('LoginCookie', uc_authcode($uid."\t".$username, 'ENCODE'));
					//生成同步登录的代码
					//$ucsynlogin = uc_user_synlogin($uid);
					//echo json_encode($ucsynlogin);exit;
					
					if(is_array($vo)){
				
			if($vo['user_pass'] == md5($arr['password']) ){//本站登录成功
				
				$this->_memberlogin($vo['id']);
				//alogs("login",'','1',session("u_id")."登录成功");
				$arr2 = array();
	            $arr2['type'] = 'actlogin';
				$arr2['deal_user'] = $vo['user_name'];
	            $arr2['tstatus'] = '1';
				$arr2['deal_time'] = time();
	            $arr2['deal_info'] = $vo['user_name']."app登录成功";
	            $newid = M("auser_dologs")->add($arr2);
				
				$mess = array();
			    $mess['uid'] = intval(session("u_id"));
				$mess['username'] = $vo['user_name'];
				//$mess['phone'] = intval(session("u_user_phone"));
				$mess['head'] = get_avatar($mess['uid']);//头像
				$minfo = getMinfo($mess['uid'],true);
				$mess['credits'] = getLeveIco($minfo['credits'],3);//会员等级
				$membermoney = M("member_money")->field(true)->where("uid={$mess['uid']}")->find();
				if(is_array($membermoney)){
					$mess['mayuse'] = $membermoney['account_money']+$membermoney['back_money'];//可用	
			        $mess['freeze'] = $membermoney['money_freeze'];//冻结
			        $mess['collect'] = $membermoney['money_collect'];//代收
					$mess['total'] = $mess['mayuse']+$mess['freeze']+$mess['collect'];//总额
				}else{
				    $mess['total'] = 0;
			        $mess['mayuse'] = 0;
			        $mess['freeze'] = 0;
			        $mess['collect'] = 0;
				}
			    

			    $pre = C('DB_PREFIX');
				$vo = M("members m")->field("m.user_email,m.user_phone,m.id,m.user_leve,m.time_limit,m.pin_pass,s.id_status,s.phone_status,s.email_status,s.video_status,s.face_status")->join("{$pre}members_status s ON s.uid=m.id")->where("m.id={$mess['uid']}")->find();
				$str = "";
				if($vo['id_status']==1){
					$mess['id_status']=1;
					$vm = M("member_info")->field('idcard,real_name')->find($mess['uid']);
					$mess['real_name']=$vm['real_name'];
					$mess['idcard']=hidecard($vm['idcard'],1);
				}elseif($vo['id_status']==3){
					$mess['id_status']=2;
				}else{
					$mess['id_status']=0;
				} 
				if($vo['phone_status']==1) {
					$mess['phone_status']=1;
					$mess['phone']=hidecard($vo['user_phone'],2);
				}else{ 
					$mess['phone_status']=0;
				}
				if($vo['email_status']==1) {
					$mess['email_status']=1;
					$mess['email']=$vo['user_email'];
				}else{ 
					$mess['email_status']=0;
				}
				/*if(M('escrow_account')->where("uid={$this->uid}")->count('uid')){
		        	// ajaxmsg('您已经绑定了托管账户，无需重复绑定',0);
		        	$mess['escrow']=1;
		        }else{
		        	$mess['escrow']=0;
		        }
		        $user_qdd = M('escrow_account')->field("invest_auth,secondary_percent")->where("uid=".$this->uid)->find();
				if($user_qdd['invest_auth'] == 0 || $user_qdd['secondary_percent'] == 0)
				{
					$mess['escrow_auth']=0;
				}else{
					$mess['escrow_auth']=1;
				}
				*/
				$vobank = M("member_banks")->field(true)->where("uid = {$this->uid} and bank_num !=''")->find();
		        if(empty($vobank['bank_num'])){
		        	$mess['bank_status']=0;
		        }else{
		        	$mess['bank_status']=1;
		        }
		        $money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
				$mess['common_money']=$money_info['account_money'];
				$money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
				$mess['back_money']=$money_info['back_money'];
				
			}else{//本站登录不成功
//			    
				
				ajaxmsg("kk用户名或者密码错误！",0);
			}

		}
				ajaxmsg($mess);	

				}else{
					ajaxmsg($newuid,0);
				}
			} else {
				session('u_id',$localuser['id']);
				session('u_user_name',$data['user_name']);
				$ucsynlogin = uc_user_synlogin($uid);
				
				if(is_array($vo)){
				
			if($vo['user_pass'] == md5($arr['password']) ){//本站登录成功
				
				$this->_memberlogin($vo['id']);
				//alogs("login",'','1',session("u_id")."登录成功");
				$arr2 = array();
	            $arr2['type'] = 'actlogin';
				$arr2['deal_user'] = $vo['user_name'];
	            $arr2['tstatus'] = '1';
				$arr2['deal_time'] = time();
	            $arr2['deal_info'] = $vo['user_name']."app登录成功";
	            $newid = M("auser_dologs")->add($arr2);
				
				$mess = array();
			    $mess['uid'] = intval(session("u_id"));
				$mess['username'] = $vo['user_name'];
				//$mess['phone'] = intval(session("u_user_phone"));
				$mess['head'] = get_avatar($mess['uid']);//头像
				$minfo = getMinfo($mess['uid'],true);
				$mess['credits'] = getLeveIco($minfo['credits'],3);//会员等级
				$membermoney = M("member_money")->field(true)->where("uid={$mess['uid']}")->find();
				if(is_array($membermoney)){
					$mess['mayuse'] = $membermoney['account_money']+$membermoney['back_money'];//可用	
			        $mess['freeze'] = $membermoney['money_freeze'];//冻结
			        $mess['collect'] = $membermoney['money_collect'];//代收
					$mess['total'] = $mess['mayuse']+$mess['freeze']+$mess['collect'];//总额
				}else{
				    $mess['total'] = 0;
			        $mess['mayuse'] = 0;
			        $mess['freeze'] = 0;
			        $mess['collect'] = 0;
				}
			    

			    $pre = C('DB_PREFIX');
				$vo = M("members m")->field("m.user_email,m.user_phone,m.id,m.user_leve,m.time_limit,m.pin_pass,s.id_status,s.phone_status,s.email_status,s.video_status,s.face_status")->join("{$pre}members_status s ON s.uid=m.id")->where("m.id={$mess['uid']}")->find();
				$str = "";
				if($vo['id_status']==1){
					$mess['id_status']=1;
					$vm = M("member_info")->field('idcard,real_name')->find($mess['uid']);
					$mess['real_name']=$vm['real_name'];
					$mess['idcard']=hidecard($vm['idcard'],1);
				}elseif($vo['id_status']==3){
					$mess['id_status']=2;
				}else{
					$mess['id_status']=0;
				} 
				if($vo['phone_status']==1) {
					$mess['phone_status']=1;
					$mess['phone']=hidecard($vo['user_phone'],2);
				}else{ 
					$mess['phone_status']=0;
				}
				if($vo['email_status']==1) {
					$mess['email_status']=1;
					$mess['email']=$vo['user_email'];
				}else{ 
					$mess['email_status']=0;
				}
				/*if(M('escrow_account')->where("uid={$this->uid}")->count('uid')){
		        	// ajaxmsg('您已经绑定了托管账户，无需重复绑定',0);
		        	$mess['escrow']=1;
		        }else{
		        	$mess['escrow']=0;
		        }
		        $user_qdd = M('escrow_account')->field("invest_auth,secondary_percent")->where("uid=".$this->uid)->find();
				if($user_qdd['invest_auth'] == 0 || $user_qdd['secondary_percent'] == 0)
				{
					$mess['escrow_auth']=0;
				}else{
					$mess['escrow_auth']=1;
				}
				*/
				$vobank = M("member_banks")->field(true)->where("uid = {$this->uid} and bank_num !=''")->find();
		        if(empty($vobank['bank_num'])){
		        	$mess['bank_status']=0;
		        }else{
		        	$mess['bank_status']=1;
		        }
		        $money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
				$mess['common_money']=$money_info['account_money']?$money_info['account_money']:0;
				$money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
				$mess['back_money']=$money_info['back_money']?$money_info['back_money']:0;
				
			}else{//本站登录不成功

				
				ajaxmsg("kk用户名或者密码错误！",0);
			}

		}
		
				
				
				
				ajaxmsg($mess);
				//echo json_encode($ucsynlogin);exit;
			}
		} elseif($uid == -1) {
			ajaxmsg("用户不存在,或被删除!",0);
		} elseif($uid == -2) {
			ajaxmsg("密码错误!",0);
		} else {
			ajaxmsg("未知错误!",0);
		}
		

		
		
		
		
		
		
		
}	
	//注册
	public function regaction(){
		
		$jsoncode = file_get_contents("php://input");
		
		$arr = array();
		
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)) {
		  ajaxmsg("提交信息不能为空",0);
		}
		if ($arr['user_name']==""||$arr['pwd']==""||/*$arr['tmp_phone']==""||*/$arr['email']=="") {
		  ajaxmsg("提交信息错误，请重试!",0);
		}
		/*if($arr['tmp_phone']!=session['temp_phone']){
			ajaxmsg("验证码不符，请重新发送",0);
		}*/
		require_once "./config.inc.php";
		require "./uc_client/client.php";
		$uid = uc_user_register($arr['user_name'], $arr['pwd'],$arr['email']);
		if($uid <= 0) {
			if($uid == -1) {
				ajaxmsg('用户名不合法',0);
			} elseif($uid == -2) {
				ajaxmsg('包含要允许注册的词语',0);
			} elseif($uid == -3) {
				ajaxmsg('用户名已经存在',0);
			} elseif($uid == -4) {
				ajaxmsg('Email 格式有误',0);
			} elseif($uid == -5) {
				ajaxmsg('Email 不允许注册',0);
			} elseif($uid == -6) {
				ajaxmsg('该 Email 已经被注册',0);
			} else {
				ajaxmsg('未定义',0);
			}
		}else{			
			$data['user_name'] = $arr['user_name'];
			$data['user_pass'] = $arr['pwd'];
			$data['user_phone'] = $arr['user_name'];
			$data['reg_time'] = time();
			$data['reg_ip'] = get_client_ip();
			$data['last_log_time'] = time();
			$data['last_log_ip'] = get_client_ip();
			
			if(session("tmp_invite_user")) {
				$data['recommend_id'] = session("tmp_invite_user");
			}else if(session('rec_temp')){
				$Rectemp = session('rec_temp');
				$Retemp1 = M('members')->field("id")->where("user_name = '{$Rectemp}'")->find();
				if($Retemp1['id']>0){
					$data['recommend_id'] = $Retemp1['id'];//推荐人为投资人
				}
			}
			//注册奖励
			$get_data = M('global')->field("text")->where("code = 'is_reward'")->find();
			$is_new = $get_data['text'];
			if($is_new == '1'){
				$data['is_new'] = 1;
			}
			$newid = M('members')->add($data);
			
			if($newid){
				$updata['cell_phone'] = session("temp_phone");
				$b = M('member_info')->where("uid = {$newid}")->count('uid');
				if ($b == 1){
					M("member_info")->where("uid={$newid}")->save($updata);
				}else{
					$updata['uid'] = $newid;
					$updata['cell_phone'] = session("temp_phone");
					if(M('member_info')->add($updata)){
						ajaxmsg('添加信息失败，请联系管理员',0);
					}
					$status['uid']=$newid;
					$status['email_status']=0;
					$status['phone_status']=1;
					if(M('member_status')->add($status)){
						ajaxmsg('添加状态失败，请联系管理员',0);
					}
				} 
				session('u_id',$newid);
				session('u_user_name',$data['user_name']);
				ajaxmsg('注册成功',1);
			}else{
				ajaxmsg('数据添加失败',0);
			}			

		}		
	}
	public function mactlogout(){
		$this->_memberloginout();
		ajaxmsg("注销成功");
		
	}
		//找回密码(相当于手机上的登录页面的忘记密码)
	public function dogetpass(){
		$jsoncode=file_get_contents("php://input");
		$arr=array();
		$arr=json_decode($jsoncode,ture);
		if (!is_array($arr)||empty($arr)||empty($arr['user'])) {
		   ajaxmsg("数据错误！",0);
		}
		(false!==strpos($arr['user'],"@"))?$data['user_email'] = text($arr['user']):$data['user_name'] = text($arr['user']);
		$vo = M('members')->field('id')->where($data)->find();
		if(is_array($vo)){
			$res = Notice(7,$vo['id']);
			if($res) ajaxmsg('发送成功');
			else ajaxmsg('发送失败',0);
		}else{
			ajaxmsg('发送失败',0);
		}
	}
	
	
}
