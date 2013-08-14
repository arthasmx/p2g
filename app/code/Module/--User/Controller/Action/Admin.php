<?php

require_once 'Module/User/Controller/Action.php';


class Module_User_Controller_Action_Admin extends Module_User_Controller_Action  {

	/**
	 * Constructor llamado desde el __construct del parent
	 */
	protected function _construct() {
		// Ejecutamos el construct del Parent
			parent::_construct();
	}

	function init() {
		//$this->designManager()->setCurrentLayout('account/main');
	}

}