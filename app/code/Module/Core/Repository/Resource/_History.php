<?php

class Module_Core_Repository_Resource_History extends Core_Model_Repository_Resource {

	/**
	 * Almacén para los mensajes
	 *
	 * @var unknown_type
	 */
	protected $_urls = array();

	/**
	 * Namespace para almacenar los mensajes
	 *
	 * @var unknown_type
	 */
	protected $_namespace 	= null;

	public function init() {
		$this->_namespace = App::module('Core')->getResourceSingleton('Namespace')->get( get_class($this) );
		$this->_loadData();
	}

	/**
	 * Añade un mensaje
	 *
	 * @param string $type Tipo del mensaje
	 * @param string $msg
	 */
	public function add($url,$name=null) {
		if (null===$name) {
			$name=str_replace(md5($url),5,10);
		}
		$this->_urls[$name][] = array( 'time' => time(), 'msg' => $msg );
		$this->_saveData();
	}

	/**
	 * Carga los mensajes del namespace
	 *
	 */
	protected function _loadData() {
		$this->_urls=$this->_namespace->urls;
		$this->loadCss();
	}

	/**
	 * Almacena los mensajes en el namespace
	 *
	 */
	protected function _saveData() {
		$this->_namespace->urls=$this->_urls;
		$this->loadCss();
	}

	public function loadCss() {
		if (is_array($this->_urls) && count($this->_urls)>0) {

			// Añadimos los estilos de los mensajes a la cabecera
				App::header()->addLink(
					App::skin('/css/blocks/flashmsg.css'),
					array('rel'=>'stylesheet','type'=>'text/css')
				);

		}
	}
}