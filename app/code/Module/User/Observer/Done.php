<?php
class Module_User_Observer_Done extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		$user=$this->_module->getModelSingleton( 'user' );

		echo "<pre>Usuario:";
			print_r($user->user);
		echo "</pre>";
		echo "<pre>Data:";
			print_r($user->data);
		echo "</pre>";

		$referer=$this->_module->getModelSingleton( 'user/referer' );
		//$referer->set('prueba');
		echo "<pre>Referer:";
			print_r($referer->get());
		echo "</pre>";



	}

}