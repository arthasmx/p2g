<?php
require_once 'Core/Controller/Block.php';
class Articles_Events_IndexBlockController extends Core_Controller_Block {

  function init() {}

  function eventsAction(){
    $this->view->today    = $this->_module->getModel('Event')->today();
    $this->view->tomorrow = $this->_module->getModel('Event')->tomorrow();

    $days               = cal_days_in_month(CAL_GREGORIAN, date('n'), date('Y') );
    $this->view->month  = $this->_module->getModel('Event')->month( date('Y-m-').'01', date('Y-m-').$days );
    $this->view->mobile = $this->getParam('mobile');
  }

  function eventsAsideAction(){
    $this->view->today    = $this->_module->getModel('Event')->today( true );
    $this->view->tomorrow = $this->_module->getModel('Event')->tomorrow( true );

    $days               = cal_days_in_month(CAL_GREGORIAN, date('n'), date('Y') );
    $this->view->month  = $this->_module->getModel('Event')->past(true);
    $this->view->mobile = $this->getParam('mobile');
  }
  
  
  
  
  
  
  
  
  /*
  
  function latestAction(){
    // $this->view->latest = $this->_module->getModel('Article')->latest( 'eventos' );
    // App::module('Core')->getModel('Libraries')->twitter_bootstrap_slider_autoplay('#latest-events-carousel');

    $this->view->latest = $this->_module->getModel('Article')->latest( 'eventos' );
  }

  function promoteAction(){
    $this->view->promote  = $this->_module->getModel('Article')->get_article_basic_data( $this->getParam('seo') );
  }

  function promoteDescribedAction(){
    $this->view->promote  = $this->_module->getModel('Article')->get_article_basic_data( $this->getParam('seo') );
  }





  function socialAction(){
//    $this->view->social = $this->_module->getModel('Event')->social();
  }



  function carouselPromoteAction(){
    $this->view->promote = $this->_module->getModel('Article')->latest( App::xlat('eventos') );
    App::module('Core')->getModel('Libraries')->twitter_bootstrap_slider_autoplay('#events-carousel');
  }

  function carouselPromoteDescribedAction(){
    $this->view->promote = $this->_module->getModel('Article')->latest( App::xlat('eventos') );
    App::module('Core')->getModel('Libraries')->twitter_bootstrap_slider_autoplay('#events-described-carousel');
  }

  */
  
}