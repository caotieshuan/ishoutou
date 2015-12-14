<?php
/*	配资专员记录控制器
 *	@author:Bob
 *	@time:2015/4/20
 */
class StockdetailAction extends ACommonAction{
	public function index() {
		import("ORG.Util.Page");
		$map['status'] = array("in","2,3,6");
		if($_GET['id']) {
			$map['stock_admin_id'] = $_GET['id'];
		}else {
			$map['stock_admin_id'] = session("admin_id");
		}
		
		$stock_type = M("ausers")->where("id = {$map['stock_admin_id']}")->field("is_stock")->find();
		if($stock_type['is_stock'] == 2) {
			$parent = M("ausers")->where("stock_parent = {$map['stock_admin_id']}")->field("id")->select();
			if($parent) {
				foreach($parent as $v) {
					$parentin[] = $v['id'];
				}
				$parentin = implode(",",$parentin);
				unset($map);
				$map['stock_admin_id'] = array("in",$parentin);
			}
		}
		
		$count = M("shares_apply")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$sum = M("shares_apply")->where($map)->sum("already_manage_fee");
		$commission = M("ausers")->where("id = {$map['stock_admin_id']}")->getField("commission");
		$sum = $sum * $this->glo['commision_ratio'] / 100;
		if($stock_type['is_stock'] == 2) {
			$sum = $sum * $this->glo['commision_ratio'] / 100;
		}
		$list = M("shares_apply")->where($map)->limit($Lsql)->select();
		$this->assign("commission",$commission);
		$this->assign("sum",$sum);
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
	}
	
	public function financeDetail() {
		import("ORG.Util.Page");
		$map['is_stock'] = array("in","1,2");
		$count = M("ausers")->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M("ausers")->where($map)->limit($Lsql)->select();
		foreach($list as $k=>$v) {
			if($v['is_stock'] == 2) {
				$com = getComsById($v['id']);
				$com = $com * $this->glo['commision_ratio'] / 100;
				$com = $com * $this->glo['commision_ratio'] / 100;
				$list[$k]['all_commission'] = $com;
			}else {
				$com = getComById($v['id']);
				$com = $com * $this->glo['commision_ratio'] / 100;
				$list[$k]['all_commission'] = $com;
			}
		}
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->display();
	}
	
	public function detailgrant() {
		if($_POST['id']) {
			$mod = M("ausers")->find($_POST['id']);
			$savedata['id'] = $_POST['id'];
			$savedata['commission'] = $mod['commission'] + $_POST['commission'];
			M("ausers")->save($savedata);
			$this->success("清算成功!");
		}else {
			$this->display();
		}
	}
}
?>