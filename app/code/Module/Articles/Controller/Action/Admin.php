<?php

require_once 'Module/Articles/Controller/Action.php';


class Module_Articles_Controller_Action_Admin extends Module_Articles_Controller_Action  {

	/**
	 * Constructor llamado desde el __construct del parent
	 */
	protected function _construct() {
		// Ejecutamos el construct del Parent
			parent::_construct();
	}

	function init() {
	  App::module("Acl")->getModel('acl')->is_user_logged();
	  //$na = App::module('Core')->getModel('Namespace')->get('user');
	  
	  // echo '<pre>'; print_r( $na->user['menu'] ); echo '</pre>'; 	  exit;
	}

}