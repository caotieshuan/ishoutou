<?php
class MobilecommonAction extends MMCommonAction{
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
	
	//登录（手机号登录）
	public function phoneactlogin(){
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||$arr['name']==""||$arr['password']=="") {
		  ajaxmsg("提交信息错误，请重试!",0);
		}
		$data['user_name']=$arr['name'];
		// 
		// var_dump($sessionid);die;
		session("u_user_name",$data['user_name']);
		$data['user_pass']=md5($arr['password']);
		$vo = M('members')->field('id,user_name,user_email,user_pass,is_ban,user_phone')->where($data)->find();
		
		if($vo['is_ban']==1) ajaxmsg("您的帐户已被冻结，请联系客服处理！",0);
			if(is_array($vo)){	
				if($vo['user_pass'] == md5($arr['password']) ){//本站登录成功
					//会员中心头像
					$this->_memberlogin($vo['id']);
					$arr2 = array();
		            $arr2['type'] = 'actlogin';
					$arr2['deal_user'] = $vo['user_name'];
		            $arr2['tstatus'] = '1';
					$arr2['deal_time'] = time();
		            $arr2['deal_info'] = $vo['user_name']."登录成功_".$jsoncode;
		            $newid = M("auser_dologs")->add($arr2);
					//需要返回的数据
					$mess = array();
					$mess['session_id']=session_id();
					//session_set_cookie_params(0);
					session("u_id",$vo['id']);
					session("u_user_name",$vo['user_name']);
				    $mess['uid'] = intval(session("u_id"));
					$mess['username'] = $vo['user_name'];
					$mess['phone'] = $vo['user_phone'];
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
			    	
					ajaxmsg($mess);
				}else{
					ajaxmsg("用户名或者密码错误！",0);
				}

				}else {
					ajaxmsg("用户名或者密码错误！",0);
			}
		}
	//注册
	public function regaction(){
		$jsoncode = file_get_contents("php://input");
		$arr = array();	
		$arr = json_decode($jsoncode,true);
		//ajaxmsg(print_r($arr,true),1);
		if (!is_array($arr)||empty($arr)) {
		  ajaxmsg("提交信息错误，请重试.",0);
		}
		if ($arr['name']==""||$arr['password']==""||$arr['email']==""||$arr['code']==""/*||$arr['people']==""*/) {
		  ajaxmsg("提交信息错误，请重试!",0);
		}
		// //限制用户必须用手机号码注册	
		// if (!strlen($arr['name']) == "11"){
		// 	   ajaxmsg("请用手机号码注册",0);
		// }else if(!preg_match("/13[123569]{1}\d{8}|15[1235689]\d{8}|188\d{8}/",$arr['name']))
		//  		{ajaxmsg("号码格式错误",0);}		
		$data['user_name'] = trim($arr['name']);
		$data['user_pass'] = md5($arr['password']);
		$data['user_email'] = text($arr['email']);
		$data['user_phone'] = trim($arr['name']);
		//判断是否存在推荐人
		if (!empty($arr['people'])){
			$map['user_name'] = $arr['people'];
			$count1 = M('members')->field('id')->where($map)->find();
			//ajaxmsg($count1['id']);
			}
        	//判断用户名是否被注册
		$count2 = M('members')->where("user_email = '{$data['user_email']}' OR user_name='{$data['user_phone']}' OR user_phone='{$data['user_phone']}'")->count('id');
		if($count2>0) {
			ajaxmsg("kk注册失败，用户名或者邮件或者电话号码已经有人使用" ,0);
		}
	
		$data['reg_time'] = time();
		//获得会员当前注册设备IP
		$data['reg_ip'] = get_client_ip();
		$data['lastlog_time'] = time();
		$data['lastlog_ip'] = get_client_ip();
		$newid = M('members')->add($data);
		if($newid){
			//通数据格讯式信息提示（状态码，消息提示，返回数据）
			Notice(1,$newid,array('phone',$data['user_phone']));
			$mess = array();
			$mess['uid'] = $newid;
			//ajaxmsg($mess);
			$mess['username'] = $data['user_name'];
			$mess['head'] = get_avatar($newid);
			$mess['total'] = 0;
			$mess['mayuse'] = 0;
			$mess['freeze'] = 0;
			$mess['collect'] = 0;
			if($count1){
			$mess['people_id']=$count1['id'];
		}
			ajaxmsg($mess);
			//$this->actlogin();
			
			//ajaxmsg($mess);
		}else{
			ajaxmsg("注册失败，请重试",0);
		}
	}
	
	public function mactlogout(){
		$this->_memberloginout();
		ajaxmsg("注销成功");
		
	}


	//找回密码(用手机找回)
	public function phonegetpass(){
		$jsoncode=file_get_contents("php://input");
		$arr=array();
		$arr=json_decode($jsoncode,true);
		//ajaxmsg(print_r($arr,true),0);
		if (!is_array($arr)||empty($arr)||empty($arr['username'])|| empty($arr['password'])){
			ajaxmsg("数据有误！",0);
		}
		$data['user_pass']=md5($arr['password']);
		//看用户名是否输入正确
		if (M("members")->where("user_name={$arr['username']}")->find())
		//修改密码
			if(!$updatepassword= M("members")->where("user_name={$arr['username']}")->save($data)){
						ajaxmsg('密码设置失败',0);
				}		ajaxmsg('密码设置成功',1);
			}
		

		//找回密码(用邮箱找回)(待完善)
		public function emailgetpass(){
		$jsoncode=file_get_contents("php://input");
		$arr=array();
		$arr=json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['email'])||empty($arr['username'])) {
		   ajaxmsg("数据错误aa！",0);
		}
		$vo = M('members')->field('id')->where("user_email={$arr['email']}")->find();
		if(is_array($vo)){
			$res = Notice(7,$vo['id']);
			if($res) ajaxmsg('发送成功');
			else ajaxmsg('发送失败',0);
		}else{
			ajaxmsg('邮箱不存在或用户名有误',0);
		}
	}
}