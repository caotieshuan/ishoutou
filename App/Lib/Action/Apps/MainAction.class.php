<?php
class MainAction extends HCommonAction {
	/**
	 * [list 散标列表123456]
	 * @return [type] [description]
	 */
		public function getlist()
		{
			$Bconfig=require c("APP_ROOT")."Conf/borrow_config.php";
			$jsoncode=file_get_contents("php://input");
			$arr=array();
			$arr=json_decode($jsoncode,ture);
			if(is_array($arr)&&isset($arr['id'])&&isset($arr['type'])&&isset($arr['num']))
			{
 				$type=$arr['type'];
 				$id=intval($arr['id']);
 				$num=intval($arr['num']);
			}else{
				$type=2;
				$num=2;
			}
			$per=C('DB_PREFIX');
			if($type==1){
				$searchMap['borrow_status']=array("in",'2,4,6,7');
				$searchMap['b.id']=array("gt",$id);
				$parm['map']=$searchMap;
				$parm['limit']=$num;
				//$parm['orderby']="b.borrow_status ASC,b.id asc";
				$parm['orderby']="b.id asc";

			}elseif ($type==0) {
				$searchMap['borrow_status']=array("in",'2,4,6,7');
				$searchMap['b.id']=array("lt",$id);
				$parm['map']=$searchMap;
				$parm['limit']=$num;
				//$parm['orderby']="b.borrow_status ASC,b.id DESC";
				$parm['orderby']="b.id DESC";

			}else{
				$searchMap['borrow_status']=array("in",'2,4,6,7');
				$parm['map']=$searchMap;
				$parm['limit']=5;
				//$parm['orderby']="b.borrow_status ASC,b.id DESC";
				$parm['orderby']="b.id DESC";

			}
			$list=getBorrowList($parm);
			foreach($list['list'] as $key =>$v){
			$_list[$key]['uid'] = intval($v['uid']);
			$_list[$key]['type'] = getleixing($v);
			$_list[$key]['id'] = intval($v['id']);
			$_list[$key]['borrow_name'] = $v['borrow_name'];
			$_list[$key]['borrow_interest_rate'] = $v['borrow_interest_rate'];
			if($v['repayment_type']==1){
				$_list[$key]['borrow_duration'] = $v['borrow_duration'];
				}else{
					$_list[$key]['borrow_duration'] = $v['borrow_duration'];
				}
				$_list[$key]['repayment_type'] =$v['repayment_type'];
				$_list[$key]['huankuan_type'] =$Bconfig['REPAYMENT_TYPE'][$v['repayment_type']];
				$_list[$key]['borrow_money'] =$v['borrow_money'];
				$_list[$key]['progress'] =$v['progress'];
				$_list[$key]['credits'] =$v['credits'];
				$_list[$key]['user_name'] =$v['user_name'];
				$_list[$key]['imgpath'] =get_avatar(intval($v['uid']));
				$_list[$key]['suo'] = empty($v['password'])?0:1;//是否定向标
				if($v['reward_type']==1){
					$_list[$key]['reward']=$v['reward_num']."%";
				}elseif($v['reward_type']==2){
					$_list[$key]['reward']=$v['reward_num']."元";
				}else{
					$_list[$key]['reward']="0";
				}
				$borrowinfo = M("borrow_info bi")->field('bi.id as bid,bi.*,ac.title,ac.id')->join('lzh_article ac on ac.id= bi.danbao')->where('bi.id='.$v['id'])->find();
				$borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();

				if($v['progress'] >= 100 ){
				    $_list[$key]['lefttime'] ="已结束";
				}elseif ($borrowinfo['lefttime'] > 0){
				    $left_tian = floor($borrowinfo['lefttime']/ (60 * 60 * 24));
					$left_hour = floor(($borrowinfo['lefttime'] - $left_tian * 24 * 60 * 60)/3600);
					$left_minute = floor(($borrowinfo['lefttime'] - $left_tian * 24 * 60 * 60 - $left_hour * 60 * 60)/60);
					$left_second = floor($borrowinfo['lefttime'] - $left_tian * 24 * 60 * 60 - $left_hour * 60 * 60 - $left_minute *60);
					$_list[$key]['lefttime'] = $left_tian.",".$left_hour.",".$left_minute.",".$left_second;
				}else {
				    $_list[$key]['lefttime'] ="已结束";
				}

			}
			$m_list['list']= $_list;
			ajaxmsg($m_list);

		}
		/**
		 * [tlist 企业直投]
		 * @return [type] [description]
		 */
		public function gettlist()
		{
			$jsoncode=file_get_contents("php://input");

			$arr=array();
			$arr=json_decode($jsoncode,ture);
			if(is_array($arr)&&isset($arr['tid'])&&isset($arr['ttype'])&&isset($arr['tnum']))
			{
				$ttype=$arr['ttype'];
				$tid=intval($arr['tid']);
				$tnum=intval($arr['tnum']);
			}else{
				$ttype=2;
				$tnum=5;
			}
			$parmt = array();
			$searchMapt = array();
			//$searchMap['borrow_status']=2;
			if($ttype == 1){
				$searchMapt['is_show'] = array('in','0,1');
				$searchMapt['b.id']=array("gt",$tid);
				$parmt['map'] = $searchMapt;
				$parmt['limit'] = $tnum;
				$parmt['orderby'] = "b.id asc";
			}elseif($ttype == 0){
			    $searchMapt['is_show'] = array('in','0,1');
				$searchMapt['b.id']=array("lt",$tid);
				$parmt['map'] = $searchMapt;
				$parmt['limit'] = $tnum;
				$parmt['orderby'] = "b.id DESC";
			}else{
				$searchMapt['is_show'] = array('in','0,1');
				$parmt['map'] = $searchMapt;
				$parmt['limit'] = $tnum;
				$parmt['orderby'] = "b.id DESC";
			}

			$tlist = getTBorrowList($parmt);
			
			foreach($tlist['list'] as $key =>$v){
			  $_tlist[$key]['uid'] = intval($v['uid']);
			  $_tlist[$key]['type'] = 2;
			  $_tlist[$key]['id'] = intval($v['id']);
			  $_tlist[$key]['borrow_name'] = $v['borrow_name'];
			  $_tlist[$key]['borrow_interest_rate'] = $v['borrow_interest_rate'];
			  $_tlist[$key]['borrow_duration'] = $v['borrow_duration'];
			  $_tlist[$key]['per_transfer'] = $v['per_transfer'];
			  $_tlist[$key]['borrow_money'] =$v['borrow_money'];
			  $_tlist[$key]['progress'] =$v['progress'];
			  $_tlist[$key]['credits'] =$v['credits'];
			  $_tlist[$key]['user_name'] =$v['user_name'];
			  $_tlist[$key]['imgpath'] =get_avatar(intval($v['uid']));
			  $_tlist[$key]['reward'] = $v['reward_rate']."%";
			}
			$m_list['list']=$_tlist;
			//dump($m_list);die;
			ajaxmsg($m_list);
			

		}
		/**
		 * [reclist(recommend list) 推荐标信息作废]
		 * @return [type] [description]
		 * @author yudianguo <yudianguo163@163.com>
		 */
		public function reclist()
		{
			//普通表
			//$per=C('DB_PREFIX');
			$parm=array();
			$searchMap=array();
			//$searchMap['is_tuijian']='1';
			//$searchMap['is_show']=array('in','0,1');
			$searchMap['borrow_status']='2';
			$parm['map']=$searchMap;
			$parm['limit']=2;
			$parm['orderby']="b.id DESC";
			$list=getBorrowList($parm);
			//$_list = $list;
			foreach($list['list'] as $key =>$v){
			  $_list[$key]['uid'] = intval($v['uid']);
			  $_list[$key]['type'] = getleixing($v);
			  $_list[$key]['id'] = intval($v['id']);
			  $_list[$key]['borrow_name'] = $v['borrow_name'];
			  $_list[$key]['borrow_interest_rate'] = $v['borrow_interest_rate'];
			  if($v['repayment_type']==1){
			      $_list[$key]['borrow_duration'] = $v['borrow_duration']."天";
			  }else{
			      $_list[$key]['borrow_duration'] = $v['borrow_duration']."个月";
			  }
			  $_list[$key]['repayment_type'] = $v['repayment_type'];
			  $_list[$key]['borrow_money'] =$v['borrow_money'];
			  $_list[$key]['progress'] =$v['progress'];
			  $_list[$key]['credits'] =$v['credits'];
			  $_list[$key]['user_name'] =$v['user_name'];
			  $_list[$key]['imgpath'] =get_avatar(intval($v['uid']));
			  $_list[$key]['suo'] = empty($v['password'])?0:1;//是否定向标
			  if($v['reward_type']==1){
			      $_list[$key]['reward']=$v['reward_num']."%";
			  }elseif($v['reward_type']==2){
			      $_list[$key]['reward']=$v['reward_num']."元";
			  }else{
			      $_list[$key]['reward']="0";
			  }
			}
			$m_list['tjlist']= $_list;
			//企业标
			$parmt=array();
			$searchMapt=array();
			$searchMapt['is_show']=array('in','0,1');
			$searchMapt['borrow_status']='2';
			$parmt['map']=$searchMapt;
			$parmt['limit']=1;
			$parmt['orderby']="b.id DESC";
			$tlist=getTBorrowList($parmt);
			foreach ($tlist['list'] as $key => $v) {
				$_tlist[$key]['uid'] = intval($v['uid']);
				$_tlist[$key]['type'] = 2;
				$_tlist[$key]['id'] = intval($v['id']);
				$_tlist[$key]['borrow_name'] = $v['borrow_name'];
				$_tlist[$key]['borrow_interest_rate'] = $v['borrow_interest_rate'];
				$_tlist[$key]['borrow_duration'] = $v['borrow_duration']."个月";
				$_tlist[$key]['per_transfer'] = $v['per_transfer'];
				$_tlist[$key]['borrow_money'] =$v['borrow_money'];
				$_tlist[$key]['progress'] =$v['progress'];
				$_tlist[$key]['credits'] =$v['credits'];
				$_tlist[$key]['user_name'] =$v['user_name'];
				$_tlist[$key]['imgpath'] =get_avatar(intval($v['uid']));
				$_tlist[$key]['reward'] = $v['reward_rate']."%";
			}
			$m_list['tjtlist']= $_tlist;	

		    ajaxmsg($m_list);
		}
		public function index(){
	   $jsoncode = file_get_contents("php://input"); 
	   $arr = array();
	   $arr = json_decode($jsoncode,true);
//	   $arr['id'] = 0;
//	   $arr['type'] = 0;
//	   $arr['num'] = 3;
//	   $arr['tid'] = 11;
//	   $arr['ttype'] = 1;
//	   $arr['tnum'] = 3;
       //普通标翻页
	   if(is_array($arr) && isset($arr['id']) && isset($arr['type']) && isset($arr['num'])){
	       $type = $arr['type'];
		   $id = intval($arr['id']);
		   $num = intval($arr['num']);
	   }else{
	       $type = 2;
		   $num = 5;
	   }
	   //流转标翻页
	   if(is_array($arr) && isset($arr['tid']) && isset($arr['ttype']) && isset($arr['tnum'])){
	       $ttype = $arr['ttype'];
		   $tid = intval($arr['tid']);
		   $tnum = intval($arr['tnum']);
	   }else{
	       $ttype = 2;
		   $tnum = 5;
	   }
		//alogsm("Main",0,1,$jsoncode);

		$per = C('DB_PREFIX');
		//普通标	
		if($type == 1){
			$searchMap['borrow_status']=array("in",'2,4,6,7');
			$searchMap['b.id']=array("gt",$id);
			$parm['map'] = $searchMap;
			$parm['limit'] = $num;
			$parm['orderby']="b.borrow_status ASC,b.id asc";
		}elseif($type == 0){
		    $searchMap['borrow_status']=array("in",'2,4,6,7');
			$searchMap['b.id']=array("lt",$id);
			$parm['map'] = $searchMap;
			$parm['limit'] = $num;
			$parm['orderby']="b.borrow_status ASC,b.id DESC";
		}else{
		    $searchMap['borrow_status']=array("in",'2,4,6,7');
			$parm['map'] = $searchMap;
			$parm['limit'] = $num ;
			$parm['orderby']="b.borrow_status ASC,b.id DESC";
		}
		
		$list = getBorrowList($parm);
		//$_list = $list;
		foreach($list['list'] as $key =>$v){
		  $_list[$key]['uid'] = intval($v['uid']);
		  $_list[$key]['type'] = getleixing($v);
		  $_list[$key]['id'] = intval($v['id']);
		  $_list[$key]['borrow_name'] = $v['borrow_name'];
		  $_list[$key]['borrow_interest_rate'] = $v['borrow_interest_rate'];
		  if($v['repayment_type']==1){
		      $_list[$key]['borrow_duration'] = $v['borrow_duration']."天";
		  }else{
		      $_list[$key]['borrow_duration'] = $v['borrow_duration']."个月";
		  }
		  
		  
		  $_list[$key]['repayment_type'] = $v['repayment_type'];
		  $_list[$key]['borrow_money'] =$v['borrow_money'];
		  $_list[$key]['progress'] =$v['progress'];
		  $_list[$key]['credits'] =$v['credits'];
		  $_list[$key]['user_name'] =$v['user_name'];
		  $_list[$key]['imgpath'] =get_avatar(intval($v['uid']));
		  $_list[$key]['suo'] = empty($v['password'])?0:1;//是否定向标
		  if($v['reward_type']==1){
		      $_list[$key]['reward']=$v['reward_num']."%";
		  }elseif($v['reward_type']==2){
		      $_list[$key]['reward']=$v['reward_num']."元";
		  }else{
		      $_list[$key]['reward']="0";
		  }
		}
		$m_list['list']= $_list;
		//企业直投
		$parmt = array();
		$searchMapt = array();
		//$searchMap['borrow_status']=2;
		if($ttype == 1){
			$searchMapt['is_show'] = array('in','0,1');
			$searchMapt['b.id']=array("gt",$tid);
			$parmt['map'] = $searchMapt;
			$parmt['limit'] = $tnum;
			$parmt['orderby'] = "b.is_show desc,b.id asc";
		}elseif($ttype == 0){
		    $searchMapt['is_show'] = array('in','0,1');
			$searchMapt['b.id']=array("lt",$tid);
			$parmt['map'] = $searchMapt;
			$parmt['limit'] = $tnum;
			$parmt['orderby'] = "b.is_show desc,b.id DESC";
		}else{
			$searchMapt['is_show'] = array('in','0,1');
			$parmt['map'] = $searchMapt;
			$parmt['limit'] = $tnum;
			$parmt['orderby'] = "b.is_show desc,b.id DESC";
		}

		$tlist = getTBorrowList($parmt);
		foreach($tlist['list'] as $key =>$v){
		  $_tlist[$key]['uid'] = intval($v['uid']);
		  $_tlist[$key]['type'] = 2;
		  $_tlist[$key]['id'] = intval($v['id']);
		  $_tlist[$key]['borrow_name'] = $v['borrow_name'];
		  $_tlist[$key]['borrow_interest_rate'] = $v['borrow_interest_rate'];
		  $_tlist[$key]['borrow_duration'] = $v['borrow_duration']."个月";
		  $_tlist[$key]['per_transfer'] = $v['per_transfer'];
		  $_tlist[$key]['borrow_money'] =$v['borrow_money'];
		  $_tlist[$key]['progress'] =$v['progress'];
		  $_tlist[$key]['credits'] =$v['credits'];
		  $_tlist[$key]['user_name'] =$v['user_name'];
		  $_tlist[$key]['imgpath'] =get_avatar(intval($v['uid']));
		  $_tlist[$key]['reward'] = $v['reward_rate']."%";
		}
		$m_list['tlist']= $_tlist;
		
		echo ajaxmsg($m_list);

    }
	//普通标详细信息
	public function detail(){
		$jsoncode = file_get_contents("php://input");
		//alogsm("detail",0,1,$jsoncode);
		$arr = array();
		$arr = json_decode($jsoncode,true);
		
		if (!is_array($arr)||empty($arr)||empty($arr['id'])||!in_array($arr['type'],array(3,4,5,6,7))) {
		   ajaxmsg("查询错误！",0);
		}


		$pre = C('DB_PREFIX');
		$id = intval($arr['id']);
		//$id = 30;
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		
		
		//borrowinfo
		
		$borrowinfo = M("borrow_info bi")->field('bi.id as bid,bi.*,ac.title,ac.id')->join('lzh_article ac on ac.id= bi.danbao')->where('bi.id='.$id)->find();
		if(!is_array($borrowinfo) || ($borrowinfo['borrow_status']==0 && $this->uid!=$borrowinfo['borrow_uid']) ) ajaxmsg("数据有误",0);
		$borrowinfo['biao'] = $borrowinfo['borrow_times'];
		$borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
		$borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();
		$borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow']/$borrowinfo['borrow_money']*100,2);
		
		//$list['type'] = 1;
		$list['id'] = $id;
		$list['type'] = getleixing($borrowinfo);
		$list['borrow_name'] = $borrowinfo['borrow_name'];
		$list['borrow_money'] = $borrowinfo['borrow_money'];
		$list['borrow_interest_rate'] = $borrowinfo['borrow_interest_rate'];
		//$list['huankuan_type'] = $borrowinfo['repayment_type'];
		$list['repayment_type'] = $borrowinfo['repayment_type'];
		
		if($list['repayment_type']==1){
		    $list['borrow_duration'] = $borrowinfo['borrow_duration'];
		}else{
		    $list['borrow_duration'] = $borrowinfo['borrow_duration'];
		}
		$list['huankuan_type'] = $Bconfig['REPAYMENT_TYPE'][$borrowinfo['repayment_type']];
		$list['borrow_use'] = $Bconfig['REPAYMENT_TYPE'][$borrowinfo['borrow_use']];
		$list['borrow_min'] = $borrowinfo['borrow_min'];
		$list['borrow_max'] = $borrowinfo['borrow_max']=="0"?"无":"{$borrowinfo['borrow_max']}";
		$list['progress'] = $borrowinfo['progress'];
		$list['need'] = $borrowinfo['need'];
		if($borrowinfo['progress'] >= 100 ){
		    $list['lefttime'] ="已结束";
		}elseif ($borrowinfo['lefttime'] > 0){
		    $left_tian = floor($borrowinfo['lefttime']/ (60 * 60 * 24));
			$left_hour = floor(($borrowinfo['lefttime'] - $left_tian * 24 * 60 * 60)/3600);
			$left_minute = floor(($borrowinfo['lefttime'] - $left_tian * 24 * 60 * 60 - $left_hour * 60 * 60)/60);
			$left_second = floor($borrowinfo['lefttime'] - $left_tian * 24 * 60 * 60 - $left_hour * 60 * 60 - $left_minute *60);
			$list['lefttime'] = $left_tian.",".$left_hour.",".$left_minute.",".$left_second;
			//$list['lefttime'] = $left_tian."天".$left_hour."小时".$left_minute."分钟".$left_second."秒";
		}else {
		    $list['lefttime'] ="已结束";
		}
		//$list['borrow_breif'] = $borrowinfo['borrow_info'];
		$list['borrow_breif'] ="<p style='color:#5c5c5c;font-size:13.5px;font-family: Droid Sans Fallback;'>".strip_tags($borrowinfo['borrow_info'])."</p>";
		$list['invest_num'] = M("borrow_investor")->where("borrow_id={$id}")->count("id");
		
		$minfo = M("members")->where("id={$borrowinfo['borrow_uid']}")->find();
		$list['credit_rating']=getLeveIco($minfo['credits'],3);
		$list['user_name'] = $minfo['user_name'];
		$list['imgpath'] = get_avatar($borrowinfo['borrow_uid']);
		$list['addtime'] = date("Y-m-d",$borrowinfo['add_time']);
		$list['loan_use']=$this->gloconf['BORROW_USE'][$borrowinfo['borrow_use']];

		if($borrowinfo['reward_type']==1){
		    $list['reward'] = $borrowinfo['reward_num'];
		}elseif($borrowinfo['reward_type']==2){
		    $list['reward'] = $borrowinfo['reward_num'];
		}else{
		    $list['reward']="0";
		}
		echo ajaxmsg($list);
		
    }
	//普通标投标记录
	public function investlog(){
	    $jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['id'])) {
		   ajaxmsg("查询错误！",0);
		}
		$pre = C('DB_PREFIX');
		$id = intval($arr['id']);
		//$id = 16;
		$fieldx = "bi.investor_capital,bi.add_time,m.user_name,bi.is_auto";
		$investinfo = M("borrow_investor bi")->field($fieldx)->join("{$pre}members m ON bi.investor_uid = m.id")->where("bi.borrow_id={$id} AND bi.loanno!=''")->order("bi.id DESC")->select();
		foreach($investinfo as $key=>$v){
			$list[$key]['user_name'] = $v['user_name'];
			$list[$key]['investor_capital'] = $v['investor_capital'];
			$list[$key]['add_time'] = date("Y-m-d",$v['add_time']);
			
		}
		$_list['list'] = $list;
		if(is_array($list)&&!empty($list)){
		    echo ajaxmsg($_list);
		}else echo ajaxmsg("暂无投标记录",0);
		
	}
	//企业直投投标记录
	public function tinvestlog(){
	    $jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['id'])) {
		   ajaxmsg("查询错误！",0);
		}
		$pre = C('DB_PREFIX');
		$id = intval($arr['id']);
		//$id = 4;
		//$fieldx = "bi.investor_capital,bi.add_time,m.user_name,bi.is_auto";
		$fieldx = "bi.investor_capital,bi.transfer_month,bi.transfer_num,bi.add_time,m.user_name,bi.is_auto,bi.final_interest_rate";
		$investinfo = M("transfer_borrow_investor bi")->field($fieldx)->join("{$pre}members m ON bi.investor_uid = m.id")->where("bi.borrow_id={$id}")->order("bi.id DESC")->select();
		foreach($investinfo as $key=>$v){
			$list[$key]['user_name'] = $v['user_name'];
			$list[$key]['investor_capital'] = $v['investor_capital'];
			$list[$key]['add_time'] = date("Y-m-d",$v['add_time']);
			
		}
		$_list['list'] = $list;
		if(is_array($list)&&!empty($list)){
		    echo ajaxmsg($_list);
		}else echo ajaxmsg("暂无投标记录",0);
	}
	//喇叭右边的轮播接口
	public function appbroadcast(){
		$pre = C('DB_PREFIX');
		$id = intval($arr['id']);
		//$id = 16;
		$fieldx = "bi.investor_capital,bi.add_time,m.user_name,bi.borrow_id";
		$investinfo = M("borrow_investor bi")->field($fieldx)->join("{$pre}members m ON bi.investor_uid = m.id")->order("bi.id DESC")->limit('10')->select();
		foreach($investinfo as $key=>$v){
			$list[$key]['data']=substr(hidecard($v['user_name'],4),0,5)."投资标号为".$v['borrow_id']."的项目".appgetmoney($v['investor_capital'])."元"."  ".date("Ymd",$v['add_time']);
			
		}
		$_list['list'] = $list;
		if(is_array($list)&&!empty($list)){
		    echo ajaxmsg($_list);
		}else echo ajaxmsg("暂无投标记录",0);
	}	
	//投标详细页里的立即投资的验证（第一次点）
	public function ajax_invest(){
		
        $jsoncode = file_get_contents("php://input");
		//alogsm("ajax_invest",0,1,session("u_id").$jsoncode);
		if(!$this->uid) {
			ajaxmsg("请先登录",0);
			exit;
		}
		$arr = array();
		$arr = json_decode($jsoncode,true);
		//dump($arr['uid']);die;
		if (intval($arr['uid'])!=$this->uid){
			ajaxmsg("查询错误r！",0);
		}
		if (!is_array($arr)||empty($arr)||empty($arr['id'])||!in_array($arr['type'],array(3,4,5,6,7))) {
		   ajaxmsg("查询错误t！",0);
		}
		


		$pre = C('DB_PREFIX');
		$id=intval($arr['id']);
		//$id=23;
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$field = "id,borrow_uid,borrow_money,borrow_status,borrow_type,has_borrow,has_vouch,borrow_interest_rate,borrow_duration,repayment_type,collect_time,borrow_min,borrow_max,password,borrow_use,money_collect";
		$vo = M('borrow_info')->field($field)->find($id);
		if($this->uid == $vo['borrow_uid']) ajaxmsg("不能去投自己的标",0);
		if($vo['borrow_status'] <> 2) ajaxmsg("只能投正在借款中的标",0);
	
		
		$vo['need'] = $vo['borrow_money'] - $vo['has_borrow'];
		if($vo['need']<0){
			ajaxmsg("投标金额不能超出借款剩余金额",0);
		}
		$vo['lefttime'] =$vo['collect_time'] - time();
		$vo['progress'] = getFloatValue($vo['has_borrow']/$vo['borrow_money']*100,4);//ceil($vo['has_borrow']/$vo['borrow_money']*100);
		$vo['uname'] = M("members")->getFieldById($vo['borrow_uid'],'user_name');
		$time1 = microtime(true)*1000;
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		$amoney = $vm['account_money']+$vm['back_money'];
		
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($vm['money_collect']<$binfo['money_collect']) {
				ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",0);
			}
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		
		////////////////////投标时自动填写可投标金额在投标文本框 2013-07-03 fan////////////////////////
		
		if($amoney<floatval($vo['borrow_min'])){
			ajaxmsg("您的账户可用余额小于本标的最小投标金额限制，不能投标！",0);
		}elseif($amoney>=floatval($vo['borrow_max']) && floatval($vo['borrow_max'])>0){
			$toubiao = intval($vo['borrow_max']);
		}else if($amoney>=floatval($vo['need'])){
			$toubiao = intval($vo['need']);
		}else{
			$toubiao = floor($amoney);
		}
		$vo['toubiao'] =$toubiao;
		////////////////////投标时自动填写可投标金额在投标文本框 2013-07-03 fan////////////////////////
		$pin_pass = $vm['pin_pass'];
		$has_pin = (empty($pin_pass))?"no":"yes";
        $data['type'] = $arr['type'];
		$data['id'] = $id;
		$data['has_pin'] = $has_pin=='yes'?1:0;
		$data['borrow_min'] = $vo['borrow_min'];
		$data['borrow_max'] = $vo['borrow_max']=="0"?"无":"{$vo['borrow_max']}";
		$data['need'] = $vo['need'];
		$data['toubiao'] = $vo['toubiao'];
		$data['password'] = empty($vo['password'])?0:1;;
		$data['account_money'] = $amoney;
		
		
		ajaxmsg($data);
	}
	//投标详细页里的立即投资的验证（第二次）
	public function investcheck(){
		$jsoncode = file_get_contents("php://input");
		//alogsm("investcheck",0,1,session("u_id").$jsoncode);
		if(!$this->uid) {
			ajaxmsg('请先登录',0);
			exit;
		}
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['borrow_id'])||!in_array($arr['type'],array(3,4,5,6,7))) {
		   ajaxmsg("查询错误1！",0);
		}
		if (intval($arr['uid'])!=$this->uid){
			ajaxmsg("查询错误2！",0);
		}
		$pre = C('DB_PREFIX');
		$_pin = $arr['pin'];
		$borrow_id = intval($arr['borrow_id']);
		$money = intval($arr['money']);
		//$_pin = "123456";
//		$borrow_id = 23;
//		$money = 100;
        $pin = md5($_pin);
		$borrow_pass = $arr['borrow_pass'];
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		$amoney = $vm['account_money']+$vm['back_money'];
		$uname = session('u_user_name');
		$pin_pass = $vm['pin_pass'];
		$amoney = floatval($amoney);
		
		$binfo = M("borrow_info")->field('borrow_money,has_borrow,has_vouch,borrow_max,borrow_min,borrow_type,password,money_collect')->find($borrow_id);
		//if($binfo['has_vouch']<$binfo['borrow_money'] && $binfo['borrow_type'] == 2) ajaxmsg("此标担保还未完成，您可以担保此标或者等担保完成再投标",3);
		if(!empty($binfo['password'])){
			if(empty($borrow_pass)) ajaxmsg("此标是定向标，必须验证投标密码",3);
			else if($binfo['password']<>md5($borrow_pass)) ajaxmsg("投标密码不正确",3);
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($vm['money_collect']<$binfo['money_collect']) {
				ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",3);
			}
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		//投标总数检测
		$capital = M('borrow_investor')->where("borrow_id={$borrow_id} AND investor_uid={$this->uid}")->sum('investor_capital');
		if(($capital+$money)>$binfo['borrow_max']&&$binfo['borrow_max']>0){
			$xtee = $binfo['borrow_max'] - $capital;
			ajaxmsg("您已投标{$capital}元，此投上限为{$binfo['borrow_max']}元，你最多只能再投{$xtee}",3);
		}
		
		$need = $binfo['borrow_money'] - $binfo['has_borrow'];
		$caninvest = $need - $binfo['borrow_min'];
		if( $money>$caninvest && ($need-$money)<>0 ){
			$msg = "尊敬的{$uname}，此标还差{$need}元满标,如果您投标{$money}元，将导致最后一次投标最多只能投".($need-$money)."元，小于最小投标金额{$binfo['borrow_min']}元，所以您本次可以选择<font color='#FF0000'>满标</font>或者投标金额必须<font color='#FF0000'>小于等于{$caninvest}元</font>";
			if($caninvest<$binfo['borrow_min']) $msg = "尊敬的{$uname}，此标还差{$need}元满标,如果您投标{$money}元，将导致最后一次投标最多只能投".($need-$money)."元，小于最小投标金额{$binfo['borrow_min']}元，所以您本次可以选择<font color='#FF0000'>满标</font>即投标金额必须<font color='#FF0000'>等于{$need}元</font>";

			ajaxmsg($msg,3);
		}
		
		if(($need-$money)<0 ){
			$this->error("尊敬的{$uname}，此标还差{$need}元满标,您最多只能再投{$need}元",3);
		}
		
		//if($pin<>$pin_pass) ajaxmsg("支付密码错误，请重试!",0);
		if($money>$amoney){
			$msg = "尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，请先去充值!";
			ajaxmsg($msg,2);
		}else{
			$msg = "尊敬的{$uname}，您的账户可用余额为{$amoney}元，您确认投标{$money}元吗？";
			
			$_msg['type'] = 1;
			$_msg['id'] = $borrow_id;
			$_msg['message'] = $msg;
			ajaxmsg($_msg,1);
		}
	}
	
	//投标详细页里的立即投资的验证（第三次）
	public function investmoney(){
		$jsoncode = file_get_contents("php://input");
		//alogsm("investmoney",0,1,session("u_id").$jsoncode);
		if(!$this->uid) {
			ajaxmsg('请先登录',0);
			exit;
		}
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (intval($arr['uid'])!=$this->uid){
			ajaxmsg("查询错误f！",0);
		}
		if (!is_array($arr)||empty($arr)||empty($arr['borrow_id'])||empty($arr['zhifu_money'])||!in_array($arr['type'],array(3,4,5,6,7))) {
		   ajaxmsg("查询错误g！",0);
		}
		
		$pre = C('DB_PREFIX');
		$_pin = $arr['pin'];
		$pin = md5($_pin);
		$borrow_id = intval($arr['borrow_id']);
		$money =$arr['zhifu_money'];
//		$_pin = "123456";
//		$pin = md5($_pin);
//		$borrow_id = 23;
//		$money = 60;
//		
		$borrow_pass = $arr['borrow_pass'];
				
		$m = M("member_money")->field('account_money,back_money,money_collect')->find($this->uid);
		$amoney = $m['account_money']+$m['back_money'];
		$uname = session('u_user_name');
		if($amoney<$money) ajaxmsg("尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再投标.",0);
		
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		$pin_pass = $vm['pin_pass'];
		
		//if($pin<>$pin_pass) ajaxmsg("支付密码错误，请重试",2);

		$binfo = M("borrow_info")->field('borrow_money,borrow_max,has_borrow,has_vouch,borrow_type,borrow_min,money_collect,borrow_uid')->find($borrow_id);
		
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($m['money_collect']<$binfo['money_collect']) {
				ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",0);
			}
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		
		//投标总数检测
		$capital = M('borrow_investor')->where("borrow_id={$borrow_id} AND investor_uid={$this->uid}")->sum('investor_capital');
		if(($capital+$money)>$binfo['borrow_max']&&$binfo['borrow_max']>0){
			$xtee = $binfo['borrow_max'] - $capital;
			ajaxmsg("您已投标{$capital}元，此投上限为{$binfo['borrow_max']}元，你最多只能再投{$xtee}",0);
		}
		//if($binfo['has_vouch']<$binfo['borrow_money'] && $binfo['borrow_type'] == 2) $this->error("此标担保还未完成，您可以担保此标或者等担保完成再投标");
		$need = $binfo['borrow_money'] - $binfo['has_borrow'];
		$caninvest = $need - $binfo['borrow_min'];
		if( $money>$caninvest && ($need-$money)<>0 ){
			$msg = "尊敬的{$uname}，此标还差{$need}元满标,如果您投标{$money}元，将导致最后一次投标最多只能投".($need-$money)."元，小于最小投标金额{$binfo['borrow_min']}元，所以您本次可以选择<font color='#FF0000'>满标</font>或者投标金额必须<font color='#FF0000'>小于等于{$caninvest}元</font>";
			if($caninvest<$binfo['borrow_min']) $msg = "尊敬的{$uname}，此标还差{$need}元满标,如果您投标{$money}元，将导致最后一次投标最多只能投".($need-$money)."元，小于最小投标金额{$binfo['borrow_min']}元，所以您本次可以选择<font color='#FF0000'>满标</font>即投标金额必须<font color='#FF0000'>等于{$need}元</font>";

			ajaxmsg($msg,0);
		}
		if(($need-$money)<0 ){
			ajaxmsg("尊敬的{$uname}，此标还差{$need}元满标,您最多只能再投{$need}元",0);
		}else{
			$invest_id = investMoney($this->uid,$borrow_id,$money);
		}
		 $loanconfig = FS("Webconfig/loanconfig");
		if($invest_id) {
            // 发送到乾多多
            $orders = date("YmdHi").$invest_id;
            $invest_qdd = M("escrow_account")->field('*')->where("uid={$this->uid}")->find();
            $borrow_qdd = M("escrow_account")->field('*')->where("uid={$binfo['borrow_uid']}")->find();
			
            $invest_info = M("borrow_investor")->field("reward_money, borrow_fee")->where("id={$invest_id}")->find();
			
            $secodary = '';
            import("ORG.Loan.Escrow");
            $loan = new Escrow();
			
            if($invest_info['reward_money']>0.00){  // 投标奖励
			
               // $secodary[] = $loan->secondaryJsonList($invest_qdd['qdd_marked'], $invest_info['reward_money'],'二次分配', '支付投标奖励'); 
			    $secodary['LoanInMoneymoremore'] = $invest_qdd['qdd_marked'];
				$secodary['Amount'] = $invest_info['reward_money'];
				$secodary['TransferName'] = '二次分配';
				$secodary['Remark'] = '支付投标奖励';
				
				$secodarys['reward_money'] = $secodary;
            }
            if($invest_info['borrow_fee']>0.00){  // 借款管理费
			
				
               // $secodary[] = $loan->secondaryJsonList($loanconfig['pfmmm'], $invest_info['borrow_fee'],'二次分配', '支付平台借款管理费'); 
			    $secodary['LoanInMoneymoremore'] = $loanconfig['pfmmm'];
				$secodary['Amount'] = $invest_info['borrow_fee'];
				$secodary['TransferName'] = '二次分配';
				$secodary['Remark'] = '支付平台借款管理费';
				
				$secodarys['borrow_fee'] = $secodary;
			    
            }
            
            //$secodarys && $secodarys = json_encode($secodarys);
            // 投标奖励
           // $loanList[] = $loan->loanJsonList($invest_qdd['qdd_marked'], $borrow_qdd['qdd_marked'], $orders, $borrow_id, $money, $binfo['borrow_money'],'投标',"对{$borrow_id}号投标",$secodary);
				$loanJsonList = array();
			    $loanJsonList['LoanOutMoneymoremore'] = $invest_qdd['qdd_marked'];
				$loanJsonList['LoanInMoneymoremore'] = $borrow_qdd['qdd_marked'];
				$loanJsonList['OrderNo'] = $orders;
				$loanJsonList['BatchNo'] = $borrow_id;
				$loanJsonList['Amount'] = $money;
				$loanJsonList['FullAmount'] = $binfo['borrow_money'];
				$loanJsonList['TransferName'] = '投标';
				$loanJsonList['Remark'] = "对{$borrow_id}号投标";
				//$loanJsonList['SecondaryJsonList'] = $secodarys;
				
            //$loanJsonList = json_encode($loanList);
            //$returnURL = C('WEB_URL').U("invest/investReturn");
			
			//$returnURL = 'http://'.$_SERVER ['HTTP_HOST'].U("/invest/investReturn");
          //  $notifyURL = C('WEB_URL').U("member/notify/notifys");
			
			
			//$returnURL = 'http://'.$_SERVER ['HTTP_HOST'].U("/invest/investReturn");
            $notifyURL = C('WEB_URL')."/invest/notify";
			
			
			
			
            $data1 =  $loan->transfer('',$returnURL , $notifyURL);
            $data['PlatformMoneymoremore']=$data1['PlatformMoneymoremore'];
			$data['TransferAction']=$data1['TransferAction'];
			$data['Action']=$data1['Action'];
			$data['TransferType']=$data1['TransferType'];
			$data['NeedAudit']=$data1['NeedAudit'];
			$data['RandomTimeStamp']=$data1['RandomTimeStamp'];
			$data['Remark1']=$data1['Remark1'];
			$data['Remark2']=$data1['Remark2'];
			$data['Remark3']=$data1['Remark3'];
			$data['NotifyURL']=$data1['NotifyURL']; 
			
			$data['LoanJsonList'] = $loanJsonList;
			$data['SecondaryJsonList'] = $secodarys;

			
			// $data['loanJsonLists'] = $loanJsonList;
			// $data['SecondaryJsonList'] = $secodarys;
            
			ajaxmsg($data);

        }else{
            $this->error("对不起，投标失败，请重试!");
        }
	}
	/*企业标详情页面*/
	public function tdetail(){
		
        $jsoncode = file_get_contents("php://input");
		//alogsm("tdetail",0,1,session("u_id").$jsoncode);
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['id'])||$arr['type']!=2) {
		   ajaxmsg("查询错误！",0);
		}

		$pre = C('DB_PREFIX');
		$id = intval($arr['id']);
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		
		$borrowinfo = M("transfer_borrow_info b")->join("{$pre}transfer_detail d ON d.borrow_id=b.id")->field(true)->find($id);
		$borrowinfo['progress'] = getfloatvalue($borrowinfo['transfer_out']/$borrowinfo['transfer_total'] * 100, 2);
		$borrowinfo['need'] = getfloatvalue(($borrowinfo['transfer_total'] - $borrowinfo['transfer_out'])*$borrowinfo['per_transfer'], 2 );
		$borrowinfo['updata'] = unserialize($borrowinfo['updata']);
				$memberinfo = M("members m")->field("m.id,m.customer_name,m.customer_id,m.user_name,m.reg_time,m.credits,fi.*,mi.*,mm.*")->join("{$pre}member_financial_info fi ON fi.uid = m.id")->join("{$pre}member_info mi ON mi.uid = m.id")->join("{$pre}member_money mm ON mm.uid = m.id")->where("m.id={$borrowinfo['borrow_uid']}")->find();

		$list['id'] = $id;
		$list['type'] = 2;
		$list['borrow_name'] = $borrowinfo['borrow_name'];
		$list['borrow_interest_rate'] = $borrowinfo['borrow_interest_rate'];
		$list['borrow_money'] = $borrowinfo['borrow_money'];
		$list['transfer_out'] = $borrowinfo['transfer_out'];
		$list['per_transfer'] = $borrowinfo['per_transfer'];
		$list['borrow_duration'] = $borrowinfo['borrow_duration']."个月";
		$list['progress'] = $borrowinfo['progress'];
		$list['borrow_max'] =$borrowinfo['borrow_max']?$borrowinfo['borrow_max']:"无";
		$list['transfer_total'] = $borrowinfo['transfer_total'];
		$list['transfer_leave'] = $borrowinfo['transfer_total']-$borrowinfo['transfer_out'];
		$list['transfer_back'] = $borrowinfo['transfer_back'];
		//$list['borrow_breif'] ="<p style='color:#5c5c5c;font-size:60px;font-family: Droid Sans Fallback;'>".strip_tags($borrowinfo['borrow_breif'])."</p>";
		$list['borrow_breif'] =strip_tags($borrowinfo['borrow_breif']);
		$list['reward'] = $borrowinfo['reward_rate'];
		$list['min_month'] = $borrowinfo['min_month'];
		$list['huankuan_type'] = "一次性还款";
		$minfo = M("members")->where("id={$borrowinfo['borrow_uid']}")->find();
		$list['user_name'] = $minfo['user_name'];
		$list['imgpath'] = get_avatar($borrowinfo['borrow_uid']);
		$list['addtime'] = date("Y-m-d",$borrowinfo['add_time']);
		$list['credit_rating']=getLeveIco($memberinfo['credits'],3);
		$list['need_money']=$borrowinfo['transfer_total']*$borrowinfo['per_transfer']-$borrowinfo['transfer_out']*$borrowinfo['per_transfer'];
		$list['invested_money']=$borrowinfo['transfer_out']*$borrowinfo['per_transfer'];
		if($borrowinfo['danbao']!=0 ){
			$danbao = M('article')->field('id,title')->where("type_id=7 and id={$borrowinfo['danbao']}")->find();
			$borrowinfo['danbao']=$danbao['title'];//担保机构
			$borrowinfo['danbaoid'] = $danbao['id'];
		}else{
			$borrowinfo['danbao']='暂无担保机构';//担保机构
		}
		$list['guarantee_institutions']=$borrowinfo['danbao'];
		ajaxmsg($list);
		
    }
	//企业直投详细页时的点击立即投资验证接口（第一次点）
	public function tajax_invest()	{
				
        $jsoncode = file_get_contents("php://input");
		if(!$this->uid) {
			ajaxmsg("请先登录",0);
			exit;
		}
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (intval($arr['uid'])!=$this->uid){
			ajaxmsg("查询错误3！",0);
		}
		if (!is_array($arr)||empty($arr)||empty($arr['borrow_id'])||$arr['type']!=2) {
		   ajaxmsg("查询错误4！",0);
		}
		$pre = c( "DB_PREFIX" );
		$id = intval( $arr['borrow_id'] );
		$Bconfig = require( C("APP_ROOT" )."Conf/borrow_config.php" );
		$field = "id,borrow_uid,borrow_money,borrow_interest_rate,borrow_duration,repayment_type,transfer_out,transfer_back,transfer_total,per_transfer,is_show,deadline,min_month,increase_rate,reward_rate";
		$vo = M("transfer_borrow_info" )->field($field)->find($id);
		if ($this->uid == $vo['borrow_uid'])
		{
			ajaxmsg("不能息投自己的标", 0);
		}
		if ($vo['transfer_out'] == $vo['transfer_total'])
		{
			ajaxmsg( "此标可认购份数为0", 0 );
		}
		if ($vo['is_show'] == 0)
		{
			ajaxmsg( "只能投正在借款中的标", 0 );
		}
		$vo['transfer_leve'] = $vo['transfer_total'] - $vo['transfer_out'];
		$vo['uname'] = M("members")->getFieldById($vo['borrow_uid'], "user_name");
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		$amoney = $vm['account_money']+$vm['back_money'];
		$pin_pass = $vm['pin_pass'];
		$has_pin = empty( $pin_pass ) ? 0 : 1;
		$rate = $vo['borrow_interest_rate'];
		$list['type'] = 2;
		$list['id'] = $id;
		//$list['has_pin'] = $has_pin ;//是否设置支付密码
		$list['account_money'] = $amoney;//可用余额
		$list['min_month'] = $vo['min_month'];//最小认购期限
		$list['borrow_duration'] = $vo['borrow_duration'];//最大认购期限
		$list['transfer_leave'] = $vo['transfer_total']-$vo['transfer_out'];//剩余多少份
		$list['per'] = $vo['per_transfer'];//每份多少钱
		
		/*if($has_pin == 0){
		    ajaxmsg("投标前请先设置支付密码", 0);
		}*/
		
		ajaxmsg($list);
	}
	//企业直投详细页时的点击立即投资验证接口（第二次点）
	public function tinvestcheck()
	{
		$jsoncode = file_get_contents("php://input");
		//alogsm("tinvestcheck",0,1,session("u_id").$jsoncode);
		if(!$this->uid) {
			ajaxmsg('请先登录',0);
			exit;
		}
		$arr = array();
		$arr = json_decode($jsoncode,true);
		
		//ajaxmsg($arr);die;
		if (intval($arr['uid'])!=$this->uid){
			ajaxmsg("查询错误2！",0);
		}
		if (!is_array($arr)||empty($arr)||empty($arr['borrow_id'])||empty($arr['num'])||empty($arr['month'])||$arr['type']!=2) {
		   ajaxmsg("查询错误1！",0);
		}

		
		
		$pre = C("DB_PREFIX");
		
        $_pin = $arr['pin'];
		$_borrow_id = $arr['borrow_id'];
		$_tnum = $arr['num'];
		$_month = $arr['month'];
		$pin = md5($_pin);
		$borrow_id = intval($_borrow_id);
		$tnum = intval($_tnum);
		$month = intval($_month);
		$m = M("member_money")->field('account_money,back_money,money_collect')->find($this->uid);
		$amoney = $m['account_money']+$m['back_money'];
		$uname = session("u_user_name");
		$vm = getMinfo($this->uid,"m.pin_pass");
		$pin_pass = $vm['pin_pass'];
		
		$amoney = floatval($amoney);
		$binfo = M("transfer_borrow_info")->field( "transfer_out,transfer_back,transfer_total,per_transfer,is_show,deadline,min_month,increase_rate,reward_rate,borrow_duration")->find($borrow_id);
		$max_month = $binfo['borrow_duration'];//getTransferLeftmonth($binfo['deadline']);
		$min_month = $binfo['min_month'];
		$max_num = $binfo['transfer_total'] - $binfo['transfer_out'];
		
		if($tnum<1){
			ajaxmsg("购买份数必须大于等于1份！", 3);
		}
		if($month < $min_month || $max_month < $month)
		{
			ajaxmsg("本标认购期限只能在'".$min_month."个月---".$max_month."个月'之间", 3);
		}
		if ($max_num < $tnum)
		{
			ajaxmsg( "本标还能认购最大份数为".$max_num."份，请重新输入认购份数", 3 );
		}
		$money = $binfo['per_transfer'] * $tnum;
		/*if ($pin != $pin_pass)
		{
			ajaxmsg( "支付密码错误，请重试", 0);
		}*/
		if ($amoney < $money)
		{
			$msg = "尊敬的{$uname}，您准备认购{$money}元，但您的账户可用余额为{$amoney}元，您要先去充值吗？";
			ajaxmsg($msg, 2);
		}
		else
		{
			
			$msg = "尊敬的{$uname}，您的账户可用余额为{$amoney}元，您确认认购{$money}元吗？";
			$_msg['type'] = 2;
			$_msg['id'] = $borrow_id;
			$_msg['message'] = $msg;
			ajaxmsg($_msg, 1);
		}
	}
	//企业直投详细页时的点击立即投资后进入乾多多页面的接口
	public function tinvestmoney()
	{
		$jsoncode = file_get_contents("php://input");
		//alogsm("tinvestmoney",0,1,session("u_id").$jsoncode);
		if(!$this->uid) {
			ajaxmsg('请先登录',0);
			exit;
		}
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (intval($arr['uid'])!=$this->uid){
			ajaxmsg("查询错误！",0);
		}
		if (!is_array($arr)||empty($arr)||empty($arr['borrow_id'])||empty($arr['zhifu_money'])||$arr['type']!=2) {
		   ajaxmsg("查询错误！",0);
		}

		
        $_pin = $arr['pin'];
        $_month = $arr['month'];
		$borrow_id = $arr['borrow_id'];
		
		$month = intval($_month);
		
		$binfo = M("transfer_borrow_info")->field( "borrow_max,borrow_uid,borrow_interest_rate,transfer_out,transfer_back,transfer_total,per_transfer,is_show,deadline,min_month,increase_rate,reward_rate,borrow_duration,borrow_money")->find($borrow_id);
		//$_tnum = $arr['zhifu_money']/$binfo['per_transfer'];
		$tnum =  $arr['zhifu_money']/$binfo['per_transfer'];

		$m = M("member_money")->field('account_money,back_money,money_collect')->find($this->uid);
		$amoney = $m['account_money']+$m['back_money'];
		$uname = session("u_user_name");
		
		//ajaxmsg($binfo);die;
		
		if($this->uid == $binfo['borrow_uid']) ajaxmsg("不能去投自己的标",0);
		$max_month = $binfo['borrow_duration'];//getTransferLeftmonth($binfo['deadline']);
		$min_month = $binfo['min_month'];
		$max_num = $binfo['transfer_total'] - $binfo['transfer_out'];
		if($tnum<1){
			ajaxmsg("购买份数必须大于等于1份！".$binfo['per_transfer'], 0);
		}
		if($month < $min_month || $max_month < $month){
			ajaxmsg( "本标认购期限只能在'".$min_month."个月---".$max_month."个月'之间",0);
		}
		//echo $max_num;die();
		// echo $tnum;die();
		//echo $max_num;die();

		if($max_num*$binfo['per_transfer'] < $tnum*$binfo['per_transfer']){
			ajaxmsg( "本标还能认购最大金额为".$max_num."元，请重新输入认购金额",0);
			
		}

		$map['i.investor_uid'] = $this->uid;
		$map['i.status'] = 1;
		$map['i.borrow_id']=$borrow_id;
		$map['i.loanno']=array('neq','');
		$list = getttenderlist($map, 15);
		$invested_money_t=$tnum*$binfo['per_transfer']+$list['total_money'];
		if($binfo['borrow_max']!=0)
		{
			if($binfo['borrow_max']*$binfo['per_transfer']<$invested_money_t)
			{
				ajaxmsg( "本标个人认购最大金额为".$binfo['borrow_max']*$binfo['per_transfer']."元",0);
			}
		}
		$money = $tnum;
		if($amoney < $money){
			ajaxmsg( "尊敬的{$uname}，您准备认购{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再认购",0);
			
		}
		$vm = getMinfo($this->uid,"m.pin_pass,mm.invest_vouch_cuse,mm.money_collect");
		$pin_pass = $vm['pin_pass'];
		$pin = md5($_pin);
		$tinvest_id = TinvestMoney($this->uid,$borrow_id,$tnum,$month);//投企业直投
		if($tinvest_id){
			   $loanconfig = FS("Webconfig/loanconfig");
            $orders = date("YmdHi").$tinvest_id;
			// 发送到乾多多
            $invest_qdd = M("escrow_account")->field('*')->where("uid={$this->uid}")->find();
            $borrow_qdd = M("escrow_account")->field('*')->where("uid={$binfo['borrow_uid']}")->find();
            $invest_info = M("transfer_borrow_investor")->field("reward_money, borrow_fee")->where("id={$tinvest_id}")->find();
            $secodary = '';
            import("ORG.Loan.Escrow");
            $loan = new Escrow();
			 if($invest_info['reward_money']>0.00){  // 投标奖励
			
               // $secodary[] = $loan->secondaryJsonList($invest_qdd['qdd_marked'], $invest_info['reward_money'],'二次分配', '支付投标奖励'); 
			    $secodary['LoanInMoneymoremore'] = $invest_qdd['qdd_marked'];
				$secodary['Amount'] = $invest_info['reward_money'];
				$secodary['TransferName'] = '二次分配';
				$secodary['Remark'] = '支付投标奖励';
				
				$secodarys['reward_money'] = $secodary;
            }
            if($invest_info['borrow_fee']>0.00){  // 借款管理费
			
				
               // $secodary[] = $loan->secondaryJsonList($loanconfig['pfmmm'], $invest_info['borrow_fee'],'二次分配', '支付平台借款管理费'); 
			    $secodary['LoanInMoneymoremore'] = $loanconfig['pfmmm'];
				$secodary['Amount'] = $invest_info['borrow_fee'];
				$secodary['TransferName'] = '二次分配';
				$secodary['Remark'] = '支付平台借款管理费';
				
				$secodarys['borrow_fee'] = $secodary;
			    
            }
            
            //$secodarys && $secodarys = json_encode($secodarys);
            // 投标奖励
           // $loanList[] = $loan->loanJsonList($invest_qdd['qdd_marked'], $borrow_qdd['qdd_marked'], $orders, $borrow_id, $money, $binfo['borrow_money'],'投标',"对{$borrow_id}号投标",$secodary);
				$loanJsonList = array();
			    $loanJsonList['LoanOutMoneymoremore'] = $invest_qdd['qdd_marked'];
				$loanJsonList['LoanInMoneymoremore'] = $borrow_qdd['qdd_marked'];
				$loanJsonList['OrderNo'] = 'T'.$orders;
				$loanJsonList['BatchNo'] = 'T_'.$borrow_id;
				$loanJsonList['Amount'] = $money*$binfo['per_transfer'];
				$loanJsonList['FullAmount'] = $binfo['borrow_money'];
				$loanJsonList['TransferName'] = '投标';
				$loanJsonList['Remark'] = "对{$borrow_id}号投标";
				$loanJsonList['NeedAudit']='1';
				//$loanJsonList['SecondaryJsonList'] = $secodarys;
				
            //$loanJsonList = json_encode($loanList);
            //$returnURL = C('WEB_URL').U("invest/investReturn");
           // $notifyURL = C('WEB_URL').U("notify/notifys");
		   
			$notifyURL = C('WEB_URL')."/tinvest/notify";
			
			//echo $notify;die();
			
	
			
			
            //$data =  $loan->transfer('',$returnURL , $notifyURL);
			$data1 =  $loan->transfer('', $returnURL , $notifyURL,1,1,2,1); // 自动到帐

						//$data['LoanJsonList']=$data1['LoanJsonList'];
			//$data['LoanJsonList'] = $loanJsonList;
			$data['PlatformMoneymoremore']=$data1['PlatformMoneymoremore'];
			$data['TransferAction']=$data1['TransferAction'];
			$data['Action']=$data1['Action'];
			$data['TransferType']=$data1['TransferType'];
			$data['NeedAudit']=$data1['NeedAudit'];
			$data['RandomTimeStamp']=$data1['RandomTimeStamp'];
			$data['Remark1']=$data1['Remark1'];
			$data['Remark2']=$data1['Remark2'];
			$data['Remark3']=$data1['Remark3'];
			$data['NotifyURL']=$data1['NotifyURL']; 
			
			$data['LoanJsonList'] = $loanJsonList;
			$data['SecondaryJsonList'] = $secodarys;
			ajaxmsg($data);
   //          $loanconfig = FS("Webconfig/loanconfig");
   //          $orders = date("YmdHi").$tinvest_id;
			// // 发送到乾多多
   //          $invest_qdd = M("escrow_account")->field('*')->where("uid={$this->uid}")->find();
   //          $borrow_qdd = M("escrow_account")->field('*')->where("uid={$binfo['borrow_uid']}")->find();
   //          $invest_info = M("transfer_borrow_investor")->field("reward_money, borrow_fee")->where("id={$tinvest_id}")->find();
   //          $secodary = '';
   //          import("ORG.Loan.Escrow");
   //          $loan = new Escrow();
			//  if($invest_info['reward_money']>0.00){  // 投标奖励
			
   //             // $secodary[] = $loan->secondaryJsonList($invest_qdd['qdd_marked'], $invest_info['reward_money'],'二次分配', '支付投标奖励'); 
			//     $secodary['LoanInMoneymoremore'] = $invest_qdd['qdd_marked'];
			// 	$secodary['Amount'] = $invest_info['reward_money'];
			// 	$secodary['TransferName'] = '二次分配';
			// 	$secodary['Remark'] = '支付投标奖励';
				
			// 	//$secodarys['reward_money'] = $secodary;
   //          }
   //          if($invest_info['borrow_fee']>0.00){  // 借款管理费
			
				
   //             // $secodary[] = $loan->secondaryJsonList($loanconfig['pfmmm'], $invest_info['borrow_fee'],'二次分配', '支付平台借款管理费'); 
			//     $secodary['LoanInMoneymoremore'] = $loanconfig['pfmmm'];
			// 	$secodary['Amount'] = $invest_info['borrow_fee'];
			// 	$secodary['TransferName'] = '二次分配';
			// 	$secodary['Remark'] = '支付平台借款管理费';
				
			// 	//$secodarys['borrow_fee'] = $secodary;
			    
   //          }
            
   //          //$secodarys && $secodarys = json_encode($secodarys);
   //          // 投标奖励
   //         // $loanList[] = $loan->loanJsonList($invest_qdd['qdd_marked'], $borrow_qdd['qdd_marked'], $orders, $borrow_id, $money, $binfo['borrow_money'],'投标',"对{$borrow_id}号投标",$secodary);
			// 	$loanJsonList = array();
			//     $loanJsonList['LoanOutMoneymoremore'] = $invest_qdd['qdd_marked'];
			// 	$loanJsonList['LoanInMoneymoremore'] = $borrow_qdd['qdd_marked'];
			// 	$loanJsonList['OrderNo'] = 'T'.$orders;
			// 	$loanJsonList['BatchNo'] = 'T_'.$borrow_id;
			// 	$loanJsonList['Amount'] = $money*$binfo['per_transfer'];
			// 	$loanJsonList['FullAmount'] = $binfo['borrow_money'];
			// 	$loanJsonList['TransferName'] = '投标';
			// 	$loanJsonList['Remark'] = "对{$borrow_id}号投标";
			// 	//$loanJsonList['NeedAudit']='1';
			// 	$loanJsonList['SecondaryJsonList']=$secodary;
			// $notifyURL = C('WEB_URL').U("tinvest/notify");
   //          //$data1 =  $loan->transfer('',$returnURL , $notifyURL);
   //          $data1=  $loan->transfer($loanJsonList, $returnURL , $notifyURL,1,1,2,1); // 自动到帐
			// //$data['LoanJsonList']=$data1['LoanJsonList'];
			// $data['LoanJsonList'] = $loanJsonList;
			// $data['PlatformMoneymoremore']=$data1['PlatformMoneymoremore'];
			// $data['TransferAction']=$data1['TransferAction'];
			// $data['Action']=$data1['Action'];
			// $data['TransferType']=$data1['TransferType'];
			// $data['NeedAudit']=$data1['NeedAudit'];
			// $data['RandomTimeStamp']=$data1['RandomTimeStamp'];
			// $data['Remark1']=$data1['Remark1'];
			// $data['Remark2']=$data1['Remark2'];
			// $data['Remark3']=$data1['Remark3'];
			// $data['NotifyURL']=$data1['NotifyURL'];            

			
			//$data['SecondaryJsonList'] = $secodarys;
            
			
		}else{
			$ajaxmsg("对不起，认购失败，请重试!");
		}
	}
//网站公告
	public function gg_list() {

       $id = M('article_category')->where("type_name = '网站公告'")->getField('id');
       $list=M('article')->where("type_id = {$id} ")->order('id desc')->limit('10')->select();
	   foreach ($list as $key=>$v){
		 $_list[$key]['id'] = $v['id'];
	     $_list[$key]['title'] = $v['title'];
	     $_list[$key]['art_time'] = date("Y-m-d",$v['art_time']);
	     $_list[$key]['art_img']=$v['art_img'];
 		 $_list[$key]['content']=mb_substr(str_replace('&nbsp;','',strip_tags($v['art_content'])),0,120);

	   }
	   $m_list['list']= $_list;
	   ajaxmsg($m_list);
		
	}
	///网站公告的加载更多
	public function gg_list_add() {
	   
	   $jsoncode = file_get_contents("php://input"); 
	   $arr = array();
	   $arr = json_decode($jsoncode,true);
//	   $arr['gtype'] = 0;
//	   $arr['num'] = 4;
//	   $arr['id'] = 98;
	   if(is_array($arr) && !empty($arr['id']) && isset($arr['gtype']) && !empty($arr['num'])){
	       $gtype = $arr['gtype'];
		   $id = intval($arr['id']);
		   $num = intval($arr['num']);
	   }else{
	       $gtype = 2;
		   $num = 3;
	   }
       $listid = M('article_category')->where("type_name = '网站公告'")->getField('id');
	   if($gtype == 1){  //往大查询
           $list=M('article')->where("type_id = {$listid} and id > {$id} ")->order('id asc')->limit("{$num}")->select();
	   }elseif($gtype == 0){  //往小查询
	       $list=M('article')->where("type_id = {$listid} and id < {$id} ")->order('id desc')->limit("{$num}")->select();
	   }elseif($gtype == 2){  //
	       $list=M('article')->where("type_id = {$listid} ")->order('id desc')->limit("{$num}")->select();
	   }
	   
	   
	   foreach ($list as $key=>$v){
		 $_list[$key]['id'] = $v['id'];
	     $_list[$key]['title'] = $v['title'];
	     $_list[$key]['art_time'] = date("Y-m-d",$v['art_time']);
	     $_list[$key]['art_img']=$v['art_img'];
 		 $_list[$key]['content']=mb_substr(str_replace('&nbsp;','',strip_tags($v['art_content'])),0,120);

		 
	   }
	   $m_list['list']= $_list;
	   if(is_array($m_list['list'])){
	       ajaxmsg($m_list);
	   }else{
	       ajaxmsg();
	   }
		
	}
	//网站公告的详细信息
	public function gg_show() {
	   //$id = 100;
		$jsoncode = file_get_contents("php://input");
		//alogsm("gg_show",0,1,$jsoncode);
		
		$arr = array();
		$arr = json_decode($jsoncode,true);
//		if (!is_array($arr)||empty($arr)||empty($arr['id'])) {
//		   ajaxmsg("查询错误！",0);
//		}
        $id = $arr["id"];
        $content=M('article')->find($id);
		$_content['id'] = $content['id'];
		$_content['title'] = $content['title'];
		$_content['art_time'] = date("Y-m-d H:i",$content['art_time']);
		$_content['art_content'] = $content['art_content'];
        ajaxmsg($_content);
		
		
	}
	//最新动态（相当于媒体报道）的接口
	public function news_list() {
	   //$id=4;
      // $id = M('article_category')->where("type_name = '行业新闻'")->getField('id');
	   $id=2;
       $list=M('article')->where("type_id = {$id} ")->order('id desc')->limit('10')->select();
	   foreach ($list as $key=>$v){
		 $_list[$key]['id'] = $v['id'];
	     $_list[$key]['title'] = $v['title'];
	     $_list[$key]['art_time'] = date("Y-m-d",$v['art_time']);
 		 $_list[$key]['content']=mb_substr(str_replace('&nbsp;','',strip_tags($v['art_content'])),0,120);
	     
	   }
	   $m_list['list']= $_list;
	   ajaxmsg($m_list);
		
	}
	//最新动态（相当于媒体报道）加载更多的接口
	public function news_list_add() {
	   
	   $jsoncode = file_get_contents("php://input"); 
	   $arr = array();
	   $arr = json_decode($jsoncode,true);
//	   $arr['gtype'] = 0;
//	   $arr['num'] = 4;
//	   $arr['id'] = 98;
	   if(is_array($arr) && !empty($arr['id']) && isset($arr['gtype']) && !empty($arr['num'])){
	       $gtype = $arr['gtype'];
		   $id = intval($arr['id']);
		   $num = intval($arr['num']);
	   }else{
	       $gtype = 2;
		   $num = 7;
	   }
      // $listid = M('article_category')->where("type_name = '行业新闻'")->getField('id');
       $listid=2;
	   if($gtype == 1){  //往大查询
           $list=M('article')->where("type_id = {$listid} and id > {$id} ")->order('id asc')->limit("{$num}")->select();
	   }elseif($gtype == 0){  //往小查询
	       $list=M('article')->where("type_id = {$listid} and id < {$id} ")->order('id desc')->limit("{$num}")->select();
	   }elseif($gtype == 2){  //
	       $list=M('article')->where("type_id = {$listid} ")->order('id desc')->limit("{$num}")->select();
	   }
	   
	 //  var_dump($list);die();
	   foreach ($list as $key=>$v){
		 $_list[$key]['id'] = $v['id'];
	     $_list[$key]['title'] = $v['title'];
	     $_list[$key]['art_time'] = date("Y-m-d",$v['art_time']);
	    // $_list[$key]['art_img']=$v['art_img'];
 		 $_list[$key]['content']=mb_substr(str_replace('&nbsp;','',strip_tags($v['art_content'])),0,120);
		 
	   }
	   $m_list['list']= $_list;
	   if(is_array($m_list['list'])){
	       ajaxmsg($m_list);
	   }else{
	       ajaxmsg();
	   }
		
	}
	//最新动态（相当于媒体报道）详细信息
	public function news_show() {
	   //$id = 100;
		$jsoncode = file_get_contents("php://input");
		//alogsm("gg_show",0,1,$jsoncode);
		
		$arr = array();
		$arr = json_decode($jsoncode,true);
//		if (!is_array($arr)||empty($arr)||empty($arr['id'])) {
//		   ajaxmsg("查询错误！",0);
//		}
        $id = $arr["id"];
        $content=M('article')->find($id);
		$_content['id'] = $content['id'];
		$_content['title'] = $content['title'];
		$_content['art_time'] = date("Y-m-d H:i",$content['art_time']);
		$_content['art_content'] = $content['art_content'];
        ajaxmsg($_content);
		
		
	}
	//检测是否可以更新新版本（没用）
	public function version(){
		$jsoncode = file_get_contents("php://input");
		//alogsm("version",0,1,$jsoncode);
		$arr = array();
		$arr = json_decode($jsoncode,true);
		$datag = FS("Webconfig/msgconfig");//get_global_setting();
		$newversion = $datag['baidu']['apkVersion'];
		if(is_array($arr)&&(!empty($arr))&&(!empty($arr['version']))&&((float)$arr['version'])<((float)$newversion)){
		    $content['path'] = $datag['baidu']['apkPath'];
			ajaxmsg($content,0);
		}else{
		    ajaxmsg();
		}
		
	}
	   /**
    * 检测版本（相当于手机上的版本升级）的接口
    * @param [float] [vid] [version id]
    * @return [json] [Status]
    * @author [yudianguo] <[yudianguosoftware@163.com]>
    * 
    */
	public function appverison()
	{
		//11
		$arr=array();
		$jdata['version']="3.0";
		//$jdata['path']="http://www.baidu.com";
		ajaxmsg($jdata,1);
	}  
	//手机喇叭上的广告图（可多图传）
	public function getbanner()
	{
		//$bannder=get_ad(123);
		$id=10;
		$stype = "home_ad".$id;
	    if(!S($stype)){
	    $list=array();
		$condition['id']=array('eq',$id);
		$_list = M('ad')->field('ad_type,content')->where($condition)->find();
		if($_list['ad_type']==1) $_list['content']=unserialize($_list['content']);
		$list = $_list;
		S($stype,$list,3600*C('HOME_CACHE_TIME')); 
		}else{
		$list = S($stype);
		}

		if($list['ad_type']==0 || !$list['content']){
		if(!$list['content']){
			ajaxmsg("获取失败",2);
		}
		}
		else
		{
			foreach ($list['content'] as $key => $v) {
				$t_list[$key]=trim(C('WEB_URL')."/".$v['img']);
			}
			$tlist['img']=$t_list;
			$tlist['version']='1.0';
			ajaxmsg($tlist);
		}
		
		//var_dump($key);
	}
			//投资计算器
    public function tool($id,$amount,$type){
  		//$jsoncode = file_get_contents("php://input");
		// $arr = array();
		// $arr = json_decode($jsoncode,true);
		// if (!is_array($arr)||empty($arr)||empty($arr['id'])||empty($arr['amount'])||empty($arr['type'])) {
		//    ajaxmsg("查询错误！",0);
		// }
		$arr['id']=$id;
		$arr['amount']=$amount;
		$arr['type']=$type;
		$pre = C('DB_PREFIX');
		if($arr['type']==2)
		{
			$borrowinfo = M("transfer_borrow_info b")->join("{$pre}transfer_detail d ON d.borrow_id=b.id")->field(true)->find($arr['id']);
			$date_limit = intval($borrowinfo['borrow_duration']);//投资期限
			$rate = floatval($borrowinfo['borrow_interest_rate']);//投资利率
			$reward_rate = floatval($borrowinfo['reward_rate']);//借款奖励
			$datag = get_global_setting( );
			$date_type = 1;//投资类型：1：月；2：日
			$repayment_type = intval($borrowinfo['repayment_type']);
			$data['bank']=$arr['amount']*0.35*$borrowinfo['borrow_duration']/12/100;
			//$data['allbank']=$borrowinfo['borrow_money']*0.35*$borrowinfo['borrow_duration']/12/100;
			$data['fund']=$arr['amount']*4.5*$borrowinfo['borrow_duration']/12/100;
			//$data['allfund']=$borrowinfo['borrow_money']*4.5*$borrowinfo['borrow_duration']/12/100;
			// var_dump($borrowinfo);die();
		}else{
			$m=M("borrow_info bi");
			$borrowinfo = $m->field('bi.id as bid,bi.*,ac.title,ac.id')->join('lzh_article ac on ac.id= bi.danbao')->where('bi.id='.$arr['id'])->find();
			$date_limit = intval($borrowinfo['borrow_duration']);//投资期限
			$rate = floatval($borrowinfo['borrow_interest_rate']);//投资利率
			$reward_rate = floatval($borrowinfo['reward_num']);//借款奖励
			$datag = get_global_setting( );
			$date_type = (intval($borrowinfo['repayment_type'])==1)?2:1;//投资类型：1：月；2：日
			$repayment_type = intval($borrowinfo['repayment_type']);
			if($date_type==1){
				$data['bank']=$arr['amount']*0.35*$borrowinfo['borrow_duration']/12/100;
				$data['fund']=$arr['amount']*4.5*$borrowinfo['borrow_duration']/12/100;
				// $data['allbank']=$borrowinfo['borrow_money']*0.35*$borrowinfo['borrow_duration']/12/100;

			}else{
				$data['bank']=$arr['amount']*0.35*$borrowinfo['borrow_duration']/365/100;
				$data['fund']=$arr['amount']*4.5*$borrowinfo['borrow_duration']/365/100;

				// $data['allbank']=$borrowinfo['borrow_money']*0.35*$borrowinfo['borrow_duration']/365/100;

			}
				
				// $data['allfund']=$borrowinfo['borrow_money']*4.5*$borrowinfo['borrow_duration']/12/100;

		}

		$amount = round(floatval($arr['amount']),2);//投资金额
		$invest_manage =$datag['fee_invest_manage'];//利息管理费
		$rate_type = 1;
		if ($repayment_type !=1 && $rate_type==2) 	$rate = $rate*365;
		if ($repayment_type ==1 && $rate_type==1) 	$rate = $rate/365;
	
		$repay_detail['reward_money'] = round($amount*$reward_rate/100,2);
		$repay_detail['invest_money'] = $amount - $repay_detail['reward_money'];
		switch ($repayment_type) {
			case '1'://按天到期还款
				$repay_detail['repayment_money'] = round($amount*($rate*$date_limit*(100-$invest_manage)/100+100)/100,2);
				//echo $repay_detail['repayment_money'];die();
				$repay_detail['interest'] = $repay_detail['repayment_money'] - $amount;
				$repay_detail['day_apr'] = round(($repay_detail['repayment_money']-$repay_detail['invest_money'])*100/($repay_detail['invest_money']*$date_limit),2); 
				$repay_detail['year_apr'] = round($repay_detail['day_apr']*365,2); 
				$repay_detail['month_apr'] = round($repay_detail['day_apr']*365/12,2); 
				break;
			case '4'://到期还本息
				$repay_detail['repayment_money'] = round(($amount+$amount*($date_limit*$rate/12/100)*(100-$invest_manage)/100),2); 
				$repay_detail['interest'] = $repay_detail['repayment_money'] - $amount;
				$repay_detail['month_apr'] = round(($repay_detail['repayment_money']-$repay_detail['invest_money'])*100/($repay_detail['invest_money']*$date_limit),2); 
				$repay_detail['year_apr'] = round($repay_detail['month_apr']*12,2); 
				$repay_detail['day_apr'] = round($repay_detail['month_apr']*12/365,2);
				break;
			case '3'://每月还息到期还本
				$repay_detail['repayment_money'] = round($amount*($rate*$date_limit*(100-$invest_manage)/100/12+100)/100,2);
				$repay_detail['interest'] = $repay_detail['repayment_money'] - $amount;
				$repay_detail['month_apr'] = round(($repay_detail['repayment_money']-$repay_detail['invest_money'])*100/($repay_detail['invest_money']*$date_limit),2); 
				$repay_detail['year_apr'] = round($repay_detail['month_apr']*12,2); 
				$repay_detail['day_apr'] = round($repay_detail['month_apr']*12/365,2);

					$interest = round($amount*$rate*(100-$invest_manage)/100/12/100,2);//利息等于应还金额乘月利率
				$repay = $repay_detail['repayment_money'];
					for($i=0;$i<$date_limit;$i++){
  					if ($i+1 == $date_limit){
  						$capital = $amount;//本金只在最后一个月还，本金等于借款金额除季度
  						$repay = $interest+$capital;
  					}else{
  						$capital = 0;
  						$repay = $repay- $interest;
  					} 	
  
				  	$_result[$i]['repayment_money'] = $interest+$capital;
				  	$_result[$i]['interest'] = $interest;
				  	$_result[$i]['capital'] = $capital;
					$_result[$i]['last_money'] = $repay;
					}
				break;
			case '5'://先息后本
				$repay_detail['interest'] = round(($amount*($rate/12/100)*$date_limit)*((100-$invest_manage)/100),2);
				$repay_detail['invest_money'] -= $repay_detail['interest'];
				$repay_detail['repayment_money'] = $amount; 

				$repay_detail['month_apr'] = round(($repay_detail['repayment_money']-$repay_detail['invest_money'])*100/($repay_detail['invest_money']*$date_limit),2); 
				$repay_detail['year_apr'] = round($repay_detail['month_apr']*12,2); 
				$repay_detail['day_apr'] = round($repay_detail['month_apr']*12/365,2);
				break;
			case '2'://按月分期还款
			default:
				$month_apr = $rate/(12*100);
				$_li = pow((1+$month_apr),$date_limit);
				$repayment = ($_li!=1)?round($amount * ($month_apr * $_li)/($_li-1),2):round($amount/$date_limit,2);
				$repay_detail['repayment_money'] = round(($repayment*$date_limit-$amount)*(100-$invest_manage)/100+$amount,2);
				$repay_detail['interest'] = $repay_detail['repayment_money'] - $amount;

				$repay = $repay_detail['repayment_money'];
				for($i=0;$i<$date_limit;$i++){
					if ($i==0){
						$interest = round($amount*$month_apr,2);
					}else{
						$_lu = pow((1+$month_apr),$i);
						$interest = round(($amount*$month_apr - $repayment)*$_lu + $repayment,2);
					}
					$fee = $interest*$invest_manage/100;

					$_result[$i]['repayment_money'] = getFloatValue($repayment-$fee,2);
					$_result[$i]['interest'] = getFloatValue($interest-$fee,2);
					$_result[$i]['capital'] = getFloatValue($repayment-$interest,2);

					if($i+1 != $date_limit)	$repay = $repay-$_result[$i]['repayment_money'];
					else $repay = 0;
					$_result[$i]['last_money'] = $repay;
				}

				$month_apr2 = ($repay_detail['repayment_money']-$repay_detail['invest_money'])/($repay_detail['invest_money']*$date_limit);
				$rekursiv = 0.001;
				for ($i=0; $i < 100; $i++) { 
					$_li2 = pow((1+$month_apr2),$date_limit);
					$repay = $repay_detail['invest_money'] * $date_limit * ($month_apr2 * $_li2)/($_li2-1);
					if($repay<$repay_detail['repayment_money']*0.99999) {
						$month_apr2 += $rekursiv;
					}elseif($repay>$repay_detail['repayment_money']*1.00001) {
						$month_apr2 -= $rekursiv*0.9;
						$rekursiv *= 0.1;
					}else break;
				}
				$repay_detail['month_apr'] = round($month_apr2*100,2); 

				$repay_detail['year_apr'] = round($repay_detail['month_apr']*12,2); 
				$repay_detail['day_apr'] = round($repay_detail['month_apr']*12/365,2);
				break;
			}
			$repay_detail['total_interest'] = round($repay_detail['repayment_money'] - $repay_detail['invest_money'],2);

			// $this->assign('repayment_type',$repayment_type);
			// $this->assign('month',$date_limit);
			// $this->assign('repay_list',$_result);
			// $this->assign('repay_detail',$repay_detail);
			$data['reward_rate']=$repay_detail['total_interest'];
		//	$data['allreward_rate']=$repay_detail['total_interest'];
		
			// var_dump($repay_detail);die();
			return $data;
			//ajaxmsg($data);
			// var_dump($repay_detail);die();
			
			// $data['html'] = $this->fetch('tool2_res');
			// exit(json_encode($data));
		
	}
	public function tool_sum()
	{
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['id'])||empty($arr['amount'])||empty($arr['type'])||empty($arr['need'])) {
		   ajaxmsg("查询错误！",0);
		}
		$data_invest=$this->tool($arr['id'],$arr['amount'],$arr['type']);
		$data['bank']=$data_invest['bank'];
		$data['fund']=$data_invest['fund'];
		$data['reward_rate']=$data_invest['reward_rate'];
		$data_need=$this->tool($arr['id'],$arr['need'],$arr['type']);
		$data['bank_percent']=(int)(round($data_invest['bank']/$data_need['reward_rate'],2)*100);
		$data['fund_percent']=(int)(round($data_invest['fund']/$data_need['reward_rate'],2)*100);
		$data['reward_percent']=(int)(round($data_invest['reward_rate']/$data_need['reward_rate'],2)*100);
		//var_dump($data);die();
		ajaxmsg($data);
		// var_dump($data1);die();

	}
	//发布借款列表详情页的接口
	public function jiekuan_list()
	{
		
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if(!$this->uid) {
			ajaxmsg("请先登录",0);
			exit;
		}
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		$pre = C('DB_PREFIX');
		$id = intval($arr['id']);
		//$id = 30;
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		//$vo = M('members m')->field('mm.account_money,mm.back_money,(mm.account_money+mm.back_money) all_money,m.user_leve,m.time_limit')->join("{$pre}member_money mm on mm.uid = //m.id")->where("m.id={$this->uid} AND m.pin_pass='{$pwd}'")->find();
		$vminfo = M('members m')->field("m.user_leve,m.time_limit,m.is_borrow,m.is_vip")->where("m.id={$this->uid}")->find();
		if($vminfo['is_vip']==0){
			$_xoc = M('borrow_info')->where("borrow_uid={$this->uid} AND borrow_status in(0,2,4)")->count('id');
			if($_xoc>0)  ajaxmsg("您有一个借款中的标，请等待审核",0);

			if(!($vminfo['user_leve']>0 && $vminfo['time_limit']>time())) ajaxmsg("请先通过VIP审核再发标",0);
			
			if($vminfo['is_borrow']==0){
				ajaxmsg("您目前不允许发布借款，如需帮助，请与客服人员联系！",0);
				$this->assign("waitSecond",3);
			}
			
			$vo = getMemberDetail($this->uid);
			if($vo['province']==0 && $vo['province_now ']==0 && $vo['province_now ']==0 && $vo['city']==0 && $vo['city_now']==0 ){
				ajaxmsg("请先填写个人详细资料后再发标",0);
			}
		}

		$gtype = text($arr['type']);
		$vkey = md5(time().$gtype);
		switch($gtype){
			case "1"://普通标
				$borrow_type=1;
			break;
			case "2"://新担保标
				$borrow_type=2;
			break;
			case "4"://净值标
				$borrow_type=4;
			break;
			case "5"://抵押标
				$borrow_type=5;
			break;
		}
		cookie($vkey,$borrow_type,3600);
		$borrow_duration_day = explode("|",$this->glo['borrow_duration_day']);
		$day = range($borrow_duration_day[0],$borrow_duration_day[1]);
		$day_time=array();
		foreach($day as $v){
			$day_time[$v] = $v."天";
		}

		$borrow_duration = explode("|",$this->glo['borrow_duration']);
		$month = range($borrow_duration[0],$borrow_duration[1]);
		$month_time=array();
		foreach($month as $v){
			$month_time[$v] = $v."个月";
		}
		$rate_lixt = explode("|",$this->glo['rate_lixi']);
		$borrow_config = require C("APP_ROOT")."Conf/borrow_config.php";
		
		//是否有投标奖励,是否有投标待收限制,是否定向标
		if(is_set($arr['tenderAward'])){
			$data['tenderAward']=$arr['tenderAward'];
		}elseif(is_set($arr['restricted'])){
			$data['restricted']=$arr['restricted'];
		}elseif(is_set($arr['leading'])){
			$data['leading']=$arr['leading'];
			
		}else{
			$data['tenderAward']='';
			$data['restricted']='';
			$data['leading']='';
			
		}

		$data['borrow_use']=$this->gloconf['BORROW_USE'];
		$data['borrow_min']=$this->gloconf['BORROW_MIN'];
		$data['borrow_max']=$this->gloconf['BORROW_MAX'];
		$data['borrow_time']=$this->gloconf['BORROW_TIME'];
		$data['BORROW_TYPE']=$borrow_config['BORROW_TYPE'];
		$data['borrow_type']=$borrow_type;
		$data['borrow_day_time']=$day_time;
		$data['borrow_month_time']=$month_time;
		$data['REPAYMENT_TYPE']=$borrow_config['REPAYMENT_TYPE'];
		$data['rate_lixt']=$rate_lixt;
		ajaxmsg($data);
		
		
		
	}
	//发布借款保存接口
	public function save()
	{
		
		
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if(!$this->uid||$arr['uid']!=$this->uid){
			ajaxmsg("请先登录",0);
			exit;
		}
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		$pre = C('DB_PREFIX');
		$id = intval($arr['id']);
		//$id = 30;
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		//相关的判断参数
		$rate_lixt = explode("|",$this->glo['rate_lixi']);
		$borrow_duration = explode("|",$this->glo['borrow_duration']);
		$borrow_duration_day = explode("|",$this->glo['borrow_duration_day']);
		$fee_borrow_manage = explode("|",$this->glo['fee_borrow_manage']);
		$vminfo = M('members m')->join("{$pre}member_info mf ON m.id=mf.uid")->field("m.user_leve,m.time_limit,mf.province_now,mf.city_now,mf.area_now,m.is_vip,m.is_borrow")->where("m.id={$this->uid}")->find();
		//var_dump($vminfo);die;
		if($vminfo['is_vip']==0){
			$_xoc = M('borrow_info')->where("borrow_uid={$this->uid} AND borrow_status in(0,2,4)")->count('id');
			if($_xoc>0)  ajaxmsg("您有一个借款中的标，请等待审核",0);

			if(!($vminfo['user_leve']>0 && $vminfo['time_limit']>time())) ajaxmsg("请先通过VIP审核再发标",0);
			
			if($vminfo['is_borrow']==0){
				ajaxmsg("您目前不允许发布借款，如需帮助，请与客服人员联系！",0);
				//$this->assign("waitSecond",3);
			}
			
			$vo = getMemberDetail($this->uid);
			if($vo['province']==0 && $vo['province_now ']==0 && $vo['province_now ']==0 && $vo['city']==0 && $vo['city_now']==0 ){
				ajaxmsg("请先填写个人详细资料后再发标",0);
			}
		}
		
		
		$borrow['borrow_type'] = $arr['vkey'];//intval(cookie(text($_POST['vkey'])));
		//dump($borrow['borrow_type']);die;
		if($borrow['borrow_type']==0) ajaxmsg("校验数据有误，请重新发布",0);
		if(floatval($arr['borrow_interest_rate'])>$rate_lixt[1] || floatval($arr['borrow_interest_rate'])<$rate_lixt[0]) ajaxmsg("提交的借款利率不在允许范围，请重试",0);
		$borrow['borrow_money'] = intval($arr['borrow_money']);
        $_minfo = getMinfo($this->uid,"m.pin_pass,mm.account_money,mm.back_money,mm.credit_cuse,mm.money_collect");
		$_capitalinfo = getMemberBorrowScan($this->uid);
		///////////////////////////////////////////////////////
		//$vo = M('members m')->field('mm.account_money,mm.back_money,(mm.account_money+mm.back_money) all_money,m.user_leve,m.time_limit')->join("{$pre}member_money mm on mm.uid = //m.id")->where("m.id={$this->uid} AND m.pin_pass='{$pwd}'")->find();
		
		$borrowNum=M('borrow_info')->field("borrow_type,count(id) as num,sum(borrow_money) as money,sum(repayment_money) as repayment_money")->where("borrow_uid = {$this->uid} AND borrow_status=6 ")->group("borrow_type")->select();
		$borrowDe = array();
		foreach ($borrowNum as $k => $v) {
			$borrowDe[$v['borrow_type']] = $v['money'] - $v['repayment_money'];
		}
		///////////////////////////////////////////////////
		switch($borrow['borrow_type']){
			case 1://普通标
				if($_minfo['credit_cuse']<$borrow['borrow_money']) ajaxmsg("您的可用信用额度为{$_minfo['credit_cuse']}元，小于您准备借款的金额，不能发标",0);
			break;
			case 2://新担保标
			break;
			case 4://净值标
				$_netMoney = getFloatValue(0.9*$_minfo['money_collect']-$borrowDe[4],2);
				if($_netMoney<$borrow['borrow_money']) ajaxmsg("您的净值额度{$_netMoney}元，小于您准备借款的金额，不能发标",0);
			break;
			case 5://抵押标
				//$borrow_type=5;
			break;
		}
		
		$borrow['borrow_uid'] = $this->uid;
		$borrow['borrow_name'] = text($arr['borrow_name']);
		$borrow['borrow_duration'] = ($borrow['borrow_type']==3)?1:intval($arr['borrow_duration']);//秒标固定为一月
		$borrow['borrow_interest_rate'] = floatval($arr['borrow_interest_rate']);
		if(strtolower($arr['is_day'])=='yes') $borrow['repayment_type'] = 1;
		elseif($borrow['borrow_type']==3) $borrow['repayment_type'] = 2;//秒标按月还
		else $borrow['repayment_type'] = intval($arr['repayment_type']);
		if($borrow['repayment_type']=='1' || $borrow['repayment_type']=='5'){
			$borrow['total'] = 1;
		}else{
			$borrow['total'] = $borrow['borrow_duration'];//分几期还款
		}
		$borrow['borrow_status'] = 0;
		$borrow['borrow_use'] = intval($arr['borrow_use']);
		$borrow['add_time'] = time();
		$borrow['collect_day'] = intval($arr['borrow_time']);
		$borrow['add_ip'] = get_client_ip();
		$borrow['borrow_info'] = text($arr['borrow_info']);
		$borrow['reward_type'] = intval($arr['reward_type']);
		$borrow['reward_num'] = floatval($arr["reward_type_{$borrow['reward_type']}_value"]);
		$borrow['borrow_min'] = intval($arr['borrow_min']);
		$borrow['borrow_max'] = intval($arr['borrow_max']);
		//$borrow['province'] = $vminfo['province_now'];
		//$borrow['city'] = $vminfo['city_now'];
		//$borrow['area'] = $vminfo['area_now'];
		if($arr['is_pass']&&intval($arr['is_pass'])==1) $borrow['password'] = md5($arr['password']);
		$borrow['money_collect'] = floatval($arr['moneycollect']);//代收金额限制设置
		
		
		//借款费和利息
		$borrow['borrow_interest'] = getBorrowInterest($borrow['repayment_type'],$borrow['borrow_money'],$borrow['borrow_duration'],$borrow['borrow_interest_rate']);
		$borrow['borrow_fee'] = 0.00;
		
		
		if($borrow['borrow_type']==3){//秒还标
			if($borrow['reward_type']>0){
				$_reward_money = getFloatValue($borrow['borrow_money']*$borrow['reward_num']/100,2);
			}
			$_reward_money =floatval($_reward_money);
			if(($_minfo['account_money']+$_minfo['back_money'])<($borrow['borrow_fee']+$_reward_money)) ajaxmsg("发布此标您最少需保证您的帐户余额大于等于".($borrow['borrow_fee']+$_reward_money)."元，以确保可以支付借款管理费和投标奖励费用",0);
		}
		
		//投标上传图片资料（暂隐）
		foreach($arr['swfimglist'] as $key=>$v){
			if($key>10) break;
			$row[$key]['img'] = substr($v,1);
			$row[$key]['info'] = $arr['picinfo'][$key];
		}
		$borrow['updata']=serialize($row);
		
		$newid = M("borrow_info")->add($borrow);

		$suo=array();
		$suo['id']=$newid; 
        $suo['suo']=0;
        $suoid = M("borrow_info_lock")->add($suo);
		
		if($newid) ajaxmsg("借款发布成功，网站会尽快初审",1);
		else ajaxmsg("发布失败，请先检查是否完成了个人详细资料然后重试",0);
		
		
		
		
		
		
		
	}
	
	public function threejiekou()
	{
		
		
		
		$data['investor_profit'] = M("borrow_investor")->sum("investor_interest");
		
		
		///////////////为客户赚取收益///////
		
		///////////////累计配资人数////////////
		$shares_num = M("shares_apply")->group("uid")->select();
		
		$data['shares_num']=count($shares_num);
		///////////////累计配资人数////////////
		
		///////////////累计配资金额1////////////
		$data['shares_sum']= M("shares_apply")->where("status in('2,3')")->sum("shares_money");
		ajaxmsg($data,1);
		
		
	}
	//最新操盘资讯
	public function news_stock()
	{
		///////////////配资盈利列表////////////
		$shares_list = M("shares_record r")->join("lzh_shares_apply a ON a.id = r.shares_id")->where("r.profit_loss > 0")->field("r.profit_loss,a.principal,a.shares_money,a.u_name")->order("r.add_time DESC")->limit(7)->select();
		$data=getRetRate($shares_list);
		foreach ($data as $key => $v) {
			$list[$key]['profit_loss']=$v['profit_loss'];
			$list[$key]['principal']=$v['principal'];
			$list[$key]['shares_money']=$v['shares_money'];
			$list[$key]['u_name']=hidecard($v['u_name'],5);
			$list[$key]['retrate']=$v['retrate'];
		}
		$redata['list']=$list;
		ajaxmsg($redata);
	}
	//用户动态
	public function news_user()
	{
		///////////////配资列表////////////
		$shares_apply = M("shares_apply")->where("status in(2,3,6)")->field("u_name,shares_money,examine_time")->order("examine_time DESC")->limit(20)->select();
         foreach ($shares_apply as $key => $v) {
         	$list[$key]['u_name']=hidecard($v['u_name'],5);
         	$list[$key]['shares_money']=$v['shares_money'];
         	$list[$key]['examine_time']=$v['examine_time'];
         }
		$redata['list']=$list;
		ajaxmsg($redata);
	}
	//天天盈接口
	public function daystock()
	{
		
		
		
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if(!$this->uid||$arr['uid']!=$this->uid){
			ajaxmsg("请先登录",0);
			exit;
		}
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		
		$Match_small = 	M('shares_global')->field('text')->where("code = 'Match_small'")->find();
		$Match_big = 	M('shares_global')->field('text')->where("code = 'Match_big'")->find();
		$lever = M('shares_global')->field('text,code')->where("times_type = 1")->order("order_sn asc")->select();
		//var_dump($lever);die;
		foreach($lever as $k=>$v) {
			$tmp = explode("|",$v['text']);
			//var_dump($tmp);die;
			$ret[$k]['times'] = $tmp[0];
			$ret[$k]['times_interest'] = $tmp[1];
			$ret[$k]['times_open'] = $tmp[2];
			$ret[$k]['times_alert'] = $tmp[3];
			$ret[$k]['type'] = $v['code'];
		}
		
		//$this->assign("list",$ret);
		$list['list']=$ret;//(1)
		//最小配资金额与最大配资金额渲染
		//$this->assign('small',$Match_small['text']);
		//$this->assign('big',$Match_big['text']);
		$list['small']=$Match_small['text'];
		//var_dump($list['small']);die;//(1000)
		$list['big']=$Match_big['text'];
		//var_dump($list['big']);die;(1000000)
		
		if($this->uid){
			
			$uid = $this->uid;
		}else{
			
			$uid = 88;
		}
		//获取当前时间
		$time = time();
		//获取当前的小时数
		$hour = date('H',$time);
		//获取星期中的第几天
		$whatday = date('w',$time);
		//当今天是周末的时候或者今天下午两点半 或者今天是节假日 只能选下个交易日
		$res = get_holiday_data('shares_holiday');
		if($res=='1' || $whatday==6 || $whatday ==0 || $hour >= 14){//如果返回1证明处在节假日之间
			//$this->assign('holiday',1);
			$list['holiday']=1;
		}else{
			//$this->assign('holiday',0);
			$list['holiday']=0;
		}
	
		ajaxmsg($list);
		
	}
	
	
	
	//天天盈立即申请接口
	public function payment()
	{
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		
      $money = M('member_money')->where("uid = {$this->uid}")->find();
	  
	  $list['money']=$money;
	 
      
	  ajaxmsg($list);
			
	}
	//天天盈确认支付接口
	public function postdata()
	{
		
		
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		
		$days = $arr['days'];
		$stock_money = $arr['stock_money'];
		$type = $arr['type'];
		
		$istoday = $arr['istoday'];
		
		
		$glo = 	M('shares_global')->field('text')->where("code = "."'{$type}'")->find();
		$glos = explode('|',$glo['text']);
		$guarantee_money = $stock_money / $glos[0];//保证金
		$interest = $stock_money * ($glos[1] / 1000) * $days;//总利息
		$user_money = M('member_money')->where("uid = {$this->uid}")->find();
		
		//判断是否实名认证
		$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		if($ids!=1){
			ajaxmsg('您还未完成身份验证,请先进行实名认证！',2);
		}
		//判断是否手机认证
		$phones = M('members_status')->getFieldByUid($this->uid,'phone_status');
		if($phones!=1){
			ajaxmsg('您还未手机认证,请先进行手机认证！',3);
		}
		$uid = $this->uid;

		$count = getMoneylimit($this->uid);
		$all_money = $count + $guarantee_money + $interest;
		if($all_money > ($user_money['account_money'] + $user_money['back_money'])) {
			ajaxmsg('您的可用余额不足以支付您所有的配资申请费用,请等待审核完成或进行充值！',4);
		}
		
		
		$ret = stockmoney($days,$stock_money,$type,$istoday,$uid);
		//var_dump($ret);die;
		
		if($ret){
			ajaxmsg('恭喜配资成功！');
		}else{
			ajaxmsg("配资失败");
		}
		
		
		
		
	}
	//月月盈列表显示接口
	public function Monthstock()
	{
		
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		$lever = D("SharesLever")->getMonthLever();
		
		$list['lever']=$lever;
		
		$term_config = D("SharesType")->getMonthtermConfig();
		$list['term']=$term_config;
		$money_config = D("SharesType")->getMonthmoneyConfig();
		$list['min_money']=$money_config[0];
		$list['max_money']=$money_config[1];
		
		if(get_holiday_data('shares_holiday') == '1' || date('w',time()) == 6 || date('w',time()) == 0 || date('H',time()) >= 14){
			//$this->assign('holiday',1);
			$list['holiday']=1;
		}else{
			$list['holiday']=0;
		}
		
		ajaxmsg($list);
		
		
		
		
	}
   //月月盈立即申请接口
   public function paymenta()
   {
	   
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		
		$money = M('member_money')->where("uid = {$this->uid}")->find();
		
		$list['money']=$money;
		ajaxmsg($list);
		
		
		
   }
   //月月盈确认支付接口
   public function postdataa(){
	   
	   $jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		
		//查询余额是否充足
		$member_money = M('member_money')->where("uid = {$this->uid}")->find();

		//判断是否实名认证
		$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		if($ids!=1){
			ajaxmsg('您还未完成身份验证,请先进行实名认证！',0);
		}
		//判断是否手机认证
		$phones = M('members_status')->getFieldByUid($this->uid,'phone_status');
		if($phones!=1){
			ajaxmsg('您还未手机认证,请先进行手机认证！',0);
		}
		
		$uid = $this->uid;
		$money = D("SharesApply")->where("uid = {$uid} and status = 1")->sum("principal + one_manage_fee");
		//var_dump($money);die;
		$all_money = $money + $arr["principal"];
		
		//var_dump($all_money);die;
		if($all_money > ($member_money['account_money'] + $member_money['back_money'])) {
			ajaxmsg('您的可用余额不足以支付您所有的配资申请费用,请等待审核完成或进行充值！',0);
		}
		//执行添加
		//'principal':principal,'trading_time':trading_time,'duration':duration,'lever_id':lever_id
		$_POST['duration'] = $arr['duration'];
		
		$_POST['principal']=$arr['principal'];
		$_POST['trading_time']=$arr['trading_time'];
		$_POST['lever_id']=$arr['lever_id'];
		$_POST['uid'] = $this->uid;
		
		
		$ret = D("SharesApply")->addMonthStock();

		
		if($ret){
			ajaxmsg('配资成功！',1);
		}else{
			ajaxmsg('配资失败！',0);
		}
		
	}
	//我是操盘手显示列表接口
	public function Manipulator()
	{
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		$res = get_cps_trader('shares_global');
		$list['maxprincipal']=$res[0];
		$list['minprincipal']=$res[1];
		$list['dbrate']=$res[2];
		$list['noticerate']=$res[3]/100;
		$list['closerate']=$res[4]/100;
		$list['tradingday']=$res[5];
		//获取当前时间
		$time = time();
		//获取当前的小时数
		$hour = date('H',$time);
		//获取星期中的第几天
		$whatday = date('w',$time);
		//当今天是周末的时候或者今天下午两点半 或者今天是节假日 只能选下个交易日
		$res = get_holiday_data('shares_holiday');
		if($res=='1' || $whatday==6 || $whatday ==0 || $hour >= 14){//如果返回1证明处在节假日之间
				//$this->assign('holiday',1);
				$list['holiday']=1;
			}else{
				//$this->assign('holiday',0);
				$list['holiday']=1;
			}
			ajaxmsg($list);
	}
	
	
	//申请操盘接口
	 public function affirm(){
			
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		//$uid = session('u_id');
		$principal = str_replace(',','',$arr['principal']);
		//$this->assign('principals',$principal);
		$list['principals']=$principal;
		$res = getBalance('member_money',"back_money,account_money","uid='".$arr['uid']."'");
		//var_dump($res);die;
		if($res){
				$remaimonery = $res['back_money']+$res['account_money'];	//获取用户的余额(7975995.2)
				//echo $remaimonery;die;
				//用户余额减去本金计算差值
				$tmp= $remaimonery - $principal;
                //echo $tmp;die;				
				if($tmp>=0){	//如果结果大于等于0 用户足以支付本金
					//$this->assign('normal',$tmp);
					$list['normal']=$tmp;//(7973995.2)
				}else{	//用户余额不足与支付本金
					$tmp = abs($tmp);
					//$this->assign('notnormal',$tmp);
					$list['notnormal']=$tmp;
				}
				//$this->assign('remai',$remaimonery);	//账户余额
				$list['remai']=$remaimonery;//(7975995.2)
			}else{
					//$this->assign('remai',0);
					$list['remai']=0;
					$tmp = abs(0-$principal);
					//$this->assign('notnormal',$tmp);
					$list['notnormal']=$tmp;
			}

			
			
			ajaxmsg($list);
			
		}
		//操盘手确认支付接口
		public function getMeMonery(){
			
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		
		$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
			if($ids!=1){
				ajaxmsg('您还未完成身份验证,请先进行实名认证！',0);
			}
			//判断是否手机认证
			$phones = M('members_status')->getFieldByUid($this->uid,'phone_status');
			if($phones!=1){
				ajaxmsg('您还未手机认证,请先进行手机认证！',0);
			}
			 $res = get_cps_trader('shares_global');
			 $data = array();
			 $data['principal']= $arr['memonery'];		//用户的本金
			 $data['type_id'] = 3;		//类型id 3代表操盘手
			 $data['uid'] = $_SESSION['u_id'];	//申请人uid
			 $data['lever_ratio'] = $res[2];		//倍率
			 $data['order'] = 'cps_'.time().mt_rand(1000,100000);	//订单号
			 $data['shares_money'] = $data['principal']*$res[2];	//配资金额
			 $noticerate = $res[3]/100;
			 $closerate = $res[4]/100;
			 $data['open'] =  $closerate * $data['principal']+$data['shares_money'];	//平仓线 = 平仓线比率*本金+操盘资金
			 $data['alert']  = $noticerate * $data['principal']+$data['shares_money'];	//警戒线 = 平仓线比率*本金+操盘资金
			 $data['open_ratio'] = $res[4];		//平仓线比率
			 $data['alert_ratio'] = $res[3];		//警戒线比率
			 $data['add_time'] = time();
			 $data['ip_address'] = get_client_ip();	//获取客户端ip
			 $data['status'] = 1;	//待审核
			 $data['duration'] = $res[5];	//交易天数
			 $data['total_money'] =  $data['principal'] +$data["shares_money"];	//总操盘资金 = 用户本金+配资金额
			 $data['trading_time'] = $arr['istoday'];	//是否今天交易
			 $data['u_name'] = $_SESSION['u_user_name'];
			 
			//	查询用户余额 如果用户余额足以支付则提交申请，不足以支付的时候返回配资失败
			 
			//用户id
			$id = $_SESSION['u_id'];
			$result = getBalance('member_money',"back_money,account_money","uid=$id");
			if($result){//查询成功
				$total_money = $result['back_money']+$result['account_money'];	//获取用户的余额
				if($total_money-$data['principal'] >=0){//用户的余额足够支付保证金
					//扣除保证金
					$deduct= $result["back_money"]-$data['principal'] ;	
					if($deduct >=0){
						$update['back_money'] = $deduct;
						$umoney = M("member_money")->where("uid=$id")->save($update);
						if(!$umoney){
							echo '1';
							exit;
						}else{//写入到日志
							$ainfo = $data['order'].'我是操盘手订单支付保证金';
							$areturnlog = pzmembermoneylod($data['principal'],$data['uid'],$ainfo,'',52);
						}
					}else{
						$update['account_money'] = $result['account_money']-abs($deduct);
						$umoney = M("member_money")->where("uid=$id")->save($update);
						if(!$umoney){//更新失败
							echo '1';
							exit;
						}else{
							$ainfo = $data['order'].'我是操盘手订单支付保证金';
							$areturnlog = pzmembermoneylod($data['principal'],$data['uid'],$ainfo,'',52);
						}

					}
					$addapply = M('shares_apply');
					$res = $addapply->add($data);
					if($res){
					 	/*echo '0';	//成功
					 	exit;
						*/
						ajaxmsg("成功");
					 }else{
					 	/*echo '1';	//失败
					 	exit;	
						*/
						ajaxmsg("失败",1);
					}				
				}else{
					/*echo '2';	//余额不足
					exit;
					*/
					ajaxmsg("余额不足",2);
				}
			}else{
				/*echo '1';
				exit;
				*/
				ajaxmsg("退出",1);
			}
			
		  
   }
	//免费体验立即申请接口
	public function freestock()
	{
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		
		$money = M('member_money')->where("uid = {$this->uid}")->find();
		//$this->assign("money",$money);
		ajaxmsg($money);
	}
	//免费体验确认支付接口
	public function postdatab()
	{
		$jsoncode = file_get_contents("php://input");
		$arr = array();
		$arr = json_decode($jsoncode,true);
		if (!is_array($arr)||empty($arr)||empty($arr['uid'])) {
		   ajaxmsg("查询错误！",0);
		}
		$user_money = M("member_money")->where("uid = {$this->uid}")->find();
		
		$quota_map['status'] = array("not in","1,4");
		$quota_map['uid'] = $this->uid;
		$quata_num = D("shares_apply")->where("(status not in(1,4) AND uid = {$this->uid}) OR (status = 1 AND type_id = 4 AND uid = {$this->uid})")->count();
		if($quata_num != 0) {
			ajaxmsg('很抱歉,您不具备免费体验配资资格！',0);
		}
		
		//当天范围
		$today_start = strtotime(date("Y-m-d 00:00:00",time()));
		$today_end = strtotime(date("Y-m-d 23:59:59",time()));
		$free_map = array();
		$free_map['status'] = 4;
		$free_map['add_time'] = array("between",array($today_start,$today_end));
		$free_num = D("shares_apply")->where($free_map)->count();
		
		//判断是否满足免费体验名额
		if($free_num >= $this->glob['free_num']) {
			ajaxmsg('今日免费体验名额已满,请明天再来！',0);
		}
		//判断用户是否登录
		if(session('u_id')==null){
			ajaxmsg('您还没有登录，请先登录！',2);
		}
		
		$uid = $this->uid;

		$count = getMoneylimit($this->uid);
		$all_money = $count + 1;
		if($all_money > ($user_money['account_money'] + $user_money['back_money'])) {
			ajaxmsg('您的可用余额不足以支付您所有的配资申请费用,请等待审核完成或进行充值！',4);
		}
		
		//执行添加
		$_POST['uid'] = $this->uid;
		$ret = D("SharesApply")->addFreeStock();
		if($ret){
			ajaxmsg('恭喜配资成功！',1);
		}else{
			ajaxmsg('恭喜配资失败！',0);
		}
		
	}
	
	//我要理财接口
	public function invest()
	{
		echo "dufudf";
	}
	
	
} 
