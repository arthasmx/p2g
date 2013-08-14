<?php
require_once 'Module/Articles/Controller/Action/Frontend.php';
class Articles_AnnouncementController extends Module_Articles_Controller_Action_Frontend{

  function preDispatch() {
    
  }

  function readAction(){
    $this->view->event   = $this->_module->getModel('Article')->read_full_article( $this->getRequest()->getParam('seo'),true,true );
    $this->view->folders = $this->_module->getModel('Event')->set_event_folders($this->view->event['article_id'],$this->view->event['created'] );

    $this->view->pageBreadcrumbs = array('title'=> $this->view->event['title'], 'icon'=>'icon-file', 'crumbs' => array( 
      array( 'txt'=> ucwords($this->view->event['type']) ,'ico'=>'icon-copy','url'=> $this->view->event['type'])
    ));
    $this->view->small_bread = true;
  }

  function listAction(){
    $this->view->events = $this->_module->getModel('Article')->get_article_list( $this->getRequest()->getParam( App::xlat('route_paginator_page') ), App::xlat('eventos') ,false,false,false,'enabled' );
    $this->view->pageBreadcrumbs = array('title'=> ucwords( App::xlat('eventos') ), 'icon'=>'icon-file');
    $this->view->small_bread = true;
  }

  protected function get_breadcrumbs($action=null, $title=null ){
    switch ( $action ){
      case 'read':
        return array(
        array('title'=> $title )
        );
        break;
      default:
              return null;
              break;
    }

  }

}