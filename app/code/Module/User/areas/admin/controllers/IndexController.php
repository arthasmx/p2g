<?php
require_once 'Module/User/Controller/Action/Admin.php';
class IndexController extends Module_User_Controller_Action_Admin {

  function preDispatch(){
    $this->view->current_menu = array('menu'=>2,'sub'=>3); // initial menu
  }

  function dashboardAction(){
    $this->indexAction();
    $this->_helper->getHelper('ViewRenderer')->setScriptAction( "index" );
  }

  function indexAction(){

  }

  function adminAction(){
    $this->_helper->getHelper('ViewRenderer')->setScriptAction( "index" );
  }

  function logoutAction(){
    App::module('Acl')->getModel('Acl')->logout();
  }

  protected function get_breadcrumbs( $breadcrumb = null ){
    if( empty($breadcrumb)){
      return null;
    }

    return array( array('title'=> App::xlat($breadcrumb) ) );
  }

}