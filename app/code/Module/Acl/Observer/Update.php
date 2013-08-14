<?php
class Module_Acl_Observer_Update extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		$acl=$this->_module->getModel( 'acl' );

		// Actualizamos el email del usuario
			$acl->changeEmail($options['email'],$options['user']);

	}

}