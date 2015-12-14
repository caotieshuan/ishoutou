<?php
class LoanAction extends ACommonAction{

/**
申请贷款的列表
*/
	function index()
	{
		$model=M('loan');
		import('ORG.Util.Page');// 导入分页类
		$count      = $model->count();// 查询满足要求的总记录数
		$Page       = new Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出
		$this->Econfig = require C("APP_ROOT") . "Conf/enterprise_config.php";
		$join=array("lzh_enterprise ON lzh_enterprise.e_id=lzh_loan.e_id");
		$list=$model->order('l_id desc')->join("lzh_enterprise ON lzh_enterprise.e_id=lzh_loan.e_id")->limit($Page->firstRow.','.$Page->listRows)->select();

		$this->assign("list",$list);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}

/**
申请贷款删除
*/
	function del()
	{
		$l_id=$_GET['l_id'];
		$model=M('loan');
		$del=$model->where("l_id=".$l_id)->delete();
		if($del)
		{
			$this->success("删除成功");
		}else{
			$this->erroe("删除失败");
		}
	}

//////////////////////////////////////////////////////////快速申请///////////////////////////////////////////////////////////////////////////

/**
快速申请列表
*/

	function lists()
	{
		$model=M('k_loan');
		import('ORG.Util.Page');// 导入分页类
		$count      = $model->count();// 查询满足要求的总记录数
		$Page       = new Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出
		$list=$model->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign("list",$list);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}  
/**
快速申请 删除
*/
	function kdel()
	{
		$del=M('k_loan')->where("id=".$_GET['id'])->delete();
		if($del)
		{
			$this->success("删除成功");
		}else
		{
			$this->error("删除失败");
		}
	}
   ///////////////////////////////////////////快速投资////////////////////////////////////////////////////////////////////////

/**
快速投资列表
*/

		function invest()
		{
			$model=M('k_invest');
			import('ORG.Util.Page');// 导入分页类
			$count      = $model->count();// 查询满足要求的总记录数
			$Page       = new Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			$list=$model->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('page',$show);// 赋值分页输出
			$this->assign("list",$list);
			$this->display();
		}

/**
快速投资 删除
*/
	function idel()
	{
		$del=M('k_invest')->where("id=".$_GET['id'])->delete();
		if($del)
		{
			$this->success("删除成功");
		}else
		{
			$this->error("删除失败");
		}
	}


}
?>