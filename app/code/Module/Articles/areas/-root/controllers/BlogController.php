<?php

require_once 'Module/Blog/Controller/Action/Admin.php';

class Blog_BlogController extends Module_Blog_Controller_Action_Admin   {

	function preDispatch() {
		// Indicamos que los usuarios deben tener privilegios de administrador
			App::module("Acl")->getModelSingleton('acl')->requirePrivileges('admin');
        // Definios el layout a utilizar
		    $this->designManager()->setCurrentLayout('page/root');
	}

	/**
	 * Metodo para no repetir codigo
	 *
	 * @param string $path | El path del XML en "files\Module\Cms\account\es\admin\ofertas"
	 * @return void
	 */
	protected function __common($path=false, $replace=false,$datos=false){
		if(!$path) return false;

		// Cabecera
			$this->view->pageClass="page-account page-cms page-form";

		// CMS
			// Cargamos página a través de un modelo local Cms que se encarga de conectar con el módulo Cms
				$this->view->page=App::module('Account')->getModelSingleton('Cms')->setView($this->view)
																				  ->getPage($path);

		// HACEMOS CREER AL REQUEST QUE ESTAMOS EN EL MÓDULO CMS PARA QUE UTILIZE SU VISTA.
			$this->getRequest()->setModuleName('Cms');
			$this->getRequest()->setControllerName('Page');
			$this->getRequest()->setActionName('Show');

        // Remplaza un juego de caracteres y lo regresa
            if($replace && $datos){
               $this->view->page['content']=str_replace($replace,(string)$datos,$this->view->page['content']);
            }
	}

    /**
    * @desc Listado de articulos. Editar, Eliminar se hacen desde esta seccion
    */
    function indexAction() {
        if (!$this->_request->getParam('export')) {
            // Añadimos al goback la url actual, si ya existe una url igual, se va promocionar al primer lugar (Al cargar la url para regresar, se retornará esta)
                App::module('Core')->getResourceSingleton('Goback')->add("account/admin/blog/index"); // Añade la url actual (el parametro indicado es el nombre de la ruta, en caso de querer indicar la url y que no sea la actual, puede enviarse como segundo parámetro)
        }

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
                                     "id"                => App::xlat("ID"),
                                     "date_created"        => App::xlat("Creado"),
                                     "date_updated"        => App::xlat("Actualizado"),
                                     "date_publish"        => App::xlat("Publicación"),
                                     "image"                => App::xlat("Imagen"),
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
            $this->view->pageBreadcrumbs=array(
                array('title'=>App::xlat('Inicio'),'url'=>App::base('/')),
                array('title'=>App::xlat('Listado de articulos')),
            );

        //$this->__common('admin/blog/index');

    }

    /**
    * Mostramos opcion para agregar
    *
    */
    function agregarAction() {

        // FORMULARIO
            $form=$this->_module->getModelObject('Admin/Forms/Create')->get();

            if ($this->getRequest()->isPost()) {

                $_POST['date_publish'] = App::locale()->fromDate($_POST['date_publish']);

echo '<pre>'; print_r('Porque chin! no envia el parametro ARTICLE ???'); echo '</pre>';
echo '<pre>'; print_r($_POST); echo '</pre>';
exit;

                if ($form->isValid($_POST)) {
                        // Agregamos SEO al array $_POST
                            $_POST['title'] = str_replace("'","",$_POST['title']);
                            $_POST['seo'] = App::module('Core')->getResourceSingleton('Filter')->seoUrl( $_POST['title'] );
                        if( $this->_module->getModelSingleton('Blog')->create($_POST)    ) {
                            App::module('Core')->getResourceSingleton('Flashmsg')->success(App::xlat('Articulo agregado correctamente'));
                            Header("Location:" . App::base('/articulos/blog/index'));
                            exit;
                        }else {
                            App::module('Core')->getResourceSingleton('Flashmsg')->error(App::xlat('No se agrego el articulo por un posible Id duplicado'));
                        }
                }

                $_POST['date_publish'] = App::locale()->toDate($_POST['date_publish']); // Tenemos que ponerla otra vez en el formato del locale
                $form->populate($_POST);
            }

         // Usamos el CMS para renderizar vista
            $this->__common('admin/blog/agregar', '/*FORM*/',$form);
     }

    /**
    * Editamos un caso de exito
    *
    */
    function editarAction() {

        // FORMULARIO
            $form=$this->_module->getModelObject('Admin/Forms/Edit')->get( $this->getRequest()->getParam('id') );

            if ($this->getRequest()->isPost()) {

                if ($form->isValid($_POST)) {
                    // Guardamos los cambios
                        // Convertimos la fecha
                            $_POST['date_publish'] = App::locale()->fromDate($_POST['date_publish']);
                        // Agregamos SEO al array $_POST
                            $_POST['title'] = str_replace("'","",$_POST['title']);
                            $_POST['seo'] = App::module('Core')->getResourceSingleton('Filter')->seoUrl( $_POST['title'] );

                        if( $this->_module->getModelSingleton('Blog')->update($_POST)    ) {
                            App::module('Core')->getResourceSingleton('Flashmsg')->success(App::xlat('El articulo ha sido modificado correctamente.'));
                            Header("Location:" . App::base('/articulos/blog/index'));
                            exit;
                        }

                    // Los propios modulos de user y acl generan los errores si existe alguno.
                }
                $form->populate($_POST);
            }

        // Usamos el CMS para renderizar vista
            $this->__common('admin/blog/editar', '/*FORM*/',$form);

    }

    /**
    * Eliminar articulo
    *
    */
    function eliminarAction() {

        // FORMULARIO
            $form=$this->_module->getModelObject('Admin/Forms/Delete')->get( $this->getRequest()->getParam('id') );

            if ($this->getRequest()->isPost()) {
                // Proseguimos con la eliminacion
                    if( $this->_module->getModelSingleton('Blog')->delete($_POST)    ) {
                        App::module('Core')->getResourceSingleton('Flashmsg')->success(App::xlat('La información de su articulo ha sido eliminada'));
                        Header("Location:" . App::module('Core')->getResourceSingleton('Goback')->get());
                        exit;
                    }

                App::module('Core')->getResourceSingleton('Flashmsg')->error(App::xlat('No se ha podido eliminar el caso'));
                $form->populate($_POST);
            }

        // Obtenemos el articulo
            $this->view->articulo = $this->_module->getModelSingleton('Blog')->get( $this->getRequest()->getParam('id') );
            $this->view->form = $form;

        // Breadcrumbs ------------------------------------------------------------------------------------
            $this->view->pageBreadcrumbs=array(
                array('title'=>App::xlat('Inicio'),'url'=>App::base('/')),
                array('title'=>App::xlat('Blog'),'url'=>App::base('/articulos/blog/index')),
                array('title'=>'Eliminar articulo'),
            );


    }

}