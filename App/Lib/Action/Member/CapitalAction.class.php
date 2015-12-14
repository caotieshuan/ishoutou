<?php
// 本类由系统自动生成，仅供测试用途
class CapitalAction extends MCommonAction {

    public function index(){

		if(ListMobile()){
			$this->detail(true);
		}

		$this->display();
    }
	public function ajaxdetail(){
		$list = $this->detail(true);
		foreach($list as &$v){
			$v['add_time'] = date('Y-m-d H:i',$v['add_time']);
			$v['moneys'] = $v['account_money']+$v['back_money'];
		}

		$this->ajaxReturn($list,'JSON');
	}
    public function summary($re=false){
		$vlist = getMemberMoneySummary($this->uid);
		$this->assign("vo",$vlist);
		

        $this->assign('pcount', get_personal_count($this->uid)); 

		$minfo =getMinfo($this->uid,true);
        $this->assign("minfo",$minfo); 
        $this->assign('benefit', get_personal_benefit($this->uid));   //收入
        $this->assign('out', get_personal_out($this->uid));      //支出



		////////////////////////////////////////////////////////////////////
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function detail($re = false){
		$logtype = C('MONEY_LOG');
		$this->assign('log_type',$logtype);
		$logtype_in = C('MONEY_LOG_IN');
		$this->assign('log_type_in',$logtype_in);
		$logtype_out = C('MONEY_LOG_OUT');
		$this->assign('log_type_out',$logtype_out);

		$map['uid'] = $this->uid;
		if($_GET['start_time']&&$_GET['end_time']){
			$_GET['start_time'] = strtotime($_GET['start_time']." 00:00:00");
			$_GET['end_time'] = strtotime($_GET['end_time']." 23:59:59");
			if($_GET['start_time']<$_GET['end_time']){
				$map['add_time']=array("between","{$_GET['start_time']},{$_GET['end_time']}");
				$search['start_time'] = $_GET['start_time'];
				$search['end_time'] = $_GET['end_time'];
			}
		}
		if(!empty($_GET['log_type'])){
				$map['type'] = intval($_GET['log_type']);
				$search['log_type'] = intval($_GET['log_type']);
		}

		$list = getMoneyLog($map,10);

		$this->assign('search',$search);
		$this->assign("list",$list['list']);		
		$this->assign("pagebar",$list['page']);	
        $this->assign("query", http_build_query($search));
		if(true === $re){
			$dpage = array();
			$dpage['numpage'] = $list['count'] ? ceil($list['count']/10) : 1;
			$dpage['curpage'] = (int)$_GET['p'] ? (int)$_GET['p'] : 1;
			$this->assign("dpage",$dpage);
			$this->assign( "noview",false);
			return $list['list'];
		}
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
	
	public function export(){
		import("ORG.Io.Excel");

		$map=array();
		$map['uid'] = $this->uid;
		if($_GET['start_time']&&$_GET['end_time']){
			$_GET['start_time'] = strtotime($_GET['start_time']." 00:00:00");
			$_GET['end_time'] = strtotime($_GET['end_time']." 23:59:59");
			
			if($_GET['start_time']<$_GET['end_time']){
				$map['add_time']=array("between","{$_GET['start_time']},{$_GET['end_time']}");
				$search['start_time'] = $_GET['start_time'];
				$search['end_time'] = $_GET['end_time'];
			}
		}
		if(!empty($_GET['log_type'])){
				$map['type'] = intval($_GET['log_type']);
				$search['log_type'] = intval($_GET['log_type']);
		}

		$list = getMoneyLog($map,100000);
		
		$logtype = C('MONEY_LOG');
		$row=array();
		$row[0]=array('序号','发生日期','类型','影响金额','可用余额','冻结金额','待收金额','说明');
		$i=1;
		foreach($list['list'] as $v){
				$row[$i]['i'] = $i;
				$row[$i]['uid'] = date("Y-m-d H:i:s",$v['add_time']);
				$row[$i]['card_num'] = $v['type'];
				$row[$i]['card_pass'] = $v['affect_money'];
				$row[$i]['card_mianfei'] = ($v['account_money']+$v['back_money']);
				$row[$i]['card_mianfei0'] = $v['freeze_money'];
				$row[$i]['card_mianfei1'] = $v['collect_money'];
				$row[$i]['card_mianfei2'] = $v['info'];
				$i++;
		}
		
		$xls = new Excel_XML('UTF-8', false, 'moneyLog');
		$xls->addArray($row);
		$xls->generateXML("moneyLog");
	}


}