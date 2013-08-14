<?php

require_once 'Module/Email/Controller/Block.php';

class Email_Template_ReservacionescanceledBlockController extends Module_Email_Controller_Block {

	/**
	 * Parámetros que debe recibir obligatoriamente el bloque
	 *
	 * @var array
	 */
	protected $_mandatoryParams = array(
//		"sender"	=> "Sender del email (object)",
//		"firma"		=> "Firma del mensaje (string)",
//		"body"		=> "Cuerpo del mensaje (string)",
	);

	function init() {
		parent::init(); // Añade todos los parámetros recibidos a la vista y comprueba parámetros obligatorios
	}

	function htmlAction() {
		// Añadimos logo
			/*
			// NO FUNCIONA BIEN, SI SE ADJUNTA PERO NO SE MUESTRA
			$this->getParam('sender')->createAttachment(
				file_get_contents($this->view->path_skin."/art/email/logo.gif"),
				'image/gif',
				Zend_Mime::DISPOSITION_ATTACHMENT,
				Zend_Mime::ENCODING_BASE64,
				'logo.gif'
			);
			*/

	}

	function txtAction() {}

}