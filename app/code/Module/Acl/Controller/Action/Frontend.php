<?php

require_once 'Module/Acl/Controller/Action.php';


class Module_Acl_Controller_Action_Frontend extends Module_Acl_Controller_Action  {

//	protected $_requireSSL = true; // Las acciones de éste controlador requieren SSL
	protected $_requireSSL = null;

	/**
	 * Constructor llamado desde el __construct del parent
	 */
	protected function _construct() {
		// Ejecutamos el construct del Parent
			parent::_construct();
	}


}