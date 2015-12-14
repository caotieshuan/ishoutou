<?php
// 全局设置
class PayonlineAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
		$payconfig = FS("Webconfig/payconfig");
		$this->assign('guofubao_config',$payconfig['guofubao']);
		$this->assign('ips_config',$payconfig['ips']);
		$this->assign('chinabank_config',$payconfig['chinabank']);
		$this->assign('baofoo_config', $payconfig['baofoo']);
		$this->assign('shengpay_config', $payconfig['shengpay']);
		$this->assign('tenpay_config', $payconfig['tenpay']);
		$this->assign('ecpss_config', $payconfig['ecpss']);
		$this->assign('easypay_config', $payconfig['easypay']);
		$this->assign('cmpay_config', $payconfig['cmpay']);
        $this->display();
    }
    public function save()
    {
		FS("payconfig",$_POST['pay'],"Webconfig/");
		alogs("Payonline",0,1,'执行了pc端第三方支付接口参数的编辑操作！');//管理员操作日志
		$this->success("操作成功",__URL__."/index/");
    }

	public function wap(){
		$payconfig = FS("Webconfig/wappayconfig");

		$this->assign('llpay_config',$payconfig['llpay']);
		$this->display();
	}
	public function wapsave(){
		FS("wappayconfig",$_POST['pay'],"Webconfig/");
		alogs("Payonline",0,1,'执行了wap端第三方支付接口参数的编辑操作！');//管理员操作日志
		$this->success("操作成功le",__URL__."/wap");
	}

	public function banklist()
	{
		//分页处理
		import("ORG.Util.Page");
		$count = M('bankList')->count();
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$list = M('bankList')->limit($Lsql)->order('id desc')->select();
		$this->assign("list", $list);
		$this->assign("pagebar", $page);
		$this->display();
	}
	public function addbank(){
		$id = (int)$_GET['id'];
		$id && $vo = M('bankList')->find($id);
		$this->assign("vo", $vo);
		$this->display();
	}
	public function delbank(){
		$id = (int)$_GET['id'];
		$id && M('bankList')->delete($id);
		$this->success("操作成功",__URL__."/banklist");
	}
	public function savebank(){

		$data = array();
		$data['bankname'] = htmlspecialchars($_POST['bankname']);
		$data['bankfile'] = htmlspecialchars($_POST['bankfile']);
		$id = (int)$_POST['id'];
		if($id){
			M('bankList')->where(array('id'=>$id))->save($data);
		}else{
			if(M('bankList')->where(array('bankname'=>array('like',$data['bankname'])))->count()){
				$this->error($data['bankname'].'已存在');
			}
			M('bankList')->add($data);
		}
		$this->success("操作成功",__URL__."/banklist");
	}

	public function UploadXls(){
		$result = array();

		import("ORG.Net.UploadFile");
		$upload = new UploadFile();
		$upload->saveRule=uniqid;
		$upload->maxSize  = 5000000 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg','gif','jpeg','png');// 设置附件上传类型

		$upload->savePath = C( "ADMIN_UPLOAD_DIR" )."banklist/";
		if(!$upload->upload()) {// 上传错误提示错误信息
			$result = array('code'=>1,'msg'=>'上传失败');
		}else{// 上传成功 获取上传文件信息
			$file =  array_shift($upload->getUploadFileInfo());
			$xlsPath = C('WEB_ROOT').$file['savepath'].$file['savename'];
			if(!file_exists($xlsPath)){
				$result = array('code'=>1,'msg'=>'上传失败');
			}else{
				$result  = array('code'=>0,'xlspath'=>'/'.$file['savepath'].$file['savename']);
			}
		}
		$this->ajaxReturn($result);
	}
}
?>