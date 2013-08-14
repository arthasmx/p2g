<?php

require_once 'Module/Error/Controller/Action.php';

class Error_Ajax_ErrorController extends Module_Error_Controller_Action {

	function preDispatch(){
		//$this->designManager()->disable();
	}

}