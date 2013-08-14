<?php

class Module_Blog_Repository_Model_Cms extends Core_Model_Repository_Model {

	protected $view=null;

	public function setView(&$view) {
		$this->view=$view;
		return $this;
	}

	/**
	 * Wrapper que configura el cms para éste módulo y carga la página a través del resource Cms del módulo Cms
	 *
	 * @param string $pageId
	 * @return string
	 */
	public function getPage($pageId) {
		App::header()->addLink(App::skin('/css/pages/cms.css'),array('rel'=>'stylesheet','type'=>'text/css'));

		// Comprobamos si está habiltado el módulo Cms, el cual utilizaremos en éste controlador.
			if (!App::module('Cms')) {
				require_once "Module/Account/Exception.php";
				throw new Module_Account_Exception("El módulo Cms no se encuentra habilitado");
			}

		// Obtenemos acceso al pageManager (Administrador de páginas), en este caso, serán páginas almacenadas en archivos xml, pero pueden crearse otros pageManagers (bases de datos, html, etc)
			$pageManager=App::module('Cms')->getResourceSingleton('Pagemanager/Xml')
									->setBackend(
										App::module('Cms')->getResourceSingleton('Pagemanager/Xml/Backend/File')
														->setPath(BP."/files/Module/Cms/account") // Indicamos directorio donde se encuentran los xml de las páginas
									);

		// Intentamos cargar la página
			$page=App::module('Cms')->getResourceSingleton('Cms')
									->setPageManager($pageManager)
									->setView($this->view)
									->getPage($pageId);

		return $page;
	}



}