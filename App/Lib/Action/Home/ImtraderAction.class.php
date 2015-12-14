<?php
	class ImtraderAction extends HCommonAction {
		public function index(){
			//查询数据分配到模板
			$res = get_cps_trader('shares_global');
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
		public function affirm(){
			//获取用户ID
			$uid = session('u_id');
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

			
			$this->display();
		}
		public function getMeMonery(){
			/* //判断是否实名认证
			$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
			if($ids!=1){
				echo jsonmsg('<font style="color:#E74A4A;font-weight:bold;font-size:16px;margin-bottom:30px;">您还未完成身份验证,请先进行实名认证！</font>',0);exit;
			} */
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
		public function contract(){
		
		$this->display();
	}
	}
	

?>