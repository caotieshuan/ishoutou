<?php
// 全局设置
class StockglobalAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function websetting()
    {
		$list = M('shares_global')->where("type_id = 1")->order("order_sn DESC")->select();
		
		
		$this->assign('list', de_xie($list));
        $this->display();
    }
	public function doAdd(){
		$glo = D('shares_global');
		
		if($glo->create()) {
			$newid = $glo->add();
			if($newid) $this->success('修改成功');
			else $this->error('修改失败');
		}else{
			$this->error($glo->getError());
		}
	}
	public function doDelweb(){
		
		$delnum = M('shares_global')->where("id = '{$_POST['id']}'")->delete(); 
		
		if($delnum){			
			$a_data['status'] = 1;
			$a_data['id'] = $data['id'];
		}else{
			$a_data['status'] = 0;
			$a_data['message'] = "删除失败";
		}
		
		exit(json_encode($a_data));
		
	}
	public function doEdit(){
			
		if($_POST){			
			$data = $_POST;
		}
		foreach($data as $key => $v){
			if(is_numeric($key)) M('shares_global')->where("id = '{$key}'")->setField('text',EnHtml($v));
		
		}
		
		$this->success('更新成功');
		
	}
	
	public function homsuser(){
		import("ORG.Util.Page");
		$count = M('homsuser h')->field("h.*,m.user_name")->join("lzh_members m ON m.id = h.uid")->count();
		$p = new Page($count, 10);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M('homsuser h')->field("h.*,m.user_name")->join("lzh_members m ON m.id = h.uid")->limit($Lsql)->order("id DESC")->select();
		$this->assign("page",$page);
		$this->assign('list',$list);
		$this->display();
	}
	
	public function dohomsuser(){
		
		$arr = $_POST;
		//dump($_POST);die;
		$length=count($_POST["homsuser"]); //数你有多少行数据
		for($i=0; $i<=$length-1;$i++){
			
			$data['homsuser'] = $_POST['homsuser'][$i]; 
			$data['homspass'] = $_POST['homspass'][$i];
			
			$adddata[] = $data;
		}
		$ret = M('homsuser')->addAll($adddata);
		if($ret){
			
			$this->success('添加成功！');
		}else{
			
			$this->error('添加失败！');
		}
		
	}
	
	public function echohtml(){
		
		$id = $_POST['id'];
		$uid = $_POST['uid'];
		
		$apply = M('homsuser')->field("homsuser,homspass")->where("id = {$id}")->find();
			
		$this->assign('apply',$apply);
		$this->assign('id',$id);
		$this->assign('uid',$uid);
		echo json_encode($this->fetch());
	}
	
	public function doedits(){
		
		$id = $_POST['id'];
		
		$data = array();
		$data['homsuser'] = $_POST['homsuser'];
		$data['homspass'] = $_POST['homspass'];
		$ret = M('homsuser')->where("id = {$id}")->save($data);
		if($ret){
			
			if($_POST['uid'] != 0){
				
				$user_phone = M('members')->getFieldByid($_POST['uid'],'user_phone');
				
				$info = '您在手投网股票配资平台申请重置HOMS账号信息，您的HOMS账号为：'.$_POST['homsuser'].'，密码为：'.$_POST['homspass'].'，请妥善保管，不要泄露他人！【手投网】';
				
				$ret = sendsms($user_phone,$info);
				if($ret){
					
					alogs("doedit",0,1,'管理员执行了股票配资重置homs信息操作成功！');
					$this->success('重置成功！');
				}else{
					alogs("doedit",0,1,'管理员执行了股票配资重置homs信息操作失败！');
					$this->error('重置失败！'); 
					
				}
			}else{
				alogs("doedit",0,1,'管理员执行了股票配资修改homs信息操作成功！');
				$this->success('修改成功！');
				
			}
			
		}else{
			 
			alogs("doedit",0,1,'管理员执行了股票配资修改homs信息操作失败！');
			$this->error('修改失败！');
		}
	}
}
?>
