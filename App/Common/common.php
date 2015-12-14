<?php
require APP_PATH."Common/Lib.php";
require APP_PATH."Common/Stock.php";
require APP_PATH."Common/DataSource.php";
//require APP_PATH."Common/Refusedcc.php";//防御CC攻击  fan  2013-11-28
function acl_get_key(){
	empty($model)?$model=strtolower(MODULE_NAME):$model=strtolower($model);
	empty($action)?$action=strtolower(ACTION_NAME):$action=strtolower($action);
	
	$keys = array($model,'data','eqaction_'.$action);
	require C('APP_ROOT')."Common/acl.inc.php";
	$inc = $acl_inc;
	
	$array = array();
	foreach($inc as $key => $v){
			if(isset($v['low_leve'][$model])){
				$array = $v['low_leve'];
				continue;
			}
	}//找到acl.inc中对当前模块的定义的数组
	
	$num = count($keys);
	$num_last = $num - 1;
	$this_array_0 = &$array;
	$last_key = $keys[$num_last];
	
	for ($i = 0; $i < $num_last; $i++){
		$this_key = $keys[$i];
		$this_var_name = 'this_array_' . $i;
		$next_var_name = 'this_array_' . ($i + 1);        
		if (!array_key_exists($this_key, $$this_var_name)) {            
			break;       
		}        
		$$next_var_name = &${$this_var_name}[$this_key];    
	}    
	/*取得条件下的数组  ${$next_var_name}得到data数组 $last_key即$keys = array($model,'data','eqaction_'.$action);里面的'eqaction_'.$action,所以总的组成就是，在acl.inc数组里找到键为$model的数组里的键为data的数组里的键为'eqaction_'.$action的值;*/
	$actions = ${$next_var_name}[$last_key];//这个值即为当前action的别名,然后用别名与用户的权限比对,如果是带有参数的条件则$actions是数组，数组里有相关的参数限制
	if(is_array($actions)){
		foreach($actions as $key_s => $v_s){
			$ma = true;
			if(isset($v_s['POST'])){
				foreach($v_s['POST'] as $pkey => $pv){
					switch($pv){
						case 'G_EMPTY';//必须为空
							if( isset($_POST[$pkey]) && !empty($_POST[$pkey]) ) $ma = false;
						break;
					
						case 'G_NOTSET';//不能设置
							if( isset($_POST[$pkey]) ) $ma = false;
						break;
					
						case 'G_ISSET';//必须设置
							if( !isset($_POST[$pkey]) ) $ma = false;
						break;
					
						default;//默认
							if( !isset($_POST[$pkey]) || strtolower($_POST[$pkey]) != strtolower($pv) ) $ma = false;
						break;
					}
				}
			}
			
			if(isset($v_s['GET'])){
				foreach($v_s['GET'] as $pkey => $pv){
					switch($pv){
						case 'G_EMPTY';//必须为空
							if( isset($_GET[$pkey]) && !empty($_GET[$pkey]) ) $ma = false;
						break;
					
						case 'G_NOTSET';//不能设置
							if( isset($_GET[$pkey]) ) $ma = false;
						break;
					
						case 'G_ISSET';//必须设置
							if( !isset($_GET[$pkey]) ) $ma = false;
						break;
					
						default;//默认
							if( !isset($_GET[$pkey]) || strtolower($_GET[$pkey]) != strtolower($pv) ) $ma = false;
						break;
					}
					
				}
			}
			if($ma)	return $key_s;
			else $actions="0";
		}//foreach
	}else{
		return $actions;
	}
}
//////////////////////////////////// 第三方支付--移动支付专用 开始 fan 2014-06-07 ////////////////////////////
//* 移动支付使用该方法
//获取客户端ip地址
//注意:如果你想要把ip记录到服务器上,请在写库时先检查一下ip的数据是否安全.
//*
function getIp() {
        if (getenv('HTTP_CLIENT_IP')) {
				$ip = getenv('HTTP_CLIENT_IP'); 
		}
		elseif (getenv('HTTP_X_FORWARDED_FOR')) { //获取客户端用代理服务器访问时的真实ip 地址
				$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_X_FORWARDED')) { 
				$ip = getenv('HTTP_X_FORWARDED');
		}
		elseif (getenv('HTTP_FORWARDED_FOR')) {
				$ip = getenv('HTTP_FORWARDED_FOR'); 
		}
		elseif (getenv('HTTP_FORWARDED')) {
				$ip = getenv('HTTP_FORWARDED');
		}
		else if(!empty($_SERVER["REMOTE_ADDR"])){
				$cip = $_SERVER["REMOTE_ADDR"];  
		}else{
				$cip = "unknown";  
		}
		return $ip;
}

	//移动支付MD5方式签名
	  function MD5sign($okey,$odata){
	  		$signdata=hmac("",$odata);			     
	  		return hmac($okey,$signdata);
	  }
	  
	  function hmac ($key, $data){
		  $key = iconv('gb2312', 'utf-8', $key);
		  $data = iconv('gb2312', 'utf-8', $data);
		  $b = 64;
		  if (strlen($key) > $b) {
		  		$key = pack("H*",md5($key));
		  }
		  $key = str_pad($key, $b, chr(0x00));
		  $ipad = str_pad('', $b, chr(0x36));
		  $opad = str_pad('', $b, chr(0x5c));
		  $k_ipad = $key ^ $ipad ;
		  $k_opad = $key ^ $opad;
		  return md5($k_opad . pack("H*",md5($k_ipad . $data)));
      } 
//////////////////////////////////// 第三方支付--移动支付专用 结束 fan 2014-06-07 ////////////////////////////	 

function popusers(){
// fang@14/11/12
//投资排行榜
$affect_money20 = M('member_moneylog l' )
				->field("sum(l.affect_money) as affect,m.user_name ")
				->join("lzh_members m ON l.uid = m.id ")
				->where("l.type = 15 or l.type = 39 ")
				->group("l.uid")
				->order("affect DESC")
				->limit("20")
				->select();
$html = '<ul class="popbox">';
foreach($affect_money20 as $i=>$rows){
	$html .= '<li><span class="ln">'. ($i+1) .'</span><span class="user">'. hidecard($rows['user_name'] ,5).'</span><span class="money">'. $rows['affect']. '</span></li>';
}
return $html . '</ul>';
}


function popusers1(){
// fang@14/11/12
//日投资排行榜
	$morning = strtotime(date('Y-m-d'));//获取当天凌晨时间
	
	$Mmorning = strtotime(date('Y-m-d',strtotime('+1 day')));
$affect_money8 = M('member_moneylog l' )
					->field("abs(sum(l.affect_money))as affect ,m.user_name,l.add_time ")
					->join("lzh_members m ON l.uid = m.id ")
					->where("(l.type = 6 or l.type = 39) and (l.add_time < {$Mmorning} and l.add_time > {$morning})")
					->group("l.uid")
					->order("affect DESC")
					->limit("20")
					->select();
$html = '<ul class="popbox">';
foreach($affect_money8 as $i=>$rows){
	$html .= '<li><span class="ln">'. ($i+1) .'</span><span class="user">'. hidecard($rows['user_name'] ,5).'</span><span class="money">'. $rows['affect']. '</span></li>';
}
return $html . '</ul>';
}

function popusers2(){
// fang@14/11/12
//投资排行榜
	$MonthA = strtotime(date("Y-m-1"));
	
	$thirty = strtotime(date("Y-m-30"));

	$affect_money9 = M('member_moneylog l' )
						->field("abs(sum(l.affect_money))as affect ,m.user_name,l.add_time ")
						->join("lzh_members m ON l.uid = m.id ")
						->where("(l.type = 6 or l.type = 39) and (l.add_time < {$thirty} and l.add_time > {$MonthA})")
						->group("l.uid")
						->order("affect DESC")
						->limit("20")
						->select();
		
$html = '<ul class="popbox">';
foreach($affect_money9 as $i=>$rows){
	$html .= '<li><span class="ln">'. ($i+1) .'</span><span class="user">'. hidecard($rows['user_name'] ,5).'</span><span class="money">'. $rows['affect']. '</span></li>';
}
return $html . '</ul>';
}

//手机专用
function getleixing($map){
	
	if($map['borrow_type']==2) $str=4;//担保标
	elseif($map['borrow_type']==3) $str=5;//秒还标
	elseif($map['borrow_type']==4) $str=6;//净值标
	elseif($map['borrow_type']==1) $str=3;//信用标
	elseif($map['borrow_type']==5) $str=7;//抵押标
	return $str;
} 

//友情链接
function get_friends()
{
	 $list = M('friend')->where(" is_show = 1")->order("link_order DESC")->select();
	
	 return $list;
}
//获取借款列表
function getMemberDetail($uid){
	$pre = C('DB_PREFIX');
	$map['m.id'] = $uid;
	//$field = "*";
	$list = M('members m')->field(true)->join("{$pre}member_banks mbank ON m.id=mbank.uid")->join("{$pre}member_contact_info mci ON m.id=mci.uid")->join("{$pre}member_house_info mhi ON m.id=mhi.uid")->join("{$pre}member_department_info mdpi ON m.id=mdpi.uid")->join("{$pre}member_ensure_info mei ON m.id=mei.uid")->join("{$pre}member_info mi ON m.id=mi.uid")->join("{$pre}member_financial_info mfi ON m.id=mfi.uid")->where($map)->limit($Lsql)->find();
	return $list;
}

/**

	// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
	{
		return true;
	}
	// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
	if (isset ($_SERVER['HTTP_VIA']))
	{
		// 找不到为flase,否则为true
		return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
	}
	// 脑残法，判断手机发送的客户端标志,兼容性有待提高
	if (isset ($_SERVER['HTTP_USER_AGENT']))
	{
		$clientkeywords = array ('nokia',
			'sony',
			'ericsson',
			'mot',
			'samsung',
			'htc',
			'sgh',
			'lg',
			'sharp',
			'sie-',
			'philips',
			'panasonic',
			'alcatel',
			'lenovo',
			'iphone',
			'ipod',
			'blackberry',
			'meizu',
			'android',
			'netfront',
			'symbian',
			'ucweb',
			'windowsce',
			'palm',
			'operamini',
			'operamobi',
			'openwave',
			'nexusone',
			'cldc',
			'midp',
			'wap',
			'mobile'
		);
		// 从HTTP_USER_AGENT中查找手机浏览器的关键字
		if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
		{
			return true;
		}
	}
	// 协议法，因为有可能不准确，放到最后判断
	if (isset ($_SERVER['HTTP_ACCEPT']))
	{
		// 如果只支持wml并且不支持html那一定是移动设备
		// 如果支持wml和html但是wml在html之前则是移动设备
		if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
		{
			return true;
		}
	}
	return false;
 * **/


?>