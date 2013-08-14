<?php

class Module_User_Repository_Model_User_Referer extends Core_Model_Repository_Model {

	public $referer			= null;

	public $detail			= null;

	protected $_namespace 	= null;

	function init() {
		if (!$this->_namespace) {
            // $this->_namespace=App::module('Core')->getResourceObject('Namespace')->get(get_class($this));
            $this->_namespace=App::module('Core')->getModel('Namespace')->get(get_class($this));
		}

		$this->_loadData();

		$this->_saveData();
	}

	protected function _loadData() {
		// Cargamos todos los atributos públicos
			foreach ($this->_namespace as $var=>$value) {
				$this->{$var}=$value;
			}
	}

	protected function _saveData() {
		// Almacenamos todos los atributos públicos
			foreach ($this as $var=>$value) {
				if ($var[0]!="_") {
					$this->_namespace->{$var}=$value;
				}
			}
	}

	protected function _unloadData() {
		// Eliminamos todos los atributos públicos y reiniciamos el namespace
			foreach ($this as $var=>$value) {
				if ($var[0]!="_") {
					$this->{$var}=null;
				}
			}
			$this->_namespace->unsetAll();
			//$this->_namespace->lock();
	}

// MAIN ****************************************************************************************

	public function set($referer,$showErrors=true) {
		// Si el usuario está identificado no permitimos que tenga otro referer más que el actual
			if ($this->_module->getModelSingleton('user')->data['referer']) {
				$referer=$this->_module->getModelSingleton('user')->data['referer'];
			}
		// Si el usuario está identificado ya no permitimos que tenga referer si no tiene uno ya
			if ($this->_module->getModelSingleton('user')->user && $this->_module->getModelSingleton('user')->data['referer']!=$referer) {
				return $this;
			}


		$this->referer=null;
		$this->detail=null;

		// Cargamos admin
			$admin=App::module('Admin')->getModelSingleton('admin')->get($referer,false);

			if ($admin) {
				if ($admin['active']==0 || $admin['locked']==1 || $admin['deleted']==1) {
					if ($showErrors) App::module('Core')->getModel('flashmsg')->warning(App::xlat("Lo sentimos, el admin %s no ha sido activado",$referer));
				} else {
					// Cargamos el descuento que le corresponde a éstos usuarios
						$admin['descuento']=App::module('Request')->getModelSingleton('descuento/admin')->get($referer);

					$this->referer=$referer;
					$this->detail=$admin;
				}
			} else {
				if ($showErrors) App::module('Core')->getModel('flashmsg')->warning(App::xlat("Lo sentimos, el admin %s no se encuentra",$referer));
			}

		$this->_saveData();

		return $this;
	}

	public function reload() {
		$this->set($this->referer,false);
	}

	public function get() {
		return $this->referer;
	}

	public function detail() {
		return $this->detail;
	}

	public function clear() {
		// Si el usuario está identificado y el referer que tiene es el mismo que el que hay asignado no permitimos hacer un unset del referer
			if ($this->_module->getModelSingleton('user')->user && $this->_module->getModelSingleton('user')->data['referer']==$this->referer) {
				return $this;
			}

		$this->_unloadData();
		return $this;
	}

}