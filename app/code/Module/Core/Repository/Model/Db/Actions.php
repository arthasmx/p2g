<?php
require_once 'Zend/Db/Table/Abstract.php';
require_once 'Zend/Db/Table.php';
class Module_Core_Repository_Model_Db_Actions extends Zend_Db_Table_Abstract {

  public $table  = null;
  public $cud_db = null;

  public function __construct() {
    $utf8    = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'");
    $config  = Core_Model_Config::getConfigIni("db");
    $options = array ('host'            => $config->db->config->host,
                      'username'        => $config->db->config->user,
                      'password'        => $config->db->config->pass,
                      'dbname'          => $config->db->config->db
                      ,'driver_options'  => $utf8
    );
    $db = Zend_Db::factory('PDO_MYSQL', $options);
    Zend_Db_Table_Abstract::setDefaultAdapter($db);
  }

  function set_table($name = null){
    if( empty($name) ){
      App::module('Core')->exception( App::xlat('EXC_table_name_missing') . '<br />Launched at method set_table, file Repository/Model/Db/Actions' );
    }
    $this->table = new Zend_Db_Table($name);
  }

  function already_in_database($id=0, $field=null){
    if( empty($field) ){
      App::module('Core')->exception( App::xlat('EXC_table_field_name_missing') . '<br />Launched at method already_in_database, file Repository/Model/Db/Actions' );
    }
    $select = $this->table->select()->where("$field = ?", $id);
    $result = $this->table->fetchRow( $select );
    return empty($result)? null : $result->toArray();
  }

  function get_db(){
    return Zend_Db_Table::getDefaultAdapter();
  }

}