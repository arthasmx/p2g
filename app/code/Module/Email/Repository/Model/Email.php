<?php

require_once "Core/Model/Repository/Model.php";

class Module_Email_Repository_Model_Email extends Core_Model_Repository_Model {

	protected $cc = true;

	protected $_resource = null;

	protected $debug = false;

	public function init() {
		$this->_resource = &$this->_module->getResource('email');
		App::url()->setProtocol('http'); // No queremos que nuestros emails contengan links al https.
	}

// ACCESORS ********************************************************************************

	public function __call($function, $args) {
		// Comprueba SET
			preg_match("/^set([a-zA-Z\_]+)$/",$function,$matches);
			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var}) || @$this->{$var}===false) {
					$this->{$var}=$args[0];
				}
				return $this;
			}
		// Comprueba GET
			preg_match("/^get([a-zA-Z\_]+)$/",$function,$matches);
			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var})) {
					return $this->{$var};
				}
				return false;
			}


		$this->_module->exception("No se encuentra el método $function en ".get_class($this));
	}

	public function asArray() {
		$array=array();
		foreach ($this as $var=>$value) {
			if ($var[0]!="_" && $value!==false) $array[$var]=$value;
		}
		return $array;
	}

// MAIN ********************************************************************************

	public function submit() {}

	protected function getSender() {
		if (!$this->_resource) {
			$this->_module->exception("No se ha podido acceder al recurso para el envío de emails.");
		}
		return $this->_resource->sender();
	}

	protected function debug() {
		echo $this->content['html'];
		echo "<br/><br/><br/><br/><hr><br/><br/><br/><br/>";
		echo "<h1>Texto plano:</h1>";
		echo "<pre>";
			print_r($this->content['txt']);
		echo "</pre>";
		exit;
	}

	protected function send() {
		if (!isset($this->sender)) {
			$this->_module->exception('No se ha podido acceder al \$this->sender');
		}

		// Comprobamos carboncopy y lo enviamos si está activo
			$config=$this->_module->getConfig('core');
			if (@$config['config_carboncopy']==1) {
				// Destinatario
					$this->sender->addBcc( $this->_module->getConfig('core','remitente_carboncopy_rcpt') );
			}

		// Enviamos email
			$this->sender->send();
	}

}