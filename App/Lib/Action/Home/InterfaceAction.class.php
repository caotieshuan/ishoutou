<?php
// 本类由系统自动生成，仅供测试用途
class InterfaceAction extends Action {

	private $iskey = 'fdsafdsafdsa';

	function _initialize(){
		if(!in_array(strtolower(ACTION_NAME),array('login'))){
			$token = htmlspecialchars($_GET['token']);
			$this->verToken($token);
		};
	}
	protected function verToken($token){
		if(empty($token)){
			exit(self::json_encode(array('code'=>1,'message'=>'No Token')));
		}
		$dateline = M('interface_token')->where(array('token'=>$token))->getField('dateline');
		if(empty($dateline)){
			exit(self::json_encode(array('code'=>1,'message'=>'error Token')));
		}
		if(time()-$dateline > 3600){
			exit(self::json_encode(array('code'=>1,'message'=>'be overdue Token')));
		}
	}
	public function Login(){
		$user = htmlspecialchars($_GET['username']);
		$pass = htmlspecialchars($_GET['password']);
		$userdata = array(
			'tianyan'=>'fsdafdsa32',
			'wdzj' =>'432fdsa42',
		);

		$re=-1;
		if(isset($userdata[$user])){
			if($pass == $userdata[$user]){
				$token = substr(md5($this->iskey.date('Y-m-d H:i:s')),8,16);
				$a = M('interface_token')->add(array(
					'token'=>$token,
					'dateline'=>time(),
					'username'=>$user
				));
				if($a){
					$re=1;
					$result=array(
						'token'=>$token,
						'dateline'=>date('Y-m-d H:i:s')
					);
				}else{
					$result=array(
						'token'=>null,
						'message'=>'获取token失败，请稍后重试'
					);
				}
			}else{
				$result = array(
					'token'=>null,
					'message'=>'密码不正确'
				);
			}
		}else{
			$result = array(
				'token'=>null,
				'message'=>'获取token失败，请稍后重试'
			);
		}
		exit(self::json_encode(array("result"=>$re,'data'=>$result)));
	}


	public function tianyan(){
		//预发标的借款
		$parm=array();
		$searchMap = array();
		$searchMap['borrow_status']=0;
		$parm['map'] = $searchMap;
		$parm['orderby']="id DESC";
		//预发标的借款
		$search = array();
		$search['borrow_status']=array("in",'2,4,6,7,9,10');
		$search['stock_type']=array("in","1,2,3,4");
		$parm['map'] = $search;
		$parm['pagesize'] = 10;
		$data = $this->getTyBorrowList($parm);
		exit(self::json_encode($data));
	}

	public function Record(){
		$id = (int)$_GET['id'];
		if($id){
			$result['result_code'] = 1;
			$result['result_msg'] = '获取数据成功';
			$result['page_count'] = 1;
			$result['totalCount'] = 1;
			$result['loans'] = $this->investTyRecord($id);
		}else{
			$result['result_code'] = 0;
			$result['result_msg'] = '参数错误';
		}
		exit(self::json_encode($result));
	}

	public function wangdaizhijia(){
		//预发标的借款
		$parm=array();
		$searchMap = array();
		$searchMap['borrow_status']=0;
		$parm['map'] = $searchMap;
		$parm['orderby']="id DESC";
		//预发标的借款
		$search = array();
		$search['borrow_status']=array("in",'2,4,6,7,9,10');
		$search['stock_type']=array("in","1,2,3,4,5");
		$parm['map'] = $search;
		$parm['pagesize'] = 10;
		$data = $this->getWdzjBorrowList($parm);

		echo self::json_encode($data);
		exit;
	}

	//天眼获取借款列表
	protected function getTyBorrowList($parm=array()){
		if(empty($parm['map'])) return;
		$map= $parm['map'];

		$dateline1 = $_GET['time_from'];
		$dateline2 = $_GET['time_to'];
		if($this->is_date($dateline1) && $this->is_date($dateline2)){
			$start = strtotime($dateline1);
			$end =  strtotime($dateline2);
			$timespan = $start.",".$end;
			$map['first_verify_time'] = array("between",$timespan);
		}


		$orderby= $parm['orderby'];
		$pagesize =  intval($_GET['pagesize']);
		$status = (int)$_GET['status'];
		if(array_key_exists('status',$_GET)){
			if($status){
				$map['full_time'] = array('gt',0);
			}else{
				$map['full_time'] = array('eq',0);
			}
		}

		$parm['pagesize'] = $pagesize ? $pagesize : 10;

		$searchMap = array();
		//$searchMap['stock_type'] = array("1"=>"天天盈","2"=>"月月盈",'4'=>"打新宝");
		//关联目前的还款类型
		$searchMap['repayments'] = array(
			1=>1,
			2=>2,
			3=>5,
			4=>1,
			5=>1,
		);
		$result = array();
		$count = M('borrow_info')->where($map)->count('id');
		$page = (int)$_GET['page'];
		$page = $page ? $page : 1;
		$offset=$parm['pagesize']*($page-1);

		$result['result_code'] = 1;
		$result["result_msg"] = '数据获取成功';
		$result['page_count']=ceil($count/$parm['pagesize']);
		//$result['totalCount'] = $count;
		$result['page_index'] = $page;

		$Lsql = "$offset,{$parm['pagesize']}";

		$suffix=C("URL_HTML_SUFFIX");

		$field = "id,borrow_name as title,has_borrow,borrow_duration as period,borrow_interest_rate as rate,reward_num as reward,borrow_money as amount,repayment_type as p_type,stock_type,capital_name,full_time,first_verify_time";
		$list = M('borrow_info')->field($field)->where($map)->order($orderby)->limit($Lsql)->select();

		foreach($list as &$v){
			$toId = M('member_to')->where(array('username'=>array('like',$v['capital_name'])))->getField('id');
			if(empty($toId)){
				$toId = M('member_to')->add(array('username'=>$v['capital_name']));
			}
			$progress = $v['has_borrow']/$v['amount'];
			$v['username'] = $toId	;
			$v['status'] = 1 == $progress ? 1:0;
			$v['p_type']= 1 == $v['p_type'] ? 0:1;
			$v['userid'] = $toId;
			//$v['c_type'] = $v['stock_type'];
			$v['c_type'] = 4;//使用2代表全部为低压标
			$v['pay_way'] = $searchMap['repayments'][$v['p_type']];
			$v['rate'] = $v['rate']/100;//增加floor
			$v['reward'] = $v['reward'].'%';//增加floor
			$v['process'] = $progress;//增加floor
			$v['platform_name'] = '手投网';

			if( 100 == $progress && empty($v['full_time'])){
				$tmp = end($v['subscribes']);
				$full_time = $tmp['addDate'];
			}else{
				$full_time = date('Y-m-d H:i:s',$v['full_time']);
			}

			$v['end_time'] = $full_time;
			$v['start_time'] = date('Y-m-d H:i:s',$v['first_verify_time']);
			$v['invest_num'] = M('borrow_investor')->where('borrow_id='.$v['id'])->count('id');
			$v['url'] = 	MU("Home/invest","invest",array("id"=>$v['id'],"suffix"=>$suffix),true);
			unset($v['has_borrow'],$v['repayment_type'],$v['capital_name'],$v['stock_type'],$v['user_name'],$v['full_time'],$v['first_verify_time']);
		}
		$result['loans'] = $list;
		return $result;
	}
	//天眼投资列表
	protected function investTyRecord($borrow_id){
		$suffix=C("URL_HTML_SUFFIX");
		$list = M("borrow_investor as b")
			->join(C(DB_PREFIX)."members as m on  b.investor_uid = m.id")
			->join(C(DB_PREFIX)."borrow_info as i on  b.borrow_id = i.id")
			->field('b.investor_capital as account, b.add_time, b.is_auto as type, m.user_name')
			->where('b.borrow_id='.$borrow_id)->order('b.id')->select();
		foreach($list as &$v){
			$ouId = M('member_ou')->where(array('username'=>array('like',$v['user_name'])))->getField('id');
			if(empty($ouId)){
				$ouId = M('member_ou')->add(array('username'=>$v['user_name']));
			}
			$v['id'] = $borrow_id;
			$v['useraddress'] = '';
			$v['money'] = $v['account'];
			$v['username'] = $ouId;
			$v['userid'] = $ouId;
			$v['type'] = 1 == $v['is_auto'] ? '自动' : '手动';
			$v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
			$v['status'] = '成功';
			$v['link'] = 	MU("Home/invest","invest",array("id"=>$borrow_id,"suffix"=>$suffix),true);
			unset($v['user_name']);
		}
		return $list;
	}


	//网贷之家获取借款列表
	protected function getWdzjBorrowList($parm=array()){
		if(empty($parm['map'])) return;

		$pagesize =  intval($_GET['pagesize']);
		$dateline = $_GET['date'];
		$parm['pagesize'] = $pagesize ? $pagesize : 10;
		$map= $parm['map'];
		if($this->is_date($dateline)){
			$start = strtotime($dateline);
			$end = $start+86400;

			$timespan = $start.",".$end;
			$map['first_verify_time'] = array("between",$timespan);
		}
		$orderby= $parm['orderby'];

		$searchMap = array();
		$searchMap['stock_type'] = array("1"=>"天天盈","2"=>"月月盈",'4'=>"打新宝");

		//关联目前的还款类型
		$searchMap['repayments'] = array(
			1=>1,
			2=>2,
			3=>5,
			4=>1,
			5=>1,
		);
		$result = array();

		$count = M('borrow_info')->where($map)->count('id');
		$page = (int)$_GET['page'];
		$page = $page ? $page : 1;
		$offset=$parm['pagesize']*($page-1);

		$result['totalPage']=ceil($count/$parm['pagesize']);
		$result['totalCount'] = $count;
		$result['currentPage'] = $page;


		$result['totalAmount'] = (int)M('borrow_info')->where($map)->sum('borrow_money');


		$Lsql = "$offset,{$parm['pagesize']}";

		$suffix=C("URL_HTML_SUFFIX");

		$field = "id as projectId,borrow_name as title,has_borrow,borrow_duration as deadline,borrow_interest_rate as interestRate,reward_num as reward,borrow_money as amount,repayment_type,stock_type,capital_name,full_time,first_verify_time,borrow_info as amountUsedDesc";
		$list = M('borrow_info')->field($field)->where($map)->order($orderby)->limit($Lsql)->select();

		foreach($list as &$v){
			$toId = M('member_to')->where(array('username'=>array('like',$v['capital_name'])))->getField('id');
			if(empty($toId)){
				$toId = M('member_to')->add(array('username'=>$v['capital_name']));
			}
			$progress = $v['has_borrow']/$v['amount']*100;
			$v['interestRate'] = $v['interestRate'].'%';
			$v['schedule'] = $progress > 50 ? floor($progress) : ceil($progress);//增加floor
			$v['deadlineUnit']= 1 == $v['repayment_type'] ? '天':'月';
			$v['type']= '抵押标';
			//$v['type']= $searchMap['stock_type'][$v['stock_type']];
			$v['repaymentType'] = $searchMap['repayments'][$v['repayment_type']];
			$v['subscribes'] = $this->investRecord($v['projectId']);
			$v['province'] = 	'';
			$v['city'] = 	'';
			$v['userName'] = $toId	;
			$v['userAvatarUrl'] = 	'';
			$v['revenue'] = 	'';
			$v['loanUrl'] = 	MU("Home/invest","invest",array("id"=>$v['projectId'],"suffix"=>$suffix),true);

			if( 100 == $progress && empty($v['full_time'])){
				$tmp = end($v['subscribes']);
				$full_time = $tmp['addDate'];
			}else{
				$full_time = date('Y-m-d H:i:s',$v['full_time']);
			}

			$v['successTime'] = $full_time;
			$v['publishTime'] = date('Y-m-d H:i:s',$v['first_verify_time']);
			unset($v['has_borrow'],$v['repayment_type'],$v['capital_name'],$v['stock_type'],$v['user_name'],$v['full_time'],$v['first_verify_time']);
		}
		$result['borrowList'] = $list;
		return $result;
	}

	//网贷之家获取投资列表
	protected function investRecord($borrow_id){
		$list = M("borrow_investor as b")
			->join(C(DB_PREFIX)."members as m on  b.investor_uid = m.id")
			->join(C(DB_PREFIX)."borrow_info as i on  b.borrow_id = i.id")
			->field('b.investor_capital as amount, b.add_time, b.is_auto as type, m.user_name')
			->where('b.borrow_id='.$borrow_id)->order('b.id')->select();
		foreach($list as &$v){
			$ouId = M('member_ou')->where(array('username'=>array('like',$v['user_name'])))->getField('id');

			if(empty($ouId)){
				$ouId = M('member_ou')->add(array('username'=>$v['user_name']));
			}
			$v['validAmount'] = $v['amount'];
			$v['subscribeUserName'] = $ouId;
			$v['addDate'] = date('Y-m-d H:i:s',$v['add_time']);
			$v['status'] = 1;

			unset($v['add_time'],$v['user_name']);
		}
		return $list;
	}
	static function json_encode($input){

		$fla = $_GET['fla'];
		if(empty($fla)){
			return json_encode($input);
		}

		// 从 PHP 5.4.0 起, 增加了这个选项.
		if(defined('JSON_UNESCAPED_UNICODE')){
			return json_encode($input, JSON_UNESCAPED_UNICODE);
		}
		if(is_string($input)){
			$text = $input;
			$text = str_replace('\\', '\\\\', $text);
			$text = str_replace(
				array("\r", "\n", "\t", "\""),
				array('\r', '\n', '\t', '\\"'),
				$text);
			return '"' . $text . '"';
		}else if(is_array($input) || is_object($input)){
			$arr = array();
			$is_obj = is_object($input) || (array_keys($input) !== range(0, count($input) - 1));
			foreach($input as $k=>$v){
				if($is_obj){
					$arr[] = self::json_encode($k) . ':' . self::json_encode($v);
				}else{
					$arr[] = self::json_encode($v);
				}
			}
			if($is_obj){
				return '{' . join(',', $arr) . '}';
			}else{
				return '[' . join(',', $arr) . ']';
			}
		}else{
			return $input . '';
		}
	}

	/**
	 * 验证日期格式是否正确
	 * @param string $date
	 * @param string $format
	 * @return boolean
	 */
	function is_date($date){
		list($y,$m,$d)=explode('-',$date);
		return checkdate($m,$d,$y);
	}
/**
	public function test(){
		$t=$_GET['t'];
		$token = $_GET['token'];
		$id = $_GET['id'];
		$url = 'http://www.ishoutou.com/interface/'.$t.'?token='.$token.($id ? '&id='.$id : ''); //这儿填页面地址
		echo $url;
		echo '<br>';
		echo '<pre>';
		for($i=1;$i<4; $i++){
			$info=file_get_contents($url.'&page='.$i);
			print_r(json_decode($info));
		}
		exit;
	}

	public function loading(){
		$list = M('warelog')->select();
		echo '<pre>';
		foreach($list as $val){
			$val['contxt'] = unserialize($val['contxt']);
			print_r($val);
		}
		exit;

	}
 **/
}