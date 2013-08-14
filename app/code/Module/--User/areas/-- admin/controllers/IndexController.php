<?php
require_once 'Module/User/Controller/Action/Frontend.php';
class IndexController extends Module_User_Controller_Action_Frontend {

  function preDispatch(){}

  function indexAction(){
    $this->designManager()->setCurrentLayout('intro');
  }



  protected function get_breadcrumbs( $breadcrumb = null ){
    if( empty($breadcrumb)){
      return null;
    }

    return array( array('title'=> App::xlat($breadcrumb) ) );
  }

}