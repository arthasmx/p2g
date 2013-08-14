<?php

class Module_Articles_Repository_Model_Article extends Core_Model_Repository_Model {

	/**
	 * Total de últimas noticias por defecto
	 *
	 * @var int
	 */
	protected $_totalLatest = 3;

	/**
	 * Almacén para el acceso al recurso de noticias
	 *
	 * @var object
	 */
	protected $_resource = null;

	/**
	 * Almacén del resultset actual
	 *
	 * @var array
	 */
	protected $_currentResultSet = null;

	/**
	 * Tag para el corte tipo more
	 *
	 */
	const MOREBREAK_TAG = '<!-- pagebreak -->';

	/**
	 * Cantidad de caracteres a cortar
	 * (si no se encuentra el tag more)
	 *
	 */
	const MOREBREAK_SUBSTR = 500;

	/**
	 * Auto inicializa el acceso al recurso de las noticias
	 *
	 */
	public function init() {
		$this->_resource=$this->_module->getResource('Article');
    }

    /**
     * Obtiene todas las noticias
     *
     * @param int $page
     * @return array
     */
    function getAllByDate($year=null,$month=null) {
		$this->_currentResultSet=$this->_resource->getAllByDate($year,$month);
		$this->_parseMoreBreak();
		$this->_parseDates();
		return $this->_currentResultSet;
    }

    /**
     * Obtioene las ultimas noticias publicadas
     *
     * @param int $total
     * @return array
     */
    function getLatest($total=null) {
    	if (!$total) {
    		$total=App::getConfig('lastArticlesRegistered');
    		if (!$total) $total=$this->_totalLatest;
    	}
    	
    	$this->_currentResultSet=$this->_resource
    									->reset()
    									->setMaxstatus(4)
    									->setOrder(array('created'=>'DESC'))
    									->setLimit($total)
    									->get(true);
    	
		/*
    	$this->_currentResultSet=$this->_resource->getLatest($total);
		$this->_parseMoreBreak();
		$this->_parseDates();
		*/
		$this->_parseMoreBreak();
		$this->_parseDates();
		return $this->_currentResultSet;
    }

	/**
	 * Devuelve la noticia con el id indicado
	 *
	 * @param string $id
	 * @return array
	 */
	function read($id) {
		$this->_currentResultSet=$this->_resource->getById($id);
		$this->_parseDates();

		return $this->_currentResultSet;
	}

/********************************************************************************************************************************************/
// METODOS DE ADMINISTRACION
//--------------------------------------------------------------------------------------------------------------------------------------------

	/**
	 * Creamos un nuevo articulo
	 * @return array
	 */
	function create($idioma,$tipo,$fecha_publicado,$titulo,$seo,$article) {
		if ( !$idioma || !$tipo || !$fecha_publicado || !$titulo || !$seo || !$article ) return false;

		return $this->_resource
						->setLanguaje($idioma)
				// Articulos_es o en
						->setTitulo($titulo)
						->setSeo($seo)
						->setArticulo($article)
						->setUsername(App::module('Acl')->getModelSingleton('Acl')->data['username'])
						->setFecha_creado( date('Y-m-d H:i:s') )
				// Articulos
						->setTipo($tipo)
						->setFecha_publicado($fecha_publicado)

						->create();
	}

	/**
	 * Actualizamos un articulo
	 *
	 * @param array $post | indices: date_publish, body
	 * @return array
	 */
	function update($post=false) {
		if ( !$post ) return false;

		return $this->_module->getResourceSingleton('Admin/Update/Blog')
						->setId($post['id'])
						->setDate_publish($post['date_publish'] . date(' H:i:s') )
						->setLogo($post['logo'])
						->setTitle($post['title'])
						->setBody($post['body'])
						->setSeo($post['seo'])
						->setUsername(App::module('Acl')->getModelSingleton('Acl')->data['username'])
						->update();

	}

	/**
	 * Eliminacion de un articulo
	 *
	 * @param array $post | indices: id de caso por POST
	 * @return array
	 */
	function delete($post=false) {
		if ( !$post ) return false;

		return $this->_module->getResourceSingleton('Admin/Delete/Blog')
						->setId($post['id'])
						->setUsername(App::module('Acl')->getModelSingleton('Acl')->data['username'])
						->delete();
	}

	/**
	 * Elimina el contenido despues del salto More de las noticias, o las corta si es necesario.
	 *
	 */
    function _parseMoreBreak() {
    	foreach ((array)$this->_currentResultSet as $key=>$value) {
    		if (isset($value['article']) ) {
    			if (stristr($value['article'],self::MOREBREAK_TAG )) {
    				$tmp=explode(self::MOREBREAK_TAG,$value['article']);
    				$this->_currentResultSet[$key]['article']=$tmp[0];
    				$this->_currentResultSet[$key]['article_more']=true;
    			} else {
    				if (strlen($value['article'])>self::MOREBREAK_SUBSTR ) {
    					$this->_currentResultSet[$key]['article']=substr(strip_tags($value['article']),0,self::MOREBREAK_SUBSTR);
    					$this->_currentResultSet[$key]['article_more']=true;
    				} else {
    					$this->_currentResultSet[$key]['article_more']=false;
    				}
    			}
    		}
    	}
    }


    /**
     * Parsea las fechas y las convierte a un formato correspondiente al locale actual
     *
     */
    function _parseDates($field='date_publish') {
    	// Cargamos Zend_Date y definimos el formato a utilizar para las fechas
    		require_once("Zend/Date.php");
	    	$dateFormat=App::locale()->getDateFormats();

    	foreach ((array)$this->_currentResultSet as $key=>$value) {
    		if (isset($value[$field])) {
				$date = new Zend_Date($value[$field]);
				$this->_currentResultSet[$key][$field]=$date->toString( $dateFormat['long'],null,App::locale()->zend() );
    		}
    	}
    }

    /**
     * Parsea las fechas y las convierte a un formato correspondiente al locale actual
     *
     */
    function _parseMonths($field='date_publish') {

    	// Cargamos Zend_Date y definimos el formato a utilizar para las fechas
	    	require_once("Zend/Date.php");
	    	$locale = App::locale()->zend();
	    	//$dateFormat=Zend_Locale_Data::getList($locale,'dateTime'); // Obtiene toda la lista de formatos
	    	date_default_timezone_set('America/Mazatlan');

    	foreach ((array)$this->_currentResultSet as $key=>$value) {
    		if (isset($value[$field])) {
				$date = new Zend_Date($value[$field]);
				$this->_currentResultSet[$key][$field]=ucwords($date->toString( "MMMM yyyy" ,null,$locale ));
    		}
    	}
    }

}