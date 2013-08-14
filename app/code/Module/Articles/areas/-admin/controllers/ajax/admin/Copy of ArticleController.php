<?php
require_once 'Module/Articles/Controller/Action/Admin.php';
class Articles_Ajax_Admin_ArticleController extends Module_Articles_Controller_Action_Admin{

	function preDispatch() {
		App::module("Acl")->getModelSingleton('acl')->requirePrivileges('admin');
		$this->designManager()->setCurrentLayout('ajax');
		$this->view->locale = App::locale()->getLang();
	}

	/**
	 * Listado de articulos
	 */
	function listingAction(){
		$resource=$this->_module->getResource('Article');
		
        // Ordenación de datos ================================================================================================
        require_once("Xplora/Datasorter.php");
        $datasorter=Xplora_Datasorter::factory()
                        ->setUrl(
                            App::url()->removeParams(
                                array(
                                    'sort_f'=>$this->getRequest()->getParam('sort_f'),
                                    'sort_t'=>$this->getRequest()->getParam('sort_t'),
                                    'page'=>$this->getRequest()->getParam('page')
                                )
                            )
                        );

        $datasorter->createField( "title" )
                    ->setFieldName( "title", Xplora_Datasorter::SORT_DESC );
        $datasorter->createField( "autor" )
                    ->setFieldName( "autor", Xplora_Datasorter::SORT_DESC );
        $datasorter->createField( "created" )
                    ->setFieldName( "created", Xplora_Datasorter::SORT_DESC );
        $datasorter->createField( "publicated" )
                    ->setFieldName( "publicated", Xplora_Datasorter::SORT_DESC );
        $datasorter->createField( "type" )
                    ->setFieldName( "type", Xplora_Datasorter::SORT_DESC );
        $datasorter->createField( "status" )
                    ->setFieldName( "status", Xplora_Datasorter::SORT_DESC );
   		// Establecemos ordenación por defecto
        $datasorter->setDefault( "created" )
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
                                    App::url()->removeParams(
                                        array(
                                            'page'=>$this->getRequest()->getParam('page')
                                        )
                                    )
                                )
                                ->setTranslator(App::translate()->getFormTranslator()) // Usamos el mismo traductor que para los formularios
                                ->setLocale(App::locale()->zend()); // Especificamos el traductor
            // Creamos los campos de filtrado
                // TIPOS
                	$tipos= $resource->getTipos();
                	$callback='articles.filterArticlesListing(this,"type")';
                    $datafilter->createField( "type" , Xplora_Datafilter::TYP_ONCHANGE, $callback )
                            ->setFieldname( "type" )
                            ->setMultioptions($tipos);
                // USUARIO CREADO
                	$user= $resource->getAuthor();
                	$callback='articles.filterArticlesListing(this,"ucreated")';
                    $datafilter->createField( "ucreated" , Xplora_Datafilter::TYP_ONCHANGE, $callback )
                            ->setFieldname( "autor" )
                            ->setMultioptions($user);
                // STATUS
                	$status= $resource->getStatus();
                	$callback='articles.filterArticlesListing(this,"status")';
                    $datafilter->createField( "status" , Xplora_Datafilter::TYP_ONCHANGE, $callback )
                            ->setFieldname( "status" )
                            ->setMultioptions($status);

            	// FECHA CREADO (RANGO DE FECHAS)
					$datafilter->createField( "fcredrang1" , Xplora_Datafilter::TYP_TIMESTAMP )
							->setFieldname( "created" )
							->setCondition( Xplora_Datafilter::COND_TIMESTAMPMOREEQ );
					$datafilter->cloneField( "fcredrang2" , "fcredrang1" )
							->setCondition( Xplora_Datafilter::COND_TIMESTAMPLESSEQ );

            	// FECHA PUBLICADO (RANGO FECHAS)
					$datafilter->createField( "fpubdrang1" , Xplora_Datafilter::TYP_TIMESTAMP )
							->setFieldname( "publicated" )
							->setCondition( Xplora_Datafilter::COND_TIMESTAMPMOREEQ );
					$datafilter->cloneField( "fpubdrang2" , "fpubdrang1" )
							->setCondition( Xplora_Datafilter::COND_TIMESTAMPLESSEQ );									

                // Establecemos el valor de los campos según los parámetros recibidos
                    $datafilter->populate($this->_request->getParams());
                // Pasamos el filtro de datos a la vista
                    $this->view->datafilter=$datafilter;
	    // Fin Filtrado de datos ================================================================================================			

        // Inicializamos el recurso para la carga de los datos y enviamos las opciones de filtrado globales (no los datafilters)
            //$articulos->setItems_per_page(3)
             $articulos=$resource->reset()
             		->setItems_per_page(3)
                    ->setDatasorter($datasorter)
                    ->setDatafilter($datafilter);
		// Iniciamos ordenamiento DESCENDENTE si no se ha ordenado manualmente
			if( !$this->_request->getParam('sort_f') ){
				$articulos->setOrder(array('id'=>'DESC'));
			}

		// Paginado de datos ================================================================================================
            $articulos->setPage((int)$this->_request->getParam('page'))
            		 ->setMaxstatus(4)	// Indicamos que nos muestre solo los que el ADMIN haya bloqueado. Los eliminados NO se muestran pues solo el admin los podra ver
            		 ->setLanguage(App::locale()->getLang());

            // Administrador NO DEBE administrar a los articulos que no le pertenezcan
            if(App::getConfig('admin_modifica_cualquier_articulo')==0){
				$articulos->setUsername(App::module('Acl')->getModel('acl')->user);
            }
			$this->view->articles = $articulos->get(true);           		 
		// Paginado de datos ================================================================================================
	}	
	
	/**
	 * Creamos nuevo articulo
	 */
	function createAction(){
		$idioma=$this->getRequest()->getParam('idioma');
		$form=$this->_module->getModel('Forms/Areas/Admin/Create')->get($idioma);

		// Has hecho post ?
		if ($this->getRequest()->isPost()) {

			// Revisamos si es valido
			if ($form->isValid($_POST)) {

				if ( $this->_module->getModel('Article')->create( $idioma, $_POST['tipo'],$_POST['fecha_publicado'],$_POST['titulo'],$_POST['seo'],$_POST['elarticulo'] ) ) {
					echo 'true'; exit;
				}else{
					$form->populate($_POST);
				}

			}else{
				$form->populate($_POST);
			}
		}
		$this->view->form=$form;    
	}

	 /**
     * Editar articulo
     */
    function editAction(){
    	
    	$articulo=$this->getRequest()->getParam('articulo');
    	$idioma=$this->getRequest()->getParam('idioma');
    	
		// Has hecho post ?
		if ($this->getRequest()->isPost()) {

			$form=$this->_module->getModel('Forms/Areas/Admin/Edit')->get(@$_POST,$articulo,$idioma,$this->getRequest()->getParam('languajes'), @$this->getRequest()->getParam('langchanged') );
			$_POST['article']=$_POST['elarticulo'];
			// Revisamos si es valido
			if ($form->isValid($_POST) && $_POST['elarticulo']) {
				if ( $this->_module->getResource('Article')->edit($articulo,$_POST['articulo'],$_POST['elarticulo'],$_POST['titulo'],$_POST['seo'],$_POST['fecha_publicado'],$_POST['tipo'],$idioma) ) {
					// Recargamos el articulo para que muestre los cambios
					//$detalle_articulo=$this->_module->getResource('Article')->reset()->setArticulo($articulo)->detail($locale_idioma);
					//$form=$this->_module->getModel('Forms/Areas/Admin/Edit')->get($detalle_articulo,$articulo,$locale_idioma,$this->getRequest()->getParam('languajes'));
					//$this->view->exito=true;
					echo 'true'; exit;
				}else{
					$this->view->error=true;
				}

			}else{
				$form->populate($_POST);
			}
		}else{
			$detalle_articulo=$this->_module->getResource('Article')->detail($articulo,$idioma);
			$form=$this->_module->getModel('Forms/Areas/Admin/Edit')->get($detalle_articulo,$articulo,$idioma,$this->getRequest()->getParam('languajes'), @$this->getRequest()->getParam('langchanged') );
		}
		$this->view->form=$form;
    }
	
	
	
    /**
     * Editar articulo
     */
    function editOLDAction(){

		// Cargamos los datos del cliente
		$articulo=$this->getRequest()->getParam('articulo');
		$locale_idioma=$this->getRequest()->getParam('idioma');

		// Cargamos los IDs de los idiomas que tiene el articulo
		/**
		 * TODO: Mejorar esto...descripcion abajo
		 */
			$ids_idiomas=$this->getRequest()->getParam('languajes');
			$ids_idiomas=explode('-',$ids_idiomas);
			$idiomas=explode('|',$ids_idiomas[0]);
			$article_languaje_listing=explode('|',$ids_idiomas[1]);

			// Aqui se lee: Si el idioma NO es ESPAÑOL y el LOCALE es español...
			if( (!$article_languaje_listing[0] && !$this->getRequest()->getParam('langchanged') ) && $locale_idioma=='es' ){
				$locale_idioma='en';
			}
    		if( (!$article_languaje_listing[1] && !$this->getRequest()->getParam('langchanged') ) && $locale_idioma=='en' ){
				$locale_idioma='es';
			}	
			// Sacamos el ID del articulo que estamos editando	
    		if($locale_idioma=='es' ){
				$articulo_id=$idiomas[0];
			}else{
				$articulo_id=$idiomas[1];		
			}
			
		/*
		 * DESCRIPCION:
		 * Lo que hacemos anteriormente, es obtener los IDS de los articulos segun su idioma, esto se almacena en variable $ids
		 * Tambien identificamos el idioma REAL del articulo al cual hicimos CLICK. Esto porque da un error con el LOCALE.
		 * Es decir, si el locale fuera "ES", pero el articulo NO esta en español, pero esta en ingles, si no hacemos este monton de validaciones, intentaria
		 * la aplicacion cargar al idioma en ES, pero al no estar, tendriamos que cambiar manualmente nosotros al idioma ingles....lo cual es molesto.
		 * Con esto, la aplicacion investiga si el idioma es igual que el locale, y asi hace la carga del articulo correcto 
		 */

		// Has hecho post ?
		if ($this->getRequest()->isPost()) {

			$form=$this->_module->getModel('Forms/Areas/Admin/Edit')->get(@$_POST,$articulo,$locale_idioma,$this->getRequest()->getParam('languajes'), @$this->getRequest()->getParam('langchanged') );
			$_POST['article']=$_POST['elarticulo'];
			// Revisamos si es valido
			if ($form->isValid($_POST) && $_POST['elarticulo']) {
				if ( $this->_module->getResource('Article')->edit($articulo_id,$_POST['articulo'],$_POST['elarticulo'],$_POST['titulo'],$_POST['seo'],$_POST['fecha_publicado'],$_POST['tipo'],$locale_idioma) ) {
					// Recargamos el articulo para que muestre los cambios
					//$detalle_articulo=$this->_module->getResource('Article')->reset()->setArticulo($articulo)->detail($locale_idioma);
					//$form=$this->_module->getModel('Forms/Areas/Admin/Edit')->get($detalle_articulo,$articulo,$locale_idioma,$this->getRequest()->getParam('languajes'));
					//$this->view->exito=true;
					echo 'true'; exit;
				}else{
					$this->view->error=true;
				}

			}else{
				$form->populate($_POST);
			}
		}else{
			$detalle_articulo=$this->_module->getResource('Article')->reset()->setArticulo($articulo)->detail($locale_idioma);
			$form=$this->_module->getModel('Forms/Areas/Admin/Edit')->get($detalle_articulo,$articulo,$locale_idioma,$this->getRequest()->getParam('languajes'), @$this->getRequest()->getParam('langchanged') );
		}
		$this->view->form=$form;

  		// Identificamos si tiene otro idioma y almacenamos los idiomas que tiene para mostrarlos en un SELECT-BOX
		if($locale_idioma=="es"){
			$this->view->languaje_selector = array('es'=>'Español','en'=>'Ingles');
		}else{
			$this->view->languaje_selector = array('en'=>'English','es'=>'Spanish');
		}		
    }

	/**
	 * Detalle de articulo
	 */
    function previewAction(){
		$articulo=$this->getRequest()->getParam('articulo');
		// Idioma actual para ver la informacion. Esto da problemas cuando el articulo esta en 1 idioma y NO es el del locale actual, es decir:
		// Si tienes un articulo en ingles solamente, pero estas viendo la pagina en español, el LOCALE es= es_MX, entonces no carga el articulo
		// pues $idioma = 'es', pero el articulo no es iguala 'es', sino que es 'en'
		// Por ello, se obtienen los IDS de idiomas, para intentar cargar los articulos por el idioma que se encuentre dispoible, sin importar el locale
			$locale_idioma=$this->getRequest()->getParam('idioma');
		// Cargamos los IDs de los idiomas que tiene el articulo
			$idiomas=$this->getRequest()->getParam('languajes');
			$idiomas=explode('|',$idiomas);
			// Aqui se lee: Si el idioma NO es ESPAÑOL y el LOCALE es español...
			if(!$idiomas[0] && $locale_idioma=='es' ){
				$locale_idioma='en';
			}

		// Cargamos el articulo de la VISTA del idioma actual
			$detalle=$this->_module->getResource('Article')->reset()->setArticulo($articulo)->detail($locale_idioma);

		if(!$detalle){
			echo "error"; exit;
		}else{
			
			// Detalle del articulo
				$this->view->detalle=$detalle;
			// Directorios de las imagenes correspondientes a los articulos
				$config=$this->_module->getConfig('core','articulos_galeria'); // Cargamos la configuracion del ini
			// Los path de las imagenes para los articulos
				eval("\$this->view->url_articulos=".$config['tipo']['url'].";");
				eval("\$this->view->path_articulos=".$config['path']['basepath'].";");
			// Extensiones permitidas
				$this->_permitidas=$config['permitidas'];
		}
		$this->view->id=$articulo;
    }	



    /**
     * Editar los parametros del articulo
     */
    function paramsAction(){
		// Cargamos los datos del cliente
		$articulo=$this->getRequest()->getParam('articulo');
		$locale_idioma=$this->getRequest()->getParam('idioma');
		$idiomas=$this->getRequest()->getParam('languajes');
		
		// Cargamos los IDs de los idiomas que tiene el articulo
			$idiomas=explode('|',$idiomas);
			// Aqui se lee: Si el idioma NO es ESPAÑOL y el LOCALE es español...
			if(!$idiomas[0] && $locale_idioma=='es' ){
				$locale_idioma='en';
			}
    		if(!$idiomas[1] && $locale_idioma=='en' ){
				$locale_idioma='es';
			}	
			// Sacamos el ID del articulo que estamos editando	
    		if($locale_idioma=='es' ){
				$articulo_id=$idiomas[0];
			}else{
				$articulo_id=$idiomas[1];		
			}

		// Cargamos el detalle del articulo
			//$detalle_articulo=$this->_module->getResource('Article')->reset()->setArticulo($articulo_id)->params($locale_idioma);
			$detalle_articulo=$this->_module->getResource('Article')->reset()->setArticulo($articulo)->detail($locale_idioma);
			$detalle_articulo=$detalle_articulo[0];

			if(!$detalle_articulo){
				echo 'error'; exit;
			}else{
			// Identificamos si tiene otro idioma y almacenamos los idiomas que tiene para mostrarlos en un SELECT-BOX
				if($locale_idioma=="es" && $idiomas[0]){
					if($detalle_articulo['en_id']>0){
						$this->view->languaje_selector = array('es'=>'Español','en'=>'Ingles');
					}
				}else{
					if($detalle_articulo['es_id']>0 && $idiomas[1]){
						$this->view->languaje_selector = array('en'=>'English','es'=>'Spanish');
					}
				}
			}
			
		$form=$this->_module->getModel('Forms/Areas/Admin/Params')->get($detalle_articulo,$locale_idioma,$this->getRequest()->getParam('languajes') );

		// Has hecho post ?
		if ($this->getRequest()->isPost()) {

			// Revisamos si es valido
			if ($form->isValid($_POST)) {
			
				if ( $this->_module->getResource('Article')->editParams($articulo,$articulo_id,$locale_idioma,$_POST['titulo'],$_POST['seo'],$_POST['fecha_publicado'],$_POST['tipo']) ) {
					// Recargamos el articulo para que muestre los cambios
//					$form=$this->_module->getModel('Forms/Areas/Admin/Params')->get($detalle_articulo,$locale_idioma,$this->getRequest()->getParam('languajes') );
					$this->view->exito=true;
				}else{
					$this->view->error=true;
				}

			}else{
				$form->populate($_POST);
			}
		}
		$this->view->form=$form;  
		$this->view->articulo=$articulo;
		$this->view->detalle=$detalle_articulo;
		$this->view->articulo_id = $articulo_id;
    }
    
	/**
	 * Modificamos el status de uno o mas articulos: POR EL LISTADO
	 */
	function statusAction(){
		$articulos=$this->_json()->decode(stripslashes($this->getRequest()->getParam('articulos')));
		// Como los IDs de los articulos los recibimos separados por |, debemos hacer un explode
		// Los recibimos asi, porque son 2 idiomas x default, por lo que necesitaremos procesar el array para obtener los IDS 
		// El primer Id es para los articulos en ESPAÑOL, el segundo es en INGLES
		foreach($articulos AS $articulo){
			$ids=explode("|",$articulo);
			if($ids[0]){
				$spanish[]=@$ids[0];	
			}
			if($ids[1]){
				$english[]=@$ids[1];	
			}
		}


		$valor= strip_tags($this->getRequest()->getParam('valor'));

		if($articulos && sizeof($articulos)>0){
			$recurso=$this->_module->getResource('Article')->reset()->setArticulos_en($english)->setArticulos_es($spanish);
			echo $recurso->setStatus($valor)->statusUpdate();
		}else{
			echo 'false';
		}
		exit;
	}

	/**
	 * Modificamos el status de un articulo( esto al ver el detalle)
	 */
	function statusLanguajeAction(){
		$articulo=$this->getRequest()->getParam('articulo');
		$valor= $this->getRequest()->getParam('valor');
		$idioma= $this->getRequest()->getParam('idioma');

		if($articulo){
			$recurso=$this->_module->getResource('Article')->reset()->setArticulo($articulo)->setLanguaje($idioma);
			echo $recurso->setStatus($valor)->statusByArticleUpdate();
		}else{
			echo 'false';
		}
		exit;
	}
	
	/**
	 * Instanciamos al objeto Json
	 */
	protected function _json() {
		require_once("Zend/Json.php");
		return new Zend_Json;
	}	
}