<?php
// 本类由系统自动生成，仅供测试用途
class StockAction extends HCommonAction {
	public function index(){
		
		$Match_small = 	M('shares_global')->field('text')->where("code = 'Match_small'")->find();

		
		//var_dump($Match_small);die;

		$Match_big = 	M('shares_global')->field('text')->where("code = 'Match_big'")->find();
		$lever = M('shares_global')->field('text,code')->where("times_type = 1")->order("order_sn asc")->select();
		//var_dump($lever);die;
		foreach($lever as $k=>$v) {
			$tmp = explode("|",$v['text']);
			$ret[$k]['times'] = $tmp[0];
			$ret[$k]['times_interest'] = $tmp[1];
			$ret[$k]['times_open'] = $tmp[2];
			$ret[$k]['times_alert'] = $tmp[3];
			$ret[$k]['type'] = $v['code'];
		}
		//dump($ret);die;
		
		$this->assign("list",$ret);
		
		//最小配资金额与最大配资金额渲染
		$this->assign('small',$Match_small['text']);
		$this->assign('big',$Match_big['text']);
		
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
		    //echo "holiday111";die;
			$this->assign('holiday',1);
		}else{
			//echo "holiday222";die;
			$this->assign('holiday',0);
		}
		
		//配资排行榜
		
		$shares_list = M('shares_apply a')->field("a.shares_money,a.add_time,a.duration,m.user_name")->join("lzh_members m ON m.id = a.uid")->where("a.type_id = 1")->order("a.id DESC and status in(2,3,6)")->select();
		$this->assign("shares_list",$shares_list);
		$this->assign("count",count($shares_list));
		$this->assign('uid',$uid);
		
		$this->display();
		
	}
	
	
	public function payment(){
		//echo "9689568";die;
		$money = M('member_money')->where("uid = {$this->uid}")->find();

		
		//获取当前时间
		$time = time();
		//获取当前的小时数
		$hour = date('H',$time);
		//获取星期中的第几天
		$whatday = date('w',$time);
		//当今天是周末的时候或者今天下午两点半 或者今天是节假日 只能选下个交易日
		$res = get_holiday_data('shares_holiday');
		
		if($res=='1' || $whatday==6 || $whatday ==0 || $hour >= 14){//如果返回1证明处在节假日之间
			
			$this->assign('holiday',1);
		}else{
			$this->assign('holiday',0);
		}

		$this->assign("money",$money);
		$this->display("day_confirm");
	}
	public function postdata(){
		
		//if(!$this->uid) $this->qingxiana();exit;
		
		
		$days = intval($_POST['days']);
		$stock_money = $_POST['stock_money'];
		$type = $_POST['type'];
		$istoday = $_POST['istoday'];
		if(!$istoday){
			
			echo jsonmsg('数据有误！',0);exit;
		}elseif(!$type){
			
			echo jsonmsg('数据有误！',0);exit;
		}elseif($days < 2 || $days > 30){
			
			echo jsonmsg('配资天数有误！',0);exit;
		}elseif($stock_money < 1000){
			
			echo jsonmsg('配资金额小于最小配资金额！',0);exit;
		}
		$uid = $this->uid;
		
		$glo = 	M('shares_global')->field('text')->where("code = "."'{$type}'")->find();
		$glos = explode('|',$glo['text']);
		$guarantee_money = $stock_money / $glos[0];//保证金
		$interest = $stock_money * ($glos[1] / 1000) * $days;//总利息
		$user_money = M('member_money')->where("uid = {$this->uid}")->find();
		
		/* //判断是否实名认证
		$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		if($ids!=1){
			echo jsonmsg('您还未完成身份验证,请先进行实名认证！',2);exit;
		} */
		//判断是否手机认证
		/*$phones = M('members_status')->getFieldByUid($this->uid,'phone_status');
		if($phones!=1){
			echo jsonmsg('您还未手机认证,请先进行手机认证！',3);exit;
		}*/
		//判断用户是否登录
		if(session('u_id')==null){
			echo jsonmsg('您还没有登录，请先登录！',2);exit;
		}
		$uid = $this->uid;

		$count = getMoneylimit($this->uid);
		$all_money = $count + $guarantee_money + $interest;
		
		if($all_money > ($user_money['account_money'] + $user_money['back_money'])) {
			echo jsonmsg('您的可用余额不足以支付您所有的配资申请费用,请等待审核完成或进行充值！',4);exit;
		}
		
		$ret = stockmoney($days,$stock_money,$type,$istoday,$uid);
		if($ret){

			echo jsonmsg('恭喜配资成功！',1);

		}else{
			echo jsonmsg('Sorry,配资失败！',0);
		}
		
		//dump($daydata);die;
		
		
	}
	/*public function qingxiana(){
		
		
		$this->display("aa");
	}
	*/
	
	public function contract(){
		
		$this->display();
	}

	
	//我是操盘手列表
	public function caopan(){
		
		//查询数据分配到模板
			$res = get_cps_trader('shares_global');
			
			//dump($res);die;
			$this->assign('maxprincipal',$res[0]);	//最大本金
			$this->assign('minprincipal',$res[1]);	//最小本金
			$this->assign('dbrate',$res[2]);	//倍率
			$noticerate = $res[3]/100;
			$this->assign('noticerate',$noticerate);	//警戒线倍率
			$closerate = $res[4]/100;
			$this->assign('closerate',$closerate);		//平仓线倍率
			$this->assign('tradingday',$res[5]);	//交易天数
			//获取当前时间
			$time = time();
			//获取当前的小时数
			$hour = date('H',$time);
			//获取星期中的第几天
			$whatday = date('w',$time);
			//当今天是周末的时候或者今天下午两点半 或者今天是节假日 只能选下个交易日
			$res = get_holiday_data('shares_holiday');
			if($res=='1' || $whatday==6 || $whatday ==0 || $hour >= 14){//如果返回1证明处在节假日之间
				$this->assign('holiday',1);
			}else{
				$this->assign('holiday',0);
			}
			$this->display();
			
	}
	//申请操盘
	public function affirm(){
		  //dump($_GET);die;
			//获取用户ID
			$uid = session('u_id');
			//$istoday=$_GET['istoday'];
			
			//echo $istoday;die;
			//获取用户的本金
			$principal = str_replace(',','',$_GET['principal']);
			$this->assign('principals',$principal);
			$res = getBalance('member_money',"back_money,account_money","uid=$uid");
			if($res){
				$remaimonery = $res['back_money']+$res['account_money'];	//获取用户的余额
				//用户余额减去本金计算差值
				$tmp= $remaimonery - $principal;		
				if($tmp>=0){	//如果结果大于等于0 用户足以支付本金
					$this->assign('normal',$tmp);
				}else{	//用户余额不足与支付本金
					$tmp = abs($tmp);
					$this->assign('notnormal',$tmp);
				}
				$this->assign('remai',$remaimonery);	//账户余额
			}else{
					$this->assign('remai',0);
					$tmp = abs(0-$principal);
					$this->assign('notnormal',$tmp);
			}
           //$this->assign("istoday",$istoday);
			
			$this->display();
		}
		
		
		//确认支付提交
			public function getMeMonery(){
			 //判断是否实名认证
			$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
			if($ids!=1){
				echo jsonmsg('<font style="color:#E74A4A;font-weight:bold;font-size:16px;margin-bottom:30px;">您还未完成身份验证,请先进行实名认证！</font>',0);exit;
			} 
			//判断是否手机认证
			/*$phones = M('members_status')->getFieldByUid($this->uid,'phone_status');
			if($phones!=1){
				echo jsonmsg('<font style="color:#E74A4A;font-weight:bold;font-size:16px;margin-bottom:30px;">您还未手机认证,请先进行手机认证！</font>',1);exit;
			}*/
			$res = M("shares_global")->where("code='cps_1'")->getField("text");
			
			if($this->_post('memonery')<$res){
				echo jsonmsg('<font style="color:#E74A4A;font-weight:bold;font-size:16px;margin-bottom:30px;">数据有误!</font>',4);exit;
			}
			 $res = get_cps_trader('shares_global');
			 $data = array();
			 $data['principal']= $_POST['memonery'];		//用户的本金
			 $data['type_id'] = 3;		//类型id 3代表操盘手
			 $data['uid'] = $_SESSION['u_id'];	//申请人uid
			 $data['lever_ratio'] = $res[2];		//倍率
			 $data['order'] = 'cps_'.time().mt_rand(1000,100000);	//订单号
			 $data['shares_money'] = $data['principal']*$res[2];	//配资金额
			 $noticerate = $res[3]/100;
			 $closerate = $res[4]/100;
			 $data['open'] =  $closerate * $data['principal']+$data['shares_money'];	//平仓线 = 平仓线比率*本金+操盘资金
			 //var_dump($data['open']);die;
			 $data['alert']  = $noticerate * $data['principal']+$data['shares_money'];	//警戒线 = 平仓线比率*本金+操盘资金
			 $data['open_ratio'] = $res[4];		//平仓线比率
			 $data['alert_ratio'] = $res[3];		//警戒线比率
			 $data['add_time'] = time();
			 $data['ip_address'] = get_client_ip();	//获取客户端ip
			 $data['status'] = 1;	//待审核
			 $data['duration'] = $res[5];	//交易天数
			 $data['total_money'] =  $data['principal'] +$data["shares_money"];	//总操盘资金 = 用户本金+配资金额
			 $data['trading_time'] = $_POST['istoday'];	//是否今天交易
			 $data['u_name'] = $_SESSION['u_user_name'];
			 
			 //var_dump($data);die;
			 /**
				查询用户余额 如果用户余额足以支付则提交申请，不足以支付的时候返回配资失败
			 */
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
					//echo M('shares_apply')->getLastSql($res);die;
					if($res){
					 	echo '0';	//成功
					 	exit;
					 }else{
					 	echo '1';	//失败
					 	exit;	
					}				
				}else{
					echo '2';	//余额不足
					exit;
				}
			}else{
				echo '1';
				exit;
			}	
			 
		}
		
		//实名认证
		public function realname(){
		$_SESSION['u_selectid'] = $this -> _get('selectid');
		$id5_config = FS("Webconfig/id5");

		if($id5_config['enable']== 1){
			$id5_enable = "idcheck";
		}else {
		    $id5_enable = "saveid";
		}
		//提交审核
		//$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		
		//$this->assign("id_status",$ids);
		
		$this->assign("id5_enable",$id5_enable);
		
		
		
		
		$this -> display();
		}
		
		
		//实名认证执行
	/*public function idcheck() {
		
		// 开启错误提示
		ini_set('display_errors', 'on');
		error_reporting(E_ALL);
		$id5_config = FS("Webconfig/id5");
		
		if ($id5_config[enable] == 0) {
			//echo '实名验证授权没有开启！！！';die;
			
			$this -> saveid();
			//exit;
			
		}
        	
		$data['real_name'] = text($_POST['real_name']);
		$data['idcard'] = text($_POST['idcard']);
		var_dump();die;
		$data['up_time'] = time(); 
		
		
		// ///////////////////////
		$data1['idcard'] = text($_POST['idcard']);
		$data1['up_time'] = time();
		$data1['uid'] = $this -> uid;
		$data1['status'] = 0;
        $card = $data1['idcard'];
       
		$xuid = M('member_info')->getFieldByIdcard($data['idcard'],'uid');
		if($xuid>0 && $xuid!=$this->uid) echo "此身份证号码已被人使用";//ajaxmsg("此身份证号码已被人使用",0);
		// dump(11222);exit;
		$b = M('name_apply') -> where("uid = {$this->uid}") -> count('uid');
		if ($b == 1) {
			M('name_apply') -> where("uid ={$this->uid}") -> save($data1);
		} else {
			M('name_apply') -> add($data1);
		} 
		// //////////////////////
		//if($isimg!=1) ajaxmsg("请先上传身份证正面图片",0);
	    //if($isimg2!=1) ajaxmsg("请先上传身份证反面图片",0);
		if (empty($data['real_name']) || empty($data['idcard'])) echo "请填写真实姓名和身份证号码";//ajaxmsg("请填写真实姓名和身份证号码", 0);

		$c = M('member_info') -> where("uid = {$this->uid}") -> count('uid');
		if ($c == 1) {
			$newid = M('member_info') -> where("uid = {$this->uid}") -> save($data);
		} else {
			$data['uid'] = $this -> uid;
			$newid = M('member_info') -> add($data);
		} 

		function get_data($d) {
			preg_match_all('/<ROWS>(.*)<\/ROWS>/isU', $d, $arr);
			$data = array();
			$aa = array();
			$cc = array();
			foreach($arr[1] as $k => $v) {
				preg_match_all('#<result_gmsfhm>(.*)</result_gmsfhm>#isU', $v, $ar[$k]);
				preg_match_all('#<gmsfhm>(.*)</gmsfhm>#isU', $v, $sfhm[$k]);
				preg_match_all('#<result_xm>(.*)</result_xm>#isU', $v, $br[$k]);
				preg_match_all('#<xm>(.*)</xm>#isU', $v, $xm[$k]);
				preg_match_all('#<xp>(.*)</xp>#isU', $v, $cr[$k]);

				$data[] = $ar[$k][1];
				$aa[] = $br[$k][1];
				$cc[] = $cr[$k][1];
				$sfhm[] = $sfhm[1];
				$xm[] = $xm[1];
			} 
			$resa['data'] = $data[0][0];
			$resa['aa'] = $aa[0][0];
			$resa['cc'] = $cc[0][0];
			$resa['xm'] = $xm[0][0][0];
			$resa['sfhm'] = $sfhm[0][0][0];
			return $resa;
		} 
		$res = '';
		try {
			$client = new SoapClient(C("APP_ROOT") . "common/wsdl/NciicServices.wsdl");
			$licenseCode = $id5_config['auth']; //file_get_contents(C("APP_ROOT")."common/wsdl/license.txt");
			$condition = '<?xml version="1.0" encoding="UTF-8" ?>
        <ROWS>
            <INFO>
            <SBM>' . time() . '</SBM>
            </INFO>
            <ROW>
                <GMSFHM>公民身份号码</GMSFHM>
                <XM>姓名</XM>
            </ROW>
            <ROW FSD="100022" YWLX="身份证认证测试-错误" >
            <GMSFHM>' . trim($_REQUEST['idcard']) . '</GMSFHM>
            <XM>' . trim($_REQUEST['real_name']) . '</XM>
            </ROW>
            
        </ROWS>'; //330381198609262623 薛佩佩
			$params = array('inLicense' => $licenseCode,
				'inConditions' => $condition,
				);
			$res = $client -> nciicCheck($params);
		} 
		catch(Exception $e) {
			echo $e -> getMessage();
			exit;
		} 
		// ajaxmsg("aaaaaaaaaa",1);
		// echo $res->out;
		
		$shuju = get_data($res -> out); 
		// ajaxmsg("实名认证成功",1); 
		
		if (@$shuju['data'] == '一致' && @$shuju['aa'] == '一致') {
			$time = time();
			

			$temp = M('members_status') -> where("uid={$this->uid}") -> find();
			
			if(is_array($temp)){
				$cid['id_status'] = 1;
			    $status = M('members_status') -> where("uid={$this->uid}") -> save($cid);
			}else{
			    $dt['uid'] = $this -> uid;
				$dt['id_status'] = 1;
				$status = M('members_status') -> add($dt);
			}
			if($status){
				
			    $data2['status'] = 1;
				$data2['deal_info'] = '会员中心实名认证成功';
				$new = M("name_apply") -> where("uid={$this -> uid}") -> save($data2);
				if($new) echo "认证失败";//ajaxmsg();
			}else{
				
			    $data2['status'] = 0;
				$data2['deal_info'] = '会员中心实名认证失败';
				$new = M("name_apply") -> where("uid={$this -> uid}") -> save($data2);
				//ajaxmsg("认证失败",0);
				echo "认证失败";
			}


			 
			// unlink($file);
		}else{   
		    //ajaxmsg("实名认证失败",0);
			echo "实名认证失败";
		    $mm = M('members_status') -> where("uid={$this->uid}") -> setField('id_status', 3);
		    if ($mm == 1) {
			    //ajaxmsg('待审核', 0);
		    } else {
			    $dt['uid'] = $this -> uid;
			    $dt['id_status'] = 3;
			    M('members_status') -> add($dt);
			    ajaxmsg('等待审核', 0);
		    }
		}
		// }
		// $this->assign("shuju",$shuju);
		// $this->assign("ps",$ps);
		$data['html'] = $this -> fetch();
		exit(json_encode($data));
	}
	*/
	
	
	public function saveid(){
      
		$isimg = session('idcardimg');
		
		$isimg2 = session('idcardimg2');
		$data['real_name'] = text($_POST['real_name']);
		$data['idcard'] = text($_POST['idcard']);
		$data['up_time'] = time(); 
		//var_dump($data);die;
		
		// ///////////////////////
		$data1['idcard'] = text($_POST['idcard']);
		$data1['real_name'] = text($_POST['real_name']);
		$data1['up_time'] = time();
		$data1['uid'] = $this -> uid;
		$data1['status'] = 0;
		//var_dump($data1);die;

//		if (M('name_apply') -> field('idcard') -> where("idcard ={$data1['idcard']} and status=1") -> find()) {
//			ajaxmsg("此身份证号码已被占用", 0);
//			exit;
//		} 
        $xuid = M('member_info')->getFieldByIdcard($data['idcard'],'uid');
		if($xuid>0 && $xuid!=$this->uid) echo "此身份证号码已被人使用";
		$b = M('name_apply') -> where("uid = {$this->uid}") -> count('uid');
		//var_dump($b);die;
		if ($b == 1) {
			M('name_apply') -> where("uid ={$this->uid}") -> save($data1);
		} else {
			M('name_apply') -> add($data1);
		} 
		// //////////////////////
		if($isimg!="") echo "请先上传身份证正面图片";//ajaxmsg("请先上传身份证正面图片",0);
		if($isimg2!="") echo "请先上传身份证反面图片";//ajaxmsg("请先上传身份证反面图片",0);
		if (empty($data['real_name']) || empty($data['idcard'])) echo "请填写真实姓名和身份证号码";//ajaxmsg("请填写真实姓名和身份证号码", 0);

		$c = M('member_info') -> where("uid = {$this->uid}") -> count('uid');
		 //echo $c;die;
		if ($c == 1) {
			$newid = M('member_info') -> where("uid = {$this->uid}") -> save($data);
		} else {
			$data['uid'] = $this -> uid;
			$newid = M('member_info') -> add($data);
		} 
		session('idcardimg',NULL);
		session('idcardimg2',NULL);
		if ($newid) {
			//echo "success";die;
			$ms = M('members_status') -> where("uid={$this->uid}") -> setField('id_status', 3);
			//echo $ms;die;
			if ($ms == 1) {
				ajaxmsg();
			} else {
				$dt['uid'] = $this -> uid;
				$dt['id_status'] = 3;
				M('members_status') -> add($dt);
				//echo M('members_status')->getLastSql();die;
			} 
			
			 ajaxmsg("成功");
			
			
		} else echo "error";die;//ajaxmsg("保存失败，请重试", 0);
    }
	
	
	public function ajaxupimg(){
		$this->savePathNew = C('MEMBER_UPLOAD_DIR').'Idcard/' ;
		if(!empty($_FILES['imgfile']['name'])){
			import("ORG.Net.UploadFile");
        $upload = new UploadFile();
		
		$upload->thumb = true;
		$upload->saveRule =date("YmdHis",time()).rand(0,1000)."_{$this->uid}";//图片命名规则
		$upload->thumbMaxWidth = $this->thumbMaxWidth;
		$upload->thumbMaxHeight = $this->thumbMaxHeight;
		$upload->maxSize  = C('MEMBER_MAX_UPLOAD') ;// 设置附件上传大小
		$upload->allowExts  = C('MEMBER_ALLOW_EXTS');// 设置附件上传类型
		$upload->savePath =  $this->savePathNew?$this->savePathNew:C('MEMBER_MAX_UPLOAD');// 设置附件上传目录
		if(!$upload->upload()) {// 上传错误提示错误信息
			//$this->error($upload->getErrorMsg());
		}else{// 上传成功 获取上传文件信息
			$info =  $upload->getUploadFileInfo();
		}
			$img = $info[0]['savepath'].$info[0]['savename'];
		}
		if($img){
			$c = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if($c==1){
				$newid = M("member_info")->where("uid={$this->uid}")->setField('card_img',$img);
			}else{
				$data['uid'] = $this->uid;
				$data['card_img'] = $img;
				$newid = M('member_info')->add($data);
			}
			session("idcardimg","1");
			ajaxmsg('',1);
		}
		else  ajaxmsg('',0);
		
		
		
	}

	public function ajaxupimg2(){
		/*if(!empty($_FILES['imgfile2']['name'])){
			$this->fix = false;
			$this->saveRule = date("YmdHis",time()).rand(0,1000)."_{$this->uid}_back";
			$this->savePathNew = C('MEMBER_UPLOAD_DIR').'Idcard/' ;
			//$this->savePathNew ="http://qfw.taoweikeji.com/UF/Uploads/Idcard/" ;
			$this->thumbMaxWidth = "1000,1000";
			$this->thumbMaxHeight = "1000,1000";
			$info = $this->CUpload();
			$img = $info[0]['savepath'].$info[0]['savename'];
		}
		if($img){
			$c = M('member_info')->where("uid = {$this->uid}")->count('uid');
			
			if($c==1){
				$newid = M("member_info")->where("uid={$this->uid}")->setField('card_back_img',$img);
			}else{
				$data['uid'] = $this->uid;
				$data['card_back_img'] = $img;
				$newid = M('member_info')->add($data);
			}
			session("idcardimg2","1");
			ajaxmsg('',1);
		}
		else  ajaxmsg('',0);
		*/
		$this->savePathNew = C('MEMBER_UPLOAD_DIR').'Idcard/' ;
		if(!empty($_FILES['imgfile2']['name'])){
			
			import("ORG.Net.UploadFile");
        $upload = new UploadFile();
		
		$upload->thumb = true;
		$upload->saveRule =date("YmdHis",time()).rand(0,1000)."_{$this->uid}_back";//图片命名规则
		$upload->thumbMaxWidth = $this->thumbMaxWidth;
		$upload->thumbMaxHeight = $this->thumbMaxHeight;
		$upload->maxSize  = C('MEMBER_MAX_UPLOAD') ;// 设置附件上传大小
		$upload->allowExts  = C('MEMBER_ALLOW_EXTS');// 设置附件上传类型
		$upload->savePath =  $this->savePathNew?$this->savePathNew:C('MEMBER_MAX_UPLOAD');// 设置附件上传目录
		if(!$upload->upload()) {// 上传错误提示错误信息
			//$this->error($upload->getErrorMsg());
		}else{// 上传成功 获取上传文件信息
			$info =  $upload->getUploadFileInfo();
		}
			$img = $info[0]['savepath'].$info[0]['savename'];
		}
		if($img){
			$c = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if($c==1){
				$newid = M("member_info")->where("uid={$this->uid}")->setField('card_back_img',$img);
			}else{
				$data['uid'] = $this->uid;
				$data['card_back_img'] = $img;
				$newid = M('member_info')->add($data);
			}
			session("idcardimg2","1");
			ajaxmsg('',1);
		}
		else  ajaxmsg('',0);
		
		
	}
	
	public function about(){
		
		$this->display();
	}
	public function tuwen(){
		
		$this->display();
	}
	
	public function team(){
		$this->display();
	}
	

   
	
	
	
		
		



}













