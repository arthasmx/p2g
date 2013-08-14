<?php
class Module_Acl_Observer_Error extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {

		$acl=$this->_module->getModelSingleton( 'acl' );

		foreach ( (array)$acl->flushErrors() as $errorKey=>$errorData) {
			$errorMessage=App::xlat(@$errorData['message']);
			$errorVars=@$errorData['vars'];
			foreach((array)$errorVars as $var=>$value) {
				$errorMessage=str_replace("%".$var."%",$value,$errorMessage);
			}

			if (preg_match("/^ERROR_/",$errorKey)) {
				App::module('Core')->getModel('Flashmsg')->error($errorMessage);
			}
			if (preg_match("/^WARNING_/",$errorKey)) {
				App::module('Core')->getModel('Flashmsg')->warning($errorMessage);
			}
			if (preg_match("/^INFO_/",$errorKey)) {
				App::module('Core')->getModel('Flashmsg')->info($errorMessage);
			}

		}

	}

}