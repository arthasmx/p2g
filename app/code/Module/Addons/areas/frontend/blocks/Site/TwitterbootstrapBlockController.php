<?php
require_once 'Core/Controller/Block.php';
class Addons_Site_TwitterbootstrapBlockController extends Core_Controller_Block {

  function imageSliderAction(){

    $this->view->target = $this->getParam('target');
    $this->view->folder = $this->getParam('folder');
    $this->view->files  = null;
    $path               = $this->getParam('path') . $this->getParam('folder');
    if ( empty( $path ) ){
      return null;
    }

    $this->view->files = App::module('Core')->getModel('Filesystem')->get_files_from_path( $path , array( "include" => "/\.jpg$/i", "exclude" => array("/listing\.jpg$/","/side\.jpg$/","/promote\.jpg$/","/article\.jpg$/","/aside-big\.jpg$/","/mobile\.jpg$/","/slider\.jpg$/")) );

    if ( empty( $this->view->files )  ){
      return null;
    }
    App::module('Core')->getModel('Libraries')->twitter_bootstrap_slider_autoplay( $this->getParam('target') );
  }

}