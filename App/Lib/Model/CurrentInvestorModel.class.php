<?php
/*	活期理财添加记录模型
 *	@author:DQ
 *	@time:2015/4/07
 */
class CurrentInvestorModel extends Model {
	
	
	protected $_validate	=	array(
		array('buy_money','require',"购买金额不能为空"),
		array('current_id','require',"数据有误"),
		);
	
	//添加活期宝
	public function addInvest() {
		$this->create();
		$this->status = 1;
		$this->add_time = time();
		$this->interest_rate = M('current_info')->getFieldByid($this->current_id,"interest_rate");
		$this->order = "LCI".rand(1,999).time();		
		return $this->add();
	}
	
}