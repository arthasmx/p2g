<?php
require_once 'Core/Controller/Block.php';
class Addons_Bible_IndexBlockController extends Core_Controller_Block {

  function init() {}

  function searchAction(){
    App::module('Core')->getModel('Libraries')->bible_search();

    $this->view->form     = $this->_module->getModel('Forms/Bible')->get( $this->getParam("form_type"), $this->getParam("reset") );
    $this->view->location = $this->getParam("location");
    $this->setScriptAction( "search-intro" );
  }

  function phraseAction(){
    $this->view->phrase = $this->_module->getModel("Bible")->get_phrase();
  }
  function phraseContentAction(){
    $this->view->phrase = $this->_module->getModel("Bible")->get_phrase();
  }

  function chaptersPaginatorAction(){
    $this->view->chapters        = $this->_module->getModel('Bible')->get_chapters_for_pagination( $this->getParam('seo'), $this->getParam('chapter') );
    $this->view->book_seo        = $this->getParam('seo');
    $this->view->current_chapter = $this->getParam('chapter');
    $this->view->chapters_total  = $this->getParam('chapters_total');
  }

  function versesPaginatorAction(){
    $this->view->verses          = $this->_module->getModel('Bible')->get_verses_for_pagination( $this->getParam('book'), $this->getParam('chapter'), $this->getParam('verse') );
    $this->view->book            = $this->getParam('book');
    $this->view->current_chapter = $this->getParam('chapter');
    $this->view->current_verse   = $this->getParam('verse');
    $this->view->verses_total    = $this->getParam('verses_total');
  }

  function optionsAction(){
    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->bible_search();
    $this->view->section         = $this->getParam("section");
    $this->view->current_book_id = $this->getParam('book_id');

    $this->view->form    = $this->_module->getModel('Forms/Bible')->get("advanced", $this->getParam("reset") );
    $this->view->books   = $this->_module->getModel("Bible")->get_books();
    $this->view->details = $this->getParam("details");

    if( $this->view->section=="chapter" || $this->view->section=="verse"){
      $this->view->chapters = ( ! empty($this->view->details['seo']) ) ? $this->_module->getModel('Bible')->get_chapters( $this->view->details['seo'] ) : null;
    }

    $this->setScriptAction( 'book-options' );
  }

}