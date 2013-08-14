<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Core_Repository_Model_Db_Datafilter extends Module_Core_Repository_Model_Abstract {

  public $filter_section = false;

  private $params     = null;

  const FILTER_PREFIX = 'f_';

  public function set_filters($params=null){
    unset($params['module'], $params['controller'], $params['action'], $params['controller_prefix']);
    $filters = array();

    foreach($params AS $key=>$value){
      $filter_found = stristr($key, self::FILTER_PREFIX );
      if( ! empty($filter_found) && $this->is_default_value($key,$value,$params['type'])===FALSE ){
        $filters[$key] = $value;
      }
    }

    $session = App::module('Core')->getModel('Namespace')->get( 'search' );
    $session->search['filters'][$params['type']] = $filters;
  }

  function is_default_value($key=null,$value=null,$type=null){
    $values_not_required = array();
    switch($type){
      case App::xlat('LINK_bible'):
           if($key=="f_seo"){
             $values_not_required = array("old","new", "null");
           }
           if($key=="f_testament"){
             $values_not_required = array("null");
           }
           break;

      default:break;
    }
    return (in_array($value, $values_not_required) )? TRUE : FALSE;
  }

  function apply_filters_to_query(&$select){
    $session = App::module('Core')->getModel('Namespace')->get( 'search' );
    if( empty($session->search['filters']) || empty($this->filter_section) ){
      return false;
    }

    /*
     * @todo: Modify this code to use it like XPLORA_DATAFILTER, so I can use
     * different types of filters, like: =, >, <, between, in, like, !=, etc.
     * IT'S A MUST!
     */
    foreach($session->search['filters'][$this->filter_section] AS $field=>$value){
      $select->where( str_replace(self::FILTER_PREFIX,"",$field) . " = ?", $value );
    }
  }

}