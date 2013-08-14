<?php

class Module_Core_Repository_Resource_Namespace extends Core_Model_Repository_Resource {
    protected $_namespace = null;

	public function getId() {
		require_once 'Zend/Session.php';
		return Zend_Session::getId();
	}

	public function setId($id) {
		require_once 'Zend/Session.php';
		return Zend_Session::setId($id);
	}

    /**
	 * Obtiene acceso al namespace indicado
	 *
	 * @param string $namespace
	 * @return Zend_Session_Namespace
	 */
	protected function _getSession($namespace=null,$unique=false) {
		require_once 'Zend/Session/Namespace.php';
		require_once 'Zend/Session.php';

		// Si la petición se ha realizado por línea de comandos, no queremos que nos almacene nada
			if (App::$requestType=='cli') {
				Zend_Session::forgetMe();
				//Zend_Session::stop();
			}

		$session=new Zend_Session_Namespace($namespace,$unique);
		return $session;
	}

	/**
	 * Accede al _namespace
	 *
	 * @return Zend_Session_Namespace
	 */
	public function get($namespace=null,$unique=false) {
		if (!$this->_namespace) {
			$this->_namespace=$this->_getSession( $namespace , $unique );
			echo "<pre>"; print_r( "1" ); echo "</pre>";
		}
		if (!$this->_namespace instanceof Zend_Session_Namespace) {
			throw new Core_Exception("No se ha podido obtener el acceso al namespace de la sesión");
		}
		echo "<pre>"; print_r( $this->_namespace ); echo "</pre>";
		return $this->_namespace;
	}

	public function set($namespace=null){
        if( empty($namespace) ){
            require_once 'Zend/Session.php';
            $n = new Zend_Session_Namespace("Module_Acl_Repository_Model_Acl");
            $n->test = 100;
        }

	}

	/**
	 * Metodo que agrega/elimina ids de items.
	 * Utilizado en Admin para agregar seleccionar productos por checkbox en una lista paginada
	 * @param $id | id del item
	 * @param $section | seccion del administrador
	 * @return void
	 */
	public function add2Session($id=false,$section=false,$action=false,$session_name='admin'){
		if(!$id || !$section || !$action) return false;

	// crear/leer session
		$session = $this->get($session_name);

	// Buscamos los indices dentro de la session y se guardan en 1 array temporal $sesion_tmp (similares....)
		foreach ( $session AS $key=>$value) {
			$session_tmp[$key]=$value;
		}

	// agregamos o quitamos el producto
		if ( array_key_exists($id,(array)@$session_tmp[$section][$action]) ){ //$session_tmp['similares']['editar']
			// Ya esta
				unset($session_tmp[$section][$action][$id]);
		}else{
			// No esta
				$session_tmp[$section][$action][$id]=$id;
		}

	// Guardamos nuevos datos en session
		unset($session->{$section}[$action]);
	    foreach ($session_tmp[$section][$action] AS $key=>$value) {
			$session->{$section}[$action][$key]=$value;
		}

	// Mostrandolo/Usandolo en otra seccion del sitio
		//echo "<pre>"; print_r( App::module('Core')->getResourceSingleton('Namespace')->get('admin')->similares ); echo "</pre>";
		//exit;

	}

}