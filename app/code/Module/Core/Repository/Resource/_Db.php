<?php

class Module_Core_Repository_Resource_Db extends Core_Model_Repository_Resource {

	private $db=null;

	function init() {
    	// Cargamos configuración de base de datos en APP\ETC\db.ini, usando la funcion de clase Core_Model_Config
    	$config=Core_Model_Config::getConfigIni("db");
    	require_once('Xplora/Db.php');

    	$parametros = array ('host' =>		$config->db->config->host,
							 'username' =>	$config->db->config->user,
							 'password' =>	$config->db->config->pass,
							 'dbname' =>	$config->db->config->db);

    	// Requerimos el adaptador correspondiente en función de la configuración
	    	switch ($config->db->config->adapter) {
	    		case "PDO_MYSQL":
	    			require_once('Local/Db/Adapter/Pdo/Mysql.php'); // Adaptador PDO_MYSQL personalizado que incorpora un mejor debugging
	    			$this->db=new Local_Db_Adapter_Pdo_Mysql($parametros);
	    			break;
	    		case "MYSQLI":
	    			require_once('Local/Db/Adapter/Mysqli.php'); // Adaptador PDO_MYSQL personalizado que incorpora un mejor debugging
	    			$this->db=new Local_Db_Adapter_Mysqli($parametros);
	    			break;
	    	}

		try {
			$this->db->query("SET NAMES utf8;");
		} catch (Exception $e) {
    		$this->_module->exception("No se ha podido inicializar el adaptador ".$config->db->config->adapter.": ".$e->getMessage(),$e->getCode());
    	}

		$this->db->debug=$config->db->config->debug; // Si se ha especificado debug en la configuración se envía para que se logeen las consultas en el debug

    }

    function get() {
    	if (is_null($this->db)) {
    		$this->_module->exception("No se ha podido inicializar la bbdd");
    	}
    	return $this->db;
    }

}