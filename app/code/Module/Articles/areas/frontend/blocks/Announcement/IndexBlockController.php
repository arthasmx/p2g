<?php
require_once 'Core/Controller/Block.php';
class Articles_Announcement_IndexBlockController extends Core_Controller_Block {

  function init() {}

  function carouselPromoteAction(){
    $this->view->promote = $this->_module->getModel('Article')->latest( App::xlat('anuncios') );
    App::module('Core')->getModel('Libraries')->twitter_bootstrap_slider_autoplay('#announcement-carousel');
  }

  function carouselPromoteDescribedAction(){
    $this->view->promote = $this->_module->getModel('Article')->latest( App::xlat('anuncios') );
    App::module('Core')->getModel('Libraries')->twitter_bootstrap_slider_autoplay('#announcement-described-carousel');
  }

}