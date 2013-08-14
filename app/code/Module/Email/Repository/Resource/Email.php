<?php

class Module_Email_Repository_Resource_Email extends Core_Model_Repository_Resource {

	private static $config=null;

	function init() {
		if (!self::$config) self::$config=$this->_module->getConfig('core');
    }

    function sender() {
    	require_once('Zend/Mail.php');
    	$email=new Zend_Mail('UTF-8');
    	$email->addHeader('X-MailGenerator', 'XSEngine');


    	// Transport
			$login = array(
					'auth' => 'login',
					'port' => self::$config['config_smtp_port'],
					'username' => self::$config['config_smtp_user'],
					'password' => self::$config['config_smtp_pwd']
			);
			require_once("Zend/Mail/Transport/Smtp.php");
			$transport = new Zend_Mail_Transport_Smtp( self::$config['config_smtp_host'], $login );
			$email->setDefaultTransport($transport);

    	return $email;
    }

    function config($key=false) {
    	if ($key) {
    		return @self::$config[$key];
    	}
    	return self::$config;
    }

}