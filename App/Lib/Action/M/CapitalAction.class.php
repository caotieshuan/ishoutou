<?php
// 本类由系统自动生成，仅供测试用途
class CapitalAction extends MCommonAction {

    public function index(){
	
		//累计充值总和
		$sum = M("member_payonline")->field("SUM(money) as money")->where("uid = {$this->uid} and status = 1")->select();
		
		$this->assign("c_money",$sum[0]['money']);
		//累计提现总和
		$wsum = M("member_withdraw")->field("SUM(withdraw_money) as withdraw_money")->where("uid = {$this->uid} and withdraw_status = 2")->select();
		$this->assign("w_money",$wsum[0]['withdraw_money']);
		$vlist = getMemberMoneySummary($this->uid);
		$this->assign("vo",$vlist);
		

        $this->assign('pcount', get_personal_count($this->uid)); 

		$minfo =getMinfo($this->uid,true);
        $this->assign("minfo",$minfo); 
        $this->assign('benefit', get_personal_benefit($this->uid));   //收入
        $this->assign('out', get_personal_out($this->uid));      //支出
		
		//详情
		$logtype = C('MONEY_LOG');
		$this->assign('log_type',$logtype);

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
		
		$list = getMoneyLog($map,15);
		//dump($list);
		//exit;
		$this->assign('search',$search);
		$this->assign("list",$list['list']);		
		$this->assign("pagebar",$list['page']);	
        $this->assign("query", http_build_query($search));
		
		//银行卡添加
		
		$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
        if($ids!=1){
            $data['html'] = '<style type="text/css"> .error_msg{padding:20px; font-size:17px; color:#333333;} .error_msg a{color:#1D53BF; font-weight: bold;}  </style>
            <div class="error_msg">您还未完成身份验证，请先进行实名认证.点击这里<a href="'.__APP__.'/M/verify/idcard">进行实名认证</a></div>';
                                                                              
        }else{
            if(!M('escrow_account')->where("uid={$this->uid} and account <>''")->count('uid')){
               $data['html'] = '<style type="text/css"> .error_msg{padding:20px; font-size:17px; color:#333333;} .error_msg a{color:#1D53BF; font-weight: bold;}  </style>
               <div class="error_msg">你还未绑定托管账户，请先绑定托管账户:马上<a href="'.U('/M/bank/bindingAccount').'" >绑定托管账户</a></div>'; 
              $this->display();
            }
			$voinfo = M("member_info")->field('idcard,real_name')->find($this->uid);
			$vobank = M("member_banks")->field(true)->where("uid = {$this->uid} and bank_num !=''")->find();
			$vobank['bank_province'] = M('area')->getFieldByName("{$vobank['bank_province']}",'id');
			$vobank['bank_city'] = M('area')->getFieldBycityName("{$vobank['bank_city']}",'id');
			
			
			$this->assign("voinfo",$voinfo);
			
			$this->assign("vobank",$vobank);
			//dump($this->gloconf['BANK_NAME']);die;
			$this->assign("bank_list",$this->gloconf['BANK_NAME']);
			$this->assign('edit_bank', $this->glo['edit_bank']);
			$data['html'] = $this->fetch();
		}
		$this->display();
    }

    public function summary(){
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

    public function detail(){
		$logtype = C('MONEY_LOG');
		$this->assign('log_type',$logtype);

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
		
		$list = getMoneyLog($map,15);
		//dump($list);
		//exit;
		
		if(!empty($_GET['start_time']) || !empty($_GET['end_time']) || !empty($_GET['log_type'])){
			$html .="<table width='100%' border='0'>
					  <tr>
						<td align='center'>时间</td>
						<td align='center'>操作</td>             
						<td align='center'>影响金额（元）</td>
						<td align='center'>余额（元）</td>
						<td align='center'>冻结金额</td>
						<td align='center'>待收金额</td>
						<td align='center'>说明</td>
					  </tr>";
			foreach($list['list'] as $key=>$v){
				$tmp = $v['account_money'] + $v['back_money'];
				$html .="<tr>
							<td align='center'>".date("Y-m-d",$v['add_time'])."</td>
							<td align='center'>".$v['type']."</td>              
							<td align='center'>".$v['affect_money']."</td>
							<td align='center'>".$tmp."</td>
							<td align='center'>".$v['freeze_money']."</td>
							<td align='center'>".$v['collect_money']."</td>
							<td align='center'>".$v['info']."</td>
						</tr>";
							
				
			}
			$html .="</table>";
			
			$data['html'] = $html;
			exit(json_encode($data));
		
		}
		
		$this->assign('search',$search);
		$this->assign("list",$list['list']);		
		$this->assign("pagebar",$list['page']);	
        $this->assign("query", http_build_query($search));
		$this->display();
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