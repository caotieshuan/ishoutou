<?php
/*	股权配资杠杆模型类
 *	@author:Bob
 *	@time:2015/3/27
 */
class SharesLeverModel extends Model {
	public function getMonthLever() {
		return $this->field("id,lever_ratio,open_ratio,alert_ratio,manage_rate")->where("type_id = 2 and status = 1")->select();
	}
}