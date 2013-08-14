<?php
class Module_Acl_Observer_Logout extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		$acl=$this->_module->getModelSingleton( 'acl' );
		$acl->logout();
	}

}