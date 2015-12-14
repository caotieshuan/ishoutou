<?php
// 本类由系统自动生成，仅供测试用途
class DaystockAction extends MCommonAction {

    public function index(){
		$this->display();
    }
	 public function tindex(){
		$this->display();
    }
	
	public function detail() {
		$record = M("shares_record")->where("shares_id = {$_GET['id']}")->find();
		$apply = M("shares_apply")->find($_GET['id']);
		if($record['profit_loss'] < 0) {
			$plr = "-".($record['profit_loss'] / $apply['total_money'] * 100);
		}else {
			$plr = $record['profit_loss'] / $apply['total_money'] * 100 ;
		}
		$this->assign("plr",$plr);
		$this->assign("pl",$record['profit_loss']);
		$this->assign("apply",$apply);
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
		$map['type_id'] = 1;
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
		$map['type_id'] = 1;
		$map['status'] = 2;
		$map['uid'] = $this->uid;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, 10);
		
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
		$map['type_id'] = 1;
		$map['status'] = 2;
		$map['uid'] = $this->uid;
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, 10);
		
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
		$map['type_id'] = 1;
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
		$map['type_id'] = 1;
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
		$status = array(2=>"交易进行中",3=>"交易完成");
		$this->assign("status",$status);
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
	
	//追加 @dong
	public function additional(){
		$id = $_POST['id'];
		
		$apply = M('shares_apply')->field("manage_rate,lever_ratio,open_ratio,alert_ratio,principal,total_money,duration,already_manage_fee,shares_money")->where("id = {$id}")->find();
		
		$this->assign('manage_rate',$apply['manage_rate']);//管理费比例
		$this->assign('lever_ratio',$apply['lever_ratio']);//倍数
		$this->assign('open_ratio',$apply['open_ratio']);//平仓线比例
		$this->assign('alert_ratio',$apply['alert_ratio']);//警戒线比率
		$this->assign('already_manage_fee',$apply['already_manage_fee']);//已出管理费
		$this->assign('duration',$apply['duration']);//使用期限
		$this->assign('apply',$apply);//警戒线比率
		$this->assign('id',$id);
		$this->display();
	}
	
	//追加 POST
	public function postdata(){
		
		$manage_rate = $_POST['manage_rate'];
		$lever_ratio = $_POST['lever_ratio'];
		$open_ratio = $_POST['open_ratio'];
		$alert_ratio = $_POST['alert_ratio'];
		$already_manage_fee = $_POST['already_manage_fee'];
		$duration = $_POST['duration'];
		$id = $_POST['apply_id'];
		$data = $_POST['data'];
		//用户名
		$user = M('members')->field("user_name")->where("id = {$this->uid}")->find();
		$apply = M('shares_apply')->field("manage_rate,lever_ratio,open_ratio,alert_ratio,principal,total_money,duration,already_manage_fee,shares_money,uid,order")->where("id = {$id}")->find();
		
		
		
		$bond = $data / $lever_ratio;//保证金 1
		$Open = $open_ratio / 100 * $bond + $data;//平仓线 1
		$alert_s = $alert_ratio / 100 * $bond + $data;//警戒线 1
		$new_interest = $manage_rate/1000*$data;//现所选一天管理费 1
		$interest = $manage_rate/1000*$apply['shares_money'];//计算出原本一天管理费
		$yy_day = $already_manage_fee / $interest;//已用天数 1

		$sy_day = $duration-$yy_day;//剩余天数 1
		$count_interest = $sy_day*$new_interest;//现共需管理费 1
		$count_f = $bond + $count_interest;//共支付
		
		$user_money = M('member_money')->where("uid = {$apply['uid']}")->find();
		$count = getMoneylimit($apply['uid']);
		
		if(($count + $count_f ) > ($user_money['back_money'] + $user_money['account_money'])){
			$msg = array();
			$msg['msg'] = '可用余额不足！Sorry！';
			echo json_encode($msg);
			die;
			
		} 
		
		$arr = array();
		$arr['principal'] = $bond;
		$arr['shares_money'] = $data;
		$arr['manage_rate'] = $manage_rate;
		$arr['manage_fee'] = $count_interest;
		$arr['shares_id'] = $id;
		$arr['add_time'] = time();
		$arr['status'] = 1;
		$arr['u_name'] = $user['user_name'];
		$arr['open_ratio'] = $Open;
		$arr['alert_ratio'] = $alert_s;
		$arr['new_interest'] = $new_interest;
		$arr['y_interest'] = $interest;
		$arr['yy_day'] = $yy_day;
		$arr['sy_day'] = $sy_day;
		$arr['order'] = $apply['order'];
		$arr['type_id'] = 1;
		
		$ret = M('shares_additional')->add($arr);
	

		if($ret){
			$msg = array();
			$msg['msg'] = '追加申请成功，请等下管理员审核！';
			echo json_encode($msg);
			die;
		}else{
			$msg = array();
			$msg['msg'] = '追加申请失败！';
			echo json_encode($msg);
			die;
		}
	}
	
	//减少实盘资金 @dong
	public function reduce(){
		$id = $_POST['id'];
		
		$apply = M('shares_apply')->field("manage_rate,lever_ratio,open_ratio,alert_ratio,principal,total_money,duration,already_manage_fee,shares_money")->where("id = {$id}")->find();
		
		$this->assign('manage_rate',$apply['manage_rate']);//管理费比例
		$this->assign('lever_ratio',$apply['lever_ratio']);//倍数
		$this->assign('open_ratio',$apply['open_ratio']);//平仓线比例
		$this->assign('alert_ratio',$apply['alert_ratio']);//警戒线比率
		$this->assign('already_manage_fee',$apply['already_manage_fee']);//已出管理费
		$this->assign('duration',$apply['duration']);//使用期限
		$this->assign('apply',$apply);//警戒线比率
		$this->assign('id',$id);
		$this->display();
	}
	//减少实盘资金存表
	public function reducedata(){

		$manage_rate = $_POST['manage_rate'];
		$lever_ratio = $_POST['lever_ratio'];
		$open_ratio = $_POST['open_ratio'];
		$alert_ratio = $_POST['alert_ratio'];
		$already_manage_fee = $_POST['already_manage_fee'];
		$duration = $_POST['duration'];
		$id = $_POST['apply_id'];
		$data = $_POST['data'];
		//用户名
		$user = M('members')->field("user_name")->where("id = {$this->uid}")->find();
		$apply = M('shares_apply')->field("manage_rate,lever_ratio,open_ratio,alert_ratio,principal,total_money,duration,already_manage_fee,shares_money,order")->where("id = {$id}")->find();
		
		$bond = $data / $lever_ratio;//保证金
		$Open = $open_ratio / 100 * $bond + $data;//平仓线
		$alert_s = $alert_ratio / 100 * $bond + $data;//警戒线
		$new_interest = $manage_rate/1000*($apply['shares_money'] - $data);//现所选之后一天管理费
		$interest = $manage_rate/1000*$apply['shares_money'];//计算出原本一天管理费
		$yy_day = $already_manage_fee / $interest;//已用天数

		$sy_day = $duration-$yy_day;//剩余天数
		$count_interest = $manage_rate/1000*$data*$sy_day;//现共需减少管理费
		
		$count_f = $Bond + $count_interest;//共支付
		
		
		$arr = array();
		$arr['principal'] = $apply['principal'] - $bond;
		$arr['shares_money'] = $apply['shares_money'] - $bond * $lever_ratio;
		$arr['manage_rate'] = $manage_rate;
		$arr['manage_fee'] = $count_interest;//如是减少实盘资金，该字段为共需减少管理费
		$arr['shares_id'] = $id;
		$arr['add_time'] = time();
		$arr['status'] = 1;
		$arr['u_name'] = $user['user_name'];
		$arr['open_ratio'] = $Open;
		$arr['alert_ratio'] = $alert_s;
		$arr['new_interest'] = $new_interest;
		$arr['y_interest'] = $interest;
		$arr['yy_day'] = $yy_day;
		$arr['sy_day'] = $sy_day;
		$arr['is_additional'] = 2;
		$arr['type_id'] = 1;
		$arr['order'] = $apply['order'];
		$arr['apply_shares_money'] = $bond * $lever_ratio;
		$arr['apply_principal'] = $bond;
		$ret = M('shares_additional')->add($arr);

		if($ret){
			$msg = array();
			$msg['msg'] = '减少实盘资金申请成功，请等下管理员审核！';
			echo json_encode($msg);
			die;
		}else{
			$msg = array();
			$msg['msg'] = '减少实盘资金申请失败！';
			echo json_encode($msg);
			die;
		}
		
		
	}
	
	public function extraction(){
		
		
		$user_name = M('members')->getFieldById($this->uid,"user_name");
		
		$this->assign("user_name",$user_name);
		$this->display();
	}
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
	//资金补充
	public function supply(){
		$this->assign('id',$_POST['id']);
		$this->display();
	}
	
	public function dosupply() {
		$user_money = M('member_money')->where("uid = {$this->uid}")->find();
		$count = getMoneylimit($this->uid);
		
		if(($count + $this->_post("money") ) > ($user_money['back_money'] + $user_money['account_money'])){
			$msg = array();
			$msg['msg'] = '可用余额不足！Sorry！';
			echo json_encode($msg);
			die;
			
		}
		dosupply($this->_post("id"),$this->_post("money"),$this->uid,1);
	}
	
	public function opens(){
		$user_name = M('members')->getFieldById($this->uid,"user_name");
		$this->assign("user_name",$user_name);
		$this->display();
	}
	public function doopens(){
		
		$id = $_GET['id'];
		$map = array();
		$map['id'] = $id;
		$savedata = array();
		$savedata['is_want_open'] = 1;
		$ret = M('shares_apply')->where($map)->save($savedata);
		
		if($ret){
			
			echo jsonmsg("申请成功，请耐心等待管理员审核！",1);
		}else{
			echo jsonmsg("申请失败！",0);
		}
	}


}
?>