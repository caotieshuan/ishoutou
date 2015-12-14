<?php
// 本类由系统自动生成，仅供测试用途
class VerifyAction extends MCommonAction{
    public function cellphone(){
    	$isid  =M('members_status')->getFieldByUid($this->uid,'phone_status');
    	$phone = M('members')->getFieldById($this->uid,'user_phone');
		$this->assign("phone",$phone);
		$sq= M('member_safequestion')->find($this->uid);
		$this->assign("sq",$sq);
		$this->assign("phone_status",$isid);
		$datag = get_global_setting();
		$is_manual=$datag['is_manual'];
		$this->assign("is_manual",$is_manual);
		$this->display();
    }
    public function idcard(){
		$_SESSION['u_selectid'] = $this -> _get('selectid');
		$id5_config = FS("Webconfig/id5");
		if($id5_config['enable']== 1){
			$id5_enable = "idcheck";
		}else {
		    $id5_enable = "saveid";
		}
		$this->assign("id5_enable",$id5_enable);
		
		$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		if($ids==1){
			$vo = M("member_info")->field('idcard,real_name')->find($this->uid);
			$this->assign("vo",$vo);
			$data['html'] = $this->fetch();
		}
		$id5_config = FS("Webconfig/id5");
		$this->assign("id5_config",$id5_config);
		$this->assign("id_status",$ids);
		$this->display();
    }
     public function saveid(){
		
		$isimg = session('idcardimg');
		$isimg2 = session('idcardimg2');
		$data['real_name'] = text($_POST['real_name']);
		$data['idcard'] = text($_POST['idcard']);
		$data['up_time'] = time(); 
		// ///////////////////////
		$data1['idcard'] = text($_POST['idcard']);
		$data1['real_name'] = text($_POST['real_name']);
		$data1['up_time'] = time();
		$data1['uid'] = $this -> uid;
		$data1['status'] = 0;

//		if (M('name_apply') -> field('idcard') -> where("idcard ={$data1['idcard']} and status=1") -> find()) {
//			ajaxmsg("此身份证号码已被占用", 0);
//			exit;
//		} 
        $xuid = M('member_info')->getFieldByIdcard($data['idcard'],'uid');
		if($xuid>0 && $xuid!=$this->uid) ajaxmsg("此身份证号码已被人使用",0);
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
			$data['uid'] = $this -> uid;
			$newid = M('member_info') -> add($data);
		} 
		session('idcardimg',NULL);
		session('idcardimg2',NULL);
		if ($newid) {
			$ms = M('members_status') -> where("uid={$this->uid}") -> setField('id_status', 3);
			if ($ms == 1) {
				ajaxmsg();
			} else {
				$dt['uid'] = $this -> uid;
				$dt['id_status'] = 3;
				M('members_status') -> add($dt);
			} 
			ajaxmsg();
		} else ajaxmsg("保存失败，请重试", 0);
    }



}
?>