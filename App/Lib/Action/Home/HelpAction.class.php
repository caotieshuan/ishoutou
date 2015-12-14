<?php
// 本类由系统自动生成，仅供测试用途
class HelpAction extends HCommonAction {
    public function index(){
		$is_subsite=false;
		$typeinfo = get_type_infos();
		if(intval($typeinfo['typeid'])<1){
			$typeinfo = get_area_type_infos($this->siteInfo['id']);
			$is_subsite=true;
		}

		$typeid = $typeinfo['typeid'];
		$typeset = $typeinfo['typeset'];
		//left
		$listparm['type_id']=$typeid;
		$listparm['limit']=20;
		if($is_subsite===false) $leftlist = getTypeListActa($listparm);//getTypeList($listparm);
		else{
			$listparm['area_id'] = $this->siteInfo['id'];
			$leftlist = getAreaTypeList($listparm);
		}
		$this->assign("leftlist",$leftlist);
		$this->assign("cid",$typeid);

		if($typeset==1){
			$parm['pagesize']=15;
			$parm['type_id']=$typeid;
			if($is_subsite===false){
				$list = getArticleList($parm);
				$vo = D('Acategory')->find($typeid);
				if($vo['parent_id']<>0) $this->assign('cname',D('Acategory')->getFieldById($vo['parent_id'],'type_name'));
				else $this->assign('cname',$vo['type_name']);
			}
			else{
				$vo = D('Aacategory')->find($typeid);
				if($vo['parent_id']<>0) $this->assign('cname',D('Aacategory')->getFieldById($vo['parent_id'],'type_name'));
				else $this->assign('cname',$vo['type_name']);
				$parm['area_id']= $this->siteInfo['id'];
				$list = getAreaArticleList($parm);
			}
			$this->assign("vo",$vo);
			$this->assign("numpage",($list['count'] ? ceil($list['count']/15) : 1));
			$this->assign("list",$list['list']);
			$this->assign("pagebar",$list['page']);
		}else{
			if($is_subsite===false){
				$vo = D('Acategory')->find($typeid);
				if($vo['parent_id']<>0) $this->assign('cname',D('Acategory')->getFieldById($vo['parent_id'],'type_name'));
				else $this->assign('cname',$vo['type_name']);
			}else{
				$vo = D('Aacategory')->find($typeid);
				if($vo['parent_id']<>0) $this->assign('cname',D('Aacategory')->getFieldById($vo['parent_id'],'type_name'));
				else $this->assign('cname',$vo['type_name']);
			}
			$this->assign("vo",$vo);
		}

		if('/aboutus/jianjie' == $_SERVER['PATH_INFO']){
			$typeinfo['templet'] = 'index_jianjie';
		}else if('/aboutus/anquan' == $_SERVER['PATH_INFO']){
			$typeinfo['templet'] = 'index_anquan';
		}else if('/aboutus/zhiya' == $_SERVER['PATH_INFO']){
			$typeinfo['templet'] = 'index_zhiya';
		}else if('/aboutus/tdjs' == $_SERVER['PATH_INFO']){
			$typeinfo['templet'] = 'index_tdjs';
		}

		if(ListMobile()){
			$this->display();
			exit;
		}
		$this->display($typeinfo['templet']);
    }

	public function ajaxindex(){

		$typeid = 43;//43是网站公告的ID。给手机版使用了
		$parm['pagesize']=15;
		$parm['type_id']=$typeid;
		$list = getArticleList($parm);
		foreach($list['list'] as &$v){
			$v['art_time'] = date('Y-m-d',$v['art_time']);
			$v['title'] = cnsubstr($v['title'],40);
		}
		$this->ajaxReturn($list['list']);
	}

	public function viewv(){
		$id=(int)$_GET['id'];

		if(ListMobile()){
			$vo = M('izhubo')->where("id = {$id}")->find(); 
			/*
			$items = array(
				1=>array(
					'手投网带您走进文交所',
					'story1'
				),
				2=>array(
					'手投网梦想基金走进津汕希望小学',
					'story2_2015.07.19',
				),
				3=>array(
					'为爱回报一点点',
					'story3_2015.07.19'
				),
				4=>array(
					'“互联网+”文化产业商务论坛隆重举行',
					'story4_2015.09.06'
				)*/
				$items = array(
					$vo['link_txt'],
					$vo['link_href']
				);
			//$item = isset($items[$id]) ? $items[$id] : $items[1];
				$list = M('izhubo')->where(" is_wap = 1 and is_show =1 ")->limit('4')->order('link_order  DESC ')->select();
			$this->assign("item",$items);
			$this->assign("list",$list);
			$this->display();
		}
	}
	//手机版使用（邀请好友）
	public function xinshou(){
		if(ListMobile()){
			$this->display();
		}
	}
	//手机版使用（邀请好友）
	public function yaoqin(){
		if(ListMobile()){
			$this->display();
		}
	}

	public function yaoqing(){
		$this->display();
	}
	//手机版使用（媒体报道）
	public function media(){
		if(ListMobile()){
			$this->display();
		}
	}
	//手机版使用(公司介绍)
	public function brief(){
		if(ListMobile()){
			$this->display();
		}
	}
	//手机版使用(安全)
	public function safety(){
		if(ListMobile()){
			$this->display();
		}
	}
	//手机版使用（团队介绍）
	public function team(){
		if(ListMobile()){
			$this->display();
		}
	}
	public function contact(){
		if(ListMobile()){
			$this->display();
		}
	}


	public function help(){
		$this->display();
	}
	public function videos()
	{
		///行业动态
		$parm['type_id'] = 2;
		$parm['limit'] =6;
	
		$this->assign("dynamiclists",getArticleList($parm));
		//dump(getArticleList($parm));die;
		///公司新闻
		$parm1['type_id'] = 38;
		$parm1['limit'] =6;
		$this->assign("gnewslists",getArticleList($parm1));

		//i主播
		$this->assign("izhubo",$this->getIzhuboList(1));
		//微视
		$this->assign("weishi",$this->getIzhuboList(2));

		//i主播
		$list_i = M('izhubo')->where(" link_type = 1 and is_wap =  1 and is_show =1 ")->order('link_order  DESC ')->select();
		$this->assign("list_i",$list_i);
		//微视
		$list_w = M('izhubo')->where(" link_type = 2 and is_wap =  1 and is_show =1 ")->order('link_order  DESC ')->select();
		$this->assign("list_w",$list_w);

		$this->display();
	}
	public function getIzhuboList($type){
		$list = M('izhubo')->where(" link_type = {$type} and is_wap =  2 and is_show =1 ")->order('link_order  DESC ')->select();
		return $list;
	}

	public function getIzhuboJson(){
		$type = $_GET['type'];
		$list = M('izhubo')->where(" link_type = {$type}  and is_wap =  2 and is_show =1 ")->order('link_order  DESC ')->select();
		echo json_encode($list);
	}
	public function view(){
		$id = intval($_GET['id']);
		if($_GET['type']=="subsite") {
			$vo = M('article_area')->find($id);
		}else {
			$vo = M('article')->find($id);
			$pre = C('DB_PREFIX');
			$sql = "update `{$pre}article` set ";
			$sql .= "`read_count`=`read_count`+1";
			$sql .= " WHERE `id`={$id}";
			$res = M()->execute($sql);
			
			$tid = $vo['type_id'];
			$wo = M('article_category')->find($tid);
			$this->assign("wo",$wo);
		}
		$this->assign("vo",$vo);

		//left
		$typeid = $vo['type_id'];
		$listparm['type_id']=$typeid;
		$listparm['limit']=15;
		if($_GET['type']=="subsite"){
			$listparm['area_id'] = $this->siteInfo['id'];
			$leftlist = getAreaTypeList($listparm);
		}else	$leftlist = getTypeListActa($listparm);
		
		$this->assign("leftlist",$leftlist);
		$this->assign("cid",$typeid);
		
		if($_GET['type']=="subsite"){
			$vop = D('Aacategory')->field('type_name,parent_id')->find($typeid);
			if($vop['parent_id']<>0) $this->assign('cname',D('Aacategory')->getFieldById($vop['parent_id'],'type_name'));
			else $this->assign('cname',$vop['type_name']);
		}else{
			$vop = D('Acategory')->field('type_name,parent_id')->find($typeid);
			if($vop['parent_id']<>0) $this->assign('cname',D('Acategory')->getFieldById($vop['parent_id'],'type_name'));
			else $this->assign('cname',$vop['type_name']);
		}

		$this->display();
	}
	
	public function kf(){
		$kflist = M("ausers")->where("is_kf=1")->select();
		$this->assign("kflist",$kflist);
		//left
		$listparm['type_id']=0;
		$listparm['limit']=20;
		if($_GET['type']=="subsite"){
			$listparm['area_id'] = $this->siteInfo['id'];
			$leftlist = getAreaTypeList($listparm);
		}else	$leftlist = getTypeList($listparm);
		
		$this->assign("leftlist",$leftlist);
		$this->assign("cid",$typeid);
		
		if($_GET['type']=="subsite"){
			$vop = D('Aacategory')->field('type_name,parent_id')->find($typeid);
			if($vop['parent_id']<>0) $this->assign('cname',D('Aacategory')->getFieldById($vop['parent_id'],'type_name'));
			else $this->assign('cname',$vop['type_name']);
		}else{
			$vop = D('Acategory')->field('type_name,parent_id')->find($typeid);
			if($vop['parent_id']<>0) $this->assign('cname',D('Acategory')->getFieldById($vop['parent_id'],'type_name'));
			else $this->assign('cname',$vop['type_name']);
		}

		$this->display();
	}

	public function tuiguang(){
		$_P_fee=get_global_setting();
		$this->assign("reward",$_P_fee);	
		$field = " m.id,m.user_name,sum(ml.affect_money) jiangli ";
		$list = M("members m")->field($field)->join(" lzh_member_moneylog ml ON m.id = ml.target_uid ")->where("ml.type=13")->group("ml.uid")->order('jiangli desc')->limit(10)->select();
		$this->assign("list",$list);	
		
		$this->display();
	}
	/*
	public function aaa(){
	
		$result = M('borrow_investor')->where('borrow_id = 18')->select();
		foreach ($result as $v){	
			$new = array();
			$new['is_new'] = 1;
			$new['reward_zhuce'] = 0;
			$parm = array();
			$parm['id'] = $v['investor_uid'];
			
			$re = M('members')->where($parm)->data($new)->save();
			dump(M()->getlastsql());
			echo '<br>';
		}
		
		exit;
	
	}
	*/
	public function ranking(){
			//投资排行榜
		$affect_money7 = M('member_moneylog l' )
							->field("abs(sum(l.affect_money) )as affect,m.user_name ")
							->join("lzh_members m ON l.uid = m.id ")
							->where("(l.type = 6 or l.type = 37) and l.affect_money < 0 ")
							->group("l.uid")
							->order("affect DESC")
							->limit("9")
							->select();
						
		$this->assign("affect_money7",$affect_money7);
		//日投资排行榜
		$morning = strtotime(date('Y-m-d'));//获取当天凌晨时间
		
		$Mmorning = strtotime(date('Y-m-d',strtotime('+1 day')));
		
		$affect_money8 = M('member_moneylog l' )
							->field("abs(sum(l.affect_money))as affect ,m.user_name,l.add_time ")
							->join("lzh_members m ON l.uid = m.id ")
							->where("(l.type = 6 or l.type = 39) and (l.add_time < {$Mmorning} and l.add_time > {$morning}) and l.affect_money < 0 ")
							->group("l.uid")
							->order("affect DESC")
							->limit("9")
							->select();
		
		
		$this->assign("affect_money8",$affect_money8);
		//echo M('member_moneylog l' )->getlastsql();exit;	
		//月投资排行榜	
		$MonthA = strtotime(date("Y-m-1"));	
		$thirty = strtotime(date("Y-m-30"));	
		$affect_money9 = M('member_moneylog l' )
							->field("abs(sum(l.affect_money))as affect ,m.user_name,l.add_time ")
							->join("lzh_members m ON l.uid = m.id ")
							->where("(l.type = 6 or l.type = 39) and (l.add_time < {$thirty} and l.add_time > {$MonthA}) and l.affect_money < 0")
							->group("l.uid")
							->order("affect DESC")
							->limit("9")
							->select();												
		$this->assign("affect_money9",$affect_money9);	
		$this->display();
	}
	
	//秒标未能自动复审时，管理员手动处理方法之应急处理方案  fan  2013-10-22
	//使用方法：直接在浏览器访问该方法。例如：http://www.rongtianxiabeat.cn/help/domiao?borrow_id=15
	 public function domiao()
    {
		$borrow_id = intval($_REQUEST['borrow_id']);
		$vm = M('borrow_info')->field('borrow_uid,borrow_money,has_borrow,borrow_type,borrow_status')->find($borrow_id);
		if(($vm['borrow_status']==7) ||($vm['borrow_status']==9) || ($vm['borrow_status']==10)){
			$this->error('该标已还款完成，请不要重复还款！');
			exit;
		}
		
		//复审投标检测
		$capital_sum1=M('investor_detail')->where("borrow_id={$borrow_id}")->sum('capital');
		$capital_sum2=M('borrow_investor')->where("borrow_id={$borrow_id}")->sum('investor_capital');
		if(($vm['borrow_money']!=$capital_sum2) || ($capital_sum1 != $capital_sum2) || ($vm['borrow_money'] !=$vm['has_borrow'])){
			$this->error('投标金额不统一，请确认！');
			exit;
		}else{
		//dump($borrow_id);exit;
			if($vm['borrow_type']==3){
				borrowApproved($borrow_id); 
				$done = borrowRepayment($borrow_id,1);
				if(!$done){
					$this->error('还款失败，请确认！');exit;
				}else{
					$this->success('还款成功，请确认！');
					exit;
				}
			}else{
				$this->error('非秒标类型，不能执行此操作，请确认！');exit;
			}
		}
	}
	//秒标未能自动复审时，管理员手动处理方法之应急处理方案  fan  2013-10-22
	
	public function invite(){
		
		$code = M('members')->where("recommend_id = {$this->uid}")->select();
		$count = M('members')->where("recommend_id = {$this->uid}")->count();
		
		$this->assign('list',$code);		
		$this->assign('count',$count);		
		$this->display();
	}
}