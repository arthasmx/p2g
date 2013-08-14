<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Core_Repository_Model_Actions extends Module_Core_Repository_Model_Abstract {

  const ACTION_SESSION_NAME = "Actions";

  function get_translated_actions( $desired_action_url ){
    $locale = App::locale();
    $current_locale = $locale->getName();

    if( $locale->getDefault() === $current_locale ){
        return false;
    }

    $session = App::module('Core')->getModel('Namespace')->get( 'Actions' );
    $session->{$current_locale};

    $actions = $this->get_current_locale_actions($current_locale);
    $parsed_actions = $this->parse_actions($actions);

    if( empty($parsed_actions) ){
        return false;
    }

    $session->{$current_locale} = $parsed_actions;
    return $session->{$current_locale};
  }

  function get_current_locale_actions($current_locale){

    $select = $this->_db->select()
                        ->from(array('mt'   => 'actions_translation'), array( 'simulated' => 'description'))
                        ->join(array('m'	=> 'actions'            ), 'm.id = mt.action_id'            , array('real' => 'description'))
                        ->join(array('lang' => 'languages'       ), 'lang.namespace = mt.namespace', array())
                        ->where('mt.namespace = ?', $current_locale)
                        ->where('lang.status = ?' , 'enabled');

    return $this->_db->query( $select )->fetchAll();
  }

  function parse_actions($actions){
    $tmp_actions = Array();

    foreach((array)$actions AS $action){
        $tmp_actions[$action['simulated']] = $this->convert_seo_url_to_action($action['real']); 
    }

    if(empty($tmp_actions)){
        return false;
    }
    return $tmp_actions;
  }

  function convert_seo_url_to_action($seo_url){
    $tmp_action = explode("-" , $seo_url);
    if( empty($tmp_action)){
        $this->_module->exception( "EXC_NO_ACTION_TO_PARSE" );
    }

    $action_to_execute = null;
    foreach($tmp_action AS $key=> $value){
        $action_to_execute .= ($key > 0)? ucfirst($value) : $value;
    }

    return Array ( 'action' => $action_to_execute
                    ,'view' => $seo_url );
  }

}