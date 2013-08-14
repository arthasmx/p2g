<?php
class Module_User_Observer_Login extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		$user=$this->_module->getModel( 'user' );

		if (isset($options['user'])) {
			$user->load($options['user']);
		}
	}

}