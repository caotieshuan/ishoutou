<?php
// 本类由系统自动生成，仅供测试用途
class MpayAction extends HCommonAction {

    protected $sign;
    protected $payCore;
    protected $locked = false;
    protected $return_url;
    protected $notify_url;
    protected $member_url;
    protected $pay_no;
    protected $payName = 'llpay';
    protected $name_goods = '手投网充值';
    protected $payMoney = 0;
    protected $paydetail =array();
    protected $orderInfo = array();
    protected $cardInfo = array();
    protected $moreInfo = false;

    public function _initialize()
    {

        parent::_initialize();
        $this->return_url = "http://".$_SERVER['HTTP_HOST']."/Mpay/payreturn";
        $this->notify_url = "http://".$_SERVER['HTTP_HOST']."/Mpay/paynotice";
        $this->member_url = "http://".$_SERVER['HTTP_HOST']."/member";
        try{
            import('App.Api.llpay');
            $this->payCore = llpay::getInstance();
        }catch (Exception $e){
            $this->errLog($e,'_initialize');
            die($e->getMessage());
        }
    }
    public function payreturn(){
        self::isPost();
        $res = json_decode($_POST['res_data']);
        try{
            if($this->Rsaverify($res)){
                $this->payDone();
                $this->getBindCard();
            }else{
                throw new Exception('签名串错误');
            }
        }catch (Exception $e){
            $this->errLog($e,__METHOD__);
            redirect('/member/index');
        }
        redirect('/member/index');
    }

    public function paynotice(){
        self::isPost();
        $res = json_decode(file_get_contents("php://input"));
        try{
            if($this->Rsaverify($res)){
                $this->payDone();
                $this->getBindCard();
                $result  = array(
                    'ret_code'=>'0000',
                    'ret_msg'=>'交易成功'
                );
                echo json_encode($result);
            }else{
                $result  = array(
                    'ret_code'=>'0001',
                    'ret_msg'=>'交易已处理'
                );
                echo json_encode($result);
                //throw new Exception('签名串错误');
            }
        }catch (Exception $e){
            $this->errLog($e,__METHOD__);
            $result  = array(
                'ret_code'=>'9999',
                'ret_msg'=>$e->getMessage()
            );
            echo json_encode($result);
        }
    }

    protected function payDone(){
        if($this->locked) return false;
        $this->locked = true;
        $newid = memberMoneyLog($this->orderInfo['uid'],3,$this->orderInfo['money'],"充值订单号:".$this->orderInfo['pay_no'],0,'@网站管理员@');//更新成功才充值,避免重复充值
        $this->locked = false;

        $this->okLog($newid,$this->orderInfo['pay_no'].'金额发放',__METHOD__,$this->orderInfo['uid']);

        M('member_payonline')->where(array('pay_no'=>$this->orderInfo['pay_no']))->save(array('payres'=>$newid));
        if($newid){
            $vx = M('members')->find($this->orderInfo['uid']);
            SMStip("payonline",$vx['user_phone'],array("#USERANEM#","#MONEY#"),array($vx['user_name'],$this->orderInfo['money']));
        }
        return $newid;
    }

    public function pay()
    {
        $nopay = $this->getCardInfo();
        if(1 == $nopay['phone_status']){
            redirect('/mpay');
        }
        if(1 == $nopay['id_status']){
            redirect('/mpay');
        }

        if(1 != $this->payCore->card_info['status']){
            redirect('/mpay');
        }
        $this->getMemberInfo();
        $this->assign("nopay",$nopay);
        $this->assign("bankpic",$this->getBankPic());
        $this->assign("cardinfo",$this->payCore->card_info);
        $this->display();
    }
    public function index()
    {
        $nopay = $this->getCardInfo();
        $this->assign("nopay",$nopay);
        $this->assign("bankpic",$this->getBankPic());

        $this->assign("cardinfo",$this->payCore->card_info);
        $this->display();
    }

    public function getBankPic(){
        if(empty($this->payCore->card_info['bank_name'])){
            return false;
        }
        $bankName = $this->payCore->card_info['bank_name'] == '中国银行' ? '中国银行' : str_replace('中国','',$this->payCore->card_info['bank_name']);
        $bankPic = M('bankList')->where(array('bankname'=>array('like','%'.$bankName)))->getField('bankfile');

        return $bankPic;
    }

    public function submit(){
        if(!$this->uid){
            redirect('/member/common/login');
        }
        if(empty($_SERVER['HTTP_REFERER'])){
            redirect('/mpay/repl');
            exit;
        }
        $payinfo = $this->payCore->getPayinfo($this->uid);
        if(empty($payinfo)){
            die('参数错误');
        }


        $i = 1;
        $this->pay_no = date("YmdHis").mt_rand( 100000,999999);
        $this->payMoney = getFloatValue($payinfo['nowmoney'],2);
        while(M('member_payonline')->where(array('pay_no'=>$this->pay_no))->count()){
            $this->pay_no = date("YmdHis").mt_rand( 100000,999999);
            $i++;
        }

        $reg_time = M('members')->where('id='.$this->uid)->getField('reg_time');
        //获取用户身份认证信息
        $realname = $this->getMemberInfo();
        $this->payCore->user_info_dt_register = date('YmdHis');
        $this->payCore->card_no = $payinfo['card_no'];
        $this->payCore->no_order =$this->pay_no;
        $this->payCore->name_goods =$this->name_goods.'-编号('.$this->pay_no.')';
        $this->payCore->info_order =$this->info_order;
        $this->payCore->money_order = $this->payMoney;
        $this->payCore->user_id = $this->uid;
        $this->payCore->acct_name = $realname['real_name'];          //姓名
        $this->payCore->id_no = $realname['idcard']; //身份证号
        $this->payCore->return_url = $this->return_url;
        $this->payCore->notify_url = $this->notify_url;
        //if($payinfo['no_agree']){
            //$this->payCore->html = $this->fetch();
            $this->payCore->ajax = 1;
       // }
        $this->payCore->no_agree = $this->getNoArgee($payinfo);
        $this->getPaydetail();
        $res = M('member_payonline')->add($this->paydetail);
        if($res){
            echo $this->payCore->paySubmit();
        }
        //die('订单创建失败');
    }

    //更换银行卡
    public function repl(){
        $nopay = $this->getCardInfo();
        if(false === empty($nopay)){
            redirect('/mpay');
        }else if(!$this->payCore->card_info['first']){
            redirect('/mpay/pay');
        }
        $this->getMemberInfo();
        $this->assign("bank_list",$this->gloconf['BANK_NAME_M']);
        $this->assign("cardinfo",$this->payCore->card_info);
        $this->display();
    }
    public function llpayinfo(){
        self::isPost();
        $nopay = $this->getCardInfo();
        if(1 == $nopay['phone_status']){
            ajaxmsg('请先进行手机认证');
        }
        if(1 == $nopay['id_status']){
            ajaxmsg('需要先进行实名认证');
        }

        $nowmoney = htmlspecialchars($_POST['money']);

        if(false == is_numeric($nowmoney) || empty($nowmoney)){
            ajaxmsg('金额填写错误');
        }

        if(50>$nowmoney){
            //ajaxmsg('充值金额必须大于50元');
        }

        if(1 == $this->payCore->card_info['first']){
            $this->firstPay();
            $otherUid = M('llpayinfo')->where(array('card_no'=>$this->payCore->card_no,'status'=>1))->getField('uid');
            if($otherUid && $this->uid <> $otherUid){
                ajaxmsg('卡号已被绑定！');
            }
        }
        if(1 == $this->payCore->card_info['status']){
            $payinfo = $this->getPayinfo();
            $payinfo['nowmoney'] =$nowmoney;
            $mod = M('llpayinfo');
            if($mod->where(array('uid'=>$this->uid))->count()){
                $res = $mod->where(array('uid'=>$this->uid))->save($payinfo);
            }else{
                $payinfo['uid']=$this->uid;
                $res = $mod->add($payinfo);
            }
            if($res)
                ajaxmsg('验证成功',2);
            ajaxmsg('异常错误，请刷新重试');
        }else{
            ajaxmsg('卡号暂不支持快捷支付！');
        }
    }

    protected function getPaydetail(){
        if(!$this->uid) exit;
        $this->paydetail['pay_no'] = $this->pay_no;
        $this->paydetail['money'] = $this->payMoney;
        $this->paydetail['name_goods'] = $this->name_goods;
        $this->paydetail['fee'] = 0;
        $this->paydetail['way'] = $this->payName;
        $this->paydetail['add_time'] = time();
        $this->paydetail['add_ip'] = get_client_ip();
        $this->paydetail['status'] = 0;
        $this->paydetail['uid'] = $this->uid;
    }

    protected function getMemberInfo(){
        $memberInfo = M('member_info')->field('idcard,real_name')->where(array('uid'=>$this->uid))->find();
        $this->assign("memberInfo",$memberInfo);
        return  $memberInfo;
    }

    protected function getPayData(){
        if(1 == $this->payCore->card_info['first']){
            $card_no = htmlspecialchars($_POST['card_no']);
        }else if(1 == $this->payCore->card_info['status']){
            $card_no = $this->payCore->card_info['card_no'];
            //$this->payCore->no_agree = $this->payCore->card_info['no_agree'];
        }
        return $card_no;
    }

    protected function getPayinfo(){
        if(1 == $this->payCore->card_info['first']){
            $payinfo = array(
                'bank_code'=>$this->payCore->card_info['bank_code'],
                'bank_name'=>$this->payCore->card_info['bank_name'],
                'card_no'=>$this->payCore->card_info['card_no'],
                'card_type'=>$this->payCore->card_info['card_type'],
                'day_amt'=>$this->payCore->card_info['day_amt'],
                'month_amt'=>$this->payCore->card_info['month_amt'],
                'lastdate'=>time(),
                'createdate'=>time()
            );
            if(true === $this->moreInfo){
                $payinfo['bank_address']=htmlspecialchars($_POST['bank_address']);
                $payinfo['province']=M('area')->where(array('id'=>intval($_POST['province'])))->getField('name');
                $payinfo['city']=M('area')->where(array('id'=>intval($_POST['city'])))->getField('name');
            }else{
                $vobank = M("member_banks")->field(true)->where("uid = {$this->uid}")->find();
                $payinfo['bank_address']=$vobank['bank_address'];
                $payinfo['province']=$vobank['bank_province'];
                $payinfo['city']=$vobank['bank_city'];
            }
        }else{
            $this->payCore->no_agree = $this->payCore->card_info['no_agree'];
            $payinfo = array(
                'lastdate'=>time(),
            );
        }
        return $payinfo;
    }

    //第一次充值
    protected function firstPay(){
        if($_POST['card_no']) {
            $this->moreInfo = true;
            $this->payCore->card_no = $_POST['card_no'];
            $this->payCore->getAllowBank();

            if(3 == $this->payCore->card_info['card_type']){
                ajaxmsg('请使用借记卡，暂不支持信用卡');
            }

            $this->payCore->getUnBankCard($this->uid);//给所有卡解绑
        }
    }
    protected function getCardInfo(){
        $this->isLogin();
        $nopay = array();
        $phones = M('members')->getFieldByid($this->uid,'user_phone');
        if(empty($phones)){
            $nopay['phone_status']=1;
        }
        $ids = M('members_status')->getFieldByUid($this->uid,'id_status');
        if($ids!=1){
            $nopay['id_status'] = 1;
        }
        if(empty($nopay)){
            $vobank = M("member_banks")->field(true)->where("uid = {$this->uid}")->find();
            if($vobank['bank_num']){
                $this->assign("cardlist",'更换银行卡');
                $this->payCore->card_no = $vobank['bank_num'];//卡号
                $this->payCore->bank_name = $vobank['bank_name'];
                if(false === $this->payCore->getUserBank($this->uid)){
                    $this->payCore->getAllowBank();
                }
            }else{
                $this->assign("cardlist",'添加银行卡');
                $this->assign("addcard",'1');
                $this->payCore->card_info['first'] =1;
            }
        }

        return $nopay;

    }

    protected function Rsaverify($res)
    {
        if(!$this->payCore->Rsaverify($res)){
            throw new Exception('订单不合法,'.json_encode($res));
        };
        $this->orderInfo = M('member_payonline')->where(array('pay_no'=>$res->no_order))->find();
        if(empty($this->orderInfo)){
            throw new Exception('订单不存在!');
        }else if(0<$this->orderInfo['status']){
            throw new Exception('订单已完成');
        }else{
            if('SUCCESS' == $res->result_pay){
                $InerOrder = array(
                    'oid_paybill' => $res->oid_paybill,
                    'tran_id' => $res->oid_paybill,
                    'result_pay'=>$res->result_pay,
                    'money_order'=>$res->money_order,
                    'settle_date'=>$res->settle_date,
                    'dt_order'=>$res->dt_order,
                    'status'=>1
                );
                $result = M('member_payonline')->where(array('pay_no'=>$res->no_order))->save($InerOrder);
                $this->okLog($result,$res->no_order.'更新订单状态',__METHOD__,$this->orderInfo['uid']);
                return $result;
            }else if('PROCESSING' == $res->result_pay){
                throw new Exception('支付处理中');
            }else if('FAILURE' == $res->result_pay){
                throw new Exception('支付失败');
            }
        }
    }


    //根据返回信息查询绑定银行卡
    protected function getBindCard(){
        $payinfo = $this->payCore->getPayinfo($this->orderInfo['uid']);
        if(0 == $payinfo['status']){
            $banks = array(
                'uid'=>$payinfo['uid'],
                'bank_num'=>$payinfo['card_no'],
                'bank_province'=>$payinfo['province'],
                'bank_city'=>$payinfo['city'],
                'add_time'=>$payinfo['createdate'],
                'add_ip'=>get_client_ip(),
                'bank_name'=>$payinfo['bank_name'],
                'bank_address'=>$payinfo['bank_address']
            );
            if(M('member_banks')->where(array('uid'=>$this->orderInfo['uid']))->count()){
                unset($banks['uid']);
                $res1 = M('member_banks')->where(array('uid'=>$payinfo['uid']))->save($banks);
            }else{
                $res1 = M('member_banks')->add($banks);
            }
            $this->okLog($res1,$this->orderInfo['pay_no'].'更新银行卡',__METHOD__,$this->orderInfo['uid']);
            $res = M('llpayinfo')->where(array('uid'=>$payinfo['uid']))->save(array('status'=>1,'no_agree'=>$this->getNoArgee($payinfo,0)));
            $this->okLog($res,$this->orderInfo['pay_no'].'绑定银行卡',__METHOD__,$this->orderInfo['uid']);
            return $res;
        }
    }

    protected function getNoArgee($payinfo,$re=1){
        if($payinfo['no_agree'] && 1 == $payinfo['status']){
            return $payinfo['no_agree'];
        }
        $bankCardList = $this->payCore->getUserBankcard($payinfo['uid']);
        if(false === empty($bankCardList->agreement_list)){
            $bankCard = array_shift($bankCardList->agreement_list);
            if(substr($payinfo['card_no'],-4) == $bankCard->card_no)
            {
                $this->okLog(1,$this->orderInfo['pay_no'].'卡号对比',__METHOD__,$payinfo['uid']);
                if(1 == $re){
                    M('llpayinfo')->where(array('uid'=>$payinfo['uid']))->save(array('no_agree'=>$bankCard->no_agree));
                }
                return $bankCard->no_agree;
            }else{
                $this->okLog(0,$this->orderInfo['pay_no'].'卡号对比',__METHOD__,$this->orderInfo['uid']);
            }
        }else{
            $this->okLog(0,$this->orderInfo['pay_no'].'用户查询最后一次付款卡失败'.json_encode($bankCardList),__METHOD__,$payinfo['uid']);
        }
    }



    protected function errLog(Exception $e,$err_mod){
        M('llpaylog')->add(
            array(
                'log_code'=>0001,
                'log_file'=>$e->getFile(),
                'log_msg'=>$e->getMessage(),
                'dateline'=>date('Y-m-d H:i:s'),
                'log_mod'=>$err_mod,
                'uid'=>$this->uid,
                'status'=>'成功',
                'log_ip'=>get_client_ip()
            )
        );
    }

    protected function okLog($res,$msg,$mod,$uid=0){
        M('llpaylog')->add(
            array(
                'log_code'=>000,
                'log_file'=>000,
                'log_msg'=>$msg,
                'dateline'=>date('Y-m-d H:i:s'),
                'log_mod'=>$mod,
                'uid'=>$uid,
                'log_ip'=>get_client_ip(),
                'status'=>$res ? '成功' : '失败'
            )
        );
    }
    protected static function isPost(){
        if('POST' != $_SERVER['REQUEST_METHOD']){
            redirect('/mpay');
        }
    }
    protected function isLogin(){
        if(!$this->uid){
            redirect('/member/common/login');
        }
    }
}