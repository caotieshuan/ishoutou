<?php
// 本类由系统自动生成，仅供测试用途
class PromotionAction extends MCommonAction {

    public function index(){
		$this->display();
    }

    public function promotion(){
		$_P_fee=get_global_setting();
		$this->assign("reward",$_P_fee);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	public function promotionlogs(){
		$this->promotionlog(5);
		$vm = $this->promotionfriend(true);

		$vm = array_chunk($vm,5);

		$this->assign("vm",$vm);
		$this->display();
	}

	public function ajaxprotionlog(){
		$list = $this->promotionlog(5);
		foreach($list as &$v){
			$v['add_time'] = date('Y-m-d H:i',$v['add_time']);
		}
		$this->ajaxReturn($list,'JSON');
	}


    public function promotionlog($re = 0){
		$map['uid'] = $this->uid;
		$map['type'] = array("in","1,13");
		$list = getMoneyLog($map,($re ? $re : 15));

		$totalR = M('member_moneylog')->where("uid={$this->uid} AND type in(1,13)")->sum('affect_money');
		$this->assign("totalR",$totalR);		
		$this->assign("CR",M('members')->getFieldById($this->uid,'reward_money'));		
		$this->assign("list",$list['list']);		
		$this->assign("pagebar",$list['page']);
		if($re>0) {
			$dpage = array();
			$dpage['numpage'] = $list['count'] ? ceil($list['count']/5) : 1;
			$dpage['curpage'] = (int)$_GET['p'] ? (int)$_GET['p'] : 1;

			$this->assign("dpage",$dpage);
			return $list['list'];
		};
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	public function promotionfriend($re = false){
		$pre = C('DB_PREFIX');
		$uid=session('u_id');
		$field = " m.id,m.user_name,m.reg_time,sum(ml.affect_money) jiangli ";
		$field1 = " m.user_name,m.reg_time";
		$vm1 = M("members m")->field($field1)->where(" m.recommend_id ={$uid}")->group("m.id")->select();
		if(true === $re) return $vm1;
		$vm = M("members m")->field($field)->join(" lzh_member_moneylog ml ON m.id = ml.target_uid ")->where(" m.recommend_id ={$uid} AND ml.type =13")->group("ml.target_uid")->select();
		$this->assign("vi",$vm1);
		$this->assign("vm",$vm);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
}