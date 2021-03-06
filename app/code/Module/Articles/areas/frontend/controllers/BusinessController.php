<?php
require_once 'Module/Articles/Controller/Action/Frontend.php';
class Articles_BusinessController extends Module_Articles_Controller_Action_Frontend{

  function preDispatch() {}

  function readAction(){
    $this->view->article = $this->_module->getModel('Article')->read_full_article( $this->getRequest()->getParam('seo'),true,true );
    $this->view->folders = $this->_module->getModel('Business')->set_business_folders($this->view->article['article_id'],$this->view->article['created'] );
    $this->view->related_tags = $this->_module->getModel('Article')->related_tags( $this->getRequest()->getParam('seo'),true,true );

    $this->view->pageBreadcrumbs = array('title'=> $this->view->article['title'], 'icon'=>'icon-file', 'crumbs' => array(
      array( 'txt'=> ucwords($this->view->article['type']) ,'ico'=>'icon-copy','url'=> $this->view->article['type'])
    ));

  }

  function listAction(){
    $this->view->articles        = $this->_module->getModel('Business')->get_business_list( $this->getRequest()->getParam( App::xlat('route_paginator_page') ), null, 'enabled' );
  }

  function readPromoAction(){
    $this->view->promo = App::module('Addons')->getModel('Promotions')->get( $this->getRequest()->getParam('id') );
  }

  function promotionsAction(){
    $this->view->promotions = App::module('Addons')->getModel('Promotions')->promotions( $this->getRequest()->getParam( App::xlat('route_paginator_page') ) );
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