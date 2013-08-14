<?php
require_once 'Module/Addons/Controller/Action/Frontend.php';
class Addons_AjaxController extends Module_Addons_Controller_Action_Frontend   {

  function preDispatch(){
    $this->designManager()->setCurrentLayout('ajax');
  }

  function articleRateAction(){

    // $this->view->rating = $this->_module->getModel('Rating')->get_rate( $this->getRequest()->getParam('id') );
    echo $this->_module->getModel('Cud/Rating')->rate_article( $this->getRequest()->getParam('id'), $this->getRequest()->getParam('rate') );
    exit;
  }

  function pollVoteAction(){
    $id = $this->getRequest()->getParam('id');

    $was_vote_saved = $this->_module->getModel('Cud/Poll')->poll( $id, $this->getRequest()->getParam('vote'));
    echo $this->_module->getModel('Poll')->get_results_chart($id, $was_vote_saved);
    exit;
  }

}