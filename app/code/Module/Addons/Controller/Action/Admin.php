<?php
require_once 'Module/Addons/Controller/Action.php';
class Module_Addons_Controller_Action_Admin extends Module_Addons_Controller_Action  {

  protected function _construct(){
		  parent::_construct();
	 }

  function init(){
    App::module("Acl")->getModel('acl')->is_user_logged();
  }

}