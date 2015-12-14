<?php
/*	股权配资月类控制器
 *	@author:Bob
 *	@time:2015/3/27
 */
class MonthstockAction extends MCommonAction {

    public function index(){
		$this->display();
    }
	
	public function agreement() {
		$id = intval($_GET['id']);
		$apply = M("shares_apply")->find($id);
		$minfo = M("member_info")->find($apply['uid']);//real_name idcard
		if($apply['uid'] != $this->uid) {
			$this->error("数据有误!");
		}
		$this->assign("minfo",$minfo);
		$this->assign("apply",$apply);
		$this->display();
	}
	
	//补充实盘资金
	public function dosupply() {
		dosupply($this->_post("id"),$this->_post("money"),$this->uid,2);
	}
	
	//申请平仓
	public function applyeven() {
		applyeven($this->_post("id"),2);
	}
	
	//补充实盘资金页面渲染
	public function supply(){
		$this->assign('id',$_POST['id']);
		$this->display();
	}
	
	//追加保证金
	public function additional(){
		$this->assign('id',$_POST['id']);
		$apply = M('shares_apply')->field("manage_rate,lever_ratio,open_ratio,alert_ratio,principal,total_money,duration,already_manage_fee,shares_money")->where("id = {$_POST['id']}")->find();
		$this->assign("apply",$apply);
		$this->display();
	}
	
	//等待审核的配资列表
	public function tending(){
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = 1;
		$map['uid'] = $this->uid;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply s")->field("s.*,m.user_name")->join("lzh_members m ON m.id = s.uid")->where($map)->limit($Lsql)->order("id DESC")->select();
		$this->assign("list",$list);
		$this->assign("page",$page);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
	
	//进行中的配资列表
	public function tendbacking(){
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = array("in","2,6");
		$map['uid'] = $this->uid;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply s")->field("s.*,m.user_name")->join("lzh_members m ON m.id = s.uid")->where($map)->limit($Lsql)->order("id DESC")->select();
		$this->assign("list",$list);
		$this->assign("page",$page);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
	//进行中的配资列表
	public function tendback(){
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = array("in","2,6");
		$map['uid'] = $this->uid;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply s")->field("s.*,m.user_name")->join("lzh_members m ON m.id = s.uid")->where($map)->limit($Lsql)->order("id DESC")->select();
		$this->assign("list",$list);
		$this->assign("page",$page);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}

	//审核未通过的配资列表
	public function tenddone(){
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = 4;
		$map['uid'] = $this->uid;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply s")->field("s.*,m.user_name")->join("lzh_members m ON m.id = s.uid")->where($map)->limit($Lsql)->order("id DESC")->select();
		
		$this->assign("list",$list);
		$this->assign("page",$page);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}

	//已完成的配资列表
	public function tendbreak(){
		import("ORG.Util.Page");
		$map['type_id'] = 2;
		$map['status'] = 3;
		$map['uid'] = $this->uid;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("shares_apply s")->field("s.*,m.user_name")->join("lzh_members m ON m.id = s.uid")->where($map)->limit($Lsql)->order("id DESC")->select();
		
		$this->assign("list",$list);
		$this->assign("page",$page);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
	
	//配资详情
	public function stockdetails(){
		import('ORG.Util.Date');// 导入日期类
		$Date = new Date();
		if($this->_get('id')){
			$id = $this->_get('id');
		}else{
			$this->error('数据有误！');
		}
		$status = array(2=>"交易进行中",3=>"交易完成");
		$this->assign("status",$status);
		$apply = M('shares_apply')->find($id);
		if($apply['trading_time'] == 1) {
			$apply['trading'] = $apply['examine_time'];
		}else {
			$apply['trading'] = strtotime("+24 hours",$apply['examine_time']);
		}
		$apply['start_time'] = abs(intval($Date->dateDiff(date("Y-m-d H:i:s",$apply['examine_time']))));
		$this->assign("vo",$apply);
		$this->ajax_page($id);
		$this->assign("id",$id);
		$this->display();
	}
	
	//配资详情分页
	public function ajax_page($id=0){
		$id = $_GET['id'] ? $_GET['id'] : $id;
		$Page = D('Page');       
     		 import("ORG.Util.Page");       
     		 $count = M('member_moneylog')->field("add_time,affect_money,type")->where("shares_id = {$id}")->count('id');
     		 $Page     = new Page($count,5);
     		 $show = $Page->ajax_show();
     		 $this->assign('page', $show);
		if($_GET['id']){
			$log = M('member_moneylog')->field("add_time,affect_money,type")->where("shares_id = {$id}")->limit($Page->firstRow.','.$Page->listRows)->select();
			$str = '';
			foreach($log as $key=>$v){
				$str .="<tr align='center'>
							<td>
								".date("Y-m-d H:i:s",$v['add_time'])."
							</td>
							<td>
								".$v['type']."
							</td>
							<td>
								".$v['affect_money']."
							</td>
						</tr>";
			}
			if($this->isAjax()) {
				echo $str;
			}
		}	
	}
	
	//追加保证金
	public function postdata() {
		$id = $this->_post("id");
		$money = $this->_post("money");
		additional($id,$money,2);
	}
	
	//提取收益
	public function extraction(){

		$user_name = M('members')->getFieldById($this->uid,"user_name");
		
		$this->assign("user_name",$user_name);
		$this->display();
	}
	
	//提取收益
	public function edit(){
		
		$id = $_GET['id'];
		$status = array();
		$status['status'] = 6;
		$ret = M('shares_apply')->where("id = {$id}")->save($status);
		
		if($ret){
			
			$msg = array();
			$msg['msg'] = '申请提取盈利成功，请耐心等待管理员审核！';
			echo json_encode($msg);
			die;
		}else{
			
			$msg = array();
			$msg['msg'] = '申请提取盈利失败！';
			echo json_encode($msg);
			die;
		}
	}
	
}