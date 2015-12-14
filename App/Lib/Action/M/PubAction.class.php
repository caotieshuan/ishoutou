<?php
    class PubAction extends Action
    {
        
         public function Verify()
         {
            // import("ORG.Util.Image");
            // Image::buildImageVerify(4, 5, 'png', 60, 34);   
            import("ORG.Util.Image");
            ob_end_clean();
            Image::buildImageVerify();
         }
        /**
         * 用户登录
         */
         public function login()
         {   

		 
        $loginconfig = FS("Webconfig/loginconfig");//判断快捷登录是否开启
		$this->assign("loginconfig",$loginconfig);
		$this->display();
             
         }
		 /*
		 用户登录执行
		 */
		 public function actlogin(){
	   
	  
		setcookie('LoginCookie','',time()-10*60,"/");
		//uc登录
		require_once "./config.inc.php";
		require "./uc_client/client.php";
		//uc登录
		//list($uid, $username, $password, $email) = uc_user_login(text($arr['username']), $arr['password']);
		
		if(false!==strpos($_POST['sUserName'],"@")){
			$data['user_email'] = text($_POST['sUserName']);
		}else {
			$data['user_name'] = text($_POST['sUserName']);
			$data['user_phone'] = text($_POST['sUserName']);
			$data['_logic'] = 'OR';
		}
		$localuser = M('members')->field('id,user_name,user_pass,is_ban')->where($data)->find();
		//var_dump($localuser);die;
		if($localuser) {
			list($uid, $username, $password, $email) = uc_user_login(text($localuser['user_name']), $_POST['sPassword']);
			//echo "1";die;
		}else {
			list($uid, $username, $password, $email) = uc_user_login(text($_POST['sUserName']), $_POST['sPassword']);
			//echo "0";die;
		}
		//var_dump($uid);die;
		if($localuser['is_ban']==1) ajaxmsg("您的帐户已被冻结，请联系客服处理！",0);
		if($uid > 0) {
			//echo "eurer";die;
			if(!$localuser) {
				
				//echo "54656";die;
				$regdata['txtUser'] = text($_POST['sUserName']);
				$regdata['txtPwd'] = text($_POST['sPassword']);
				$regdata['txtEmail'] = $email;
				$newuid = $this->ucreguser($regdata);
				//var_dump($newuid);die;
				 
				if(is_numeric($newuid) && $newuid > 0){
					//用户登录成功，设置 Cookie，加密直接用 uc_authcode 函数，用户使用自己的函数
					setcookie('LoginCookie', uc_authcode($uid."\t".$username, 'ENCODE'));
					//生成同步登录的代码
					$ucsynlogin = uc_user_synlogin($uid);
					
					//echo json_encode($ucsynlogin);exit;
					//$this->success("登录成功",);
					header('Location: http://www.baidu.com/');
				}else{
					//ajaxmsg($newuid,0);
					$this->error("登录失败");
				}
			} else {
				//echo "2";die;
				session('u_id',$localuser['id']);
				session('u_user_name',$localuser['user_name']);
				$ucsynlogin = uc_user_synlogin($uid);
				//echo json_encode($ucsynlogin);exit;
				header('Location: http://qfw.taoweikeji.com/M/Member/member');
			}
		} elseif($uid == -1) {
			//ajaxmsg("用户不存在,或被删除!",0);
			$this->error("用户不存在,或被删除!");
		} elseif($uid == -2) {
			//ajaxmsg("密码错误!",0);
			$this->error("密码错误");
		} else {
			//ajaxmsg("未知错误!",0);
			$this->error("未知错误!");
		}
		
		
		
		
}

private function ucreguser($reg){
		$data['user_name'] = text($reg['txtUser']);
		$data['user_pass'] = md5($reg['txtPwd']);
		$data['user_email'] = text($reg['txtEmail']);
		$count = M('members')->where("user_email = '{$data['user_email']}' OR user_name='{$data['user_name']}'")->count('id');
		if($count>0) return "登录失败,UC用户名冲突,用户名或者邮件已经有人使用";
		$data['reg_time'] = time();
		$data['reg_ip'] = get_client_ip();
		$data['last_log_time'] = time();
		$data['last_log_ip'] = get_client_ip();
	
		$newid = M('members')->add($data);
		
		if($newid){
			session('u_id',$newid);
			session('u_user_name',$data['user_name']);
			return $newid;
		}
		return "登录失败,UC用户名冲突";
	}
	
	
	
         /**
         * 注销用户
         */
         public function logout()
         {
            session(null);
            if(session('u_id')){
            	$this->ajaxReturn('', '退出失败，请稍后再试', 0);
            }
           	$this->ajaxReturn();
         } 
         
         /**
         * 用户注册
         * 
         */
	 public function regist(){
		$loginconfig = FS("Webconfig/loginconfig");//判断快捷登录是否开启
		$this->assign("loginconfig",$loginconfig);
		if($_GET['invite']){
			//$uidx = M('members')->getFieldByUserName(text($_GET['invite']),'id');
			//if($uidx>0) session("tmp_invite_user",$uidx);
			session("tmp_invite_user",$_GET['invite']);
		  }
		   $this->display();
       }
	   /**
         * 用户注册执行
         * 
         */
	   public function regaction(){
		   //var_dump($_POST);die;
		/*$data['user_email'] = session('email_temp');	
		$data['user_name'] = session('name_temp');
		$data['user_pass'] = session('pwd_temp');
		$data['no_user_pass'] = session('no_pwd_temp');
		$data['code'] = session('code_temp');
		if(session('temp_phone')){
		    $data['user_phone'] = session('temp_phone');
		}
		*/
		$data['user_email'] = $_POST['user_email'];	
		$data['user_name'] = $_POST['user_name'];
		//$data['user_pass'] = md5($_POST['no_user_pass']);
		$data['user_pass'] = $_POST['user_pass'];
		$data['code']=$_POST['code'];
		
		//var_dump($data);die;
		if (session('code_temp')==$data['code']) {
			//echo "1";die;
			
			/*$updata['phone_status'] = 1;
			//if (!session("temp_phone")) {
			if (!session("user_name")) {
				//echo "0";die;
				//ajaxmsg("验证失败", 0);
				return false;
			}
            $mid = $this->regaction();
			
			$newid = setMemberStatus($mid, 'phone', 1, 10, '手机');
			if ($newid) {
				//echo "1";die;
				//ajaxmsg();
				return true;
			} else{
				//echo "2";die;
				//ajaxmsg("验证失败", 0);
				return false;
			}
			*/
			//uc注册
		require_once "./config.inc.php";
		require "./uc_client/client.php";
		$uid = uc_user_register($data['user_name'], $data['user_pass'], $data['user_email']);
		if($uid <= 0) {
			if($uid == -1) {
				//ajaxmsg('用户名不合法',0);
				$this->error('用户名不合法');
			} elseif($uid == -2) {
				//ajaxmsg('包含要允许注册的词语',0);
				$this->error('包含要允许注册的词语');
			} elseif($uid == -3) {
				//ajaxmsg('用户名已经存在',0);
				$this->error('用户名已经存在');
			} elseif($uid == -4) {
				//ajaxmsg('Email 格式有误',0);
				$this->error('Email 格式有误');
			} elseif($uid == -5) {
				//ajaxmsg('Email 不允许注册',0);
				$this->error('Email 不允许注册');
			} elseif($uid == -6) {
				//ajaxmsg('该 Email 已经被注册',0);
				$this->error('该 Email 已经被注册');
			} else {
				//ajaxmsg('未定义',0);
				$this->error('未定义');
			}
		}
		
		//uc注册
		
		$data['reg_time'] = time();
		$data['reg_ip'] = get_client_ip();
		$data['last_log_time'] = time();
        $data['last_log_ip'] = get_client_ip();
        $data['user_pass'] = md5($data['user_pass']);
		//echo "aaaa";die();
		
		
		
	   //var_dump($data);die;
		$newid = M('members')->add($data);
		
		
		
		
		if($newid){
			//$updata['cell_phone'] = session("temp_phone");
			$updata['cell_phone'] = $_POST['user_name'];
			$b = M('member_info')->where("uid = {$newid}")->count('uid');
			if ($b == 1){
				M("member_info")->where("uid={$newid}")->save($updata);
			}else{
				$updata['uid'] = $newid;
				$updata['cell_phone'] = session("temp_phone");
				M('member_info')->add($updata);
			} 
			session('u_id',$newid);
			session('u_user_name',$data['user_name']);
			//return $newid;
			header('Location:http://qfw.taoweikeji.com/M/Member/member');
		  }
		
		} else {
			//echo "o";die;
			
			//echo "3";die;
			//ajaxmsg("验证校验码不对，请重新输入！", 2);
			//$this->error('验证校验码不对，请重新输入！','/pub/regaction');
			$this->error('验证校验码不对，请重新注册！','/pub/regist');
			//header('验证校验码不对，请重新注册！','Location:http://qfw.taoweikeji.com/M/Pub/regist');
			//$this->regaction();
			//return false;
		} 
		
		
		
		
		
	}
	
	public function sendphone() {
		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt = de_xie($smsTxt);
		$phone = text($_POST['cellphone']);
		$xuid = M('members') -> getFieldByUserPhone($phone, 'id');
		if ($xuid > 0 && $xuid <> $this -> uid) ajaxmsg("", 2);

		$code = rand_string_reg(6, 1, 2);
		$datag = get_global_setting();
		$is_manual = $datag['is_manual'];
		
		if ($is_manual == 0) { // 如果未开启后台人工手机验证，则由系统向会员自动发送手机验证码到会员手机，
			$res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array(session('u_user_name'), $code), $smsTxt['verify_phone']));
			
		} else { // 否则，则由后台管理员来手动审核手机验证
			$res = true;
			$phonestatus = M('members_status') -> getFieldByUid($this -> uid, 'phone_status');
			if ($phonestatus == 1) ajaxmsg("手机已经通过验证", 1);
			$updata['phone_status'] = 3; //待审核
			$updata1['user_phone'] = $phone;
			$a = M('members') -> where("id = {$this->uid}") -> count('id');
			if ($a == 1) $newid = M("members") -> where("id={$this->uid}") -> save($updata1);
			else {
				M('members') -> where("id={$this->uid}") -> setField('user_phone', $phone);
			} 

			$updata2['cell_phone'] = $phone;
			$b = M('member_info') -> where("uid = {$this->uid}") -> count('uid');
			if ($b == 1) $newid = M("member_info") -> where("uid={$this->uid}") -> save($updata2);
			else {
				$updata2['uid'] = $this -> uid;
				$updata2['cell_phone'] = $phone;
				M('member_info') -> add($updata2);
			} 
			$c = M('members_status') -> where("uid = {$this->uid}") -> count('uid');
			if ($c == 1) $newid = M("members_status") -> where("uid={$this->uid}") -> save($updata);
			else {
				$updata['uid'] = $this -> uid;
				$newid = M('members_status') -> add($updata);
			} 
			if ($newid) {
				ajaxmsg();
			} else ajaxmsg("验证失败", 0); 
			// ////////////////////////////////////////////////////////////
		} 
		
		if ($res) {
			session("temp_phone", $phone);
			ajaxmsg();
		} else ajaxmsg("", 0);
	}
	public function phone(){
		$this->assign("phone",$_GET['phone']);
		$data['content'] = $this->fetch();
		exit(json_encode($data));
		
	}
	
	/*public function validatephone() {
		if (session('code_temp')==text($_POST['code'])) {
			$updata['phone_status'] = 1;
			if (!session("temp_phone")) {
				//ajaxmsg("验证失败", 0);
				return false;
			}
            $mid = $this->regaction();
			
			$newid = setMemberStatus($mid, 'phone', 1, 10, '手机');
			if ($newid) {
				//ajaxmsg();
				return true;
			} else{
				//ajaxmsg("验证失败", 0);
				return false;
			}
		} else {
			$this->regaction();
			//ajaxmsg("验证校验码不对，请重新输入！", 2);
			return false;
		} 
	} 
	*/
	
	
	
	
	
         /*public function join()
         {
             if(session('u_id')){
                $this->redirect('M/user/index');   
             }
             if($this->isAjax()){
                 $email = $this->_post('email');
                 $username = $this->_post('username');
                 $password = $this->_post('password');
                 $verify = $this->_post('verify');
                 
                 if($_SESSION['verify'] != md5($verify)) {
                 	$this->ajaxReturn('', '验证码错误', 0);
                 }
                 
                 if(empty($password)){
                 	$this->ajaxReturn('', '密码不为空', 0);
                 }
                 
                 if($password != $this->_post('password2')){
                 	$this->ajaxReturn('', '两次密码输入不一至', 0);
                 }
                 
                 if(empty($email) || (!strpos($email, '@') && !strpos($email, '.'))){
                 	$this->ajaxReturn('', '邮箱地址不正确');
                 }
                 
                 $existName = M('members')->where("user_name='{$username}'")->count();
                 if($existName){
                     $this->ajaxReturn('', '用户名已被注册', 0);
                 }
                 $existMail = M('members')->where("user_email='{$email}'")->count();
                 if($existMail){
                 	$this->ajaxReturn('', '邮箱地址已被注册', 0);
                 }
                 
                 $data = array(
                        'user_name'=>$username,
                        'user_pass'=>md5($password),
                        'user_email'=>$email,
                        'reg_time'=>time(),
                        'reg_ip' => get_client_ip(),
                 );
                 if($newid = M("members")->add($data)){
                     session('u_id', $newid);
                     session('u_user_name', $username);
                     $this->ajaxReturn();
                 }else{
                     $this->ajaxReturn('', '注册失败', 0);
                 }
             }else{
                 
                 $this->display();
             }
         } 
      */		 
    }
?>
