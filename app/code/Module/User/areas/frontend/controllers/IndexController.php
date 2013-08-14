<?php
require_once 'Module/User/Controller/Action/Admin.php';

class IndexController extends Module_User_Controller_Action_Admin {

	function preDispatch(){


		$this->designManager()->setCurrentLayout('admin');
		$this->view->locale=App::locale()->getLang();
	}

	function indexAction() {
		echo "<pre>"; print_r('Its here'); echo "</prex>";
		exit;
	}

}