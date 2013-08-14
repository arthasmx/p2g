<?php

class Module_Core_Repository_Model_Namespace extends Core_Model_Repository_Model {

	public function get($namespace=null,$unique=null) {
		require_once 'Zend/Session/Namespace.php';
		require_once 'Zend/Session.php';

        if (App::$requestType=='cli') {
            Zend_Session::forgetMe();
        }

	    if( empty($namespace) ){
            $namespace = "Default";
	    }
        $session = new Zend_Session_Namespace($namespace,$unique);

		if ( ! $session instanceof Zend_Session_Namespace) {
			$this->_module->exception(App::xlat("No se ha podido obtener el acceso al namespace de la sesión"));
		}
		return $session;
	}

	public function getId() {
		require_once 'Zend/Session.php';
		return Zend_Session::getId();
	}

	public function setId($id) {
		require_once 'Zend/Session.php';
		return Zend_Session::setId($id);
	}

	public function clear($session_name=null){
  if (empty($session_name) ){
    return null;
  }

  require_once('Zend/Session.php');
  Zend_Session::namespaceUnset($session_name);
  return true;
	}

}