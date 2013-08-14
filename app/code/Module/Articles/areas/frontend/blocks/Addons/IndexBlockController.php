<?php
require_once 'Core/Controller/Block.php';
class Articles_Addons_IndexBlockController extends Core_Controller_Block {

  function init() {}

  function belowImageAction(){
    $this->view->addons   = $this->_module->getModel('Article')->get_article_addons( $this->getParam('id'), true );
    $this->view->folders = $this->getParam('folders');
    $this->view->created = $this->getParam('created');

    App::module('Core')->getModel('Libraries')->addons_dropdown_menu();
    App::module('Core')->getModel('Libraries')->twitter_bootstrap_video_player('a.video');
  }

  function mapAction(){
    App::module('Core')->getModel('Libraries')->google_map_launcher($this->getParam('id'), $this->getParam('launcher'), $this->getParam('coordinates') );
  }

  function miniGalleryAction(){
    $this->view->gallery = App::module('Articles')->getModel('Files')->get_gallery_thumbnails( $this->getParam('thumb') );
    $this->view->path    = $this->getParam('path');

    if( ! empty($this->view->gallery) && ! empty($this->view->path) ){
      App::module('Core')->getModel('Libraries')->colorbox('white');
      App::header()->add_jquery_events("
        jQuery('a.cBox-mini-gallery').colorbox({rel:'cBox'});
      ");
    }

  }




  function articleSlideGalleryAction(){
    $folders           = $this->getParam('folders');
    $this->view->files = App::module('Articles')->getModel('Files')->get_gallery_thumbnails( $folders['gallery'] );
    $this->view->path  = $folders['url'] . 'gallery/';
    App::module('Core')->getModel('Libraries')->cycle2();
  }

  function articleAddonsAction(){
    $this->view->addons  = App::module('Articles')->getModel('Article')->get_article_addons( $this->getParam('id'), true );
    $this->view->created = $this->getParam('created');
    $this->view->folders = $this->getParam('folders');

    if( ! empty( $this->view->addons['map'] ) ){
      App::module('Core')->getModel('Libraries')->google_map("googleMap", $this->view->addons['map']['reference'] );
    }

  }

}