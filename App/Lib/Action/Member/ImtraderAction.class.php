<?php
// 本类由系统自动生成，仅供测试用途
class ImtraderAction extends MCommonAction {

    public function index(){
		$this->display();
    }
    public function tindex(){
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
	
    public function summary(){
		$uid = $this->uid;
		$pre = C('DB_PREFIX');
		
		$this->assign("dc",M('investor_detail')->where("investor_uid = {$this->uid}")->sum('substitute_money'));
		$this->assign("mx",getMemberBorrowScan($this->uid));
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
	
	public function tending(){
		import("ORG.Util.Page");
		$map['type_id'] = 3;
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

	public function tendbacking(){
		import("ORG.Util.Page");
		$map['type_id'] = 3;
		$map['status'] = 2;
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

    public function tendback(){
		import("ORG.Util.Page");
		$map['type_id'] = 3;
		$map['status'] = 2;
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

	public function tenddone(){
		import("ORG.Util.Page");
		$map['type_id'] = 3;
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

	public function tendbreak(){
		import("ORG.Util.Page");
		$map['type_id'] = 3;
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
	
	public function stockdetails(){
		
		import('ORG.Util.Date');// 导入日期类
		$Date = new Date();
		if($this->_get('id')){
			$id = $this->_get('id');
		}else{
			$this->error('数据有误！');
		}  
		
		$apply = M('shares_apply')->find($id);
	
		if($apply['trading_time'] == 1) {	
			$apply['trading'] = $apply['examine_time'];
		}else {
			$apply['trading'] = strtotime("+24 hours",$apply['examine_time']);
		}
		$apply['start_time'] = intval($Date->dateDiff(date("Y-m-d H:i:s",$apply['examine_time'])));
		$this->assign("vo",$apply);
		$this->ajax_page($id);
		$this->assign("id",$id);
		$this->display();
	}
	
	public function ajax_page($id=0){
		 isset($_GET['id']) && $id = intval($_GET['id']);
		$Page = D('Page');       
      		  import("ORG.Util.Page");       
      		  $count = M('member_moneylog')->field("add_time,affect_money,type")->where("shares_id = {$id}")->count('id');
      		  $Page = new Page($count,5);
      		  $show = $Page->ajax_show();
      		  $this->assign('page', $show);
		
		
		if($_GET['id']){
			$log = M('member_moneylog')->field("add_time,affect_money,type")->where("shares_id = {$id}")->limit($Page->firstRow.','.$Page->listRows)->select();
			$str = '';
			foreach($log as $key=>$v){
				$v['type']?$v['type']="我是操盘手费用" :$v['type']="未知";
				$str .="<tr>
							<td align='center'>
								<div class='log-cell'>".date("Y-m-d H:i:s",$v['add_time'])."</div>
							</td>
							<td align='center'>
								<div class='log-cell'>".$v['type']."</div>
							</td>
							<td align='center'>
								<div class='log-cell'>".$v['affect_money']."</div>
							</td>
						</tr>";
				
			}
			if($this->isAjax()) {
				echo $str;
			}
		}
	}
	public function addpform(){
		$this->assign('id',$_POST['id']);
		$apply = M('shares_apply')->field("manage_rate,lever_ratio,open_ratio,alert_ratio,principal,total_money,duration,already_manage_fee,shares_money")->where("id = {$_POST['id']}")->find();
		$this->assign("apply",$apply);
		$this->display();
	}
	public function postdata() {
		$id = $this->_post("id");
		$money = $this->_post("money");
		additional($id,$money,3);
	}
	public function supply(){
		$this->assign('id',$_POST['id']);
		$this->display();
	}
	//增加实盘资金
	public function dosupply() {
		//配资id 补充金额 用户id 类型:天/月/操
		dosupply($this->_post("id"),$this->_post("money"),$this->uid,3);
	}
	//减少实盘资金
	public function docatsupply(){
		$iscut = true;
		dosupply($this->_post("id"),$this->_post("money"),$this->uid,3,$iscut);
	}
	public function applyeven() {
		applyeven($this->_post("id"));
	}
	public function cutpply(){
		$this->assign('id',$_POST['id']);
		$this->display();
	}
}