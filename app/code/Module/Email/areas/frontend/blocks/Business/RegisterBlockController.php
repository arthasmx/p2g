<?php

require_once 'Module/Email/Controller/Block.php';

class Email_Business_RegisterBlockController extends Module_Email_Controller_Block {

	/**
	 * Parámetros que debe recibir obligatoriamente el bloque
	 *
	 * @var array
	 */
  protected $_mandatoryParams = array(
    "to"          =>	"Destinatario",
    "from_email"  =>	"Email cliente",
    "from_name"   =>	"Nombre cliente",
    "msg"         =>	"Mensaje"
     // "carboncopy"  =>	"Copia al email del cliente",
  );

	function init() {
		parent::init(); // Añade todos los parámetros recibidos a la vista y comprueba parámetros obligatorios
	}

	function htmlAction() {}

	function txtAction() {}

}