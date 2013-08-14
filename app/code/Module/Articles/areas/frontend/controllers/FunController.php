<?php
require_once 'Module/Articles/Controller/Action/Frontend.php';
class Articles_FunController extends Module_Articles_Controller_Action_Frontend{

  function preDispatch() {}

  function readAction(){
    $this->view->article = $this->_module->getModel('Article')->read_full_article( $this->getRequest()->getParam('seo'),true,true );
    $this->view->folders = $this->_module->getModel('Business')->set_business_folders($this->view->article['article_id'],$this->view->article['created'] );

    App::module('Core')->getModel('Libraries')->jquery_vegas_default('fun');

    $this->view->pageBreadcrumbs = array('title'=> $this->view->article['title'], 'icon'=>'icon-file', 'crumbs' => array(
        array( 'txt'=> ucwords($this->view->article['type']) ,'ico'=>'icon-copy','url'=> $this->view->article['type'])
    ));
  }

  function listAction(){
    App::module('Core')->getModel('Libraries')->jquery_vegas_default('fun');

    $this->view->fun             = $this->_module->getModel('Business')->get_business_list( $this->getRequest()->getParam( App::xlat('route_paginator_page') ), $this->getRequest()->getParam('type'), 'enabled' );
    $this->view->pageBreadcrumbs = array('title'=> ucwords( App::xlat('hospedaje') ), 'icon'=>'icon-file');
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