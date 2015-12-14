<?php
// 本类由系统自动生成，仅供测试用途
class CommonAction extends MCommonAction {
	var $notneedlogin=true;
    public function index(){
		$this->display();
    }
	
    public function login()
	{

		$loginconfig = FS("Webconfig/loginconfig");//判断快捷登录是否开启
		$this->assign("loginconfig",$loginconfig);
		$this->display();
    }

	public function redbag()
	{
		if(!$this->uid){
			redirect(__APP__."/member/");
		}
		$redbag = M('redbag')->order('id desc')->where('status=1')->find();//判断活动是否存在
		$id = $redbag['id'];
		if(!$id){
			redirect(__APP__."/member/");
		}
		$usered = M('redbag_list')->where('uid='.$this->uid.' and pid='.$id)->count();//判断是否领过红包
		//已经领取过了
		if($usered){
			redirect(__APP__."/member/");
		}
		$redinfo = M('redbag_list')->order('id asc')->where('uid=0 and pid='.$id)->find();//判断是否还有剩余红包
		if(!$redinfo){
			M('redbag')->where('id='.$id)->save(array('status'=>2));
			redirect(__APP__."/member/");
		}
		$this->display();
	}

	public function ajaxredbag(){
		if(!$this->uid){
			ajaxmsg('未登录不能领取',0);
		}else{
			 //认证 后才能投标
		       $pre = C('DB_PREFIX');
		        $memberstatus = M("members m")->field("m.id,m.user_leve,m.time_limit,m.pin_pass,s.id_status,s.phone_status,s.email_status,s.video_status,s.face_status,m.user_phone")->join("{$pre}members_status s ON s.uid=m.id")->where("m.id={$this->uid}")->find();
		        $rooturl=__ROOT__;
		        if ($memberstatus['id_status'] !=1&&empty($memberstatus['user_phone'])){
		        	ajaxmsg('进行手机、实名认证后才能领取!',2);exit;
		        }else if (empty($memberstatus['user_phone'])){
		            ajaxmsg('进行手机认证后才能领取！', 2);exit;
		        }else if ($memberstatus['id_status'] !=1){
		            ajaxmsg('进行实名认证后才能领取！', 3);exit;
		        }
			//$vphone = M('members')->field('user_phone')->find($this->uid);
			//if(empty($vphone['user_phone'])){
			//	ajaxmsg('进行手机认证后才能领取!',2);
			//}
		}
		$redbag = M('redbag')->order('id desc')->where('status=1')->find();//判断活动是否存在
		$id = $redbag['id'];
		if(!$id){
			ajaxmsg('活动不存在',0);
		}
		$usered = M('redbag_list')->where('uid='.$this->uid.' and pid='.$id)->count();//判断是否领过红包
		//已经领取过了
		if($usered){
			ajaxmsg('您已经领过了',0);
		}
		$redinfo = M('redbag_list')->order('id asc')->where('uid=0 and pid='.$id.' and status=1')->find();//判断是否还有剩余红包


		if($redbag['prize_num']){
			$num = M('redbag_list')->where(' pid='.$id.' and redtype=1')->count();
			if($redbag['prize_num'] > $num){
				if(rand(1,$redbag['bonus_count']) <= $redbag['prize_num']){
					$newid = memberMoneyLog($this->uid,49,$redbag['prize_max'],'领取wap注册红包'.$redbag['prize_max'].'元');
					if($newid){
						M('redbag_list')->add(
							array(
								'money'=>$redbag['prize_max'],
								'pid'=>$id,
								'status'=>2,
								'redtype'=>1,
								'addtime'=>time(),
								'usetime'=>time(),
								'uid'=>$this->uid
							)
						);
						ajaxmsg($redbag['prize_max']);
					}
				}
			}
		}
		if(!$redinfo){
			M('redbag')->where('id='.$id)->save(array('status'=>2));
			ajaxmsg('红包已被抢光',0);
		}
		$prize = floatval($redinfo['money']);
		$newid = memberMoneyLog($this->uid,49,$prize,'领取wap注册红包'.$prize.'元');
		if($newid){
			M('redbag_list')->where('id='.$redinfo['id'])->save(array('uid'=>$this->uid,'usetime'=>time(),'status'=>2));
			if(!M('redbag_list')->where('uid=0')->count()){
				M('redbag')->where('id='.$id)->save(array('status'=>2));
			}
			ajaxmsg($redinfo['money']);
		}else{
			ajaxmsg('领取失败，请刷新后重试');
		}
	}
	
    public function register(){
		$loginconfig = FS("Webconfig/loginconfig");//判断快捷登录是否开启
		$this->assign("loginconfig",$loginconfig);
		if($_GET['invite']){
			session("tmp_invite_user",$_GET['invite']);
			$Retemp1 = M('members')->field("id,user_name")->where("id = '{$_GET['invite']}'")->find();
			if($Retemp1['id']>0){
				$this->assign("inviteuser",$Retemp1);
			}else{
				$this->assign("inviteuser","");
			}
		}
		$tid = (int)$_GET['t'];
		if($tid) {
			session('promoteid',$tid);
			$_SERVER['REDIRECT_QUERY_STRING'] && session('promote_other',$_SERVER['REDIRECT_QUERY_STRING']);
		}
		$this->display();
    }
	public function actlogin($verify = true)
	{
		setcookie('LoginCookie','',time()-10*60,"/");
		//uc登录
		$loginconfig = FS("Webconfig/loginconfig"); 
		$uc_mcfg  = $loginconfig['uc'];
		if($uc_mcfg['enable']==1){
			require_once C('APP_ROOT')."Lib/Uc/config.inc.php";
			require C('APP_ROOT')."Lib/Uc/uc_client/client.php";
		}
		if(true == $verify){
			//uc登录
			if($_SESSION['verify'] != md5(strtolower($_POST['sVerCode'])))
			{
				ajaxmsg("验证码错误!",0);
			}
		}

		$isphone = false;
		if($this->isMobile($_POST['sUserName'])){
			$isphone = true;
			$data['user_phone'] = text($_POST['sUserName']);
			$data['user_pass'] = md5($_POST['sPassword']);
		}else{
			$data['user_name'] = text($_POST['sUserName']);
			$data['user_pass'] = md5($_POST['sPassword']);
		}
		$vo = M('members')->field('id,user_name,user_email,user_pass,is_ban')->where($data)->find();

		if(empty($vo) && true === $isphone){
			unset($data['user_phone']);
			$data['user_name'] = text($_POST['sUserName']);
			$vo = M('members')->field('id,user_name,user_email,user_pass,is_ban')->where($data)->find();
		}

		if($vo['is_ban']==1) ajaxmsg("您的帐户已被冻结，请联系客服处理！",0);

		if(empty($vo)){
			//本站登录不成功，偿试uc登录及注册本站
			ajaxmsg("用户名或者密码错误！",0);
		}else{
			if(true == $verify){
				$this->_memberlogin($vo['id'],'pc');
			}else{
				$this->_memberlogin($vo['id'],'wap');
			}
			
			ajaxmsg();
		}

	}

	public function modlogin(){
		$this->actlogin(false);
	}
	public function actlogout(){
		$this->_memberloginout();
		//uc登录
		$loginconfig = FS("Webconfig/loginconfig");
		$uc_mcfg  = $loginconfig['uc'];
		if($uc_mcfg['enable']==1){
			require_once C('APP_ROOT')."Lib/Uc/config.inc.php";
			require C('APP_ROOT')."Lib/Uc/uc_client/client.php";
			$logout = uc_user_synlogout();
		}
		//uc登录
		$this->assign("uclogout",de_xie($logout));
		$this->redirect('/index');
	}
	public function actlogoutback() {
		$this->success("注销成功",__APP__."/");
	}
	public function idCheck() {
		$this->uid = session('u_id');
		if(!$this->uid){
			return '请先登录';
		}
		// 开启错误提示
		ini_set('display_errors', 'on');
		error_reporting(E_ALL);
		$id5_config = FS("Webconfig/id5");
		if ($id5_config[enable] == 0) {
			return '实名验证授权没有开启！！！';
		}
		$txtUser = text($_POST['txtUser']);
		$txtCard = text($_POST['txtCard']);
		if (empty($txtUser) || empty($txtCard)) return '请填写真实姓名和身份证号码';
		$xuid = M('member_info')->getFieldByIdcard($txtCard,'uid');
		if($xuid>0 && $xuid!=$this->uid) return '此身份证号码已被占用，请确认！';
		import("ORG.Io.id5");
		$synPlatApi = new SynPlatAPI();
		try {
			$result = $synPlatApi->getData('1A020201',implode(',',array($txtUser,$txtCard)));
			$xml =	simplexml_load_string($result);
		} catch(Exception $e) {
			return $e -> getMessage();
		}
		if(0 <> (int)$xml->message->status){
			return (string)$xml->message->value;
		}else{
			if (false === empty($xml->policeCheckInfos->policeCheckInfo->compResult) && @(string)$xml->policeCheckInfos->policeCheckInfo->compResult == '一致') {
				$temp = M('members_status') -> where("uid={$this->uid}") -> find();
				if(is_array($temp)){
					$cid['id_status'] = 1;
					$status = M('members_status') -> where("uid={$this->uid}") -> save($cid);
				}else{
					$dt['uid'] = $this -> uid;
					$dt['id_status'] = 1;
					$status = M('members_status') -> add($dt);
				}

				$data = array();
				$data['real_name'] = $txtUser;
				$data['idcard'] = $txtCard;
				$data['up_time'] = time();
				$data['uid'] = $this->uid;

				if (M('member_info') -> where("uid = {$this->uid}") -> count('uid')) {
					M('member_info') -> where("uid = {$this->uid}") -> save($data);
				} else {
					$data['uid'] = $this -> uid;
					M('member_info') -> add($data);
				}
				unset($data['real_name']);
				if (M('name_apply') -> where("uid = {$this->uid}") -> count('uid')) {
					M('name_apply') -> where("uid ={$this->uid}") -> save($data);
				} else {
					M('name_apply') -> add($data);
				}

				if($status){
					$data2['status'] = 1;
					$data2['deal_info'] = '会员中心实名认证成功';
					$new = M("name_apply") -> where("uid={$this -> uid}") -> save($data2);
					if($new) return true;
				}else{
					$data2['status'] = 0;
					$data2['deal_info'] = '会员中心实名认证失败';
					M("name_apply") -> where("uid={$this -> uid}") -> save($data2);
					return '认证失败';
				}
			}else{
				return '认证失败，请检查姓名、身份证号码是否正确！';
			}
		}
	}


	public function regtemp2(){
		$phone = session('temp_phone');

		if(session('code_temp')!=text($_POST['txtsmsCode'])){
			ajaxmsg('手机验证码不正确',0);
		}

		$uid = (int)session('u_id');

		if($phone&&$uid){
			$data = array();
			$data['user_phone']=$phone;
			$result =  M('members')->where('id='.$uid)->save($data);
			if($result){
				$this->updateUserInfo($uid);
			}
		}
		$check = $this->idCheck();
		if(true !== $check){
			ajaxmsg($check,0);
		}else{
			ajaxmsg(array('msg'=>'验证成功','uid'=>session('u_id')));
		}
	}

	public function updateUserInfo($newid){
		if($newid){
			$userinfo = M('members')->find($newid);
			$phone = $userinfo['user_phone'];
			if(!$phone) return ;
			$temp = M('members_status') -> where("uid={$newid}") -> find();
			if(is_array($temp)){
				$cid['phone_status'] = 1;
				M('members_status') -> where("uid={$newid}") -> save($cid);
			}else{
				$dt['uid'] = $newid;
				$dt['phone_status'] = 1;
				M('members_status') -> add($dt);
			}
			$updata['cell_phone'] = $phone;
			$b = M('member_info')->where("uid = {$newid}")->count('uid');
			if ($b == 1){
				M("member_info")->where("uid = {$newid}")->save($updata);
			}else{
				$updata['uid'] = $newid;
				$updata['cell_phone'] = $phone;
				M('member_info')->add($updata);
			}
			return $newid;
		}
	}
	public function register3(){
		$this->display();
	}

	public function regtempWap(){
		$username = text($_POST['txtUser']);
	    session('name_temp',text($_POST['txtUser']));	//用户名
		session('pwd_temp',md5($_POST['txtPwd']));	//密码
		session('no_pwd_temp',$_POST['txtPwd']);
		//session('code_temp',$_POST['sVerCode']);
		session('rec_temp',text($_POST['txtRec']));//推荐人

		if(M('members')->where(array('user_name'=>array('like',$username)))->count()){
			ajaxmsg($username.'用户已存在',0);
		}

		$Rectemp = text($_POST['txtRec']);
		if(false === empty($Rectemp)){
			$Retemp1 = M('members')->field("id")->where("user_name = '{$Rectemp}'")->find();
			if($Retemp1['id']>0){
				$data['recommend_id'] = $Retemp1['id'];//推荐人为投资人
			}else{
				ajaxmsg($Rectemp.'推荐人不存在',0);
			}
		}
		$mid = $this->regaction();
		if($mid){
			$this->memberlog($mid,'wap');
			ajaxmsg(array('msg'=>'注册成功','uid'=>session('u_id')));
			
		}else{
			ajaxmsg('注册失败',0);
		}
	}
	public function regtemp(){
		//session('email_temp',text($_POST['txtEmail']));	//常用邮箱
		$username = text($_POST['txtUser']);
	    session('name_temp',text($_POST['txtUser']));	//用户名
		session('pwd_temp',md5($_POST['txtPwd']));	//密码
		session('no_pwd_temp',$_POST['txtPwd']);
		//session('code_temp',$_POST['sVerCode']);
		session('rec_temp',text($_POST['txtRec']));//推荐人

		if(M('members')->where(array('user_name'=>array('like',$username)))->count()){
			ajaxmsg($username.'用户已存在',0);
		}

		$Rectemp = text($_POST['txtRec']);
		if(false === empty($Rectemp)){
			$Retemp1 = M('members')->field("id")->where("user_name = '{$Rectemp}'")->find();
			if($Retemp1['id']>0){
				$data['recommend_id'] = $Retemp1['id'];//推荐人为投资人
			}else{
				ajaxmsg($Rectemp.'推荐人不存在',0);
			}
		}
		$mid = $this->regaction();
		//$newid = setMemberStatus($mid, 'phone', 1, 10, '手机');
		if($mid){
			$this->memberlog($mid,'pc');
			ajaxmsg(array('msg'=>'注册成功','uid'=>session('u_id')));
		}else{
			ajaxmsg('注册失败',0);
		}
	}
	public function regaction(){

		$data['user_name'] = session('name_temp');
		$data['user_pass'] = session('pwd_temp');
		$data['no_user_pass'] = session('no_pwd_temp');

		$promoteid = (int)$_REQUEST['promoteid'];
		$data['ent'] = true == ListMobile() ? 1 : 0;
		$data['reg_time'] = time();
		$data['reg_ip'] = get_client_ip();
		$data['last_log_time'] = time();
        $data['last_log_ip'] = get_client_ip();
		$data['tid'] = $promoteid;
		//$global = get_global_setting();
		//$data['reward_money'] = $global['reg_reward'];//新注册用户奖励

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

		$promote_other = session('promote_other');
		if($promoteid){
			M('promote')->where('id='.$promoteid)->setInc('nums');
			if($promote_other){
				M('promote_other')->add(array(
					'tid'=>$promoteid,
					'uid'=>$newid,
					'other'=>$promote_other,
					'dateline'=>time()
				));
			}
		}
		session('u_id',$newid);
		session('u_user_name',$data['user_name']);

		return $newid;
	}

	public function sendphone() {
		if(!$this->uid) return false;
		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt = de_xie($smsTxt);
		$phone = text($_POST['cellphone']);
		$xuid = M('members') -> getFieldByUserPhone($phone, 'id');
		if ($xuid > 0 && $xuid <> $this -> uid) ajaxmsg("", 2);

		$code = rand_string_reg(6, 1, 2);

		$res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array(session('u_user_name'), $code), $smsTxt['verify_phone']));

		if ($res) {
			session("temp_phone", $phone);
			ajaxmsg();
		} else {
			ajaxmsg("", 0);
		};
	}
public function sendphone_reg() {
		if(!$this->uid) return false;
		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt = de_xie($smsTxt);
		$phone = text($_POST['cellphone']);
		$imgcode = text($_POST['imgcode']);
		if(!empty($imgcode)){
			if($_SESSION['verify'] != md5(strtolower($imgcode)))
			{
				ajaxmsg("图片验证码错误!",0);
			}
		}else{
			ajaxmsg("请输入图片验证码!",0);
		}
		$xuid = M('members') -> getFieldByUserPhone($phone, 'id');
		if ($xuid > 0 && $xuid <> $this -> uid) ajaxmsg("", 2);

		$code = rand_string_reg(6, 1, 2);

		$res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array(session('u_user_name'), $code), $smsTxt['verify_phone']));

		if ($res) {
			session("temp_phone", $phone);
			ajaxmsg();
		} else {
			ajaxmsg("", 0);
		};
	}
	public function promotion()
	{

		$phone = text($_POST['phone']);

		if(empty($_POST)){
			exit;
		}

		if(M('members')->where(array('user_phone'=>$phone))->count())
		{
			ajaxmsg('手机号已存在',0);
		}

		if(M('members')->where(array('user_name'=>$phone))->count())
		{
			ajaxmsg('用户名已存在',0);
		}
		$Rectemp = text($_POST['txtRec']);
		if(false === empty($Rectemp)){
			$Retemp1 = M('members')->field("id")->where("user_name = '{$Rectemp}'")->find();
			if($Retemp1['id']>0){
				$data['recommend_id'] = $Retemp1['id'];//推荐人为投资人
			}else{
				ajaxmsg($Rectemp.'推荐人不存在',0);
			}
		}


		if(empty($phone) || empty($_POST['vercode'])){
			ajaxmsg('',0);
		}

		if(session('code_temp')!=text($_POST['vercode'])){
			ajaxmsg('手机验证码不正确',0);
		}

		$data['user_name'] = $phone;
		$data['user_pass'] = md5($_POST['txtPwd']);
		$data['no_user_pass'] = $_POST['txtPwd'];
		$data['user_phone']=$phone;
		$promoteid = (int)$_REQUEST['promoteid'];
		$data['ent'] = true == ListMobile() ? 1 : 0;
		$data['reg_time'] = time();
		$data['reg_ip'] = get_client_ip();
		$data['last_log_time'] = time();
		$data['last_log_ip'] = get_client_ip();
		$data['tid'] = $promoteid;

		$newid = M('members')->add($data);

		if(empty($newid)){
			//本站登录不成功，偿试uc登录及注册本站
			ajaxmsg("用户名或者密码错误！",0);
		}else{
			if($promoteid){
				M('promote')->where('id='.$promoteid)->setInc('nums');
			}
			$promote_other = session('promote_other');
			if($promoteid){
				M('promote')->where('id='.$promoteid)->setInc('nums');
				if($promote_other){
					M('promote_other')->add(array(
						'tid'=>$promoteid,
						'uid'=>$newid,
						'other'=>$promote_other,
						'dateline'=>time()
					));
				}
			}
			session('u_id',$newid);
			session('u_user_name',$data['user_name']);
			$this->updateUserInfo($newid);
			$this->_memberlogin($newid);
			ajaxmsg();
		}
	}

	/*public function sendcode() {
		$phone = text($_POST['cellphone']);
		if(!$this->isMobile($phone)){
			ajaxmsg('',0);
		}

		if(M('members')->where(array('user_phone'=>$phone))->count())
		{
			ajaxmsg('',2);
		}

		if(M('members')->where(array('user_name'=>$phone))->count())
		{
			ajaxmsg('用户名已存在',0);
		}

		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt = de_xie($smsTxt);
		$code = rand_string_reg(6, 1, 2);
		$res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array(session('u_user_name'), $code), $smsTxt['verify_phone']));

		if ($res) {
			if(M('sendcode')->where(array('phone'=>$phone))->count()){
				M('sendcode')->where(array('phone'=>$phone))->save(array(
					'code'=>$code,
					'dateline'=>time(),
					'clientip'=>get_client_ip()
				));
			}else{
				M('sendcode')->add(
					array(
						'phone'=>$phone,
						'code'=>$code,
						'dateline'=>time(),
						'clientip'=>get_client_ip()
					)
				);
			}
			session("temp_phone", $phone);
			ajaxmsg();
		} else {
			ajaxmsg("验证码发送失败！", 0);
		};
	}*/
	public function sendcodewap() {
		$phone = text($_POST['cellphone']);
		$imgcode = text($_POST['imgcode']);
		if(!empty($imgcode)){
			if($_SESSION['verify'] != md5(strtolower($imgcode)))
			{
				ajaxmsg("图片验证码错误!",0);
			}
		}else{
			ajaxmsg("请输入图片验证码!",0);
		}
		if(!$this->isMobile($phone)){
			ajaxmsg('',0);
		}

		if(M('members')->where(array('user_phone'=>$phone))->count())
		{
			ajaxmsg('',2);
		}

		if(M('members')->where(array('user_name'=>$phone))->count())
		{
			ajaxmsg('用户名已存在',0);
		}

		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt = de_xie($smsTxt);
		$code = rand_string_reg(6, 1, 2);
		$res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array(session('u_user_name'), $code), $smsTxt['verify_phone']));

		if ($res) {
			if(M('sendcode')->where(array('phone'=>$phone))->count()){
				M('sendcode')->where(array('phone'=>$phone))->save(array(
					'code'=>$code,
					'dateline'=>time(),
					'clientip'=>get_client_ip()
				));
			}else{
				M('sendcode')->add(
					array(
						'phone'=>$phone,
						'code'=>$code,
						'dateline'=>time(),
						'clientip'=>get_client_ip()
					)
				);
			}
			session("temp_phone", $phone);
			ajaxmsg();
		} else {
			ajaxmsg("验证码发送失败！", 0);
		};
	}
	public function validatephonev2(){
		if (session('code_temp')==text($_POST['code']) && text($_POST['code'])) {
			$vo = M('members')->field('user_phone')->where(array('user_name'=>$_SESSION['retrieve_name']))->find();
			if($vo['user_phone'] == $_SESSION['retrieve_phone']){
				session('vphone',true);
				ajaxmsg();
			}else{
				ajaxmsg("验证校验码不对，请重新输入！", 2);
			}
		}else{
			ajaxmsg("验证校验码不对，请重新输入！", 2);
		}
	}

	public function validatephonev(){
		if (session('code_temp')==text($_POST['code']) && text($_POST['code'])) {
			ajaxmsg();
		}else{
			ajaxmsg("验证校验码不对，请重新输入！", 2);
		}

	}

	public function verphonev(){
		$phone = session('temp_phone');
		if(!$this->uid){
				ajaxmsg('未登录',0);
			}
		if(session('code_temp')!=text($_POST['code'])){
			ajaxmsg('手机验证码不正确',0);
		}
		$uid = (int)session('u_id');
		if($phone&&$uid){
			$data = array();
			$data['user_phone']=$phone;
			$result =  M('members')->where('id='.$uid)->save($data);
			if($result){
				$this->updateUserInfo($uid);
			}
		}
		$check = $this->idCheck();
		if(true !== $check){
			ajaxmsg($check,0);
		}else{
					$redbag = M('redbag')->order('id desc')->where('status=1')->find();//判断活动是否存在
					$id = $redbag['id'];
					if(false === empty($id)){
						$usered = M('redbag_list')->where('uid='.$uid.' and pid='.$id)->count();//判断是否领过红包
						if(true === empty($usered)){
							$redinfo = M('redbag_list')->order('id asc')->where('uid=0 and pid='.$id.' and status=1')->find();//判断是否还有剩余红包
							if(false === empty($redinfo)){
								ajaxmsg('认证通过，领取红包',3);
							}else{
								ajaxmsg('红包已被抢光',0);
							}
						}else{
							ajaxmsg('已经领取过了',0);
						}
					}else{
						ajaxmsg('红包活动已过期',0);
					}
		}
	}
	public function validatephone() {
		if (session('code_temp')==text($_POST['code'])) {
			if (!session("temp_phone")) {
				ajaxmsg("验证失败", 0);
			}
			ajaxmsg();
		} else {
			$this->regaction();
			ajaxmsg("验证校验码不对，请重新输入！", 2);
		} 
	} 
	
	public function emailverify(){
		$code = text($_GET['vcode']);
		$uk = is_verify(0,$code,1,60*1000);
		if(false===$uk){
			$this->error("验证失败");
		}else{
			$this->assign("waitSecond",3);
            setMemberStatus($uk, 'email', 1, 9, '邮箱');  
			$this->success("验证成功",__APP__."/member");
		}
	}
	
	public function getpasswordverify(){
		$code = text($_GET['vcode']);
		$uk = is_verify(0,$code,7,60*1000);
		if(false===$uk){
			$this->error("验证失败");
		}else{
			session("temp_get_pass_uid",$uk);
			$this->display('getpass');
		}
	}
	
	public function setnewpass(){
		$d['content'] = $this->fetch();
		echo json_encode($d);
	}
	
	public function dosetnewpass(){
		$per = C('DB_PREFIX');
		$uid = session("temp_get_pass_uid");
		$oldpass = M("members")->getFieldById($uid,'user_pass');
		if($oldpass == md5($_POST['pass'])){
			$newid = true;
		}else{
			$newid = M()->execute("update {$per}members set `user_pass`='".md5($_POST['pass'])."' where id={$uid}");
		}
		
		if($newid){
			session("temp_get_pass_uid",NULL);
			ajaxmsg();
		}else{
			ajaxmsg('',0);
		}
	}
	
	
	public function ckuser(){
		$map['user_name'] = text($_POST['UserName']);
		$count = M('members')->where($map)->count('id');
        
		if ($count>0) {
			$json['status'] = 0;
			exit(json_encode($json));
        } else {
			$json['status'] = 1;
			exit(json_encode($json));
        }
	}
	
	public function ckemail(){
		$map['user_email'] = text($_POST['Email']);
		$count = M('members')->where($map)->count('id');
        
		if ($count>0) {
			$json['status'] = 0;
			exit(json_encode($json));
        } else {
			$json['status'] = 1;
			exit(json_encode($json));
        }
	}
	public function emailvsend(){
		session('email_temp',text($_POST['email']));
		$mid = $this->regaction();
				
		$status=Notice(8,$mid);
		if($status) ajaxmsg('邮件已发送，请注意查收！',1);
		else ajaxmsg('邮件发送失败,请重试！',0);
		
    }
	public function ckcode(){
		if($_SESSION['verify'] != md5(strtolower($_POST['sVerCode']))) {
			echo (0);
		 }else{
			echo (1);
        }
	}
	
	public function verify(){
		import("ORG.Util.Image");
		Image::buildImageVerify();
	}
	public function verifyToWap(){
		import("ORG.Util.Image");
		/*Image::buildImageVerify(4,5,'png',105,52);*/
		Image::buildImageVerify();
	}
	public function regsuccess(){
		$this->assign('userEmail',M('members')->getFieldById($this->uid,'user_email'));
		$d['content'] = $this->fetch();
		echo json_encode($d);
	}


	public function setpassword(){
		if(true === $_SESSION['vphone'] && $_SESSION['retrieve_name']){

			$password = $_POST['psw'];
			$password1 = $_POST['psw1'];

			if(empty($password)){
				ajaxmsg('请输入密码',0);
			}

			if($password != $password1){
				ajaxmsg('两次密码不一样',0);
			}

			$vo = M('members')->field('id,user_phone,user_pass')->where(array('user_name'=>$_SESSION['retrieve_name']))->find();

			if(!$vo){
				ajaxmsg('参数错误',0);
			}

			if($vo['user_phone'] != $_SESSION['retrieve_phone']){
				ajaxmsg('验证码不正确');
			}

			if(md5($password) == $vo['user_pass']){
				ajaxmsg('密码修改成功');
			}

			$newid = M('members')->where("id={$vo['id']}")->setField('user_pass',md5($password));
			if($newid){
				MTip('chk1',$vo['id']);
				ajaxmsg('密码修改成功');
			}else{
				ajaxmsg('修改失败',0);
			}
		}else{
			ajaxmsg('参数错误1',0);
		}
	}

	public function sendpswphone(){
		if($_SESSION['retrieve_name']){
			$vo = M('members')->field('user_name,user_phone')->where(array('user_name'=>$_SESSION['retrieve_name']))->find();
			if(!$vo['user_phone']){
				echo 'no';exit;
			}
			$code = rand_string_reg(6, 1, 2);
			$msg = session('retrieve_name').'您好，你正在使用手机修改密码，您的验证码是:'.$code;
			$res = sendsms($vo['user_phone'], $msg);
			if(true == $res){
				session('retrieve_phone',$vo['user_phone']);
				echo 1;exit;
			}else{
				exit('no');
			}
		}else{
			session('retrieve_name','');
			echo 'no';exit;
		}
	}
	public function getpassword(){
		$this->display();
	}
	public function getpassword2(){


		$vo = M('members')->field('id,user_phone,user_pass')->where(array('user_name'=>$_SESSION['retrieve_name']))->find();
		if(!$vo) exit;
		$this->assign("userphone",hidecard($vo['user_phone'],2));
		$this->display();
	}
	public function getpassword3(){
		$this->display();
	}

	public function dogetpass(){
		$data['user_name'] = text($_POST['u']);
		$v = text($_POST['v']);
		if(empty($v) && !ListMobile()){
			ajaxmsg('验证码不能为空！',2);
		}

		if($_SESSION['verify'] != md5(strtolower($v)) && !ListMobile()) {
			ajaxmsg("验证码错误!",2);
		}

		if(empty($data['user_name'])){
			ajaxmsg('用户名不能为空！',3);
		}

		$vo = M('members')->field('user_name,user_phone')->where($data)->find();
		if(is_array($vo)){
			if($vo['user_phone']){
				session('retrieve_name',$vo['user_name']);
			//	session('retrieve',$vo['user_phone']);
				ajaxmsg(hidecard($vo['user_phone'],2));
			}else{
				ajaxmsg('您的手机未通过认证，不能自动取回密码，请联系客服！',4);
			}
		}else{
			ajaxmsg('用户名不存在！',3);
		}
	}

	public function doverphone(){
		if(session('retrieve')){

		}
		ajaxmsg('参数错误',0);
	}

    public function register2(){
		if(!$this->uid) return false;
		$this->display();
	}

	public function regrealname(){
		$this->display();
	}

	public function submitrealname(){
		if(!$this->uid){
			ajaxmsg('未登录',0);
		}
		$check = $this->idCheck();
		if(true !== $check){
			//ajaxmsg($check,0);
			ajaxmsg(array('message'=>$check,'uid'=>session('u_id'),'status'=>0));
		}else{
			ajaxmsg(array('message'=>'验证成功','uid'=>session('u_id'),'status'=>1));
		}
	}

	public function phone(){
		$this->assign("phone",$_GET['phone']);
		$data['content'] = $this->fetch();
		exit(json_encode($data));
		
	}
	
	//跳过手机验证
	public function skipphone(){
		$this->regaction();
		ajaxmsg();
		
	}
	//推荐人检测
	public function ckInviteUser(){
		$map['user_name'] = text($_POST['InviteUserName']);
		$map2['user_name'] = text($_POST['InviteUserName']);
		$map2['u_group_id'] = 26;
		$count = M('members')->where($map)->count('id');
		$count2 = M('ausers')->where($map2)->count('id');
        
		if ($count==1 || $count2==1) {
			$json['status'] = 1;
			exit(json_encode($json));
        } else {
			$json['status'] = 0;
			exit(json_encode($json));
        }
	}

	//验证是否是手机号
	public function isMobile($m){
		return preg_match("/^1[0-9]{10}$/",$m);
	}
	public function memberlog($uid,$logintype){
				$up['uid'] = $uid;
				$up['add_time'] = time();
				$up['ip'] = get_client_ip();
				$up['logintype'] = $logintype;
				M('member_login')->add($up);
	}
}