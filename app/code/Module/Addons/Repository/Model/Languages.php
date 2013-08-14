<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Languages extends Module_Core_Repository_Model_Abstract {

  protected $name = false;
  protected $prefix = false;

  public $current_language = null;

  function get_languages(){
    $select = $this->_db->select()->from(array('lang'   => 'languages'));
    return $this->_db->query( $select )->fetchAll();
  }

  function get_enabled_languages() {
    if( ! empty($_SESSION['languages'])){
      return $_SESSION['languages'];
    }
    $select = $this->_db->select()
                        ->from(array('lang'   => 'languages'))
                        ->where('lang.status = ?', 'enabled');

    return $this->_db->query( $select )->fetchAll();
  }

  function get_languages_for_select(){
    $languages = $this->get_enabled_languages();
    $prefix    = array();
    foreach((array)$languages AS $language){
      $prefix[$language['prefix']]=$language['name'];
    }
    return $prefix;
  }

}