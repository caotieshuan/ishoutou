<?php
class HolidayAction extends ACommonAction{
    public function index(){
		$vo = M('shares_holiday')->select();
		$this->assign("vo",$vo);
        $this->display();
    }

	public function Postdate(){
		$start_time = strtotime($_GET['start_time']);
		$the_time = date("Y-m-d 23:59:59",strtotime($_GET['end_time']));
		$end_time = strtotime($the_time);
		
		$info = $_GET['info'];
		
		$m = M('shares_holiday');
		$time_data = array();
		$time_data['from_date'] = $start_time;
		$time_data['to_date'] = $end_time;
		$time_data['info'] = $info;
		$holiday = $m->add($time_data);
		if($holiday){

			echo 'yes';
		}else{
			
			echo 'no';
		}
	}
	public function delete(){
		
		$id = $_GET['id'];
		
		$holiday = M('shares_holiday')->where("id = {$id}")->delete();
		if($holiday){						
			echo 'yes';
		}else{
			
			echo 'no';
		}
	}
	
}
?>