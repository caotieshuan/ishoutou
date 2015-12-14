<?php
class EnterpriseAction extends HCommonAction {

/**
列表页
*/
		function index()
		{
		/**下拉列表*/
			$this->Econfig = require C("APP_ROOT") . "Conf/enterprise_config.php";
		/*下拉列表结束**/
			$model=M('enterprise');
			///////////////////
				$curl = $_SERVER['REQUEST_URI'];
				$urlarr = parse_url($curl);
				parse_str($urlarr['query'],$surl);//array获取当前链接参数，2.
				$urlArr = array('credit','vehicle','house','identity');
				foreach($urlArr as $v){
				$newpars = $surl;//用新变量避免后面的连接受影响
				unset($newpars[$v],$newpars['type'],$newpars['order_sort'],$newpars['orderby']);//去掉公共参数，对掉当前参数
				foreach($newpars as $skey=>$sv){
					if($sv=="all") unset($newpars[$skey]);//去掉"全部"状态的参数,避免地址栏全满
				}
				
				$newurl = http_build_query($newpars);//生成此值的链接,生成必须是即时生成
				$searchUrl[$v]['url'] = $newurl;
				$searchUrl[$v]['cur'] = empty($_GET[$v])?"all":text($_GET[$v]);
				}
		$searchMap['identity'] = array("all"=>"全部","1"=>"企业主","2"=>"个体户","3"=>"上班族","4"=>"上班族","5"=>"自由职业");

		$searchMap['house'] = array("all"=>"全部","1"=>"没有房产","2"=>"普通住宅","3"=>"商住两用房","4"=>"经济适用房/限价房","5"=>"小产权房",
			"6"=>"公寓","7"=>"别墅","8"=>"商铺","9"=>"办公楼","10"=>"厂房","11"=>"宅基地","12"=>"军产房");
		$searchMap['vehicle'] = array("all"=>"全部","1"=>"没有车辆","2"=>"家用轿车","3"=>"营运轿车","4"=>"营运货车","5"=>"其他车辆");
		$searchMap['credit'] = array("all"=>"全部","1"=>"无信用记录","2"=>"信用记录良好","3"=>"少数逾期","4"=>"长期或多次逾期","5"=>"不了解");

		/*搜索*/
		$search = array();
		foreach($urlArr as $v){
			if($_GET[$v] && $_GET[$v]<>'all'){
				switch($v){
					case 'identity':
						$search[$v] =$_GET[$v];
					break;
					case 'house':
						$search["house"] = $_GET[$v];
					break;
					case 'vehicle':
						$search["vehicle"] = $_GET[$v];
					break;
					case 'credit':
						$search["credit"] = $_GET[$v];
					break;
				}
			}
		}
			$this->assign("searchMap",$searchMap);
			$this->assign("searchUrl",$searchUrl);
			if(!empty($_GET['money']))
			{
				$search['money']=$_GET['money']*10000;
			}
			if(!empty($_GET['deadline']))
			{
				$search['deadline']=$_GET['deadline'];
			}
			//////////////
			import("ORG.Util.Page");
			$count = $model->where($search)->count();
			$p = new Page($count,8);
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$this->select=$model->where($search)->limit($Lsql)->order("e_id desc")->select();
			$this->assign('page',$page);// 赋值分页输出
			$this->display();
		}


/**
内容页
*/
		function select()
		{
			$model=M('enterprise');
			$e_id=$_GET['e_id'];
			//$this->Econfig = require C("APP_ROOT") . "Conf/enterprise_config.php";
			$this->list=$model->where("e_id=".$e_id)->find();
			$this->display();
			
		}
/**
贷款申请表单页
*/
		function ssd_sq(){
			$this->Econfig = require C("APP_ROOT") . "Conf/enterprise_config.php";
			$this->display();
		}

/**
///////////////////////////////////////////////////////////经理人入驻申请/////////////////////////////////////////////////////////
*/
		function hadd()
		{
			if($this->isPost())
			{
				$model=M('handler');
				//$form=$this->gl($model->create());
				$form=$this->gl($_POST);
				$flog=$this->yz($form);
				$form['add_time']=time();
				if($flog == 1)
				{
					if($model->add($form))
					{
						echo "申请成功";
					}else{
						echo "提交失败";
					}
				}
				die;
			}
			$this->display();
		}

/**
过滤两边的空格
*/
	protected function gl($form)
	{
		$array=array();
		foreach($form as $k=>$v)
		{
			$array[$k]=trim($v);
		}
		return $array;
	}

/**
验证经理人入驻表单
*/


	protected function yz($form)
	{
			
			$flog=1;

			//dump();die;
/**
验证姓名		
*/
		if(empty($form['name']))
		{
			echo "姓名不能为空";
			$flog=0;
			die;
		}else if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$form['name']) == '')
		{
			echo "姓名格式不正确";
			$flog=0;
			die;
		}
		
/**
手机验证		
*/
		if(empty($form['phone']))
		{
			echo "手机号不能为空";
			$flog=0;
			die;
		}else if(preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#',$form['phone']) == 0)
		{
			echo "您输入的手机号格式不正确";
			$flog=0;
			die;
		}

		
/**
公司验证		
*/

		if(empty($form['company']))
		{
			echo "请填写公司名称";
			$flog=0;
			die;
		}else if(eregi('select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile',$form['company']))
		{
			echo "请填写公司格式错误";
			$flog=0;
			die;
		}

		return $flog;

	}


/**
///////////////////////////////////////////////////////////申请贷款/////////////////////////////////////////////////////////
*/
		function loan()
		{

			$model=M('loan');
			$form=$this->gl($model->create());
			$flog=$this->loan_yz($form);
			$model->loan_time=time();
			
			if($flog == 1)
			{
				if($model->add())
				{
					$this->success("申请成功");
				}
			}else{
				$this->error("申请失败");
			}
		}
/**
获取手机验证码
*/

		function phone()
		{
			$smsTxt = FS("Webconfig/smstxt");
			$smsTxt = de_xie($smsTxt);
			 if(preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#',$_POST['phone']) == 0)
			{
				echo "3";
				die;
			}else
			{
				$phone = text($_POST['phone']);
			}
			//$phone = text($_POST['phone']);
			$code = rand_string_reg(6, 1, 2);
			$datag = get_global_setting();
			$is_manual = $datag['is_manual'];
			if ($is_manual == 0) 
				{ 
			$res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array(session('u_user_name'), $code), $smsTxt['verify_phone']));
				}
			//	dump(session('code_temp'));
			if($res)
			{
				echo "1";
			}else
			{
				echo "0";
			}

				
		}
		
/**
验证手机验证码
*/
		function sms()
		{
			$sms=text($_POST['sms']);
			if($sms == session('code_temp'))
			{
				echo "1";
			}else{
				echo "2";
			}
		}


		protected	function loan_yz($form)
		{
			
		$flog=1;

/**
验证金额
*/
		if(empty($form['person_money']))
		{
			$this->error("贷款金额不能为空");
			$flog=0;

		}
		if(!ereg("^\+?[1-9][0-9]*$",$form['person_money']))
		{
			$this->error("输入贷款金额的格式不正确");
			$flog=0;
		}

/**
验证姓名		
*/
		if(empty($form['person_name']))
		{
			$this->error("请填写您的姓名");
			$flog=0;
		}else if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$form['person_name']) == '')
		{
			$this->error("姓名格式不正确");
			$flog=0;
		}

/**
手机验证		
*/
		if(empty($form['person_phone']))
		{
			$this->error("手机号不能为空");
			$flog=0;
		}else if(preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#',$form['person_phone']) == 0)
		{
			$this->error("您输入的手机号格式不正确");
			$flog=0;
		}

/**
验证邮箱格式
*/
		if(!empty($form['person_email']))
		{
			if(!ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+",$form['person_email']))
			{
				$this->error("邮箱格式不正确!");
				$flog=0;
			}
		}
		
		if(!empty($form['countdition']))
		{
			if(eregi('select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile',$form['countdition']))
			{
				$this->error("填写详细信息格式错误!");
				$flog=0;
			}
		}



		return $flog;
		}
	
}
?>