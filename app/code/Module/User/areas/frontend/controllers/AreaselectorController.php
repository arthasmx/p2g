<?php
require_once 'Module/User/Controller/Action/Frontend.php';

class User_AreaselectorController extends Module_User_Controller_Action_Frontend   {

	protected $_user=false;

	function preDispatch() {
			if($this->_user=App::module("Acl")->getModel('acl')->data){
				$this->designManager()->setCurrentLayout('area-selector');				
			}else{
				$this->logoutAction();
			}
	}

	/**
	* Metodo para la seleccion del area a la cual deseas ingresar.
	* Obvio, solamente mostraremos las areas a las cuales tienes privilegios
	*/
	function indexAction() {
		// Sacamos los datos del cliente logeado. OJO: Debera estar LOGEADO para que pueda ingresar aqui
		$this->view->areas = $this->_user['privileges'];
	}

	/**
	 * Hacemos logout desde el AREA-SELECTOR
	 */
	function logoutAction(){
		session_destroy();
		header('Location:' . App::www('/') );
		exit;
	}

}