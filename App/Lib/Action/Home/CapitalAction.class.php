<?php
// 本类由系统自动生成，仅供测试用途
class CapitalAction extends HCommonAction {
	public function index(){
		$phone = '';
		if($this->uid){
			$phone = M('members')->getFieldById($this->uid,'user_phone');
		}else{
			$this->redirect(__APP__."/member/common/login");
		}
		$this->assign("phone", $phone);
		//if($_GET['debug'] == true){
			$this->display('indexv');
		//}else{
		//	$this->display();
		//}
	}

	public function save(){
		$data = array();
		$result = array();
		$data['title'] = htmlspecialchars($_POST['username']);
		$data['phone'] = htmlspecialchars($_POST['phone']);
		$ver_code = htmlspecialchars($_POST['vcode']);
		$data['contxt'] = htmlspecialchars($_POST['contxt']);

		$data['optype'] = intval($_POST['optype']);
		if(empty($data['title'])){
			$this->ajaxReturn(array('code'=>1,'msg'=>'姓名必须填写'));
		}
		if(empty($data['phone'])){
			$this->ajaxReturn(array('code'=>1,'msg'=>'手机号必须填写'));
		}
		$data['dateline'] = time();
		$data['isuser'] = $this->uid ? 1 : 0;
		$data['username'] = $this->uid ? M('members')->getFieldById((int)$this->uid,'user_name') : 0;
		$data['uid'] = $this->uid? $this->uid : 0;
		$data['verp'] = 1;
		$data['status'] = 0;
		$result = M('applylog')->add($data);
		if($result){
			$this->ajaxReturn(array('code'=>0,'msg'=>"提交成功！\r\n 若您急需融资，请直接与在线客服联系，加快处理进度"));
		}else{
			$this->ajaxReturn(array('code'=>1,'msg'=>'保存失败，请刷新重试！'));
		}


	}

	public function sendphone(){
		$phone = htmlspecialchars($_POST['cellphone']);
		$code = rand_string_reg(6, 1, 2);
		$result = sendsms($phone,'验证码为：'.$code);
		$this->ajaxReturn(array('status'=>$result));
	}
}













