<?php
class Module_User_Observer_Logout extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		$user=$this->_module->getModel( 'user' );
		$user->unload();
	}

}