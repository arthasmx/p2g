<?php
require_once 'Module/Default/Controller/Action/Frontend.php';

class MultimediaController extends Module_Default_Controller_Action_Frontend {

  function preDispatch(){}

  function indexAction(){
    $this->view->multimedia = App::module('Articles')->getModel('Article')->get_article( App::xlat('LINK_multimedia') );
    if( empty($this->view->multimedia) ){
      $this->_module->exception(404);
    }

    $this->view->pageBreadcrumbs = $this->get_breadcrumbs( $this->getRequest()->getParam('action') );
  }

  function mediaAction(){
    $media = $this->getRequest()->getParam('media');
    $this->view->media = App::module('Articles')->getModel('Article')->get_article( $media );
    if( empty($this->view->media) ){
      $this->_module->exception(404);
    }

    $this->view->pageBreadcrumbs = $this->get_breadcrumbs( $this->getRequest()->getParam('action'), $media );
  }

  protected function get_breadcrumbs( $action = null, $media=null ){

	$trimed_route = rtrim( App::xlat('route_multimedia'), "/" );
    switch ( $action ){
      case 'index':
              return array(
                array('title'=> App::xlat('BREADCRUMBS_multimedia') )
              );
              break;
      case 'media':
              return array(
                array('title'=> App::xlat('BREADCRUMBS_multimedia')  , 'url' => App::base( $trimed_route ) ),
                array('title'=> str_replace("-", " ", $media) )
              );
              break;
      default:
              return null;
              break;
    }

  }

}