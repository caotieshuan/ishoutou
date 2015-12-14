<?php
    class CurrentAction extends ACommonAction
    {
		public function add(){
			
			$this->display();
		}
        public function index()
        {
			import("ORG.Util.Page");
			$count =M('current_info')->where("status = 1")->count();
			$p = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page = $p->show();
			$list = M('current_info')->where("status = 1")->select();
			$this->assign('list',$list);
			$this->assign('page',$page); 
			$this->display();
        }
		
		public function doAdd(){
			$model = D("CurrentInfo");
			$ret = $model->addCurrent();
			if($ret){
				
				alogs("doAdd",0,1,'管理员执行了添加活期理财操作成功！');
				$this->success('添加成功！');
			}else{
				alogs("doAdd",0,1,'管理员执行了添加活期理财操作失败！');
				$this->error('添加失败！');
			}
			
		}
		
		public function Record(){
			
			
			$current_id = $_GET['id'];
			import("ORG.Util.Page");
			$count = M('current_investor')->where("current_id = {$current_id}")->count();
			$p = new Page($count, 5);//C('ADMIN_PAGE_SIZE')
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$list = M('current_investor r')->field("r.*,m.user_name")->join("lzh_members m ON m.id = r.invest_uid")->where("r.current_id = {$current_id}")->limit($Lsql)->select();

			$this->assign('list',$list);
			$this->assign('page',$page);
			$this->display();
		}
		
		public function complete(){
			import("ORG.Util.Page");
			$count =M('current_info')->where("status = 2")->count();
			$p = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page = $p->show();
			$list = M('current_info')->where("status = 2")->select();
			$this->assign('list',$list);
			$this->assign('page',$page);
			$this->display();
			
		}
		
		public function extraction(){
			
			$map = array();
			$map['status'] = 2;
			import("ORG.Util.Page");
			$count = M('current_investor')->where($map)->count();
			$p = new Page($count, 10);
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$list = M('current_investor')->where($map)->limit($Lsql)->select();
			$this->assign('list',$list);
			$this->assign('page',$page);
			$this->display();
		}
		//已提取
		public function yextraction(){
			
			$map = array();
			$map['status'] = 3;
			import("ORG.Util.Page");
			$count = M('current_investor')->where($map)->count();
			$p = new Page($count, 10);
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			$list = M('current_investor')->where($map)->limit($Lsql)->select();
			$this->assign('list',$list);
			$this->assign('page',$page);
			$this->display();
		}

		
		public function doextraction(){
			
			
			$buy_money = M('current_investor')->getFieldByid($_POST['id'],"buy_money");
			
			$this->assign('id',$_POST['id']);
			$this->assign('interest',$_POST['interest']);
			$this->assign('buy_money',$buy_money);
			$this->display();
			
		}
		
		public function examiney(){
			
			if($_POST){
				
				if($_POST['examiney'] == 3){
					
					$ret = extraction($_POST['buy_money'],$_POST['interest'],$_POST['id']);
					if($ret){
						alogs("doAdd",0,1,'管理员执行了审核通过操作成功！');
						$this->success('审核成功！');
					}else{
						alogs("doAdd",0,1,'管理员执行了审核通过操作失败！');
						$this->error('审核失败！');
					}
					
				}elseif($_POST['examiney'] == 4){
					
					$savedata = array();
					$savedata['status'] = 1;					
					$invest = M('current_investor')->where("id = {$_POST['id']}")->save($savedata);
					if($invest){
						alogs("doAdd",0,1,'管理员执行了审核不通过操作成功！');
						$this->success('审核不通过操作成功！');
					}else{
						alogs("doAdd",0,1,'管理员执行了审核不通过操作失败！');
						$this->error('审核不通过失败！');
					}
				}
				
				
			}
		}
        
    }
?>
