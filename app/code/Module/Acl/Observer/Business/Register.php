<?php
class Module_Acl_Observer_Business_Register extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		$acl=$this->_module->getModel( 'acl' );

		if (!@$options['tipo']) $options['tipo']="business";

		// Creamos el usuario en el acl
			$acl->create($options['user'],$options['passwd'],$options['email'],false,$options['tipo']);

	}

}