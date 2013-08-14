<?php
require_once 'Module/Addons/Controller/Action/Frontend.php';
class Addons_CommentsController extends Module_Addons_Controller_Action_Frontend   {

  function preDispatch(){
    $this->designManager()->setCurrentLayout('ajax');
  }

  function commentAction(){
    $this->_comment();
  }

  function replyAction(){
    $this->_comment('reply');
  }

  private function _comment($action='comment'){
    $request = $this->getRequest();
    $form    = ($action=='comment') ?
                 $this->_module->getModel('Forms/Comment')->get( $request->getParam('reference'),$request->getParam('type') )
               :
                 $this->_module->getModel('Forms/Reply')->get( $request->getParam('parent'),$request->getParam('child'),$request->getParam('type'), $request->getParam('reference') );

    if ( $this->getRequest()->isPost() ){

       require_once('Xplora/Captcha.php');
      $captcha = new Xplora_Captcha();
      if ( ! $captcha->validate(@$_POST['captcha']) ) {
        $form->getElement('captcha')->getValidator('Custom')->addError("captchaWrongCode",App::xlat("ERROR_bad_captcha"));
      }

      if( $form->isValid($_POST) ) {

        $comment = $this->_module->getModel('Cud/Comments');
        $this->view->message_posted = ($action =='comment') ?
          $comment->comment( $request->getParam('name'),$request->getParam('email'),$request->getParam('comment'),$request->getParam('reference'),$request->getParam('type') )
        :
          $comment->reply( $request->getParam('name'),$request->getParam('email'),$request->getParam('comment'),$request->getParam('reference'),$request->getParam('type'),$request->getParam('parent'),$request->getParam('child') );

        $core                       = App::module('Core');
        $recent_comment             = $this->_module->getModel('Comments')->get_comment( $this->view->message_posted );

        $to_json_comment['comment'] = $recent_comment['comment'];
        $to_json_comment['created'] = App::xlat('COMMENTS_on') . $core->getModel('Dates')->toDate(6,$recent_comment['created']) . App::xlat('COMMENTS_at') . $core->getModel('Dates')->toDate(9,$recent_comment['created']);
        $to_json_comment['level']   = substr_count($recent_comment['child_id'], '.') + 1;
        $to_json_comment['pending'] = App::xlat('COMMENTS_pending');

        echo $core->getModel('Json')->encode($to_json_comment);
        exit;

      }else{
        $form->populate($_POST);
      }
    }

    $this->view->form = $form;
    $this->_helper->getHelper('ViewRenderer')->setScriptAction( "comment" );
  }

}