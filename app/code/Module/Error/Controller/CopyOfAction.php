<?php

require_once 'Local/Controller/Action.php';


class Module_Error_Controller_Action extends Local_Controller_Action  {

	protected $_requireSSL = null;

	function init() {}

	function preDispatch() {
		$this->designManager()->setCurrentLayout( "error" );
		$this->view->pageClass='page-error';
		$this->view->menuSelectOption=null;
	}

    function errorAction() {
        $errors = $this->_getParam('error_handler');

        $e=$errors->exception;
        $eType=get_class($e);

        $errorCode=$e->getCode();
        $errorMessage=$e->getMessage();

        if ($e instanceof Core_Exception) {
        	// Excepciones de nuestra aplicación
        	switch ($errorCode) {
        		case 404:
        			$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
        			$this->view->assign("errorCode","404");
	                $this->view->assign("errorDesc","La página que usted busca no ha sido encontrada.");
	                $this->_helper->getHelper('ViewRenderer')->setScriptAction('error404');
        			break;
        		case 403:
        			$this->getResponse()->setRawHeader('HTTP/1.1 403 Forbidden');
        			$this->view->assign("errorCode",$e->getCode());
					$this->view->assign("errorDesc",$e->getMessage());
        			break;
        		default:
        			$this->getResponse()->setRawHeader('HTTP/1.1 501 Internal Server Error');
        			$this->view->assign("errorCode","501");
					$this->view->assign("errorDesc","Se ha producido un error interno del servidor: <br/><small>".$e->getMessage()."</small>");
        			break;
        	}
		} else {
			// Excepciones del Zend
	        switch ($errors->type) {
	            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
	            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
	                // 404 error -- controller or action not found
	                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
	                $this->view->assign("errorCode","404");
	                $this->view->assign("errorDesc","La página que usted busca no ha sido encontrada.");
	                $errorCode=404;
	                $errorMessage="La página que usted busca no ha sido encontrada.";
	                $this->_helper->getHelper('ViewRenderer')->setScriptAction('error404');
	                // ... get some output to display...
	                break;
	            default:
	                // application error; display error page, but don't change
	                // status code
	                $this->getResponse()->setRawHeader('HTTP/1.1 501 Internal Server Error');
					$this->view->assign("errorCode","501");
					$this->view->assign("errorDesc","Se ha producido un error interno del servidor: <br/><small>".$e->getMessage()."</small>");
					$errorCode=501;
	                $errorMessage="Se ha producido un error interno del servidor.";
	                break;
	        }
		}

		App::events()->dispatch('module_error',array("errorCode"=>$errorCode,"errorMessage"=>$errorMessage));

    }

}