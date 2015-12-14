<?php
/*	股权配资模型类
 *	@author:Bob
 *	@time:2015/3/25
 */
class CurrentInfoModel extends Model {
	
	//添加活期宝
	public function addCurrent() {
		$this->create();
		$this->status = 1;	//状态
		$this->add_time = time();	//添加时间
		$this->order = "HQLC".rand(1,999).time();	//订单号
		return $this->add();
	}
	
}