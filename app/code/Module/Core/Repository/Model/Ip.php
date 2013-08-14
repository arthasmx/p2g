<?php
class Module_Core_Repository_Model_Ip extends Core_Model_Repository_Model {

	public function get() {
		if (@$_SERVER['HTTP_X_FORWARDED_FOR']) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

}