<?php
	class TraderAction extends ACommonAction{
		public function index(){

			$list = M('shares_global')->where("type_id = 3")->order("order_sn DESC")->select();
			$this->assign('list', de_xie($list));
			$this->display();
		}
		public function doAdd(){
			$glo = D('shares_global');
		
			if($glo->create()) {
				$newid = $glo->add();
				if($newid) $this->success('修改成功');
				else $this->error('修改失败');
			}else{
				$this->error($glo->getError());
			}	
		}
		public function doEdit(){
			if($_POST){			
				$data = $_POST;
			}
			foreach($data as $key => $v){
				if(is_numeric($key)) M('shares_global')->where("id = '{$key}'")->setField('text',EnHtml($v));
			}
		
			$this->success('更新成功');

		}
		public function doDelweb(){
			$delnum = M('shares_global')->where("id = '{$_POST['id']}'")->delete(); 
			
			if($delnum){			
				$a_data['status'] = 1;
				$a_data['id'] = $data['id'];
			}else{
				$a_data['status'] = 0;
				$a_data['message'] = "删除失败";
			}
			
			exit(json_encode($a_data));
				
		}
	}

