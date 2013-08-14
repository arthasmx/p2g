<?php
require_once 'Module/Blog/Controller/Action/Blog.php';

class IndexController extends Module_Blog_Controller_Action_Blog   {

	function preDispatch() {
    	$this->view->pageTitle=App::xlat( $this->_module->getConfig('core','site_title_text') );
			$this->designManager()->setCurrentLayout('page/front');

		// Asignamos el city_id para poder obtener las galerias en promocion de la ciudad en cuestion
			$this->view->city = '';

		// Cargamos los CSS correspondientes en caso de que exista la ciudad y la entidad
			App::header()->addLink(App::skin('/css/pages/rate.css'),array(
				"rel"=>"stylesheet",
				"type"=>"text/css",
				"media"=>"all",
			));

	}

	function indexAction() {
		// Sacamos los articulos del blog
			$this->view->blog=$this->_module->getModelSingleton('Blog')->getAllByDate($this->getRequest()->getParam('year'),$this->getRequest()->getParam('month'));
	}

	function readAction() {
		$this->view->blog=$this->_module->getModelSingleton('blog')->read($this->getRequest()->getParam('id'));

		if (!isset($this->view->blog[0])) {
			// No existe, redireccionamos al indice
				App::module("Core")->getResourceSingleton('flashmsg')->error(App::xlat('El articulo con id <b>%s</b> no existe. Utiliza nuestros links por favor.',$this->getRequest()->getParam('id')));
				$this->__redirect("/blog","base");
		} else {
			// Breadcrumbs
				/*$this->view->pageBreadcrumbs=array(
					array('title'=>App::xlat('Inicio'),'url'=>App::base($this->view->city)),
					array('title'=>App::xlat('Blog'),'url'=>App::base($this->view->city.'/blog')),
					array('title'=>App::xlat('Leyendo')),
				);*/
			// Agregamos el JS que efectua el llamado al star rating
				//App::header()->addScript( App::url()->get('/xse_form.js','jslib') );
		}
	}

}