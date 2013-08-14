<?php

// Va a extender a Frontend_Admin para incluir todas las comprobaciones del predispatch
require_once 'Module/Blog/Controller/Action/Frontend/Admin.php';

class Blog_AdminController extends Module_Blog_Controller_Action_Frontend_Admin   {

	function preDispatch() {
		parent::preDispatch(); // Llama primero al de Controller_Action_Frontend_Admin para control de acceso y demás
	}

	/**
	* Mostramos el listado de Caseos de exito
	*
	*/
	function indexAction() {
		if (!$this->_request->getParam('export')) {
			// Añadimos al goback la url actual, si ya existe una url igual, se va promocionar al primer lugar (Al cargar la url para regresar, se retornará esta)
				App::module('Core')->getResourceSingleton('Goback')->add("account/admin/blog/index"); // Añade la url actual (el parametro indicado es el nombre de la ruta, en caso de querer indicar la url y que no sea la actual, puede enviarse como segundo parámetro)
		}

		$this->view->pageTitle=App::xlat('Gestión de casos de éxito');

		// CSS
			App::header()->addLink(App::skin('/css/blocks/dataset.css'),array('rel'=>'stylesheet','type'=>'text/css'));


		// Ordenación de datos ================================================================================================
				require_once("Xplora/Datasorter.php");
				$datasorter=Xplora_Datasorter::factory()
								->setUrl(
									// El datasorter utilizará la url actual para sus links pero eliminando el parámetro de la página y el de la ordenación mismo.
									// Los parámetros de filtrado se mantendrán (para no quitar los filtros al cambiar la ordenación)
									App::url()->removeParams(
										array(
											'sort_f'=>$this->getRequest()->getParam('sort_f'),
											'sort_t'=>$this->getRequest()->getParam('sort_t'),
											'page'=>$this->getRequest()->getParam('page')
										)
									)
								);

			// Creamos los campos de ordenación
				$datasorter->createField( "date_created" , Xplora_Datasorter::SORT_DESC );

				$datasorter->createField( "id" )
							->setFieldName( "id", Xplora_Datasorter::SORT_DESC );
				$datasorter->createField( "dupdated1" )
							->setFieldName( "date_updated", Xplora_Datasorter::SORT_DESC );
				$datasorter->createField( "dpublish1" , Xplora_Datasorter::SORT_DESC ) // El segundo parámetro es la ordenación por defecto de éste campo, para las fechas lo ponemos Descendente por defecto
							->setFieldName( "date_publish" );

			// Establecemos ordenación por defecto
				$datasorter->setDefault( "date_created" )
							   ->setSort(
							   		(string)$this->getRequest()->getParam('sort_f'),
							   		(string)$this->getRequest()->getParam('sort_t')
							   	);
				// Pasamos el ordenador de datos a la vista
					$this->view->datasorter=$datasorter;
		// Fin Ordenación de datos ================================================================================================



		// Filtrado de datos ================================================================================================
					// Creación del objeto
						require_once("Xplora/Datafilter.php");
						$datafilter=Xplora_Datafilter::factory()
										->setUrl(
											// El datasorter utilizará la url actual para sus links pero eliminando el parámetro de la página
											// Los parametros de ordenación se mantienen.
											// Los parámetros de filtrado también se mantienen (El datafilter eliminará los parámetros de filtrado automáticamente)
											App::url()->removeParams(
												array(
													'page'=>$this->getRequest()->getParam('page')
												)
											)
										)
										->setTranslator(App::translate()->getFormTranslator()) // Usamos el mismo traductor que para los formularios
										->setLocale(App::locale()->zend()); // Especificamos el traductor
					// Creamos los campos de filtrado
						// ID
							$datafilter->createField( "id" , Xplora_Datafilter::TYP_TEXT )
									->setFieldname( "id" )
									->setAttribute( "size" , 10 );

						// Fecha de creación
							$datafilter->createField( "dcreated1" , Xplora_Datafilter::TYP_DATETIME )
									->setFieldname( "date_created" )
									->setFormat( App::locale()->getDateFormat('medium') )  // Asignamos formato de la fecha que utilizamos para que pueda convertirla a una fecha ISO
									->setCondition( Xplora_Datafilter::COND_LESSEQ );
							$datafilter->cloneField( "dcreated2" , "dcreated1" ) // Clonamos el campo created_1 (usa las mismas opciones pero modificamos la condición)
									->setCondition( Xplora_Datafilter::COND_MOREEQ );

						// Fecha de modificado
							$datafilter->createField( "dupdated1" , Xplora_Datafilter::TYP_DATETIME )
									->setFieldname( "date_updated" )
									->setFormat( App::locale()->getDateFormat('medium') )
									->setCondition( Xplora_Datafilter::COND_LESSEQ );
							$datafilter->cloneField( "dupdated2" , "dupdated1" )
									->setCondition( Xplora_Datafilter::COND_MOREEQ );

						// Fecha de publicacion
							$datafilter->createField( "dpublish1" , Xplora_Datafilter::TYP_DATETIME )
									->setFieldname( "date_publish" )
									->setFormat( App::locale()->getDateFormat('medium') )
									->setCondition( Xplora_Datafilter::COND_LESSEQ );
							$datafilter->cloneField( "dpublish2" , "dpublish1" )
									->setCondition( Xplora_Datafilter::COND_MOREEQ );

					// Establecemos el valor de los campos según los parámetros recibidos
						$datafilter->populate($this->_request->getParams());
					// Mensajes a aplicar en función de si existe un filtrado o no.
						if (!$datafilter->isValid()) App::module("Core")->getResourceSingleton('flashmsg')->error(App::xlat('Existe un error en alguna de las reglas, por lo que los filtros no han sido aplicados [ %squitar filtros%s ]',array("<a href='".$datafilter->getBaseUrl()."'>","</a>")));
					// Pasamos el filtro de datos a la vista
						$this->view->datafilter=$datafilter;
		// Fin Filtrado de datos ================================================================================================



		// Exportado de datos ================================================================================================
			require_once("Xplora/Dataexporter.php");
				$dataexporter=Xplora_Dataexporter::factory()
								->setUrl(
									// El datasorter utilizará la url actual para sus links pero eliminando el parámetro de la página y el de la ordenación mismo.
									// Los parámetros de filtrado se mantendrán (para no quitar los filtros al cambiar la ordenación)
									App::url()->removeParams(
										array(
											'sort_f'=>$this->getRequest()->getParam('sort_f'),
											'sort_t'=>$this->getRequest()->getParam('sort_t'),
											'page'=>$this->getRequest()->getParam('page')
										)
									)
								);
		// Fin Exportado de datos ================================================================================================



		// Inicializamos el recurso para la carga de los datos y enviamos las opciones de filtrado globales (no los datafilters)
			$blog=$this->_module->getResourceObject('Admin/Blog')
					->setDatasorter($datasorter)
					->setDatafilter($datafilter);

		if ($this->_request->getParam('export')) {

			// Exportado de datos ================================================================================================
				require_once("Xplora/Dataexporter.php");
				$dataexporter=Xplora_Dataexporter::factory( Xplora_Dataexporter::FORMAT_CSV );
				$dataexporter->setData( $blog->get() )
							 ->setFields(
							 	array(
							 		"id"				=> App::xlat("ID"),
							 		"date_created"		=> App::xlat("Creado"),
							 		"date_updated"		=> App::xlat("Actualizado"),
							 		"date_publish"		=> App::xlat("Publicación"),
							 		"image"				=> App::xlat("Imagen"),
							 	)
							 )
							 ->export();
			// Fin Exportado de datos ================================================================================================
		} else {

			// Paginado de datos ================================================================================================
				$this->view->blog=$blog->setPage((int)$this->_request->getParam('page'))
										 ->setnotpreview(true)
										 ->get();
			// Paginado de datos ================================================================================================

		}


		// Breadcrumbs ------------------------------------------------------------------------------------
			$this->view->pageBreadcrumbs[]=array('title'=>App::xlat('Gestión de casos de éxito'));

	}

	/**
	* Mostramos opcion para agregar
	*
	*/
	function createAction() {
		// Agregamos scripts para hacer ajaxuploader
//App::header()->addScript( App::url()->get('/juploader/jquery.js','js') );
//App::header()->addScript( App::url()->get('/juploader/ajaxfileupload.js','js') );


		// Cabecera
			$this->view->pageClass="page-account page-cms page-form";

		// CMS
			// Cargamos página a través de un modelo local Cms que se encarga de conectar con el módulo Cms
				$this->view->page=$this->_module->getModelSingleton('Cms')->setView($this->view)
																		  ->getPage('blog/create');
		// FORMULARIO
			$form=$this->_module->getModelObject('Admin/Forms/Create')->get();

			if ($this->getRequest()->isPost()) {
				
				$_POST['date_publish'] = App::locale()->fromDate($_POST['date_publish']);

				if ($form->isValid($_POST)) {

					// Guardamos los cambios
						
//echo '<pre>'; print_r($_POST); echo '</pre>';
//exit;
						if( $this->_module->getModelSingleton('Blog')->create($_POST)	) {
							App::module('Core')->getResourceSingleton('Flashmsg')->success(App::xlat('Nuevo caso de éxito agregado correctamente'));
							Header("Location:" . App::module('Core')->getResourceSingleton('Goback')->get());
							exit;
						}else {
							App::module('Core')->getResourceSingleton('Flashmsg')->error(App::xlat('Id duplicado'));
						}
					/*
					 [logo] => Array
					        (
					            [name] => perfil.jpg
					            [type] => image/jpeg
					            [tmp_name] => /tmp/phpGSPxYu
					            [error] => 0
					            [size] => 20547
					        )
					*/
					// Los propios modulos de user y acl generan los errores si existe alguno.
				}

				$_POST['date_publish'] = App::locale()->toDate($_POST['date_publish']); // Tenemos que ponerla otra vez en el formato del locale
				
				$form->populate($_POST);
			}


		// En lugar de asignar el formulario a la vista, lo reemplazamos por el tag acordado en la página del CMS
			$this->view->page['content']=str_replace('/*FORM*/',(string)$form,$this->view->page['content']);

		// HACEMOS CREER AL REQUEST QUE ESTAMOS EN EL MÓDULO CMS PARA QUE UTILIZE SU VISTA.
			$this->getRequest()->setModuleName('Cms');
			$this->getRequest()->setControllerName('Page');
			$this->getRequest()->setActionName('Show');


		// Breadcrumbs ------------------------------------------------------------------------------------
			$this->view->pageBreadcrumbs[]=array('title'=>App::xlat('Gestión de casos de éxito'),'url'=> App::base("/account/admin/blog/index"));
			$this->view->pageBreadcrumbs[]=array('title'=>App::xlat('Nuevo caso de exito'));

	}

	/**
	* Editamos un caso de exito
	*
	*/
	function editAction() {
		// Cabecera
			$this->view->pageClass="page-account page-cms page-form";

		// CMS
			// Cargamos página a través de un modelo local Cms que se encarga de conectar con el módulo Cms
				$this->view->page=$this->_module->getModelSingleton('Cms')->setView($this->view)
																		  ->getPage('blog/edit');
		// FORMULARIO
			$form=$this->_module->getModelObject('Admin/Forms/Edit')->get( $this->getRequest()->getParam('case') );

			if ($this->getRequest()->isPost()) {

				if ($form->isValid($_POST)) {
					// Guardamos los cambios
						$_POST['date_publish'] = App::locale()->fromDate($_POST['date_publish']);
						if( $this->_module->getModelSingleton('Blog')->update($_POST)	) {
							App::module('Core')->getResourceSingleton('Flashmsg')->success(App::xlat('El caso ha sido modificado correctamente.'));
							Header("Location:" . App::module('Core')->getResourceSingleton('Goback')->get());
							exit;
						}
					/*
					 [logo] => Array
					        (
					            [name] => perfil.jpg
					            [type] => image/jpeg
					            [tmp_name] => /tmp/phpGSPxYu
					            [error] => 0
					            [size] => 20547
					        )
					*/


					// Los propios modulos de user y acl generan los errores si existe alguno.

				}

				$form->populate($_POST);
			}


		// En lugar de asignar el formulario a la vista, lo reemplazamos por el tag acordado en la página del CMS
			$this->view->page['content']=str_replace('/*FORM*/',(string)$form,$this->view->page['content']);

		// HACEMOS CREER AL REQUEST QUE ESTAMOS EN EL MÓDULO CMS PARA QUE UTILIZE SU VISTA.
			$this->getRequest()->setModuleName('Cms');
			$this->getRequest()->setControllerName('Page');
			$this->getRequest()->setActionName('Show');


		// Breadcrumbs ------------------------------------------------------------------------------------
			$this->view->pageBreadcrumbs[]=array('title'=>App::xlat('Gestión de casos de éxito'),'url'=> App::base("/account/admin/blog/index"));
			$this->view->pageBreadcrumbs[]=array('title'=>App::xlat('Editar caso de exito'));

	}

	/**
	* Eliminar caso
	*
	*/
	function deleteAction() {
		// Cabecera
			$this->view->pageClass="page-account page-cms page-form";

		// CMS
			// Cargamos página a través de un modelo local Cms que se encarga de conectar con el módulo Cms
				$this->view->page=$this->_module->getModelSingleton('Cms')->setView($this->view)
																		  ->getPage('blog/delete');
		// FORMULARIO
			$form=$this->_module->getModelObject('Admin/Forms/Delete')->get( $this->getRequest()->getParam('case') );

			if ($this->getRequest()->isPost()) {

				//if ($form->isValid($_POST)) {
				// Proseguimos con la eliminacion
					if( $this->_module->getModelSingleton('Blog')->delete($_POST)	) {
						App::module('Core')->getResourceSingleton('Flashmsg')->success(App::xlat('La información de su caso de exito ha sido eliminada'));
						Header("Location:" . App::module('Core')->getResourceSingleton('Goback')->get());
						exit;
					}
				//}

				App::module('Core')->getResourceSingleton('Flashmsg')->error(App::xlat('No se ha podido eliminar el caso'));
				$form->populate($_POST);
			}

		// En lugar de asignar el formulario a la vista, lo reemplazamos por el tag acordado en la página del CMS
			$this->view->page['content']=str_replace('/*SCASE*/',(string)$form,$this->view->page['content']);

		// HACEMOS CREER AL REQUEST QUE ESTAMOS EN EL MÓDULO CMS PARA QUE UTILIZE SU VISTA.
			$this->getRequest()->setModuleName('Cms');
			$this->getRequest()->setControllerName('Page');
			$this->getRequest()->setActionName('Show');


		// Breadcrumbs ------------------------------------------------------------------------------------
			$this->view->pageBreadcrumbs[]=array('title'=>App::xlat('Gestión de noticias'),'url'=> App::base("/account/admin/news/index"));
			$this->view->pageBreadcrumbs[]=array('title'=>App::xlat('Eliminar noticia'));

	}
}