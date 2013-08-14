<?php

require_once 'Core/Controller/Block.php';

class Blog_IndexBlockController extends Core_Controller_Block {

	function init() {}

	/**
	 * Bloque que muestra los últimos casos de éxito
	 *
	 */
	function getlatestpAction() {

		// Sacamos los articulos
			$this->view->blog=$this->_module->getModelSingleton('blog')->getLatest();
	}

	function getarchivesAction() {
		$this->view->archives=$this->_module->getModelSingleton('blog')->getArchives();
	}

}