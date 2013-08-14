<?php

require_once 'Core/Controller/Action.php';

/**
 * Clase Local_Controller_Action para acciones genéricas de la aplicación.
 *
 * Todas las acciones de la aplicación (independientemente del área), deberán acabar extendiendo ésta clase.
 *
 */
class Local_Controller_Action extends Core_Controller_Action  {


	/**
	 * Constructor llamado desde el __construct del parent
	 */
	protected function _construct() {

		// Ejecutamos el construct del Abstract
			parent::_construct();

	}

	public function preDispatch() {
		parent::preDispatch();
	}

	/**
	 * postDispatch();
	 */
	public function postDispatch() {
		parent::postDispatch();	// Importante, si no se especifica, el método postDispatch del Abstract no se ejecutará
	}

}