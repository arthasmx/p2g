<?php
require_once 'Module/Articles/Controller/Action/Admin.php';

class Articles_IndexController extends Module_Articles_Controller_Action_Admin {

	function preDispatch(){
		App::module("Acl")->getModel('acl')->requirePrivileges('admin');
		$this->designManager()->setCurrentLayout('admin');
		$this->view->locale=App::locale()->getLang();
	}

	function indexAction() {}

	/**
	* Listado de Articulos (ARTICLES)
	*/
	function listingAction(){}

	/**
	* Agregar nuevo articulo
	*/
	function addAction(){
		$this->view->createParamsForm=$this->_module->getModel('Forms/Areas/Admin/CreateArticle')->get();
	}

}