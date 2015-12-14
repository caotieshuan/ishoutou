<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends ACommonAction {

	var $justlogin = true;
	
    public function index(){

		require(C('APP_ROOT')."Common/acl.inc.php");
		require(C('APP_ROOT')."Common/menu.inc.php");
		if($this->glo['admin_login'] == 0) {
			$id = session('admin_id');
			$admin = M("ausers")->find($id);
			if($admin) {
				if($admin['last_log_time'] != session('lastlogintime')) {
					alogs("logout",'','1',"管理员退出");
					session(null);
					$this->assign('jumpUrl', '/');
					$this->error('您的帐号在别处登录,您被迫下线!');
				}
			}
		}
       	$this->assign('menu_left',$menu_left);
		$this->display();
    }
	
	
	 public function logincheck(){
		$code=$_GET["code"];
		$datag = get_global_setting();
		$codecheck=$datag['admin_url'];
			
	    if($code!=$codecheck){
			$this->assign('jumpUrl', '/');
            $this->error("非法请求");
	    }else{
			$this->redirect('login');
		}

	 }

	
	public function verify(){
		import("ORG.Util.Image");
		Image::buildImageVerify();
	}

    public function login()
    {
		require C("APP_ROOT")."Common/menu.inc.php";
		if( session("admin") > 0){
			$this->redirect('index');
			exit;
		}
		if($_POST){
			$cod = md5(strtolower($_POST['code']));
			if($_SESSION['verify'] != $cod){
				$this->error("验证码错误!");
			}
			$data['user_name'] = text($_POST['admin_name']);
			$data['user_pass'] = md5(strtolower($_POST['admin_pass']));
			$data['is_ban'] = array('neq','1');
			$data['user_word'] = text($_POST['user_word']);
			$admin = M('ausers')->field('id,user_name,u_group_id,real_name,is_kf,area_id,user_word,last_log_time,last_log_ip')->where($data)->find();

			if(is_array($admin) && count($admin)>0 ){
				foreach($admin as $key=>$v){
					session("admin_{$key}",$v);
				}
				if(session("admin_area_id")==0) session("admin_area_id","-1");
				session('admin',$admin['id']);
				session('adminname',$admin['user_name']);
				$info['last_log_time'] = time();
				session('lastlogintime',$info['last_log_time']);
                $info['last_log_ip'] = get_client_ip();
				M("ausers")->where('id='.$admin['id'])->save($info);
				 
				alogs("login",'','1',"管理员登录成功");//管理员操作日志之登录日志
				$this->assign('jumpUrl', "__URL__/index");
				$this->success('登录成功，现在转向管理主页');
			}else{
				alogs("login",'','0',"管理员登录失败",$admin['real_name']);
				$this->error('用户名或密码或口令错误，登录失败');
			}
		}else{
			$this->error("非法请求");
			//$this->display();
		}
		
    }


    public function logout()
    {
		alogs("logout",'','1',"管理员退出");
		//require C("APP_ROOT")."Common/menu.inc.php";
		session(null);
		$this->assign('jumpUrl', '/');
		$this->success('注销成功，现在转向首页');
    }
	
}