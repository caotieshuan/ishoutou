<?php
// 本类由系统自动生成，仅供测试用途
class CurrentAction extends MCommonAction {

    public function index(){
		$this->display();
    }
	 public function tindex(){
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

	
		$map = array();
		$map['status'] = 1;
		import("ORG.Util.Page");
		$count = M('current_investor')->where($map)->count();
		$p = new Page($count, 10);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M('current_investor')->where($map)->limit($Lsql)->select();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}

	public function tendbacking(){
		$map = array();
		$map['status'] = 2;
		import("ORG.Util.Page");
		$count = M('current_investor')->where($map)->count();
		$p = new Page($count, 10);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M('current_investor')->where($map)->limit($Lsql)->select();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}



	public function tendbreak(){
		$map = array();
		$map['status'] = 3;
		import("ORG.Util.Page");
		$count = M('current_investor')->where($map)->count();
		$p = new Page($count, 10);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M('current_investor')->where($map)->limit($Lsql)->select();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
	
	public function edit(){
		
		$invest_id = $_POST['id'];
		
		$status = array();
		$status['status'] = 2;
		$map = array();
		$map['id'] = $invest_id;
		$ret = M("current_investor")->where($map)->save($status);
		$vo = M('current_investor')->find($invest_id);
		$day = buy_day($vo['add_time']);
		if($day < 0){
			
			echo jsonmsg('活期理财为次日计息方式！',0);die;
		}
		if($ret){
			
			echo jsonmsg('提取成功，请耐心等待审核:)！',1);
		}else{
			
			echo jsonmsg('提取失败！',0);
		}
		
	}


}