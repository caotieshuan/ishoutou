<?php
    class DebtAction extends HCommonAction
    {
       /**
        * 债权转让列表
        * 
        */
        public function index()
        {
			
		if($this->uid){
			
			$uid = $this->uid;
		}else{
			
			$uid = 88;
		}
           
        $searchMap['borrow_status']=array("in",'2,4,6,7,3'); 
            $parm['map'] = $searchMap;
            $parm['pagesize'] = 2;
            $sort = "desc";
			$parm['orderby']="b.borrow_status ASC,b.id DESC";
            D("DebtBehavior");
            $Debt = new DebtBehavior();
			
            $list = $Debt->listAllz($parm);
		    
            $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
            if($this->isAjax()){
                $str = '';
       foreach($list['list'] as $vb){
					
		 $str.="<div class='box'>";
		 $str.=" <p class='tit'><a href='/m/invest/detail/id/$vb[id]'>$vb[borrow_name]</a></p>";
		 $str.="<table cellpadding='0' cellspacing='0' border='0' class='table'>";
		 $str.="<tr>";
		 $str.="<td>借款标题：</td><td>".getIco($vb)."<a href='".getInvestUrl($vb[id])."' title='$vb[borrow_name]' class='BL_name'>".cnsubstr($vb[borrow_name],12)."</a></td><td>信誉等级：</td><td>".getLeveIco($vb[credits],2)."</td>";
		 $str.="</tr><tr>";
		 $str.="<td>借款利率：</td><td>$vb[borrow_interest_rate]%</td><td>转让价格：</td><td>$vb[transfer_price]</span>&nbsp;元</td>";
		 $str.="</tr><tr>";
		 $str.="<td>待收本息：</td><td>￥$vb[money]</span>&nbsp;元</td><td>转让期数／总期数：</td><td>$vb[period]期/$vb[total_period]期</td>";
         $str.="</table>";	
         $str.="<p class='sub'>";
         if($vb[status]==2){
         $str.="<a href='javascript:;' onclick='buy_debt($vb[invest_id])' id='tz' class='<css1 an　btn-a fr'>我要投资</a>";
          }elseif($vb[status]==1){
           $str.="<img  class='' src='/Style/H/images/status/ywc.gif'  />";
         }elseif($vb[status]==4){
            $str.="<img  class='' src='/Style/H/images/status/yts.gif'  />";
           }
        $str.=" </p></div>";	   
					
					
     }
         echo $str;
            }else{
                    $this->assign("list", $list);
					$this->assign('uid',$uid);
                    $this->assign("searchUrl",$searchUrl);
                    $this->assign("searchMap",$searchMap);
                    $this->display();  
            }
			
			
		
      
       
       
		   
			
			
           
        }

         /**
        * 购买债权提示框
        * 
        */
        public function buydebt()
        {
			
            //判断用户是否登录
		if(session('u_id')==null){
			echo jsonmsg('您还没有登录，请先登录！',2);exit;
		}
            $invest_id = intval($_REQUEST['invest_id']);
            !$invest_id && ajaxmsg(L('参数错误'),0);
            $debt = M("invest_detb")->field("transfer_price, money")->where("invest_id={$invest_id}")->find();
            $buy_user = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
            $account =  $buy_user['account_money'] + $buy_user['back_money'];
            
            $this->assign('debt', $debt);
            $this->assign('account', $account);
            $this->assign('invest_id', $invest_id);
            $d['content'] = $this->fetch();
            echo json_encode($d);
            
        }
		

         /**
        * 确认购买
        * 流程： 检测购买条件
        * 购买
        */
        public function buy()
        {
		   //dump($this->uid);die;(2)
            $paypass = strval($_REQUEST['paypass']);
            $invest_id = intval($_REQUEST['invest_id']);
			//dump($invest_id);(146)die;
            /*var_dump($paypass);//(123)
			var_dump($invest_id);//(1)
			uid=2
			
			*/
            D("DebtBehavior");
            $Debt = new DebtBehavior($this->uid);
            // 检测是否可以购买  密码是否正确，余额是否充足
            $result = $Debt->buya($paypass, $invest_id);
            //dump($result);die;
            if($result === '购买成功'){
                //ajaxmsg('购买成功');
				ajaxmsg($result,1);
            }else{
                ajaxmsg($result, 0);
            }
        }
		public function zq_chenggong(){
			$this->display();
		}
        /**
        * 手机版债权转让
        */
        // public function buydebt()
        // {   
        //     if(!$this->uid){
        //         if($this->isAjax()){
        //             die("请先登录后投资");   
        //         }else{
        //             $this->redirect('M/pub/login');       
        //         }
        //     }
        //     if($this->isAjax()){   // ajax提交投资信息
        //         $paypass = strval($this->_post('paypass'));
        //         $invest_id = intval($this->_post('invest_id'));
        //         D("DebtBehavior");
        //         $Debt = new DebtBehavior($this->uid);
        //         // 检测是否可以购买  密码是否正确，余额是否充足
        //         //$result = $Debt->buy($paypass, $invest_id);
        //         $result = $Debt->checkBuy($invest_id);

        //         if($result == 'TRUE'){
        //             $info = $Debt->qddBuy($invest_id);
               
        //        if(is_array($info)){
        //                    // 发送到乾多多
        //                     $loanconfig = FS("Webconfig/loanconfig");
        //                     $buy_qdd = M("escrow_account")->field('*')->where("uid={$this->uid}")->find();
        //                     $invest_info = M("borrow_investor")->field("reward_money, investor_uid, borrow_id")->where("id={$invest_id}")->find();
                            
                            
        //                     $sell_qdd = M("escrow_account")->field('*')->where("uid={$invest_info['investor_uid']}")->find();
                            
        //                     $secodary = '';
        //                     import("ORG.Loan.Escrow");
        //                     $loan = new Escrow();
                            
        //                     if($info['fee']){  // 借款管理费
        //                         $secodary[] = $loan->secondaryJsonList($loanconfig['pfmmm'], $info['fee'],'债权转让手续费', '支付平台债权转让手续费'); 
        //                     }
        //                     $secodary && $secodary = json_encode($secodary);
        //                     // 投标奖励
        //                     $markNo = 'zq'.$invest_info['borrow_id'].'_'.$invest_id;
                            
        //                     $loanList[] = $loan->loanJsonList($buy_qdd['qdd_marked'], $sell_qdd['qdd_marked'], $info['serial'], $markNo , $info['money'], $info['money'],'债权转让',$invest_id,$secodary);
        //                     $loanJsonList = json_encode($loanList);
        //                     $returnURL = C('WEB_URL').U("wapdebtReturn");
        //                     $notifyURL = C('WEB_URL').U("notify");

        //                     //echo $returnURL."  notifyURL".$notifyURL;die();
                            
        //                     $data =  $loan->transfer($loanJsonList, $returnURL , $notifyURL,2,1,2,1,$info['serial']);
        //                     $form =  $loan->setForm($data, 'transfer');
        //                     echo $form."正在跳转至乾多多。。。";
                            
        //                     exit;    
        //                }else{
        //                    $this->error("数据有误");
        //                }
        //         }else{
        //             die($result);
        //         }
        //     }else{  
        //     $invest_id = $this->_get('bid');
        //     $debt = M("invest_detb")->field("transfer_price, money")->where("invest_id={$invest_id}")->find();
        //     $buy_user = M("member_money")->field("account_money, back_money")->where("uid={$this->uid}")->find();
        //     $account =  $buy_user['account_money'] + $buy_user['back_money'];
        //     $paypass = M("members")->field('pin_pass')->where('id='.$this->uid)->find();
        //     $this->assign('paypass', $paypass['pin_pass']);
        //     $this->assign('debt', $debt);
        //     $this->assign('account', $account);
        //     $this->assign('invest_id', $invest_id);
        //     $this->display();           
        //     }
        // }
    }

?>
