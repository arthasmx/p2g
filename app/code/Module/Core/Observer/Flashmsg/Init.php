<?php
class Module_Core_Observer_Flashmsg_Init extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		// Inicializa el Flash messenger (para poder cargar los datos)
			App::module('Core')->getModel('Flashmsg')->init();
	}

}