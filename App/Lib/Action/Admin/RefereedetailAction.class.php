<?php
// 全局设置
class RefereedetailAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function indexbak()
    {
		$this->pre = C('DB_PREFIX');
		$map=array();
		if(!empty($_REQUEST['runame'])){
			$ruid = M("members")->getFieldByUserName(text($_REQUEST['runame']),'id');
			$map['m.recommend_id'] = $ruid;
		}else{
			$map['m.recommend_id'] =array('neq','0');
		}
		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['bi.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		if(session('admin_is_kf')==1 && m.customer_id!=0)	$map['m.customer_id'] = session('admin_id');
		//分页处理
		import("ORG.Util.Page");
		$xcount =M('borrow_investor bi')->join("{$this->pre}members m ON m.id = bi.investor_uid")->where($map)->group('bi.investor_uid')->buildSql();
		$newxsql = M()->query("select count(*) as tc from {$xcount} as t");
		$count = $newxsql[0]['tc'];

		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		
		$field= ' sum(bi.investor_capital) investor_capital,count(bi.id) total,bi.investor_uid,m.recommend_id,m.id,m.user_name';
		$list = M('borrow_investor bi')->join("{$this->pre}members m ON m.id = bi.investor_uid")->field($field)->where($map)->group('bi.investor_uid')->limit($Lsql)->select();

		$tfield= ' sum(bi.investor_capital) investor_capital,count(bi.id) total,bi.investor_uid,m.recommend_id,m.id,m.user_name';
		$tlist = M('transfer_borrow_investor bi')->join("{$this->pre}members m ON m.id = bi.investor_uid")->field($tfield)->where($map)->group('bi.investor_uid')->limit($Lsql)->find();
		
		foreach($list as $key => $v)
		{
			$list[$key]['investor_capital'] = $v['investor_capital']+$tlist['investor_capital'];
			$list[$key]['total'] = $v['total']+$tlist['total'];		
		}
	
		$list=$this->_listFilter($list);
		
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }

	public function index()
	{
		$this->pre = C('DB_PREFIX');
		$map=array();
		if(!empty($_REQUEST['runame'])){
			$umap['user_name'] = array("like",($_REQUEST['runame'])."%");
			$ruidlist = M("members")->where($umap)->field('id')->select();
			$ruid = array();
			foreach($ruidlist as $v){
				$ruid[]=$v['id'];
			}
			$map['bi.recommend'] = array("in",$ruid);
		}
		if($_REQUEST['uname']){
			$umap['user_name'] = array("like",($_REQUEST['uname'])."%");
			$ruidlist = M("members")->where($umap)->field('id')->select();
			$ruid = array();
			foreach($ruidlist as $v){
				$ruid[]=$v['id'];
			}
			$map['bi.uid'] = array("in",$ruid);
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.dateline'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);
			$search['end_time'] = urldecode($_REQUEST['end_time']);
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['bi.dateline'] = array("gt",$xtime);
			$search['start_time'] = $xtime;
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.dateline'] = array("lt",$xtime);
			$search['end_time'] = $xtime;
		}

		import("ORG.Util.Page");
		$count = M('recommendlog bi')->join("{$this->pre}members m ON m.id = bi.recommend")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";



		$list=M('recommendlog bi')->where($map)->field('bi.*,m.user_name as username ,cm.user_name as cusername')->join("{$this->pre}members m ON m.id = bi.recommend")->join("{$this->pre}members cm ON cm.id = bi.uid")->order('dateline desc')->limit($Lsql)->select();




		$this->assign("list", $list);
		$this->assign("pagebar", $page);
		$this->assign("query", http_build_query($search));
		$this->display();
	}
	
	
	public function _listFilter($list){
		$row=array();
		foreach($list as $key=>$v){
			 if($v['recommend_id']<>0){
				$v['recommend_name'] = M("members")->getFieldById($v['recommend_id'],"user_name");
			 }else{
				$v['recommend_name'] ="<span style='color:red'>无推荐人</span>";
			 }
			 $row[$key]=$v;
		 }
		return $row;
	}
	
	public function export(){
		import("ORG.Io.Excel");
		$this->pre = C('DB_PREFIX');
		$map=array();
		if(!empty($_REQUEST['runame'])){
			$umap['user_name'] = array("like",($_REQUEST['runame'])."%");
			$ruidlist = M("members")->where($umap)->field('id')->select();
			$ruid = array();
			foreach($ruidlist as $v){
				$ruid[]=$v['id'];
			}
			$map['bi.recommend'] = array("in",$ruid);
		}
		if($_REQUEST['uname']){
			$umap['user_name'] = array("like",($_REQUEST['uname'])."%");
			$ruidlist = M("members")->where($umap)->field('id')->select();
			$ruid = array();
			foreach($ruidlist as $v){
				$ruid[]=$v['id'];
			}
			$map['bi.uid'] = array("in",$ruid);
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.dateline'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);
			$search['end_time'] = urldecode($_REQUEST['end_time']);
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['bi.dateline'] = array("gt",$xtime);
			$search['start_time'] = $xtime;
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.dateline'] = array("lt",$xtime);
			$search['end_time'] = $xtime;
		}

		import("ORG.Util.Page");
		$count = M('recommendlog bi')->join("{$this->pre}members m ON m.id = bi.recommend")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";



		$list=M('recommendlog bi')->where($map)->field('bi.*,m.user_name as username ,cm.user_name as cusername')->join("{$this->pre}members m ON m.id = bi.recommend")->join("{$this->pre}members cm ON cm.id = bi.uid")->order('dateline desc')->select();



		$row=array();
		$row[0]=array('序号','邀请人用户名	','被邀请人用户名','被邀请人投标ID','首次总额超过	','奖励金额','发生时间');
		$i=1;
		foreach($list as $v){
				$row[$i]['i'] = $i;
				$row[$i]['username'] = $v['username'];
				$row[$i]['cusername'] = $v['cusername'];
				$row[$i]['bid'] = $v['bid'];
				$row[$i]['levelnums'] = $v['levelnums'];
				$row[$i]['money'] = $v['money'];
				$row[$i]['dateline'] = date('Y-m-d H:i:s',$v['dateline']);
				$i++;
		}
		
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("datalistcard");
	}


	
}
?>