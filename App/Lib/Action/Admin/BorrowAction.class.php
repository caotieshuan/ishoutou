<?php
// 全局设置
class BorrowAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function waitverify()
    {
		$map=array();
		$map['b.borrow_status'] = 0;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		
		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.updata,b.borrow_money,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.add_time,m.user_name,m.id mid,b.is_tuijian,b.money_collect';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		
		$list = $this->_listFilter($list);
		
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function waitverify2()
    {
		$map=array();
		$map['b.borrow_status'] = 4;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.full_time,m.user_name,m.id mid,b.is_tuijian,b.money_collect';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function waitmoney()
    {
		$map=array();
		$map['b.borrow_status'] = 2;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.add_time,m.user_name,m.id mid,b.is_tuijian,b.has_borrow,b.money_collect';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function repaymenting()
    {
		$map=array();
		$map['bi.borrow_status'] = 6;//还款中
		$map['id.status'] = 7;//还款中
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['bi.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['bi.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['bid'])&&!isset($search['bid']) ){
			$map['bi.id'] = intval($_REQUEST['bid']);
			$search['bid'] = $map['bi.id'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['bi.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['id.deadline'] = array("between",$timespan);
			$search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));	
			$search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['id.deadline'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['id.deadline'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			/*if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}*/
		//}
		//分页处理
		/*import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'm.id as mid,m.customer_name,b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.borrow_interest,b.repayment_money,b.repayment_interest,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.deadline,m.user_name,m.user_phone,b.is_tuijian,b.money_collect';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);*/
		import("ORG.Util.Page");
		$count = M('investor_detail id')->join("{$this->pre}borrow_info bi on id.borrow_id = bi.id ")->where($map)->group('id.borrow_id,sort_order')->select();
		$p = new Page(count($count), C('ADMIN_PAGE_SIZE'));
		$page = $p->show();

		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'borrow_id as id,borrow_id as borrow_id,bi.borrow_status as borrow_status,id.borrow_uid as borrow_uid, bi.borrow_name as borrow_name,bi.borrow_money as borrow_money,(bi.repayment_money+bi.repayment_interest) as repaymoney,bi.borrow_duration as borrow_duration,bi.borrow_fee as borrow_fee,bi.repayment_type as repayment_type,sort_order,(sum(capital)+sum(interest)) as need_money,id.deadline as repayment_time';
		$list = M('investor_detail id')->field($field)->join("{$this->pre}borrow_info bi on id.borrow_id = bi.id ")->where($map)->group('id.borrow_id,sort_order')->limit($Lsql)->order(" repayment_time asc")->select();
		$list = $this->_listFilter($list);

		foreach ($list as $k => $v) {
			$vx = M('members')->field('id,user_name,user_phone')->where(" id={$v['borrow_uid']}  ")->find();
			$list[$k]['user_phone'] = $vx['user_phone'];
			$list[$k]['user_name'] = $vx['user_name'];
			$list[$k]['mid'] = $vx['id'];
		}
		
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	public function export(){
		ini_set("memory_limit","-1");
		set_time_limit (0);
		import("ORG.Io.Excel");
		$map=array();
		$map['bi.borrow_status'] = 6;//还款中
		$map['id.status'] = 7;//还款中
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['bi.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['bi.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['bid'])&&!isset($search['bid']) ){
			$map['bi.id'] = intval($_REQUEST['bid']);
			$search['bid'] = $map['bi.id'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['bi.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = (urldecode($_REQUEST['start_time'])).",".(urldecode($_REQUEST['end_time']));
			$map['id.deadline'] = array("between",$timespan);
			$search['start_time'] = (urldecode($_REQUEST['start_time']));	
			$search['end_time'] = (urldecode($_REQUEST['end_time']));	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = (urldecode($_REQUEST['start_time']));
			$map['id.deadline'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = (urldecode($_REQUEST['end_time']));
			$map['id.deadline'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}

		$field= 'borrow_id as id,borrow_id as borrow_id,bi.borrow_status as borrow_status,id.borrow_uid as borrow_uid, bi.borrow_name as borrow_name,bi.borrow_money as borrow_money,(bi.repayment_money+bi.repayment_interest) as repaymoney,bi.borrow_duration as borrow_duration,bi.borrow_fee as borrow_fee,bi.repayment_type as repayment_type,sort_order,(sum(capital)+sum(interest)) as need_money,id.deadline as repayment_time';
		$list = M('investor_detail id')->field($field)->join("{$this->pre}borrow_info bi on id.borrow_id = bi.id ")->where($map)->group('id.borrow_id,sort_order')->order(" repayment_time asc")->select();
		$list = $this->_listFilter($list);

		foreach ($list as $k => $v) {
			$vx = M('members')->field('user_name,user_phone')->where(" id={$v['borrow_uid']}  ")->find();
			$list[$k]['user_phone'] = $vx['user_phone'];
			$list[$k]['user_name'] = $vx['user_name'];
		}
		$row=array();
		$row[0]=array('标ID','用户名','手机号','标题','借款金额','已还金额','借款期限','借款管理费','还款方式','期数','还款时间','还款金额');
		$i=1;
		foreach($list as $v){
			$row[$i]['mid'] = $v['id'];
			$row[$i]['user_name'] = $v['user_name'];

			$row[$i]['user_phone'] = $v['user_phone'];
			$row[$i]['borrow_name'] = $v['borrow_name'];
			$row[$i]['borrow_money'] = $v['borrow_money'];

			$row[$i]['repaymoney'] = $v['repaymoney'];
			$row[$i]['borrow_duration'] = $v['borrow_duration'].($v['repayment_type_num'] == 1 ? '天':'个月');
			$row[$i]['borrow_fee'] = $v['borrow_fee'];
			$row[$i]['repayment_type'] = $v['repayment_type'];
			$row[$i]['sort_order'] = $v['sort_order'];
			$row[$i]['repayment_time'] = date('Y-m-d H:i:s',$v['repayment_time']);
			$row[$i]['need_money'] = $v['need_money'];
			$i++;
		}
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("repayment");
	}
    public function borrowbreak()
    {//暂时未处理
		$map['deadline'] = array("exp","<>0 AND deadline<".time()." AND `repayment_money`<`borrow_money`");
		$field= 'id,borrow_name,borrow_uid,borrow_duration,borrow_type,borrow_money,borrow_fee,repayment_money,b.updata,borrow_interest_rate,repayment_type,deadline';
		$this->_list(D('Borrow'),$field,$map,'id','DESC');
        $this->display();
    }
	
	public function unfinish(){
		$map=array();
		$map['b.borrow_status'] = 3;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_status,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.deadline,m.id mid,m.user_name,v.deal_user_2,v.deal_time_2,v.deal_info_2';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->join("{$this->pre}borrow_verify v ON b.id=v.borrow_id")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
	}
	
	
    public function done()
    {
		$map=array();
		$map['b.borrow_status'] = array("in","7,9");
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.repayment_money,b.deadline,m.id mid,m.user_name';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function fail()
    {
		$map=array();
		$map['b.borrow_status'] = 1;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_status,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.add_time,m.user_name,v.deal_user,v.deal_time,m.id mid,v.deal_info';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->join("{$this->pre}borrow_verify v ON b.id=v.borrow_id")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function fail2()
    {
		$map=array();
		$map['b.borrow_status'] = 5;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_status,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.add_time,m.user_name,m.id mid,v.deal_user_2,v.deal_time_2,v.deal_info_2';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->join("{$this->pre}borrow_verify v ON b.id=v.borrow_id")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function _addFilter()
    {
		$typelist = get_type_leve_list('0','acategory');//分级栏目
		$this->assign('type_list',$typelist);
		
    }
	
    public function _editFilter($id)
    {
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$borrow_status = $Bconfig['BORROW_STATUS'];
	 	//$BType = $Bconfig['BORROW_TYPE'];
		switch(strtolower(session('listaction'))){
			case "waitverify":
				for($i=0;$i<=10;$i++){
					if(in_array($i,array("1","2")) ) continue;
					unset($borrow_status[$i]);
				}
			break;
			case "waitverify2":
				for($i=0;$i<=10;$i++){
					if(in_array($i,array("5","6")) ) continue;
					unset($borrow_status[$i]);
				}
			break;
			case "waitmoney":
				for($i=0;$i<=10;$i++){
					if(in_array($i,array("2","3")) ) continue;
					unset($borrow_status[$i]);
				}
			break;
			case "fail":
				unset($borrow_status['3'],$borrow_status['4'],$borrow_status['5']);
			break;
		}
		///////////////////////////////////////////////////////////////////////////////////
		//$danbao = M('article_category')->field('id,type_name')->where("type_name='合作机构资质展示'")->select();
		
		//$sql = M('article')->field("id,title")->where("type_id =7")->select();//"select id,title from lzh_article where type_id =7";
		$danbao = M('article')->field("id,title,art_keyword")->where("type_id =7")->select();//M()->query($sql);
		$dblist = array();
		if(is_array($danbao)){
			foreach($danbao as $key => $v){
				$dblist[$v['id']]=$v['title'];
			}
		}
		$this->assign("danbao_list",$danbao);//新增担保标A+

		//////////////////////////////////////////////////////////////////////////////
		$this->assign('xact',session('listaction'));
		$btype = $Bconfig['REPAYMENT_TYPE'];
		$this->assign("vv",M("borrow_verify")->find($id));
		$this->assign('borrow_status',$borrow_status);
		$this->assign('type_list',$btype);	
		$this->assign('borrow_type',$Bconfig['BORROW_TYPE']);
		//setBackUrl(session('listaction'));	
    }

	public function add_ajax()
	{
		//echo $_POST['id'];
		$find=M("article")->where("id=".$_POST['id'])->field("art_keyword")->find();
		echo $find['art_keyword'];
	}
	public function sRepayment(){
		$borrow_id = $_GET['id'];
		$binfo = M("borrow_info")->field("has_pay,total")->find($borrow_id);
		$from = $binfo['has_pay'] + 1;
		for($i=$from;$i<=$binfo['total'];$i++){
			$res = borrowRepayment($borrow_id,$i,2);
		}
		if($res===true){
			alogs("Repay",0,1,'网站代还成功！');//管理员操作日志
			$this->success("代还成功");
		}elseif(!empty($res)){
			$this->error($res);
		}else{
			alogs("Repay",0,0,'网站代还出错！');//管理员操作日志
			$this->error("代还出错，请重试");
		}
	}

	public function _doAddFilter($m){
		if(!empty($_FILES['imgfile']['name'])){
			$this->saveRule = date("YmdHis",time()).rand(0,1000);
			$this->savePathNew = C('ADMIN_UPLOAD_DIR').'Article/' ;
			$this->thumbMaxWidth = C('ARTICLE_UPLOAD_W');
			$this->thumbMaxHeight = C('ARTICLE_UPLOAD_H');
			$info = $this->CUpload();
			$data['art_img'] = $info[0]['savepath'].$info[0]['savename'];
		}
		if($data['art_img']) $m->art_img=$data['art_img'];
		$m->art_time=time();
		if($_POST['is_remote']==1) $m->art_content = get_remote_img($m->art_content);
		return $m;
	}

	public function doEditWaitverify(){
        $m = D(ucfirst($this->getActionName()));
        if (false === $m->create()) {
            $this->error($m->getError());
        }
		$vm = M('borrow_info')->field('id,borrow_name,reward_num,borrow_money,capital_name,borrow_duration,repayment_type,borrow_interest_rate,borrow_uid,borrow_status,borrow_type,first_verify_time,password,updata,danbao,vouch_money,money_collect,can_auto')->find($m->id);

		$rate_lixt = explode("|",$this->glo['rate_lixi']);
		$borrow_duration = explode("|",$this->glo['borrow_duration']);
		$borrow_duration_day = explode("|",$this->glo['borrow_duration_day']);
		if(floatval($_POST['borrow_interest_rate'])>$rate_lixt[1] || floatval($_POST['borrow_interest_rate'])<$rate_lixt[0]){
			$this->error("提交的借款利率不在允许范围，请重试",0);exit;
		}
		if($m->repayment_type=='1'&&($m->borrow_duration>$borrow_duration_day[1] || $m->borrow_duration<$borrow_duration_day[0])){
			$this->error("提交的借款期限不在允许范围，请去网站设置处重新设置系统参数",0);exit;
		}
		if($m->repayment_type!='1'&&($m->borrow_duration>$borrow_duration[1] || $m->borrow_duration<$borrow_duration[0])){
			$this->error("提交的借款期限不在允许范围，请去网站设置处重新设置系统参数",0);exit;
		}
		
		////////////////////图片编辑///////////////////////
		if(!empty($_POST['swfimglist'])){
			foreach($_POST['swfimglist'] as $key=>$v){
				$row[$key]['img'] = substr($v,1);
				$row[$key]['info'] = $_POST['picinfo'][$key];
			}
			$m->updata=serialize($row);
		}
		////////////////////图片编辑///////////////////////
		
		if($vm['borrow_status']<>2 && $m->borrow_status==2){
		  //新标提醒
			//newTip($m->id);
			MTip('chk8',$vm['borrow_uid'],$m->id);
		  //自动投标
			if($m->borrow_type==1){
				memberLimitLog($vm['borrow_uid'],1,-($m->borrow_money),$info="{$m->id}号标初审通过");
			}elseif($m->borrow_type==2){
				memberLimitLog($vm['borrow_uid'],2,-($m->borrow_money),$info="{$m->id}号标初审通过");
			}
			$vss = M("members")->field("user_phone,user_name")->where("id = {$vm['borrow_uid']}")->find();
			SMStip("firstV",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$m->id));
		}
		//if($m->borrow_status==2) $m->collect_time = strtotime("+ {$m->collect_day} days");
		if($m->borrow_status==2){ 
			$m->collect_time = strtotime("+ {$m->collect_day} days");
			$m->reward_num = ($_POST['reward_num']);
			//$m->is_tuijian = 1;
		}
		$m->money_invest_place = intval($_POST['money_invest_place']);
		$m->borrow_interest = getBorrowInterest($m->repayment_type,$m->borrow_money,$m->borrow_duration,$m->borrow_interest_rate);
        //保存当前数据对象
		if($m->borrow_status==2 || $m->borrow_status==1) $m->first_verify_time = time();
		else unset($m->first_verify_time);
		unset($m->borrow_uid);
		$bs = intval($_POST['borrow_status']);

		//新加接口数据准备
		$toId = M('member_to')->where(array('username'=>array('like',$vm['capital_name'])))->getField('id');
		if(empty($toId)){
			$toId = M('member_to')->add(array('username'=>$vm['capital_name']));
		}
		//按天到期还款、一次性还款    4
		//按月分期还款  3
		//每月还息到期还本  1
		$lilv = array(
			1=>4,
			5=>4,
			2=>3,
			4=>1,
		);
		$yottarr = array(
			'subject_id' => $m->id,//*标识唯一id
			'is_test' => '0',//是否为测试数据：默认为0，1为测试数据，必填
			'type' => 0,//*数据类型：0为标，1为 基金
			'name' => $m->borrow_name,//*产品名称
			'borrow_type' => '1',//*借款担保方式：1 抵押借款 2 信用借款 3 质押借款 4 第三方担保
			'desc' => $m->borrow_info,//产品描述（仅限文字）
			'reward' => $vm['reward_num'],  //借款奖励
			'reward_type' => '1',//奖励方式：1百分比，2固定金额
			'url' => MU("Home/invest","invest",array("id"=>$m->id,"suffix"=>C("URL_HTML_SUFFIX")),true),//链接地址 该值为接入方的标的地址
			'borrow_username' => $toId,//借款人名称
			'account' => $m->borrow_money,//募集金额
			'period' => $m->borrow_duration,        //借款期限（月或天）
			'period_type' => 1 == $m->repayment_type ? 1 : 0,   //产品周期：0为月，1为天
			'apr' => $m->borrow_interest_rate,          //年收益率(%)， 不要带 %，例：18
			'repay_style' => $lilv[$m->repayment_type],
			'status' => '2',//2（借款中）
			'addtime' => time()//发布时间 （时间磋）
		);


		//结束新加

        if ($result = $m->save()) { //保存成功
			if(2 == $bs && $this->yott()){
				$yott = new yott();
				$res = json_decode($yott->createP2p($yottarr));
				//记录日志
				M('yott_log')->add(
					array(
						'dateline'=>time(),
						'apitype'=>'createp2p',
						'apidata'=>json_encode($yottarr),
						'code'=>$res->code,
						'msg'=>$res->msg
					)
				);
			}
			if($bs==2 || $bs==1){
				$verify_info['borrow_id'] = intval($_POST['id']);
				$verify_info['deal_info'] = text($_POST['deal_info']);
				$verify_info['deal_user'] = $this->admin_id;
				$verify_info['deal_time'] = time();
				$verify_info['deal_status'] = $bs;
				if($vm['first_verify_time']>0) M('borrow_verify')->save($verify_info);
				else  M('borrow_verify')->add($verify_info);
			}
			if($vm['borrow_status']<>2 && $_POST['borrow_status']==2 && $vm['can_auto']==1 && empty($vm['password'])==true) {
				if($vm['borrow_type'] <> 3){
					autoInvest(intval($_POST['id']));
				}
			}
			//if($vm['borrow_status']<>2 && $_POST['borrow_status']==2)) autoInvest(intval($_POST['id']));
			alogs("doEditWait",$result,1,'初审操作成功！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
			alogs("doEditWait",$result,0,'初审操作失败！');//管理员操作日志
            //失败提示
            $this->error(L('修改失败'));
		}	
	}

	public function doEditWaitverify2(){
        $m = D(ucfirst($this->getActionName()));
        if (false === $m->create()) {
            $this->error($m->getError());
        }
		$vm = M('borrow_info')->field('borrow_uid,borrow_money,borrow_status,first_verify_time,updata,danbao,vouch_money,borrow_fee,borrow_interest_rate,borrow_duration,repayment_type,collect_day,collect_time,money_collect')->find($m->id);

		if($vm['borrow_money']<>$m->borrow_money ||
			 $vm['borrow_interest_rate']<>$m->borrow_interest_rate ||
			 $vm['borrow_duration']<>$m->borrow_duration ||
			 $vm['repayment_type']<>$m->repayment_type ||
			 $vm['borrow_fee'] <> $m->borrow_fee
		  ){
			$this->error('复审中的借款不能再更改‘还款方式’，‘借款金额’，‘年化利率’，‘借款期限’,‘借款管理费’');
			exit;
		}


		if($m->borrow_status<>5 && $m->borrow_status<>6){
			$this->error('已经满标的的借款只能改为复审通过或者复审未通过');
			exit;
		}

		////////////////////图片编辑///////////////////////
		if(!empty($_POST['swfimglist'])){
			foreach($_POST['swfimglist'] as $key=>$v){
				$row[$key]['img'] = substr($v,1);
				$row[$key]['info'] = $_POST['picinfo'][$key];
			}
			$m->updata=serialize($row);
		}
		////////////////////图片编辑///////////////////////
		//复审投标检测
		//$capital_sum1=M('investor_detail')->where("borrow_id={$m->id}")->sum('capital');


		//$capitallist=M('investor_detail d')->join(' members m on investor_uid=m.id ')->where("borrow_id={$m->id}")->Field('investor_uid')->select();


		$capital_sum2=M('borrow_investor')->where("borrow_id={$m->id}")->sum('investor_capital');
		if(($vm['borrow_money']!=$capital_sum2)){
			$this->error('投标金额不统一，请确认！');
			exit;
		}
		if($m->borrow_status==6){//复审通过



			$appid = borrowApproved($m->id);
			if(!$appid) $this->error("复审失败");
		  
			MTip('chk9',$vm['borrow_uid'],$m->id);
			
			$vss = M("members")->field("user_phone,user_name")->where("id = {$vm['borrow_uid']}")->find();

			//回款续投奖励
			$capitallist=M('investor_detail d')->join($this->pre.'members m on d.investor_uid=m.id ')->Field('d.rewardid,d.borrow_id,m.id,m.user_phone,m.user_name,d.capital')->where("d.borrow_id={$m->id} and d.capital>0")->select();
			$smsTxt = (array)FS("Webconfig/smstxt");
			foreach($capitallist as $val){
				/**
				if($val['rewardid']){
					$save_reward = M('today_reward')->Field('reward_money,invest_money,borrow_id')->where('id='.$val['rewardid'].' and status=0')->find();
					if(0 < $save_reward['reward_money']){
						$res = membermoneylog($val['id'],33,$save_reward['reward_money'],"续投有效金额({$save_reward['invest_money']})的奖励({$save_reward['borrow_id']}号标)预奖励",0,"@网站管理员@");
						if($res){
							M('today_reward')->data(array(
								'id'=>$val['rewardid'],
								'status'=>1
							))->save();
						}
					}

				}
				 ***/
				if(false == empty($val['user_phone'])){
					$txt = preg_replace(array("/#USERANEM#/","/#ID#/"),array($val['user_name'],$m->id),$smsTxt['BidV']['content']);
					sendsms($val['user_phone'],$txt);
				}
			}

			SMStip("approve",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$m->id));
		}elseif($m->borrow_status==5){//复审未通过
			$appid = borrowRefuse($m->id,3);
			if(!$appid) $this->error("复审失败");
			MTip('chk12',$vm['borrow_uid'],$m->id);
		}
        //保存当前数据对象
		$m->second_verify_time = time();
		unset($m->borrow_uid);
		$bs = intval($_POST['borrow_status']);
        if ($result = $m->save()) { //保存成功
				$verify_info['borrow_id'] = intval($_POST['id']);
				$verify_info['deal_info_2'] = text($_POST['deal_info_2']);
				$verify_info['deal_user_2'] = $this->admin_id;
				$verify_info['deal_time_2'] = time();
				$verify_info['deal_status_2'] = $bs;
				if($vm['first_verify_time']>0) M('borrow_verify')->save($verify_info);
				else  M('borrow_verify')->add($verify_info);
			alogs("borrowApproved",$result,1,'复审操作成功！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
			alogs("borrowApproved",$result,0,'复审操作失败！');//管理员操作日志
            //失败提示
            $this->error(L('修改失败'));
		}	
	}

	public function doEditWaitmoney(){
        $m = D(ucfirst($this->getActionName()));
        if (false === $m->create()) {
            $this->error($m->getError());
        }

		$vm = M('borrow_info')->field('borrow_uid,borrow_type,borrow_money,first_verify_time,borrow_interest_rate,borrow_duration,repayment_type,collect_day,collect_time,borrow_fee,money_collect')->find($m->id);
		if($vm['borrow_money']<>$m->borrow_money ||
			 $vm['borrow_interest_rate']<>$m->borrow_interest_rate ||
			 $vm['borrow_duration']<>$m->borrow_duration ||
			 //$vm['borrow_type']<>$m->borrow_type ||
			 $vm['repayment_type']<>$m->repayment_type ||
			 $vm['borrow_fee'] <> $m->borrow_fee
		  ){
			$this->error('招标中的借款不能再更改‘还款方式’，‘借款种类’，‘借款金额’，‘年化利率’，‘借款期限’,‘借款管理费’');
			exit;
		}

		//招标中的借款流标
		if($m->borrow_status==3){
			alogs("borrowRefuse",0,1,'流标操作成功！');//管理员操作日志
			//流标返回
			$appid = borrowRefuse($m->id,2);
			if(!$appid) {
				alogs("borrowRefuse",0,0,'流标操作失败！');//管理员操作日志
				$this->error("流标失败");
			}
			MTip('chk11',$vm['borrow_uid'],$m->id);
			$m->second_verify_time = time();
			//流标操作相当于复审
			$verify_info['borrow_id'] = $m->id;
			$verify_info['deal_info_2'] = text($_POST['deal_info_2']);
			$verify_info['deal_user_2'] = $this->admin_id;
			$verify_info['deal_time_2'] = time();
			$verify_info['deal_status_2'] = $m->borrow_status;
			if($vm['first_verify_time']>0) M('borrow_verify')->save($verify_info);
			else  M('borrow_verify')->add($verify_info);
			
			$vss = M("members")->field("user_phone,user_name")->where("id = {$vm['borrow_uid']}")->find();
			SMStip("refuse",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$m->id));

		}else{
			if($vm['collect_day'] < $m->collect_day){
				$spanday = $m->collect_day-$vm['collect_day'];
				$m->collect_time = strtotime("+ {$spanday} day",$vm['collect_time']);
			}
			unset($m->second_verify_time);	
		}
		
        //保存当前数据对象
 		unset($m->borrow_uid);
		////////////////////图片编辑///////////////////////
		foreach($_POST['swfimglist'] as $key=>$v){
			$row[$key]['img'] = substr($v,1);
			$row[$key]['info'] = $_POST['picinfo'][$key];
		}
		$m->updata = false === empty($row) ? serialize($row) : '';
		$result = $m->save();
		////////////////////图片编辑///////////////////////
       if ($result) { //保存成功
	   		//$this->assign("waitSecond",10000);
			alogs("borrowing",0,1,'招标中的借款操作修改成功！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
			alogs("borrowing",0,0,'招标中的借款操作修改失败！');//管理员操作日志
            //失败提示
            $this->error(L('修改失败'));
		}	
	}
	public function doeditwaitrepay(){
        $m = D(ucfirst($this->getActionName()));

        if (false === $m->create()) {
            $this->error($m->getError());
        }
        //保存当前数据对象
 		unset($m->borrow_uid);
		////////////////////图片编辑///////////////////////
		foreach($_POST['swfimglist'] as $key=>$v){
			$row[$key]['img'] = substr($v,1);
			$row[$key]['info'] = $_POST['picinfo'][$key];
		}
		$m->updata = false === empty($row) ? serialize($row) : '';

		$result = $m->save();
		////////////////////图片编辑///////////////////////
       if ($result) { //保存成功
	   		//$this->assign("waitSecond",10000);
			alogs("borrowing",0,1,'还款中修改图片成功！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
			alogs("borrowing",0,0,'还款中修改图片失败！');//管理员操作日志
            //失败提示
            $this->error(L('修改失败'));
		}	
	}

	public function doEditFail(){
        $m = D(ucfirst($this->getActionName()));
        if (false === $m->create()) {
            $this->error($m->getError());
        }
		$vm = M('borrow_info')->field('borrow_uid,borrow_status')->find($m->id);
		if($vm['borrow_status']==2 && $m->borrow_status<>2){
			$this->error('已通过审核的借款不能改为别的状态');
			exit;
		}
		
		foreach($_POST['updata_name'] as $key=>$v){
			$updata[$key]['name'] = $v;
			$updata[$key]['time'] = $_POST['updata_time'][$key];
		}
		$m->borrow_interest = getBorrowInterest($m->repayment_type,$m->borrow_money,$m->borrow_duration,$m->borrow_interest_rate);
		$m->updata = serialize($updata);
		$m->collect_time = strtotime($m->collect_time);
        //保存当前数据对象
        if ($result = $m->save()) { //保存成功
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
            //失败提示
            $this->error(L('修改失败'));
		}	
	}
	
	
	public function _AfterDoEdit(){
		switch(strtolower(session('listaction'))){
			case "waitverify":
				$v = M('borrow_info')->field('borrow_uid,borrow_status,deal_time')->find(intval($_POST['id']));
				if(empty($v['deal_time'])){
					$newid = M('members')->where("id={$v['borrow_uid']}")->setInc('credit_use',floatval($_POST['borrow_money']));
					if($newid) M('borrow_info')->where("id={$v['borrow_uid']}")->setField('deal_time',time());
				}
				//$vss = M("members")->field("user_phone,user_name")->where("id = {$v['borrow_uid']}")->find();
				//SMStip("firstV",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],intval($_POST['id'])));
				//$this->assign("waitSecond",1000);
				//Notice();
			break;
		}
	}
	
	public function _listFilter($list){
		session('listaction',ACTION_NAME);
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
	 	$listType = $Bconfig['REPAYMENT_TYPE'];
	 	$BType = $Bconfig['BORROW_TYPE'];
		$row=array();
		$aUser = get_admin_name();
		foreach($list as $key=>$v){
			$v['repayment_type_num'] = $v['repayment_type'];
			$v['repayment_type'] = $listType[$v['repayment_type']];
			$v['borrow_type'] = $BType[$v['borrow_type']];
			if($v['deadline']) $v['overdue'] = getLeftTime($v['deadline']) * (-1);
			if($v['borrow_status']==1 || $v['borrow_status']==3 || $v['borrow_status']==5){
				$v['deal_uname_2'] = $aUser[$v['deal_user_2']];
				$v['deal_uname'] = $aUser[$v['deal_user']];
			}

			$v['last_money'] = $v['borrow_money']-$v['has_borrow'];//新增剩余金额
			if($v['is_auto']==1){
				$v['is_auto']="自动投标";
			}else{
				$v['is_auto']="手动投标";
			}
			
			$row[$key]=$v;
		}
		return $row;
	}
	
	
	 public function doweek()
    {
		$map=array();
		$map['bi.borrow_status'] = 6;//还款中
		$map['id.status'] = 7;//还款中
		if(!empty($_REQUEST['isShow'])){
			$week_1 = array(strtotime(date("Y-m-d",time())." 00:00:00"),strtotime("+6 day",strtotime(date("Y-m-d",time())." 23:59:59")));//一周内
			$map['id.deadline'] = array("between",$week_1);
		}
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['bi.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['bi.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['bi.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['id.deadline'] = array("between",$timespan);
			$search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));	
			$search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['id.deadline'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['id.deadline'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		
		if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
			$map['m.customer_id'] = $_REQUEST['customer_id'];
			$search['customer_id'] = $map['m.customer_id'];	
			$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
		}
		
		if($_REQUEST['customer_name'] && !$search['customer_id']){
			$cusname = urldecode($_REQUEST['customer_name']);
			$kfid = M('ausers')->getFieldByUserName($cusname,'id');
			$map['m.customer_id'] = $kfid;
			$search['customer_name'] = $cusname;	
			$search['customer_id'] = $kfid;	
		}
		
		import("ORG.Util.Page");
		$count = M('investor_detail id')->join("{$this->pre}borrow_info bi on id.borrow_id = bi.id ")->where($map)->group('id.borrow_id,sort_order')->select();
		$p = new Page(count($count), C('ADMIN_PAGE_SIZE'));
		$page = $p->show();

		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'borrow_id as id,borrow_id as borrow_id,bi.borrow_status as borrow_status,id.borrow_uid as borrow_uid, bi.borrow_name as borrow_name,bi.borrow_money as borrow_money,(bi.repayment_money+bi.repayment_interest) as repaymoney,bi.borrow_duration as borrow_duration,bi.borrow_fee as borrow_fee,bi.repayment_type as repayment_type,sort_order,(sum(capital)+sum(interest)) as need_money,id.deadline as repayment_time';
		$list = M('investor_detail id')->field($field)->join("{$this->pre}borrow_info bi on id.borrow_id = bi.id ")->where($map)->group('id.borrow_id,sort_order')->limit($Lsql)->order(" repayment_time asc")->select();
		$list = $this->_listFilter($list);

		foreach ($list as $k => $v) {
			$vx = M('members')->field('user_name,user_phone')->where(" id={$v['borrow_uid']}  ")->find();
			$list[$k]['user_phone'] = $vx['user_phone'];
			$list[$k]['user_name'] = $vx['user_name'];
		}
		
		
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	//swf上传图片
	public function swfupload(){
		if($_POST['picpath']){
			$imgpath = substr($_POST['picpath'],1);
			if(in_array($imgpath,$_SESSION['imgfiles'])){
					 unlink(C("WEB_ROOT").$imgpath);
					 $thumb = get_thumb_pic($imgpath);
				$res = unlink(C("WEB_ROOT").$thumb);
				if($res) $this->success("删除成功","",$_POST['oid']);
				else $this->error("删除失败","",$_POST['oid']);
			}else{
				$this->error("图片不存在","",$_POST['oid']);
			}
		}else{
			$this->savePathNew = C('ADMIN_UPLOAD_DIR').'Product/' ;
			$this->thumbMaxWidth = C('PRODUCT_UPLOAD_W');
			$this->thumbMaxHeight = C('PRODUCT_UPLOAD_H');
			$this->saveRule = date("YmdHis",time()).rand(0,1000);
			$info = $this->CUpload();
			$data['product_thumb'] = $info[0]['savepath'].$info[0]['savename'];
			if(!isset($_SESSION['count_file'])) $_SESSION['count_file']=1;
			else $_SESSION['count_file']++;
			$_SESSION['imgfiles'][$_SESSION['count_file']] = $data['product_thumb'];
			echo "{$_SESSION['count_file']}:".__ROOT__."/".$data['product_thumb'];//返回给前台显示缩略图
		}
	}
	
	//人工处理满标但未进入复审列表的数据
	public function dowaitMoneyComplete(){
		$pre = C('DB_PREFIX');
		$borrow_id = $_REQUEST['id'];
		$upborrowsql = "update `{$pre}borrow_info` set ";
		$upborrowsql .= "`borrow_status`= 4,`full_time`=".time();
		$upborrowsql .= " WHERE `id`={$borrow_id}";
		
		$result = M()->execute($upborrowsql);
		if($result) {
			alogs("dowaitMoneyComplete",0,1,'人工处理满标但未进入复审列表的数据操作成功！');//管理员操作日志
			$this->success("处理成功");
			$this->assign('jumpUrl', __URL__."/".session('listaction'));
		}else{
			alogs("dowaitMoneyComplete",0,0,'人工处理满标但未进入复审列表的数据操作失败！');//管理员操作日志
			$this->error("处理失败");
			$this->assign('jumpUrl', __URL__."/".session('listaction'));
		}
	}
	
	//邮件提醒
	  public function tip() {
	  	$id = intval($_REQUEST['id']);
		$vm = M('borrow_info')->field('borrow_uid,borrow_name,borrow_money,repayment_type,deadline')->find($id);
		$borrowName = $vm['borrow_name'];
		$borrowMoney = $vm['borrow_money'];
		if($id){
			Notice(9,$vm['borrow_uid'],array('id'=>$id,'borrowName'=>$borrowName,'borrowMoney'=>$borrowMoney));
			ajaxmsg();
		}
		else ajaxmsg('',0);
	}
	
	//每个借款标的投资人记录
	 public function doinvest()
    {
		$borrow_id = intval($_REQUEST['borrow_id']);
		$map=array();
		
		$map['bi.borrow_id'] = $borrow_id;
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_investor bi')->join("{$this->pre}members m ON m.id=bi.investor_uid")->where($map)->count('bi.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'bi.id bid,b.id,bi.investor_capital,bi.investor_interest,bi.ent,bi.invest_fee,bi.add_time,bi.is_auto,m.user_name,m.id mid,m.user_phone,b.borrow_duration,b.repayment_type,m.customer_name,b.borrow_type,b.borrow_name';
		$list = M('borrow_investor bi')->field($field)->join("{$this->pre}members m ON m.id=bi.investor_uid")->join("{$this->pre}borrow_info b ON b.id=bi.borrow_id")->where($map)->limit($Lsql)->order("bi.id DESC")->select();
		$list = $this->_listFilter($list);
		
		//dump($list);exit;
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->display();
    }

	
	/////////////////////////////////////新增未满标的人工满标应急处理  2014-06-13 fan 开始//////////////////////////////////
	 public function borrowfull(){
		$map=array();
		$map['b.borrow_status'] = 2;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
			$map['m.customer_id'] = $_REQUEST['customer_id'];
			$search['customer_id'] = $map['m.customer_id'];	
			$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
		}
		
		if($_REQUEST['customer_name'] && !$search['customer_id']){
			$cusname = urldecode($_REQUEST['customer_name']);
			$kfid = M('ausers')->getFieldByUserName($cusname,'id');
			$map['m.customer_id'] = $kfid;
			$search['customer_name'] = $cusname;	
			$search['customer_id'] = $kfid;	
		}
		
		$map['b.borrow_min'] = array("exp","> (b.borrow_money-b.has_borrow)");
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,m.user_name,m.id mid,b.is_tuijian,b.has_borrow,b.money_collect,b.borrow_min';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		
		$list = $this->_listFilter($list);
		$vo = M('members')->field("id,user_name")->select();//查询出所有会员
		$userlist = array();
		if(is_array($vo)){
			foreach($vo as $key => $v){
				foreach($list as $key1 => $v1){
					if($v['id']!=$v1['borrow_uid']){
						$userlist[$v['id']]=$v['user_name'];
					}
				}
			}
		}
		$this->assign("userlist",$userlist);//流转会员
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
	//人工处理低于最小投标金额无法正常满标的情况
	
	public function doMoneyComplete(){
		$pre = C('DB_PREFIX');
		$borrow_id = $_REQUEST['id'];
		$money = intval($_POST['lastmoney']);
		$uid = $_REQUEST['uid'];
		if(empty($uid)){
			$this->error("请选择投资人");
		}
		$vm = M('borrow_info')->field('borrow_status')->find($borrow_id);
		if(($vm['borrow_status']!=2)){
			$this->error('该标借款状态不在借款中，无法执行满标处理！');
			exit;
		}else{
			$done = investMoney($uid,$borrow_id,$money);
			if($done===true) {
				alogs("doMoneyComplete",0,1,'人工处理低于最小投标金额无法正常满标的数据操作成功！');//管理员操作日志
				$this->success("恭喜成功投标{$money}元");
			}else if($done){
				$this->error($done);
			}else{
				alogs("doMoneyComplete",0,1,'人工处理低于最小投标金额无法正常满标的数据操作成功！');//管理员操作日志
				$this->error("对不起，投标失败，请重试!");
			}
		}
	}
 	/////////////////////////////////////新增未满标的人工满标应急处理  2014-06-13 fan 结束//////////////////////////////////


	private function yott(){
		return false;//关闭YOTT
		//return import('App.Api.yott');
	}
	public function edit_repay() {
        $model = D(ucfirst($this->getActionName()));
        $id = intval($_REQUEST['id']);
        $vo = $model->find($id);
        $this->assign('vo', $vo);
        $this->display();
    }
}
?>