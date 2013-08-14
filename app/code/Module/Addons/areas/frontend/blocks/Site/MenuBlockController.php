<?php
require_once 'Core/Controller/Block.php';
class Addons_Site_MenuBlockController extends Core_Controller_Block {

  function subMenuAction(){
    $this->view->sub_menus = App::module('Addons')->getModel('Categories')->get_children_by_seo( $this->getParam('parent') );
    $this->view->parent    = $this->getParam('parent');
  }


/*
  function townSectionsAction(){
    $this->view->sections = $this->_module->getModel('Cities')->get_town_sections_for_menu( $this->getParam('town') );
    $this->view->town     = $this->getParam('town');
    $this->view->city     = $this->getParam('city');
  }

  function townGalleryAction(){
    $town = $this->getParam('town');
    if( empty($town) ){
      $this->view->files = null;
    }else{
      $path = WP . DS . 'media' .DS . 'cities' . str_replace('/',DS,$this->getParam('folder')) . 'gallery' . DS;
      $this->view->path  = '/media/cities'.$this->getParam('folder');
      $this->view->files = App::module('Core')->getModel('Filesystem')->get_files_from_path( $path, array( "include" => array("/\.jpg$/"), "exclude" => array("/listing\.jpg$/","/side\.jpg$/","/promote\.jpg$/","/article\.jpg$/","/aside-big\.jpg$/","/mobile\.jpg$/","/slider\.jpg$/") ) );
      if ( empty( $this->view->files ) ){
        $this->view->files = null;
      }
      App::module('Core')->getModel('Libraries')->cycle2();
    }
  }
*/
}