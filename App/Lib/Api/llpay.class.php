<?php

class llpay
{

    private static $_instance;
    private $llpay;
    private $llpayConfig;

//    public $card_no = '6214830118322717';//卡号
    public $card_no = '';//卡号
    public $bank_name = '';//卡号
    public $acct_name = '';//姓名
    public $id_no = '';//身份证号
    public $no_agree = '';//协议号
    public $no_order = 0;     //商品唯一编号
    public $user_id = '';//用户唯一ID
    public $name_goods = '商品名称';//商品名称
    public $info_order = '商品描述';//商品描述
    public $money_order = 0;
    public $notify_url = '';//服务器异步通知页面路径
    public $return_url = '';//页面跳转同步通知页面路径
    public $card_info = array();
    public $ajax='';
    public $user_info_dt_register = '';//用户注册时间
    public $user_info_full_name = '';//用户注册姓名
    public $user_info_id_no = '';//用户证件号码
    public $user_info_identify_type = 1;
    public $user_info_identify_state = 1;

    private $busi_partner = 101001; //商品类型
    private $frms_ware_category = 2009;

    private function __construct()
    {
        if (false === import('App.Api.llpayApi.llpay_submit'))
            throw new Exception('连连支付Api加载失败!');
        $payconfig = FS("Webconfig/wappayconfig");
        $this->llpayConfig = $payconfig['llpay'];
        if (empty($this->llpayConfig['enable']))
            throw new Exception('在线付款功能尚未开启');
        $this->llpay = new LLpaySubmit($payconfig['llpay']);
    }

    //通过API获取用户帮卡信息
    public function getUserBankcard($uid){
        return $this->llpay->getUserBankcard($uid);
    }
    //获取用户绑定卡信息
    public function getUserBank($uid)
    {
        $this->card_info = M('llpayinfo')->find($uid);
        if($this->card_info['status'])
        {
            return true;
        }
        return false;;
    }

    public function getUnBankCard($uid){
        return $this->llpay->getUnBankCard($uid);
    }

    public function getPayinfo($uid){
        return M('llpayinfo')->where(array('uid'=>$uid))->find();
    }
    //检查卡号是否可用
    public function getAllowBank()
    {
        $result = $this->llpay->getAllowBank($this->card_no);
        $this->card_info = array(
            'status'=> '交易成功' == $result->ret_msg,
            'ret_msg'=>$result->ret_msg,
            'bank_name'=>empty($result->bank_name) ? $this->bank_name : $result->bank_name,
            'bank_code'=>$result->bank_code,
            'card_type'=>$result->card_type,
            'day_amt'=> $result->day_amt,
            'month_amt'=>$result->month_amt,
            'single_amt'=>$result->single_amt,
            'card_no'=>$this->card_no,
            'first' => 1
        );
    }

    public function Rsaverify($res)
    {
        $sign = $res->sign;
        unset($res->sign);
        $res = llpayCore::paraFilter2($res);
        $res = llpayCore::argSort($res);
        foreach($res as $key=>$val){
            $str[] = $key.'='.$val;
        }
        $str = join('&',$str);
        return llpayRsa::Rsaverify($str,$sign);
    }

    public function paySubmit()
    {
        //构造要请求的参数数组，无需改动
        $parameter = array (
            "oid_partner" => trim($this->llpayConfig['oid_partner']),
            "app_request" => trim($this->llpayConfig['app_request']),
            "sign_type" => trim($this->llpayConfig['sign_type']),
            "valid_order" => trim($this->llpayConfig['valid_order']),
            "user_id" => $this->user_id,
            "busi_partner" => $this->busi_partner,
            "no_order" => $this->no_order,
            "dt_order" => llpayCore::local_date('YmdHis', time()),
            "name_goods" => $this->name_goods,
            "info_order" => $this->info_order,
            "money_order" => $this->money_order,
            "notify_url" => $this->notify_url,
            "url_return" => $this->return_url,
            "card_no" => $this->card_no,
            "acct_name" => $this->acct_name,
            "id_no" => $this->id_no,
            "valid_order" => (is_numeric($this->llpayConfig['valid_order']) && 30<= $this->llpayConfig['valid_order']) ? $this->llpayConfig['valid_order'] : 10080
        );
        if($this->no_agree){
            $parameter["no_agree"] = $this->no_agree;
        }

        $risk = array();
        foreach($parameter as $key=>$val){
            $risk[]=$key.'='.$val;
        }

        $risk = array(
            'frms_ware_category'=>$this->frms_ware_category,
            'user_info_mercht_userno'=>$this->user_id,
            'user_info_dt_register'=>$this->user_info_dt_register,
            'user_info_full_name'=>$this->acct_name,
            'user_info_id_no'=>$this->id_no,
            'user_info_identify_type'=>$this->user_info_identify_type,
            'user_info_identify_state'=>$this->user_info_identify_state
        );

        $parameter['risk_item'] = llpayCore::decodeUnicode(json_encode($risk));

        $html_text = $this->llpay->buildRequestForm($parameter, "post", "确认");

        if($this->ajax){
            return $html_text;
        }
        die($html_text);
    }


    public static function getInstance()
    {
        if (is_null(self::$_instance) || isset (self::$_instance)) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }
}