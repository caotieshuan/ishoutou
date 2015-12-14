<?php
// 本类由系统自动生成，仅供测试用途
class MsgAction extends MCommonAction {

    public function index(){
		if(ListMobile()){
			$this->sysmsg(true);
		}
		$this->display();
    }

	public function ajaxsysmsg(){
		$list = $this->sysmsg(true);

		foreach($list as &$val){
			$val['send_time'] = date('Y-m-d',$val['send_time']);
		}

		$this->ajaxReturn($list,'JSON');
	}

    public function sysmsg($re=false){
		$map['uid'] = $this->uid;
		//分页处理
		import("ORG.Util.Page");
		$count = M('inner_msg')->where($map)->count('id');
		$p = new Page($count, 15);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		$list = M('inner_msg')->where($map)->order('id DESC')->limit($Lsql)->select();
	
		$read=M("inner_msg")->where("uid={$this->uid} AND status=1")->count('id');

		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->assign("read",$read);
		$this->assign("unread",$count-$read);
		$this->assign("count",$count);

		if(true === $re){
			$dpage = array();
			$dpage['numpage'] = $count ? ceil($count/15) : 1;
			$dpage['curpage'] = (int)$_GET['p'] ? (int)$_GET['p'] : 1;
			$this->assign("dpage",$dpage);
			return $list;
		}

		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	public function viewmsgs(){
		$data = $this->viewmsg(true);
		$data['content'] = $this->fetch();
		exit(json_encode($data));
	}

	public function viewmsg($re=false){
		$id = intval($_GET['id']);
		$vo = M("inner_msg")->field('msg,send_time,status')->where("id={$id} AND uid={$this->uid}")->find();
		if(!is_array($vo)){
			$this->assign("msg","数据有误");
			$data['content'] = $this->fetch();
			exit(json_encode($data));
		}
		M("inner_msg")->where("id={$id} AND uid={$this->uid}")->setField("status",1);
		$this->assign("mid",$id);
		$this->assign("msg",$vo['msg']);
		$this->assign("dateline",date('Y-m-d',$vo['send_time']));
		if(true == $re) return array('id'=>$id,'unread'=>$vo['status']);
		$data['content'] = $this->fetch();
		exit(json_encode($data));
	}
	
	public function delmsg(){
		$id = text($_POST['idarr']);
		$wsql = "uid={$this->uid}";
		$up = M("inner_msg")->where("{$wsql} AND id in({$id})")->delete();
		if($up){
			$data['status'] = 1;
			$data['data'] = $id;
			echo json_encode($data);
		}else{
			$data['status'] = 0;
			echo json_encode($data);
		}
	}

}