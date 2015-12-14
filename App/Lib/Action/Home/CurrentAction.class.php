<?php
// 本类由系统自动生成，仅供测试用途
class CurrentAction extends HCommonAction {
	public function index(){
		
		$info = M('current_info')->order("id DESC")->find();
		$this->ajax_page($info['id']);
		$this->assign("vo",$info);
		$this->display();
	}
	
	public function Popup(){
		
		$this->assign("id",$_POST['id']);
		$this->assign("buy_money",$_POST['buy_money']);
		$this->assign("invest_uid",$this->uid);
		$this->display();
	}
	
	public function doAdd(){
		
		$current_info = M('current_info')->find($_POST['current_id']);
		$user_money = M('member_money')->find($this->uid);
		
		
		
		$buy_money = $_POST['buy_money'];
		
		if(($user_money['back_money'] + $user_money['account_money']) < $buy_money){
			
			$this->error('可用余额不足！');
		}
		
		if($buy_money == ''){
			
			$this->error('数据有误！');
		}
		
		if($buy_money % $current_info['one_money'] != 0){

			$this->error('加入金额需为最小购买金额的整数倍！');
		}
		if($buy_money > $current_info['max_money']){
			
			$this->error('加入金额大于最大购买金额！');
		}
		
		$model = D("CurrentInvestor");		
		$ret = $model->addInvest();
		if($ret){
			
			$res = currentinvest($_POST['buy_money'],$_POST['current_id'],$this->uid);
			if($res){
				
				$this->success('加入成功！');
			}else{
				
				$this->error('数据校验有误！');
			}
			
		}else{
			
			$this->error('加入失败！');
		}
	}
	public function ajax_page($current_id){
		
		 isset($_GET['current_id']) && $current_id = intval($_GET['current_id']);
        $Page = D('Page');       
        import("ORG.Util.Page");       
        $count = M("current_investor")->where("current_id = {$current_id} and `order` <> ''")->count('id');
		
        $Page = new Page($count,5);
        $show = $Page->ajax_show();
        $this->assign('page', $show);
        if($_GET['current_id']){
			$list = M("current_investor t")
					->join("lzh_members s ON s.id = t.invest_uid")
					->where("t.current_id = {$current_id} and t.order <> ''")
					->order('t.id')
					->limit($Page->firstRow.','.$Page->listRows)				
					->select();
			
			
            $str = '';
			
           foreach($list as $k=>$v){
			  
				$str .="<ul class='items'>
						<li class='col_1'>".hidecard($v['user_name'],5)."</li>
						<li class='col_2'>".$v['buy_money']."</li>
						<li class='col_3'>".date("Y-m-d H:i",$v['add_time'])."</li>
						<li class='col_4'><span class='succ'><i class='icons cm-green'></i>成功</span></li>						
					</ul>";
            }
			$data = array();
			$data['html'] = empty($str)?'暂时没有投资记录':$str;
			$data['count'] = $count;
			echo json_encode($data);
		
		
	}

  }
}	














