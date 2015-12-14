<?php
    class TinvestAction extends HCommonAction
    {
        public function index()
        {
              $parm = array();
            //$Map  = ' b.borrow_status = 2 and b.is_show=1 and b.transfer_total > b.transfer_out';
            $Map  = 'b.is_show=1 '; 
            $parm['map'] = $Map;
            $parm['orderby'] = "b.is_show desc,b.id DESC";
            $parm['pagesize'] = 8;
            $listTBorrow = getTBorrowList($parm);
            if($this->isAjax()){
                $string ='';
                foreach($listTBorrow['list'] as $vb){
                    $string .='<a href="'.getInvestUrl($vb['id']).'">
                        <div class="biao_box">
                         <h4><div class="title_img" style="float:left;margin-top:4px">'.getIco($vb).'</div>
                            <div style="float:left;text-align:left">'.cnsubstr($vb['borrow_name'],5).'</div></h4><table>
                            <tr>
                                <td>融资金额：'.MFormt($vb['borrow_money']).'元</td>
                                <td>融资期限：'.$vb['borrow_duration'];
                                $string .= $vb['repayment_type']==1?'天':'个月';
                                $string .= '</tr><tr><td>年化利率：'.$vb['borrow_interest_rate'].'%/';
                                $string .= $vb['repayment_type']==1?'天' : '年'; 
                                $string .='</td><td><span class="progress"> <span class="precent" style="width":'.$vb['progress'].'></span></span>
                 </td></tr></table> </div> </a>';
                }
                echo $string;
            }else{
                $this->assign("listTBorrow",$listTBorrow);
                 $this->display(); 
            }
        }
        public function tdetail() 
        {    
            if($_GET['type']=='commentlist'){
                //评论
                $cmap['tid'] = intval($_GET['id']);
                $clist = getCommentList($cmap,5);
                $this->assign("commentlist",$clist['list']);
                $this->assign("commentpagebar",$clist['page']);
                $this->assign("commentcount",$clist['count']);
                $data['html'] = $this->fetch('commentlist');
                exit(json_encode($data));
            }


            $pre = C('DB_PREFIX');
            $id = intval($_GET['id']);
            $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
            
            //合同ID
            if($this->uid){
                $invs = M('transfer_borrow_investor')->field('id')->where("borrow_id={$id} AND (investor_uid={$this->uid} OR borrow_uid={$this->uid})")->find();
                if($invs['id']>0) $invsx=$invs['id'];
                elseif(!is_array($invs)) $invsx='no';
            }else{
                $invsx='login';
            }
            $this->assign("invid",$invsx);
            //合同ID
            //borrowinfo
            //$borrowinfo = M("borrow_info")->field(true)->find($id);
            $borrowinfo = M("transfer_borrow_info b")->join("{$pre}transfer_detail d ON d.borrow_id=b.id")->field(true)->find($id);
            /*if(!is_array($borrowinfo) || $borrowinfo['is_show'] == 0){
                $this->error("数据有误或此标已认购完");
            }*/
            $borrowinfo['progress'] = getfloatvalue($borrowinfo['transfer_out']/$borrowinfo['transfer_total'] * 100, 2);
            $borrowinfo['need'] = getfloatvalue(($borrowinfo['transfer_total'] - $borrowinfo['transfer_out'])*$borrowinfo['per_transfer'], 2 );
            $borrowinfo['updata'] = unserialize($borrowinfo['updata']);
            $this->assign("vo", $borrowinfo);
                                    
                                
            //此标借款利息还款相关情况
            //memberinfo
            $memberinfo = M("members m")->field("m.id,m.customer_name,m.customer_id,m.user_name,m.reg_time,m.credits,fi.*,mi.*,mm.*")->join("{$pre}member_financial_info fi ON fi.uid = m.id")->join("{$pre}member_info mi ON mi.uid = m.id")->join("{$pre}member_money mm ON mm.uid = m.id")->where("m.id={$borrowinfo['borrow_uid']}")->find();
            $areaList = getArea();
            $memberinfo['location'] = $areaList[$memberinfo['province']].$areaList[$memberinfo['city']];
            $memberinfo['location_now'] = $areaList[$memberinfo['province_now']].$areaList[$memberinfo['city_now']];
            $this->assign("minfo",$memberinfo);
            //memberinfo
            
            //investinfo
            $fieldx = "bi.investor_capital,bi.transfer_month,bi.transfer_num,bi.add_time,m.user_name,bi.is_auto,bi.final_interest_rate";
            $investinfo = M("transfer_borrow_investor bi")->field($fieldx)->join("{$pre}members m ON bi.investor_uid = m.id")->where("bi.borrow_id={$id}")->order("bi.id DESC")->select();
            $this->assign("investinfo",$investinfo);
            //investinfo
            
            $oneday = 86400;
            $time_1 = time() - 30 * $oneday.",".time();
            $time_6 = time() - 180 * $oneday.",".time();
            $time_12 = time() - 365 * $oneday.",".time();
            $mapxr['borrow_id'] = $id;
            $this->assign("time_all_out", M("transfer_borrow_investor")->where($mapxr)->sum("transfer_num"));
            $mapxr['add_time'] = array("between","{$time_1}");
            $this->assign("time_1_out", M("transfer_borrow_investor")->where($mapxr)->sum("transfer_num"));
            $mapxr['add_time'] = array("between","{$time_6}");
            $this->assign("time_6_out",M("transfer_borrow_investor")->where($mapxr)->sum("transfer_num"));
            $mapxr['add_time'] = array("between","{$time_12}");
            $this->assign("time_12_out",M("transfer_borrow_investor")->where($mapxr)->sum("transfer_num"));
            
            $mapxr = array();
            $mapxr['borrow_id'] = $id;
            $mapxr['status'] = 2;
            $this->assign("time_all_back", M("transfer_borrow_investor")->where($mapxr)->sum("transfer_num"));
            $mapxr['back_time'] = array("between","{$time_1}");
            $this->assign("time_1_back",M("transfer_borrow_investor")->where($mapxr)->sum("transfer_num"));
            $mapxr['back_time'] = array("between","{$time_6}");
            $this->assign("time_6_back", M("transfer_borrow_investor")->where($mapxr)->sum("transfer_num"));
            $mapxr['back_time'] = array("between","{$time_12}");
            $this->assign("time_12_back", M("transfer_borrow_investor")->where($mapxr)->sum("transfer_num"));
            
          
            $this->assign("Bconfig",$Bconfig);
            $this->display();
        } 
        
        public function invest()
        {
            if(!$this->uid){
                if($this->isAjax()){
                    die("请先登录后投资");   
                }else{
                    $this->redirect('M/pub/login');       
                }
            }
            if($this->isAjax()){
                $borrow_id = intval($this->_get('bid'));
                $tnum = intval($_POST['cnum']);
                $pre = c( "DB_PREFIX" );

                $m = M("member_money")->field('account_money,back_money,money_collect')->find($this->uid);
                $amoney = $m['account_money']+$m['back_money'];
                $uname = session("u_user_name");
                $binfo = M("transfer_borrow_info")
                        ->field( "borrow_uid,borrow_interest_rate,transfer_out,transfer_back,transfer_total,
                                per_transfer,is_show,deadline,min_month,increase_rate,reward_rate,borrow_duration")
                        ->find($borrow_id);
                
                if($this->uid == $binfo['borrow_uid']) ajaxmsg("不能去投自己的标",0);
                $month = $binfo['borrow_duration'];//手机版默认投资最大期限
                $max_num = $binfo['transfer_total'] - $binfo['transfer_out'];
                if($max_num < $tnum){
                    die("本标还能认购最大份数为".$max_num."份，请重新输入认购份数" );
                }
                $money = $binfo['per_transfer'] * $tnum;
                if($amoney < $money){
                    die( "尊敬的{$uname}，您准备认购{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再认购");
                }
                $vm = getMinfo($this->uid,"m.pin_pass,mm.invest_vouch_cuse,mm.money_collect");
                $pin_pass = $vm['pin_pass'];
                $pin = md5($_POST['paypass']);
                // if ($pin != $pin_pass){
                //     die( "支付密码错误，请重试" );
                // }
                $tinvest_id = TinvestMoney($this->uid,$borrow_id,$tnum,$month);//投企业直投
                if($tinvest_id){
				      //die('TRUE');
                    $loanconfig = FS("Webconfig/loanconfig");
                    $orders = 'T'.date("YmdHi").$tinvest_id;
                    // 发送到乾多多
                    $invest_qdd = M("escrow_account")->field('*')->where("uid={$this->uid}")->find();
                    $borrow_qdd = M("escrow_account")->field('*')->where("uid={$binfo['borrow_uid']}")->find();
                    $invest_info = M("transfer_borrow_investor")->field("reward_money, borrow_fee")->where("id={$tinvest_id}")->find();
                    $secodary = '';
                    import("ORG.Loan.Escrow");
                    $loan = new Escrow();
                    if($invest_info['reward_money']>0.00){  // 投标奖励
                        $secodary[] = $loan->secondaryJsonList($invest_qdd['qdd_marked'], $invest_info['reward_money'],'二次分配', '投标奖励'); 
                    }
                    if($invest_info['borrow_fee']>0.00){  // 借款管理费
                        $secodary[] = $loan->secondaryJsonList($loanconfig['pfmmm'], $invest_info['borrow_fee'],'二次分配', '借款管理费'); 
                    }
                    
                    $secodary && $secodary = json_encode($secodary);
                    
                    $loanList = $loan->loanJsonList($invest_qdd['qdd_marked'], $borrow_qdd['qdd_marked'], $orders, 'T_'.$borrow_id, $money, $binfo['borrow_money'],'投标',"对{$borrow_id}号企业直投进行投标",$secodary);
                    
                    $loanJsonList = json_encode($loanList);
                    $returnURL = C('WEB_URL').U("tinvest/wapinvestReturn");
                    $notifyURL = C('WEB_URL').U("tinvest/notify");
                    $data =  $loan->transfer($loanJsonList, $returnURL , $notifyURL,1,1,2,1); // 自动到帐
                    
                    $form =  $loan->setForm($data, 'transfer');
                   // echo "aaaaaa";die();
                    echo $form."正在跳转至乾多多。。。";;
                    exit;
                }else{
                    die("很遗憾，认购失败，请重试!");
                }
                
            }else{
                $borrow_id = $this->_get('bid');
                $pre = C('DB_PREFIX'); 
                $borrowinfo = M("transfer_borrow_info b")->join("{$pre}transfer_detail d ON d.borrow_id=b.id")->field(true)->find($borrow_id);

                $borrowinfo['progress'] = getfloatvalue($borrowinfo['transfer_out']/$borrowinfo['transfer_total'] * 100, 2);
                $borrowinfo['need'] = getfloatvalue(($borrowinfo['transfer_total'] - $borrowinfo['transfer_out'])*$borrowinfo['per_transfer'], 2 );
                $borrowinfo['updata'] = unserialize($borrowinfo['updata']);
                $this->assign("vo", $borrowinfo);    
                
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
        
    
    }
?>
