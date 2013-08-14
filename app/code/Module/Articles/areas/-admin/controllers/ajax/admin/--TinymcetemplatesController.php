<?php
require_once 'Module/Articles/Controller/Action/Admin.php';
class Articles_Ajax_Admin_TinymcetemplatesController extends Module_Articles_Controller_Action_Admin{

	function preDispatch() {
		App::module("Acl")->getModelSingleton('acl')->requirePrivileges('admin');
		$this->designManager()->setCurrentLayout('ajax');
		$this->view->locale = App::locale()->getLang();
	}

	/**
	 * Metodo para cargar los templates de ejemplo para el TINY
	 */
	function templateAction(){
		$this->getHelper('viewRenderer')->setScriptAction("template-".$this->getRequest()->getParam('id'));
	}

	/**
	 * Instanciamos al objeto Json
	 */
	protected function _json() {
		require_once("Zend/Json.php");
		return new Zend_Json;
	}

}