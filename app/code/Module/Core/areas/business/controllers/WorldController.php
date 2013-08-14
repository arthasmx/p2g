<?php
require_once 'Local/Controller/Action.php';

class Core_WorldController extends Local_Controller_Action   {

 	/*
    * @desc Cargamos los estados para un selectbox
    */
	function statesAction(){
		if ( $this->getRequest()->getParam('pais') && (string)strlen($this->getRequest()->getParam('pais'))>=1 ) {
			echo App::module('Core')->getResource('World')->statesJson($this->getRequest()->getParam('pais'));
		}else{
			echo "false";
		}
		exit;
	}

}