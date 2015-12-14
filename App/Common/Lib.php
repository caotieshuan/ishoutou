<?php
//对提交的参数进行过滤
function EnHtml($v){
	return $v;
}
function mydate($format,$time,$default=''){
	if(intval($time)>10000) return date($format,$time);	
	else return $default;
}
function textPost($data){
	if(is_array($data)){
		foreach($data as $key => $v){
			$x[$key]=text($v);
		}
	}
	return $x;
}



function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=false){
	if(function_exists("mb_substr")){
		if($suffix)
			return mb_substr($str, $start, $length, $charset)."...";
		else
			return mb_substr($str, $start, $length, $charset);
	}elseif(function_exists('iconv_substr')) {
		if($suffix)
			return iconv_substr($str,$start,$length,$charset)."...";
		else
			return iconv_substr($str,$start,$length,$charset);
	}
	$re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
	$re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
	$re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
	$re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
	preg_match_all($re[$charset], $str, $match);
	$slice = join("",array_slice($match[0], $start, $length));
	if($suffix) return $slice."…";
	return $slice;
}
/*$url：要生成的地址,$vars:参数数组,$domain：是否带域名*/
function MU($url,$type,$vars=array(),$domain=false){
	//获得基础地址START
	$path = explode("/",trim($url,"/"));
	$model = strtolower($path[1]);
	$action = isset($path[2])?strtolower($path[2]):"";
	//获得基础地址START
	//获取前缀根目录及分组
	$http = UD($path,$domain);
	//获取前缀根目录及分组
	switch($type){
		case "article":
		default:
			if(!isset($vars['id'])){//特殊栏目,用nid来区分,不用ID
				unset($path[0]);//去掉分组名
				$url = implode("/",$path)."/";
				$newurl=$url;
			}else{//普通栏目,带ID
				if(1==1||strtolower(GROUP_NAME) == strtolower(C('DEFAULT_GROUP'))) {//如果是默认分组则去掉分组名
					unset($path[0]);//去掉分组名
					$url = implode("/",$path)."/";
				}
				$newurl=$url.$vars['id'].$vars['suffix'];
			}
		break;
		case "typelist":
				if(1==1||strtolower(GROUP_NAME) == strtolower(C('DEFAULT_GROUP'))) {//如果是默认分组则去掉分组名
					unset($path[0]);//去掉分组名
					$url = implode("/",$path);
				}
				$newurl=$url.$vars['suffix'];
		break;
	}
	
	return $http.$newurl;
	
}
// URL组装 支持不同模式
// 格式：UD('url参数array('分组','model','action')','显示域名')在传入的url数组中，只用到分组
function UD($url=array(),$domain = false) {
    // 解析URL
	$isDomainGroup = true;//当值为true时,不对任何链接加分组前缀,当为false时,自动判断分组及域名等,加前缀
	$isDomainD = false;
	$asdd = C('APP_SUB_DOMAIN_DEPLOY');
	//###########修复START#############，增加对当前分组分配了二级域名的判断,变量给下面用
	if($asdd){
		foreach (C('APP_SUB_DOMAIN_RULES') as $keyr => $ruler) {
			if(strtolower($url[0]."/") == strtolower($ruler[0])){
				$isDomainGroup = true;//分组分配了二级域名
				$isDomainD = true;
				break;
			}
		}
	}

	//#########及默认分组不需要加分组名 都转换成小写来比较，避免在linux上出问题
	if(strtolower(GROUP_NAME) == strtolower(C('DEFAULT_GROUP'))) $isDomainGroup = true;
	//###########修复END#############，增加对当前分组分配了二级域名的判断
    // 解析子域名
    if($domain===true){
        $domain = $_SERVER['HTTP_HOST'];
        if($asdd) { // 开启子域名部署
			//###########修复START#############，增加对没带前缀域名的判断
			$xdomain = explode(".",$_SERVER['HTTP_HOST']);
			if(!isset($xdomain[2])) $ydomain="www.".$_SERVER['HTTP_HOST'];
			else  $ydomain=$_SERVER['HTTP_HOST'];
			//###########修复END#############，增加对没带前缀域名的判断
            $domain = $domain=='localhost'?'localhost':'www'.strstr($ydomain,'.');
            // '子域名'=>array('项目[/分组]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                if(false === strpos($key,'*') && $isDomainD) {
                    $domain = $key.strstr($domain,'.'); // 生成对应子域名
                    $url   =  substr_replace($url,'',0,strlen($rule[0]));
                    break;
                }
            }
        }
    }
	
	if(!$isDomainGroup) $gpurl = __APP__."/".$url[0]."/";
	else $gpurl = __APP__."/";

    if($domain) {
        $url   =  'http://'.$domain.$gpurl;
    }else{
        $url   =  $gpurl;
	}

	return $url;
}

function Mheader($type){
	header("Content-Type:text/html;charset={$type}"); 
}

// 自动转换字符集 支持数组转换
function auto_charset($fContents, $from='gbk', $to='utf-8') {
    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    if ( ($to=='utf-8'&&is_utf8($fContents)) || strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = auto_charset($key, $from, $to);
            $fContents[$_key] = auto_charset($val, $from, $to);
            if ($key != $_key)
                unset($fContents[$key]);
        }
        return $fContents;
    }
    else {
        return $fContents;
    }
}
//判断是否utf8
function is_utf8($string) {
	return preg_match('%^(?:
		 [\x09\x0A\x0D\x20-\x7E]            # ASCII
	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $string);
}

//获取日期
/*			case "yesterday";
				$date = date("Y-m-d",$now_time);//d,w,m分别表示天，周，月,后面的第三个参数选填，正数1表示后一天(d)的00:00:00到23:59:59负数表示前一天(d),-2表示前面第二天的00:00:00到23:59:59
				$day = get_date($date,'d',-1);//第三个参数表示时间段包含的天数
			break;
*/
function get_date($date,$t='d',$n=0){
	if($t=='d'){
		$firstday = date('Y-m-d 00:00:00',strtotime("$n day"));
		$lastday = date("Y-m-d 23:59:59",strtotime("$n day"));
	}elseif($t=='w'){
		if($n!=0){$date = date('Y-m-d',strtotime("$n week"));}
		$lastday = date("Y-m-d 00:00:00",strtotime("$date Sunday"));
		$firstday = date("Y-m-d 23:59:59",strtotime("$lastday -6 days"));
	}elseif($t=='m'){
		if($n!=0){
			if(date("m",time())==1) $date = date('Y-m-d',strtotime("$n months -1 day"));//2特殊的2月份
			else $date = date('Y-m-d',strtotime("$n months"));
		}
		
		$firstday = date("Y-m-01 00:00:00",strtotime($date));
		$lastday = date("Y-m-d 23:59:59",strtotime("$firstday +1 month -1 day"));
	}
	return array($firstday,$lastday);

}

/**
 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
 
 
function rand_string($ukey="",$len=6,$type='1',$utype='1',$addChars='') {
    $str ='';
    switch($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if($len>10 ) {//位数过长重复字符串一定次数
        $chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
    }
    $chars   =   str_shuffle($chars);
    $str     =   substr($chars,0,$len);
	if(!empty($ukey)){
		$vd['code'] = $str;
		$vd['send_time'] = time();
		$vd['ukey'] = $ukey;
		$vd['type'] = $utype;
		M('verify')->add($vd);
	}
    return $str;
}

//验证是否通过
function is_verify($uid,$code,$utype,$timespan){
	if(!empty($uid)) $vd['ukey'] = $uid;
	$vd['type'] = $utype;
	$vd['send_time'] = array("lt",time()+$timespan);
	$vd['code'] = $code;
	$vo = M("verify")->field('ukey')->where($vd)->find();
	if(is_array($vo)) return $vo['ukey'];
	else return false;
}
//网站基本设置
function get_global_setting(){
	$list=array();
	if(!S('global_setting')){
		$list_t = M('global')->field('code,text')->select();
		foreach($list_t as $key => $v){
			$list[$v['code']] = de_xie($v['text']);
		}
		S('global_setting',$list);
		S('global_setting',$list,3600*C('TTXF_TMP_HOUR')); 
	}else{
		$list = S('global_setting');
	}
	
	return $list;
}
//acl权限管理
/*
print_r(acl_get_key(array('global','data','eqaction_edit'),$acl_inc));
*/


//获取用户权限数组
function get_user_acl($uid=""){
	$model=strtolower(MODULE_NAME);

	if(empty($uid)) return false;
	$gid = M('ausers')->field('u_group_id')->find($uid);
	
	$al = get_group_data($gid['u_group_id']);
	
	$acl = $al['controller'];
	$acl_key = acl_get_key();
	
	if( array_keys($acl[$model],$acl_key) ) return true;
	else return false;
}

//获取权限列表
function get_group_data($gid=0){
	$gid=intval($gid);
	$list=array();

	if($gid==0){
		if( !S("ACL_all") ){
			$_acl_data = M('acl')->select();
			$acl_data=array();
			
			foreach($_acl_data as $key => $v){
				$acl_data[$v['group_id']] = $v;
				$acl_data[$v['group_id']]['controller'] = unserialize($v['controller']);
			}
			
			S("ACL_all",$acl_data,C('ADMIN_CACHE_TIME')); 
			$list = $acl_data;
		}else{
			$list = S("ACL_all");
		}
	}else{
		if( !S("ACL_".$gid) ){
			$_acl_data = M('acl')->find($gid);
			$_acl_data['controller'] = unserialize($_acl_data['controller']);
			$acl_data = $_acl_data;
			S("ACL_".$gid,$acl_data,C('ADMIN_CACHE_TIME')); 
			$list = $acl_data;
		}else{
			$list = S("ACL_".$gid);
		}
	}
	return $list;
}
//删除文件夹并重建文件夹
function rmdirr($dirname) {

	if (!file_exists($dirname)) {
		return false;
	}

	if (is_file($dirname) || is_link($dirname)) {
		return unlink($dirname);
	}

	$dir = dir($dirname);
	if($dir) {
		while (false !== $entry = $dir->read()) {

			if ($entry == '.' || $entry == '..') {
				continue;
			}

			rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
		}

		$dir->close();
	}

	return rmdir($dirname);
}
//删除文件夹及文件夹下所有内容
function Rmall($dirname) {
	if (!file_exists($dirname)) {
		return false;
	}
	if (is_file($dirname) || is_link($dirname)) {
		return unlink($dirname);
	}

	$dir = dir($dirname);//如果对像是目录

	while (false !== $file = $dir->read()) {

		if ($file == '.' || $file == '..') {
			continue;
		}
		if(!is_dir($dirname."/".$file)){
			unlink($dirname."/".$file);
		}else{
			Rmall($dirname."/".$file);
		}
		
		rmdir($dirname."/".$file);
	}

	$dir->close();
	
	rmdir($dirname);

	return true;
}

//取得文件内容
function ReadFiletext($filepath){
	$htmlfp=@fopen($filepath,"r");
	while($data=@fread($htmlfp,1000))
	{
		$string.=$data;
	}
	@fclose($htmlfp);
	return $string;
}

//生成文件
function MakeFile($con,$filename){//$filename是全物理路径加文件名
	MakeDir(dirname($filename));
	$fp=fopen($filename,"w");
	fwrite($fp,$con);
	fclose($fp);
}

//生成全路径文件夹
function MakeDir($dir){
	return is_dir($dir) or (MakeDir(dirname($dir)) and mkdir($dir,0777));
}

//友情链接
function get_home_friend($type,$datas = array()){
	$condition['is_show']=array('eq',1);
	
	$condition['link_type']=array('eq',$type);
	$type = "friend_home".$type;


	if(!S($type)){
		$_list = M('friend')->field('link_txt,link_href,link_img,link_type')->where($condition)->order("link_order DESC")->select();
		$list=array();
		foreach($_list as $key => $v){
			$list[$key] = $v;
		}
		S($type,$list,3600*C('HOME_CACHE_TIME')); 
	}else{
		$list = S($type);
	}
	
	return $list;
}

/*
栏目相关函数
Start
*/

//获取某栏目下的所有子栏目以nid-nid顺次链接
function get_type_leve($id="0"){
	$model = D('Acategory');
	if(!S("type_son_type")){
		$allid=array();
		$data = $model->field('id,type_nid')->where("parent_id = {$id}")->select();
		if(count($data)>0){
			foreach($data as $v){
				//二级
				$allid[$v['type_nid']]=$v['id'];
				$data_1=array();//清空,避免下面判断错误
				$data_1 = $model->field('id,type_nid')->where("parent_id = {$v['id']}")->select();
				if(count($data_1)>0){
					foreach($data_1 as $v1){
						//三级
						$allid[$v['type_nid']."-".$v1['type_nid']]=$v1['id'];
						$data_2=array();//清空,避免下面判断错误
						$data_2 = $model->field('id,type_nid')->where("parent_id = {$v1['id']}")->select();
						if(count($data_2)>0){
							foreach($data_2 as $v2){
								//四级
								$allid[$v['type_nid']."-".$v1['type_nid']."-".$v2['type_nid']]=$v2['id'];
								$data_3=array();//清空,避免下面判断错误
								$data_3 = $model->field('id,type_nid')->where("parent_id = {$v2['id']}")->select();
	
								if(count($data_3)>0){
									foreach($data_3 as $v3){
										$allid[$v['type_nid']."-".$v1['type_nid']."-".$v2['type_nid']."-".$v3['type_nid']]=$v3['id'];
									}
								}
								//四级
							}
						}
						//三级
					}
				}
				//二级
			}
	
		}
		S("type_son_type",$allid,3600*C('HOME_CACHE_TIME')); 
	}else{
		$allid = S("type_son_type");
	}
	
	return $allid;
}


//获取某栏目下的所有子栏目以nid-nid顺次链接
function get_area_type_leve($id="0",$area_id=0){

	$model = D('Aacategory');
	if(!S("type_son_type_area".$area_id)){
		$allid=array();
		$data = $model->field('id,type_nid')->where("parent_id = {$id} AND area_id={$area_id}")->select();
		if(count($data)>0){
			foreach($data as $v){
				//二级
				$allid[$area_id.$v['type_nid']]=$v['id'];
				$data_1=array();//清空,避免下面判断错误
				$data_1 = $model->field('id,type_nid')->where("parent_id = {$v['id']}")->select();
				if(count($data_1)>0){
					foreach($data_1 as $v1){
						//三级
						$allid[$area_id.$v['type_nid']."-".$v1['type_nid']]=$v1['id'];
						$data_2=array();//清空,避免下面判断错误
						$data_2 = $model->field('id,type_nid')->where("parent_id = {$v1['id']}")->select();
						if(count($data_2)>0){
							foreach($data_2 as $v2){
								//四级
								$allid[$area_id.$v['type_nid']."-".$v1['type_nid']."-".$v2['type_nid']]=$v2['id'];
								$data_3=array();//清空,避免下面判断错误
								$data_3 = $model->field('id,type_nid')->where("parent_id = {$v2['id']}")->select();
	
								if(count($data_3)>0){
									foreach($data_3 as $v3){
										$allid[$area_id.$v['type_nid']."-".$v1['type_nid']."-".$v2['type_nid']."-".$v3['type_nid']]=$v3['id'];
									}
								}
								//四级
							}
						}
						//三级
					}
				}
				//二级
			}
	
		}
		S("type_son_type_area".$area_id,$allid,3600*C('HOME_CACHE_TIME')); 
	}else{
		$allid = S("type_son_type_area".$area_id);
	}
	return $allid;
}

//获取某栏目的所有父栏目的type_nid,按由远到近的顺序出现在数组中1/2
function get_type_leve_nid($id="0"){
	if(empty($id)) return;
	global $allid;
	static $r=array();//先声明要返回静态变量,不然在下面被赋值时是引用赋值
	get_type_leve_nid_run($id);
	
	$r = $allid;
	$GLOBALS['allid'] = NULL;
	
	return array_reverse($r);
}
//获取某栏目的所有父栏目的type_nid,按由远到近的顺序出现在数组中2/2
function get_type_leve_nid_run($id="0"){
	global $allid;
	$data_parent = $data = "";
	$data = D('Acategory')->field('parent_id,type_nid')->find($id);
	$data_parent = D('Acategory')->field('id,type_nid')->where("id = {$data['parent_id']}")->find();
	if(isset($data_parent['type_nid'])>0){
		if(!isset($allid[0])) $allid[]=$data['type_nid'];
		$allid[]=$data_parent['type_nid'];
		get_type_leve_nid_run($data_parent['id']);
	}else{
		if(!isset($allid[0])) $allid[]=$data['type_nid'];
	}
}


//获取某栏目的所有父栏目的type_nid,按由远到近的顺序出现在数组中1/2
function get_type_leve_area_nid($id="0",$area_id=0){
	if(empty($id)||empty($area_id)) return;
	global $allid_area;
	static $r=array();//先声明要返回静态变量,不然在下面被赋值时是引用赋值

	get_type_leve_area_nid_run($id);
	
	$r = $allid_area;
	$GLOBALS['allid_area'] = NULL;
	
	return array_reverse($r);
}
//获取某栏目的所有父栏目的type_nid,按由远到近的顺序出现在数组中2/2
function get_type_leve_area_nid_run($id="0"){
	global $allid_area;
	$data_parent = $data = "";
	$data = D('Aacategory')->field('parent_id,type_nid,area_id')->find($id);
	$data_parent = D('Aacategory')->field('id,type_nid,area_id')->where("id = {$data['parent_id']}")->find();
	if(isset($data_parent['type_nid'])>0){
		if(!isset($allid_area[0])) $allid_area[]=$data['type_nid'];
		$allid_area[]=$data_parent['type_nid'];
		get_type_leve_area_nid_run($data_parent['id']);
	}else{
		if(!isset($allid_area[0])) $allid_area[]=$data['type_nid'];
	}
}

//获取某栏目下的所有子栏目,查询次数较少，查询效率更高,入口函数1/2
function get_son_type($id){
	$tempname = "type_sfs_son_all".$id;
	if(!S($tempname)){
		$row = get_son_type_run($id);
		S($tempname,$row,3600*C('HOME_CACHE_TIME')); 
	}else{
		$row = S($tempname);
	}
	return $row;
}

//获取某栏目下的所有子栏目,查询次数较少，查询效率更高2/2
function get_son_type_run($id){
	static $rerow;
	global $allid;
	$data = M('type')->field('id')->where("parent_id in ({$id})")->select();
	if(count($data)>0){
		foreach($data as $key=>$v){
			$allid[]=$v['id'];
			$nowid[]=$v['id'];
		}
		$id = implode(",",$nowid);
		get_son_type_run($id);
	}
//递归函数不要加else来返回内容，不然得不到返回值
//	else{
//		return $allid;
//	}
	$rerow = $allid;
	$allid=array();
	return $rerow;
}

//获取某栏目下所有的子栏目,以数组的形式返回,入口函数1/2
function get_type_son($id=0){
	$tempname = "type_son_all".$id;
	if(!S($tempname)){
		$row = get_type_son_all($id);
		S($tempname,$row,3600*C('HOME_CACHE_TIME')); 
	}else{
		$row = S($tempname);
	}
	return $row;
}
//获取某栏目下所有的子栏目2/2
function get_type_son_all($id="0"){
	static $rerow;
	global $get_type_son_all_row;
	
	if(empty($id)) exit;
	
	$data = M('type')->where("parent_id = {$id}")->field('id')->select();
	foreach($data as $key=>$v){
		$get_type_son_all_row[]=$v['id'];
		$data_son = M('type')->where("parent_id = {$v['id']}")->field('id')->select();
		if(count($data_son)>0){
			get_type_son_all($v['id']);
		}
	}
	
	$rerow = $get_type_son_all_row;
	$get_type_son_all_row = array();
	return $rerow;
}
//获取所有栏目每个栏目的父栏目的nid,以栏目ID为键名
function get_type_parent_nid(){
	$row=array();
	$p_nid_new=array();
	if(!S("type_parent_nid_temp")){
		$data = M('type')->field('id')->select();
		if(count($data)>0){
			foreach($data as $key => $v){
				$p_nid = get_type_leve_nid($v['id']);
				$i=$n=count($p_nid);
				//倒序处理
				if($i>1){
					for($j=0;$j<$n;$j++,$i--){
						$p_nid_new[($i-1)]=$p_nid[$j];
					}
				}else{
					$p_nid_new = $p_nid;
				}
				//倒序处理
				$row[$v['id']] = $p_nid_new;
			}
		}
		S("type_parent_nid_temp",$row,3600*C('HOME_CACHE_TIME')); 
	}else{
		$row = S("type_parent_nid_temp");
	}
	
	return $row;
}

//获取以栏目ID为键的所有栏目数组,二维,如果field只有两个，并且其中一个是id，那么就会自动成为一维数组
function get_type_list($model,$field=true){
	$acaheName=md5("type_list_temp".$model.$field);
	if(!S($acaheName)){
		$list = D(ucfirst($model))->getField($field);
		S($acaheName,$list,3600*C('HOME_CACHE_TIME')); 
	}else{
		$list = S($acaheName);
	}
	return $list;
}

//通过网址获取栏目相关信息
function get_type_infos(){
	$row=array();
	$type_list = get_type_list('acategory','id,type_nid,type_set');
	if(!isset($_GET['typeid'])){
		$type_nid = get_type_leve();//获得所有栏目自己的nid的组合 
		$rurl = explode("?",$_SERVER['REQUEST_URI']); 
		$xurl_tmp = explode("/",str_replace(array("index.html",".html"),array('',''),$rurl[0]));//获得组合的type_nid
		$zu = implode("-",array_filter($xurl_tmp));//组合
		//print_r($type_nid);
		$typeid = $type_nid[$zu];
		$typeset = $type_list[$typeid]['type_set'];
	}else{
		$typeid = intval($_GET['typeid']);
		$typeset = $type_list[$typeid]['type_set'];
	}

	if($typeset==1){//列表
		$templet = "list_index";
	}else{//单页
		$templet = "index_index";
	}
	
	$row['typeset'] = $typeset;
	$row['templet'] = $templet;
	$row['typeid'] = $typeid;
	
	return $row;
}

//通过网址获取栏目相关信息
function get_area_type_infos($area_id=0){
	$row=array();
	$type_list = get_type_list('aacategory','id,type_nid,type_set,area_id');
	if(!isset($_GET['typeid'])){

		$type_nid = get_area_type_leve(0,$area_id);//获得所有栏目自己的nid的组合 
		$rurl = explode("?",$_SERVER['REQUEST_URI']); 
		$xurl_tmp = explode("/",str_replace(array("index.html",".html"),array('',''),$rurl[0]));//获得组合的type_nid
		$zu = implode("-",array_filter($xurl_tmp));//组合
		//print_r($type_nid);
		$typeid = $type_nid[$area_id.$zu];
		$typeset = $type_list[$typeid]['type_set'];
	}else{
		$typeid = intval($_GET['typeid']);
		$typeset = $type_list[$typeid]['type_set'];
	}

	if($typeset==1){//列表
		$templet = "list_index";
	}else{//单页
		$templet = "index_index";
	}
	
	$row['typeset'] = $typeset;
	$row['templet'] = $templet;
	$row['typeid'] = $typeid;
	
	return $row;
}

//获取栏目列表,按栏目分级,有缩进,入口函数1/2
function get_type_leve_list($id=0,$modelname=false, $type){
	static $rerow;
	global $get_type_leve_list_run_row;


	if(!$modelname) $model = D("type");
	else $model=D(ucfirst($modelname));
	$stype = $modelname."home_type_leve_list".$id;
	if(!S($stype)){
		get_type_leve_list_run($id,$model, $type);
		$rerow = $get_type_leve_list_run_row;//把全局变量赋值给静态变量，避免引用清空
		$GLOBALS['get_type_leve_list_run_row']=NULL;//清空全局变量避免影响其他数据,不能用unset,unset只能清空单个变量或者数组中的某一元素,并且unset只能清空局部变量，清空全局变量要用unset($GLOBALS
		$data = $rerow;
		//S($stype,$data,3600*C('HOME_CACHE_TIME')); 
	}else{
		$data = S($stype);
	}
	return $data;
}

//获取栏目列表,按栏目分级,有缩进2/2
function get_type_leve_list_run($id=0,$model, $type){
	global $get_type_leve_list_run_row;
	//全局变量的定义都要放在最前面
	$spa = "----";
	if(count($get_type_leve_list_run_row)<1) $get_type_leve_list_run_row=array();

	$typelist = $model->where("parent_id={$id} and model='{$type}'")->field('type_name,id,parent_id')->order('sort_order DESC')->select();//上级栏目

	foreach($typelist as $k=>$v){
		$leve = intval(get_typeLeve($v['id'],$model));
		$v['type_name'] = str_repeat($spa,$leve).$v['type_name'];
		$get_type_leve_list_run_row[]=$v;
		
		$typelist_s1 = $model->where("parent_id={$v['id']} and model='{$type}'")->field('type_name,id')->select();//上级栏目
		if(count($typelist_s1)>0){
			get_type_leve_list_run($v['id'],$model, $type);
		}
	}
}//id


//获取栏目列表地区性的,按栏目分级,有缩进,入口函数1/2
function get_type_leve_list_area($id=0,$modelname=false,$area_id=0){
	static $rerow;
	global $get_type_leve_list_area_run_row;


	if(!$modelname) $model = D("type");
	else $model=D(ucfirst($modelname));
	$stype = $modelname."home_type_leve_list_area".$id.$area_id;
	if(!S($stype)){
		get_type_leve_list_area_run($id,$model,$area_id);
		$rerow = $get_type_leve_list_area_run_row;//把全局变量赋值给静态变量，避免引用清空
		$GLOBALS['get_type_leve_list_area_run_row']=NULL;//清空全局变量避免影响其他数据,不能用unset,unset只能清空单个变量或者数组中的某一元素,并且unset只能清空局部变量，清空全局变量要用unset($GLOBALS
		$data = $rerow;
		S($stype,$data,3600*C('HOME_CACHE_TIME')); 
	}else{
		$data = S($stype);
	}
	return $data;
}

//获取栏目列表,按栏目分级,有缩进2/2
function get_type_leve_list_area_run($id=0,$model,$area_id){
	global $get_type_leve_list_area_run_row;
	//全局变量的定义都要放在最前面
	$spa = "----";
	if(count($get_type_leve_list_area_run_row)<1) $get_type_leve_list_area_run_row=array();

	$typelist = $model->where("parent_id={$id} AND area_id={$area_id}")->field('type_name,id,parent_id')->order('sort_order DESC')->select();//上级栏目

	foreach($typelist as $k=>$v){
		$leve = intval(get_typeLeve($v['id'],$model));
		$v['type_name'] = str_repeat($spa,$leve).$v['type_name'];
		$get_type_leve_list_area_run_row[]=$v;
		
		$typelist_s1 = $model->where("parent_id={$v['id']}")->field('type_name,id')->select();//上级栏目
		if(count($typelist_s1)>0){
			get_type_leve_list_area_run($v['id'],$model,$area_id);
		}
	}
}//id


//获取栏目的级别1/2
function get_typeLeve($typeid,$model){
	$typeleve = 0;
	global $typeleve;
	static $rt=0;//先声明要返回静态变量,不然在下面被赋值时是引用赋值
	get_typeLeve_run($typeid,$model);
	$rt = $typeleve;
	unset($GLOBALS['typeleve']);
	return $rt;
}
//获取栏目的级别2/2
function get_typeLeve_run($typeid,$model){
	global $typeleve;
	$condition['id'] = $typeid;
	$v = $model->field('parent_id')->where($condition)->find();
	if($v['parent_id']>0){
		$typeleve++;
		get_typeLeve_run($v['parent_id'],$model);
	}
}

/*
栏目相关函数
End
*/
//在前台显示时去掉反斜线,传入数组，最多二维
function de_xie($arr){
	$data=array();
	if(is_array($arr)){
		foreach($arr as $key=>$v){
			if(is_array($v)){
				foreach($v as $skey=>$sv){
					if(is_array($sv)){
							
					}else{
						$v[$skey] = stripslashes($sv);
					}
				}
				$data[$key] = $v;
			}else{
				$data[$key] = stripslashes($v);
			}
		}
	}else{
		$data = stripslashes($arr);
	}
	return $data;
}


//输出纯文本
function text($text,$parseBr=false,$nr=false){
    $text = htmlspecialchars_decode($text);
    $text	=	safe($text,'text');
    if(!$parseBr&&$nr){
        $text	=	str_ireplace(array("\r","\n","\t","&nbsp;"),'',$text);
        $text	=	htmlspecialchars($text,ENT_QUOTES);
    }elseif(!$nr){
        $text	=	htmlspecialchars($text,ENT_QUOTES);
	}else{
        $text	=	htmlspecialchars($text,ENT_QUOTES);
        $text	=	nl2br($text);
    }
    $text	=	trim($text);
    return $text;
}
function safe($text,$type='html',$tagsMethod=true,$attrMethod=true,$xssAuto = 1,$tags=array(),$attr=array(),$tagsBlack=array(),$attrBlack=array()){

    //无标签格式
    $text_tags	=	'';

    //只存在字体样式
    $font_tags	=	'<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';

    //标题摘要基本格式
    $base_tags	=	$font_tags.'<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';

    //兼容Form格式
    $form_tags	=	$base_tags.'<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';

    //内容等允许HTML的格式
    $html_tags	=	$base_tags.'<ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed>';

    //专题等全HTML格式
    $all_tags	=	$form_tags.$html_tags.'<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';

    //过滤标签
    $text	=	strip_tags($text, ${$type.'_tags'} );

        //过滤攻击代码
        if($type!='all'){
            //过滤危险的属性，如：过滤on事件lang js
            while(preg_match('/(<[^><]+) (onclick|onload|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i',$text,$mat)){
                $text	=	str_ireplace($mat[0],$mat[1].$mat[3],$text);
            }
            while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
                $text	=	str_ireplace($mat[0],$mat[1].$mat[3],$text);
            }
        }
        return $text;
}


//输出安全的html
function h($text, $tags = null){
	$text	=	trim($text);
	$text	=	preg_replace('/<!--?.*-->/','',$text);
	//完全过滤注释
	$text	=	preg_replace('/<!--?.*-->/','',$text);
	//完全过滤动态代码
	$text	=	preg_replace('/<\?|\?'.'>/','',$text);
	//完全过滤js
	$text	=	preg_replace('/<script?.*\/script>/','',$text);

	$text	=	str_replace('[','&#091;',$text);
	$text	=	str_replace(']','&#093;',$text);
	$text	=	str_replace('|','&#124;',$text);
	//过滤换行符
	$text	=	preg_replace('/\r?\n/','',$text);
	//br
	$text	=	preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
	$text	=	preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
	//过滤危险的属性，如：过滤on事件lang js
	while(preg_match('/(<[^><]+) (lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1],$text);
	}
	while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].$mat[3],$text);
	}
	if(empty($tags)) {
		$tags = 'table|tbody|td|th|tr|i|b|u|strong|img|p|br|div|span|em|ul|ol|li|dl|dd|dt|a|alt|h[1-9]?';
		$tags.= '|object|param|embed';	// 音乐和视频
	}
	//允许的HTML标签
	$text	=	preg_replace('/<(\/?(?:'.$tags.'))( [^><\[\]]*)?>/i','[\1\2]',$text);
	//过滤多余html
	$text	=	preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|style|xml)[^><]*>/i','',$text);
	//过滤合法的html标签
	while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
	}
	//转换引号
	while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
		$text = str_replace($mat[0], $mat[1] . '|' . $mat[3] . '|' . $mat[4],$text);
	}
	//过滤错误的单个引号
	// 修改:2011.05.26 kissy编辑器中表情等会包含空引号, 简单的过滤会导致错误
//	while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
//		$text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
//	}
	//转换其它所有不合法的 < >
	$text	=	str_replace('<','&lt;',$text);
	$text	=	str_replace('>','&gt;',$text);
    $text   =   str_replace('"','&quot;',$text);
    //$text   =   str_replace('\'','&#039;',$text);
	 //反转换
	$text	=	str_replace('[','<',$text);
	$text	=	str_replace(']','>',$text);
	$text	=	str_replace('|','"',$text);
	//过滤多余空格
	$text	=	str_replace('  ',' ',$text);
	return $text;
}
//根据原图片地址得到缩略图地址
function get_thumb_pic($str){
	$path = explode("/",$str);
	$sc = count($path);
	$path[($sc-1)] = "thumb_".$path[($sc-1)];
	return implode("/",$path);
}
//得到分类kvtable里的分类,以id为键
function get_kvtable($nid=""){
	$stype = "kvtable".$nid;
	$list = array();
	if(!S($stype)){
		if(!empty($nid))$tmplist = M('kvtable')->where("nid='{$nid}'")->field(true)->select();
		else $tmplist = M('rule')->field(true)->select();
		foreach($tmplist as $v){
			$list[$v['id']]=$v;
		}
		S($stype,$list,3600*C('HOME_CACHE_TIME')); 
		$row = $list;
	}else{
		$list = S($stype); 
		$row = $list;
	}
	
	return $row;
}
/*
* 中文截取，支持gb2312,gbk,utf-8,big5 
*
* @param string $str 要截取的字串
* @param int $start 截取起始位置
* @param int $length 截取长度
* @param string $charset utf-8|gb2312|gbk|big5 编码
* @param $suffix 是否加尾缀
*/
function cnsubstr($str, $length, $start=0, $charset="utf-8", $suffix=true)
{
	   $str = strip_tags($str);
	   if(function_exists("mb_substr"))
	   {
			   if(mb_strlen($str, $charset) <= $length) return $str;
			   $slice = mb_substr($str, $start, $length, $charset);
	   }
	   else
	   {
			   $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			   $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			   $re['gbk']          = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			   $re['big5']          = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			   preg_match_all($re[$charset], $str, $match);
			   if(count($match[0]) <= $length) return $str;
			   $slice = join("",array_slice($match[0], $start, $length));
	   }
	   if($suffix) return $slice."…";
	   return $slice;
}

/*
	格式化显示时间
*/
function getLastTimeFormt($time,$type=0){
	if($type==0) $f="m-d H:i"; 
	else if($type==1) $f="Y-m-d H:i";
	$agoTime = time() - $time;
    if ( $agoTime <= 60&&$agoTime >=0 ) {
        return $agoTime.'秒前';
    }elseif( $agoTime <= 3600 && $agoTime > 60 ){
        return intval($agoTime/60) .'分钟前';
    }elseif ( date('d',$time) == date('d',time()) && $agoTime > 3600){
		return '今天 '.date('H:i',$time);
    }elseif( date('d',$time+86400) == date('d',time()) && $agoTime < 172800){
		return '昨天 '.date('H:i',$time);
    }else{
        return date($f,$time);
    }

}

/**
 * 获取指定uid的头像文件规范路径
 * 来源：Ucenter base类的get_avatar方法
 *
 * @param int $uid
 * @param string $size 头像尺寸，可选为'big', 'middle', 'small'
 * @param string $type 类型，可选为real或者virtual
 * @return unknown
 */
function get_avatar($uid, $size = 'middle', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'big';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	$path = __ROOT__.'/Style/header/customavatars/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
	if(!file_exists(C("WEB_ROOT").$path)) $path = __ROOT__.'/Style/header/images/'."noavatar_$size.gif";
	return  $path;
}
/**
 * 获取地区列表，id为键，地区名为值的二维数组
 */
function get_Area_list($id="") {
	$cacheName = "temp_area_list_s";
	if(!S($cacheName)){
		$list = M('area')->getField('id,name');
		S($cacheName,$list,3600*1000000); 
	}else{
		$list = S($cacheName);
	}
	if(!empty($id)) return $list[$id];
	else return $list;
}

/**
 * IP转换成地区
 */
function ip2area($ip="") {
	if(strlen($ip)<6) return;
	import("ORG.Net.IpLocation");
	$Ip = new IpLocation("CoralWry.dat"); 
	$area = $Ip->getlocation($ip);
	$area = auto_charset($area);
	if($area['country']) $res = $area['country'];
	if($area['area']) $res = $res."(".$area['area'].")";
	if(empty($res)) $res = "未知";
	return $res;
}
//把秒换成小时或者天数
function second2string($second,$type=0){
	$day = floor($second/(3600*24));
	$second = $second%(3600*24);//除去整天之后剩余的时间
	$hour = floor($second/3600);
	$second = $second%3600;//除去整小时之后剩余的时间 
	$minute = floor($second/60);
	$second = $second%60;//除去整分钟之后剩余的时间 
	
	switch($type){
		case 0:
			if($day>=1) $res = $day."天";
			elseif($hour>=1) $res = $hour."小时";
			else  $res = $minute."分钟";
		break;
		case 1:
			if($day>=5) $res = date("Y-m-d H:i",time()+$second);
			elseif($day>=1&&$day<5) $res = $day."天前";
			elseif($hour>=1) $res = $hour."小时前";
			else  $res = $minute."分钟前";
		break;
	}
	//返回字符串
	return $res;
}


//快速缓存调用和储存
function FS($filename,$data="",$path=""){
	$path = C("WEB_ROOT").$path;
	if($data==""){
		$f = explode("/",$filename);
		$num = count($f);
		if($num>2){
			$fx = $f;
			array_pop($f);
			$pathe = implode("/",$f);
			$re = F($fx[$num-1],'',$pathe."/");
		}else{
			isset($f[1])?$re = F($f[1],'',C("WEB_ROOT").$f[0]."/"):$re = F($f[0]);
		}
		return $re;
	}else{
		if(!empty($path)) $re = F($filename,$data,$path);
		else $re = F($filename,$data);
	}
}
//格式化URL，只判断域名，前台后台共用，前台生成供判断的URL，后台生成供储存以便对比的URL
function formtUrl($url){
	if(!stristr($url,"http://")) $url = str_replace("http://","",$url);
	
	$fourl = explode("/",$url);
	$domain = get_domain("http://".$fourl[0]);
	$perfix = str_replace($domain,'',$fourl[0]);
	return $perfix.$domain;
}
function get_domain($url)
{
	$pattern = "/[/w-]+/.(com|net|org|gov|biz|com.tw|com.hk|com.ru|net.tw|net.hk|net.ru|info|cn|com.cn|net.cn|org.cn|gov.cn|mobi|name|sh|ac|la|travel|tm|us|cc|tv|jobs|asia|hn|lc|hk|bz|com.hk|ws|tel|io|tw|ac.cn|bj.cn|sh.cn|tj.cn|cq.cn|he.cn|sx.cn|nm.cn|ln.cn|jl.cn|hl.cn|js.cn|zj.cn|ah.cn|fj.cn|jx.cn|sd.cn|ha.cn|hb.cn|hn.cn|gd.cn|gx.cn|hi.cn|sc.cn|gz.cn|yn.cn|xz.cn|sn.cn|gs.cn|qh.cn|nx.cn|xj.cn|tw.cn|hk.cn|mo.cn|org.hk|is|edu|mil|au|jp|int|kr|de|vc|ag|in|me|edu.cn|co.kr|gd|vg|co.uk|be|sg|it|ro|com.mo)(/.(cn|hk))*/";
	preg_match($pattern, $url, $matches);
	if(count($matches) > 0)
	{
		return $matches[0];
	}else{
		$rs = parse_url($url);
		$main_url = $rs["host"];
		if(!strcmp(long2ip(sprintf("%u",ip2long($main_url))),$main_url))
		{
			return $main_url;
		}else{
			$arr = explode(".",$main_url);
			$count=count($arr);
			$endArr = array("com","net","org");//com.cn net.cn 等情况
			if (in_array($arr[$count-2],$endArr))
			{
				$domain = $arr[$count-3].".".$arr[$count-2].".".$arr[$count-1];
			}else{
				$domain = $arr[$count-2].".".$arr[$count-1];
			}
			return $domain;
		}
	}
} 

function getFloatValue($f,$len)
{
  return  number_format($f,$len,'.','');   
} 

//获取远程图片
function get_remote_img($content){
	$rt = C("WEB_ROOT");
	$img_dir = C("REMOTE_IMGDIR")?C("REMOTE_IMGDIR"):"/UF/Remote";//img_dir远程图片的保存目录，带前"/"不带后"/"
	$base_dir = substr($rt,0,strlen($rt)-1);//$base_dir网站根目录物理路径，不带后"/"
	
	$content = stripslashes($content); 
	$img_array = array(); 
	preg_match_all("/(src|SRC)=[\"|'| ]{0,}(http:\/\/(.*)\.(gif|jpg|jpeg|bmp|png|ico))/isU",$content,$img_array); //获取内容中的远程图片
	$img_array = array_unique($img_array[2]); //把重复的图片去掉
	set_time_limit(0); 
	$imgUrl = $img_dir."/".strftime("%Y%m%d",time()); //img_dir远程图片的保存目录，带前"/"不带后"/"
	$imgPath = $base_dir.$imgUrl; //$base_dir网站根目录物理路径，不带后"/"
	$milliSecond = strftime("%H%M%S",time()); 
	if(!is_dir($imgPath)) MakeDir($imgPath,0777);//如果路径不存在则创建
	foreach($img_array as $key =>$value) 
	{ 
		$value = trim($value); 
		$get_file = @file_get_contents($value); 
		$rndFileName = $imgPath."/".$milliSecond.$key.".".substr($value,-3,3); 
		$fileurl = $imgUrl."/".$milliSecond.$key.".".substr($value,-3,3); 

		if($get_file) 
		{ 
			$fp = @fopen($rndFileName,"w"); 
			@fwrite($fp,$get_file); 
			@fclose($fp); 
		} 
		$content = ereg_replace($value,$fileurl,$content); 
	} 
	//$content = addslashes($content); 
	return $content;
}

function getSubSite(){
	$map['is_open'] = 1;
	$list = M("area")->field(true)->where($map)->select();
	$cdomain = explode(".",$_SERVER['HTTP_HOST']);
	$cpx = array_pop($cdomain);
	$doamin = array_pop($cdomain);
	$host = ".".$doamin.".".$cpx;
	foreach($list as $key=>$v){
		$list[$key]['host'] = "http://".$v['domain'].$host;
	}
	return $list;
}
function getCreditsLog($map,$size){
	if(empty($map['uid'])) return;
	
	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('member_creditslog')->where($map)->count('id');
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}

	$list = M('member_creditslog')->where($map)->order('id DESC')->limit($Lsql)->select();
	$type_arr = C("MONEY_LOG");
	foreach($list as $key=>$v){
		//$list[$key]['type'] = $type_arr[$v['type']];
	}
	
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	return $row;
}

function getCredit($uid){
	$pre = C('DB_PREFIX');
	$user = M('members m')->join("{$pre}member_money mm ON m.id=mm.uid")->where("m.id={$uid}")->find();
	if( !is_array($user) ) 	return "用户出错，请重新操作";

	$credit = array();
	$credit['xy']['limit'] = 	getFloatValue($user['credit_limit'],2);
	$credit['xy']['use'] = 		getFloatValue(M('borrow_info')->where("borrow_uid = {$uid} AND borrow_status in(0,2,4,6) AND borrow_type=1")->sum("borrow_money-repayment_money"),2);
	$credit['xy']['cuse'] = 	getFloatValue($credit['xy']['limit'] - $credit['xy']['use'],2);

	$credit['db']['limit'] = 	getFloatValue($user['vouch_limit'],2);
	$credit['db']['use'] = 		getFloatValue(M('borrow_info')->where("borrow_uid = {$uid} AND borrow_status in(0,2,4,6) AND borrow_type=2")->sum("borrow_money-repayment_money"),2);
	$credit['db']['cuse'] = 	getFloatValue($credit['db']['limit'] - $credit['db']['use'],2);
	
	$credit['dy']['limit'] = 	getFloatValue($user['diya_limit'],2);
	$credit['dy']['use'] = 		getFloatValue(M('borrow_info')->where("borrow_uid = {$uid} AND borrow_status in(0,2,4,6) AND borrow_type=5")->sum("borrow_money-repayment_money"),2);
	$credit['dy']['cuse'] = 	getFloatValue($credit['dy']['limit'] - $credit['dy']['use'],2);

	$credit['jz']['limit'] = 	getFloatValue(0.9 * M('investor_detail')->where(" investor_uid={$uid} AND status =7 ")->sum("capital+interest-interest_fee"),2);
	$credit['jz']['use'] = 		getFloatValue(M('borrow_info')->where("borrow_uid = {$uid} AND borrow_status in(0,2,4,6) AND borrow_type=4")->sum("borrow_money+borrow_interest-repayment_money-repayment_interest"),2);
	$credit['jz']['cuse'] = 	getFloatValue($credit['jz']['limit'] - $credit['jz']['use'],2);

	$credit['all']['limit'] = 	getFloatValue($credit['xy']['limit'] + $credit['db']['limit'] + $credit['dy']['limit'],2);
	$credit['all']['use'] = 	getFloatValue($credit['xy']['use'] + $credit['db']['use'] + $credit['dy']['use'],2);
	$credit['all']['cuse'] = 	getFloatValue($credit['all']['limit'] - $credit['all']['use'],2);

	return $credit;
}

//积分日志
function getIntegralLog($map,$size){
	if(empty($map['uid'])) return;
	
	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('member_integrallog')->where($map)->count('id');
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}

	$list = M('member_integrallog')->where($map)->order('id DESC')->limit($Lsql)->select();
	$type_arr = C("INTEGRAL_LOG");
	foreach($list as $key=>$v){
		$list[$key]['type'] = $type_arr[$v['type']];
	}
	
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	return $row;
}

//所有圈子列表,以id为键
function Notice($type,$uid,$data=array()){
		$datag = get_global_setting();
		$datag=de_xie($datag);
		$msgconfig = FS("Webconfig/msgconfig");
		
		$emailTxt = FS("Webconfig/emailtxt");
		$smsTxt = FS("Webconfig/smstxt");
		$msgTxt = FS("Webconfig/msgtxt");
		$emailTxt=de_xie($emailTxt);
		$smsTxt=de_xie($smsTxt);
		$msgTxt=de_xie($msgTxt);
		//邮件
		import("ORG.Net.Email");
		$port =$msgconfig['stmp']['port'];//25;
		$smtpserver=$msgconfig['stmp']['server']; 
		$smtpuser = $msgconfig['stmp']['user']; 
		$smtppwd = $msgconfig['stmp']['pass']; 
		$mailtype = "HTML"; 
		$sender = $msgconfig['stmp']['user']; 
		$smtp = new smtp($smtpserver,$port,true,$smtpuser,$smtppwd,$sender); 
		//邮件
		$minfo = M('members')->field('user_email,user_name,user_phone')->find($uid);
		$uname = $minfo['user_name'];
	switch($type){
		
		case 1://注册成功发送邮件
			$vcode = rand_string($uid,32,0,1);
			$link='<a href="'.C('WEB_URL').'/member/common/emailverify?vcode='.$vcode.'">点击链接验证邮件</a>';
			/*站内信*/
			$body = str_replace(array("#UserName#"),array($uname),$msgTxt['regsuccess']); 
			addInnerMsg($uid,"恭喜您注册成功",$body);
			/*站内信*/
			/*邮件*/
			$subject = "您刚刚在".$datag['web_name']."注册成功"; 
			$body = str_replace(array("#UserName#","#LINK#"),array($uname,$link),$emailTxt['regsuccess']); 
			$to = $minfo['user_email']; 
			$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
			/*邮件*/
			return $send;
		break;
		
		case 2://安全中心通过验证码改密码安全问题
			$vcode = rand_string($uid,10,3,3);
			$pcode = rand_string($uid,6,1,3);
			/*邮件*/
			$subject = "您刚刚在".$datag['web_name']."注册成功"; 
			$body = str_replace(array("#CODE#"),array($vcode),$emailTxt['safecode']); 
			$to = $minfo['user_email']; 
			$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
			/*邮件*/
			
			//手机
			$content = str_replace(array("#CODE#"),array($pcode),$smsTxt['safecode']); 
			$sendp = sendsms($minfo['user_phone'],$content);
			return $send;
		break;
		
		case 3://安全中心通过验证码改手机
			$vcode = rand_string($uid,6,1,4);
			$content = str_replace(array("#CODE#"),array($vcode),$smsTxt['safecode']); 
			$send = sendsms($minfo['user_phone'],$content);
			return $send;
		
		case 4://安全中心新手机验证码
			$vcode = rand_string($uid,6,1,5);
			$content = str_replace(array("#CODE#"),array($vcode),$smsTxt['safecode']); 
			$send = sendsms($data['phone'],$content);
			return $send;
		break;
		
		case 5://安全中心新手机验证码安全码
			$vcode = rand_string($uid,10,1,6);
			/*邮件*/
			$subject = "您刚刚在".$datag['web_name']."申请更换手机的安全码"; 
			$body = str_replace(array("#CODE#"),array($vcode),$emailTxt['changephone']); 
			$to = $minfo['user_email']; 
			$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
			/*邮件*/
			return $send;
		break;
		
		case 6://借款发布成功审核通过
			/*邮件*/
			$subject = "恭喜，你在".$datag['web_name']."发布的借款审核通过"; 
			$body = str_replace(array("#UserName#"),array($uname),$emailTxt['verifysuccess']); 
			$to = $minfo['user_email']; 
			$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
			/*邮件*/
			/*站内信*/
			$body = str_replace(array("#UserName#"),array($uname),$msgTxt['verifysuccess']); 
			addInnerMsg($uid,"恭喜借款审核通过",$body);
			/*站内信*/
			return $send;
		break;

		case 7://密码找回
			$vcode = rand_string($uid,32,0,7);
			$link='<a href="'.C('WEB_URL').'/member/common/getpasswordverify?vcode='.$vcode.'">点击链接验证邮件</a>';
			/*邮件*/
			$subject = "您刚刚在".$datag['web_name']."申请了密码找回"; 
			$body = str_replace(array("#UserName#","#LINK#"),array($uname,$link),$emailTxt['getpass']); 
			$to = $minfo['user_email']; 
			$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
			/*邮件*/
			return $send;
		break;
		case 8://验证中心邮件验证
			$vcode = rand_string($uid,32,0,1);
			$link='<a href="'.C('WEB_URL').'/member/common/emailverify?vcode='.$vcode.'">点击链接验证邮件</a>';
			/*邮件*/
			$subject = "您刚刚在".$datag['web_name']."申请邮件验证"; 
			$body = str_replace(array("#UserName#","#LINK#"),array($uname,$link),$emailTxt['regsuccess']); 
			$to = $minfo['user_email'];

			
			$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
			/*邮件*/
			return $send;
		break;
		
		
		case 9://还款到期提醒
			/*邮件*/
			$subject = "您在".$datag['web_name']."的还款最终期限即将到期。"; 
			$body = str_replace(array("#UserName#","#borrowName#","#borrowMoney#"),array($uname,$data['borrowName'],$data['borrowMoney']),$emailTxt['repaymentTip']); 
			$to = $minfo['user_email']; 
			$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
			/*邮件*/
			return $send;
		break;
		case 10://支付密码找回
			$vcode = rand_string($uid,32,0,7);
			$link='<a href="'.C('WEB_URL').'/member/index/getpaypasswordverify?vcode='.$vcode.'">点击链接验证邮件</a>';
			/*邮件*/
			$subject = "您刚刚在".$datag['web_name']."申请了支付密码找回"; 
			$body = str_replace(array("#UserName#","#LINK#"),array($uname,$link),$emailTxt['getpaypass']); 
			$to = $minfo['user_email']; 
			$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
			/*邮件*/
			return $send;
		break;

	}
}

function SMStip($type,$mob,$from=array(),$to=array()){
	if(empty($mob)) return;
		$datag = get_global_setting();
		$datag=de_xie($datag);
		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt=de_xie($smsTxt);
		if($smsTxt[$type]['enable']==1){
			$body = str_replace($from,$to,$smsTxt[$type]['content']); 
			return sendsms($mob,$body);
		}else{
			return;	
		}
}


//所有圈子列表,以id为键
function MTip($type,$uid=0,$info="",$autoid=""){
		$datag = get_global_setting();
		$datag=de_xie($datag);
		$port =25;  
		//邮件
		$id1 = "{$type}_1";
		$id2 = "{$type}_2";
		$per = C('DB_PREFIX');
		
		$sql ="select 1 as tip1,0 as tip2,m.user_email,m.id from {$per}members m WHERE m.id={$uid}";
		$memail = M()->query($sql);
	switch($type){
		
		case "chk1"://修改密码
			/*邮件*/
			$to="";
			$subject = "您刚刚在".$datag['web_name']."修改了登录密码"; 
			$body = "您刚刚在".$datag['web_name']."修改了登录密码,如不是自己操作,请尽快联系客服"; 
			$innerbody = "您刚刚修改了登录密码,如不是自己操作,请尽快联系客服"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"您刚刚修改了登录密码",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk2"://修改银行帐号
			/*邮件*/
			$to="";
			$subject = "您刚刚在".$datag['web_name']."修改了提现的银行帐户"; 
			$body = "您刚刚在".$datag['web_name']."修改了提现的银行帐户,如不是自己操作,请尽快联系客服"; 
			$innerbody = "您刚刚修改了提现的银行帐户,如不是自己操作,请尽快联系客服"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"您刚刚修改了提现的银行帐户",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk6"://资金提现
			/*邮件*/
			$to="";
			$subject = "您刚刚在".$datag['web_name']."申请了提现操作"; 
			$body = "您刚刚在".$datag['web_name']."申请了提现操作,如不是自己操作,请尽快联系客服"; 
			$innerbody = "您刚刚申请了提现操作,如不是自己操作,请尽快联系客服"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"您刚刚申请了提现操作",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk7"://借款标初审未通过
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."发布的借款标刚刚初审未通过"; 
			$body = "您在".$datag['web_name']."发布的第{$info}号借款标刚刚初审未通过"; 
			$innerbody = "您发布的第{$info}号借款标刚刚初审未通过"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"刚刚您的借款标初审未通过",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk8"://借款标初审通过
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."发布的借款标刚刚初审通过"; 
			$body = "您在".$datag['web_name']."发布的第{$info}号借款标刚刚初审通过"; 
			$innerbody = "您发布的第{$info}号借款标刚刚初审通过"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"刚刚您的借款标初审通过",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk9"://借款标复审通过
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."发布的借款标刚刚复审通过"; 
			$body = "您在".$datag['web_name']."发布的第{$info}号借款标刚刚复审通过"; 
			$innerbody = "您发布的第{$info}号借款标刚刚复审通过"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"刚刚您的借款标复审通过",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk12"://借款标复审未通过
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."的发布的借款标刚刚复审未通过"; 
			$body = "您在".$datag['web_name']."的发布的第{$info}号借款标复审未通过"; 
			$innerbody = "您发布的第{$info}号借款标复审未通过"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"刚刚您的借款标复审未通过",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk10"://借款标满标
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."的借款标已满标"; 
			$body = "刚刚您在".$datag['web_name']."的第{$info}号借款标已满标，请登录查看"; 
			$innerbody = "刚刚您的借款标已满标"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"刚刚您的第{$info}号借款标已满标",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk11"://借款标流标
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."的借款标已流标"; 
			$body = "您在".$datag['web_name']."发布的第{$info}号借款标已流标，请登录查看"; 
			$innerbody = "您的第{$info}号借款标已流标"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"刚刚您的借款标已流标",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk25"://借入人还款成功
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."的借入的还款进行了还款操作"; 
			$body = "您对在".$datag['web_name']."借入的第{$info}号借款进行了还款，请登录查看"; 
			$innerbody = "您对借入的第{$info}号借款进行了还款"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"您对借入标还款进行了还款操作",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk27"://自动投标借出完成
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."设置的第{$autoid}号自动投标按设置投了新标"; 
			$body = "您在".$datag['web_name']."设置的第{$autoid}号自动投标按设置对第{$info}号借款进行了投标，请登录查看"; 
			$innerbody = "您设置的第{$autoid}号自动投标对第{$info}号借款进行了投标"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"您设置的第{$autoid}号自动投标按设置投了新标",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk14"://借出成功
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."投标的借款成功了"; 
			$body = "您在".$datag['web_name']."投标的第{$info}号借款借出成功了"; 
			$innerbody = "您投标的借款成功了"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"您投标的第{$info}号借款借款成功",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;

		
		case "chk15"://借出流标
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."投标的借款流标了"; 
			$body = "您在".$datag['web_name']."投标的第{$info}号借款流标了，相关资金已经返回帐户，请登录查看"; 
			$innerbody = "您投标的借款流标了"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"您投标的第{$info}号借款流标了，相关资金已经返回帐户",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk16"://收到还款
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."借出的借款收到了新的还款"; 
			$body = "您在".$datag['web_name']."借出的第{$info}号借款收到了新的还款，请登录查看"; 
			$innerbody = "您借出的借款收到了新的还款"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"您借出的第{$info}号借款收到了新的还款",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
		
		case "chk18"://网站代为偿还
			/*邮件*/
			$to="";
			$subject = "您在".$datag['web_name']."借出的借款逾期网站代还了本金"; 
			$body = "您在".$datag['web_name']."借出的第{$info}号借款逾期网站代还了本金，请登录查看"; 
			$innerbody = "您借出的第{$info}号借款逾期网站代还了本金"; 
			/*邮件*/
			foreach($memail as $v){
				if($v['tip1']>0) addInnerMsg($v['id'],"您借出的第{$info}号借款逾期网站代还了本金",$innerbody);
				if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email']; 
			}
		break;
	}
	//if(!empty($to)) $send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
	//return $send;
}

function investMoney($uid,$borrow_id,$money,$_is_auto=0){
	//echo "Libhfdjgf";die;
	$pre = C('DB_PREFIX');
	$done = false;
	$datag = get_global_setting();
	//var_dump($datag);die;
	//$fee_invest_manage = explode("|",$datag['fee_invest_manage']);
	/////////////////////////////锁表  辉 2013-11-16////////////////////////////////////////////////

	$dataname = C('DB_NAME');
	$db_host = C('DB_HOST');
	$db_user = C('DB_USER');
	$db_pwd = C('DB_PWD');
	//echo "7123726372";die;
	$bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
	//$bdb->errorInfo();die;
	$bdb->beginTransaction();
	$bId = $borrow_id;
	//echo $bId;die;
		   
	$sql1 ="SELECT suo FROM lzh_borrow_info_lock WHERE id = ? FOR UPDATE";
    $stmt1 = $bdb->prepare($sql1);
	$stmt1->bindParam(1, $bId);    //绑定第一个参数值
    $stmt1->execute();
	/////////////////////////////锁表  辉 2013-11-16////////////////////////////////////////////////
	$binfo = M("borrow_info")->field("borrow_uid,borrow_money,borrow_interest_rate,borrow_type,borrow_duration,repayment_type,has_borrow,reward_money,money_collect")->find($borrow_id);//新加入了奖金reward_money到资金总额里
	$vminfo = getMinfo($uid,'m.user_phone,m.user_leve,m.time_limit,mm.account_money,mm.back_money,mm.money_collect');
	
	$temp_amoney=($vminfo['account_money']+$vminfo['back_money']+$binfo['reward_money']);//20151209
	//if(($vminfo['account_money']+$vminfo['back_money']+$binfo['reward_money'])<$money) {
	if (bccomp($money,$temp_amoney) == 1) {//20151209
		return "您当前的可用金额为：".($vminfo['account_money']+$vminfo['back_money']+$binfo['reward_money'])." 对不起，可用余额不足，不能投标";
	}
	
	////////////新增投标时检测会员的待收金额是否大于标的设置的代收金额限制，大于就可投标，小于就不让投标 2013-08-26 fan//////////////
	
	if($binfo['money_collect']>0){//判断是否设置了投标待收金额限制
		if($vminfo['money_collect']<$binfo['money_collect']){
			return "对不起，此标设置有投标待收金额限制，您当前的待收金额为".$vminfo['money_collect']."元，小于该标设置的待收金额限制".$binfo['money_collect']."元。";
		}
	}
	
	////////////新增投标时检测会员的待收金额是否大于标的设置的代收金额限制，大于就可投标，小于就不让投标 2013-08-26 fan//////////////
	
	//不同会员级别的费率
	//($vminfo['user_leve']==1 && $vminfo['time_limit']>time())?$fee_rate=($fee_invest_manage[1]/100):$fee_rate=($fee_invest_manage[0]/100);
	$fee_rate=$datag['fee_invest_manage']/100;
	//投入的钱
	$havemoney = $binfo['has_borrow'];
	if(($binfo['borrow_money'] - $havemoney -$money)<0) {
		//return "对不起，此标还差".($binfo['borrow_money'] - $havemoney)."元满标，您最多投标".($binfo['borrow_money'] - $havemoney)."元";
		$money = $binfo['borrow_money'] - $havemoney;
	}
	
	$borrow_invest = M("borrow_investor")->where("borrow_id = {$borrow_id}")->sum('investor_capital');//新加投资金额检测
	
	$investMoney = D('borrow_investor');
	$investMoney->startTrans();
		//还款概要公共信息START
		$investinfo['ent'] = true == ListMobile() ? 1 : 0;
		$investinfo['status'] = 1;//等待复审
		$investinfo['borrow_id'] = $borrow_id;
		$investinfo['investor_uid'] = $uid;
		$investinfo['borrow_uid'] = $binfo['borrow_uid'];
		
		/////////////////////////////////////新加投资金额检测/////////////////////////////////////////////
		if($borrow_invest['investor_capital']>$binfo['borrow_money']){
			$investinfo['investor_capital'] = $binfo['borrow_money'] - $binfo['has_borrow'];
		}else{
			$investinfo['investor_capital'] = $money;
		}
		/////////////////////////////////////新加投资金额检测/////////////////////////////////////////////
		
		$investinfo['is_auto'] = $_is_auto;
		$investinfo['add_time'] = time();
		//还款详细公共信息START
		$savedetail=array();
		switch($binfo['repayment_type']){
			case 1://按天到期还款
				//还款概要START
				$investinfo['investor_interest'] = getFloatValue($binfo['borrow_interest_rate']/365*$investinfo['investor_capital']*$binfo['borrow_duration']/100,4);
				$investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'],4);//修改投资人的天标利息管理费2013-03-19 fan
				$invest_info_id = M('borrow_investor')->add($investinfo);
				//还款概要END
				$investdetail['borrow_id'] = $borrow_id;
				$investdetail['invest_id'] = $invest_info_id;
				$investdetail['investor_uid'] = $uid;
				$investdetail['borrow_uid'] = $binfo['borrow_uid'];
				$investdetail['capital'] = $investinfo['investor_capital'];
				$investdetail['interest'] = $investinfo['investor_interest'];
				$investdetail['interest_fee'] = $investinfo['invest_fee'];
				$investdetail['status'] = 0;
				$investdetail['sort_order'] = 1;
				$investdetail['total'] = 1;
				$savedetail[] = $investdetail;
			break;
			case 2://每月还款
				//还款概要START
				$monthData['type'] = "all";
				$monthData['money'] = $investinfo['investor_capital'];
				$monthData['year_apr'] = $binfo['borrow_interest_rate'];
				$monthData['duration'] = $binfo['borrow_duration'];
				$repay_detail = EqualMonth($monthData);
				
				$investinfo['investor_interest'] = ($repay_detail['repayment_money'] - $investinfo['investor_capital']);
				$investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'],4);
				$invest_info_id = M('borrow_investor')->add($investinfo);
				//还款概要END
				
				$monthDataDetail['money'] = $investinfo['investor_capital'];
				$monthDataDetail['year_apr'] = $binfo['borrow_interest_rate'];
				$monthDataDetail['duration'] = $binfo['borrow_duration'];
				$repay_list = EqualMonth($monthDataDetail);
				$i=1;
				foreach($repay_list as $key=>$v){
					$investdetail['borrow_id'] = $borrow_id;
					$investdetail['invest_id'] = $invest_info_id;
					$investdetail['investor_uid'] = $uid;
					$investdetail['borrow_uid'] = $binfo['borrow_uid'];
					$investdetail['capital'] = $v['capital'];
					$investdetail['interest'] = $v['interest'];
					$investdetail['interest_fee'] = getFloatValue($fee_rate*$v['interest'],4);
					$investdetail['status'] = 0;
					$investdetail['sort_order'] = $i;
					$investdetail['total'] = $binfo['borrow_duration'];
					$i++;
					$savedetail[] = $investdetail;
				}
			break;
			case 3://按季分期还款
				//还款概要START

				$monthData['month_times'] = $binfo['borrow_duration'];
				$monthData['account'] = $investinfo['investor_capital'];
				$monthData['year_apr'] = $binfo['borrow_interest_rate'];
				$monthData['type'] = "all";
				$repay_detail = EqualSeason($monthData);
				
				$investinfo['investor_interest'] = ($repay_detail['repayment_money'] - $investinfo['investor_capital']);
				$investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'],4);
				$invest_info_id = M('borrow_investor')->add($investinfo);
				//还款概要END
				
				$monthDataDetail['month_times'] = $binfo['borrow_duration'];
				$monthDataDetail['account'] = $investinfo['investor_capital'];
				$monthDataDetail['year_apr'] = $binfo['borrow_interest_rate'];
				$repay_list = EqualSeason($monthDataDetail);
				$i=1;
				foreach($repay_list as $key=>$v){
					$investdetail['borrow_id'] = $borrow_id;
					$investdetail['invest_id'] = $invest_info_id;
					$investdetail['investor_uid'] = $uid;
					$investdetail['borrow_uid'] = $binfo['borrow_uid'];
					$investdetail['capital'] = $v['capital'];
					$investdetail['interest'] = $v['interest'];
					$investdetail['interest_fee'] = getFloatValue($fee_rate*$v['interest'],4);
					$investdetail['status'] = 0;
					$investdetail['sort_order'] = $i;
					$investdetail['total'] = $binfo['borrow_duration'];
					$i++;
					$savedetail[] = $investdetail;
				}
			break;
			case 4://每月还息到期还本
				$monthData['month_times'] = $binfo['borrow_duration'];
				$monthData['account'] = $investinfo['investor_capital'];
				$monthData['year_apr'] = $binfo['borrow_interest_rate'];
				$monthData['type'] = "all";
				$repay_detail = EqualEndMonth($monthData);
				
				$investinfo['investor_interest'] = ($repay_detail['repayment_account'] - $investinfo['investor_capital']);
				$investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'],4);
				$invest_info_id = M('borrow_investor')->add($investinfo);
				//还款概要END
				
				$monthDataDetail['month_times'] = $binfo['borrow_duration'];
				$monthDataDetail['account'] = $investinfo['investor_capital'];
				$monthDataDetail['year_apr'] = $binfo['borrow_interest_rate'];
				$repay_list = EqualEndMonth($monthDataDetail);
				$i=1;
				foreach($repay_list as $key=>$v){
					$investdetail['borrow_id'] = $borrow_id;
					$investdetail['invest_id'] = $invest_info_id;
					$investdetail['investor_uid'] = $uid;
					$investdetail['borrow_uid'] = $binfo['borrow_uid'];
					$investdetail['capital'] = $v['capital'];
					$investdetail['interest'] = $v['interest'];
					$investdetail['interest_fee'] = getFloatValue($fee_rate*$v['interest'],4);
					$investdetail['status'] = 0;
					$investdetail['sort_order'] = $i;
					$investdetail['total'] = $binfo['borrow_duration'];
					$i++;
					$savedetail[] = $investdetail;
				}
			break;
			case 5://一次性还款
				$monthData['month_times'] = $binfo['borrow_duration'];
				$monthData['account'] = $investinfo['investor_capital'];
				$monthData['year_apr'] = $binfo['borrow_interest_rate'];
				$monthData['type'] = "all";
				$repay_detail = EqualEndMonthOnly($monthData);
				
				$investinfo['investor_interest'] = ($repay_detail['repayment_account'] - $investinfo['investor_capital']);
				$investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'],4);
				$invest_info_id = M('borrow_investor')->add($investinfo);
				//还款概要END
				
				$monthDataDetail['month_times'] = $binfo['borrow_duration'];
				$monthDataDetail['account'] = $investinfo['investor_capital'];
				$monthDataDetail['year_apr'] = $binfo['borrow_interest_rate'];
				$monthDataDetail['type'] = "all";
				$repay_list = EqualEndMonthOnly($monthDataDetail);
				
				$investdetail['borrow_id'] = $borrow_id;
				$investdetail['invest_id'] = $invest_info_id;
				$investdetail['investor_uid'] = $uid;
				$investdetail['borrow_uid'] = $binfo['borrow_uid'];
				$investdetail['capital'] = $repay_list['capital'];
				$investdetail['interest'] = $repay_list['interest'];
				$investdetail['interest_fee'] = getFloatValue($fee_rate*$repay_list['interest'],4);
				$investdetail['status'] = 0;
				$investdetail['sort_order'] = 1;
				$investdetail['total'] = 1;
					
				$savedetail[] = $investdetail;
				
			break;
		}
		
		foreach ($savedetail as $key => $val) {
			$invest_defail_id = M('investor_detail')->add($val);//保存还款详情
		}
		
		$last_have_money = M("borrow_info")->getFieldById($borrow_id,"has_borrow");
		$upborrowsql = "update `{$pre}borrow_info` set ";
		$upborrowsql .= "`has_borrow`=".($last_have_money+$money).",`borrow_times`=`borrow_times`+1";
		$upborrowsql .= " WHERE `id`={$borrow_id}";
		$upborrow_res = M()->execute($upborrowsql);
		
		//更新投标进度
	if($invest_defail_id && $invest_info_id && $upborrow_res){//还款概要和详情投标进度都保存成功
		$investMoney->commit();
		$res = memberMoneyLog($uid,6,-$money,"对{$borrow_id}号标进行投标",$binfo['borrow_uid']);
		$today_reward = explode("|",$datag['today_reward']);
		if($binfo['repayment_type']=='1'){//如果是天标，则执行1个月的续投奖励利率
			$reward_rate = floatval(floatval($today_reward[0]/30)*(int)$binfo['borrow_duration']);
		}else{
			if($binfo['borrow_duration']==1){
				$reward_rate = floatval($today_reward[0]);
			}else if($binfo['borrow_duration']==2){
				$reward_rate = floatval($today_reward[1]);
			}else{
				$reward_rate = floatval($today_reward[2]);
			}
		}
		////////////////////////////////////////回款续投奖励规则 fan 2013-07-20////////////////////////////
		//$reward_rate = floatval($datag['today_reward']);//floatval($datag['today_reward']);//当日回款续投奖励利率
		if($binfo['borrow_type']!=3){//如果是秒标(borrow_type==3)，则没有续投奖励这一说
			$vd['add_time'] = array("lt",time());
			$vd['investor_uid'] = $uid;
			$borrow_invest_count = M("borrow_investor")->where($vd)->count('id');//检测是否投过标且大于一次
			if($reward_rate>0 && $borrow_invest_count>0 && ($vminfo['back_money']>0 || $binfo['repayment_type']>1 && $binfo['borrow_duration']>1)){//首次投标不给续投奖励
				if($money>$vminfo['back_money']){//如果投标金额大于回款资金池金额，有效续投奖励以回款金额资金池总额为标准，否则以投标金额为准
					$reward_money_s = $vminfo['back_money'];
				}else{
					$reward_money_s = $money;
				}

				$save_reward['borrow_id'] = $borrow_id;
				$save_reward['reward_uid'] = $uid;
				$save_reward['payment'] = $money;
				$save_reward['invest_money'] = $reward_money_s;//如果投标金额大于回款资金池金额，有效续投奖励以回款金额资金池总额为标准，否则以投标金额为准


				if($binfo['repayment_type']=='1'){//如果是天标,按原先的公式计算
					$save_reward['reward_money'] = $reward_money_s*$reward_rate/1000;//续投奖励
				}else{
					$save_reward['reward_money'] =( $reward_money_s*$reward_rate/1000 )+ ($money*($binfo['borrow_duration']-1)*$reward_rate/1000);//续投奖励
				}

				$save_reward['reward_status'] = 0;
				$save_reward['add_time'] = time();
				$save_reward['add_ip'] = get_client_ip();
				$newidxt = M("today_reward")->add($save_reward);
				if($newidxt){
					//$result = M()->execute("update `{$pre}investor_detail` set `rewardid` = {$newidxt} WHERE id=".$invest_defail_id)
					$result =membermoneylog($uid,33,$save_reward['reward_money'],"续投有效金额({$reward_money_s})的奖励({$borrow_id}号标)奖励",0,"@网站管理员@");
				}
			}else{
				$result = true;
			}
		}

		/////////////////////////回款续投奖励结束 2013-05-10 fans///////////////////////////////
		
		if( ($havemoney+$money) == $binfo['borrow_money']){
			borrowFull($borrow_id,$binfo['borrow_type']);//满标，标记为还款中，更新相关数据
		}

		if(!$res && !$result){//没有正常记录和扣除帐户余额的话手动回滚
			M('investor_detail')->where("invest_id={$invest_info_id}")->delete();
			M('borrow_investor')->where("id={$invest_info_id}")->delete();
			//更新投标进度
			$upborrowsql = "update `{$pre}borrow_info` set ";
			$upborrowsql .= "`has_borrow`=".$havemoney.",`borrow_times`=`borrow_times`-1";
			$upborrowsql .= " WHERE `id`={$borrow_id}";
			$upborrow_res = M()->execute($upborrowsql);
			//更新投标进度
			$done = false;
			
		}else{
			if(M('member_yott')->where(array('uid'=>$uid))->count()){
				//开始同步到yott
				$userTender = array(
					'id='.$invest_info_id,
					'phone='.$vminfo['user_phone'],
					'pid='.$borrow_id,
					'account='.$money,
					'datetime='.time()
				);
				import('App.Api.yott');
				$yott = new yott();
				$res = $yott->userTender($userTender);

				M('yott_log')->add(
					array(
						'dateline'=>time(),
						'apitype'=>'usertender',
						'apidata'=>json_encode($userTender),
						'code'=>$res->code,
						'msg'=>$res->msg
					)
				);

				M('yott_money_log')->add(
					array(
						'bid'=>$invest_info_id,
						'uid'=>$uid,
						'pid'=>$borrow_id,
						'account'=>$money,
						'datetime'=>time(),
						'status'=> 0 == $res->code ? 1 : 0,
					)
				);
				//结束同步yott


			}
			$done = true;

		}
	}else{
		//echo "000";die;
		$investMoney->rollback();
	}
	return $done;
}
//满标处理
function borrowFull($borrow_id,$btype = 0){
	$pre = C('DB_PREFIX');
	if($btype==3){//秒还标
		borrowApproved($borrow_id);
		sleep(3);
		borrowRepayment($borrow_id,1);
	}else{
		$saveborrow['borrow_status']=4;
		$saveborrow['full_time']=time();
		$upborrow_res = M("borrow_info")->where("id={$borrow_id}")->save($saveborrow);
	}
}

//流标处理
function borrowRefuse($borrow_id,$type){//$type=2 代表流标返还; $type=3代表复审未通过，返还
	$pre = C('DB_PREFIX');
	$done = false;
	$borrowInvestor = D('borrow_investor');
	$binfo = M("borrow_info")->field("id,borrow_type,borrow_money,borrow_uid,borrow_duration,repayment_type")->find($borrow_id);
	//$investorList = $borrowInvestor->field('id,investor_uid,investor_capital')->where("borrow_id={$borrow_id}")->select();
	$investorList = M("borrow_investor")->field('id,investor_uid,investor_capital')->where("borrow_id={$borrow_id}")->select();
	M('investor_detail')->where("borrow_id={$borrow_id}")->delete();//流标将删除其对应的还款记录表
	
	if($binfo['borrow_type']==1){//如果是普通标
		$limit_credit = memberLimitLog($binfo['borrow_uid'],12,($binfo['borrow_money']),$info="{$borrow_id}号标流标,返还借款信用额度");//返回借款额度
	}
	$borrowInvestor->startTrans();
	
	$bstatus = ($type==2)?3:5;//3:标未满，结束，流标   5:复审未通过，结束
	$upborrow_info = M('borrow_info')->where("id={$borrow_id}")->setField("borrow_status",$bstatus);
	//处理借款概要
	$buname = M('members')->getFieldById($binfo['borrow_uid'],'user_name');
	//处理借款概要
	
	if(is_array($investorList)){
		$upsummary_res = M('borrow_investor')->where("borrow_id={$borrow_id}")->setField("status",$type);
		$moneynewid_x_temp = true;
		$bxid_temp = true;
		foreach($investorList as $v){
			MTip('chk15',$v['investor_uid'],$borrow_id);//sss
			$accountMoney_investor = M("member_money")->field(true)->find($v['investor_uid']);
			$datamoney_x['uid'] = $v['investor_uid'];
			$datamoney_x['type'] = ($type==3)?16:8;
			$datamoney_x['affect_money'] = $v['investor_capital'];
			$datamoney_x['account_money'] = ($accountMoney_investor['account_money'] + $datamoney_x['affect_money']);//投标不成功返回充值资金池
			$datamoney_x['collect_money'] = $accountMoney_investor['money_collect'];
			$datamoney_x['freeze_money'] = $accountMoney_investor['money_freeze'] - $datamoney_x['affect_money'];
			$datamoney_x['back_money'] = $accountMoney_investor['back_money'];
			
			//会员帐户
			$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
			$mmoney_x['money_collect']=$datamoney_x['collect_money'];
			$mmoney_x['account_money']=$datamoney_x['account_money'];
			$mmoney_x['back_money']=$datamoney_x['back_money'];
			
			//会员帐户
			$_xstr = ($type==3)?"复审未通过":"募集期内标未满,流标";
			$datamoney_x['info'] = "第{$borrow_id}号标".$_xstr."，返回冻结资金";
			$datamoney_x['add_time'] = time();
			$datamoney_x['add_ip'] = get_client_ip();
			$datamoney_x['target_uid'] = $binfo['borrow_uid'];
			$datamoney_x['target_uname'] = $buname;
			$moneynewid_x = M('member_moneylog')->add($datamoney_x);
			if($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
			$moneynewid_x_temp = $moneynewid_x_temp && $moneynewid_x;
		    $bxid_temp = $bxid_temp && $bxid;

		}
	}else{
		$moneynewid_x_temp = true;
		$bxid_temp = true;
		$upsummary_res=true;
	}

	if($moneynewid_x_temp && $upsummary_res && $bxid_temp && $upborrow_info){
		/////////////////////////回款续投奖励预奖励取消开始 2013-05-10 fans///////////////////////////////
		$listreward =M("today_reward")->field("reward_uid,reward_money")->where("borrow_id={$borrow_id} AND reward_status=0")->select();
		if(!empty($listreward))
		{
			foreach($listreward as $v)
			{
				//membermoneylog( $v['reward_uid'],35,0-$v['reward_money'],"续投奖励({$borrow_id}号标)预奖励取消",0,"@网站管理员@");
			}
			$updata_s['deal_time'] = time();
			$updata_s['reward_status'] = 2;
			M("today_reward")->where("borrow_id={$borrow_id} AND reward_status=0")->save($updata_s);
		}
		/////////////////////////回款续投奖励预奖励取消结束 2013-05-10 fans///////////////////////////////
		$done=true;
		$borrowInvestor->commit();
	}else{
		$borrowInvestor->rollback();
	}
	
	return $done;
}


//借款成功，进入复审处理
function borrowApproved($borrow_id){
	$pre = C('DB_PREFIX');
	$done = false;
	$_P_fee = get_global_setting();
	$invest_integral = $_P_fee['invest_integral'];//投资积分
	$borrowInvestor = D('borrow_investor');
    // borrow_info 借款信息管理表
	$binfo = M("borrow_info")->field("borrow_type,reward_type,reward_num,borrow_fee,borrow_money,borrow_uid,borrow_duration,repayment_type")->find($borrow_id);
	$investorList = $borrowInvestor->field('id,borrow_id,investor_uid,investor_capital,investor_interest,reward_money')->where("borrow_id={$borrow_id}")->select();
    
	//$endTime = strtotime(date("Y-m-d",time())." 23:59:59");
	//借款天数、还款时间
	$endTime = strtotime(date("Y-m-d",time())." ".$_P_fee['back_time']);
	if($binfo['borrow_type']==3 || $binfo['repayment_type']==1){//天标或秒标
		$deadline_last = strtotime("+{$binfo['borrow_duration']} day",$endTime);
	}else{//月标
		$deadline_last = strtotime("+{$binfo['borrow_duration']} month",$endTime);
	} 
	$getIntegralDays = intval(($deadline_last-$endTime)/3600/24);//借款天数
	
	//////////////////////////////////
     
	$borrowInvestor->startTrans();
    try{  //捕获错误异常
	    //更新投资概要
	    $_investor_num = count($investorList);
        
	    foreach($investorList as $key=>$v){
		    $_reward_money=0;
		    if($binfo['reward_type']>0){
			    $investorList[$key]['reward_money'] = getFloatValue($v['investor_capital']*$binfo['reward_num']/100,4);
		    }else{
				$investorList[$key]['reward_money'] = 0;
			}

		    MTip('chk14',$v['investor_uid'],$borrow_id);//sss
		    $upsummary_res = M()->execute("update `{$pre}borrow_investor` set `deadline`={$deadline_last},`status`=4,`reward_money`='".$investorList[$key]['reward_money']."' WHERE `id`={$v['id']} ");
	    }
	    //更新投资概要
	    //更新借款信息
	    $upborrow_res = M()->execute("update `lzh_borrow_info` set `deadline`={$deadline_last},`borrow_status`=6  WHERE `id`={$borrow_id}");
	    //更新借款信息
	    //更新投资详细

	    switch($binfo['repayment_type']){
		    case 2://每月还款
		    case 3://每季还本
		    case 4://期未还本
			    for($i=1;$i<=$binfo['borrow_duration'];$i++){
				    $deadline=0;
				    $deadline=strtotime("+{$i} month",$endTime);
				    $updetail_res = M()->execute("update `lzh_investor_detail` set `deadline`={$deadline},`status`=7 WHERE `borrow_id`={$borrow_id} AND `sort_order`=$i");
			    }
		    break;
		    case 1://按天一次性还款
			case 5://一次性还款
				    $deadline=0;
				    $deadline=$deadline_last;
				    $updetail_res = M()->execute("update `{$pre}investor_detail` set `deadline`={$deadline},`status`=7 WHERE `borrow_id`={$borrow_id}");
		    break;
	    }		
        
        if($updetail_res && $upsummary_res && $upborrow_res){
            $done=true;
            $borrowInvestor->commit();
        }else{
			$done=false;
			$borrowInvestor->rollback();
		}
    }catch(Exception $e){
        $done=false;
        $borrowInvestor->rollback();
    }
    
    
	//更新投资详细

	// 当以上操作没有异常正确执行后执行下面的工作
	if($done){
		//借款者帐户
			$_P_fee=get_global_setting();
			
			$_borraccount = memberMoneyLog($binfo['borrow_uid'],17,$binfo['borrow_money'],"第{$borrow_id}号标复审通过，借款金额入帐");//借款入帐
		if(!$_borraccount) return false;//借款者帐户处理出错
			$_borrfee = memberMoneyLog($binfo['borrow_uid'],18,-$binfo['borrow_fee'],"第{$borrow_id}号标借款成功，扣除借款管理费");//借款
		if(!$_borrfee) return false;//借款者帐户处理出错
			$_freezefee = memberMoneyLog($binfo['borrow_uid'],19,-$binfo['borrow_money']*$_P_fee['money_deposit']/100,"第{$borrow_id}号标借款成功，冻结{$_P_fee['money_deposit']}%的保证金");//冻结保证金
			
		if(!$_freezefee) return false;//借款者帐户处理出错
		//借款者帐户
		//投资者帐户
		$_investor_num = count($investorList);
		$_remoney_do = true;
		foreach($investorList as $v){
			
			//////////////////////////增加投资者的投资积分 2013-08-28 fans////////////////////////////////////
		
			$integ = intval($v['investor_capital']*$getIntegralDays*$invest_integral/1000);
			//$reintegral = memberIntegralLog($v['investor_uid'],2,$integ,"第{$borrow_id}号标复审通过，应获积分");
			$reintegral = memberIntegralLog($v['investor_uid'],2,$integ,"第{$borrow_id}号标复审通过，应获积分：".$integ."分,投资金额：".$v['investor_capital']."元,投资天数：".$getIntegralDays."天");
			if(isBirth($v['investor_uid'])){
				$reintegral = memberIntegralLog($v['investor_uid'],2,$integ,"亲，祝您生日快乐，本站特赠送您{$integ}积分作为礼物，以表祝福。");
			}
			//////////////////////////增加投资者的投资积分 2013-08-28 fans////////////////////////////////////
			
			//////////////////////////处理待收金额为负的问题/////////////////////
			$wmap['investor_uid'] = $v['investor_uid'];
			$wmap['borrow_id'] = $v['borrow_id'];
			$daishou = M('investor_detail')->field('interest')->where("investor_uid = {$v['investor_uid']} and borrow_id = {$v['borrow_id']} and invest_id ={$v['id']}")->sum('interest');//待收金额
			//dump($daishou);exit;
			//////////////////////////处理待收金额为负的问题/////////////////////
			//投标奖励
			if($v['reward_money']>0){
				$_remoney_do = false;
				$_reward_m = memberMoneyLog($v['investor_uid'],20,$v['reward_money'],"第{$borrow_id}号标复审通过，获取投标奖励",$binfo['borrow_uid']);
				$_reward_m_give = memberMoneyLog($binfo['borrow_uid'],21,-$v['reward_money'],"第{$borrow_id}号标复审通过，支付投标奖励",$v['investor_uid']);
				if($_reward_m && $_reward_m_give) $_remoney_do = true;
			}
			//投标奖励
			
			$remcollect = memberMoneyLog($v['investor_uid'],15,$v['investor_capital'],"第{$borrow_id}号标复审通过，冻结本金成为待收金额",$binfo['borrow_uid']);
			//$reinterestcollect = memberMoneyLog($v['investor_uid'],28,$v['investor_interest'],"第{$borrow_id}号标复审通过，应收利息成为待收金额",$binfo['borrow_uid']);
			$reinterestcollect = memberMoneyLog($v['investor_uid'],28,$daishou,"第{$borrow_id}号标复审通过，应收利息成为待收利息",$binfo['borrow_uid']);


			//////////////////////邀请奖励开始////////////////////////////////////////
			if(3  != $binfo['borrow_type']){
				$vo = M('members')->field('id,user_phone,user_name,recommend_id')->find($v['investor_uid']);
				$_rate = $_P_fee['award_invest']/1000;//推广奖励
				$jiangli = getFloatValue($_rate * $v['investor_capital'],2);
				if($vo['recommend_id']!=0){
					//if(($binfo['borrow_type']=='1' || $binfo['borrow_type']=='2' || $binfo['borrow_type']=='5') && $binfo['repayment_type']!='1'){
					PromotionAward($vo,$borrow_id);
					//}
				}
			}

			/////////////////////邀请奖励结束/////////////////////////////////////////
			
		}
		if(!$_remoney_do||!$remcollect||!$reinterestcollect) return false;//投资者帐户处理出错

		/////////////////////////回款续投奖励预奖励开始 2013-05-10 fans///////////////////////////////
		$listreward =M("today_reward t")->field("t.reward_uid,t.reward_money,m.user_name,m.user_phone")->join(C('DB_PREFIX').'members m ON m.id=t.reward_uid')->where("t.borrow_id={$borrow_id} AND t.reward_status=0")->select();
		if(!empty($listreward))
		{
			foreach($listreward as $v) {
				membermoneylog($v['reward_uid'],34,$v['reward_money'],"续投奖励({$borrow_id}号标)奖励到账",0,"@网站管理员@");
				SMStip("investment",$v['user_phone'],array("#USERANEM#","#ID#",'#MONEY#'),array($v['user_name'],$borrow_id,$v['reward_money']));
			}
			$updata_s['deal_time'] = time();
			$updata_s['reward_status'] = 1;
			M("today_reward")->where("borrow_id={$borrow_id} AND reward_status=0")->save($updata_s);
		}


		/////////////////////////回款续投奖励预奖励取消结束 2013-05-10 fans///////////////////////////////
	}
    
	return $done;
}

//邀请好友奖励

function PromotionAward($vo,$borrow_id){
	$glo = get_global_setting();
	$level = explode('|',$glo['recrew']);

	$o = explode('-',$level[0]);

	$capital = get_personal_benefit($vo['id']);
	$capital = $capital['capital_collection'];

	if($o[0]> $capital){
		return false;
	}

	$recommendlog = M('recommendlog')->where("uid = {$vo['id']} and recommend={$vo['recommend_id']}")->field('level')->select();
	if($recommendlog){
		foreach($recommendlog as $val){
			$curlevel[]=$val['level'];
		}
	}

	$toUser = M('members')->field('id,user_phone,user_name,recommend_id')->find($vo['recommend_id']);

	foreach($level as $key=>$val)
	{
		list($nums,$money)=explode('-',$val);
		if(!in_array($key,$curlevel) && $capital >= $nums){
			$result = memberMoneyLog($vo['recommend_id'],13,$money,$vo['user_name']."投资成功超过".$nums."元，你获得推广奖励".$money."元。",$vo['id']);
			if($result){
				false == empty($toUser['user_phone']) && SMStip("invitefriends",$toUser['user_phone'],array("#USERANEM#","#MONEY#",'#PRIZE#'),array($toUser['user_name'],$nums,$money));
				M('recommendlog')->add(array(
					'uid'=>$vo['id'],
					'recommend' =>$vo['recommend_id'],
					'level'=>$key,
					'money'=>$money,
					'levelnums'=>$nums,
					'bid'=>$borrow_id,
					'dateline' =>time()
				));
			}
		}
	}
}


function lastRepayment($binfo){
	$x=true;//因为下面有!x的判断，所以为了避免影响其他标，这里默认为true
	if($binfo['borrow_type']==2){
		$x=false;
		//返回借款人的借款担保额度
		$x = memberLimitLog($binfo['borrow_uid'],8,($binfo['borrow_money']),$info="{$binfo['id']}号标还款完成");
		if(!$x) return false;
		//返回投资人的投资担保额度
		$vocuhlist = M('borrow_vouch')->field("uid,vouch_money")->where("borrow_id={$binfo['id']}")->select();
		foreach($vocuhlist as $vv){
			$x = memberLimitLog($vv['uid'],10,($vv['vouch_money']),$info="您担保的{$binfo['id']}号标还款完成");
		}
	}elseif($binfo['borrow_type']==1){
		$x=false;
		$x = memberLimitLog($binfo['borrow_uid'],7,($binfo['borrow_money']),$info="{$binfo['id']}号标还款完成");
	}
	//如果是担保
	
	if(!$x) return false;
	


	//解冻保证金
	$_P_fee=get_global_setting();
	$accountMoney_borrower = M('member_money')->field('account_money,money_collect,money_freeze,back_money')->find($binfo['borrow_uid']);
	$datamoney_x['uid'] = $binfo['borrow_uid'];
	$datamoney_x['type'] = 24;
	$datamoney_x['affect_money'] = ($binfo['borrow_money']*$_P_fee['money_deposit']/100);
	$datamoney_x['account_money'] = ($accountMoney_borrower['account_money'] + $datamoney_x['affect_money']);
	$datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
	$datamoney_x['freeze_money'] = ($accountMoney_borrower['money_freeze']-$datamoney_x['affect_money']);
	$datamoney_x['back_money'] = $accountMoney_borrower['back_money'];
	
	//会员帐户
	$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
	$mmoney_x['money_collect']=$datamoney_x['collect_money'];
	$mmoney_x['account_money']=$datamoney_x['account_money'];
	$mmoney_x['back_money']=$datamoney_x['back_money'];
	
	//会员帐户
	$datamoney_x['info'] = "网站对{$binfo['id']}号标还款完成的解冻保证金";
	$datamoney_x['add_time'] = time();
	$datamoney_x['add_ip'] = get_client_ip();
	$datamoney_x['target_uid'] = 0;
	$datamoney_x['target_uname'] = '@网站管理员@';
	$moneynewid_x = M('member_moneylog')->add($datamoney_x);
	if($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
	//解冻保证金
	
	if($bxid && $x) return true;
	else return false;
}


//还款处理
function borrowRepayment($borrow_id,$sort_order,$type=1){//type 1:会员自己还,2网站代还
	$pre = C('DB_PREFIX');
	$done = false;
	$borrowDetail = D('investor_detail');
	$binfo = M("borrow_info")->field("id,borrow_uid,borrow_type,borrow_money,borrow_duration,repayment_type,has_pay,total,deadline")->find($borrow_id);
	$b_member=M('members')->field("user_name")->find($binfo['borrow_uid']);
	if( $binfo['has_pay']>=$sort_order) return "本期已还过，不用再还";
	if( $binfo['has_pay'] == $binfo['total'])  return "此标已经还完，不用再还";
	if( ($binfo['has_pay']+1)<$sort_order) return "对不起，此借款第".($binfo['has_pay']+1)."期还未还，请先还第".($binfo['has_pay']+1)."期";
	if( $binfo['deadline']>time() && $type==2)  return "此标还没逾期，不用代还";
	//企业直投与普通标,判断还款期数不一样
	$voxe = $borrowDetail->field('sort_order,sum(capital) as capital, sum(interest) as interest,sum(interest_fee) as interest_fee,deadline,substitute_time')->where("borrow_id={$borrow_id}")->group('sort_order')->select();
	foreach($voxe as $ee=>$ss){
		if($ss['sort_order']==$sort_order) $vo = $ss;
	}
	
	if($vo['deadline']<time()){//此标已逾期
		$is_expired = true;
		if($vo['substitute_time']>0) $is_substitute=true;//已代还
		else $is_substitute=false;
		//逾期的相关计算
		$expired_days = getExpiredDays($vo['deadline']);
		$expired_money = getExpiredMoney($expired_days,$vo['capital'],$vo['interest']);
		$call_fee = getExpiredCallFee($expired_days,$vo['capital'],$vo['interest']);
		//逾期的相关计算
	}else{
		$is_expired = false;
		$expired_days = 0;
		$expired_money = 0;
		$call_fee = 0;
	}
	//企业直投与普通标,判断还款期数不一样
	MTip('chk25',$binfo['borrow_uid'],$borrow_id);//sss
	$accountMoney_borrower = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
	if($type==1 && $binfo['borrow_type']<>3 && ($accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'])<($vo['capital']+$vo['interest']+$expired_money+$call_fee)) return "帐户可用余额不足，本期还款共需".($vo['capital']+$vo['interest']+$expired_money+$call_fee)."元，请先充值";
	if($is_substitute && $is_expired){//已代还后的会员还款，则只需要对会员的帐户进行操作后然后更新还款时间即可返回
		$borrowDetail->startTrans();
			$datamoney_x['uid'] = $binfo['borrow_uid'];
			$datamoney_x['type'] = 11;
			$datamoney_x['affect_money'] = -($vo['capital']+$vo['interest']);
			if(($datamoney_x['affect_money']+$accountMoney_borrower['back_money'])<0){//如果需要还款的金额大于回款资金池资金总额
				$datamoney_x['account_money'] = $accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];
				$datamoney_x['back_money'] = 0;
			}else{
				$datamoney_x['account_money'] = $accountMoney_borrower['account_money'];
				$datamoney_x['back_money'] = $accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
			}	
			$datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
			$datamoney_x['freeze_money'] = $accountMoney_borrower['money_freeze'];
			
			//会员帐户
			$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
			$mmoney_x['money_collect']=$datamoney_x['collect_money'];
			$mmoney_x['account_money']=$datamoney_x['account_money'];
			$mmoney_x['back_money']=$datamoney_x['back_money'];
			//会员帐户
			$datamoney_x['info'] = "对{$borrow_id}号标第{$sort_order}期还款";
			$datamoney_x['add_time'] = time();
			$datamoney_x['add_ip'] = get_client_ip();
			$datamoney_x['target_uid'] = 0;
			$datamoney_x['target_uname'] = '@网站管理员@';
			$moneynewid_x = M('member_moneylog')->add($datamoney_x);
			if($moneynewid_x) $bxid_1 = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
		//逾期了
			//逾期罚息
			$accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
			$datamoney_x = array();
			$mmoney_x=array();
			
			$datamoney_x['uid'] = $binfo['borrow_uid'];
			$datamoney_x['type'] = 30;
			$datamoney_x['affect_money'] = -($expired_money);
			if(($datamoney_x['affect_money']+$accountMoney['back_money'])<0){//如果需要还款的逾期罚息金额大于回款资金池资金总额
				$datamoney_x['account_money'] = $accountMoney['account_money']+$accountMoney['back_money'] + $datamoney_x['affect_money'];
				$datamoney_x['back_money'] = 0;
			}else{
				$datamoney_x['account_money'] = $accountMoney['account_money'];
				$datamoney_x['back_money'] = $accountMoney['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
			}	
			$datamoney_x['collect_money'] = $accountMoney['money_collect'];
			$datamoney_x['freeze_money'] = $accountMoney['money_freeze'];
			
			//会员帐户
			$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
			$mmoney_x['money_collect']=$datamoney_x['collect_money'];
			$mmoney_x['account_money']=$datamoney_x['account_money'];
			$mmoney_x['back_money']=$datamoney_x['back_money'];
			//会员帐户
			$datamoney_x['info'] = "{$borrow_id}号标第{$sort_order}期的逾期罚息";
			$datamoney_x['add_time'] = time();
			$datamoney_x['add_ip'] = get_client_ip();
			$datamoney_x['target_uid'] = 0;
			$datamoney_x['target_uname'] = '@网站管理员@';
			$moneynewid_x = M('member_moneylog')->add($datamoney_x);
			if($moneynewid_x) $bxid_2 = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
			
			//催收费
			$accountMoney_2 = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
			$datamoney_x = array();
			$mmoney_x=array();
			
			$datamoney_x['uid'] = $binfo['borrow_uid'];
			$datamoney_x['type'] = 31;
			$datamoney_x['affect_money'] = -($call_fee);
			if(($datamoney_x['affect_money']+$accountMoney_2['back_money'])<0){//如果需要还款的催收费金额大于回款资金池资金总额
				$datamoney_x['account_money'] = $accountMoney_2['account_money']+$accountMoney_2['back_money'] + $datamoney_x['affect_money'];
				$datamoney_x['back_money'] = 0;
			}else{
				$datamoney_x['account_money'] = $accountMoney_2['account_money'];
				$datamoney_x['back_money'] = $accountMoney_2['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
			}	
			$datamoney_x['collect_money'] = $accountMoney_2['money_collect'];
			$datamoney_x['freeze_money'] = $accountMoney_2['money_freeze'];
				
			//会员帐户
			$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
			$mmoney_x['money_collect']=$datamoney_x['collect_money'];
			$mmoney_x['account_money']=$datamoney_x['account_money'];
			$mmoney_x['back_money']=$datamoney_x['back_money'];
			//会员帐户
			$datamoney_x['info'] = "网站对借款人收取的第{$borrow_id}号标第{$sort_order}期的逾期催收费";
			$datamoney_x['add_time'] = time();
			$datamoney_x['add_ip'] = get_client_ip();
			$datamoney_x['target_uid'] = 0;
			$datamoney_x['target_uname'] = '@网站管理员@';
			$moneynewid_x = M('member_moneylog')->add($datamoney_x);
			if($moneynewid_x) $bxid_3 = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
		
		//逾期了
			$updetail_res = M()->execute("update `{$pre}investor_detail` set `repayment_time`=".time().",`status`=5 WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order}");
			//更新借款信息
			$upborrowsql = "update `{$pre}borrow_info` set ";
			$upborrowsql .= ",`substitute_money`=0";
			$upborrowsql .= ",`borrow_status`=10";//会员在网站代还后，逾期还款
			$upborrowsql .= "`repayment_money`=`repayment_money`+{$vo['capital']}";
			$upborrowsql .= ",`repayment_interest`=`repayment_interest`+ {$vo['interest']}";
			if ( $sort_order == $binfo['total'] )
			{
				$upborrowsql .= ",`borrow_status`=7";
			}
			$upborrowsql .= ",`has_pay`={$sort_order}";
			if ( $is_expired )
			{
				$upborrowsql .= ",`expired_money`=`expired_money`+{$expired_money}";
			}
			$upborrowsql .= " WHERE `id`={$borrow_id}";
			$upborrow_res = M()->execute($upborrowsql);
			//更新借款信息

		if($updetail_res&&$bxid_1&&$bxid_2&&$bxid_3&&$upborrow_res){
		//if($updetail_res&&$upborrow_res){
			$borrowDetail->commit() ;
            //撤销转让的债权 ,完成还款更改债权转让状态
            cancelDebt($borrow_id);
			return true;
		}else{
			$borrowDetail->rollback() ;
			return false;
		}
	}



	
	//企业直投与普通标,判断还款期数不一样
	  $detailList = $borrowDetail->field('invest_id,investor_uid,capital,interest,interest_fee,borrow_id,total')->where("borrow_id={$borrow_id} AND sort_order={$sort_order}")->select();
	//企业直投与普通标,判断还款期数不一样
	
	/*************************************逾期还款积分与还款状态处理开始 20130509 fans***********************************/
	$datag = get_global_setting();
	$credit_borrow = explode("|",$datag['credit_borrow']);
	if($type==1){//客户自己还款才需要记录这些操作
		$day_span = ceil(($vo['deadline']-time())/(3600*24));
		$credits_money = intval($vo['capital']/$credit_borrow[4]);
		$credits_info = "对第{$borrow_id}号标的还款操作,获取投资积分";
		if($day_span>=0 && $day_span<1){//正常还款
			//$credits_result = memberCreditsLog($binfo['borrow_uid'],3,$credits_money*$credit_borrow[0],$credits_info);
			$credits_result = memberIntegralLog($binfo['borrow_uid'],1,intval($vo['capital']/1000),"对第{$borrow_id}号标进行了正常的还款操作,获取投资积分");//还款积分处理
			$idetail_status=1;
		}elseif($day_span>=-3 && $day_span<0){//迟还
			$credits_result = memberCreditsLog($binfo['borrow_uid'],4,$credits_money*$credit_borrow[1],"对第{$borrow_id}号标的还款操作(迟到还款),扣除信用积分");
			$idetail_status=3;
		}elseif($day_span<-3){//逾期还款
			$credits_result = memberCreditsLog($binfo['borrow_uid'],5,$credits_money*$credit_borrow[2],"对第{$borrow_id}号标的还款操作(逾期还款),扣除信用积分");
			$idetail_status=5;
		}elseif($day_span>=1){//提前还款
			//$credits_result = memberCreditsLog($binfo['borrow_uid'],6,$credits_money*$credit_borrow[3],$credits_info);
			$credits_result = memberIntegralLog($binfo['borrow_uid'],1,intval($vo['capital'] * $day_span/1000),"对第{$borrow_id}号标进行了提前还款操作,获取投资积分");//还款积分处理
			$idetail_status=2;
		}
		if(!$credits_result) return "因积分记录失败，未完成还款操作";
	}
	/*************************************逾期还款积分与还款状态处理结束 20130509 fans***********************************/
	
	$borrowDetail->startTrans();
	//对借款者帐户进行减少
	$bxid = true;
	if($type==1){
		$bxid = false;
			$datamoney_x['uid'] = $binfo['borrow_uid'];
			$datamoney_x['type'] = 11;
			$datamoney_x['affect_money'] = -($vo['capital']+$vo['interest']);
			
			$datamoney_x1['affect_money'] = -($vo['capital']);
			$datamoney_x2['affect_money'] = -($vo['interest']);
			if(($datamoney_x1['affect_money']+$accountMoney_borrower['back_money'])<0){//如果需要还款的金额大于回款资金池资金总额
				$datamoney_x1['account_money'] = floatval($accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x1['affect_money']);
				$datamoney_x1['back_money'] = 0;
			}else{
				$datamoney_x1['account_money'] = $accountMoney_borrower['account_money'];
				$datamoney_x1['back_money'] = floatval($accountMoney_borrower['back_money']) + $datamoney_x1['affect_money'];//回款资金注入回款资金池
			}
			if(($datamoney_x2['affect_money']+$accountMoney_borrower['back_money'])<0){//如果需要还款的金额大于回款资金池资金总额
				$datamoney_x2['account_money'] = floatval($accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x2['affect_money']+$datamoney_x1['affect_money']);
				$datamoney_x2['back_money'] = 0;
			}else{
				$datamoney_x2['account_money'] = $accountMoney_borrower['account_money'];
				$datamoney_x2['back_money'] = floatval($accountMoney_borrower['back_money']) + $datamoney_x2['affect_money']+$datamoney_x1['affect_money'];//回款资金注入回款资金池
			}

			//end
			if(($datamoney_x['affect_money']+$accountMoney_borrower['back_money'])<0){//如果需要还款的金额大于回款资金池资金总额
				$datamoney_x['account_money'] = floatval($accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x['affect_money']);
				$datamoney_x['back_money'] = 0;
			}else{
				$datamoney_x['account_money'] = $accountMoney_borrower['account_money'];
				$datamoney_x['back_money'] = floatval($accountMoney_borrower['back_money']) + $datamoney_x['affect_money'];//回款资金注入回款资金池
			}	
			$datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
			$datamoney_x['freeze_money'] = $accountMoney_borrower['money_freeze'];
			
			//会员帐户
			$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
			$mmoney_x['money_collect']=$datamoney_x['collect_money'];
			$mmoney_x['account_money']=$datamoney_x['account_money'];
			$mmoney_x['back_money']=$datamoney_x['back_money'];
			
			//会员帐户
			$datamoney_x['info'] = "对{$borrow_id}号标第{$sort_order}期还款";
			$datamoney_x['add_time'] = time();
			$datamoney_x['add_ip'] = get_client_ip();
			$datamoney_x['target_uid'] = 0;
			$datamoney_x['target_uname'] = '@网站管理员@';
			//$moneynewid_x = M('member_moneylog')->add($datamoney_x);

			$datamoney_x1['uid'] = $binfo['borrow_uid'];
			$datamoney_x1['type'] = 56;
			$datamoney_x1['info'] = "对{$borrow_id}号标第{$sort_order}期还款";
			$datamoney_x1['add_time'] = time();
			$datamoney_x1['add_ip'] = get_client_ip();
			$datamoney_x1['target_uid'] = 0;
			$datamoney_x1['target_uname'] = '@网站管理员@';
			$datamoney_x2['uid'] = $binfo['borrow_uid'];
			$datamoney_x2['type'] = 57;
			$datamoney_x2['info'] = "对{$borrow_id}号标第{$sort_order}期还款";
			$datamoney_x2['add_time'] = time();
			$datamoney_x2['add_ip'] = get_client_ip();
			$datamoney_x2['target_uid'] = 0;
			$datamoney_x2['target_uname'] = '@网站管理员@';
			if(intval($vo['capital'])==0){
				$moneynewid_x1 = 1;
			}else{
				$moneynewid_x1 = M('member_moneylog')->add($datamoney_x1);
			}
			
			$moneynewid_x2 = M('member_moneylog')->add($datamoney_x2);
			if($moneynewid_x1&&$moneynewid_x2) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
			
		//逾期了
		if($is_expired){
			//逾期罚息
			if($expired_money>0){
				$accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
				$datamoney_x = array();
				$mmoney_x=array();
				
				$datamoney_x['uid'] = $binfo['borrow_uid'];
				$datamoney_x['type'] = 30;
				$datamoney_x['affect_money'] = -($expired_money);
				if(($datamoney_x['affect_money']+$accountMoney['back_money'])<0){//如果需要还款的逾期罚息金额大于回款资金池资金总额
					$datamoney_x['account_money'] = $accountMoney['account_money']+$accountMoney['back_money'] + $datamoney_x['affect_money'];
					$datamoney_x['back_money'] = 0;
				}else{
					$datamoney_x['account_money'] = $accountMoney['account_money'];
					$datamoney_x['back_money'] = $accountMoney['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
				}	
				$datamoney_x['collect_money'] = $accountMoney['money_collect'];
				$datamoney_x['freeze_money'] = $accountMoney['money_freeze'];
				
				//会员帐户
				$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
				$mmoney_x['money_collect']=$datamoney_x['collect_money'];
				$mmoney_x['account_money']=$datamoney_x['account_money'];
				$mmoney_x['back_money']=$datamoney_x['back_money'];
				
				//会员帐户
				$datamoney_x['info'] = "{$borrow_id}号标第{$sort_order}期的逾期罚息";
				$datamoney_x['add_time'] = time();
				$datamoney_x['add_ip'] = get_client_ip();
				$datamoney_x['target_uid'] = 0;
				$datamoney_x['target_uname'] = '@网站管理员@';
				$moneynewid_x = M('member_moneylog')->add($datamoney_x);
				if($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
			}
			
			//催收费
			if($call_fee>0){
				$accountMoney_borrower = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
				$datamoney_x = array();
				$mmoney_x=array();
				
				$datamoney_x['uid'] = $binfo['borrow_uid'];
				$datamoney_x['type'] = 31;
				$datamoney_x['affect_money'] = -($call_fee);
				if(($datamoney_x['affect_money']+$accountMoney_borrower['back_money'])<0){//如果需要还款的催收费金额大于回款资金池资金总额
					$datamoney_x['account_money'] = $accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];
					$datamoney_x['back_money'] = 0;
				}else{
					$datamoney_x['account_money'] = $accountMoney_borrower['account_money'];
					$datamoney_x['back_money'] = $accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
				}	
				$datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
				$datamoney_x['freeze_money'] = $accountMoney_borrower['money_freeze'];
				
				//会员帐户
				$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
				$mmoney_x['money_collect']=$datamoney_x['collect_money'];
				$mmoney_x['account_money']=$datamoney_x['account_money'];
				$mmoney_x['back_money']=$datamoney_x['back_money'];
				
				//会员帐户
				$datamoney_x['info'] = "网站对借款人收取的第{$borrow_id}号标第{$sort_order}期的逾期催收费";
				$datamoney_x['add_time'] = time();
				$datamoney_x['add_ip'] = get_client_ip();
				$datamoney_x['target_uid'] = 0;
				$datamoney_x['target_uname'] = '@网站管理员@';
				$moneynewid_x = M('member_moneylog')->add($datamoney_x);
				if($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
			}
		}
		//逾期了


	}
	//对借款者帐户进行减少
	//更新借款信息
	$upborrowsql = "update `{$pre}borrow_info` set ";
	$upborrowsql .= "`repayment_money`=`repayment_money`+{$vo['capital']}";
	$upborrowsql .= ",`repayment_interest`=`repayment_interest`+ {$vo['interest']}";
	//if($sort_order == $binfo['total']) $upborrowsql .= ",`borrow_status`=7";//还款完成
	$upborrowsql .= ",`has_pay`={$sort_order}";
	//如果是网站代还的，则记录代还金额
	if($type==2){
		$total_subs = ($vo['capital']+$vo['interest']);
		$upborrowsql .= ",`substitute_money`=`substitute_money`+ {$total_subs}";
		//$upborrowsql .= ",`has_pay`={$binfo['has_pay']}+1";//网站代还款完成
		if( $binfo['has_pay']+1 == $binfo['total']){
			$upborrowsql .= ",`borrow_status`=9";//网站代还款完成
		}
		
	}
	//如果是网站代还的，则记录代还金额
	if($type==1){
	  	//$upborrowsql .= ",`has_pay`={$sort_order}";//代还则不记录还到第几期，避免会员还款时，提示已还过
		if($sort_order == $binfo['total']){
			$upborrowsql .= ",`borrow_status`=7";//还款完成
		}
	}
	
	if($is_expired)  $upborrowsql .= ",`expired_money`=`expired_money`+{$expired_money}";//代还则不记录还到第几期，避免会员还款时，提示已还过
	$upborrowsql .= " WHERE `id`={$borrow_id}";
	$upborrow_res = M()->execute($upborrowsql);
	//更新借款信息
	
	//更新还款详情表
	if($type==2){//网站代还
		$updetail_res = M()->execute("update `{$pre}investor_detail` set `receive_capital`=`capital`,`substitute_time`=".time()." ,`substitute_money`=`substitute_money`+{$total_subs},`status`=4 WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order}");
	}else if($is_expired){
		$updetail_res = m( )->execute( "update `{$pre}investor_detail` set `receive_capital`=`capital` ,`receive_interest`=(`interest`-`interest_fee`),`repayment_time`=".time().",`call_fee`={$call_fee},`expired_money`={$expired_money},`expired_days`={$expired_days},`status`={$idetail_status} WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order}" );
	}else{
		$updetail_res = M()->execute("update `{$pre}investor_detail` set `receive_capital`=`capital` ,`receive_interest`=(`interest`-`interest_fee`),`repayment_time`=".time().", `status`={$idetail_status} WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order}");
	}
	//更新还款详情表
	
	//更新还款概要表
	$smsUid = "";
	foreach($detailList as $v){
        //用于判断是否债权转让 ,债权转让日志不一样
        $debt = M("invest_detb")->field("serialid")->where("invest_id={$v['invest_id']} and status=1")->find();
      
		$getInterest = $v['interest'] - $v['interest_fee'];
		$upsql = "update `{$pre}borrow_investor` set ";
		$upsql .= "`receive_capital`=`receive_capital`+{$v['capital']},";
		$upsql .= "`receive_interest`=`receive_interest`+ {$getInterest},";
		if($type==2){
			$total_s_invest = $v['capital'] + $getInterest;
			$upsql .= "`substitute_money` = `substitute_money` + {$total_s_invest},";
		}
		if($sort_order == $binfo['total']) $upsql .= "`status`=5,";//还款完成
		$upsql .= "`paid_fee`=`paid_fee`+{$v['interest_fee']}";
		$upsql .= " WHERE `id`={$v['invest_id']}";
		$upinfo_res = M()->execute($upsql);
		
		//对投资帐户进行增加
		if($upinfo_res){
			$accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($v['investor_uid']);
			$datamoney['uid'] = $v['investor_uid'];
			$datamoney['type'] = ($type==2)?"10":"9";
			$datamoney['affect_money'] = ($v['capital']+$v['interest']);//先收利息加本金，再扣管理费
			$datamoney1['affect_money'] = ($v['capital']);
			$datamoney2['affect_money'] = ($v['interest']);
			//$datamoney['account_money'] = $accountMoney['account_money'];
			$datamoney['collect_money'] = $accountMoney['money_collect'] - $datamoney['affect_money'];
			$datamoney1['collect_money'] = $accountMoney['money_collect'] - $datamoney1['affect_money'];
			$datamoney2['collect_money'] = $accountMoney['money_collect'] - $datamoney2['affect_money'];
			$datamoney['freeze_money'] = $accountMoney['money_freeze'];
			$datamoney1['freeze_money'] = $accountMoney['money_freeze'];
			$datamoney2['freeze_money'] = $accountMoney['money_freeze'];
			///////////////秒标回款不进入汇款资金池，也就可实现秒标回款不给回款续投奖励的功能了 2013-08-23 fan//////////////////

			if($binfo['borrow_type']<>3 ){//如果不是秒标，那么回的款会进入回款资金池，如果是秒标，回款则会进入充值资金池
				$datamoney['account_money'] = $accountMoney['account_money'];
				$datamoney1['account_money'] = $accountMoney['account_money'];
				$datamoney2['account_money'] = $accountMoney['account_money'];
				$datamoney['back_money'] = ($accountMoney['back_money'] + $datamoney['affect_money']);
				$datamoney1['back_money'] = ($accountMoney['back_money'] + $datamoney1['affect_money']);
				$datamoney2['back_money'] = ($accountMoney['back_money'] + $datamoney2['affect_money']+$datamoney1['affect_money']);
			}else{
				$datamoney['account_money'] = $accountMoney['account_money'] + $datamoney['affect_money'];
				$datamoney1['account_money'] = $accountMoney['account_money'] + $datamoney1['affect_money'];
				$datamoney2['account_money'] = $accountMoney['account_money'] + $datamoney2['affect_money']+$datamoney1['affect_money'];
				$datamoney['back_money'] = $accountMoney['back_money'];
				$datamoney1['back_money'] = $accountMoney['back_money'];
				$datamoney2['back_money'] = $accountMoney['back_money'];
			}

			///////////////秒标回款不进入汇款资金池，也就可实现秒标回款不给回款续投奖励的功能了 2013-08-23 fan//////////////////
			
			//会员帐户
			$mmoney['money_freeze']=$datamoney['freeze_money'];
			$mmoney['money_collect']=$datamoney['collect_money'];
			$mmoney['account_money']=$datamoney['account_money'];
			$mmoney['back_money']=$datamoney['back_money'];
			//会员帐户
			$datamoney['info'] = ($type==2)?"网站对{$v['borrow_id']}号标第{$sort_order}期代还":"收到会员对{$v['borrow_id']}号标第{$sort_order}期的还款";
            //如果债权流水号存在
            $debt['serialid'] &&  $datamoney['info'] = ($type==2)?"网站对{$debt['serialid']}号债权第{$sort_order}期代还":"收到会员对{$debt['serialid']}号债权第{$sort_order}期的还款";
			$datamoney['add_time'] = time();
			$datamoney['add_ip'] = get_client_ip();
			if($type==2){
				$datamoney['target_uid'] = 0;
				$datamoney['target_uname'] = '@网站管理员@';
			}else{
				$datamoney['target_uid'] = $binfo['borrow_uid'];
				$datamoney['target_uname'] = $b_member['user_name'];
			}
			$datamoney1['uid'] = $datamoney['uid'];
			$datamoney1['type'] = 58;
			$datamoney1['add_time'] = time();
			$datamoney1['add_ip'] = get_client_ip();
			$datamoney1['info'] = $datamoney['info'] ;
			$datamoney1['target_uid'] = $datamoney['target_uid'];
			$datamoney1['target_uname'] = $datamoney['target_uname'];
			$datamoney2['uid'] = $datamoney['uid'];
			$datamoney2['type'] = 59;
			$datamoney2['add_time'] = time();
			$datamoney2['add_ip'] = get_client_ip();
			$datamoney2['info'] = $datamoney['info'] ;
			$datamoney2['target_uid'] = $datamoney['target_uid'];
			$datamoney2['target_uname'] = $datamoney['target_uname'];
			if(intval($v['capital'])==0){
				$moneynewid1 = 1;
			}else{
				$moneynewid1 = M('member_moneylog')->add($datamoney1);	
			}
			$moneynewid2 = M('member_moneylog')->add($datamoney2);
			if($moneynewid1&&$moneynewid2){
				$xid = M('member_money')->where("uid={$datamoney['uid']}")->save($mmoney);
			}
			
			if($type==2){//如果是网站代还
				MTip('chk18',$v['investor_uid'],$borrow_id);//sss
			}else{
				MTip('chk16',$v['investor_uid'],$borrow_id);//sss
			}
			$smsUid .= (empty($smsUid))?$v['investor_uid']:",{$v['investor_uid']}";
			
			//利息管理费
			$xid_z = true;
			if($v['interest_fee']>0 && $type==1){
				$xid_z = false;
				$accountMoney_z = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($v['investor_uid']);
				$datamoney_z['uid'] = $v['investor_uid'];
				$datamoney_z['type'] = 23;
				$datamoney_z['affect_money'] = -($v['interest_fee']);//扣管理费
				
				$datamoney_z['collect_money'] = $accountMoney_z['money_collect'];
				$datamoney_z['freeze_money'] = $accountMoney_z['money_freeze'];
				if(($accountMoney_z['back_money'] + $datamoney_z['affect_money'])<0){
					$datamoney_z['back_money'] =0;
					$datamoney_z['account_money'] = $accountMoney_z['account_money'] +$accountMoney_z['back_money']+ $datamoney_z['affect_money'];
				}else{
					$datamoney_z['account_money'] = $accountMoney_z['account_money'];
					$datamoney_z['back_money'] = ($accountMoney_z['back_money'] + $datamoney_z['affect_money']);
				}
				
				//会员帐户
				$mmoney_z['money_freeze']=$datamoney_z['freeze_money'];
				$mmoney_z['money_collect']=$datamoney_z['collect_money'];
				$mmoney_z['account_money']=$datamoney_z['account_money'];
				$mmoney_z['back_money']=$datamoney_z['back_money'];
				
				//会员帐户
				$datamoney_z['info'] = "网站已将第{$v['borrow_id']}号标第{$sort_order}期还款的利息管理费扣除";
				$datamoney_z['add_time'] = time();
				$datamoney_z['add_ip'] = get_client_ip();
				$datamoney_z['target_uid'] = 0;
				$datamoney_z['target_uname'] = '@网站管理员@';
				$moneynewid_z = M('member_moneylog')->add($datamoney_z);
				if($moneynewid_z) $xid_z = M('member_money')->where("uid={$datamoney_z['uid']}")->save($mmoney_z);
			}
		   //利息管理费
		}
		//对投资帐户进行增加
		
	}
	//更新还款概要表
	//echo "$updetail_res && $upinfo_res && $xid &&$upborrow_res && $bxid && $xid_z";
	if($updetail_res && $upinfo_res && $xid &&$upborrow_res && $bxid && $xid_z){
		$borrowDetail->commit() ;
         //撤销转让的债权 ,完成还款更改债权转让状态
         cancelDebt($borrow_id);
         
		$_last = true;
		if($binfo['total'] == ($binfo['has_pay']+1) && $type==1){
			$_last=false;
			$_is_last = lastRepayment($binfo);//最后一笔还款
			if($_is_last) $_last = true; 
		}
		$done=true;

		$vphone = M("member_info")->field("cell_phone")->where("uid in({$smsUid}) and cell_phone !=''")->select();
		$sphone = "";
		foreach($vphone as $v){
			$sphone.=(empty($sphone))?$v['cell_phone']:",{$v['cell_phone']}";
		}
		//SMStip("payback",$sphone,array("#ID#","#ORDER#"),array($borrow_id,$sort_order));
	}else{
		$borrowDetail->rollback();
	}
	
	return $done;
}

function getBorrowInterestRate($rate,$duration){
	return ($rate/(12*100)*$duration);
}


function getMoneyLog($map,$size){
	if(empty($map['uid'])) return;
	
	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('member_moneylog')->where($map)->count('id');
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}

	$list = M('member_moneylog')->where($map)->order('add_time DESC,id DESC')->limit($Lsql)->select();
	$type_arr = C("MONEY_LOG");
	foreach($list as $key=>$v){
		$list[$key]['type'] = $type_arr[$v['type']];
		/*if($v['affect_money']>0){
			$list[$key]['in'] = $v['affect_money'];
			$list[$key]['out'] = '';
		}else{
			$list[$key]['in'] = '';
			$list[$key]['out'] = $v['affect_money'];
		}*/
	}
	
	$row=array();
	$row['list'] = $list;
	$row['count'] = $count;
	$row['page'] = $page;
	return $row;
}


function memberMoneyLog($uid,$type,$amoney,$info="",$target_uid="",$target_uname="",$fee=0,$backWithMoney){
	$xva = floatval($amoney);
	if(empty($xva)) return true;
	$done = false;
	$MM = M("member_money")->field("money_freeze,money_collect,account_money,back_money")->find($uid);
	if(!is_array($MM)||empty($MM)){
	 	M("member_money")->add(array('uid'=>$uid));
		$MM = M("member_money")->field("money_freeze,money_collect,account_money,back_money")->find($uid);
	}
	$Moneylog = D('member_moneylog');
	if(in_array($type,array("71","72","73"))) $type_save=7;
	else $type_save = $type;
	
	if($target_uname=="" && $target_uid>0){
		$tname = M('members')->getFieldById($target_uid,'user_name');
	}else{
		$tname = $target_uname;
	}
	if($target_uid=="" && $target_uname==""){
		$target_uid=0;
		$tname = '@网站管理员@';	
	}
	$Moneylog->startTrans();
		$data['uid'] = $uid;
		$data['type'] = $type_save;
		$data['info'] = $info;
		$data['target_uid'] = $target_uid;
		$data['target_uname'] = $tname;
		$data['add_time'] = time();
		$data['add_ip'] = get_client_ip();
		switch($type){
		/////////////////////////////////////////
			case 5://撤消提现
				$data['affect_money'] = $amoney;

				/*if(($MM['back_money']+$amoney+$fee)<0){//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
					$data['back_money'] = 0;
					$data['account_money'] = $MM['account_money']+$MM['back_money']+$amoney+$fee;
				}else{
					$data['back_money'] = $MM['back_money'];
					$data['account_money'] = $MM['account_money']+$amoney+$fee;
				}*/
				$data['back_money'] = $MM['back_money']+$backWithMoney;
				$data['account_money'] = $MM['account_money'] + $amoney - $backWithMoney;
				$data['collect_money'] = $MM['money_collect'];
				$data['freeze_money'] = $MM['money_freeze']-$amoney;
					
				$data['collect_money'] = $MM['money_collect'];
				$data['freeze_money'] = $MM['money_freeze']-$amoney;
			break;
			case 4://提现冻结
			//case 5://撤消提现
			case 6://投标冻结
			case 37://投企业直投冻结
				$data['affect_money'] = $amoney;

				if(($MM['back_money']+$amoney+$fee)<0){//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
					$data['back_money'] = 0;
					$data['account_money'] = $MM['account_money']+$MM['back_money']+$amoney+$fee;
				}else{
					$data['back_money'] = $MM['back_money']+$amoney+$fee;
					$data['account_money'] = $MM['account_money'];
				}

				$data['collect_money'] = $MM['money_collect'];
				$data['freeze_money'] = $MM['money_freeze']-$amoney;
			break;
			case 12://提现失败
				$data['affect_money'] = $amoney;
				
				//if(($MM['account_money']+$MM['back_money'])>abs($fee)){
				//	if(($MM['back_money']+$amoney+$fee)<0){//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
				//		$data['back_money'] = 0;
				//		$data['account_money'] = $MM['account_money']+$MM['back_money']+$amoney+$fee;
				//	}else{
				//		$data['back_money'] = $MM['back_money']+$amoney+$fee;
				//		$data['account_money'] = $MM['account_money'];
				//	}
				//	$data['collect_money'] = $MM['money_collect'];
				//	$data['freeze_money'] = $MM['money_freeze']-$amoney;
				//}else{
					if(($MM['back_money']+$amoney+$fee)<0){//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
						$data['back_money'] = 0;
						$data['account_money'] = $MM['account_money']+$MM['back_money']+$amoney;
					}else{
						$data['back_money'] = $MM['back_money']+$amoney;
						$data['account_money'] = $MM['account_money'];
					}
					$data['collect_money'] = $MM['money_collect'];
					$data['freeze_money'] = $MM['money_freeze']-$amoney+$fee;
				//}
			break;
			
			case 29://提现成功
				$data['affect_money'] = $amoney;
				$data['account_money'] = $MM['account_money'];
				$data['back_money'] = $MM['back_money'];
				$data['collect_money'] = $MM['money_collect'];
				$data['freeze_money'] = $MM['money_freeze']+$amoney+$fee;
			break;
			case 36://提现通过，处理中
				$data['affect_money'] = $amoney;
				//if(($MM['account_money']+$MM['back_money'])>abs($fee)){
				//	if(($MM['back_money']+$fee)<0){//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
				//		$data['account_money'] = $MM['account_money']+$MM['back_money']+$fee;
				//		$data['back_money'] = 0;
				//	}else{
				//		$data['account_money'] = $MM['account_money'];
				//		$data['back_money'] = $MM['back_money']+$fee;
				//	}
				//	$data['collect_money'] = $MM['money_collect'];
				//	$data['freeze_money'] = $MM['money_freeze'];
				//}else{
					$data['account_money'] =$MM['account_money'];
					$data['back_money'] = $MM['back_money'];
					$data['collect_money'] = $MM['money_collect'];
					$data['freeze_money'] = $MM['money_freeze']+$fee;
				//}
			break;
		////////////////////////////////////////
			
			case 8://流标解冻
			case 19://借款保证金
			case 24://还款完成解冻
			case 34://预投标奖励撤销
				$data['affect_money'] = $amoney;
				if(($MM['account_money']+$amoney)<0){
					$data['account_money'] = 0;
					$data['back_money'] = $MM['account_money']+$MM['back_money']+$amoney;
				}else{
					$data['account_money'] = $MM['account_money']+$amoney;
					$data['back_money'] = $MM['back_money'];
				}
				$data['collect_money'] = $MM['money_collect'];
				$data['freeze_money'] = $MM['money_freeze'];
				//$data['freeze_money'] = $MM['money_freeze']-$amoney;
			break;
			case 3://会员充值
			case 17://借款金额入帐
			case 18://借款管理费
			case 20://投标奖励
			case 21://支付投标奖励
			case 40://企业直投续投奖励
			case 41://企业直投投标奖励
			case 42://支付企业直投投标奖励
				$data['affect_money'] = $amoney;
				if(($MM['account_money']+$amoney)<0){
					$data['account_money'] = 0;
					$data['back_money'] = $MM['account_money']+$MM['back_money']+$amoney;
				}else{
					$data['account_money'] = $MM['account_money']+$amoney;
					$data['back_money'] = $MM['back_money'];
				}
				$data['collect_money'] = $MM['money_collect'];
				$data['freeze_money'] = $MM['money_freeze'];
			break;
			case 9://会员还款
			case 10://网站代还
				$data['affect_money'] = $amoney;
				$data['account_money'] = $MM['account_money'];
				$data['collect_money'] = $MM['money_collect']-$amoney;
				$data['freeze_money'] = $MM['money_freeze'];
				$data['back_money'] = $MM['back_money']+$amoney;
			break;
			case 15://投标成功冻结资金转为待收资金
			case 39://企业直投投标成功冻结资金转为待收资金
				$data['affect_money'] = $amoney;
				$data['account_money'] = $MM['account_money'];
				$data['collect_money'] = $MM['money_collect']+$amoney;
				$data['freeze_money'] = $MM['money_freeze']-$amoney;
				$data['back_money'] = $MM['back_money'];
			break;
			case 28://投标成功利息待收
			case 38://企业直投投标成功利息待收
			case 73://单独操作待收金额
				$data['affect_money'] = $amoney;
				$data['account_money'] = $MM['account_money'];
				$data['collect_money'] = $MM['money_collect']+$amoney;
				$data['freeze_money'] = $MM['money_freeze'];
				$data['back_money'] = $MM['back_money'];
			break;
			case 72://单独操作冻结金额
			case 33://续投奖励(预奖励)
			case 35://续投奖励(取消)
				$data['affect_money'] = $amoney;
				$data['account_money'] = $MM['account_money'];
				$data['collect_money'] = $MM['money_collect'];
				$data['freeze_money'] = $MM['money_freeze']+$amoney;
				$data['back_money'] = $MM['back_money'];
			break;
			case 71://单独操作可用余额
			default:
				$data['affect_money'] = $amoney;
				if(($MM['account_money']+$amoney)<=0){
					$data['account_money'] = 0;
					$data['back_money'] = $MM['account_money']+$MM['back_money']+$amoney;
				}else{
					$data['account_money'] = $MM['account_money']+$amoney;
					$data['back_money'] = $MM['back_money'];
				}
				//$data['account_money'] = $MM['account_money']+$amoney;
				$data['collect_money'] = $MM['money_collect'];
				$data['freeze_money'] = $MM['money_freeze'];
				//$data['back_money'] = $MM['back_money'];
			break;
			
		}
		if(33 != $type){
			$newid = M('member_moneylog')->add($data);
		}
		//帐户更新
		$mmoney['money_freeze']=$data['freeze_money'];
		$mmoney['money_collect']=$data['collect_money'];
		$mmoney['account_money']=$data['account_money'];
		$mmoney['back_money']=$data['back_money'];
		if($newid && 33 != $type) $xid = M('member_money')->where("uid={$uid}")->save($mmoney);
		if($xid){
			$done = true;
			$Moneylog->commit();
		}else{
			$Moneylog->rollback();
		}
	return $done;
}

function memberLimitLog($uid,$type,$alimit,$info=""){
	$xva = floatval($alimit);
	if(empty($xva)) return true;
	$done = false;
	$MM = M("member_money")->field("money_freeze,money_collect,account_money,back_money",true)->find($uid);
	if(!is_array($MM)){
		M("member_money")->add(array('uid'=>$uid));
		$MM = M("member_money")->field("money_freeze,money_collect,account_money,back_money",true)->find($uid);
	}
	$Moneylog = D('member_moneylog');
	if(in_array($type,array("71","72","73"))) $type_save=7;
	else $type_save = $type;
	
	$Moneylog->startTrans();

		$data['uid'] = $uid;
		$data['type'] = $type_save;
		$data['info'] = $info;
		$data['add_time'] = time();
		$data['add_ip'] = get_client_ip();

		$data['credit_limit'] = 0;
		$data['borrow_vouch_limit'] = 0;
		$data['invest_vouch_limit'] = 0;
		
		switch($type){
			case 1://信用标初审通过暂扣
			case 4://信用标复审未通过返回
			case 7://标的完成，返回
			case 12://流标，返回
				$_data['credit_limit'] = $alimit;
			break;
			case 2://担保标初审通过暂扣
			case 5://担保标复审未通过返回
			case 8://标的完成，返回
				$_data['borrow_vouch_limit'] = $alimit;
			break;
			case 3://参与担保暂扣
			case 6://所担保的标初审未通过，返回
			case 9://所担保的标复审未通过，返回
			case 10://标的完成，返回
				$_data['invest_vouch_limit'] = $alimit;
			break;
			case 11://VIP审核通过
				$_data['credit_limit'] = $alimit;
				$mmoney['credit_limit']=$MM['credit_limit'] + $_data['credit_limit'];
			break;
		}
		$data = array_merge($data,$_data);
		$newid = M('member_limitlog')->add($data);
		//帐户更新
		$mmoney['credit_cuse']=$MM['credit_cuse'] + $data['credit_limit'];
		$mmoney['borrow_vouch_cuse']=$MM['borrow_vouch_cuse'] + $data['borrow_vouch_limit'];
		$mmoney['invest_vouch_cuse']=$MM['invest_vouch_cuse'] + $data['invest_vouch_limit'];
		if($newid) $xid = M('member_money')->where("uid={$uid}")->save($mmoney);
		if($xid){
			$Moneylog->commit();
			$done = true;
		}else{
			$Moneylog->rollback();
		}
	return $done;
}



function memberCreditsLog($uid,$type,$acredits,$info="无"){
	if($acredits==0) return true;
	$done = false;
	$mCredits = M("members")->getFieldById($uid,'credits');
	$Creditslog = D('member_creditslog');
	$Creditslog->startTrans();
		$data['uid'] = $uid;
		$data['type'] = $type;
		$data['affect_credits'] = $acredits;
		$data['account_credits'] = $mCredits + $acredits;
		$data['info'] = $info;
		$data['add_time'] = time();
		$data['add_ip'] = get_client_ip();
		$newid = $Creditslog->add($data);
		
		$xid = M('members')->where("id={$uid}")->setField('credits',$data['account_credits']);
		
		if($xid){
			$Creditslog->commit() ;
			$done = true;
		}else{
			$Creditslog->rollback() ;
		}
	
	return $done;
}

function memberIntegralLog($uid,$type,$integral,$info="无"){
	if($integral==0) return true;
	$pre = C('DB_PREFIX');
	$done = false;

	$Db = new Model();  
    $Db->startTrans(); //多表事务

	$Member = $Db->table($pre."members")->where("id=$uid")->find();

		$data['uid'] = $uid;
		$data['type'] = $type;
		$data['affect_integral'] = $integral;
		$data['active_integral'] = $integral + $Member['active_integral'];
		$data['account_integral'] = $integral + $Member['integral'];
		$data['info'] = $info;
		$data['add_time'] = time();
		$data['add_ip'] = get_client_ip();

	
	if ($integral<0 && $data['active_integral']<0){//判断积分是否消费过头
		return false; 
	} elseif ($integral<0 && $data['active_integral']>0){//消费积分只减活跃积分，总积分不变
		$data['account_integral'] = $Member['integral'];
	}

	//消费积分为负数，消费积分只减活跃积分，不减总积分
	$newid = $Db->table($pre.'member_integrallog')->add($data);//积分细则
	$xid = $Db->table($pre."members")->where("id=$uid")->setInc('active_integral',$integral);//活跃积分总数
	if($integral>0) $yid = $Db->table($pre."members")->where("id=$uid")->setInc('integral',$integral);//积分总数
	else $yid = true;
		
	if($newid && $xid && $yid){
		$Db->commit() ;
		$done = true;
	}else{
		$Db->rollback() ;
	}
	
	return $done;
}

function getMemberMoneySummary($uid){  
	$pre = C('DB_PREFIX');
	$umoney = M('member_money')->field(true)->find($uid);

	$withdraw = M('member_withdraw')->field('withdraw_status,sum(withdraw_money) as withdraw_money,sum(second_fee) as second_fee')->where("uid={$uid}")->group("withdraw_status")->select();
	$withdraw_row = array();
	foreach($withdraw as $wkey=>$wv){
		$withdraw_row[$wv['withdraw_status']] = $wv;
	}
	$withdraw0 = $withdraw_row[0];
	$withdraw1 = $withdraw_row[1];
	$withdraw2 = $withdraw_row[2];
	
	$payonline = M('member_payonline')->where("uid={$uid} AND status=1")->sum('money');//累计充值金额
	
	$commission1 = M('borrow_investor')->where("investor_uid={$uid}")->sum('paid_fee');
	$commission2 = M('borrow_info')->where("borrow_uid={$uid} AND borrow_status in(2,4)")->sum('borrow_fee');//累计借款管理费
	
	$uplevefee = M('member_moneylog')->where("uid={$uid} AND type=2")->sum('affect_money');//充值总金额
	
	$czfee = M('member_payonline')->where("uid={$uid} AND status=1")->sum('fee');//在线充值手续费总金额
	
	$toubiaojl =M('borrow_investor')->where("borrow_uid ={$uid}")->sum('reward_money');//累计支付投标奖励
	$tuiguangjl =M('member_moneylog')->where("uid={$uid} and type=13")->sum('affect_money');//推广奖励
	$xianxiajl =M('member_moneylog')->where("uid={$uid} and type=32")->sum('affect_money');//线下充值奖励
	$xtjl = M('member_moneylog')->where("uid={$uid} and type=34")->sum('affect_money');//累计续投奖励  前台已放弃
    
    //企业直投代收金额及利息
	$circulation = M('transfer_borrow_investor')
                    ->field('sum(investor_capital)as investor_capital, sum(investor_interest) as investor_interest, sum(invest_fee) as invest_fee')
                    ->where('investor_uid='.$uid.' and status=1')
                    ->find();
	///////////////////
	$moneylog = M("member_moneylog")->field("type,sum(affect_money) as money")->where("uid={$uid}")->group("type")->select();
	$list=array();
	foreach($moneylog as $vs){
		$list[$vs['type']]['money']= ($vs['money']>0)?$vs['money']:$vs['money']*(-1);
	}
	
	$tx = M('member_withdraw')->field("uid,sum(withdraw_money) as withdraw_money,sum(second_fee) as second_fee")->where("uid={$uid} and withdraw_status=2")->group("uid")->select();
	foreach($tx as $vt){
		$list['tx']['withdraw_money']= $vt['withdraw_money'];	//成功提现金额	
		$list['tx']['withdraw_fee']= $vt['second_fee'];	//提现手续费
	}
	
	////////////////////////////
	
	$capitalinfo = getMemberBorrowScan($uid);
	$money['zye'] = $umoney['account_money'] + $umoney['back_money']+$umoney['money_collect'] + $umoney['money_freeze'];//帐户总额
	$money['kyxjje'] = $umoney['account_money']+ $umoney['back_money'];//可用金额
	$money['djje'] = $umoney['money_freeze'];//冻结金额
	$money['jjje'] = 0;//奖金金额
	$money['dsbx'] = $capitalinfo['tj']['dsze']+$capitalinfo['tj']['willgetInterest']
                    +$circulation['investor_capital']+$circulation['investor_interest']-$circulation['invest_fee'];//$umoney['money_collect'];//待收本金+待收利息
	
	$money['dfbx'] = $capitalinfo['tj']['dhze'];//待付本息
	$money['dxrtb'] = $capitalinfo['tj']['dqrtb'];//待确认投标
	$money['dshtx'] = $withdraw0['withdraw_money'];//待审核提现
	$money['clztx'] = $withdraw1['withdraw_money'];//处理中提现  
	$money['total_1'] = $money['kyxjje']+$money['jjje']+$money['dsbx']-$money['dfbx']+$money['dxrtb']+$money['dshtx']+$money['clztx'];
	
	$money['jzlx'] = $capitalinfo['tj']['earnInterest'];//净赚利息
	$money['jflx'] = $capitalinfo['tj']['payInterest'];//净付利息
	//$money['ljjj'] = $umoney['reward_money'];//累计收到奖金
	$money['xtjj'] = $list['34']['money']+$list[40]['money'];//$xtjl;//累计续投奖金
	$money['ljhyf'] = $list['14']['money']+$list['22']['money']+$list['25']['money']+$list['26']['money'];//$uplevefee;//累计支付会员费
	$money['ljtxsxf'] = $list['tx']['withdraw_fee'];//$withdraw2['withdraw_fee'];//累计提现手续费
	$money['ljczsxf'] = $czfee;//累计充值手续费
    
	$money['ljtbjl'] = $list['20']['money']+$list[41]['money'];//$toubiaojl;//累计投标奖励
	$money['ljtgjl'] = $list['13']['money'];//$tuiguangjl;//累计推广奖励
	$money['xxjl'] = $list['32']['money'];//$xianxiajl;//线下充值奖励
	$money['jkglf'] =$list['18']['money'];//借款管理费
	$money['yqf'] = $list['30']['money']+$list['31']['money'];//逾期罚息及催收费
	$money['zftbjl'] = $toubiaojl;//支付投标奖励
	$money['total_2'] = $money['jzlx']
                        -$money['jflx']
                        -$money['ljhyf']
                        -$money['ljtxsxf']
                        -$money['ljczsxf']
                        +$money['ljtbjl']
                        +$money['ljtgjl']
                        +$money['xxjl']
                        +$money['xtjj']
                        -$money['jkglf']
                        -$money['yqf']
                        -$money['zftbjl'];
	
	$money['ljtzje'] = $capitalinfo['tj']['borrowOut'];//累计投资金额  
	$money['ljjrje'] = $capitalinfo['tj']['borrowIn'];//累计借入金额 
	$money['ljczje'] = $payonline;//累计充值金额
	$money['ljtxje'] = $withdraw2['withdraw_money'];//累计提现金额
	$money['ljzfyj'] = $commission1 + $commission2;//累计支付佣金
//
	$money['dslxze'] = $capitalinfo['tj']['willgetInterest'] + $circulation['investor_interest'];//待收利息总额  
	$money['dflxze'] = $capitalinfo['tj']['willpayInterest'];//待付利息总额
	
	return $money;
}

function getBorrowInvest($borrowid=0,$uid){
	if(empty($borrowid)) return;
	$vx = M("borrow_info")->field('id')->where("id={$borrowid} AND borrow_uid={$uid}")->find();
	if(!is_array($vx)) return;

	$binfo = M("borrow_info")->field('borrow_name,borrow_uid,borrow_type,borrow_duration,repayment_type,has_pay,total,deadline')->find($borrowid);
	$list = array();
	switch($binfo['repayment_type']){
		case 1://一次性还款
		case 5://一次性还款
				$field = "borrow_id,sort_order,sum(capital) as capital,sum(interest) as interest,status,sum(receive_interest+receive_capital+if(receive_capital>=0,interest_fee,0)) as paid,deadline";
				$vo = M("investor_detail")->field($field)->where("borrow_id={$borrowid} AND `sort_order`=1")->group('sort_order')->find();
				//$status_arr =array('还未还','已还完','已提前还款','逾期还款','网站代还本金');
				$status_arr =array('还未还','已还完','已提前还款','迟到还款','网站代还本金','逾期还款','','待还');
				///////////////////
				if($vo['deadline']<time() && $vo['status']==7){
					$vo['status'] ='逾期未还';
				}else{
					$vo['status'] = $status_arr[$vo['status']];
				}
				///////////////////
				//$vo['status'] = $status_arr[$vo['status']];
				//$vo['needpay'] = getFloatValue(sprintf("%.2f",($vo['interest']+$vo['capital']-$vo['paid'])),2);
				$vo['needpay'] = sprintf("%.2f",($vo['interest']+$vo['capital']-$vo['paid']));
				$vo['cur'] = strtotime(date('Y-m-d',$vo['deadline'])) == strtotime(date('Y-m-d'));
				$list[] = $vo;
		break;
		default://每月还款
			for($i=1;$i<=$binfo['borrow_duration'];$i++){
				$field = "borrow_id,sort_order,sum(capital) as capital,sum(interest) as interest,status,sum(receive_interest+receive_capital+if(receive_capital>=0,interest_fee,0)) as paid,deadline";
				$vo = M("investor_detail")->field($field)->where("borrow_id={$borrowid} AND `sort_order`=$i")->group('sort_order')->find();
				$status_arr =array('还未还','已还完','已提前还款','迟到还款','网站代还本金','逾期还款','','待还');
				///////////////////
				if($vo['deadline']<time() && $vo['status']==7){
					$vo['status'] ='逾期未还';
				}else{
					$vo['status'] = $status_arr[$vo['status']];
				}
				$vo['cur'] = strtotime(date('Y-m-d',$vo['deadline'])) == strtotime(date('Y-m-d'));
				///////////////////
				//$vo['status'] = $status_arr[$vo['status']];
				$vo['needpay'] = sprintf("%.2f",($vo['interest']+$vo['capital']-$vo['paid']));
				$list[] = $vo;
			}
		break;
	}
	$row=array();
	$row['list'] = $list;
	$row['name'] = $binfo['borrow_name'];
	return $row;

}

function getDurationCount($uid=0){
	if(empty($uid)) return;
	$pre = C('DB_PREFIX');
	
	$field = "d.status,d.repayment_time";
	$sql = "select {$field} from {$pre}investor_detail d left join {$pre}borrow_info b ON b.id=d.borrow_id where d.borrow_id in(select tb.id from {$pre}borrow_info tb where tb.borrow_uid={$uid}) group by d.borrow_id, d.sort_order";
	$list = M()->query($sql);

	$week_1 = array(strtotime("-7 day",strtotime(date("Y-m-d",time())." 00:00:00")),strtotime(date("Y-m-d",time())." 23:59:59"));
	$time_1 = array(strtotime("-1 month",strtotime(date("Y-m-d",time())." 00:00:00")),strtotime(date("Y-m-d",time())." 23:59:59"));
	$time_6 = array(strtotime("-6 month",strtotime(date("Y-m-d",time())." 00:00:00")),strtotime(date("Y-m-d",time())." 23:59:59"));
	$row_time_1=array();
	$row_time_2=array();
	$row_time_3=array();
	$row_time_4=array();
	foreach($list as $v){
		switch($v['status']){
			case 1:
				if($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]){
					$row_time_3['zc'] = $row_time_3['zc'] + 1;//6个月内
					if($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) $row_time_1['zc'] = $row_time_1['zc'] + 1;//一周内
					if($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) $row_time_2['zc'] = $row_time_2['zc'] + 1;//一个月内
				}
				$row_time_4['zc'] = $row_time_4['zc'] + 1;//所有
			break;
			case 2:
				if($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]){
					$row_time_3['tq'] = $row_time_3['tq'] + 1;//6个月内
					if($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) $row_time_1['tq'] = $row_time_1['tq'] + 1;//一周内
					if($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) $row_time_2['tq'] = $row_time_2['tq'] + 1;//一个月内
				}
				$row_time_4['tq'] = $row_time_4['tq'] + 1;//所有
			break;
			case 3:
				if($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]){
					$row_time_3['ch'] = $row_time_3['ch'] + 1;//6个月内
					if($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) $row_time_1['ch'] = $row_time_1['ch'] + 1;//一周内
					if($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) $row_time_2['ch'] = $row_time_2['ch'] + 1;//一个月内
				}
				$row_time_4['ch'] = $row_time_4['ch'] + 1;//所有
			break;
			case 5:
				if($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]){
					$row_time_3['yq'] = $row_time_3['yq'] + 1;//6个月内
					if($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) $row_time_1['yq'] = $row_time_1['yq'] + 1;//一周内
					if($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) $row_time_2['yq'] = $row_time_2['yq'] + 1;//一个月内
				}
				
				$row_time_4['yq'] = $row_time_4['yq'] + 1;//所有
			break;
			case 6:
				if($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]){
					$row_time_3['wh'] = $row_time_3['wh'] + 1;//6个月内
					if($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) $row_time_1['wh'] = $row_time_1['wh'] + 1;//一周内
					if($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) $row_time_2['wh'] = $row_time_2['wh'] + 1;//一个月内
				}
				$row_time_4['wh'] = $row_time_4['wh'] + 1;//所有
			break;
			
		}
	}
	$row['history1'] = $row_time_1;
	$row['history2'] = $row_time_2;
	$row['history3'] = $row_time_3;
	$row['history4'] = $row_time_4;
	return $row;
}


function getMemberBorrow($uid=0,$size=10){
	if(empty($uid)) return;
	$pre = C('DB_PREFIX');
	
	$field = "b.borrow_name,d.total,d.borrow_id,d.sort_order,sum(d.capital) as capital,sum(d.interest) as interest,d.status,sum(d.receive_interest+d.receive_capital+if(d.receive_capital>=0,d.interest_fee,0)) as paid,d.deadline";
	$sql = "select {$field} from {$pre}investor_detail d left join {$pre}borrow_info b ON b.id=d.borrow_id where d.borrow_id in(select tb.id from {$pre}borrow_info tb where tb.borrow_status=6 AND tb.borrow_uid={$uid}) AND d.repayment_time=0 group by d.sort_order, d.borrow_id order by  d.borrow_id,d.sort_order limit 0,10";
	//$sql = "select {$field} from {$pre}investor_detail d left join {$pre}borrow_info b ON b.id=d.borrow_id where d.borrow_uid={$uid} AND d.status=0 group by d.sort_order limit 0,10";
	$list = M()->query($sql);
	$status_arr =array('还未还','已还完','已提前还款','迟到还款','网站代还本金','逾期还款','','待还');
	foreach($list as $key=>$v){
		//$list[$key]['status'] = $status_arr[$v['status']];
		
		if($v['deadline']<time() && $v['status']==7){
			$list[$key]['status'] ='逾期未还';
		}else{
			$list[$key]['status'] = $status_arr[$v['status']];
		}
	}
	$row=array();
	$row['list'] = $list;
	return $row;
}

function getLeftTime($timeend,$type=1){
	if($type==1){
		$timeend = strtotime(date("Y-m-d",$timeend)." 23:59:59");
		$timenow = strtotime(date("Y-m-d",time())." 23:59:59");
		$left = ceil( ($timeend-$timenow)/3600/24 );
	}else{
		$left_arr = timediff(time(),$timeend);
		$left = $left_arr['day']."天 ".$left_arr['hour']."小时 ".$left_arr['min']."分钟 ".$left_arr['sec']."秒";
	}
	return $left;
}

function timediff($begin_time,$end_time )
{
    if ( $begin_time < $end_time ) {
        $starttime = $begin_time;
        $endtime = $end_time;
    } else {
        $starttime = $end_time;
        $endtime = $begin_time;
    }
    $timediff = $endtime - $starttime;
    $days = intval( $timediff / 86400 );
    $remain = $timediff % 86400;
    $hours = intval( $remain / 3600 );
    $remain = $remain % 3600;
    $mins = intval( $remain / 60 );
    $secs = $remain % 60;
    $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
    return $res;
}

function addInnerMsg($uid,$title,$msg){
	if(empty($uid)) return;
	$data['uid'] = $uid;
	$data['title'] = $title;
	$data['msg'] = $msg;
	$data['send_time'] = time();
	M('inner_msg')->add($data);
}


//获取下级或者同级栏目列表
function getTypeList($parm){
	$Osql="sort_order DESC";
	$field="id,type_name,type_set,add_time,type_url,type_nid,parent_id";
	//查询条件 
	$Lsql="{$parm['limit']}";
	$pc = D('navigation')->where("parent_id={$parm['type_id']} and model='navigation'")->count('id');
	if($pc>0){
		$map['is_hiden'] = 0;
		$map['parent_id'] = $parm['type_id'];
        $map['model']  = 'navigation';
		$data = D('navigation')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
	}elseif(!isset($parm['notself'])){
		$map['is_hiden'] = 0;
		$map['parent_id'] = D('Acategory')->getFieldById($parm['type_id'],'parent_id');
		$data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
	}

	//链接处理
	$typefix = get_type_leve_nid($parm['type_id']);
	$typeu = $typefix[0];
	$suffix=C("URL_HTML_SUFFIX");
	foreach($data as $key=>$v){
		if($v['type_set']==2){
			if(empty($v['type_url'])) $data[$key]['turl']="javascript:alert('请在后台添加此栏目链接');";
			else $data[$key]['turl'] = $v['type_url'];
		}
		elseif($parm['model']=='navigation'||($v['parent_id']==0)) $data[$key]['turl'] = MU("Home/{$v['type_nid']}/index","typelist",array("suffix"=>$suffix));
		elseif($parm['model']=='article'||($v['parent_id']==0)) $data[$key]['turl'] = MU("Home/{$v['type_nid']}/index","typelist",array("suffix"=>$suffix));
		else $data[$key]['turl'] = MU("Home/{$typeu}/{$v['type_nid']}","typelist",array("suffix"=>$suffix));
	}
	$row=array();
	$row = $data;

	return $row;
}

//获取下级或者同级栏目列表 文章栏目
function getTypeListActa($parm){
	//if(empty($parm['type_id'])) return;
	$Osql="sort_order DESC";
	$field="id,type_name,type_set,add_time,type_url,type_nid,parent_id";
	//查询条件 
	$Lsql="{$parm['limit']}";
	//$pc = D('Acategory')->where("parent_id={$parm['type_id']} and model='navigation'")->count('id');
	$pc = D('Acategory')->where("parent_id={$parm['type_id']} and model='article'")->count('id');
	if($pc>0){
		$map['is_hiden'] = 0;
		$map['parent_id'] = $parm['type_id'];
        $map['model']  = 'article';
		//$data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
		$data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
	}elseif(!isset($parm['notself'])){
		$map['is_hiden'] = 0;
		$map['parent_id'] = D('Acategory')->getFieldById($parm['type_id'],'parent_id');
		//$data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
		$data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
	}

	//链接处理
	$typefix = get_type_leve_nid($parm['type_id']);
	$typeu = $typefix[0];
	$suffix=C("URL_HTML_SUFFIX");
	foreach($data as $key=>$v){
		if($v['type_set']==2){
			if(empty($v['type_url'])) $data[$key]['turl']="javascript:alert('请在后台添加此栏目链接');";
			else $data[$key]['turl'] = $v['type_url'];
		}
		//elseif($parm['type_id']==0||($v['parent_id']==0&&count($typefix)==1)) $data[$key]['turl'] = MU("Home/{$v['type_nid']}/index","typelist",array("suffix"=>$suffix));
		elseif($parm['model']=='article'||($v['parent_id']==0)) $data[$key]['turl'] = MU("Home/{$v['type_nid']}/index","typelist",array("suffix"=>$suffix));
		else $data[$key]['turl'] = MU("Home/{$typeu}/{$v['type_nid']}","typelist",array("suffix"=>$suffix));
	}
	$row=array();
	$row = $data;
	
	return $row;
}
//新标提醒
function newTip($borrow_id){
	
	$binfo = M("borrow_info")->field('borrow_type,borrow_interest_rate,borrow_duration')->find();
	
	if($binfo['borrow_type']==3) $map['borrow_type'] = 3;
	else $map['borrow_type'] = 0;
	$tiplist = M("borrow_tip")->field(true)->where($map)->select();
	
	foreach($tiplist as $key=>$v){
		$minfo = M('members m')->field('mm.account_money,mm.back_money,m.user_phone')->join('lzh_member_money mm on m.id=mm.uid')->find($v['uid']);
		if(
		$binfo['borrow_interest_rate'] >= $v['interest_rate'] &&
		$binfo['borrow_duration'] >= $v['doration_from'] &&
		$binfo['borrow_duration'] <= $v['doration_to'] &&
		($minfo['account_money']+ $minfo['back_money'])>= $v['account_money']
		){
			(empty($tipPhone))?$tipPhone .="{$v['user_phone']}":$tipPhone .=",{$v['user_phone']}";
		}
	}
	$smsTxt = FS("Webconfig/smstxt");
	$smsTxt=de_xie($smsTxt);
	
	sendsms($tipPhone,$smsTxt['newtip']);
	
}

function getBorrowInterest($type,$money,$duration,$rate){
	//if(!in_array($type,C('REPAYMENT_TYPE'))) return $money;
	//echo $month_rate."|".$rate."|".$duration."|".$type;
	switch($type){
		case 1://按天到期还款
			$day_rate =  $rate/36500;//计算出天标的天利率
			$interest = getFloatValue($money*$day_rate*$duration ,4);
		break;
		case 2://按月分期还款
			$parm['duration'] = $duration;
			$parm['money'] = $money;
			$parm['year_apr'] = $rate;
			$parm['type'] = "all";
			$intre = EqualMonth($parm);
			$interest = ($intre['repayment_money'] - $money);
		break;
		case 3://按季分期还款
			$parm['month_times'] = $duration;
			$parm['account'] = $money;
			$parm['year_apr'] = $rate;
			$parm['type'] = "all";
			$intre = EqualSeason($parm);
			$interest = $intre['interest'];
		break;
		case 4://每月还息到期还本
			$parm['month_times'] = $duration;
			$parm['account'] = $money;
			$parm['year_apr'] = $rate;
			$parm['type'] = "all";
			$intre = EqualEndMonth($parm);
			$interest = $intre['interest'];
		break;
		case 5://一次性到期还款
			$parm['month_times'] = $duration;
			$parm['account'] = $money;
			$parm['year_apr'] = $rate;
			$parm['type'] = "all";
			$intre = EqualEndMonthOnly($parm);
			$interest = $intre['interest'];
		break;
	
	}
	return $interest;
}

//等额本息法
//贷款本金×月利率×（1+月利率）还款月数/[（1+月利率）还款月数-1] 
//a*[i*(1+i)^n]/[(1+I)^n-1] 
//（a×i－b）×（1＋i）
/*
money,year_apr,duration,borrow_time(用来算还款时间的),type(==all时，返回还款概要)

*/
function EqualMonth ($data = array()){
	if (isset($data['money']) && $data['money']>0){
		$account = $data['money'];
	}else{
		return "";
	}
	
	if (isset($data['year_apr']) && $data['year_apr']>0){
		$year_apr = $data['year_apr'];
	}else{
		return "";
	}
	
	if (isset($data['duration']) && $data['duration']>0){
		$duration = $data['duration'];
	}
	if (isset($data['borrow_time']) && $data['borrow_time']>0){
		$borrow_time = $data['borrow_time'];
	}else{
		$borrow_time = time();
	}
	$month_apr = $year_apr/(12*100);
	$_li = pow((1+$month_apr),$duration);
	$repayment = round($account * ($month_apr * $_li)/($_li-1),4);
	$_result = array();
	if (isset($data['type']) && $data['type']=="all"){
		$_result['repayment_money'] = $repayment*$duration;
		$_result['monthly_repayment'] = $repayment;
		$_result['month_apr'] = round($month_apr*100,4);
	}else{
		//$re_month = date("n",$borrow_time);
		for($i=0;$i<$duration;$i++){
			if ($i==0){
				$interest = round($account*$month_apr,4);
			}else{
				$_lu = pow((1+$month_apr),$i);
				$interest = round(($account*$month_apr - $repayment)*$_lu + $repayment,4);
			}
			$_result[$i]['repayment_money'] = getFloatValue($repayment,4);
			$_result[$i]['repayment_time'] = get_times(array("time"=>$borrow_time,"num"=>$i+1));
			$_result[$i]['interest'] = getFloatValue($interest,4);
			$_result[$i]['capital'] = getFloatValue($repayment-$interest,4);
		}
	}
	return $_result;
}
//按季等额本息法
function EqualSeason ($data = array()){
  //借款的月数
  if (isset($data['month_times']) && $data['month_times']>0){
	  $month_times = $data['month_times'];
  }
  //按季还款必须是季的倍数
  if ($month_times%3!=0){
	  return false;
  }
  //借款的总金额
  if (isset($data['account']) && $data['account']>0){
	  $account = $data['account'];
  }else{
	  return "";
  }
  //借款的年利率
  if (isset($data['year_apr']) && $data['year_apr']>0){
	  $year_apr = $data['year_apr'];
  }else{
	  return "";
  }
  
  //借款的时间 --- 什么时候开始借款，计算还款的
  if (isset($data['borrow_time']) && $data['borrow_time']>0){
	  $borrow_time = $data['borrow_time'];
  }else{
	  $borrow_time = time();
  }
  
  //月利率
  $month_apr = $year_apr/(12*100);
  
  //得到总季数
  $_season = $month_times/3;
  
  //每季应还的本金
  $_season_money = round($account/$_season,4);
  
  //$re_month = date("n",$borrow_time);
  $_yes_account = 0 ;
  $repayment_account = 0;//总还款额
  $_all_interest = 0;//总利息
  for($i=0;$i<$month_times;$i++){
	  $repay = $account - $_yes_account;//应还的金额
	  
	  $interest = round($repay*$month_apr,4);//利息等于应还金额乘月利率
	  $repayment_account = $repayment_account+$interest;//总还款额+利息
	  $capital = 0;
	  if ($i%3==2){
		  $capital = $_season_money;//本金只在第三个月还，本金等于借款金额除季度
		  $_yes_account = $_yes_account+$capital;
		  $repay = $account - $_yes_account;
		  $repayment_account = $repayment_account+$capital;//总还款额+本金
	  }
	  
	  $_result[$i]['repayment_money'] = getFloatValue($interest+$capital,4);
	  $_result[$i]['repayment_time'] = get_times(array("time"=>$borrow_time,"num"=>$i+1));
	  $_result[$i]['interest'] = getFloatValue($interest,4);
	  $_result[$i]['capital'] = getFloatValue($capital,4);
	  $_all_interest += $interest;
  }
  if (isset($data['type']) && $data['type']=="all"){
	  $_resul['repayment_money'] = $repayment_account;
	  $_resul['monthly_repayment'] = round($repayment_account/$_season,4);
	  $_resul['month_apr'] = round($month_apr*100,4);
	  $_resul['interest'] = $_all_interest;
	  return $_resul;
  }else{
	  return $_result;
  }
}

//到期还本，按月付息
function EqualEndMonth ($data = array()){
  
  //借款的月数
  if (isset($data['month_times']) && $data['month_times']>0){
	  $month_times = $data['month_times'];
  }

  //借款的总金额
  if (isset($data['account']) && $data['account']>0){
	  $account = $data['account'];
  }else{
	  return "";
  }
  
  //借款的年利率
  if (isset($data['year_apr']) && $data['year_apr']>0){
	  $year_apr = $data['year_apr'];
  }else{
	  return "";
  }
  
  
  //借款的时间
  if (isset($data['borrow_time']) && $data['borrow_time']>0){
	  $borrow_time = $data['borrow_time'];
  }else{
	  $borrow_time = time();
  }
  
  //月利率
  $month_apr = $year_apr/(12*100);
  
  
  
  //$re_month = date("n",$borrow_time);
  $_yes_account = 0 ;
  $repayment_account = 0;//总还款额
  $_all_interest=0;
  
  $interest = round($account*$month_apr,4);//利息等于应还金额乘月利率
  for($i=0;$i<$month_times;$i++){
	  $capital = 0;
	  if ($i+1 == $month_times){
		  $capital = $account;//本金只在最后一个月还，本金等于借款金额除季度
	  }
	  
	  $_result[$i]['repayment_account'] = $interest+$capital;
	  $_result[$i]['repayment_time'] = get_times(array("time"=>$borrow_time,"num"=>$i+1));
	  $_result[$i]['interest'] = $interest;
	  $_result[$i]['capital'] = $capital;
	  $_all_interest += $interest;
  }
  if (isset($data['type']) && $data['type']=="all"){
	  $_resul['repayment_account'] = $account + $interest*$month_times;
	  $_resul['monthly_repayment'] = $interest;
	  $_resul['month_apr'] = round($month_apr*100,4);
	  $_resul['interest'] = $_all_interest;
	  return $_resul;
  }else{
	  return $_result;
  }
}

/////////////////////////////////////////一次性还款//////////////////////////////////////
//到期还本，按月付息
function EqualEndMonthOnly($data = array()){
  
  //借款的月数
  if (isset($data['month_times']) && $data['month_times']>0){
	  $month_times = $data['month_times'];
  }

  //借款的总金额
  if (isset($data['account']) && $data['account']>0){
	  $account = $data['account'];
  }else{
	  return "";
  }
  
  //借款的年利率
  if (isset($data['year_apr']) && $data['year_apr']>0){
	  $year_apr = $data['year_apr'];
  }else{
	  return "";
  }
  
  //借款的时间
  if (isset($data['borrow_time']) && $data['borrow_time']>0){
	  $borrow_time = $data['borrow_time'];
  }else{
	  $borrow_time = time();
  }
  
  //月利率
  $month_apr = $year_apr/(12*100);
  
  $interest = getFloatValue($account*$month_apr*$month_times,4);//利息等于应还金额*月利率*借款月数

  if (isset($data['type']) && $data['type']=="all"){
	  $_resul['repayment_account'] = $account + $interest;
	  $_resul['monthly_repayment'] = $interest;
	  $_resul['month_apr'] = round($month_apr*100,4);
	  $_resul['interest'] = $interest;
	  $_resul['capital'] = $account;
	  return $_resul;
  }
}

///////////////////////////////////////////////////////////////////////////////////////////
function getMinfo($uid,$field='m.pin_pass,mm.account_money,mm.back_money'){
	$pre = C('DB_PREFIX');
	$vm = M("members m")->field($field)->join("{$pre}member_money mm ON mm.uid=m.id")->where("m.id={$uid}")->find();
	return $vm;
}


//获取借款列表
function getMemberInfoDone($uid){
	$pre = C('DB_PREFIX');

	$field = "m.id,m.id as uid,m.user_name,mbank.uid as mbank_id,mi.uid as mi_id,mhi.uid as mhi_id,mci.uid as mci_id,mdpi.uid as mdpi_id,mei.uid as mei_id,mfi.uid as mfi_id,s.phone_status,s.id_status,s.email_status,s.safequestion_status";
	$row = M('members m')->field($field)
	->join("{$pre}member_banks mbank ON m.id=mbank.uid")
	->join("{$pre}member_contact_info mci ON m.id=mci.uid")
	->join("{$pre}member_department_info mdpi ON m.id=mdpi.uid")
	->join("{$pre}member_house_info mhi ON m.id=mhi.uid")
	->join("{$pre}member_ensure_info mei ON m.id=mei.uid")
	->join("{$pre}member_info mi ON m.id=mi.uid")
	->join("{$pre}member_financial_info mfi ON m.id=mfi.uid")
	->join("{$pre}members_status s ON m.id=s.uid")
	->where("m.id={$uid}")->find();
	$is_data = M('member_data_info')->where("uid={$row['uid']}")->count("id");
	$i==0;
	if($row['mbank_id']>0){
		$i++;
		$row['mbank'] = "<span style='color:green'>已填写</span>";
	}else{
		$row['mbank'] = "<span style='color:black'>未填写</span>";
	}
	
	if($row['mci_id']>0){
		$i++;
		$row['mci'] = "<span style='color:green'>已填写</span>";
	}else{
		$row['mci'] = "<span style='color:black'>未填写</span>";
	}
	
	if($is_data>0){
		$row['mdi_id'] = $is_data;
		$row['mdi'] = "<span style='color:green'>已填写</span>";
	}else{
		$row['mdi'] = "<span style='color:black'>未填写</span>";
	}
	
	if($row['mhi_id']>0){
		$i++;
		$row['mhi'] = "<span style='color:green'>已填写</span>";
	}else{
		$row['mhi'] = "<span style='color:black'>未填写</span>";
	}
	
	if($row['mdpi_id']>0){
		$i++;
		$row['mdpi'] = "<span style='color:green'>已填写</span>";
	}else{
		$row['mdpi'] = "<span style='color:black'>未填写</span>";
	}
	
	if($row['mei_id']>0){
		$i++;
		$row['mei'] = "<span style='color:green'>已填写</span>";
	}else{
		$row['mei'] = "<span style='color:black'>未填写</span>";
	}
	
	if($row['mfi_id']>0){
		$i++;
		$row['mfi'] = "<span style='color:green'>已填写</span>";
	}else{
		$row['mfi'] = "<span style='color:black'>未填写</span>";
	}
	
	if($row['mi_id']>0){
		$i++;
		$row['mi'] = "<span style='color:green'>已填写</span>";
	}else{
		$row['mi'] = "<span style='color:black'>未填写</span>";
	}
	
	$row['i'] = $i;//7为完成
	return $row;
}

function getMemberBorrowScan($uid){
	//借款次数相关
	$field="borrow_status,count(id) as num,sum(borrow_money) as money,sum(repayment_money) as repayment_money";
	$borrowNum=M('borrow_info')->field($field)->where("borrow_uid = {$uid}")->group('borrow_status')->select();
	foreach($borrowNum as $v){
		$borrowCount[$v['borrow_status']] = $v;
	}
	//借款次数相关
	//还款情况相关
	$field="status,sort_order,borrow_id,sum(capital) as capital,sum(interest) as interest";
	$repaymentNum=M('investor_detail')->field($field)->where("borrow_uid = {$uid}")->group('sort_order,borrow_id')->select();
	foreach($repaymentNum as $v){
		$repaymentStatus[$v['status']]['capital']+=$v['capital'];//当前状态下的数金额
		$repaymentStatus[$v['status']]['interest']+=$v['interest'];//当前状态下的数金额
		$repaymentStatus[$v['status']]['num']++;//当前状态下的总笔数
	}
	//还款情况相关
	//借出情况相关
	$field="status,count(id) as num,sum(investor_capital) as investor_capital,sum(reward_money) as reward_money,sum(investor_interest) as investor_interest,sum(receive_capital) as receive_capital,sum(receive_interest) as receive_interest,sum(invest_fee) as invest_fee";
	$investNum=M('borrow_investor')->field($field)->where("investor_uid = {$uid}")->group('status')->select();
	$_reward_money = 0;
	foreach($investNum as $v){
		$investStatus[$v['status']]=$v;
		$_reward_money+=floatval($v['reward_money']);
	}
	//借出情况相关
	//逾期的借入
	$field="borrow_id,sort_order,sum(`capital`) as capital,count(id) as num";
	$expiredNum=M('investor_detail')->field($field)->where("`repayment_time`=0 and borrow_uid={$uid} AND status=7 and `deadline`<".time()." ")->group('borrow_id,sort_order')->select();
	$_expired_money = 0;
	foreach($expiredNum as $v){
		$expiredStatus[$v['borrow_id']][$v['sort_order']]=$v;
		$_expired_money+=floatval($v['capital']);
	}
	$rowtj['expiredMoney'] = getFloatValue($_expired_money,2);//逾期金额
	$rowtj['expiredNum'] = count($expiredNum);//逾期期数
	//逾期的借入
	//逾期的投资
	$field="borrow_id,sort_order,sum(`capital`) as capital,count(id) as num";
	$expiredInvestNum=M('investor_detail')->field($field)->where("`repayment_time`=0 and `deadline`<".time()." and investor_uid={$uid} AND status <> 0")->group('borrow_id,sort_order')->select();
	$_expired_invest_money = 0;
	foreach($expiredInvestNum as $v){
		$expiredInvestStatus[$v['borrow_id']][$v['sort_order']]=$v;
		$_expired_invest_money+=floatval($v['capital']);
	}
	$rowtj['expiredInvestMoney'] = getFloatValue($_expired_invest_money,2);//逾期金额
	$rowtj['expiredInvestNum'] = count($expiredInvestNum);//逾期期数
	//逾期的投资
	
	$rowtj['jkze'] = getFloatValue(floatval($borrowCount[6]['money']+$borrowCount[7]['money']+$borrowCount[8]['money']+$borrowCount[9]['money']),2);//借款总额
	$rowtj['yhze'] = getFloatValue(floatval($borrowCount[6]['repayment_money']+$borrowCount[7]['repayment_money']+$borrowCount[8]['repayment_money']+$borrowCount[9]['repayment_money']),2);//应还总额
	$rowtj['dhze'] = getFloatValue($rowtj['jkze']-$rowtj['yhze'],2);//待还总额
	$rowtj['jcze'] = getFloatValue(floatval($investStatus[4]['investor_capital']),2);//借出总额
	$rowtj['ysze'] = getFloatValue(floatval($investStatus[4]['receive_capital']),2);//应收总额
	$rowtj['dsze'] = getFloatValue($rowtj['jcze']-$rowtj['ysze'],2);
	$rowtj['fz'] = getFloatValue($rowtj['jcze']-$rowtj['jkze'],2);
	
	$rowtj['dqrtb'] = getFloatValue($investStatus[1]['investor_capital'],2);//待确认投标
    //净赚利息      
    $circulation = M('transfer_borrow_investor')->field('sum(investor_interest)as investor_interest, sum(invest_fee) as invest_fee')
                                                ->where('investor_uid='.$uid.' and status=1')
                                                ->find();
	$rowtj['earnInterest'] = getFloatValue(floatval($investStatus[5]['receive_interest']
                                                    +$investStatus[6]['receive_interest']
                                                    +$circulation['investor_interest']
                                                    -$investStatus[5]['invest_fee']
                                                    -$investStatus[6]['invest_fee']
                                                    -$circulation['invest_fee']
                                                    ),2);//净赚利息
    $receive_interest = M('transfer_borrow_investor')->where('investor_uid='.$uid)->sum('investor_capital');
	$rowtj['payInterest'] = getFloatValue(floatval($repaymentStatus[1]['interest']+$repaymentStatus[2]['interest']+$repaymentStatus[3]['interest']),2);//净付利息
	$rowtj['willgetInterest'] = getFloatValue(floatval($investStatus[4]['investor_interest']-$investStatus[4]['receive_interest']),2);//待收利息
	$rowtj['willpayInterest'] = getFloatValue(floatval($repaymentStatus[7]['interest']),2);//待确认支付管理费
	$rowtj['borrowOut'] = getFloatValue(floatval($investStatus[4]['investor_capital']+$investStatus[5]['investor_capital']+$investStatus[6]['investor_capital']+$receive_interest),2);//借出总额
	$rowtj['borrowIn'] = getFloatValue(floatval($borrowCount[6]['money']+$borrowCount[7]['money']+$borrowCount[8]['money']+$borrowCount[9]['money']),2);//借入总额
	
	$rowtj['jkcgcs'] = $borrowCount[6]['num']+$borrowCount[7]['num']+$borrowCount[8]['num']+$borrowCount[9]['num'];//借款成功次数
	$rowtj['tbjl'] = $_reward_money;//投标奖励

    //处理企业直投的相关数据
    //企业直投借出未确定的金额及数量
    $circulation_bor = M('transfer_borrow_investor')->field('sum(investor_capital) as investor_capital, count(id) as num')
                                                        ->where('investor_uid='.$uid.' and status=1')
                                                        ->find();
    $investStatus[8]['investor_capital'] += $circulation_bor['investor_capital'];
	$investStatus[8]['num'] += $circulation_bor['num'];
    unset($circulation_bor);
    //企业直投已回收的投资及数量
    $circulation_bor = M('transfer_borrow_investor')->field('sum(investor_capital) as investor_capital, count(id) as num')
                                                        ->where('investor_uid='.$uid.' and status=2')
                                                        ->find();
    $investStatus[9]['investor_capital'] += $circulation_bor['investor_capital'];
    $investStatus[9]['num'] += $circulation_bor['num'];
    
    //完成的投资
    $circulation_bor = M("transfer_borrow_investor i")
                        ->field('sum(i.investor_capital) as investor_capital, count(i.id) as num')
                        ->where('i.status=2 and i.investor_uid='.$uid)
                        ->join("{$pre}transfer_borrow_info b ON b.id=i.borrow_id")
                        ->order("i.id DESC")
                        ->find();
    
	$row=array();
	$row['tborrowOut']=$receive_interest;//企业直投借出总额
	$row['borrow'] = $borrowCount;
	$row['repayment'] = $repaymentStatus;
	$row['invest'] = $investStatus;
	$row['tj'] = $rowtj;
    $row['circulation_bor'] = $circulation_bor;
	return $row;
}

function getUserWC($uid){
	$row=array();
	$field="count(id) as num,sum(withdraw_money) as money";
	$row["W"] = M('member_withdraw')->field($field)->where("uid={$uid} AND withdraw_status=2")->find();
	$field="count(id) as num,sum(money) as money";
	$row["C"] = M('member_payonline')->field($field)->where("uid={$uid} AND status=1")->find();
	return $row;
}
function getExpiredDays($deadline){
	if($deadline<1000) return "数据有误";
	return ceil( (time()-$deadline)/3600/24 );
}
function getExpiredMoney($expired,$capital,$interest){
	$glodata = get_global_setting();
	$expired_fee = explode("|",$glodata['fee_expired']);

	if($expired<=$expired_fee[0]) return 0;

	return getFloatValue(($capital+$interest)*$expired*$expired_fee[1]/1000,2);
}
function getExpiredCallFee($expired,$capital,$interest){
	$glodata = get_global_setting();
	$call_fee = explode("|",$glodata['fee_call']);
	
	if($expired<=$call_fee[0]) return 0;
	return getFloatValue(($capital+$interest)*$expired*$call_fee[1]/1000,2);
}


function getNet($uid){
	//return getFloatValue($minfo['account_money'] + $minfo['money_freeze'] + $minfo['money_collect'] - intval($capitalinfo['borrow'][6]['money'] - $capitalinfo['borrow'][6]['repayment_money']),2);
	$_minfo = getMinfo($uid,"m.pin_pass,mm.account_money,mm.back_money,mm.credit_cuse,mm.money_collect");
	$borrowNum=M('borrow_info')->field("borrow_type,count(id) as num,sum(borrow_money) as money,sum(repayment_money) as repayment_money")->where("borrow_uid = {$uid} AND borrow_status=6 ")->group("borrow_type")->select();
	$borrowDe = array();
	foreach ($borrowNum as $k => $v) {
		$borrowDe[$v['borrow_type']] = $v['money'] - $v['repayment_money'];
	}	
	$_netMoney = getFloatValue(0.9*$_minfo['money_collect']-$borrowDe[4],2);
	return $_netMoney;	
}

function setBackUrl($per="",$suf=""){
	$url = $_SERVER['HTTP_REFERER'];
	$urlArr = parse_url($url);
	$query = $per."?1=1&".$urlArr['query'].$suf;
	session('listaction',$query);
}
function logInvestCredit($uid,$money,$type,$borrow_id,$duration )
{
	$xs = $type == 1 ? 1 : 2;
	if ($duration == 1){
		$xs = 1;
	}
	$credit = $xs * $duration * $money;
	$data['uid'] = $uid;
	$data['borrow_id'] = $borrow_id;
	$data['invest_money'] = $money;
	$data['duration'] = $duration;
	$data['invest_type'] = $type;
	$data['get_credit'] = $credit;
	$data['add_time'] = time();
	$data['add_ip'] = get_client_ip();
	$newid = M("invest_credit")->add($data);
	$update['invest_credits'] = array("exp","`invest_credits`+{$credit}");
	if ($newid){
		M("members")->where("id={$uid}")->save($update);
	}
}

//是否生日
function isBirth($uid){
	$pre = C('DB_PREFIX');
	$id = M("member_info i")->field("i.idcard")->join("{$pre}members_status s ON s.uid=i.uid")->where("i.uid = $uid AND s.id_status=1 ")->find();
	if(!id)		return false;

	$bir = substr($id['idcard'], 10, 4);
	$now = date("md");

	if( $bir==$now )	return true;
	else 		return false;
}

function sendemail($to,$subject,$body){
	$msgconfig = FS("Webconfig/msgconfig");
	
	import("ORG.Net.Email");
	$port =$msgconfig['stmp']['port'];//25; 
	$smtpserver=$msgconfig['stmp']['server']; 
	$smtpuser = $msgconfig['stmp']['user']; 
	$smtppwd = $msgconfig['stmp']['pass']; 
	$mailtype = "HTML"; 
	$sender = $msgconfig['stmp']['user']; 

	$smtp = new smtp($smtpserver,$port,true,$smtpuser,$smtppwd,$sender); 
	$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype); 
	return $send;
}

//企业直投投标处理方法
function getTInvestUrl($id){
	return __APP__."/tinvest/{$id}".C("URL_HTML_SUFFIX");
	//return __APP__."/tinvest/tdetail?id={$id}";
}

function TinvestMoney($uid,$borrow_id,$num,$duration,$_is_auto = 0){
	$pre = C("DB_PREFIX");
	$done = false;
	$datag = get_global_setting();
	/////////////////////////////锁表  辉 2014-04-1////////////////////////////////////////////////

	$dataname = C('DB_NAME');
	$db_host = C('DB_HOST');
	$db_user = C('DB_USER');
	$db_pwd = C('DB_PWD');
	
	$bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
	$bdb->beginTransaction();
	$bId = $borrow_id;
		   
	$sql1 ="SELECT suo FROM lzh_transfer_borrow_info_lock WHERE id = ? FOR UPDATE";
    $stmt1 = $bdb->prepare($sql1);
	$stmt1->bindParam(1, $bId);    //绑定第一个参数值
    $stmt1->execute();
	
	/////////////////////////////锁表  辉 2014-04-1////////////////////////////////////////////////
	$invest_integral = $datag['invest_integral'];//投资积分
	$fee_rate = $datag['fee_invest_manage'];//投资者成交管理费费率
	$binfo = M("transfer_borrow_info")->field( 
"id,borrow_uid,borrow_money,borrow_interest_rate,borrow_duration,repayment_type,transfer_out,transfer_back,transfer_total,per_transfer,is_show,deadline,min_month,reward_rate,increase_rate,borrow_fee")->find($borrow_id);
	
	
	$vminfo = getMinfo($uid,'m.user_leve,m.time_limit,mm.account_money,mm.back_money,mm.money_collect');
	//不同会员级别的费率
	//($vminfo['user_leve']==1 && $vminfo['time_limit']>time())?$fee_rate=($fee_invest_manage[1]/100):$fee_rate=($fee_invest_manage[0]/100);
	if($num<1){
		return "对不起,您购买的份数小于最低允许购买份数,请重新输入认购份数！";
	}
	if(($binfo['transfer_total']-$binfo['transfer_out'])<$num){
		return "对不起,您购买的份数已超出当前可供购买份数,请重新输入认购份数！";
	}
	
	$money = $binfo['per_transfer'] * $num;
	if(($vminfo['account_money']+$vminfo['back_money'])<$money){
		return "对不起，您的可用余额不足,不能投标";
	}
	$investMoney =D("transfer_borrow_investor");
	$investMoney->startTrans();
		$investinfo['status'] = 1;
		$investinfo['borrow_id'] = $borrow_id;
		$investinfo['investor_uid'] = $uid;
		$investinfo['borrow_uid'] = $binfo['borrow_uid'];
		$investinfo['investor_capital'] = $money;
		$investinfo['transfer_num'] = $num;
		$investinfo['transfer_month'] = $duration;
		$investinfo['is_auto'] = $_is_auto;
		$investinfo['add_time'] = time();
		$investinfo['deadline'] = time() + $investinfo['transfer_month'] * 30 * 24 * 3600;
		$investinfo['reward_money'] = getFloatValue($binfo['reward_rate'] * $money/100, 2);//奖励会在会员投标后一次性发放//getFloatValue($binfo['reward_rate'] * $money * $duration/100, 2);
		$interest_rate = $binfo['borrow_interest_rate'] + $duration * $binfo['increase_rate'];
		$investinfo['investor_interest'] = getFloatValue($interest_rate * $money * $duration/1200, 2);
		$investinfo['final_interest_rate'] = $interest_rate;
		$investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest']/100, 2);
		$invest_info_id = M("transfer_borrow_investor")->add($investinfo);
		
		
		//$endTime = strtotime(date("Y-m-d",time())." 11:59:59");
		$endTime = strtotime(date("Y-m-d",time())." ".$datag['auto_back_time']);//企业直投自动还款时间设置
		$detailInterest = getFloatValue($investinfo['investor_interest']/$duration,2);
		
		$investDetail['repayment_time'] = 0;
		$investDetail['borrow_id'] = $borrow_id;
		$investDetail['invest_id'] = $invest_info_id;
		$investDetail['investor_uid'] = $uid;
		$investDetail['borrow_uid'] = $binfo['borrow_uid'];
		$investDetail['capital'] = $money;
		//$investDetail['interest'] = $i == $duration - 1 ? $detailInterest:0;
		$investDetail['interest'] = getFloatValue($investinfo['investor_interest'],2);
		$investDetail['interest_fee'] = $investinfo['invest_fee'];
		$investDetail['status'] = 7;
		$investDetail['receive_interest'] = 0;
		$investDetail['receive_capital'] = 0;
		$investDetail['sort_order'] = 1;
		$investDetail['total'] = 1;
		$investDetail['deadline'] = $endTime +$duration*30*24*3600;
		$IDetail[] = $investDetail;
		
		
		$Tinvest_defail_id = M("transfer_investor_detail")->addAll($IDetail);
		if($invest_info_id && $Tinvest_defail_id){
			$investMoney->commit();
			$res = memberMoneyLog($uid,37,-$money,"对{$borrow_id}号企业直投进行了投标",$binfo['borrow_uid']);
			
			//借款人资金增加
			$_borraccount = memberMoneyLog($binfo['borrow_uid'],17,$money,"第{$borrow_id}号企业直投已被认购{$money}元，{$money}元已入帐");//借款入帐
			if(!$_borraccount) return false;//借款者帐户处理出错
			if((intval($binfo['transfer_out'])+$num)==$binfo['transfer_total']){//如果企业直投被认购完毕，则扣除借款人借款管理费
				$_borrfee = memberMoneyLog($binfo['borrow_uid'],18,-$binfo['borrow_fee'],"第{$borrow_id}号企业直投被认购完毕，扣除借款管理费{$binfo['borrow_fee']}元");//借款管理费扣除
				if(!$_borrfee) return false;//借款者帐户处理出错
			}
			
		
			//借款天数、还款时间
			$endTime = strtotime(date("Y-m-d",time())." ".$_P_fee['back_time']);
			$deadline_last = strtotime("+{$duration} month",$endTime);
			$getIntegralDays = intval(($deadline_last-$endTime)/3600/24);//借款天数
			
			
			//////////////////////////增加投资者的投资积分 2013-08-28 fans////////////////////////////////////
		
			$integ = intval($investinfo['investor_capital']*$getIntegralDays*$invest_integral/1000);//dump($invest_integral);exit;
			if($integ>0){
				$reintegral = memberIntegralLog($uid,2,$integ,"对{$borrow_id}号企业直投进行投标，应获积分：".$integ."分,投资金额：".$investinfo['investor_capital']."元,投资天数：".$getIntegralDays."天");
				if(isBirth($uid)){
					$reintegral = memberIntegralLog($uid,2,$integ,"亲，祝您生日快乐，本站特赠送您{$integ}积分作为礼物，以表祝福。");
				}
			}
			
			//////////////////////////增加投资者的投资积分 2013-08-28 fans////////////////////////////////////
			
			$res1 = memberMoneyLog($uid,39,$investinfo['investor_capital'],"您对第{$borrow_id}号企业直投投标成功，冻结本金成为待收金额",$binfo['borrow_uid']);
			$res2 = memberMoneyLog($uid,38,$investinfo['investor_interest'] - $investinfo['invest_fee'], "第{$borrow_id}号企业直投应收利息成为待收利息", $binfo['borrow_uid']);
				
			//投标奖励
			if($investinfo['reward_money']>0){
				$_remoney_do = false;
				$_reward_m = memberMoneyLog($uid,41,$investinfo['reward_money'],"第{$borrow_id}号企业直投认购成功，获取投标奖励",$binfo['borrow_uid']);
				$_reward_m_give = memberMoneyLog($binfo['borrow_uid'],42,-$investinfo['reward_money'],"第{$borrow_id}号企业直投已被认购，支付投标奖励",$uid);
				if($_reward_m && $_reward_m_give) $_remoney_do = true;
			}
			//投标奖励
			//////////////////////邀请奖励开始////////////////////////////////////////
			$vo = M('members')->field('user_name,recommend_id')->find($uid);
			$_rate = $datag['award_invest']/1000;//推广奖励
			$jiangli = getFloatValue($_rate * $investinfo['investor_capital'],2);
			if($vo['recommend_id']!=0){
				memberMoneyLog($vo['recommend_id'],13,$jiangli,$vo['user_name']."对{$borrow_id}号标投资成功，你获得推广奖励".$jiangli."元。",$uid);
			}
			/////////////////////邀请奖励结束/////////////////////////////////////////
		
			if(intval($binfo['transfer_out'])==0){
				$binfo['transfer_out'] +=$num;
			}
			$progress = getfloatvalue($binfo['transfer_out'] / $binfo['transfer_total'] * 100, 2);
			$upborrowsql = "update `{$pre}transfer_borrow_info` set ";
			$upborrowsql .= "`transfer_out` = `transfer_out` + {$num},";
			$upborrowsql .= "`progress`= {$progress}";
			if ($progress == 100 || ($binfo['transfer_out'] + $num == $binfo['transfer_total'])){
				$upborrowsql .= ",`is_show` = 0";
			}
			$upborrowsql .= " WHERE `id`={$borrow_id}";
			
			$upborrow_res = M()->execute($upborrowsql);
			if(!$res || !$res1 || !$res2){
				M("transfer_borrow_investor")->where("id={$invest_info_id}")->delete();
				M("transfer_investor_detail")->where("invest_id={$invest_info_id}")->delete();
				$upborrowsql = "update `{$pre}transfer_borrow_info` set ";
				$upborrowsql .= "`transfer_out` = `transfer_out` - {$num}";
				if($binfo['transfer_out'] + $num == $binfo['transfer_total']){
					$upborrowsql .= ",`is_show` = 1";
				}
				$upborrowsql .= " WHERE `id`={$borrow_id}";
				$upborrow_res = M()->execute($upborrowsql);
				$done = false;
			}else{
			////////////////////////////////////////回款续投奖励规则 fan 2013-07-20////////////////////////////
				$today_reward = explode("|",$datag['today_reward']);
				if($binfo['borrow_duration']==1){
					$reward_rate = floatval($today_reward[0]);
				}else if($binfo['borrow_duration']==2){
					$reward_rate = floatval($today_reward[1]);
				}else{
					$reward_rate = floatval($today_reward[2]);
				}
			////////////////////////////////////////回款续投奖励规则 fan 2013-07-20////////////////////////////
				$vd['add_time'] = array("lt",time());
				$vd['investor_uid'] = $uid;
				$borrow_invest_count = M("transfer_borrow_investor")->where($vd)->count('id');//检测是否投过标且大于一次
				//dump($borrow_invest_count);exit;
				if($reward_rate>0 && $vminfo['back_money']>0 && $borrow_invest_count>0){//首次投标不给续投奖励
					if($money>$vminfo['back_money']){//如果投标金额大于回款资金池金额，有效续投奖励以回款金额资金池总额为标准，否则以投标金额为准
						$reward_money_s = $vminfo['back_money'];
					}else{
						$reward_money_s = $money;
					}
					
					$save_reward['borrow_id'] = $borrow_id;
					$save_reward['reward_uid'] = $uid;
					$save_reward['invest_money'] = $reward_money_s;//如果投标金额大于回款资金池金额，有效续投奖励以回款金额资金池总额为标准，否则以投标金额为准
					$save_reward['reward_money'] = $reward_money_s*$reward_rate/1000;//续投奖励
					$save_reward['reward_status'] = 1;
					$save_reward['add_time'] = time();
					$save_reward['add_ip'] = get_client_ip();
					$newidxt = M("today_reward")->add($save_reward);
					
					//dump($newidxt);exit;
					if($newidxt){
						$result =memberMoneyLog($uid,40,$save_reward['reward_money'],"企业直投续投有效金额({$reward_money_s})的奖励({$borrow_id}号企业直投)奖励",0,"@网站管理员@");
					}
				}
				$done = true;
			}
		}else{
			$investMoney->rollback();
		}
		return $done;
}


function getTransferLeftmonth($deadline){
	$lefttime = $deadline-time();
	if($lefttime<=0) return 0;
	//echo $lefttime/(24*3600*30);
	$leftMonth = floor($lefttime/(24*3600*30));
	return $leftMonth;
}

//后台管理员登录日志
function alogs($type,$tid,$tstatus,$deal_info='',$deal_user='' ){
	$arr = array();
	$arr['type'] = $type;
	$arr['tid'] = $tid;
	$arr['tstatus'] = $tstatus;
	$arr['deal_info'] = $deal_info;

	$arr['deal_user'] = ($deal_user)?$deal_user:session('adminname');
	$arr['deal_ip'] = get_client_ip();
	$arr['deal_time'] = time();
	//dump($arr);exit;
	$newid = M("auser_dologs")->add($arr);
	return $newid;
}
function getMarketUrl($id){
	return __APP__."/Market/{$id}".C('URL_HTML_SUFFIX');
}
function cnsubstr2($str, $length, $start=0, $charset="utf-8", $suffix=true)
{
	   $str = strip_tags($str);
	   if(function_exists("mb_substr"))
	   {
			   if(mb_strlen($str, $charset) <= $length) return $str;
			   $slice = mb_substr($str, $start, $length, $charset);
	   }
	   else
	   {
			   $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			   $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			   $re['gbk']          = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			   $re['big5']          = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			   preg_match_all($re[$charset], $str, $match);
			   if(count($match[0]) <= $length) return $str;
			   $slice = join("",array_slice($match[0], $start, $length));
	   }
	   if($suffix) return $slice;
	   return $slice;
}


/*把数字转化成大写**/
function cny($ns) { 
    static $cnums=array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖"), 
        $cnyunits=array("圆","角","分"), 
        $grees=array("拾","佰","仟","万","拾","佰","仟","亿"); 
    list($ns1,$ns2)=explode(".",$ns,2); 
    $ns2=array_filter(array($ns2[1],$ns2[0])); 
    $ret=array_merge($ns2,array(implode("",_cny_map_unit(str_split($ns1),$grees)),"")); 
    $ret=implode("",array_reverse(_cny_map_unit($ret,$cnyunits))); 
    return str_replace(array_keys($cnums),$cnums,$ret); 
}

function _cny_map_unit($list,$units) { 
    $ul=count($units); 
    $xs=array(); 
    foreach (array_reverse($list) as $x) { 
        $l=count($xs); 
        if ($x!="0" || !($l%4)) $n=($x=='0'?'':$x).($units[($l-1)%$ul]); 
        else $n=is_numeric($xs[0][0])?$x:''; 
        array_unshift($xs,$n); 
    } 
    return $xs; 
}
/*把数字转化成大写结束**/


//下面四个不能删除。

/**
 * 是否移动端访问访问
 *
 * @return bool
 */
function ListMobile()
{
	if('test' == getsubdomain()){
		return allowPhone();
	}
	return 'm' == getsubdomain();
}



function getsubdomain(){
	$url="http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'];
	preg_match("#http://(.*?)\\.#i",$url,$match);
	return strtolower($match[1]);
}


function allowPhone(){
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	return (strpos($agent, 'iphone')) || (strpos($agent, 'android'));
}

function is_Phone(){
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if((strpos($agent, 'windows nt'))){
		return 'pc';
	}else if((strpos($agent, 'iphone'))){
		return 'iphone';
	}else if((strpos($agent, 'ipad'))){
		return 'ipad';
	}else if((strpos($agent, 'android'))){
		return 'android';
	}else{
		return 'all';
	}
}
?>
