<?php
class Module_User_Observer_Noprivileges extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		App::module('Core')->getModel('Flashmsg')->error('ERROR_AREA_NO_PRIVILEGES');
		header("Location: ".App::www("/"));
		exit;
	}

}