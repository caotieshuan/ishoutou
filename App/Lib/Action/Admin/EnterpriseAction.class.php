<?php
/**
	企业合作模块
**/
class EnterpriseAction extends ACommonAction{


	function index()
	{
			$model=M('enterprise');
			import('ORG.Util.Page');// 导入分页类
			$count      = $model->count();// 查询满足要求的总记录数
			$Page       = new Page($count,2);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$list = $model->order('e_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('select',$list);// 赋值数据集
			$this->assign('page',$show);// 赋值分页输出
			$this->display(); // 输出模板

			//$this->select=$model->where($data)->order("e_id desc")->select();
	}
/**
添加表单显示数据
*/
	function add()
	{
		$Econfig = require C("APP_ROOT") . "Conf/enterprise_config.php";
		$this -> assign("type",$Econfig['TYPE']); //贷款类型
		$this -> assign("repayment_type",$Econfig['REPAYMENT_TYPE']);	//还款类型
		$this -> assign("identity",$Econfig['IDENTITY']);			//职业身份
		$this -> assign("house",$Econfig['HOUSE']);			//本地房产
		$this -> assign("vehicle",$Econfig['VEHICLE']);			//是否有车
		$this -> assign("credit",$Econfig['CREDIT']);			 //信用记录
		$this->display();
	}
/**
数据添加功能
*/
	function doadd()
	{
		$model = M("enterprise");
		$model->create();
		$logo=$this->file_upload();
		$model->logo=$logo[0]['savepath'].'m_'.$logo[0]['savename'];
		$model->sl=$logo[0]['savepath'].'n_'.$logo[0]['savename'];
		$model->success=1;
		$model->add_time=time();
		$model->details=$_POST['art_content'];
		$add=$model->add();
		if($add)
		{
			$this->success("添加成功!");
		}else
		{
			$this->error("添加失败!");
		}
	}



	function select()
	{
		$e_id=$_GET['e_id'];
		$model = M("enterprise");
		$find=$model->where()->find();
		dump($find);
	}

/**
文件上传功能
*/
	protected function file_upload()
	{
		import('ORG.Net.UploadFile');
		import('ORG.Util.Image');
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 3145728 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath =  './UF/Uploads/Enterprise/';// 设置附件上传目录
		$upload->thumb=true;
		$upload->thumbPrefix='m_,n_';
		$upload->thumbMaxWidth='60,170';
		$upload->thumbMaxHeight='60,170';
		$upload->thumbRemoveOrigin=true;
		if(!$upload->upload()) {// 上传错误提示错误信息
		$this->error($upload->getErrorMsg());
		}else{// 上传成功 获取上传文件信息
		$info = $upload->getUploadFileInfo();
		return $info;
		}

	}

/**
删除功能
*/

		function del()
		{
			$e_id=$_GET['e_id'];
			$model=M('enterprise');
			$find=$model->where("e_id=".$e_id)->find();
			$del=$model->where("e_id=".$e_id)->delete();
			if($del)
			{
				if(unlink($find['logo']) && unlink($find['sl']))
				{
					$this->success("删除成功！");
				}
				else
				{
				$this->error("删除失败!");
				}	
			}
		}

/**
修改页面数据展示
*/
		function edit()
		{
			$e_id=$_GET['e_id'];
			$model=M('enterprise');
			$this->find=$model->where("e_id=".$e_id)->find();
			$Econfig = require C("APP_ROOT") . "Conf/enterprise_config.php";
			$this -> assign("type",$Econfig['TYPE']); //贷款类型
			$this -> assign("repayment_type",$Econfig['REPAYMENT_TYPE']); //还款类型
			$this -> assign("identity",$Econfig['IDENTITY']);			//职业身份
			$this -> assign("house",$Econfig['HOUSE']);			//本地房产
			$this -> assign("vehicle",$Econfig['VEHICLE']);			//是否有车
			$this -> assign("credit",$Econfig['CREDIT']);			 //信用记录
			$this->display();
			
		}

/**
修改功能实现
*/
		function doedit()
		{
			$model=M('enterprise');
			$from=$model->create();
			$find=$model->where("e_id=".$from['e_id'])->find(); //取出要修改的信息
			$from['details']=$_POST['art_content'];
			if($_FILES['logo']['name']!='')						//判断文件是否被上传
			{		
				if(unlink($find['logo']) && unlink($find['sl']))						//删除以前的logo
				{
					$logo=$this->file_upload();
					$from['logo']=$logo[0]['savepath'].'m_'.$logo[0]['savename'];
					$from['sl']=$logo[0]['savepath'].'n_'.$logo[0]['savename'];
					$model->sl=$logo[0]['savepath'].'n_'.$logo[0]['savename'];	

								
				}
			}

			if($model->where("e_id=".$from['e_id'])->save($from))
			{
				$this->success("修改成功!",U('Enterprise/index'));
			}else{
				$this->error("修改失败!",U('Enterprise/index'));
			}
		}

/////////////////////////////////////////////经理人入驻////////////////////////////////////////////////////////////////

/**
经理人入驻列表
*/

		function hlist()
		{
			$model=M('handler');
			import('ORG.Util.Page');// 导入分页类
			$count      = $model->count();// 查询满足要求的总记录数
			$Page       = new Page($count,3);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$list = $model->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('list',$list);// 赋值数据集
			$this->assign('page',$show);// 赋值分页输出
			$this->display();
		}

/**
删除经理人入驻
*/
		function hdel()
		{
			$id=$_GET['id'];
			$model=M('handler');
			if($model->where("id=$id")->delete())
			{
				$this->success("删除成功！");
			}else{
				$this->error("删除失败！");
			}
		}


}
?>