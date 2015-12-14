<?php

require_once ("llpay_core.function.php");
require_once ("llpay_md5.function.php");
require_once ("llpay_rsa.function.php");

class LLpaySubmit {

	protected $llpay_config;
	/**
	 *连连认证支付网关地址
	 *
	 */
	var $llpay_gateway_new = 'https://yintong.com.cn/llpayh5/authpay.htm';
	var $llpay_gateway_bankcardquery = 'https://yintong.com.cn/traderapi/bankcardquery.htm';
	var $llpay_gateway_userbankcard = 'https://yintong.com.cn/traderapi/userbankcard.htm';
	var $llpay_gateway_bankcardunbind = 'https://yintong.com.cn/traderapi/bankcardunbind.htm';

	function __construct($llpay_config) {
		$this->llpay_config = $llpay_config;
	}
	function LLpaySubmit($llpay_config) {
		$this->__construct($llpay_config);
	}

	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	function buildRequestMysign($para_sort) {
		$prestr = llpayCore::createLinkstring($para_sort);
                $prestr =stripslashes($prestr);
		switch (strtoupper(trim($this->llpay_config['sign_type']))) {
			case "MD5" :
				$mysign = llpayMd5::md5Sign($prestr, $this->llpay_config['key']);
				break;
			case "RSA" :
				$mysign = llpayRsa::RsaSign($prestr, $this->llpay_config['RSA_PRIVATE_KEY']);
				break;
			default :
				$mysign = "";
		}
		return $mysign;
	}


	/**
	 * 生成要请求给连连支付的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组
	 */
	function buildRequestPara($para_temp) {
		$para_filter = llpayCore::paraFilter($para_temp);
		$para_sort = llpayCore::argSort($para_filter);


//		$para_sort['name_goods'] = $para_sort['name_goods'].'-订单编号('.$para_sort['no_order'].')';

		$mysign = $this->buildRequestMysign($para_sort);

		if($para_sort['no_order']){
			M('llpaypost')->add(
				array(
					'pay_no'=>$para_sort['no_order'],
					'res_data'=>json_encode($para_sort),
					'dateline'=>date('Y-m-d H:i:s'),
					'sign'=>$mysign
				)
			);
		}



		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->llpay_config['sign_type']));
		//foreach ($para_sort as $key => $value) {
		//	$para_sort[$key] = urlencode($value);
		//}
		//return urldecode(json_encode($para_sort));
		return json_encode($para_sort);
	}

	/**
	 * 生成要请求给连连支付的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组字符串
	 */
	function buildRequestParaToString($para_temp) {
		$request_data = llpayCore::createLinkstringUrlencode($para_temp);
		return $request_data;
	}

	/**
	 * 建立请求，以表单HTML形式构造（默认）
	 * @param $para_temp 请求参数数组
	 * @param $method 提交方式。两个值可选：post、get
	 * @param $button_name 确认按钮显示文字
	 * @return 提交表单HTML文本
	 */
	function buildRequestForm($para_temp, $method, $button_name) {
		$para_temp['bg_color'] = '008cb4';

		$para = $this->buildRequestPara($para_temp);

		$sHtml = "<div style='display: none'><form id='paysubmit' name='paysubmit' action='" . $this->llpay_gateway_new . "' method='" . $method . "'>";
		$sHtml .= "<input type='hidden' name='req_data' id='req_post' value='" . $para . "'/>";
		$sHtml = $sHtml . "<input type='submit' value='" . $button_name . "'></form>";
		$sHtml = $sHtml."<script>window.onload = document.forms.paysubmit.submit();</script></div>";
		return $sHtml;
	}

	/**
	 * 建立请求，以模拟远程HTTP的POST请求方式构造并获取连连支付的处理结果
	 * @param $para_temp 请求参数数组
	 * @return 连连支付处理结果
	 */
	function buildRequestHttp($para_temp) {
		$request_data = $this->buildRequestPara($para_temp);
		$sResult = llpayCore::getHttpResponsePOST($this->llpay_gateway_new, $this->llpay_config['cacert'], $request_data, trim(strtolower($this->llpay_config['input_charset'])));

		return $sResult;
	}

	public function getAllowBank($bankid)
	{
		$para_temp = array(
			'oid_partner'=>trim($this->llpay_config['oid_partner']),
			'sign_type' => 'RSA',
			'pay_type'=>'D',
			'card_no'=>$bankid,
			'flag_amt_limit'=>1
		);
		$request_data = $this->buildRequestPara($para_temp);
		$sResult = llpayCore::getHttpResponsePOST($this->llpay_gateway_bankcardquery, $this->llpay_config['cacert'], $request_data, trim(strtolower($this->llpay_config['input_charset'])));
		return json_decode($sResult);
	}
	public function getUnBankCard($uid)
	{
		$result = (array)$this->getUserBankcard($uid);

		if($result['agreement_list']){
			foreach($result['agreement_list'] as $val)
			{
				$para_temp = array(
					'oid_partner'=>trim($this->llpay_config['oid_partner']),
					'sign_type' => 'RSA',
					'pay_type'=>'D',
					'user_id'=>$uid,
					'no_agree'=>$val->no_agree,
				);
				$request_data = $this->buildRequestPara($para_temp);
				llpayCore::getHttpResponsePOST($this->llpay_gateway_bankcardunbind, $this->llpay_config['cacert'], $request_data, trim(strtolower($this->llpay_config['input_charset'])));
			}
		}
		return true;
	}
	public function getUserBankcard($uid)
	{
		$para_temp = array(
			'oid_partner'=>trim($this->llpay_config['oid_partner']),
			'sign_type' => 'RSA',
			'pay_type'=>'D',
			'user_id'=>$uid,
			'offset'=>'0',
		);
		$request_data = $this->buildRequestPara($para_temp);
		$sResult = llpayCore::getHttpResponsePOST($this->llpay_gateway_userbankcard, $this->llpay_config['cacert'], $request_data, trim(strtolower($this->llpay_config['input_charset'])));
		return json_decode($sResult);
	}
	/**
	 * 建立请求，以模拟远程HTTP的POST请求方式构造并获取连连支付的处理结果，带文件上传功能
	 * @param $para_temp 请求参数数组
	 * @param $file_para_name 文件类型的参数名
	 * @param $file_name 文件完整绝对路径
	 * @return 连连支付返回处理结果
	 */
	function buildRequestHttpInFile($para_temp, $file_para_name, $file_name) {
		$para = $this->buildRequestPara($para_temp);
		$para[$file_para_name] = "@" . $file_name;
		$sResult = llpayCore::getHttpResponsePOST($this->llpay_gateway_new, $this->llpay_config['cacert'], $para, trim(strtolower($this->llpay_config['input_charset'])));
		return $sResult;
	}

	/**
	 * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
	 * return 时间戳字符串
	 */
	function query_timestamp() {
		$url = $this->llpay_gateway_new . "service=query_timestamp&partner=" . trim(strtolower($this->llpay_config['partner'])) . "&_input_charset=" . trim(strtolower($this->llpay_config['input_charset']));
		$encrypt_key = "";

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName("encrypt_key");
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

		return $encrypt_key;
	}
}