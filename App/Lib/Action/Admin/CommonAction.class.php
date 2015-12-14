<?php
/**
 * 后台公用接口
 * @version 140918
 * @author jixiang <jixiang_f@163.com>
 * @copyright taoweikeji
 */
class CommonAction extends ACommonAction
{
    public function member(){
		$utype = C('XMEMBER_TYPE');
		$area=get_Area_list();
		$uid=intval($_GET['id']);
		$vo = M('members m')
		      ->field("m.user_email,m.customer_name,m.user_phone,m.id,m.credits,m.is_ban,m.user_type,m.user_name,m.integral,m.active_integral,mi.*,mm.*,mb.*")
		      ->join("{$this->pre}member_info mi ON mi.uid=m.id")
		      ->join("{$this->pre}member_money mm ON mm.uid=m.id")
		      ->join("{$this->pre}member_banks mb ON mb.uid=m.id")
		      ->where("m.id={$uid}")
		      ->find();
		$vo['province'] = $area[$vo['province']];
		$vo['city'] = $area[$vo['city']];
		$vo['area'] = $area[$vo['area']];
		$vo['province_now'] = $area[$vo['province_now']];
		$vo['city_now'] = $area[$vo['city_now']];
		$vo['area_now'] = $area[$vo['area_now']];
		$vo['is_ban'] = ($vo['is_ban']==0)?"未冻结":"<span style='color:red'>已冻结</span>";
		$vo['user_type'] = $utype[$vo['user_type']];

        //$vo['money_collect'] = M('investor_detail')->where(" investor_uid={$uid} AND status =7 ")->sum("capital+interest-interest_fee");
        //$vo['money_need'] = M('investor_detail')->where(" borrow_uid={$uid} AND status in(4,7) ")->sum("capital+interest");
		//$vo['money_all'] = $vo['account_money'] + $vo['money_freeze'] + $vo['money_collect'] - $vo['money_need'];
		
		$this->assign("capitalinfo",getMemberBorrowScan($uid));
		$this->assign("wc",getUserWC($uid));
        $this->assign("credit", getCredit($uid));
        $this->assign("vo",$vo);
		$this->assign("user",$vo['user_name']);

		//*******2013-11-23*************
		$minfo =getMinfo($uid,true);
        $this->assign("minfo",$minfo); 

		$this->assign('benefit', get_personal_benefit($uid)); //收益相关
		$this->assign('out', get_personal_out($uid)); //支出相关
		$this->assign('pcount', get_personal_count($uid));
        $this->display();
    }
	
    // 渲染通迅系统页面@140825@方吉祥
	public function communication_system(){
		if(!empty($_POST['userName'])){
        	$this->assign('userName', $_POST['userName']);
		}else{
			$this->assign('userName', '');
		}
		
       echo json_encode($this->fetch());
    }

    /**
     * 通知到个人
     * 20140826
     * 方吉祥
     */
    function msgByAccount(){
    	
    	if(empty($_POST['content'])){
    		$this->ajaxReturn('', '请输入发送内容', 0);
    	}
    	
    	if(!empty($_POST['user_name'])){
    	    $uInfo = M('members m')
    	    ->field(" m.id,m.user_email,m.user_phone,ms.email_status,ms.phone_status ")
    	    ->join(" lzh_members_status ms ON m.id=ms.uid ")
    	    ->where(" m.user_name = '". $_POST['user_name'] ."' ")
    	    ->find();
    	    
    	    if (empty($uInfo)){
    	        $this->ajaxReturn('', '找不到用户  '. $_POST['user_name'], 0);
    	    }
    	}else{
    	    $this->ajaxReturn('', '请输入用户名  '. $_POST['user_name'], 0);
    	}
    	$data = array();
    	$data['admin_id'] = $_SESSION['admin_id'];
    	$data['admin_real_name'] = $_SESSION['adminname'];
    	$data['add_time'] = time();
    	$data['is_inner'] = 0;
    	$data['user_phone'] = '';
    	$data['user_name'] = '';
    	$data['user_email'] = '';
    	$data['title'] = trim($_POST['title']);
    	$data['content'] = trim($_POST['content']);
    	$content = $_POST['content'];
    	$title = $_POST['title'];
    	$username = $_POST['user_name'];
    	$type = $_POST['type'];
		$data['user_name'] = $username;
		$sPhone = $sMail = $sInner = 0;
		if($uInfo['phone_status'] && in_array('1', $type)){ // 短信
		    $sign = $this->_check_sms(); // 检测接口和短信落款是否配置
			$sPhone = $this->_send_sms(trim($uInfo['user_phone']), $content.$sign);
			$data['user_phone'] = $uInfo['user_phone'];
		}
		
		if($uInfo['email_status'] && in_array('2', $type)){ // 邮件
			$sMail = $this->_send_mail(trim($uInfo['user_email']), $title, $content);
			$data['user_email'] = $uInfo['user_email'];
		}
		
		if(in_array('3', $type)){ // 站内信
			$sInner = $this->_send_inner(array(
			        'uid' => $uInfo['id'],
			        'title' => $title,
			        'msg' => $content,
			        'send_time' => time()
			    ));
			$data['is_inner'] = 1;
		}
		if($sPhone || $sMail || $sInner){
		    $tip = array("对{$username}发送");
		    if($sPhone){
		       array_push($tip, '短信');
		    }
		    
		    if($sMail){
		        array_push($tip, '邮件');
		    }
		    
		    if($sInner){
		        array_push($tip, '站内信');
		    }
		    
			M('smslog')->add($data);
			alogs("Smslog",0,1, implode('、', $tip).'成功');//管理员操作日志
			$this->ajaxReturn('', '发送成功', 1);
		}else{
			alogs("Smslog",0,0,'执行会员账户通讯通知操作失败！');//管理员操作日志
			$this->ajaxReturn('', '发送失败', 0);
		}
    	
    }
    
    // 通知到地址@140825@方吉祥
    function msgByAddress(){
        
        if(empty($_POST['content'])){
            $this->ajaxReturn('', '请输入发送内容', 0);
        }
        
        $data = array();
        $data['admin_id'] = $_SESSION['admin_id'];
        $data['admin_real_name'] = $_SESSION['adminname'];
        $data['add_time'] = time();
        $data['is_inner'] = 0;
        $data['user_phone'] = '';
        $data['user_name'] = '';
        $data['user_email'] = '';
        $data['title'] = trim($_POST['title']);
    	$data['content'] = trim($_POST['content']);
        $data['is_inner'] = 0;
         
        $content = $_POST['content'];
        $title = $_POST['title'];
        $sPhone = $sMail = 0;
        if(!empty($_POST['user_phone'])){ // 短信
            $userPhone = $_POST['user_phone'];
            $has1 = M('members m')
            ->join(" lzh_members_status ms ON m.id=ms.uid ")
            ->where(" m.user_phone = '". $userPhone ."' AND ms.phone_status = 1")
            ->count();
            if (empty($has1)){
                $this->ajaxReturn('', '找不到手机号为 '. $userPhone .' 会员或会员手机号码未认证', 0);
            }
            $data['user_name'] = '短信定向发送';
        }
        
        if(!empty($_POST['user_email'])){ // 邮件
            
            $userMail = $_POST['user_email'];
            $has2 = M('members m')
            ->join(" lzh_members_status ms ON m.id=ms.uid ")
            ->where(" m.user_email = '". $userMail ."' AND ms.email_status = 1")
            ->count();
            if (empty($has2)){
                $this->ajaxReturn('', '找不到邮件地址为 '. $userMail .' 会员或会员邮箱未认证', 0);
            }
            
            $data['user_name'] = '邮件定向发送';
        }
        
        if($userMail && $userPhone){
            $data['user_name'] = '邮件、短信定向发送';
        }
        
        if($has1){ // 短信
            $sign = $this->_check_sms();
            $sPhone = $this->_send_sms(trim($userPhone), $content.$sign);
            $data['user_phone'] = $userPhone;
        }
        
        if($has2){ // 邮件
            $userMail = $_POST['user_email'];
            $sMail = $this->_send_mail(trim($userMail), $title, $content);
            $data['user_email'] = $userMail;
        }
        
        if($sPhone || $sMail){
            
            $tip = array('成功发送');
            if($sPhone){
                array_push($tip, '短信');
            }
            
            if($sMail){
                array_push($tip, '邮件');
            }
            
            M('smslog')->add($data);
            alogs("Smslog",0,1, implode('、', $tip).'通知');//管理员操作日志
            $this->ajaxReturn('', '发送成功', 1);
        }else{
            alogs("Smslog",0,0,'执行会员账户通讯通知操作失败！');//管理员操作日志
            $this->ajaxReturn('', '发送失败', 0);
        }
    }
    
    // 通知到用户组@140825@方吉祥
    function msgByGroup(){
        
        if(empty($_POST['content'])){
            $this->ajaxReturn('', '请输入发送内容', 0);
        }
        
        $content = $_POST['content'];
        $title = $_POST['title'];
        $type = $_POST['type'];
        
        $map = ' (ms.email_status OR ms.phone_status) AND ';
        switch ($_POST['user_group']){
            case 2:
                $map = ' (m.user_leve=1 AND m.time_limit>'. time() .')';
                $likename = 'vip会员';
                break;
            case 3:
                $map = ' (m.user_leve=0 OR m.time_limit<'. time() .')';
                $likename = '非vip会员';
                break;
            case 1:
            default:
                $map = '';
                $likename = '所有会员';
                break;
        }
        
        $data = array();
        $data['admin_id'] = $_SESSION['admin_id'];
        $data['admin_real_name'] = $_SESSION['adminname'];
        $data['add_time'] = time();
        $data['is_inner'] = 0;
        $data['user_name'] = $likename;
        $data['user_phone'] = 0;
        $data['user_email'] = 0;
        $data['title'] = trim($_POST['title']);
    	$data['content'] = trim($_POST['content']);
        
        $page = 1;
        $limit = 300;
        $flag = 1;
        set_time_limit(0);//设置脚本最大执行时间
        
        while (true){
            $inner = $mail = $sms = array();
            $offset = $limit * ($page -1);
            $list = M('members m')->field(" m.id,m.user_email,m.user_phone ")->join(" lzh_members_status ms ON m.id=ms.uid ")->where($map)->order('id')->limit("$offset,$limit")->select();
            $page++;
            if(empty($list)){
                set_time_limit(30); // 还原
                break;
            }
            foreach ($list as $r){ // 添加站内信要发送的内容
                array_push($inner, array(
                    'uid' => $r['id'],
                    'title' => $title,
                    'msg' => $content,
                    'send_time' => time()
                ));
                
                if(!empty($r['user_email'])){
                    array_push($mail, $r['user_email']);
                }
        
                if(!empty($r['user_phone'])){
                    array_push($sms, $r['user_phone']);
                }
            }
            if(in_array('3', $type)){ // 站内信
                $data['is_inner'] = 1;
                if(!$this->_send_inner($inner, true)){
                    $flag--;
                }
            }
            
            if(!empty($mail) && in_array('2', $type)){
                $data['user_email'] = 1;
                if(!$this->_send_mail($mail, $title, $content)){
                    $flag--;
                }
            }
            
            if(!empty($sms) && in_array('1', $type)){ // 短信
                $data['user_phone'] = 1;
                $sign = $this->_check_sms();
                if(!$this->_send_sms($sms, $content.$sign)){
                    $flag--;
                }
            }
        }
         
        if($flag){
            M('smslog')->add($data);
            $tip = $flag === 1 ? '' : '部分';
            alogs("Smslog",0,1, "对{$likename}执行通讯通知{$tip}操作成功");//管理员操作日志
            $this->ajaxReturn('', $likename.$tip.'发送成功', 1);
        }else{
            alogs("Smslog",0,1, "对{$likename}执行通讯通知操作失败");//管理员操作日志
            $this->ajaxReturn('', $likename.$tip.'发送失败', 0);
        }
    }
    
    /**
     * 获取通知记录 带分页 查询功能
     * 20140826
     * 方吉祥
     */
    function msGetData(){
    	
        if(!empty($_GET['byId'])){
            $map = M('smslog')->field('title,content')->find($_GET['byId']);
            if(empty($map)){
                $map = array('title'=> '记录不存在或查询错误', 'content'=> '记录不存在或查询错误');
            }
            $this->ajaxReturn($map, '', 0);
        }
    	$condition = "admin_id='{$_SESSION['admin_id']}'";
    	
    	// 开始时间结束时间都选择的话
    	if(!empty($_POST['start_time']) && !empty($_POST['end_time'])){
    		$start = strtotime($_POST['start_time']);
    		$end = strtotime($_POST['end_time']);
    		$condition .= " AND add_time < {$end} AND add_time > {$start}";
    	}else{
    		// 只选择开始时间
    		if(!empty($_POST['start_time'])){
    			$start = strtotime($_POST['start_time']);
	    		$condition .= " AND add_time > {$start}";
    		}
    		
    		// 只选择结束时间
    		if(!empty($_POST['end_time'])){
    			$end = strtotime($_POST['end_time']);
	    		$condition .= " AND add_time < {$end}";
    		}
    	}
    	
    	// 标题
    	if(!empty($_POST['title'])){
    		$condition .= " AND title LIKE '%{$_POST['title']}%'";
    	}
    	
    	// 收件人
    	if(!empty($_POST['user_name'])){
    		$condition .= " AND user_name = '{$_POST['user_name']}'";
    	}
    	
    	// 发送方式
    	if(!empty($_POST['type'])){
    		switch ($_POST['type']){
    			case 1:
    				$condition .= " AND user_phone != ''";
    				break;
    			case 2:
    				$condition .= " AND user_email != ''";
    				break;
    			case 3:
    				$condition .= " AND is_inner=1";
    				break;
    			default:
    				break;
    		}
    	}
    	$page = empty($_POST['page']) ? 1 : $_POST['page'];
    	$rp = empty($_POST['rp']) ? 30 : $_POST['rp'];
    	$count = M('smslog')->where($condition)->count();
    	$list = M('smslog')->field('id,title,user_name,user_email,is_inner,user_phone,add_time')->where($condition)->limit($rp)->page($page)->order('id DESC')->select();
    	$jsonData = array('page'=> $page,'total'=> $count,'rows'=> array());
    	if(!$count){
    		exit(json_encode($jsonData));
    	}
    	$line = $rp * ($page -1) + 1;
        foreach($list AS $row){
            $entry = array(
            	'id' => $row['id'],
                'cell'=>array(
                    'line' => $line++,
                    'title' => $row['title'],
                    'user_name' => $row['user_name'],
                    'msg' => ($row['is_inner']?'是':'否'),
                    'mail' => ($row['user_email']?'是':'否'),
                    'sms' => ($row['user_phone']?'是':'否'),
                    'add_time' => date('y/m/d H:i:s', $row['add_time'])
                )
            );
            $jsonData['rows'][] = $entry;
        }
        echo json_encode($jsonData);
    }
    
    /**
     * 发送站内信 支持批量发送
     * @param string|array $data 插入的数据
     * @return Ambigous <boolean, number >
     */
    private function _send_inner($data, $batch=false){
        if($batch){
           return M('inner_msg')->addAll($data);
        }else{
            return M('inner_msg')->add($data);
        }
    }
    
    /**
     * 发送邮件 支持批量发送
     * @param string|array $to 收件人
     * @param string $subject 主题
     * @param string $body 内容
     * @param string $cc 抄送
     * @param string $bcc 秘密抄送
     * @return boolean
     */
    private function _send_mail($to, $subject, $body, $cc='', $bcc=''){
        $msgconfig = FS("Webconfig/msgconfig");
        $gc = get_global_setting();
        vendor('/PHPMailer/smtp');
        vendor('/PHPMailer/phpmailer');
        $mail = new PHPMailer();
        
        $mail->IsSMTP();
        $mail->IsHTML(true);
        $mail->addCC($cc);
        $mail->addBCC($bcc);
        $mail->CharSet = 'utf-8';
        $mail->SMTPAuth = true;
        $mail->Host = $msgconfig['stmp']['server'];
        $mail->Username = $msgconfig['stmp']['user'];
        $mail->Password = $msgconfig['stmp']['pass'];
        $mail->From = $msgconfig['stmp']['user'];
        $mail->FromName = $gc['web_name'];
        $mail->Subject = $subject;
        $mail->Body  = $body;
        
        if(is_array($to)){
            $mail->AddAddress($to[0]);
            array_shift($to);
            foreach ($to as $um){
                $mail->addBCC($um);
            }
        }else{
            $mail->AddAddress($to);
        }
        return $mail->Send();
    }
    
    /**
     * 发送短信 支持批量发送
     * @param string|array $phone 手机号码
     * @param string $content 内容
     * @return boolean
     */
    private function _send_sms($phone, $content){
        if(is_array($phone)){
            return sendsms(implode(',', $phone), $content);
        }else{
            return sendsms($phone, $content);
        }
    }
    
    /**
     * 检测平台是否开启短信接口并设置短信落款
     * @return string 短信落款
     */
    private function _check_sms(){
        $sms_config = FS("Webconfig/msgconfig");
        if(empty($sms_config['sms']) || $sms_config['sms']['type'] == 3){
            $this->ajaxReturn('', '您未开启短信接口，不能发送短信', 0);
        }
        $global = get_global_setting();
        $sign = trim($global['system_signature']);
        if(empty($sign)){
            $this->ajaxReturn('', '请在全局设置中添加短信落款配置项【system_signature】', 0);
        }
        
        return $sign;
    }
}
?>