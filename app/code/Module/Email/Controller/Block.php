<?php

require_once 'Local/Controller/Block.php';

class Module_Email_Controller_Block extends Local_Controller_Block {

	/**
	 * Parámetros que debe recibir obligatoriamente el bloque
	 *
	 * @var array
	 */
	protected $_mandatoryParams = array();

	/**
	 * Añade todos los parámetros recibidos a la vista y comprueba parámetros obligatorios
	 *
	 */
	function init() {
		// Comprueba parámetros obligatorios
			foreach ($this->_mandatoryParams as $param=>$title) {
				if ($this->getParam($param)===false) {
					$this->_module->exception("El parámetro '$param' es obligatorio: ".$title);
				}
			}
		// Asigna parámetros a la vista
			foreach ($this->getParams() as $key=>$value) {
				$this->view->{$key}=$value;
			}
	}

}