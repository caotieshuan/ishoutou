<?php
/**
* 
*/
class NewsAction extends HCommonAction
{
	public function index()
	{
		$parm['type_id']=2;
		$parm['pagesize']=8;
		$list=getArticleList($parm);
		if($this->isAjax())
		{
			$string='';
			foreach ($list['list'] as $vn) {
				$string.='<div class="new_list">
					<a href="'.$vn['arturl'].'">
					  <div class="new_right">
					    <h3>'.cnsubstr($vn['title'],23).'</h3>
					    <em>日期'.date("Y-m-d",$vn['art_time']).'</em>
					  </div>
					</a>
				</div>';
			}
			echo $string;
		}
		//var_dump($list);die();
		else{
		$this->assign("noticeList",$list);
		$this->display();
		}
	}
	public function detail()
	{
		$id = intval($_GET['id']);
		if($_GET['type']=="subsite") {
			$vo = M('article_area')->find($id);
		}else {
			$vo = M('article')->find($id);
			$tid = $vo['type_id'];
			$wo = M('article_category')->find($tid);
			$this->assign("wo",$wo);
		}
		$this->assign("vo",$vo);

		//left
		$typeid = $vo['type_id'];
		$listparm['type_id']=$typeid;
		$listparm['limit']=15;
		if($_GET['type']=="subsite"){
			$listparm['area_id'] = $this->siteInfo['id'];
			$leftlist = getAreaTypeList($listparm);
		}else	$leftlist = getTypeListActa($listparm);
		
		$this->assign("leftlist",$leftlist);
		$this->assign("cid",$typeid);
		
		if($_GET['type']=="subsite"){
			$vop = D('Aacategory')->field('type_name,parent_id')->find($typeid);
			if($vop['parent_id']<>0) $this->assign('cname',D('Aacategory')->getFieldById($vop['parent_id'],'type_name'));
			else $this->assign('cname',$vop['type_name']);
		}else{
			$vop = D('Acategory')->field('type_name,parent_id')->find($typeid);
			if($vop['parent_id']<>0) $this->assign('cname',D('Acategory')->getFieldById($vop['parent_id'],'type_name'));
			else $this->assign('cname',$vop['type_name']);
		}
		$this->display();
	}
}
?>