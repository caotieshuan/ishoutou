<?php
// 管理员管理
class StockuserAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
        import("ORG.Util.Page");
		
		$AdminU = M('ausers');
		$page_size = ($page_szie==0)?C('ADMIN_PAGE_SIZE'):$page_szie;
		
		
		$count  = $AdminU->count(); // 查询满足要求的总记录数   
		$Page = new Page($count,$page_size); // 实例化分页类传入总记录数和每页显示的记录数   
		$show = $Page->show(); // 分页显示输出
		   
		$fields = "id,user_name,u_group_id,real_name,is_ban,area_name,is_kf,qq,phone,user_word";
		$order = "id DESC,u_group_id DESC";
		
		$list = $AdminU->field($fields)->where("is_stock = 1")->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();

		$AdminUserList = $list;
		
		$GroupArr = get_group_data();
		foreach($AdminUserList as $key => $v){
			$AdminUserList[$key]['groupname'] = $GroupArr[$v['u_group_id']]['groupname'];
		}

		$this->assign('position', '管理员管理');
		$this->assign('pagebar', $show);
		$this->assign('admin_list', $AdminUserList);
		$this->assign('group_list', $GroupArr);
        $this->display();
    }
	
	public function doupdate(){
		
		$aid = $_POST['aid'];
		$AdminU = M('ausers');
		$fields = "id,user_name,u_group_id,real_name,is_ban,area_name,is_kf,qq,phone,user_word,is_stock,invitation_code";
		$list = $AdminU->field($fields)->where("id = {$aid}")->find();
		$this->assign("vo",$list);
		$this->display();
	}
	
	public function code(){
		
		$code = Getstockcode($_GET['aid']);
		echo $code;
	}
	
	public function addAdmin(){
		
		$aid = $_POST['aid'];
		$data = array();
		$data['user_name'] = $_POST['user_name'];
		$data['user_pass'] = md5($_POST['user_pass']);
		$data['real_name'] = $_POST['real_name'];
		$data['is_ban'] = $_POST['is_ban'];
		$data['user_word'] = $_POST['user_word'];
		$data['invitation_code'] = $_POST['invitation_code'];
		
		$ret = M('ausers')->where("id = {$aid}")->save($data);
		//echo M()->getlastsql();die;
		if($ret){
			alogs("addAdmin",1,'管理员更新配资专员信息成功！');//管理员操作日志
			$this->success("更新成功！");
		}else{
			
			alogs("addAdmin",1,'管理员更新配资专员信息失败！');//管理员操作日志
			$this->error("更新失败！");
		}
	}



}
?>
