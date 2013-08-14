<?php
require_once 'Module/Default/Controller/Action/Frontend.php';

class BibleController extends Module_Default_Controller_Action_Frontend {

  function preDispatch(){
    if( $this->getRequest()->getParam('action') !=='index' ){
      App::module('Core')->getModel('Libraries')->bible_on_resize();
      $this->view->set_bible_options_position = true;
    }
  }

  function indexAction(){
    App::module('Core')->getModel('Libraries')->bible_search();
    $this->view->books           = App::module('Addons')->getModel('Bible')->get_books();
    $this->view->pageBreadcrumbs = $this->get_breadcrumbs( $this->getRequest()->getParam('action') );
  }

  function bookAction(){
    $this->view->book            = App::module('Addons')->getModel('Bible')->get_book_details( $this->getRequest()->getParam('book') );
    $this->view->pageBreadcrumbs = $this->get_breadcrumbs( $this->getRequest()->getParam('action'), $this->view->book['book'] );
  }

  function chapterAction(){
    $book_seo              = $this->getRequest()->getParam('book');
    $this->view->chapter   = $this->getRequest()->getParam('chapter');
    $this->view->verses    = App::module('Addons')->getModel('Bible')->get_verses( $book_seo, $this->view->chapter );
    $this->view->book      = App::module('Addons')->getModel('Bible')->get_book_details( $book_seo );

    $this->view->pageBreadcrumbs = $this->get_breadcrumbs( $this->getRequest()->getParam('action'), $this->view->book['book'], $book_seo, $this->view->chapter );
  }

  function verseAction(){
    $book_seo          = $this->getRequest()->getParam('book');
    $chapter           = $this->getRequest()->getParam('chapter');
    $verse             = $this->getRequest()->getParam('verse');

    $this->view->verse = App::module('Addons')->getModel('Bible')->get_verse( $book_seo, $chapter, $verse );
    $this->view->book  = App::module('Addons')->getModel('Bible')->get_book_details( $book_seo );

    $this->view->pageBreadcrumbs = $this->get_breadcrumbs( $this->getRequest()->getParam('action'), $this->view->book['book'], $book_seo, $chapter, $verse );
  }

  function loadBooksAction(){
    $this->designManager()->setCurrentLayout('ajax');
    echo App::module('Addons')->getModel("Bible")->get_books_for_dropbox_in_json( $this->getRequest()->getParam("testament") );
    exit;
  }

  protected function get_breadcrumbs( $action = null, $book_name=null, $book=null, $chapter=null, $verse=null ){

    $route        = App::xlat('route_bible');
    $trimed_route = rtrim($route, '/');

    switch ( $action ){
      case 'index':
              return array(
                array('title'=>App::xlat('BREADCRUM_bible'))
              );
              break;
      case 'book':
              return array(
                array('title'=> App::xlat('BREADCRUM_bible')        , 'url' => App::base( $trimed_route ) ),
                array('title'=> $book_name )
              );
              break;
      case 'chapter':
              return array(
                array('title'=> App::xlat('BREADCRUM_bible')        , 'url' => App::base( $trimed_route ) ),
                array('title'=> $book_name                          , 'url' => App::base( $route . $book ) ),
                array('title'=> App::xlat('BIBLE_chapter') . ' ' . $chapter )
              );
              break;
      case 'verse':
              return array(
                array('title'=> App::xlat('BREADCRUM_bible')        , 'url' => App::base( $trimed_route ) ),
                array('title'=> $book_name                          , 'url' => App::base( $route . $book ) ),
                array('title'=> App::xlat('BIBLE_chapter') . ' ' . $chapter , 'url' => App::base( $route . $book . '/' . $chapter ) ),
                array('title'=> App::xlat('BIBLE_verse') . ' ' . $verse )
              );
              break;
      default:
              return null;
              break;
    }

  }

}