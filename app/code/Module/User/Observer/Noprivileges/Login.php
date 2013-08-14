<?php
class Module_User_Observer_Noprivileges_Login extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		switch($options['zone']){
			case "root":
			case "admin":
			case "frontend":
			default:
				App::module('Core')->getModel('Flashmsg')->error(	App::xlat("ERROR_LOGIN_NOACTIVITY_NOPRIVILEGES")	);
				break;
		}
		header("Location: ".App::www("/"));
		exit;
	}

}