<?php

require_once('Zend/Db/Adapter/Pdo/Mysql.php');
class Local_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql  {

	var $debug	= false;

	/**
	 * Wrapper function for query function to allow for nicer debugging
	 *
	 * @param string|Zend_Db_Select $sql The SQL statement with placeholders.
	 * @param array $bind An array of data to bind to the placeholders.
	 * @return Zend_Db_Pdo_Statement
	 * @throws Exception To re-throw PDOException.
	 */
	public function query($sql, $bind = false, $debug=true) {
		if (false===$bind) $bind=array();

		// Si se ha habilitado el debug de las consultas al mysql añadimos la consulta al log de debug como info
		// Si el debug level de la web es inferior a INFO, no se añadirá, lógicamente, es decir, sólo se añade si el log level es INFO (6) o DEBUG (7)
			if (@$this->debug==1 && @$debug && Core_Model_Log::getLogLevel()>=Core_Model_Log::INFO) {
			  /* arthasmx: hide logger */
				// App::log('debug')->info('Local_Db_Adapter '.PHP_EOL."\tQUERY: ".str_replace(array(PHP_EOL,"\r","\n","\t","    ","   ","  ")," ",(string)$sql));
			}

		try {
			return parent::query($sql, $bind);
		} catch (Exception $e) {
			// Maneja las excepciones en las consultas para hacer logging y lanzar un evento en caso de que se desee notificar
		  /* arthasmx: hide logger */
				// App::log('exceptions')->err('Local_Db_Exception '.$e->getMessage().PHP_EOL."\tQUERY: ".str_replace(array(PHP_EOL,"\r","\n","\t","    ","   ","  ")," ",(string)$sql));
				App::events()->dispatch('local_db_exception',array("sql"=>&$sql,"exception"=>&$e),false,Core_Model_Log::ERR); // Error en consulta al mysql

			// Lanza la excepción
				throw $e;
		}
	}

}

?>