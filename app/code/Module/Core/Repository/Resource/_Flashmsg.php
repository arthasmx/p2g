<?php

class Module_Core_Repository_Resource_Flashmsg extends Core_Model_Repository_Resource {

	/**
	 * Almacén para los mensajes
	 *
	 * @var unknown_type
	 */
	protected $_messages = array();

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
	 * Wrapper para añadir un mensaje tipo error
	 *
	 * @param string $msg
	 */
	public function error($msg) {
		$this->add('error',$msg);
	}

	/**
	 * Wrapper para añadir un mensaje tipo info
	 *
	 * @param string $msg
	 */
	public function info($msg) {
		$this->add('info',$msg);
	}

	/**
	 * Wrapper para añadir un mensaje tipo warning
	 *
	 * @param string $msg
	 */
	public function warning($msg) {
		$this->add('warning',$msg);
	}

	/**
	 * Wrapper para añadir un mensaje tipo success
	 *
	 * @param string $msg
	 */
	public function success($msg) {
		$this->add('success',$msg);
	}

	/**
	 * Añade un mensaje
	 *
	 * @param string $type Tipo del mensaje
	 * @param string $msg
	 */
	public function add($type,$msg) {
		$this->_messages[$type][] = array( 'time' => time(), 'msg' => $msg );
		$this->_saveData();
	}

	/**
	 * Devuelve los mensajes del tipo indicado y vacia la cola.
	 *
	 * En caso de no indicar el tipo, se devuelven todos los mensajes en un array multi-dimensional agrupados por tipo
	 * y posteriormente vacia la cola.
	 *
	 * @param string $type=false Tipo del mensaje
	 * @return mixed array | bool
	 */
	function flush($type=false){
		$messages = array();

		if (!$type) {

			// El tipo no se ha especificado, llamamos recursivamente a flush() para obtener los mensajes de todos los tipos

				foreach ( array_keys((array)$this->_messages) as $msgType ) {
					$msgs=$this->flush($msgType);
					if (count($msgs)>0) $messages[$msgType]=$msgs;
				}

				// Vaciamos los mensajes y reiniciamos el namespace
					$this->_messages=array();
					$this->_saveData();

		} else {

			if (isset($this->_messages[$type])) {
				$messages=$this->_messages[$type];
				unset($this->_messages[$type]);
				$this->_saveData();
			}

		}

		if (is_array($messages) && count($messages)>0) {
			return $messages;
		}

		return false;
	}

	/**
	 * Carga los mensajes del namespace
	 *
	 */
	protected function _loadData() {

		$this->_messages=$this->_namespace->messages;
		$this->loadCss();
	}

	/**
	 * Almacena los mensajes en el namespace
	 *
	 */
	protected function _saveData() {
		$this->_namespace->messages=$this->_messages;
		$this->loadCss();
	}

	public function loadCss() {
		if (is_array($this->_messages) && count($this->_messages)>0) {

			// Añadimos los estilos de los mensajes a la cabecera
				App::header()->addLink(
					App::skin('/css/blocks/flashmsg.css'),
					array('rel'=>'stylesheet','type'=>'text/css')
				);

		}
	}
}