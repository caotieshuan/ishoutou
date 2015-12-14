<?php
// 本类由系统自动生成，仅供测试用途
class ChargeAction extends MCommonAction {

    public function index(){
		 //判断是否实名认证
		if(ListMobile()){
			redirect('/mpay');
		}
		$this->display();
    }

	public function chargeatm() {
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
	
    public function allcharge(){
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function charge(){
		
		$bank_all = $this->is_bank();
		$map['uid'] = $this->uid;
		$account_money = M('member_money')->where($map)->find();
		
		$ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		if($ids!=1){
			 $data['html'] = '<script type="text/javascript">alert("您还未完成身份验证,请先进行实名认证！");window.location.href="'.__APP__.'/member/verify?id=1#fragment-3";</script>';
			echo json_encode($data);exit;
		} 
		/*
		//判断是否手机认证
		$phones = M('members_status')->getFieldByUid($this->uid,'phone_status');
		if($phones!=1){
				 $data['html'] = '<script type="text/javascript">alert("您还未手机认证,请先进行手机认证！");window.location.href="'.__APP__.'/member/verify?id=1#fragment-3";</script>';
			echo json_encode($data);exit;
		}
		*/
		
       
		 
		 
		 
		
		$this->assign("account_money",$account_money);
		$this->assign("payConfig",FS("Webconfig/payconfig"));
		$this->assign("bank_all",$bank_all);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
/**判断是否开启环迅支付如果开启获取银行编码*/
	protected function is_bank()
	{
		$payConfig=FS("Webconfig/payconfig");
		if($payConfig['ips']['enable'] == 1)
		{
			Vendor('Nusoap.nusoap');
			$submitdata['MerCode'] = $payConfig['ips']['MerCode'];
			$submitdata['SignMD5']= strtolower(MD5($payConfig['ips']['MerCode'].$payConfig['ips']['MerKey']));
			//$ws="http://webservice.ips.net.cn/web/Service.asmx?wsdl";		//测试环境
			$ws="http://webservice.ips.com.cn/web/Service.asmx?wsdl";		//正式环境
			$client = new SoapClient($ws);
			//$client->__getFunctions();
			//$client->__getTypes();	
			$result=$client->GetBankList($submitdata);
			$decode_bank=urldecode($result->GetBankListResult);
			$array=explode('#',$decode_bank);
			$bank_all=array();
			foreach($array as $v)
			{	
				$bank_all[]=explode('|',$v);
			}
		}

		return $bank_all;

	}

	public function chargealipay() {


	$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
	
	public function postalipay() {
		$this->display();
	}
	
	public function dopostalipay() {
		
		$savedata['uid'] = $this->uid;
		$savedata['money'] = $this->_post("money");
		$savedata['ali_name'] = $this->_post("ali_name");
		$savedata['true_name'] = $this->_post("true_name");
		$savedata['user_phone'] = $this->_post("user_phone");
	//var_dump($savedata['money'] );die;
		$savedata['add_time'] = time();
		$savedata['status'] = 1;
		$savedata['u_name'] = session("u_user_name");
		if($savedata['ali_name']==""){
		$this->error("支付宝账号不能为空");
		}
		if($savedata['true_name']==""){
		$this->error("真实姓名不能为空");
		
		}
		if($savedata['money']=="" && $savedata['money']==0 ){
			$this->error("充值金额不能为空");
		}
		if(!get_magic_quotes_gpc()){
			addslashes($savedata['uid']);
			doubleval($savedata['money']);
			addslashes($savedata['ali_name']);
			addslashes($savedata['true_name']);
			addslashes($savedata['user_phone']);
		}
		$ret = M("member_alipay")->add($savedata);
		
		if($ret) {
			//$res['msg'] = "充值申请成功,请等待审核!";
			//$res['status'] = 1;
			$this->success("充值成功");
			//exit;
		}else {
			//$res['msg'] = "充值申请失败,请重试!";
			//$res['status'] = 0;
		//echo json_encode($res);
		//	exit;
		$this->error("充值失败");
		}
		
	}
	
    public function chargeoff(){
		$this->assign("vo",M('article_category')->where("type_name='线下充值'")->find());
		
        $config = FS("Webconfig/payoff");
        $this->assign('bank', $config['BANK']);
        $this->assign('info',$config['BANK_INFO']);
        $this->display();
    }

	public function chargeslog(){
		if(!ListMobile()){
			exit;
		}
		$this->chargelog(true);
		$this->display();
	}

	public function ajaxchargeslog(){
		$list= $this->chargelog(true);
		foreach ($list as &$v) {
			$v['add_time'] = date('Y-m-d H:i',$v['add_time']);
		}
		$this->ajaxReturn($list,'JSON');
	}

    public function chargelog($re = false){
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
		$list = getChargeLog($map,15);
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);
		$this->assign("success_money",$list['success_money']);
		$this->assign("fail_money",$list['fail_money']);

		if(true === $re){
			$dpage = array();
			$dpage['numpage'] = $list['count'] ? ceil($list['count']/15) : 1;
			$dpage['curpage'] = (int)$_GET['p'] ? (int)$_GET['p'] : 1;
			$this->assign("dpage",$dpage);
			return $list['list'];
		}

		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
    public function uploadImg()
    {
        $uid = $this->uid;
        if ( $_POST['picpath'] ){ //删除
            $imgpath = substr( $_POST['picpath'], 1 );           
            if ( in_array( $imgpath, $_SESSION['imgfiles'] ) ){                
                $res = unlink( C( "WEB_ROOT" ).$imgpath );                
                if ( $res )        $this->success( "删除成功", "", $_POST['oid'] );                
                else             $this->error( "删除失败", "", $_POST['oid'] );                
            }else{                
                $this->error( "图片不存在", "", $_POST['oid'] );            
            }        
        } else { //上传
            $this->savePathNew = C( "MEMBER_UPLOAD_DIR" )."PayImg/$uid/";            
            $this->saveRule = date( "YmdHis", time() ).rand( 0, 1000 );            
            $info = $this->CUpload(); 

            if ( !isset( $_SESSION['count_file'] ) )    $_SESSION['count_file'] = 1;            
            else                 ++$_SESSION['count_file'];

            $data['img'] = $info[0]['savepath'].$info[0]['savename'];  
            
                      
            $_SESSION['imgfiles'][$_SESSION['count_file']] = $data['img'];            
            echo "{$_SESSION['count_file']}:".__ROOT__."/".$data['img'];        
        }
    }

}
