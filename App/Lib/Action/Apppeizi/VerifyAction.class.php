<?php
class VerifyAction extends HCommonAction {
	//认证中心
	public function verify(){
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		//ajaxmsg($this->uid);
		//var_dump($a);die;
		if(!is_array($arr)||empty($arr)||$arr['uid']==""){
			ajaxmsg("数据有误",0);
		}
		if($arr['uid']!=$this->uid){
			ajaxmsg("您还未登陆，请先登陆！",0);
		}

		$userinfo=M("members")->field('user_name')->where("id={$this->uid}")->find();
		$mess = array();
	    $mess['uid'] = $this->uid;
		$mess['username'] = $userinfo['user_name'];
		$mess['head'] = get_avatar($mess['uid']);//头像
		$minfo = getMinfo($mess['uid'],true);
		$mess['credits'] = getLeveIco($minfo['credits'],3);//会员等级
		$membermoney = M("member_money")->field(true)->where("uid={$mess['uid']}")->find();
		if(is_array($membermoney)){
			$mess['mayuse'] = $membermoney['account_money']+$membermoney['back_money'];//可用	
	        $mess['freeze'] = $membermoney['money_freeze'];//冻结
	        $mess['collect'] = $membermoney['money_collect'];//代收
			$mess['total'] = $mess['mayuse']+$mess['freeze']+$mess['collect'];//总额
			$lixi=get_personal_benefit($this->uid);
			$mess['benefit']=$lixi['interest_collection'];
		}else{
		    $mess['total'] = 0;
	        $mess['mayuse'] = 0;
	        $mess['freeze'] = 0;
	        $mess['collect'] = 0;
	        $mess['benefit'] = 0;
		}
		$pre = C('DB_PREFIX');
		$vo = M("members m")->field("m.user_email,m.user_phone,m.id,m.user_leve,m.time_limit,s.id_status,s.phone_status,s.email_status,s.video_status,s.face_status")->join("{$pre}members_status s ON s.uid=m.id")->where("m.id={$this->uid}")->find();
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
			$mess['email']=$vo['user_email'];
		}
		// if(M('escrow_account')->where("uid={$this->uid}")->count('uid')){
  //       	// ajaxmsg('您已经绑定了托管账户，无需重复绑定',0);
  //       	$mess['escrow']=1;
  //       }else{
  //       	$mess['escrow']=0;
  //       }
		$minfo =getMinfo($this->uid,true);
		$user_name = session('u_user_name');
		//$this->assign("minfo",$minfo);
		$mess['credits'] = getLeveIco($minfo['credits'],3);
		$mess['credit_cuse'] = $minfo['credit_cuse'];
		$mess['borrow_vouch_cuse'] = $minfo['borrow_vouch_cuse'];
		if ($minfo['time_limit']>0){
		    $mess['time_limit'] = date('Y-m-d',$minfo['time_limit']);
		}else{
		    $mess['time_limit'] = "0";
		}
		if ($minfo['credit_cuse'] == null){
		    $mess['credit_cuse'] = 0;
		}
		if ($minfo['borrow_vouch_cuse'] == null){
		    $mess['borrow_vouch_cuse'] = 0;
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

        $kflist = get_admin_name();
		
		$kflist = $kflist[$minfo['customer_id']];
		if ($_kflist == null){
		    $mess['kflist'] = "暂未选择客服";
		}
		$list=array();
		$pre = C('DB_PREFIX');
		$rule = M('ausers u')->field('u.id,u.qq,u.phone')->join("{$pre}members m ON m.customer_id=u.id")->where("u.is_kf =1 and m.customer_id={$minfo['customer_id']}")->select();
		foreach($rule as $key=>$v){
			$list[$key]['qq']=$v['qq'];
			$list[$key]['phone']=$v['phone'];
		}
		if ($rule==0){
		    $mess['qq']='暂无QQ';
		    $mess['phone']='暂无客服电话';
		}
        $money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
		$mess['common_money']=empty($money_info['account_money'])?'0.00':$money_info['account_money'];
		$money_info = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
		$mess['back_money']=empty($money_info['back_money'])?'0.00':$money_info['back_money'];

		ajaxmsg($mess);
	}

	//二次认证
	public function two_verify(){
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if($arr['uid']!=$this->uid){
			ajaxmsg("数据有误！",0);
		}
		$pre = C('DB_PREFIX');
		$vo = M("members m")->field("m.user_email,m.user_phone,m.id,m.user_leve,m.time_limit,s.id_status,s.phone_status,s.email_status,s.video_status,s.face_status")->join("{$pre}members_status s ON s.uid=m.id")->where("m.id={$this->uid}")->find();
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
			$mess['email']=$vo['user_email'];
		}
		ajaxmsg($mess);

	}

	//交易记录
	// public function peizilist(){
	// 	$jsoncode = file_get_contents("php://input");
	// 	$arr = array();
	// 	$arr = json_decode($jsoncode,true);
	// 	if(!is_array($arr)||empty($arr)||$arr['uid']==""){
	// 		ajaxmsg("数据有误",0);
	// 	}
	// 	if($arr['uid']!=$this->uid){
	// 		ajaxmsg("您还未登陆，请先登陆",0);
	// 	}
	// 	$map=array();
	// 	$map['uid'] = $this->uid;
	// 	$Log_list = getMoneyLog($map,10);
	// 	$Log_lists['list']=$Log_list['list'];
	// 	ajaxmsg($Log_lists);
		
	// }
 
	//（作废）
	// public function email_status()
	// {
	// 	$jsoncode = file_get_contents("php://input");
	// 	$arr = array();
	// 	$arr = json_decode($jsoncode,true);
	// 	if($arr['uid']!=$this->uid){
	// 		ajaxmsg("数据有误！",0);
	// 	}
	// 	$email = M('members')->field('user_email')->find($this->uid);
	// 	$ids=M('members_status')->getFieldByUid($this->uid,'email_status');
	// 	if($ids==1)
	// 	{
	// 		$data['email_status']=1;
	// 		$data['email']=$email;
	// 	}else{
	// 		$data['email_status']=0;
	// 	}
	// 	ajaxmsg($data);

	// }
	//邮箱认证接口
	public function emailverify(){
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['email'])) {
			ajaxmsg("数据有误！",0);
		}
		if($arr['uid']!=$this->uid){
			ajaxmsg("数据有误！",0);
		}
		$map['user_email']=$arr['email'];
		$count = M('members')->where($map)->count('id');

		$email = M('members')->field('user_email')->find($this->uid);
		$ids=M('members_status')->getFieldByUid($this->uid,'email_status');
		if ($count>1) {
			ajaxmsg('此邮箱已被注册',0);
        }elseif($ids==1){
        	ajaxmsg('此邮箱已认证成功',0);
        } else {
        	$data['user_email'] =$arr['email'];
			$data['last_log_time']=time();
			$newid = M('members')->where("id = {$this->uid}")->save($data);//更改邮箱，重新激活
			if($newid){
				$status=Notice(8,$this->uid);
				if($status) ajaxmsg('邮件已发送，请登录邮箱认证！');
				else ajaxmsg('邮件发送失败,请检查邮箱格式，重新发送！',0);
			}else{
				 ajaxmsg('新邮件修改失败',0);
			}
        }
	}
	//(作废)？
	// public function phone_status(){
	// 	$jsoncode = file_get_contents("php://input");
	// 	$arr = array();
	// 	$arr = json_decode($jsoncode,true);
	// 	if($arr['uid']!=$this->uid){
	// 		ajaxmsg("数据有误！",0);
	// 	}
	// 	$isid = M('members_status')->getFieldByUid($this->uid,'phone_status');
	// 	$phone = M('members')->getFieldById($this->uid,'user_phone');
	// 	if($isid==1){
	// 	//验证成功
	// 		$data['phone_status']=1;
	// 		$data['phone']=hidecard($phone,2);
	// 	}elseif($isid==3){
	// 		$data['phone_status']=3;
	// 	}
	// 	ajaxmsg($data);
	// }


	//手机认证接口
	public function sendphone() {
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['phone'])) {
			ajaxmsg("数据有误！",0);
		}
		if($arr['uid']!=$this->uid){
			ajaxmsg("数据有误！",0);
		}
		$phone = text($arr['phone']);
		// $user_name=M('members')->field('user_name')->find($this->uid);
		// $username=$user_name['user_name'];
		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt = de_xie($smsTxt);
		
		$xuid = M('members') -> getFieldByUserPhone($phone, 'id');
		if ($xuid > 0 && $xuid <> $this ->uid) ajaxmsg("此手机号已被绑定", 0);		
		$code = rand_string($this->uid,6,1,2);
		$data['code']=$code;
		$data['send_time']=time();
		M('verify')->add($data);
		$datag = get_global_setting();
		$is_manual = $datag['is_manual'];
		
		if ($is_manual == 0) { // 如果未开启后台人工手机验证，则由系统向会员自动发送手机验证码到会员手机，
			
			$res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array($username, $code), $smsTxt['verify_phone']));
			
		} else { // 否则，则由后台管理员来手动审核手机验证
			
			$res = true;
			$phonestatus = M('members_status') -> getFieldByUid($this ->uid, 'phone_status');
			
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
				$updata2['uid'] = $this ->uid;
				$updata2['cell_phone'] = $phone;
				M('member_info') -> add($updata2);
			} 
			$c = M('members_status') -> where("uid = {$this->uid}") -> count('uid');
			
			if ($c == 1) $newid = M("members_status") -> where("uid={$this->uid}") -> save($updata);
			
			else {
				$updata['uid'] = $this ->uid;
				$newid = M('members_status') -> add($updata);				
			} 

			if ($newid) {
				ajaxmsg();
			} else ajaxmsg("验证失败", 0); 
			// ////////////////////////////////////////////////////////////
		} 
		
		if ($res) {
			session("temp_phone", $phone);
			ajaxmsg("发送成功");
		} else ajaxmsg("验证失败", 0);
	}

	//找回密码时发送验证码

	public function sendphones() {
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['phone'])) {
			ajaxmsg("数据有误mm！",0);
		}
		// if($arr['uid']!=$this->uid){
		// 	ajaxmsg("数据有误fff！",0);
		// }
		$phone = text($arr['phone']);
		// $user_name=M('members')->field('user_name')->find($this->uid);
		// $username=$user_name['user_name'];
		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt = de_xie($smsTxt);
		
		$xuid = M('members')->where("user_name={$arr['phone']}")->count('id');
		//var_dump($xuid);die;
		if (!$xuid /* && $xuid < $this ->uid*/) ajaxmsg("此手机号未注册", 0);		
		$code = rand_string($this->uid,6,1,2);

		$data['code']=$code;
		
		$data['send_time']=time();
		
		M('verify')->add($data);
		
		$datag = get_global_setting();
		$is_manual = $datag['is_manual'];
		
		if ($is_manual == 0) { // 如果未开启后台人工手机验证，则由系统向会员自动发送手机验证码到会员手机，
			
		$res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array($username, $code), $smsTxt['verify_phone']));
			
		} 
		if ($res) {
			session("temp_phone", $phone);
			ajaxmsg("发送成功");
		}
	}
	//手机验证码是否匹配正确接口
	public function truecode(){
		
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);

		if (!is_array($arr)||empty($arr)||empty($arr['code'])||empty($arr['phone'])) {
			ajaxmsg("数据有误！",0);
		}
		$data['code']=$arr['code'];

		// 判断手机是不是被用过
		// $count= M('members')->where("user_name = '{$arr['phone']}'")->count('id');
		// if($count>0) {
		// 	ajaxmsg("该号码应经注册" ,0);
		// }else{
		$codes=M('verify')->field('code')->where($data)->find();
			
			if (intval($arr['code'])!=intval($codes['code'])){
				ajaxmsg("验证失败",0);
			}else{
						ajaxmsg("验证成功",1);
						}
					}
				//}
		
	

	



	//注册后手机的验证验证码是否正确接口
	public function validatephone(){
	 	$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['code'])) {
			ajaxmsg("数据有误！",0);
		}
	 	if($arr['uid']!=$this->uid){
			ajaxmsg("数据有误！",0);
		}
		$phonestatus = M('members_status')->getFieldByUid($this->uid,'phone_status');
		if($phonestatus==1) ajaxmsg("手机已经通过验证",1);
		//echo "uid".$this->uid."code".$arr['code'];
		if( is_verify($this->uid,text($arr['code']),2,10*60) ){
			$updata['phone_status'] = 1;
			if(!session("temp_phone")) ajaxmsg("验证失败",0);
			
			$updata1['user_phone'] = session("temp_phone");
			$a = M('members')->where("id = {$this->uid}")->count('id');
			if($a==1) $newid = M("members")->where("id={$this->uid}")->save($updata1);
			else{
				M('members')->where("id={$this->uid}")->setField('user_phone',session("temp_phone"));
			}
			
			$updata2['cell_phone'] = session("temp_phone");
			$b = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if($b==1) $newid = M("member_info")->where("uid={$this->uid}")->save($updata2);
			else{
				$updata2['uid'] = $this->uid;
				$updata2['cell_phone'] = session("temp_phone");
				M('member_info')->add($updata2);
			}
			$c = M('members_status')->where("uid = {$this->uid}")->count('uid');
			if($c==1) $newid = M("members_status")->where("uid={$this->uid}")->save($updata);
			else{
				$updata['uid'] = $this->uid;
				$newid = M('members_status')->add($updata);
			}
			if($newid){
				$newid = setMemberStatus($this->uid, 'phone', 1, 10, '手机');
				ajaxmsg("验证成功");
				
			}
			else  ajaxmsg("验证失败",0);
		}else{
			ajaxmsg("验证校验码不对，请重新输入！",0);
		}
    }


	//作废？
	// public function id_status()
	// {

	// 	$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
	// 	if($ids==1)
	// 	{
	// 		$data['id_status']=1;
	// 		$vo = M("member_info")->field('idcard,real_name')->find($this->uid);
	// 		$data['real_name']=$vo['real_name'];
	// 		$data['idcard']=hidecard($vo['idcard'],1);
	// 	}elseif ($ids==3) {
	// 		$data['id_status']=2;
	// 	}else{
	// 		$data['id_status']=0;
	// 	}
	// 	ajaxmsg($data);
	// }
	//上传身份证正面接口
	public function ajaxupimg1(){
		// if($this->uid!=$_POST['uid'])
		// {
		// 	ajaxmsg('数据有误！',0);
		// }
		if(!empty($_FILES['imgfile1']['name'])){
			import('ORG.Net.UploadFile');
			
			$upload = new UploadFile();// 实例化上传类
			$upload->maxSize  = 3145728 ;// 设置附件上传大小
			$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型

			$upload->savePath = './UF/Uploads/Idcard/';// 设置附件上传目录
			chmod($upload->savePath,0777);
			$upload->saveRule = date("YmdHis",time()).rand(0,1000)."_{$this->uid}";
			foreach ($_FILES as $key=>$file){
			    if(!empty($file['name'])) {
			        $upload->autoSub = true;
			        $upload->subType   =  'date';
			        $info =  $upload->uploadOne($file);
			        if($info){ // 保存附件信息
			            M('Photo')->add($info);
			        }else{ // 上传错误
			            ajaxmsg($upload->getErrorMsg(),0);
			        }
			    }
			}
			//$info = $this->CUpload();
			$img= $info[0]['savepath'].$info[0]['savename'];
			//$img[1] = $info[1]['savepath'].$info[1]['savename'];
		}
		if($img){
			$c = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if($c==1){
				//$newid = M("member_info")->where("uid={$this->uid}")->setField('card_img',$img[0]);
				$newid = M("member_info")->where("uid={$this->uid}")->setField('card_img',$img);
			}else{
				$data['uid'] = $this->uid;
				$data['card_img'] = $img;
				//$data['card_back_img'] = $img[1];
				$newid = M('member_info')->add($data);
			}
			session("idcardimg1","1");
			ajaxmsg('上传成功',1);
		}else  ajaxmsg('上传失败，请重试',0);
	}


	


	//上传身份证反面接口
		public function ajaxupimg2(){
		// if($this->uid!=$_POST['uid'])
		// {
		// 	ajaxmsg('数据有误！',0);
		// }
		if(!empty($_FILES['imgfile2']['name'])){
			import('ORG.Net.UploadFile');
			
			$upload = new UploadFile();// 实例化上传类
			$upload->maxSize  = 3145728 ;// 设置附件上传大小
			$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->savePath = './UF/Uploads/Idcard/';// 设置附件上传目录
			chmod($upload->savePath,0777);
			$upload->saveRule = date("YmdHis",time()).rand(0,1000)."_{$this->uid}";
			foreach ($_FILES as $key=>$file){
			    if(!empty($file['name'])) {
			        $upload->autoSub = true;
			        $upload->subType   =  'date';
			        $info =  $upload->uploadOne($file);
			        if($info){ // 保存附件信息
			            M('Photo')->add($info);
			        }else{ // 上传错误
			            ajaxmsg($upload->getErrorMsg(),0);
			        }
			    }
			}
			//$info = $this->CUpload();
			$img = $info[0]['savepath'].$info[0]['savename'];
			//$img[1] = $info[1]['savepath'].$info[1]['savename'];
		}
		if($img){
			$c = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if($c==1){
				//$newid = M("member_info")->where("uid={$this->uid}")->setField('card_img',$img[0]);
				//$newid = M("member_info")->where("uid={$this->uid}")->setField('card_back_img',$img[1]);
				$newid = M("member_info")->where("uid={$this->uid}")->setField('card_back_img',$img);
			}else{
				$data['uid'] = $this->uid;
				//$data['card_img'] = $img[0];
				$data['card_back_img'] = $img;
				$newid = M('member_info')->add($data);
			}
			session("idcardimg2","1");
			ajaxmsg('上传成功',1);
		}
		else  ajaxmsg('上传失败，请重试',0);
	}

 
	//保存身份证信息（工idcheck接口调用）
		 public function saveid($real_name,$idcard){
			$isimg = session('idcardimg1');
			$isimg2 = session('idcardimg2');
			if($isimg!=1) ajaxmsg("请先上传身份证正面图片",0);
			if($isimg2!=1) ajaxmsg("请先上传身份证反面图片",0);
			//$isimg2 = session('idcardimg2');
			$data['real_name'] =$real_name;
			$data['idcard'] = $idcard;
			$data['up_time'] = time(); 
			// ///////////////////////
			$data1['idcard'] = text($_POST['idcard']);
			$data1['real_name'] = text($_POST['real_name']);
			$data1['up_time'] = time();
			$data1['uid'] = $this->uid;
			$data1['status'] = 0;
	        $xuid = M('member_info')->getFieldByIdcard($data['idcard'],'uid');
			if($xuid>0 && $xuid!=$this->uid) ajaxmsg("此身份证号码已被人使用",0);
			$b = M('name_apply') -> where("uid = {$this->uid}") -> count('uid');
			if ($b == 1) {
				M('name_apply') -> where("uid ={$this->uid}") -> save($data1);
			} else {
				M('name_apply') -> add($data1);
			} 
			// //////////////////////
			//if($isimg2!=1) ajaxmsg("请先上传身份证反面图片",0);
			if (empty($data['real_name']) || empty($data['idcard'])) ajaxmsg("请填写真实姓名和身份证号码", 0);

			$c = M('member_info') -> where("uid = {$this->uid}") -> count('uid');
			if ($c == 1) {
				$newid = M('member_info') -> where("uid = {$this->uid}") -> save($data);
			} else {
				$data['uid'] = $this->uid;
				$newid = M('member_info') -> add($data);
			} 
			session('idcardimg1',NULL);
			session('idcardimg2',NULL);
			if ($newid) {
				$ms = M('members_status') -> where("uid={$this->uid}") -> setField('id_status', 3);
				if ($ms == 1) {
					ajaxmsg("验证信息提交成功");
				} else {
					$dt['uid'] = $this->uid;
					$dt['id_status'] = 3;
					M('members_status') -> add($dt);
				} 
				ajaxmsg("验证信息提交成功");
			} else ajaxmsg("验证信息提交失败", 0);
	}
 //保存身份证信息接口
	public function idCheck() {
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['real_name'])||empty($arr['idcard'])) {
			ajaxmsg("数据有误！",0);
		}
		if($arr['uid']!=$this->uid){
			ajaxmsg("数据有误！",0);
		}
		$id5_config = FS("Webconfig/id5");
		if ($id5_config[enable] == 0) {
			//echo '实名验证授权没有开启！！！';
			//echo "aaaa";die();
			$this -> saveid($arr['real_name'],$arr['idcard']);
			exit;
		} 
		$data['real_name'] = text($arr['real_name']);
		$data['idcard'] = text($arr['idcard']);
		$data['up_time'] = time(); 
		// ///////////////////////
		$data1['idcard'] = text($arr['idcard']);
		$data1['up_time'] = time();
		$data1['uid'] = $this->uid;
		$data1['status'] = 0;
        $card = $data1['idcard'];
		$xuid = M('member_info')->getFieldByIdcard($data['idcard'],'uid');
		if($xuid>0 && $xuid!=$this->uid) ajaxmsg("此身份证号码已被人使用",0);
		// dump(11222);exit;
		$b = M('name_apply') -> where("uid = {$this->uid}") -> count('uid');
		if ($b == 1) {
			M('name_apply') -> where("uid ={$this->uid}") -> save($data1);
		} else {
			M('name_apply') -> add($data1);
		} 
		// //////////////////////
		// if($isimg!=1) ajaxmsg("请先上传身份证正面图片",0);
		// if($isimg2!=1) ajaxmsg("请先上传身份证反面图片",0);
		if (empty($data['real_name']) || empty($data['idcard'])) ajaxmsg("请填写真实姓名和身份证号码", 0);

		$c = M('member_info') -> where("uid = {$this->uid}") -> count('uid');
		if ($c == 1) {
			$newid = M('member_info') -> where("uid = {$this->uid}") -> save($data);
		} else {
			$data['uid'] = $this ->uid;
			$newid = M('member_info') -> add($data);
		} 

		function get_data($d) {
			preg_match_all('/<ROWS>(.*)<\/ROWS>/isU', $d, $arr);
			$data = array();
			$aa = array();
			$cc = array();
			foreach($arr[1] as $k => $v) {
				preg_match_all('#<result_gmsfhm>(.*)</result_gmsfhm>#isU', $v, $ar[$k]);
				preg_match_all('#<gmsfhm>(.*)</gmsfhm>#isU', $v, $sfhm[$k]);
				preg_match_all('#<result_xm>(.*)</result_xm>#isU', $v, $br[$k]);
				preg_match_all('#<xm>(.*)</xm>#isU', $v, $xm[$k]);
				preg_match_all('#<xp>(.*)</xp>#isU', $v, $cr[$k]);

				$data[] = $ar[$k][1];
				$aa[] = $br[$k][1];
				$cc[] = $cr[$k][1];
				$sfhm[] = $sfhm[1];
				$xm[] = $xm[1];
			} 
			$resa['data'] = $data[0][0];
			$resa['aa'] = $aa[0][0];
			$resa['cc'] = $cc[0][0];
			$resa['xm'] = $xm[0][0][0];
			$resa['sfhm'] = $sfhm[0][0][0];
			return $resa;
		} 
		$res = '';
		try {
			$client = new SoapClient(C("APP_ROOT") . "common/wsdl/NciicServices.wsdl");
			$licenseCode = $id5_config['auth']; //file_get_contents(C("APP_ROOT")."common/wsdl/license.txt");
			$condition = '<?xml version="1.0" encoding="UTF-8" ?>
        <ROWS>
            <INFO>
            <SBM>' . time() . '</SBM>
            </INFO>
            <ROW>
                <GMSFHM>公民身份号码</GMSFHM>
                <XM>姓名</XM>
            </ROW>
            <ROW FSD="100022" YWLX="身份证认证测试-错误" >
            <GMSFHM>' . trim($arr['idcard']) . '</GMSFHM>
            <XM>' . trim($arr['real_name']) . '</XM>
            </ROW>
            
        </ROWS>'; //330381198609262623 薛佩佩
			$params = array('inLicense' => $licenseCode,
				'inConditions' => $condition,
				);
			$res = $client -> nciicCheck($params);
		} 
		catch(Exception $e) {
			echo $e->getMessage();
			exit;
		} 
		// ajaxmsg("aaaaaaaaaa",1);
		// echo $res->out;
		
		$shuju = get_data($res -> out); 
		// ajaxmsg("实名认证成功",1); 
		
		if (@$shuju['data'] == '一致' && @$shuju['aa'] == '一致') {
			$time = time();
			$temp = M('members_status') -> where("uid={$this->uid}") -> find();
			if(is_array($temp)){
				$cid['id_status'] = 1;
			    $status = M('members_status') -> where("uid={$this->uid}") -> save($cid);
			}else{
			    $dt['uid'] = $this ->uid;
				$dt['id_status'] = 1;
				$status = M('members_status') -> add($dt);
			}
			if($status){
			    $data2['status'] = 1;
				$data2['deal_info'] = '会员中心实名认证成功';
				$new = M("name_apply") -> where("uid={$this -> uid}") -> save($data2);
				if($new) ajaxmsg('会员中心实名认证成功',3);
			}else{
			    $data2['status'] = 0;
				$data2['deal_info'] = '会员中心实名认证失败';
				$new = M("name_apply") -> where("uid={$this -> uid}") -> save($data2);
				ajaxmsg("认证失败",0);
			}
			// unlink($file);
		}else{   
		    ajaxmsg("实名认证失败",0);
		    $mm = M('members_status') -> where("uid={$this->uid}") -> setField('id_status', 3);
		    if ($mm == 1) {
			    ajaxmsg('待审核', 0);
		    } else {
			    $dt['uid'] = $this->uid;
			    $dt['id_status'] = 3;
			    M('members_status') -> add($dt);
			    ajaxmsg('等待审核', 0);
		    }
		}
	}


//留言提问
	public function message(){
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if(!isset($arr['message'])){
			ajaxmsg("您还没提问呢",0);
		}
		if($this->uid){
			$data['uid']=$this->uid;
		}else{
			$data['uid']="";
		}
		$data['contents']=$arr['message'];
		$data['phoneNum']=$arr['phone'];
		$a=M('app_feedbback')->add($data);
		if($a){
			ajaxmsg("留言成功",1);
		}
	}
	//邀请利率
	public function promotion(){
		$_P_fee=get_global_setting();
		$lilv['lilv']=$_P_fee['award_invest'];
		ajaxmsg($lilv);
    }
	
	
	
} 









