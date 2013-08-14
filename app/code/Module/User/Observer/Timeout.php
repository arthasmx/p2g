<?php
class Module_User_Observer_Timeout extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		App::module('Core')->getModel('flashmsg')->info(App::xlat('Ha pasado demasiado tiempo inactivo y su sesión ha sido finalizada.<br/>Debe identificarse nuevamente.'));
	}
}