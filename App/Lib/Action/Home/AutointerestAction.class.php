<?php
class AutointerestAction extends HCommonAction {
	//自动执行扣款程序
	public function deductioninterest(){
		//获取审核通过进行中的配资
		$stocks = M("shares_apply")->where("status = 2")->field("id,trading_time,add_time,examine_time")->select();
		
		//清理已失效的节假日配置
		$time = time();
		M("shares_holiday")->where("to_date <= {$time}")->delete();
		
		//获取可用节假日配置
		$holid_time = M('shares_holiday')->order("from_date ASC")->select();
		foreach($stocks as $key => $stock) {
			
			//下一交易日生效的配资
			if($stock['trading_time'] == 2) {
				//判断是否为第二天审核 如果第二天审核 则本日扣款
				if(strtotime(date("Y-m-d 00:00:00",$stock['examine_time'])) > strtotime(date("Y-m-d 00:00:00",$stock['add_time']))) {
					$this->operation(time(),$holid_time,$stock['id']);
					
				//当天审核 下一交易日扣款
				}else {
					$this->operation(strtotime(date("Y-m-d 00:00:00",strtotime("+24 hours",$stock['examine_time']))),$holid_time,$stock['id']);
				}
				
			//本日交易生效的配资
			}else if($stock['trading_time'] == 1){
				//扣除昨日当日生效的配资
				if(strtotime(date("Y-m-d 00:00:00",strtotime("+24 hours",$stock['examine_time']))) == strtotime(date("Y-m-d 00:00:00",time()))) {
					$this->deduction($stock['id']);
				
				//按天每日扣款
				}else {
					$this->operation(time(),$holid_time,$stock['id']);
				}
			}
		}
	}
	
	//递归剔除周末节假日
	private function operation($thetime,$holiday,$stockid) {
		
		foreach($holiday as $key=>$v){
			
			//判断时间参数是否在节假日范围内
			if($v['from_date'] <= $thetime && $v['to_date'] >= $thetime){
				
				//如果节假日当天为周五则自动跳转到下周一
				if(date("N",$v['to_date']) == 5) {
					
					$time = strtotime(date("Y-m-d 00:00:00",strtotime("+72 hours",$v['to_date'])));
				
				//如果节假日当天为周六则自动跳转到下周一
				}else if(date("N",$v['to_date']) == 6) {
					$time = strtotime(date("Y-m-d 00:00:00",strtotime("+48 hours",$v['to_date'])));
					
				//如果节假日当天不为周五周六则自动跳转到下周一
				}else {
					$time = strtotime(date("Y-m-d 00:00:00",strtotime("+24 hours",$v['to_date'])));
				}
				$this->operation($time,$holiday,$stockid);
			}else {
				
				$this->deduction($stockid,$thetime);
			}
		}
	}
	
	//扣款
	public function deduction($id,$time=null) {
		$time = $time ? $time : time();
		
		//只扣当天款项
		if(strtotime(date("Y-m-d",time())) == strtotime(date("Y-m-d",$time)) ) {
			
			$apply = M('shares_apply')->where("id = {$id}")->find();
			
			if($apply['type_id'] == 1){
				daydeduction($id);
			}
			
			if($apply['type_id'] == 2){
				monthdeduction($id);
			}
		}
	}
}