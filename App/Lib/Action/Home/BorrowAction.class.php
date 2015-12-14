<?php
// 本类由系统自动生成，仅供测试用途
class BorrowAction extends HCommonAction {
    public function index(){
		$per = C('DB_PREFIX');
		if($this->uid){
			$this->assign("mstatus", M('members_status')->field(true)->find($this->uid));
			$this->assign("mdata", getMemberInfoDone($this->uid));
			$minfo = getMinfo($this->uid,true);
			$this->assign("minfo", $minfo);
			$this->assign("netMoney", getNet($this->uid));//可用净值额度
			$_allnetMoney = getFloatValue(0.9*$minfo['money_collect'],2);
			$this->assign("allnetMoney",$_allnetMoney);//总净值额度
			$this->assign("capitalinfo", getMemberBorrowScan($this->uid));
		}
        $this->assign("pagebar", $donate_list['page']);
		$this->display();
    }
	
	public function post(){





		// if(!$this->uid) $this->error("请先登录",__APP__."/member/common/login");
		//$vminfo = M('members')->field("user_leve,time_limit,is_borrow,is_vip")->find($this->uid);
	/*	if($vminfo['is_vip']==0){
			$_xoc = M('borrow_info')->where("borrow_uid={$this->uid} AND borrow_status in(0,2,4)")->count('id');
			 if($_xoc>0)  $this->error("您有一个借款中的标，请等待审核",__APP__."/member/borrowin#fragment-1");

			 if(!($vminfo['user_leve']>0 && $vminfo['time_limit']>time())) $this->error("请先通过VIP审核再发标",__APP__."/member/vip");
			
			if($vminfo['is_borrow']==0){
				$this->error("您目前不允许发布借款，如需帮助，请与客服人员联系！");
				$this->assign("waitSecond",3);
			}
			
			$vo = getMemberDetail($this->uid);
			if($vo['province']==0 && $vo['province_now ']==0 && $vo['province_now ']==0 && $vo['city']==0 && $vo['city_now']==0 ){
				$this->error("请先填写个人详细资料后再发标",__APP__."/member/memberinfo#fragment-1");
			}
		}*/
		$vo = M('members')->field("id,user_name")->where("is_transfer=1")->select();//查询出所有流转会员
		$userlist = array();
		if(is_array($vo)){
			foreach($vo as $key => $v){
				$userlist[$v['id']]=$v['user_name'];
			}
		}
		$this->assign("userlist",$userlist);//流转会员
		$this->firm=M("article")->where("type_id=7")->field("id,title")->select();
		$gtype = text($_GET['type']);
		$vkey = md5(time().$gtype);
		switch($gtype){
			case "normal"://普通标
				$borrow_type=1;
			break;
			case "vouch"://新担保标
				$borrow_type=2;
			break;
			case "second"://秒还标
				$this->assign("miao",'yes');
				$borrow_type=3;
			break;
			case "net"://净值标
				$borrow_type=4;
			break;
			case "mortgage"://抵押标
				$borrow_type=5;
			break;
		}

		$this->firm=M("article")->where("type_id=7")->field("id,title")->select();
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
		$this->assign("borrow_use",$this->gloconf['BORROW_USE']);
		$this->assign("borrow_min",$this->gloconf['BORROW_MIN']);
		$this->assign("borrow_max",$this->gloconf['BORROW_MAX']);
		$this->assign("borrow_time",$this->gloconf['BORROW_TIME']);
		$this->assign("BORROW_TYPE",$borrow_config['BORROW_TYPE']);
		$this->assign("borrow_type",$borrow_type);
		$this->assign("borrow_day_time",$day_time);
		$this->assign("borrow_month_time",$month_time);
		$this->assign("repayment_type",$borrow_config['REPAYMENT_TYPE']);
		$this->assign("stock_type",$borrow_config['STOCK_TYPE']);
		$this->assign("vkey",$vkey);
		$this->assign("rate_lixt",$rate_lixt);
		
		$this->display();
	}
	
		function add_ajax()
	{
			$find=M("article")->where("id=".$_POST['id'])->field("art_keyword")->find();
		
			echo $find['art_keyword'];
	}
	function get_name(){
		$name = text($_POST['name']);
		$result = array(
			'code'=>0,
			'uid'=>0,
			'msg'=>'验证失败，用户不存在'
		);
		if($name){

			$user = M('members m')->join(C('DB_PREFIX')."member_info mf ON m.id=mf.uid")->where("user_name='{$name}'")->find();
			if($user){
				$result = array(
					'code'=>1,
					'uid'=>$user['id'],
					'msg'=>$user['real_name'].'，验证成功'
				);
			}
		}
		echo json_encode($result);
		exit;
	}

	public function save(){
		//if(!$this->uid) $this->error("请先登录",__APP__."/member/common/login");
		//if(empty($_FILES['topic']['name'])){
		//	$this->error('请上传标题图片');
		//}
		$borrow = array();
		//$this->savePathNew = C('HOME_UPLOAD_DIR').'Product/';
		//$this->thumbMaxWidth = C('PRODUCT_UPLOAD_W');
		//$this->thumbMaxHeight = C('PRODUCT_UPLOAD_H');
		//$this->saveRule = date("YmdHis",time()).rand(0,1000);
		//$info = $this->CUpload();
		//$borrow['topic'] = $info[0]['savepath'].$info[0]['savename'];
		if($_POST['borrow_uid'] == ''){
			$this->error('选择借款人不能为空！');
		}
		if($_POST['borrow_type'] == ''){
			
			$this->error('借款标类型不能为空！');
		}
		if($_POST['reward_type']&&($_POST['reward_type_1_value']>6 || $_POST['reward_type_1_value']<0)){
			$this->error("投标奖励超出范围！");
		}
		if($this->_post('stock_type') == ''){
			
			$this->error("选择配资类型不能为空！");
		}
		$capital_name = htmlspecialchars($this->_post('capital_name'));
		$capital_card = htmlspecialchars($this->_post('capital_card'));

			
		$pre = C('DB_PREFIX');
		//相关的判断参数
		$rate_lixt = explode("|",$this->glo['rate_lixi']);
		$borrow_duration = explode("|",$this->glo['borrow_duration']);
		$borrow_duration_day = explode("|",$this->glo['borrow_duration_day']);
		$fee_borrow_manage = explode("|",$this->glo['fee_borrow_manage']);
		$vminfo = M('members m')->join("{$pre}member_info mf ON m.id=mf.uid")->field("m.user_leve,m.time_limit,mf.province_now,mf.city_now,mf.area_now")->where("m.id={$this->uid}")->find();
		//相关的判断参数
		$borrow['borrow_type'] = $this->_post('borrow_type');
		if($borrow['borrow_type']==0) $this->error("校验数据有误，请重新发布");
		if(floatval($_POST['borrow_interest_rate'])>$rate_lixt[1] || floatval($_POST['borrow_interest_rate'])<$rate_lixt[0]) $this->error("提交的借款利率超出允许范围，请重试",0);
		$borrow['borrow_money'] = intval($_POST['borrow_money']);


		$_minfo = getMinfo($this->uid,"m.pin_pass,mm.account_money,mm.back_money,mm.credit_cuse,mm.money_collect");
		$_capitalinfo = getMemberBorrowScan($this->uid);
		///////////////////////////////////////////////////////
		$borrowNum=M('borrow_info')->field("borrow_type,count(id) as num,sum(borrow_money) as money,sum(repayment_money) as repayment_money")->where("borrow_uid = {$this->uid} AND borrow_status=6 ")->group("borrow_type")->select();
		$borrowDe = array();
		foreach ($borrowNum as $k => $v) {
			$borrowDe[$v['borrow_type']] = $v['money'] - $v['repayment_money'];
		}
		///////////////////////////////////////////////////
		switch($borrow['borrow_type']){
			case 1://普通标
				//if($_minfo['credit_cuse']<$borrow['borrow_money']) $this->error("您的可用信用额度为{$_minfo['credit_cuse']}元，小于您准备借款的金额，不能发标");
			break;
			case 2://新担保标
			case 3://秒还标
			break;
			case 4://净值标
				//$_netMoney = getFloatValue(0.9*$_minfo['money_collect']-$borrowDe[4],2);
				//if($_netMoney<$borrow['borrow_money']) $this->error("您的净值额度{$_netMoney}元，小于您准备借款的金额，不能发标");
			break;
			case 5://抵押标
				//$borrow_type=5;
			break;
		}
		
		$borrow['borrow_uid'] = $this->_post("borrow_uid");
		$borrow['borrow_name'] = text($_POST['borrow_name']);
		$borrow['borrow_duration'] = ($borrow['borrow_type']==3)?1:intval($_POST['borrow_duration']);//秒标固定为一月
		$borrow['borrow_interest_rate'] = floatval($_POST['borrow_interest_rate']);
		if(strtolower($_POST['is_day'])=='yes') $borrow['repayment_type'] = 1;
		elseif($borrow['borrow_type']==3) $borrow['repayment_type'] = 2;//秒标按月还
		else $borrow['repayment_type'] = intval($_POST['repayment_type']);
		if($borrow['repayment_type']=='1' || $borrow['repayment_type']=='5'){
			$borrow['total'] = 1;
		}else{
			$borrow['total'] = $borrow['borrow_duration'];//分几期还款
		}
		$borrow['capital_name'] = $capital_name;
		$borrow['capital_card'] = $capital_card;
		$borrow['borrow_status'] = 0;
		$borrow['borrow_use'] = intval($_POST['borrow_use']);
		$borrow['add_time'] = time();
		$borrow['collect_day'] = intval($_POST['borrow_time']);
		$borrow['add_ip'] = get_client_ip();
		$borrow['borrow_info'] = text($_POST['borrow_info']);
		$borrow['reward_type'] = intval($_POST['reward_type']);
		$borrow['reward_num'] = floatval($_POST["reward_type_{$borrow['reward_type']}_value"]);
		$borrow['borrow_min'] = intval($_POST['borrow_min']);
		$borrow['borrow_max'] = intval($_POST['borrow_max']);

		$prow = array();
		foreach($_POST['swfimglist'] as $key=>$v){
			$prow[$key]['img'] = substr($v,1);
			$prow[$key]['info'] = $_POST['picinfo'][$key];
		}
		$borrow['updata'] = false === empty($prow) ? serialize($prow) : '';


		/*$borrow['province'] = $vminfo['province_now'];
		$borrow['city'] = $vminfo['city_now'];
		$borrow['area'] = $vminfo['area_now'];*/
		if($_POST['is_pass']&&intval($_POST['is_pass'])==1) $borrow['password'] = md5($_POST['password']);
		$borrow['money_collect'] = floatval($_POST['moneycollect']);//代收金额限制设置
		
		
		//借款费和利息
		$borrow['borrow_interest'] = getBorrowInterest($borrow['repayment_type'],$borrow['borrow_money'],$borrow['borrow_duration'],$borrow['borrow_interest_rate']);
		
		
		if($borrow['repayment_type'] == 1){//按天还
			$fee_rate = (is_numeric($fee_borrow_manage[0]))?($fee_borrow_manage[0]/100):0.001;
			$borrow['borrow_fee'] = getFloatValue($fee_rate*$borrow['borrow_money']*$borrow['borrow_duration'],2);
		}else{
			$fee_rate_1=(is_numeric($fee_borrow_manage[1]))?($fee_borrow_manage[1]/100):0.02;
			$fee_rate_2=(is_numeric($fee_borrow_manage[2]))?($fee_borrow_manage[2]/100):0.002;
			if($borrow['borrow_duration']>$fee_borrow_manage[3]&&is_numeric($fee_borrow_manage[3])){
				$borrow['borrow_fee'] = getFloatValue($fee_rate_1*$borrow['borrow_money'],2);
				$borrow['borrow_fee'] += getFloatValue($fee_rate_2*$borrow['borrow_money']*($borrow['borrow_duration']-$fee_borrow_manage[3]),2);
			}else{
				$borrow['borrow_fee'] = getFloatValue($fee_rate_1*$borrow['borrow_money'],2);
			}
		}
		
		if($borrow['borrow_type']==3){//秒还标
			if($borrow['reward_type']>0){
				$_reward_money = getFloatValue($borrow['borrow_money']*$borrow['reward_num']/100,2);
			}
			$_reward_money =floatval($_reward_money);
			if(($_minfo['account_money']+$_minfo['back_money'])<($borrow['borrow_fee']+$_reward_money)) $this->error("发布此标您最少需保证您的帐户余额大于等于".($borrow['borrow_fee']+$_reward_money)."元，以确保可以支付借款管理费和投标奖励费用");
		}
		
		if($borrow['borrow_type']==2){
			$borrow['danbao']=$_POST['danbao'];
			$borrow['huilv'] = M('article')->where("id=".$_POST['danbao'])->getField('art_keyword');
		}
		//投标上传图片资料（暂隐）
		foreach($_POST['swfimglist'] as $key=>$v){
			if($key>10) break;
			$row[$key]['img'] = substr($v,1);
			$row[$key]['info'] = $_POST['picinfo'][$key];
		}
		$borrow['updata']=serialize($row);
		$borrow['stock_the'] = $this->_post("stock_the");
		$borrow['stock_bond_money'] = $this->_post("stock_bond_money");
		$borrow['stock_type'] = $this->_post('stock_type');
		
		$newid = M("borrow_info")->add($borrow);
		$suo=array();
		$suo['id']=$newid; 
        $suo['suo']=0;
        $suoid = M("borrow_info_lock")->add($suo);
		if($newid) $this->success("借款发布成功，网站会尽快初审");
		else $this->error("发布失败，请先检查是否完成了个人详细资料然后重试");
		
	}
	
	//swf上传图片
	public function swfUpload(){
		if($_POST['picpath']){
			$imgpath = substr($_POST['picpath'],1);
			if(in_array($imgpath,$_SESSION['imgfiles'])){
					 unlink(C("WEB_ROOT").$imgpath);
					 $thumb = get_thumb_pic($imgpath);
				$res = unlink(C("WEB_ROOT").$thumb);
				if($res) $this->success("删除成功","",$_POST['oid']);
				else $this->error("删除失败","",$_POST['oid']);
			}else{
				$this->error("图片不存在","",$_POST['oid']);
			}
		}else{
			$this->savePathNew = C('HOME_UPLOAD_DIR').'Product/';
			$this->thumbMaxWidth = C('PRODUCT_UPLOAD_W');
			$this->thumbMaxHeight = C('PRODUCT_UPLOAD_H');
			$this->saveRule = date("YmdHis",time()).rand(0,1000);
			$info = $this->CUpload();
			$data['product_thumb'] = $info[0]['savepath'].$info[0]['savename'];
			if(!isset($_SESSION['count_file'])) $_SESSION['count_file']=1;
			else $_SESSION['count_file']++;
			$_SESSION['imgfiles'][$_SESSION['count_file']] = $data['product_thumb'];
			echo "{$_SESSION['count_file']}:".__ROOT__."/".$data['product_thumb'];//返回给前台显示缩略图
		}
	}
	public function loan(){

		$this->display();
	}
}