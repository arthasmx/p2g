<?php
class Module_Acl_Observer_Done extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		$acl=$this->_module->getModelSingleton( 'acl' );

		echo "<pre>Usuario:";
			print_r($acl->user);
		echo "</pre>";
		echo "<pre>Life:";
			print_r($acl->life);
		echo "</pre>";
		echo "<pre>Pass:";
			print_r($acl->data);
		echo "</pre>";
		echo "<pre>Errors:";
			print_r($acl->flushErrors());
		echo "</pre>";

	}

}