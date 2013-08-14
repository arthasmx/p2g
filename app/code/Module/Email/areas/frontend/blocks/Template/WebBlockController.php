<?php

require_once 'Module/Email/Controller/Block.php';

class Email_Template_WebBlockController extends Module_Email_Controller_Block {

	/**
	 * Parámetros que debe recibir obligatoriamente el bloque
	 *
	 * @var array
	 */
	protected $_mandatoryParams = array(
		"sender"	=> "Sender del email (object)",
		"firma"		=> "Firma del mensaje (string)",
		"body"		=> "Cuerpo del mensaje (string)",
	);

	function init() {
		parent::init(); // Añade todos los parámetros recibidos a la vista y comprueba parámetros obligatorios
	}

	function htmlAction() {}

	function txtAction() {}

}