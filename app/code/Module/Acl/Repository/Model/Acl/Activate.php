<?php
require_once("Module/Acl/Exception.php");

class Module_Acl_Repository_Model_Acl_Activate extends Core_Model_Repository_Model {

	protected $username		= false;
	protected $hash			= false;

	const LIFETIME	= 14400; // 3600 * 4

	protected $_resource 	= null;

	/**
	 * Inicialización del modelo
	 *
	 * Se comprueba que el recurso haya sido especificado, se establece el namespace y se cargan los datos.
	 *
	 */
	function init() {
		if (!$this->_resource) {
			$this->_resource=$this->_module->getResourceSingleton('acl/activate');
		}
	}

	function reset() {
		$this->username=false;
		$this->hash=false;
		$this->data=false;
	}

	public function setUsername($username) {
		$this->username=$username;
		return $this;
	}

	public function getUsername() {
		return $this->username;
	}

	public function setHash($hash) {
		$this->reset();
		$this->hash=$hash;
		$data=$this->_resource->reset()
							->setHash($hash)
							->setCheck_expires(1)
							->setUsed(0)
							->getRow();

		if (isset($data['username'])) {
			$this->username=$data['username'];
		} else {
			$this->username=false;
		}

		return $this;
	}

	public function validate() {
		if ($this->username) {
			$this->_resource->setHash($this->hash)->douse( App::module('Core')->getResourceObject('ip')->get() );
			return true;
		}
		return false;
	}

	public function doLogin() {
		if ($this->username) {
			
			if(!$this->_module->getModel('Acl')->autologin($this->username)){
				header("Location:" . App::base("/"));
				exit;
			}
			return true;
		}
		return false;
	}

	/**
	 * Metodo utilizado para generar un ID random para controlar las activaciones de las cuentas de los usuarios
	 */
	public function getActivateHash() {
			if (!$this->username) throw new Module_Acl_Exception('Debe especificarse el usuario');
			$this->hash=substr( md5( $this->username.time().rand(0,9999) ), 0, 32);
			$id=$this->_resource->createAccountActivation(
				$this->hash,
				$this->username,
				App::module('Core')->getResourceObject('ip')->get(),
				time()+self::LIFETIME
			);
			return $this->hash;
	}
	
	/**
	 * Metodo para actualizar la tabla ACL el campo STATUS
	 */
	public function activate() {
		if (!$this->username) throw new Module_Acl_Exception('Debe especificarse el usuario');
		return $this->_resource->activate($this->username);
	}
	
}