<?php
// 本类由系统自动生成，仅供测试用途

class RongziAction extends HCommonAction {
    public function index(){
		$this->display();
	}	
	public function save(){
		if($_SESSION['verify'] != md5($_POST['txt_check'])){
            $this->error('验证码错误');
		}
		$_POST = textPost($_POST);
        $model = M('rongzi');
        if (false === $model->create()) {
            $this->error($model->getError());
        }
		unset($model->status);
		//$model->msg = "借款金额：".text($_POST['money'])."&nbsp;&nbsp;&nbsp;".$model->msg;
		$model->add_time = time();
		$model->ip = get_client_ip();
        //保存当前数据对象
        if ($result = $model->add()) { //保存成功
            //成功提示
            $this->assign('jumpUrl', __APP__."/rongzi/index");
            $this->success('反馈成功');
        } else {
            //失败提示
            $this->error('反馈失败，请重试');
        }
	}	
}