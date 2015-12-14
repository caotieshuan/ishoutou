<?php
/*	股权配资免费体验控制器
 *	@author:Bob
 *	@time:2015/4/9
 */
class FreestockAction extends MCommonAction {

	//进行中的配资列表
	public function tendbacking(){
		import("ORG.Util.Page");
		$status = array(1=>"待审核",2=>"进行中",3=>"已平仓");
		$this->assign("status",$status);
		$map['type_id'] = 4;
		$map['status'] = array("in","1,2,3");
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
	
}