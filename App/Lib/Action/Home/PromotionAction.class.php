<?php
// 本类由系统自动生成，仅供测试用途
class PromotionAction extends HCommonAction {
    public function index(){
        if($this->uid) $this->redirect('member/index');
        $tid = (int)$_GET['t'];
        if($tid) {
            session('promoteid',$tid);
            $_SERVER['REDIRECT_QUERY_STRING'] && session('promote_other',$_SERVER['REDIRECT_QUERY_STRING']);
            //$this->redirect('/promotion');
        }
		$this->display();
    }
    public function lists(){

        $tid = (int)$_GET['t'];
        if(empty($tid)) exit;

        import("ORG.Util.Page");
        $map = array();
        $map['m.tid'] = $tid;

        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.reg_time'] = array("between",$timespan);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.reg_time'] = array("gt",$xtime);
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.reg_time'] = array("lt",$xtime);
        }
        //$count = M('members m')->join("{$this->pre}lzh_members_status mi ON mi.uid=m.id")->where($map)->count('m.id');
        //$p = new Page($count, C('ADMIN_PAGE_SIZE'));
        //$page = $p->show();
        //$Lsql = "{$p->firstRow},{$p->listRows}";
        $field= 'm.id,m.user_phone,m.reg_time,m.user_name,m.customer_name,m.user_leve,m.time_limit,mi.phone_status,mi.id_status,ot.other';
        $list = M('members m')->field($field)->join("lzh_members_status mi ON mi.uid=m.id")->join('lzh_promote_other ot on ot.uid=m.id')->where($map)->order('m.id DESC')->select();

        $tabmax = array() ;

        foreach($list as &$val){
            if($val['other']){
                parse_str($val['other'], $arr);

                foreach($arr as $k =>$ll){
                    if(false === in_array($k,$tabmax)){
                        $tabmax[]=$k;
                    }
                }
                $val['other'] = $arr;
            }
        }

        //$this->assign("page", $page);
        $this->assign("tabmax", $tabmax);
        $this->assign("list", $list);
        $this->assign('id',$_GET['t']);
        $this->display();
    }
	public function idcards(){
        $ids = M('members_status')->getFieldByUid($this->uid,'id_status');
        if($ids==1){
            $vo = M("member_info")->field('idcard,real_name')->find($this->uid);
            $this->assign("vo",$vo);
            $data['html'] = $this->fetch();
        }

        $phone = M('members')->getFieldById($this->uid,'user_phone');

        if(empty($phone) && false === $re){
            $data['html'] = '<script type="text/javascript">alert("您还未完成手机认证,请先进行手机认证！");myrefresh3();</script>';
            echo json_encode($data);
            exit;
        }
        list($id5enable) = $this->getSetId5();
        $this->assign("phone",$phone);
        $this->assign("id5enable",$id5enable);
        $this->assign("id_status",$ids);
      
        $this->display();
    }
       protected  function getSetId5(){
        $result = FS("Webconfig/id5");
        $unums = 0;
        if($result['nums']>0){
            $unums = M('id5log')->where('uid='.$this->uid)->count();
            if($unums>=$result['nums']){
                $result['enable'] = 0;
            }

        }
        return array($result['enable'],array('nums'=>$result['nums'],'unums'=>$unums));
    }
	
}