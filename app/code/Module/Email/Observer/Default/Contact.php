<?php
class Module_Email_Observer_Default_Contact extends Core_Model_Module_Observer {

	public function init() {}

	/**
	 * Opciones recibidas
	 *
	 * - to: enviar al departamento....
	 * - from_name: nombre del cliente
	 * - from_email: email del cliente
	 * - from_tel: teléfono del cliente
	 * - msg: mensaje a enviar
	 * - carboncopy: enviar copia al cliente
	 *
	 * @param array $options
	 */
  public function dispatch($options=array()) {

      $this->_module->getModel('default/contact')
                    ->setTo($options['to'])
                    ->setFrom_name($options['name'])
                    ->setFrom_email($options['email'])
                    ->setMsg($options['comment'])
                    // ->setDebug(true)
                    ->submit();
  }

}