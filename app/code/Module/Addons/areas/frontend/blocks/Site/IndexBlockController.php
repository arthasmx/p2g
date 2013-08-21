<?php
require_once 'Core/Controller/Block.php';
class Addons_Site_IndexBlockController extends Core_Controller_Block {

  function init() {}

  function getSiteEmailManagerAction(){}

  function mapAction(){
    $config = $this->_module->getConfig('core', 'map');

    $coordinates = $this->getParam('coordinates');
    $zoom       = $this->getParam('zoom');
    $key        = $this->getParam('key');
    $url        = $this->getParam('url');
    $width      = $this->getParam('width');
    $height     = $this->getParam('height');
    $language   = $this->getParam('language');
    $alt        = $this->getParam('alt');
    $picture    = $this->getParam('picture');

    $this->view->coordinates = empty($coordinates) ? $config['coordinates']    : $this->getParam('coordinates');
    $this->view->zoom       = empty($zoom)       ? $config['zoom']          : $this->getParam('zoom');
    $this->view->key        = empty($key)        ? $config['key']           : $this->getParam('key');
    $this->view->url        = empty($url)        ? $config['url']           : $this->getParam('url');
    $this->view->width      = empty($width)      ? $config['width']         : $this->getParam('width');
    $this->view->height     = empty($height)     ? $config['height']        : $this->getParam('height');
    $this->view->language   = empty($language)   ? App::locale()->getLang() : $this->getParam('language');
    $this->view->picture    = empty($picture)    ? $config['picture']       : $this->getParam('picture');
    $this->view->alt        = empty($alt)        ? App::xlat('MAP_description') : $this->getParam('alt');

    App::module('Core')->getModel('Libraries')->cBox_google_maps();
  }

  function socialNetworksAction(){
    $style = $this->getParam('style');
    $this->view->style = empty($style)?'big':$style;
  }

  function bannerRightAction(){
    // App::module('Core')->getModel('Libraries')->jquery_cycle('.vertical-banner');
  }

  function logoAction(){
  }

  function menuAction(){
  }

  function topMenuAction(){
    $padding = $this->getParam('padding');
    if( ! empty( $padding ) ){
      $this->view->set_padding = " pad-left-right-10 ";
    }
  }

  function promotionsAction(){
    $this->view->promotions = $this->_module->getModel('Promotions')->latest();
  }

  function businessPromotionsAction(){
    $this->view->promotions = $this->_module->getModel('Promotions')->business( $this->getParam('business') );
  }

  /* @todo: Mover esto al bloque CITIES */
  function townsAction(){
    $this->view->towns = $this->_module->getModel('Cities')->menu();
  }

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

  /* TAGS */
  function cloudAction(){
    $this->view->direction = $this->getParam('direction');
  }

  function cloudTagAction(){
    App::module('Core')->getModel('Libraries')->aside_tags('.cloud-tag div.panel-body a');
    $this->view->chars = $this->getParam('chars');
  }

}