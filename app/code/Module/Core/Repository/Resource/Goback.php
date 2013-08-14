<?php

class Module_Core_Repository_Resource_Goback extends Core_Model_Repository_Resource {

	const MAX_ENTRIES = 15;

	/**
	 * Almacén para las urls
	 *
	 * @var array
	 */
	protected $_urls = array();

	/**
	 * Namespace para almacenar las urls
	 *
	 * @var unknown_type
	 */
	protected $_namespace 	= null;

	public function init() {
		$this->_namespace = $this->_module->getModel('Namespace')->get( get_class($this) );
		$this->_loadData();
	}

	/**
	 * Wrapper a Add para la añadir url actual;
	 *
	 * @param string $name
	 */
	public function current($name=null) {
		$this->add($name,null);
	}

	/**
	 * Añade una url
	 *
	 * @param string $name nombre de la url
	 * @param string $url=null
	 */
	public function add($name=null,$url=null) {
		if (null===$url) $url=$_SERVER['REQUEST_URI'];
		if (null===$name) $name=substr(md5($url),5,10);

		if (isset($this->_urls[$name])) unset($this->_urls[$name]);

		$this->_urls[$name] = array(
			'time' => time(),
			'url' => $url
		);

		if (count($this->_urls)>self::MAX_ENTRIES) {
			array_shift($this->_urls);
		}

		$this->_saveData();
	}

	/**
	 * Retorna la ultima url insertada en la cola o la url identificada con el nombre
	 *
	 * @param string $name
	 * @param string $default_url
	 * @return string
	 */
	public function get($name=null,$default_url=false) {

		$tmp=$this->_urls;
		if (null===$name) {
			$tmp=array_pop($tmp);
			return @$tmp['url'];
		}

		// Si no existe la url para el nombre especificado
			if (!isset($tmp[$name]['url'])) {
				// Intentamos devolver la url por defecto
					if (false!==$default_url) return $default_url;
				// Intentamos devolver la última url insertada en el goback
					$tmp=array_pop($tmp);
					return @$tmp['url'];
			} else {
				// Existe una url de retorno para el nombre especificado, la retornamos
				return @$tmp[$name]['url'];
			}
	}

	public function getAll() {
		return $this->_urls;
	}

	/**
	 * Carga las urls del namespace
	 *
	 */
	protected function _loadData() {
		$this->_urls=$this->_namespace->urls;
	}

	/**
	 * Almacena las urls en el namespace
	 *
	 */
	protected function _saveData() {
		$this->_namespace->urls=$this->_urls;
	}

}