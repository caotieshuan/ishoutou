<?php
// 本类由系统自动生成，仅供测试用途
class UserAction extends MCommonAction {

    public function index(){
		$this->display();
    }
	public function pin(){
		if(ListMobile()){
			$this->display();
		}
	}
	public function avatar(){
		if(ListMobile()){
			$this->display();
		}
	}

	public function upavataron(){
		$xml = json_decode(file_get_contents('php://input'));
		list($type, $data) = explode(',', $xml->base64);
		if('' !== strstr($type,'image/jpeg')){
			$ext = '.jpg';
		}elseif('' !== strstr($type,'image/gif')){
			$ext = '.gif';
		}elseif('' !== strstr($type,'image/png')){
			$ext = '.png';
		}
		$path = realpath(__ROOT__).'/Style/header/customavatars';

		if(!empty($ext)){
			$photo = 'upload'.$this->uid.$ext;
			if (!file_exists($path) || !is_dir($path)) {
				mkdir($path,0777,true);
			}
			$filename = $path.$photo;
			file_exists($filename) && unlink($filename);
			if(file_put_contents($filename, base64_decode($data), true)){
				$avatarpath = $this->get_avatar_path($this->uid) ;
				$avatarrealdir  =  $path. DIRECTORY_SEPARATOR . $avatarpath;
				if(!is_dir( $avatarrealdir )) {
					$this->make_avatar_path( $this->uid, $path );
				}
				$avatartype = 'virtual';
				$avatarsize = array( 1 => 'big', 2 => 'middle', 3 => 'small');
				foreach( $avatarsize as $key => $size ){
					$avatarrealpath = $path . DIRECTORY_SEPARATOR. $this->get_avatar_filepath($this->uid, $size, $avatartype);
					$writebyte = file_put_contents( $avatarrealpath, base64_decode($data), LOCK_EX );
					if( $writebyte <= 0 ){
						ajaxmsg('写入文件失败',0);
						break;
					}
					$avatarinfo = getimagesize($avatarrealpath);
					if(!$avatarinfo || $avatarinfo[2] == 4 ){
						$this->clear_avatar_file( $this->uid, $avatartype );
						ajaxmsg('操作失败',0);
						break;
					}
				}
				ajaxmsg('/Style/header/customavatars/'.$this->get_avatar_filepath($this->uid, 'small', $avatartype));
			}else{
				ajaxmsg('上传失败',0);
			};
		}else{
			ajaxmsg('文件格式不正确',0);
		}
	}

    public function header(){
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function password(){
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }


    public function pinpass(){

		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function changepass(){
		$old = md5($_POST['oldpwd']);
		$newpwd1 = md5($_POST['newpwd1']);
		$c = M('members')->where("id={$this->uid} AND user_pass = '{$old}'")->count('id');
		if($c==0) ajaxmsg('',2);
		$newid = M('members')->where("id={$this->uid}")->setField('user_pass',$newpwd1);

		//require_once "./config.inc.php";
		//require "./uc_client/client.php";
		//$username = M("members")->find($this->uid);
		//$ucresult = uc_user_edit($username['user_name'], $_POST['oldpwd'], $_POST['newpwd1']);
		if($newid){
			MTip('chk1',$this->uid);
			ajaxmsg();
		}
		else ajaxmsg('',0);
    }

    public function changepin(){
		$old = md5($_POST['oldpwd']);
		$newpwd1 = md5($_POST['newpwd1']);
		$c = M('members')->where("id={$this->uid}")->find();
		if($old==$newpwd1){
			ajaxmsg("设置失败，请勿让新密码与老密码相同。",0);
		}
		if(empty($c['pin_pass'])){
			if($c['user_pass'] == $old){
				$newid = M('members')->where("id={$this->uid}")->setField('pin_pass',$newpwd1);
				if($newid) ajaxmsg();
				else ajaxmsg("设置失败，请重试",0);
			}else{
				ajaxmsg("原支付密码(即登录密码)错误，请重试",0);
			}
		}else{
			if($c['pin_pass'] == $old){
				$newid = M('members')->where("id={$this->uid}")->setField('pin_pass',$newpwd1);
				if($newid) ajaxmsg();
				else ajaxmsg("设置失败，请重试",0);
			}else{
				ajaxmsg("原支付密码错误，请重试",0);
			}
		}
    }

    public function msgset(){
		$this->assign("vo",M('sys_tip')->find($this->uid));
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
	
	public function savetip(){
		$oldtip = M('sys_tip')->where("uid={$this->uid}")->count('uid');
		$data['tipset'] = text($_POST['Params']);
		$data['uid'] = $this->uid;
		if($oldtip) $newid = M('sys_tip')->save($data);
		else $newid = M('sys_tip')->add($data);
		//$this->display('Public:_footer');
		if($newid) echo 1;
		else echo 0;
	}



	public function get_avatar_path($uid) {
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		return $dir1.'/'.$dir2.'/'.$dir3;
	}
	/**
	 * 在指定目录内，依据uid创建指定的头像规范存放目录
	 * 来源：Ucenter base类的set_home方法
	 *
	 * @param int $uid uid编号
	 * @param string $dir 需要在哪个目录创建？
	 */
	public function make_avatar_path($uid, $dir = '.') {
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		!is_dir($dir.'/'.$dir1) && mkdir($dir.'/'.$dir1, 0777);
		!is_dir($dir.'/'.$dir1.'/'.$dir2) && mkdir($dir.'/'.$dir1.'/'.$dir2, 0777);
		!is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && mkdir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3, 0777);
	}
	/**
	 * 获取指定uid的头像文件规范路径
	 * 来源：Ucenter base类的get_avatar方法
	 *
	 * @param int $uid
	 * @param string $size 头像尺寸，可选为'big', 'middle', 'small'
	 * @param string $type 类型，可选为real或者virtual
	 * @return unknown
	 */
	public function get_avatar_filepath($uid, $size = 'big', $type = '') {
		$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'big';
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$typeadd = $type == 'real' ? '_real' : '';
		return  $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
	}
	/**
	 * 一次性清空指定uid用户已经存储的头像
	 *
	 * @param int $uid
	 */
	public function clear_avatar_file( $uid ){
		$avatarsize = array( 1 => 'big', 2 => 'middle', 3 => 'small');
		$avatartype = array( 'real', 'virtual' );
		foreach ( $avatarsize as $size ){
			foreach ( $avatartype as $type ){
				$avatarrealpath = realpath( $this->config->avatardir) . DIRECTORY_SEPARATOR. $this->get_avatar_filepath($uid, $size, $type);
				file_exists($avatarrealpath) && unlink($avatarrealpath);
			}
		}
		return true;
	}
	/**
	 * flash data decode
	 * 来源：Ucenter
	 *
	 * @param string $s
	 * @return unknown
	 */
	protected function _flashdata_decode($s) {
		$r = '';
		$l = strlen($s);
		for($i=0; $i<$l; $i=$i+2) {
			$k1 = ord($s[$i]) - 48;
			$k1 -= $k1 > 9 ? 7 : 0;
			$k2 = ord($s[$i+1]) - 48;
			$k2 -= $k2 > 9 ? 7 : 0;
			$r .= chr($k1 << 4 | $k2);
		}
		return $r;
	}
	public function verify(){
		import("ORG.Util.Image");
		Image::buildImageVerify();
	}
	public function verifylogin(){
		$c = M('members')->where("id={$this->uid}")->find();
		$this->assign('username',$c['user_name']);
		$this->display();
	}
	public function checklogin(){
		$password = md5($_POST['loginpassword']);
		$c = M('members')->where("id={$this->uid} AND user_pass = '{$password}'")->count('id');
		if($c==0) ajaxmsg('登陆密码错误！',2);
		if($_SESSION['verify'] != md5(strtolower($_POST['sVerCode'])))
		{
			ajaxmsg("验证码错误!",1);
		}
		ajaxmsg("验证成功!",3);
	}
	public function resettrade(){
		$c = M('members')->where("id={$this->uid}")->find();
		$this->assign('username',$c['user_name']);
		$this->display();
	}
	public function resetpinpwd(){
		$pwd = md5($_POST['password']);
		$vo = M('members')->where("id={$this->uid}")->find();
		
		if($pwd != $vo['user_pass']){
			$newid = M('members')->where("id={$this->uid}")->setField('pin_pass',$pwd);
			ajaxmsg("设置成功",1);
			/*if($newid){ 
				ajaxmsg("设置成功",1);
			}else{
				ajaxmsg("设置失败，请重试",2);
			}*/
		}else{
			ajaxmsg("支付密码不能和登录密码相同",3);exit();
		}
	}
	public function success(){
		$this->display();
	}
}