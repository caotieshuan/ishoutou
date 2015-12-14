<?php
// 全局设置
class ActivityAction extends ACommonAction
{
	//var $justlogin = true;

	/**
    +----------------------------------------------------------
    * 默认操作 待审核提现
    +----------------------------------------------------------
    */
	public function index()
	{
        $this->display();
    }


	//编辑
    public function Diy()
	{
		//分页处理
		import("ORG.Util.Page");
		$map=array();
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['var_time'] = array("between",$timespan);
            $search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));  
            $search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));  
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['var_time'] = array("gt",$xtime);
            $search['start_time'] = $xtime; 
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['var_time'] = array("lt",$xtime);
            $search['end_time'] = $xtime;   
        }
        if(!empty($_REQUEST['username'])){
            $map['m.user_name'] = $_REQUEST['username'];
            $search['username'] = $_REQUEST['username'];   
        }
		$count = M('activity_diy')->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M('activity_diy')->where($map)->limit($Lsql)->order('add_time desc')->select();
		if($list){
			foreach($list as &$val){
				$result =  $this->OpenXls($val['xlspath']);
				$val['sum'] = $result['sum'];
			}
		}
		$this->assign("list", $list);
		$this->assign("pagebar", $page);
		$this->assign("search", $search);
		$this->assign("query", http_build_query($search));

		$this->display();
	}
	 public function export()
	{
		ini_set("memory_limit","-1");
		set_time_limit (0);
		import("ORG.Io.Excel");
		//分页处理
		import("ORG.Util.Page");
		$map=array();
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = (urldecode($_REQUEST['start_time'])).",".(urldecode($_REQUEST['end_time']));
            $map['var_time'] = array("between",$timespan);
            $search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));  
            $search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));  
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = (urldecode($_REQUEST['start_time']));
            $map['var_time'] = array("gt",$xtime);
            $search['start_time'] = $xtime; 
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = (urldecode($_REQUEST['end_time']));
            $map['var_time'] = array("lt",$xtime);
            $search['end_time'] = $xtime;   
        }
        if(!empty($_REQUEST['username'])){
            $map['m.user_name'] = $_REQUEST['username'];
            $search['username'] = $_REQUEST['username'];   
        }
		$count = M('activity_diy')->where($map)->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M('activity_diy')->where($map)->order('add_time desc')->select();
		if($list){
			foreach($list as &$val){
				$result =  $this->OpenXls($val['xlspath']);
				$val['sum'] = $result['sum'];
			}
		}
		$row=array();
		$row[0]=array('序号','活动名称','添加人','添加时间','总金额','发奖审核人','发奖审核时间','状态');
		$i=1;
		foreach($list as $v){
				$row[$i]['i'] = $i;
				$row[$i]['art_title'] = $v['art_title'];
				$row[$i]['add_user'] = $v['add_user'];
				$row[$i]['addtime'] = date('Y-m-d H:i:s',$v['add_time']);
				$row[$i]['sum'] = $v['sum'];;
				$row[$i]['user_name'] = $v['var_user'];
				if(!empty($v['var_time'])){
					$row[$i]['var_time'] = date('Y-m-d H:i:s',$v['var_time']);
					$row[$i]['status'] = '已发奖励';
				}else{
					$row[$i]['var_time'] = '';
					$row[$i]['status'] = '';
				}
				$i++;
		}
		
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("diy");
	}

	//删除活动
	 public function delDiy()
	{
		//分页处理
		$id = (int) $_GET['id'];
		M('activity_diy')->delete($id);
		$this->assign('jumpUrl', "__URL__/diy");
		$this->success('删除成功');
	}
	public function month(){
		//分页处理
		import("ORG.Util.Page");
		$count = M('9yue')->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M('9yue')->limit($Lsql)->order('dateline desc')->select();
		$this->assign("list", $list);
		$this->assign("pagebar", $page);
		$this->display();
	}
	public function addday(){
		$id = (int)$_GET['id'];

		if($id){
			$vo = M('9yue')->find($id);
			$this->assign("vo", $vo);
		}

		$this->display();
	}
	public function DelDay(){
		$id = (int) $_GET['id'];
		M('9yue')->delete($id);
		$this->assign('jumpUrl', "__URL__/month");
		$this->success('删除成功');
	}
	public function doaddday(){
		$id = (int)$_POST['id'];
		$data = array();
		$data['art_day'] = text($_POST['art_day']);
		$data['art_10'] = text($_POST['art_10']);
		$data['art_3'] = text($_POST['art_3']);
		$data['dateline'] = time();

		if($id){
			M('9yue')->where('id='.$id)->save($data);
		}else{
			M('9yue')->add($data);
		}

		$this->assign('jumpUrl', "__URL__/month");
		$this->success('保存成功');

	}


	public function filllog(){

		//该功能暂时有问题。停用了，两次错误发奖会出问题
		$pid = (int)$_GET['pid'];
		$id = (int)$_GET['uid'];


		$list = M('member_moneylog')->where('info="活动奖励"')->select();
/**
		$data = M('activity_diy')->find($pid);
		if(empty($data)){
			$this->error('活动不存在');
		}


		$list = M('member_moneylog l')->join('lzh_members m on m.id=l.uid')->where('l.add_time='.$data['var_time'].' and type=55')->field('l.*,m.user_name')->select();


		echo '<pre>';

		foreach($list as $val){


			$next =  M('member_moneylog')->where(array('add_time'=>array('lt',$val['add_time']),'uid'=>$val['uid']))->order('add_time desc')->find();
			$money = $next['back_money']+$next['account_money']+$val['affect_money'];

			$vv = $val['back_money']+$val['account_money'];

			echo '--------------<br>',$val['user_name'],'<br>',$money,'<br>',$vv,'<br>';

			if($vv != $money){
				$ss = $money - $val['back_money'];

				if($val['id']){
					M('member_moneylog')->where('id='.$val['id'])->save(array('account_money'=>$ss));
				}

				echo '醋藕,<br>';
				echo $ss,'<br>';
//				M('member_moneylog')->where('id='.$val['id'])->save(array('account_money'=>));
			}

		}
 * */
	}

	public function Prizes(){
		$id = (int)$_GET['id'];
		$data = M('activity_diy')->find($id);
		if(empty($data)){
			$this->error('活动不存在');
		}
		$xlsPath = C('WEB_ROOT').$data['xlspath'];
		$result = $this->OpenXls($xlsPath);
		if($result['code']){
			$this->error($result['data']);
		}
		if($result['data']){
			$msum = 0;
			foreach($result['data'] as &$val){
				$val['status'] = (int) M('activity_diy_log')->where('pid='.$id.' and uid='.$val['uid'])->count();
				if(0 == $val['status']){
					$msum++;
				}
			}
		}
		$this->assign("list", $result['data']);
		$this->assign('pid',$id);
		$this->assign("ext", array('sum'=>$result['sum'],'count'=>count($result['data']),'msum'=>$msum));
		$this->assign('data',$data);
		$this->display();
	}
	public function doPrizes()
	{
		$id = (int)$_POST['id'];
		$data = M('activity_diy')->find($id);
		if(empty($data)){
			$this->error('活动不存在');
		}
		$xlsPath = C('WEB_ROOT').$data['xlspath'];
		$result = $this->OpenXls($xlsPath);
		if($result['code']){
			$this->error($result['data']);
		}

		if($result['data'] && 0 == $data['status']){
			foreach($result['data'] as &$val){
				//去掉顾虑重复
				//if(0 == M('activity_diy_log')->where('pid='.$id.' and uid='.$val['uid'])->count()){
					$this->sendPrize($val,$data);
				//}
			}
			$data['var_user'] = session('adminname');
			$data['var_time'] = time();
			$data['status'] = 1;
			if(M('activity_diy')->where('id='.$data['id'])->save($data)){
				$this->assign('jumpUrl', "__URL__/Diy");
				$this->success('发奖成功');
			}else{
				$this->error('发奖失败');
			}
		}
	}
	public function editdiy(){
		$id = (int)$_GET['id'];
		$data = M('activity_diy')->find($id);
		if(empty($data)){
			$this->error('活动不存在');
		}
		if($data['status']){
			$this->error('活动已经发奖');
		}
		$xlsPath = C('WEB_ROOT').$data['xlspath'];
		$result = $this->OpenXls($xlsPath);
		if($result['code']){
			$this->error($result['data']);
		}
		if($result['data']){
			foreach($result['data'] as &$val){
				$val['status'] = (int) M('activity_diy_log')->where('pid='.$id.' and uid='.$val['uid'])->count();
			}
		}
		$this->assign("list", $result['data']);
		$this->assign('data',$data);
		$this->display();
	}
	public function doEditDiy(){
		$id = (int)$_POST['id'];
		$data = M('activity_diy')->find($id);
		if(empty($data)){
			$this->error('活动不存在');
		}
		if($data['status']){
			$this->error('活动已经发奖');
		}
		$activity=array();
		$activity['art_title'] = htmlspecialchars($_POST['art_title']);
		$activity['art_info'] = htmlspecialchars($_POST['art_info']);
		$activity['xlspath'] = htmlspecialchars($_POST['xlspath']);
		$activity['add_user'] = session('adminname');
		$activity['add_time'] = time();
		$activity['status'] = 0;
		if(empty($activity['art_title'])){
			$this->error('请填写活动标题');
		}
		$xlsPath = C('WEB_ROOT').$activity['xlspath'];
		$xlsdata = $this->OpenXls($xlsPath);
		if($xlsdata['code']){
			$this->error($xlsdata['data']);
		}
		$activity['art_count'] = count($xlsdata['data']);
		$result  = M('activity_diy')->data($activity)->where('id='.$id)->save();
		if(!$result){
			$this->error('保存失败');
		}else{
			$this->assign('jumpUrl', "__URL__/Diy");
			$this->success('保存成功');
		}
	}
	public function addDiy(){
		$this->display();
	}
	public function OpenXls($xlsPath){
		$data = $this->format_excel2array($xlsPath);
		$code = 0;
		$sum = 0;
		if(false === empty($data)){
				foreach($data as $val){
					$userInfo = M('members')->where('user_name="'.trim($val['A']).'"')->find();
					if(false === empty($val['A']) && $userInfo['id']){
						if(false === empty($val['C'])){
							$prize = sprintf("%0.2f", $val['B']);
							$sum += $prize;
							if(!is_numeric($prize)){
								$NoPrize[]=$val['A'].'-('.$prize.')';
							}
							$result[]=array('uid'=>$userInfo['id'],'name'=>$val['A'],'phone'=>$userInfo['user_phone'],'prize'=>$prize,'adesc'=>$val['C']);
						}else{
							$NoteB[] = $val['A'];
						}
					}else{
						$NoList[]=$val['A'];
					}
				}
				if(false === empty($NoList))
				{
					$code =1;
					$result = '用户：'.implode(',',$NoList).'不存在';
				}elseif(false === empty($NoteB)){
					$code =1;
					$result = '用户：'.implode(',',$NoteB).',发奖简介没填写！';
				}elseif(false === empty($NoPrize)){
					$code =1;
					$result = '用户：'.implode($NoPrize).'金额不合法';
				}
		}else{
			$code = 1;
			$result = '打开文件失败';
		}

		return array('code'=>$code,'data'=>$result,'sum'=>sprintf("%0.2f", $sum));
	}
	public function doAddDiy(){
		$activity=array();
		$activity['art_title'] = htmlspecialchars($_POST['art_title']);
		$activity['art_info'] = htmlspecialchars($_POST['art_info']);
		$activity['xlspath'] = htmlspecialchars($_POST['xlspath']);
		$activity['add_user'] = session('adminname');
		$activity['add_time'] = time();
		$activity['status'] = 0;
		if(empty($activity['art_title'])){
			$this->error('请填写活动标题');
		}
		$xlsPath = C('WEB_ROOT').$activity['xlspath'];

		$xlsdata = $this->OpenXls($xlsPath);

		if($xlsdata['code']){
			$this->error($xlsdata['data']);
		}

		$activity['art_count'] = count($xlsdata['data']);

		$result  = M('activity_diy')->data($activity)->add();

		if(!$result){
			$this->error('保存失败');
		}else{
			$this->assign('jumpUrl', "__URL__/Diy");
			$this->success('保存成功');
		}
	}

	public function UploadXls(){
		$result = array();

		import("ORG.Net.UploadFile");
        $upload = new UploadFile();
		$upload->saveRule = 'uniqid';
		$upload->maxSize  = 5000000 ;// 设置附件上传大小
		$upload->allowExts  = array('xlsx');// 设置附件上传类型

		$upload->savePath = C( "ADMIN_UPLOAD_DIR" )."xls/";
		if(!$upload->upload()) {// 上传错误提示错误信息
			$result = array('code'=>1,'msg'=>'上传失败');
		}else{// 上传成功 获取上传文件信息
			$file =  array_shift($upload->getUploadFileInfo());
			$xlsPath = C('WEB_ROOT').$file['savepath'].$file['savename'];

			$xlsdata = $this->OpenXls($xlsPath);

			if($xlsdata['code']){
				$result = array('code'=>1,'msg'=>$xlsdata['data']);
			}else{
				$result  = array('code'=>0,'res'=>$xlsdata['data'],'xlspath'=>$file['savepath'].$file['savename']);
			}
		}
		$this->ajaxReturn($result);
	}

	function format_excel2array($filePath='',$sheet=0){
		if(empty($filePath) or !file_exists($filePath)){die('file not exists');}
		import("ORG.Util.PHPExcel");

			$PHPReader = new PHPExcel_Reader_Excel2007();        //建立reader对象

		if(!$PHPReader->canRead($filePath)){
			$PHPReader = new PHPExcel_Reader_Excel5();
			if(!$PHPReader->canRead($filePath)){
				echo 'no Excel';
				return ;
			}
		}

		$PHPExcel = $PHPReader->load($filePath);        //建立excel对象

		$currentSheet = $PHPExcel->getSheet($sheet);        //**读取excel文件中的指定工作表*/
		$allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
		$allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
		$data = array();
		for($rowIndex=1;$rowIndex<=$allRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
			for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
				$addr = $colIndex.$rowIndex;
				$cell = $currentSheet->getCell($addr)->getValue();
				if($cell instanceof PHPExcel_RichText){ //富文本转换字符串
					$cell = $cell->__toString();
				}
				$data[$rowIndex][$colIndex] = $cell;
			}
		}
		return $data;
	}



	protected function sendPrize($vo,$dd){
			$vo['prize'] = floatval($vo['prize']);
			$newid = memberMoneyLog($vo['uid'],55,$vo['prize'],$vo['adesc']);
			if($newid){
				$data = array();
				$data['username'] = $vo['name'];
				$data['uid'] = $vo['uid'];
				$data['dateline'] = time();
				$data['prize'] = $vo['prize'];
				$data['art_info'] = $vo['adesc'];
				$data['pid'] = $dd['id'];
				M('activity_diy_log')->add($data);
				//SMStip("diyactivity",$vo['phone'],array("#ITEMNAME#,#USERANEM#","#MONEY#"),array($dd['art_title'],$vo['name'],$vo['prize']));
				alogs("Paylog",0,1,'执行了管理员发放活动奖励！'.$dd['art_title']);//管理员操作日志
				return true;
			}else{
				alogs("Paylog",0,1,'执行了管理员发放活动奖励！');//管理员操作日志
				return false;
			}
	}

}
?>