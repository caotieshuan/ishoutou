<?php
// 本类由系统自动生成，仅供测试用途
class CollegeAction extends HCommonAction {
	public function news(){
		
		//热点跟踪
		$parm['type_id'] = 35; 
		$parm['limit'] =10;
		$redian = getArticleList($parm);
		//dump($redian);die;
		$this->assign("redian",$redian);
		
		//焦点
		$parm['type_id'] = 36; 
		$parm['limit'] =10;
		$focus = getArticleList($parm);	
		
		$this->assign("focus",$focus);
		
		//平台公告
		$parm['type_id'] = 9;
		$parm['limit'] =10;
		$this->assign("noticeList",getArticleList($parm));
		
		//配资课堂
		$parm['type_id'] = 38;
		$parm['limit'] =10;
		$this->assign("classroom",getArticleList($parm));
		

		$this->display();
	}
}