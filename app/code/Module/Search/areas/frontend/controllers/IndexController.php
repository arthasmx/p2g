<?php
require_once 'Module/Search/Controller/Action/Frontend.php';
class Search_IndexController extends Module_Search_Controller_Action_Frontend {

  function preDispatch(){}

  /**
   * This method process all search petitions to redirect to its corresponding action.
   * Then, executes the search query and shows results.
   *
   * * finds whether Filter or Sort were solicited and set'em to URL
   *   - there's a hack for this: method dont_apply_default_values() validates if Filter or Sort will be set to URL
   *
   * You can choose between normal/ajax search (inside the Model)
   */
  function searchAction(){
    if (  $this->getRequest()->isPost() ){
      $keyword = App::module('Core')->getModel('Parser')->string_to_seo( $this->getRequest()->getParam('search') );
      App::module('Core')->getModel('Db/Datafilter')->set_filters( $this->getRequest()->getParams() );
      Header("Location: " . App::base( $this->get_route_by_type() . App::xlat('route_search') . $keyword ) );
      exit;
    }
    App::module('Core')->exception( App::xlat('EXC_you_are_not_allowed_to_access_this_section') . '<br />Launched at method searchAction, file Module Search, Controller Index' );
  }

  function bibleSearchAction(){
    $this->view->searched_string   = str_replace('-', ' ', $this->getRequest()->getParam('keyword') );
    $this->view->search            = App::module('Addons')->getModel("Bible")
                                                          ->search( $this->view->searched_string , $this->getRequest()->getParam( App::xlat('route_paginator_page') ) );

    App::module('Core')->getModel('Libraries')->bible_on_resize();
    $this->view->set_bible_options_position = true;
    $this->view->pageBreadcrumbs            = $this->get_breadcrumbs( $this->getRequest()->getParam('action'), App::xlat('BIBLE_search_result_topic') . '"<em>' . $this->view->searched_string . '</em>"'  );
  }

  function ajaxBibleSearchAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->view->search = App::module('Addons')->getModel("Bible")->ajax_search_paginate( $this->getRequest()->getParams() );
  }


  /*
   * This method is required to allow the site to have/handle several search sections. Just add:
   * - Route
   * - Action to process the search
   */
  private function get_route_by_type(){
    switch( $this->getRequest()->getParam('type') ){
      case 'biblia':
          return App::xlat('route_bible');
        break;
      default:
          App::module('Core')->exception( App::xlat('EXC_you_are_not_allowed_to_access_this_section') . '<br />Launched at method get_route_by_type, file Module Search, Controller Index' );
        break;
    }
  }

  protected function get_breadcrumbs( $action = null, $name=null ){
    $route        = App::xlat('route_bible');
    $trimed_route = rtrim($route, '/');

    switch ( $action ){
      case 'bible-search':
        return array(
          array('title'=> App::xlat('BREADCRUM_bible')        , 'url' => App::base( $trimed_route ) ),
          array('title'=> $name )
        );
        break;

      default:
        return null;
        break;
    }

  }

}