<?php
class ShiftForm extends CommonTable {
	
	public static function isPartTime($start,$end){
		$day1 = date('Y-m-d H:i:s',strtotime($start));
		if (strtotime($start) > strtotime($end)) {
			$day2 = date('Y-m-d H:i:s',strtotime($end.'+1 day'));
		} else {
			$day2 = date('Y-m-d H:i:s',strtotime($end));
		}
		$range = round((strtotime($day2) - strtotime($day1))/(60*60));
		if ($range <= Configure::read('my.max_hour_parttime')) {
			return true;
		}
	}
	
}