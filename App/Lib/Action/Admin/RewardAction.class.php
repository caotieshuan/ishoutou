<?php
// 全局设置
class RewardAction extends ACommonAction
{
	var $justlogin = true;

	/**
    +----------------------------------------------------------
    * 默认操作 待审核提现
    +----------------------------------------------------------
    */
	public function index()
	{
        $this->display();
    }
	//编辑
    public function Today()
	{
		$map=array();
		if($_REQUEST['username']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['username'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);
		}
		if($_REQUEST['borrow_id']){
			$map['r.borrow_id'] = intval($_REQUEST['borrow_id']);
			$search['borrow_id'] = $map['r.borrow_id'];
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['r.deal_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);
			$search['end_time'] = urldecode($_REQUEST['end_time']);
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['r.deal_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['r.deal_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;
		}
		//分页处理
		import("ORG.Util.Page");
		$count = M('today_reward')->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$map['reward_status'] = 1;
		$list = M('today_reward r')->where($map)->join(C('DB_PREFIX')."members m ON r.reward_uid=m.id")->field('r.payment,m.user_name,m.id as uid,r.id,r.reward_money,r.invest_money,r.deal_time,r.borrow_id')->limit($Lsql)->order('add_time desc')->select();

		$this->assign("query", http_build_query($search));
		$this->assign("list", $list);
		$this->assign("pagebar", $page);
		$this->display();
	}
}
?>