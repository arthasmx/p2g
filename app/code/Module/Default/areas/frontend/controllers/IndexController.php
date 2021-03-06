<?php
require_once 'Module/Default/Controller/Action/Frontend.php';

class IndexController extends Module_Default_Controller_Action_Frontend {

  function preDispatch(){}

  function indexAction(){
    // $this->designManager()->setCurrentLayout('intro');
  }

  function aboutUsAction(){
    $this->view->article         = App::module('Articles')->getModel('Article')->read_full_article( $this->getRequest()->getParam('action'),true,true );
  }

  function contactUsAction(){
    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->contact();
    $libraries->block_ui();
    $libraries->js_shared_methods();

    $this->view->current_menu    = $this->getRequest()->getParam('action');
    $this->view->form            = $this->_module->getModel('Forms/Contact')->get();
  }

  function captchaContactRefreshAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $form    = $this->_module->getModel('Forms/Contact')->get();
    $captcha = $form->getElement('captcha')->getCaptcha();
    $data    = array();

    $data['id']  = $captcha->generate();
    $data['src'] = $captcha->getImgUrl() .
    $captcha->getId() .
    $captcha->getSuffix();

    $this->_helper->json($data);
    exit;
  }

  function contactAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $request = $this->getRequest();
    $form    = $this->_module->getModel('Forms/Contact')->get();

    if ( $request->isPost() ){

      if( $form->isValid($_POST) ){
        App::events()->dispatch('module_default_contacto',array("to"=>App::module('Email')->getConfig('core','frontend_contact'), "comment"=>$request->getParam('comments'), "name"=>$request->getParam('name'), "email"=>$request->getParam('email')));
        $answer = date('Y');
      }else{
        $answer = App::module('Core')->getModel('Form')->get_json_error_fields($form);
      }
    }
    echo $answer;
    exit;
  }

  function registerBusinessAction(){
    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->register();
    $libraries->block_ui();
    $libraries->js_shared_methods();

    $this->view->form = $this->_module->getModel('Forms/Register')->get();
  }

  function registerAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $request = $this->getRequest();
    $form    = $this->_module->getModel('Forms/Register')->get();

    if ( $request->isPost() ){

      if( $form->isValid($_POST) ){
        App::events()->dispatch('module_default_business_register',array("to"=>App::module('Email')->getConfig('core','frontend_contact'), "comment"=>$request->getParam('comments'), "business"=>$request->getParam('business'), "name"=>$request->getParam('name'), "email"=>$request->getParam('email')));
        $answer = date('Y');
      }else{
        $answer = App::module('Core')->getModel('Form')->get_json_error_fields($form);
      }
    }
    echo $answer;
    exit;
  }

  function captchaRegisterRefreshAction(){
    $this->captchaContactRefreshAction();
  }

  function phoneSosAction(){
  
  }
  








  function calendarAction(){
//    $this->view->article         = App::module('Articles')->getModel('Article')->read_full_article( $this->getRequest()->getParam('action'),true,true );
    $this->view->pageBreadcrumbs = array('title'=> App::xlat('breadcrumb_activities'), 'icon'=>'icon-calendar' );
  }

  function joinUsAction(){
//    $this->view->article         = App::module('Articles')->getModel('Article')->read_full_article( $this->getRequest()->getParam('action'),true,true );
    $this->view->pageBreadcrumbs = array('title'=> App::xlat('breadcrumb_join'), 'icon'=>'icon-fire' );
  }




  function flexarAction(){
    $this->designManager()->setCurrentLayout('flexar');

    $request = $this->getRequest();
    $form    = $this->_module->getModel('Forms/Flexar')->get();
    if ( $request->isPost() ){

      require_once('Xplora/Captcha.php');
      $captcha = new Xplora_Captcha();
      if ( ! $captcha->validate(@$_POST['captcha']) ) {
        $form->getElement('captcha')->getValidator('Custom')->addError("captchaWrongCode",App::xlat("ERROR_bad_captcha"));
      }

      if($form->isValid($_POST) ) {
        App::events()->dispatch('module_default_flexar',array("to"=>App::module('Email')->getConfig('core','remitente_carboncopy_rcpt'), "comment"=>$request->getParam('comment'), "name"=>$request->getParam('name'), "email"=>$request->getParam('email')));
        $this->view->message_sent = true;
        $form->reset();
      }else{
        $form->populate($_POST);
      }

    }
    $this->view->form = $form;
  }

  function siteSectionAction(){
    $this->view->current_menu    = $this->getRequest()->getParam('action');
    $this->view->article         = App::module('Articles')->getModel('Article')->read_full_article( $this->getRequest()->getParam('action'),true,true );
    $this->view->pageBreadcrumbs = array('title'=> App::xlat('breadcrumb_about_us'), 'icon'=>'icon-group' );
  }

/* MAIN CATEGORIES */

  function tagAction(){
    $this->view->tags   = App::module('Addons')->getModel('Categories')->get_children_by_seo( $this->getRequest()->getParam('action') );
    $this->view->parent = $this->getRequest()->getParam('action');
  }
  function hotelesAction(){
    if( ! $this->getRequest()->getParam('tag') ){
      $this->tagAction();
    }else{
      $this->view->tag_list = App::module('Articles')->getModel('Business')->get_by_tag( $this->getRequest()->getParam('tag') );
    }
    $this->_helper->getHelper('ViewRenderer')->setScriptAction( "tag-big-image" );
  }
  function cafesYRestaurantesAction(){
    $this->hotelesAction();
  }
  function baresAction(){
    $this->hotelesAction();
  }

  
  
/* DOWNLOADS */

  function downloadArticleAddonAction(){
    $params  = $this->getRequest()->getParams();
    $fileSys = App::module('Core')->getModel('Filesystem');
    if( empty($params['date']) || empty($params['id']) || empty($params['type']) || empty($params['reference']) ){
      App::module('Core')->exception( App::xlat('EXC_article_wasnt_found_') );
    }

    $folder = App::module('Articles')->getConfig('core','folders'); 
    $file   = $folder['articles'] .DS . App::module('Core')->getModel('Dates')->toDate(10, date("Y-m-d", $params['date'])) .DS. $params['id'] .DS. $params['type'] .DS. $params['reference'];

    // Download!
    $fileSys->set_file($file)->force_to_download();
    exit;
  }

  function audioDownloadAction(){
    $file_path = $this->getRequest()->getParam('folder').DS.$this->getRequest()->getParam('year').DS.$this->getRequest()->getParam('month').DS.$this->getRequest()->getParam('file'); 
    App::module('Core')->getModel('Filesystem')->set_file($file_path)->force_to_download();
    exit;
  }

  protected function get_breadcrumbs( $breadcrumb = null ){
    if( empty($breadcrumb)){
      return null;
    }

    return array( array('title'=> App::xlat($breadcrumb) ) );
  }

  function __call($function, $args){
    $desired_action          = $this->getRequest()->getActionName();
    $actions_for_this_locale = App::module('Core')->getModel('Actions')->get_translated_actions( $desired_action );

    if ( $actions_for_this_locale && array_key_exists($desired_action, $actions_for_this_locale) ){
      $action = $actions_for_this_locale[$desired_action]['action'];
      $view   = $actions_for_this_locale[$desired_action]['view'];
      call_user_func( array($this, "{$action}Action") );
      $this->_helper->getHelper('ViewRenderer')->setScriptAction($view);
    }else{
      $this->_module->exception("Action Given Does Not Exist");
    }
  }

}