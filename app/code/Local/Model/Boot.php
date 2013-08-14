<?php

/*
	NO BORRAR ÉSTE ARCHIVO: realiza la inicialización local de la aplicación,
	sustituyendo el View Renderer del Xplora Engine por uno Local, permitiendo
	así el uso de filtros y ayudantes locales de la aplicación.

	Si ésta clase y el método init() no existen, la aplicación no funcionará, ya que son
	accedidos desde Core_Model_Boot::init().
*/
class Local_Model_Boot  {

    function init() {
		// Rutinas para incluir objetos de la libreria Local que sustituyen a los de la libreria Xplora
			self::initLocalView();
    }

    /**
     * Reemplaza el ViewRenderer y Xplora_View por sus homónimos de la aplicación local
     *
     */
    function initLocalView() {

		require_once "Zend/Controller/Action/HelperBroker.php";
		if (Zend_Controller_Action_HelperBroker::hasHelper('ViewRenderer')) {
		    Zend_Controller_Action_HelperBroker::removeHelper('ViewRenderer');
		}

		require_once 'Core/View.php';
		require_once 'Core/Controller/Action/Helper/ViewRenderer.php';

		Zend_Controller_Action_HelperBroker::addHelper( new Core_Controller_Action_Helper_ViewRenderer() );

    }
}