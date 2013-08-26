<?php
require_once 'Module/Articles/Controller/Action/Frontend.php';

class Articles_IndexController extends Module_Articles_Controller_Action_Frontend{

  function preDispatch() {
    
  }

  function readAction(){
    $this->view->article  = $this->_module->getModel('Article')->read_full_article( $this->getRequest()->getParam('seo'),true,true );
    $this->view->folders  = $this->_module->getModel('Article')->set_article_folders($this->view->article['article_id'],$this->view->article['created'] );
  }

  function listAction(){
    $this->view->articles = $this->_module->getModel('Article')->get_article_list( $this->getRequest()->getParam( App::xlat('route_paginator_page') ), App::xlat('articulos') ,false,false,false,'enabled' );
    $this->view->pageBreadcrumbs = array('title'=> ucwords( App::xlat('articulos') ), 'icon'=>'icon-file');
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