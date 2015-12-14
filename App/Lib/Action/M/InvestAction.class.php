<?php
    class InvestAction extends HCommonAction
    {
		
		
    public function index()
        {
			$curl = $_SERVER['REQUEST_URI'];
			$urlarr = parse_url($curl);
			
			parse_str($urlarr['query'],$surl);//array获取当前链接参数，2.
		    $urlArr = array('borrow_status','borrow_duration','stock_type');
			$searchMap = array();
		//搜索条件
		foreach($urlArr as $v){
			if($_GET[$v] && $_GET[$v]<>'all'){
				switch($v){
					case 'stock_type':
						//$barr = explode("-",text($_GET[$v]));
						$searchMap["b.stock_type"] = intval($_GET[$v]);
					break;
					case 'borrow_status':
						$searchMap["b.".$v] = intval($_GET[$v]);
					break;
					default:
						$barr = explode("-",text($_GET[$v]));
						$searchMap["b.".$v] = array("between",$barr);
					break;
				}
			}
		}
		if($searchMap['b.stock_type']==0){
			$searchMap['b.stock_type']=array("in","1,2,3");
		}
		if($_GET['flag'] == 'day')
			{
				
				$searchMap['b.stock_type'] = 1;
			}else if($_GET['flag'] == 'month')
			{
				$searchMap['b.stock_type'] = 2;
			}
			
		
			
			
			
			
			
			
		   
		  $maprow = array();
            if(!empty($_GET['rate'])){
                $searchMap['borrow_interest_rate'] = array("lt",$_GET['rate']);
            }
            $searchMap['borrow_status']=array("in",'2,4,6,7,3'); 
            $parm['map'] = $searchMap;
            $parm['pagesize'] = 2;
            $sort = "desc";
            $parm['orderby']="b.borrow_status ASC,b.id DESC";
            $list = getBorrowList($parm);
			
			//dump($list);die;
            $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php"; 
			if($this->isAjax()){
				
                $str ='';
                foreach($list['list'] as $vb){
					
				 $str.="<div class='box'>";
                    //$str.="<p class='tit'><a href='/m/invest/detail/id/$vb[id]'>$vb[borrow_name]</a></p>";
					$str.="<p class='tit'>";
					if($vb['stock_type']==2){
					  $str.="<img src='/Style/H/qimages/yy.jpg'  />";
					}else{
					  $str.="<img src='/Style/H/qimages/tt.jpg'>";
					}
					$str.="<a href='/m/invest/detail/id/$vb[id]'>$vb[borrow_name]</a></p>";

					
                    $str.="<table cellpadding='0' cellspacing='0' border='0' class='table'>"; 
					
                    $str.="<tr>";
                    $str.="<td>年化收益：</td>";
					/*
                    $str.="<td>$vb[borrow_interest_rate]%/".$vb[repayment_type]==1?'天':'年'."</td>";
					
                    $str.="<td>借款期限：</td>";
                    $str.="<td>".$vb[borrow_duration]." ".$vb['repayment_type']==1?'天':'个月'."</td>";
                    
					*/
					
					if($vb['repayment_type']==1){
                    	$day = '天';
                    }else{
                    	$day = '年';
                    }
                    $str.="<td>$vb[borrow_interest_rate]%/".$day."</td>";
                    $str.="<td>借款期限：</td>";
                    if($vb[repayment_type]==1){
                    	$days = '天';
                    }else{
                    	$days = '个月';
                    }
                    $str.="<td>".$vb[borrow_duration].$days."</td>";
                    $str.="</tr><tr>";
					$str.="<td>完成进度：</td>";
                    $str.="<td colspan='3'>";
                   
					
					$str.="<span class='jdt'>";
                    $str.="<span class='jd' style='width:".intval($vb[progress])."%'></span>";
                    $str.="<strong class='strong'>
					         ".$vb[progress]."%
					      </strong>
					</span>
					</td></tr></table>";
					
                    $str.="<p class='sub'>";
                    $str.="借款金额：<strong class='strong'>￥".$vb[borrow_money]."</strong>元";
                    $str.="<a class='btn-a fr' href='/m/invest/detail/id/$vb[id]'>立即投标</a></p></div>"; 		
	  
					
                }
				
			echo $str;
            }else{
				
                $this->assign('list', $list);
                $this->assign('Bconfig', $Bconfig);
                $this->display(); 
            }
			
			
   }
        public function detail()
        {   
		 
       //if(!$this->uid) $this->qingxian();
	   //dump($this->uid);die;//(2)
		$pre = C('DB_PREFIX');
		$id = intval($_GET['id']);
		
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		
		
		//合同ID
		//borrowinfo
		//$borrowinfo = M("borrow_info")->field(true)->find($id);
		$borrowinfo = M("borrow_info bi")->field('bi.*,ac.title,ac.id as aid')->join('lzh_article ac on ac.id= bi.danbao')->where('bi.id='.$id)->find();
		//dump($borrowinfo);die;
		if(!is_array($borrowinfo) || ($borrowinfo['borrow_status']==0 && $this->uid!=$borrowinfo['borrow_uid']) ) $this->error("数据有误");
		$borrowinfo['biao'] = $borrowinfo['borrow_times'];
		$borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
		$borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();
		$borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow']/$borrowinfo['borrow_money']*100,2);
		//var_dump($borrowinfo);die;
		//var_dump($borrowinfo['id']);die;
		
		$this->assign("vo",$borrowinfo);
       $this->assign("borrow_id",$id);
		
		
		  $this->display();
		}
		public function success(){
			//echo $_GET['num'];die;
			if(!$this->uid) $this->error("请先登录");//ajaxmsg("请先登录", 0);
		$id = intval($_GET['id']);
		if($id < 1) $this->error('借款标号不正确');//ajaxmsg('借款标号不正确', 0);
		
		$field = "id,borrow_uid,borrow_money,borrow_status,borrow_type,has_borrow,has_vouch,borrow_interest_rate,borrow_duration,repayment_type,collect_time,borrow_min,borrow_max,password,borrow_use,money_collect";
		$vo = M('borrow_info')->field($field)->find($id);
		//var_dump($vo);die;
		
		$borrowinfo = M("borrow_info bi")->field('bi.*,ac.title,ac.id as aid')->join('lzh_article ac on ac.id= bi.danbao')->where('bi.id='.$id)->find();
		if(!is_array($borrowinfo) || ($borrowinfo['borrow_status']==0 && $this->uid!=$borrowinfo['borrow_uid']) ) $this->error("数据有误");
		$borrowinfo['biao'] = $borrowinfo['borrow_times'];
		$borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
		$borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();
		$borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow']/$borrowinfo['borrow_money']*100,2);
		
		
		$this->assign("voa",$borrowinfo);
		
		if(empty($vo)) $this->error('没有此标');//ajaxmsg('没有此标', 0); // 防止用户修改界面抢投
		if($this->uid == $vo['borrow_uid']) $this->error("不能去投自己的标");//ajaxmsg("不能去投自己的标",0);
		//(暂时注释)if($vo['borrow_status'] != 2) $this->error("只能投正在借款中的标");//ajaxmsg("只能投正在借款中的标",0);
		
		$binfo = M("borrow_info")->field('borrow_money,has_borrow,has_vouch,borrow_max,borrow_min,borrow_type,password,money_collect')->find($id);
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($vm['money_collect']<$binfo['money_collect']) {
				//ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",3);
				$this->error("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标");
			}
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		
		$this->assign("has_pin", (empty($vm['pin_pass']))?'no':'yes');
		$this->assign("investMoney", intval($_GET['num']));
		$this->assign("vo",$vo);
		
		$this->display();
		}
		
		public function detailpay()
        {   
		
		$pre = C('DB_PREFIX');
		$id = intval($_GET['id']);
	    $this->assign("id",$id);
	    $this->display();
		}
		
		
		
		//////////1
		public function investcheck(){
		//echo "111111111";die;
		$pre = C('DB_PREFIX');
		if(!$this->uid) {
			ajaxmsg('',3);
			exit;
		}
		$pin = md5($_POST['pin']);
		//var_dump($pin);die;
		$borrow_id = intval($_POST['borrow_id']);
		$money = intval($_POST['money']);
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		//var_dump($vm);die;
		$amoney = $vm['account_money']+$vm['back_money'];
		//var_dump($amoney);die;
		$uname = session('u_user_name');
		$pin_pass = $vm['pin_pass'];
		$amoney = floatval($amoney);
		
		$binfo = M("borrow_info")->field('borrow_money,money_invest_place,has_borrow,has_vouch,borrow_max,borrow_min,borrow_type,password,money_collect')->find($borrow_id);
		//var_dump($binfo);die;
		/*if(!empty($binfo['password'])){
			//if(empty($_POST['borrow_pass'])) ajaxmsg("此标是定向标，必须验证投标密码",3);
			else if($binfo['password']<>md5($_POST['borrow_pass'])) ajaxmsg("投标密码不正确",3);
		}
		*/
		if($money%$binfo['borrow_min'] !=0){
			//ajaxmsg("投标金额必须为起投金额的整数倍",3);
			$this->error("投标金额必须为起投金额的整数倍");
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($vm['money_collect']<$binfo['money_collect']) {
				//ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",3);
				$this->error("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标");
			}
		}
		


		$today_start = strtotime(date('Y-m-d', time())."00:00:00");
		//$today_end = strtotime(date('Y-m-d', time())."23:59:59");
		if($binfo['borrow_type'] == 3){
			if($binfo['money_invest_place'] > 0){
				$M_affect_money = M('member_moneylog')->where('uid = '.$this->uid." AND type in (6,37) AND add_time > ".$today_start." AND add_time < ".time())->sum('affect_money'); 
					$money_place =$binfo['money_invest_place'] + $M_affect_money;
				if( $money_place>0 ){
					//ajaxmsg("此标设置有当日投标金额限制，您还需投资".$money_place."元才能投此秒标",3);
					$this->error("此标设置有当日投标金额限制，您还需投资".$money_place."元才能投此秒标");
				}
			}
		}	
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		//投标总数检测
		$capital = M('borrow_investor')->where("borrow_id={$borrow_id} AND investor_uid={$this->uid}")->sum('investor_capital');
		if(($capital+$money)>$binfo['borrow_max']&&$binfo['borrow_max']>0){
			$xtee = $binfo['borrow_max'] - $capital;
			//ajaxmsg("您已投标{$capital}元，此投上限为{$binfo['borrow_max']}元，你最多只能再投{$xtee}",3);
			$this->error("您已投标{$capital}元，此投上限为{$binfo['borrow_max']}元，你最多只能再投{$xtee}");
		}
		
		$need = $binfo['borrow_money'] - $binfo['has_borrow'];
		$caninvest = $need - $binfo['borrow_min'];
		if( $money>$caninvest && ($need-$money)<>0 ){
			$msg = "尊敬的{$uname}，此标还差{$need}元满标,如果您投标{$money}元，将导致最后一次投标最多只能投".($need-$money)."元，小于最小投标金额{$binfo['borrow_min']}元，所以您本次可以选择<font color='#FF0000'>满标</font>或者投标金额必须<font color='#FF0000'>小于等于{$caninvest}元</font>";
			if($caninvest<$binfo['borrow_min']) $msg = "尊敬的{$uname}，此标还差{$need}元满标,如果您投标{$money}元，将导致最后一次投标最多只能投".($need-$money)."元，小于最小投标金额{$binfo['borrow_min']}元，所以您本次可以选择<font color='#FF0000'>满标</font>即投标金额必须<font color='#FF0000'>等于{$need}元</font>";

			ajaxmsg($msg,3);
		}
		if(($binfo['borrow_min']-$money)>0 ){
			$this->error("尊敬的{$uname}，本标最低投标金额为{$binfo['borrow_min']}元，请重新输入投标金额");
		}
		if(($need-$money)<0 ){
			$this->error("尊敬的{$uname}，此标还差{$need}元满标,您最多只能再投{$need}元");
		}
		if($pin<>$pin_pass) ajaxmsg("支付密码错误，请重试!",0);
		if($money>$amoney){
			$msg = "尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，您要先去充值吗？";
			ajaxmsg($msg,2);
		}else{
			$msg = "尊敬的{$uname}，您的账户可用余额为{$amoney}元，您确认投标{$money}元吗？";
			ajaxmsg($msg,1);
		}
		ajaxmsg($msg,1);
	}
	
	//////2
	public function investmoney(){
	   //print_r($_POST);die;
		//if(!$this->uid) exit;
		if(!$this->uid) {
			ajaxmsg('请先登录',3);
			exit;
		}
		$money = intval($_POST['money']);
		//var_dump($money);die;
		$borrow_id = intval($_POST['borrow_id']);
		//print_r($money);
		//print_r($borrow_id);die;
		
		$m = M("member_money")->field('account_money,back_money,money_collect')->find($this->uid);
		//print_r($m);die;
		$amoney = $m['account_money']+$m['back_money'];
		$uname = session('u_user_name');
		if($amoney<$money){$this->error("尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再投标.","http://qfw.taoweikeji.com/M/Center/cz_online");
			/*if($this->error("尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再投标")){
				$this->redirect("http://qfw.taoweikeji.com/M/Center/cz_online");
			}
			*/
			
			
			
			
		}
			
			
		//echo "尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再投标";$this->redirect("http://qfw.taoweikeji.com/M/Center/cz_online");
			
		//$this->error("尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再投标.");$this->redirect("http://qfw.taoweikeji.com/M/Center/cz_online");
			
		//$this->error("尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再投标.","http://qfw.taoweikeji.com/M/Center/cz_online");
		//$msg = "尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，您要先去充值吗？";
		//ajaxmsg($msg,2);
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		$pin_pass = $vm['pin_pass'];
		$pin = md5($_POST['pin']); 
		
		if($pin<>$pin_pass) $this->error("支付密码错误，请重试");

		$binfo = M("borrow_info")->field('borrow_money,money_invest_place,borrow_max,has_borrow,has_vouch,borrow_type,borrow_min,money_collect')->find($borrow_id);
		//dump($binfo);die;
		//var_dump($binfo);{ ["borrow_money"]=> string(7) "5000.00" ["money_invest_place"]=> string(1) "0" ["borrow_max"]=> string(1) "0" ["has_borrow"]=> string(4) "0.00" ["has_vouch"]=> string(4) "0.00" ["borrow_type"]=> string(1) "1" ["borrow_min"]=> string(2) "50" ["money_collect"]=> string(4) "0.00" } 
		
		if($money%$binfo['borrow_min'] !=0){
			echo 0;
			//ajaxmsg("投标金额必须为起投金额的整数倍",3);
			$this->error("投标金额必须为起投金额的整数倍");
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($m['money_collect']<$binfo['money_collect']) {
				echo 1;
				//ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",3);
				$this->error("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标");
			}
		}


		$today_start = strtotime(date('Y-m-d', time())."00:00:00");
		//$today_end = strtotime(date('Y-m-d', time())."23:59:59");
		//echo $binfo['borrow_type'];die;
		if($binfo['borrow_type'] == 3){
			if($binfo['money_invest_place'] > 0){
				$M_affect_money = M('member_moneylog')->where('uid = '.$this->uid." AND type in (6,37) AND add_time > ".$today_start." AND add_time < ".time())->sum('affect_money'); 
					$money_place =$binfo['money_invest_place'] + $M_affect_money;
				if( $money_place>0 ){
					echo 2;
					//ajaxmsg("此标设置有当日投标金额限制，您还需投资".$money_place."元才能投此秒标",3);
					$this->error("此标设置有当日投标金额限制，您还需投资".$money_place."元才能投此秒标");
				}
			}
		}	
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		
		//投标总数检测
		$capital = M('borrow_investor')->where("borrow_id={$borrow_id} AND investor_uid={$this->uid}")->sum('investor_capital');
		if(($capital+$money)>$binfo['borrow_max']&&$binfo['borrow_max']>0){
			$xtee = $binfo['borrow_max'] - $capital;
			$this->error("您已投标{$capital}元，此投上限为{$binfo['borrow_max']}元，你最多只能再投{$xtee}");
		}
		//if($binfo['has_vouch']<$binfo['borrow_money'] && $binfo['borrow_type'] == 2) $this->error("此标担保还未完成，您可以担保此标或者等担保完成再投标");
		$need = $binfo['borrow_money'] - $binfo['has_borrow'];
		//echo $need;(5000)
		$caninvest = $need - $binfo['borrow_min'];
		//echo $caninvest;(4950)
		//dump($money);
		//dump($caninvest);
		//dump($need);die;
		if( $money>$caninvest && $need==0){
			echo 3;
			$msg = "尊敬的{$uname}，此标已被抢投满了,下次投标手可一定要快呦！";
			$this->error($msg);
		}
		if(($binfo['borrow_min']-$money)>0 ){
			echo 4;
			$this->error("尊敬的{$uname}，本标最低投标金额为{$binfo['borrow_min']}元，请重新输入投标金额");
		}
		if(($need-$money)<0 ){
			echo 5;
			$this->error("尊敬的{$uname}，此标还差{$need}元满标,您最多只能再投{$need}元");
		}else{
			//var_dump($this->uid);//(23)
			//var_dump($borrow_id);//(12)
			//var_dump($money);die;//(150)
			
			$done = investMoney($this->uid,$borrow_id,$money);
			//echo $done;die();
			//var_dump($done);die;
			//echo $done;die;
		}
		
	
		if($done===true) {
			
			//$this->success("恭喜成功投标{$money}元");
			
			$this->redirect("http://qfw.taoweikeji.com/M/Invest/tb_success/money/{$money}");


			
			/*art.dialog({
					lock: true,
					background: '#ccc', // 背景色
					opacity: 0.87,	// 透明度
					content: '恭喜成功投标{$money}元',
					icon: 'succeed',
					time:3,
				});
				*/
				
				
		}else if($done){
			//echo 7;die();
			$this->error($done);
		}else{
			//echo 8;
			$this->error("对不起，投标失败，请重试!");
		}
	}

	
	
		//投标成功
		public function tb_success(){
			
			$money=$_GET['money'];
			$this->assign("money",$money);
			$this->display("tb_success");
		}
        
        /**
        * 立即投资
        */
		public function liji_invest(){
		
		if(!$this->uid) $this->qingxian();
		$id=$_GET['id'];
		$this->assign("id",$id);
		$this->display('ajax_invest');
		 
	
	}
	public function qingxian(){
		$this->display("qingxian");
	}
	
	public function liji_investzhi(){
		if(!$this->uid) $this->error('请先登录');
		$data['id'] = intval($_GET['id']);
		$data['num']=intval($_GET['num']);
		
		$data['content'] = $this->fetch();
		//$data['content'] = $this->display('ajax_invest');
		
		ajaxmsg($data);
		
		
	}
	/*
	 借款人信息
	*/
	public function invest_people_info(){
		
		if(!$this->uid) $this->qingxian();
		$pre = C('DB_PREFIX');
		$id = intval($_GET['id']);
		
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		
		$borrowinfo = M("borrow_info bi")->field('bi.*,ac.title,ac.id as aid')->join('lzh_article ac on ac.id= bi.danbao')->where('bi.id='.$id)->find();
		
		if(!is_array($borrowinfo) || ($borrowinfo['borrow_status']==0 && $this->uid!=$borrowinfo['borrow_uid']) ) $this->error("数据有误");
		$borrowinfo['biao'] = $borrowinfo['borrow_times'];
		$borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
		$borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();
		$borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow']/$borrowinfo['borrow_money']*100,2);
		
		
		$this->assign("vo",$borrowinfo);

		$memberinfo = M("members m")->field("m.id,m.user_phone,m.customer_name,m.customer_id,m.user_name,m.reg_time,m.credits,fi.*,mi.*,mm.*")->join("{$pre}member_financial_info fi ON fi.uid = m.id")->join("{$pre}member_info mi ON mi.uid = m.id")->join("{$pre}member_money mm ON mm.uid = m.id")->where("m.id={$borrowinfo['borrow_uid']}")->find();
		$areaList = getArea();
		if(!$memberinfo) {
			$memberinfo['credits'] = 0;
		}
		$memberinfo['location'] = $areaList[$memberinfo['province']].$areaList[$memberinfo['city']];
		$memberinfo['location_now'] = $areaList[$memberinfo['province_now']].$areaList[$memberinfo['city_now']];
		$memberinfo['zcze']=$memberinfo['account_money']+$memberinfo['back_money']+$memberinfo['money_collect']+$memberinfo['money_freeze'];
		//var_dump($memberinfo);die;
		$reg_time=date('Y-m-d H:i:s',$memberinfo['reg_time']);
		$this->assign("reg_time",$reg_time);
		$this->assign("minfo",$memberinfo);
		//$this->assign("capitalinfo", getMemberBorrowScan($borrowinfo['borrow_uid']));
		
		$this->assign("borrow_id",$id);
		$this->assign("capitalinfo", getMemberBorrowScan($borrowinfo['borrow_uid']));
      
	   
		
		
		$this->display();
		
	}
	//投资记录
	public function investRecord(){
		
		
		
		
		if(!$this->uid) $this->qingxian();
		$pre = C('DB_PREFIX');
		
		$borrow_id=$_GET['borrow_id'];
		
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		
		$borrowinfo = M("borrow_info bi")->field('bi.*,ac.title,ac.id as aid')->join('lzh_article ac on ac.id= bi.danbao')->where('bi.id='.$borrow_id)->find();
		
		if(!is_array($borrowinfo) || ($borrowinfo['borrow_status']==0 && $this->uid!=$borrowinfo['borrow_uid']) ) $this->error("数据有误");
		$borrowinfo['biao'] = $borrowinfo['borrow_times'];
		$borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
		$borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();
		$borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow']/$borrowinfo['borrow_money']*100,2);
		
		
		$this->assign("vo",$borrowinfo);
		
		$list = M("borrow_investor as b")
                        ->join(C(DB_PREFIX)."members as m on  b.investor_uid = m.id")
                        ->join(C(DB_PREFIX)."borrow_info as i on  b.borrow_id = i.id")
                        ->field('i.borrow_interest_rate, i.repayment_type, b.investor_capital, b.add_time, b.is_auto, m.user_name')->where('b.borrow_id='.$borrow_id)->order('b.id')->select();
		
			
			
		 
	     $this->assign("list",$list);
		// $this->assign("vo",$borrowinfo);
		 $this->assign("borrow_id",$borrow_id);
		
		 $this->display("investRecord");
	}
	
	
        public function Invest()
        {   
            if(!$this->uid){
                if($this->isAjax()){
                    die("请先登录后投资");   
                }else{
                    $this->redirect('M/pub/login');       
                }
            }
			$loanconfig = FS("Webconfig/loanconfig");
			
            if($this->isAjax()){   // ajax提交投资信息
				
				$borrow_id = intval($this->_get('bid'));

                //$borrow_id ='22';
                $invest_money = intval($this->_post('invest_money'));
				//die($borrow_id);
                $paypass = $this->_post('paypass');
				$invest_pass = isset($_POST['invest_pass'])?$_POST['invest_pass']:'';
                $binfo = M("borrow_info")->field('borrow_money,borrow_max,has_borrow,has_vouch,borrow_type,borrow_min,money_collect, borrow_uid')->find($borrow_id);
                $status = checkInvest($this->uid, $borrow_id, $invest_money, $paypass, $invest_pass);
                if($status == 'TRUE'){
                    $invest_id = investMoney($this->uid,$borrow_id,$invest_money);
                    if($invest_id == true){
					    $orders = date("YmdHi").$invest_id;
						$invest_qdd = M("escrow_account")->field('*')->where("uid={$this->uid}")->find();
						$borrow_qdd = M("escrow_account")->field('*')->where("uid={$binfo['borrow_uid']}")->find();
						$invest_info = M("borrow_investor")->field("reward_money, borrow_fee")->where("id={$invest_id}")->find();
						$secodary = '';
						import("ORG.Loan.Escrow");
						$loan = new Escrow();
						if($invest_info['reward_money']>0.00){  // 投标奖励
							$secodary[] = $loan->secondaryJsonList($invest_qdd['qdd_marked'], $invest_info['reward_money'],'二次分配', '支付投标奖励'); 
						}
						if($invest_info['borrow_fee']>0.00){  // 借款管理费
							$secodary[] = $loan->secondaryJsonList($loanconfig['pfmmm'], $invest_info['borrow_fee'],'二次分配', '支付平台借款管理费'); 
						}
						$secodary && $secodary = json_encode($secodary);
						// 投标奖励
						$loanList[] = $loan->loanJsonList($invest_qdd['qdd_marked'], $borrow_qdd['qdd_marked'], $orders, $borrow_id, $invest_money, $binfo['borrow_money'],'投标',"对{$borrow_id}号投标",$secodary);
						$loanJsonList = json_encode($loanList);
						//$returnURL = C('WEB_URL').U("invest/investReturn");
						$returnURL = 'http://'.$_SERVER ['HTTP_HOST'].U("/invest/wapinvestReturn");
						
						$notifyURL = 'http://'.$_SERVER ['HTTP_HOST'].U("invest/notify");
						//echo $returnURL."    notifyURL:".$notifyURL;die();
						//var_dump($loanJsonList);die();
						$data =  $loan->transfer($loanJsonList, $returnURL , $notifyURL);
						//var_dump($data);die();
						$form =  $loan->setForm($data, 'transfer');
						echo $form."正在跳转至乾多多。。。";
						//die('TRUE');
						exit; 
						//die('TURE');
                    }
                    elseif($invest_id){
                        die($invest_id);
                    }else{
                        die(L('investment_failure'));
                    }
                  

                    

                }else{
                    die($status);   
                }
            }else{  
                $borrow_id = $this->_get('bid');
                $borrow_info = M("borrow_info")
                    ->field('borrow_duration, borrow_money, borrow_interest, borrow_interest_rate, has_borrow,
                             borrow_min, borrow_max, password, repayment_type')
                    ->where("id='{$borrow_id}'")
                    ->find();
                $this->assign('borrow_info', $borrow_info);  
				$this->assign('borrow_pass',$borrow_info.password);
                
                $user_info = M('member_money')
                                ->field("account_money+back_money as money ")
                                ->where("uid='{$this->uid}'")
                                ->find();
                $this->assign('user_info', $user_info);
                $paypass = M("members")->field('pin_pass')->where('id='.$this->uid)->find();
                $this->assign('paypass', $paypass['pin_pass']);
                $this->display();   
            }
        }
             /**
        * ajax 获取投资记录
        * 
        */
       /* public function investRecord($borrow_id=0)
        {
            
            isset($_GET['borrow_id']) && $borrow_id = intval($_GET['borrow_id']);
            $Page = D('Page');       
            import("ORG.Util.Page");       
            $count = M("borrow_investor")->where('borrow_id='.$borrow_id)->count('id');
            $Page     = new Page($count,10);
            
            
            $show = $Page->ajax_show();
            $this->assign('page', $show);
            if($_GET['borrow_id']){
                $list = M("borrow_investor as b")
                            ->join(C(DB_PREFIX)."members as m on  b.investor_uid = m.id")
                            ->join(C(DB_PREFIX)."borrow_info as i on  b.borrow_id = i.id")
                            ->field('i.borrow_interest_rate, i.repayment_type, b.investor_capital, b.add_time, b.is_auto, m.user_name')
                            ->where("b.borrow_id=".$borrow_id." and b.loanno<>''")->order('b.id')->limit($Page->firstRow.','.$Page->listRows)->select();
                $string = '';
               foreach($list as $k=>$v){
                   $relult=$k%2;
                    if(!$relult){
                   $string .= "<tr style='background-color: rgb(255, 255, 255);' class='borrowlist3'>
                       <td width='148' style='width:143px;' class='txtC'>".hidecard($v['user_name'],5)."</td>
                          <td  width='148' style='width:143px;' class='txtC'>";
                          }else{
                               $string .= "<tr style='background-color: rgb(236, 249, 255);' class='borrowlist5'>
                       <td width='148' style='width:143px;' class='txtC'>".hidecard($v['user_name'],5)."</td>
                          <td  width='148' style='width:143px;' class='txtC'>";
                              }
                        $string .= $v['is_auto']?'自动':'手动'; 
                    $string .= "</td>
                          <td  width='128' style='width:143px;' class='txtRight pr30'>".Fmoney($v['investor_capital'])."元</td>
                          <td width='198' style='width:143px;' class='txtC'>".date("Y-m-d H:i",$v['add_time'])."</td>
                         <td  style='width:143px;'></td></tr>";
                }
                
                echo empty($string)?'暂时没有投资记录':$string;
            }
            
        }
		*/
		

  
    }
?>
