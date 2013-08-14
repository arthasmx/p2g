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


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS USUARIOS
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

    /**
     * Obtiene los ultimos articulos escritos
     * Esto es para FRONTEND o USERS, pues FORZAMOS que esten VISIBLES
     *
     * @param int $total
     * @return array
     */
    function getLatest($total=false) {
    	if (!$total || $total<=0) {
    		if (!$total) $total=$this->_totalLatest;
    	}
    	return $this->_resource->reset()
    							->setStatus(1)
    							->setOrder(array('created'=>'DESC'))
    							->setLimit($total)
    							->get(true);
    }


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS ADMIN
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

    /**
     * Obtiene los ultimos articulos escritos
     * Esto es para ADMIN, pues mostramos los que estan Ocultos, Etc.
     *
     * @param int $total
     * @return array
     */
    function getLatestByAdmin($total=false) {
    	if (!$total || $total<=0) {
    		if (!$total) $total=$this->_totalLatest;
    	}
    	return $this->_resource->reset()
    							->setMaxstatus(4)
    							->setOrder(array('created'=>'DESC'))
    							->setLimit($total)
    							->get(true);
    }

	/**
	 * Creamos un nuevo articulo
	 * @return array
	 */
	function create( $article=false,$picture=false,$languages=false,$title=false,$seo=false,$publicated=false,$type=false,$category=false,$draft=false ) {
		if ( !$picture || !$languages || !$title || !$seo || !$publicated || !$type || !$category ) return false;
		// Transformamos la fecha al formato de mysql
			$publicated=App::module('Core')->getResource('Dates')->toDate(0,$publicated);

		return $this->_resource
						->setArticle($article)
						->setPicture($picture)
						->setLanguage($languages)
						->setTitle($title)
						->setSeo($seo)
						->setPublicated($publicated)
						->setType($type)
						->setCategory($category)
						->setDraft($draft)
						->setUsername(App::module('Acl')->getModel('Acl')->user)
						->setCreated( date('Y-m-d H:i:s') )
						->create();
	}

	/**
	 * Creamos un nuevo articulo
	 * @return array
	 */
	function draft( $article=false,$languages=false,$title=false,$seo=false,$publicated=false,$type=false,$category=false,$draft=false ) {
		if ( !$article || !$languages || !$title || !$seo || !$publicated || !$type || !$category ) return false;
		// Transformamos la fecha al formato de mysql
			$publicated=App::module('Core')->getResource('Dates')->toDate(0,$publicated);
		
		return $this->_resource
						->setArticle($article)
						->setLanguage($languages)
						->setTitle($title)
						->setSeo($seo)
						->setPublicated($publicated)
						->setType($type)
						->setCategory($category)
						->setDraft($draft)
						->setUsername(App::module('Acl')->getModel('Acl')->user)
						->setCreated( date('Y-m-d H:i:s') )
						->draft();
	}


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS ROOT
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

    /**
     * Obtiene los ultimos articulos escritos
     * Esto es para ROOT, pues mostramos TODOS
     *
     * @param int $total
     * @return array
     */
    function getLatestByRoot($total=false) {
    	if (!$total || $total<=0) {
    		if (!$total) $total=$this->_totalLatest;
    	}
    	return $this->_resource->reset()
    							->setOrder(array('created'=>'DESC'))
    							->setLimit($total)
    							->get(true);
    }


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS GENERICOS que no validan STATUS, USERNAME
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

	/**
	 * Auto inicializa el acceso al recurso
	 */
	public function init() {
		$this->_resource=$this->_module->getResource('Article');
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