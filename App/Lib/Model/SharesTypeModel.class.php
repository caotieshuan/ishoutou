<?php
/*	股权配资类型模型类
 *	@author:Bob
 *	@time:2015/3/27
 */
class SharesTypeModel extends Model {
	public function getMonthtermConfig() {
		$term = $this->field("term")->find(2);
		$term = explode("|",$term['term']);
		$month = array();
		for($i = intval($term[0]) ; $i <= $term[1] ; $i++ ){
			$month[] = $i;
		}
		return $month;
	}
	public function getMonthmoneyConfig() {
		$money = $this->field("money")->find(2);
		return explode("|",$money['money']);
	}
}