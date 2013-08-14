<?php

class Module_Core_Repository_Resource_Xml extends Core_Model_Repository_Resource {

	const DOC_PATH				= 'xml';
	protected $limit			= false;
	protected $pagination		= false;

	protected $_xml				= null;
	protected $size				= null;
	protected $content			= null;
	protected $_error_message	= 'Archivo XML no ha sido definido';

	protected $_fileSystem 	= null;

    // Propiedades utilizadas al crear la galeria
	    protected $path =false;
	    protected $xmlfiles =false;
	    protected $xml =false;

// INICIALIZACION ******************************************************************************************

	function init(){
		$this->_fileSystem = $this->_module->getResourceSingleton('Filesystem');
	}

	/**
	 * Intercepta la llama a funciones que no existen y la ejecutamos
	 *
	 * @param unknown_type $function
	 * @param unknown_type $args
	 * @return unknown
	 */
	public function __call($function, $args) {
		// Comprueba SET

			preg_match("/^set([a-zA-Z]+)$/",$function,$matches);

			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var}) || @$this->{$var}===false) {
					$this->{$var}=$args[0];
				}

				return $this;
			}

		// Comprueba GET
			preg_match("/^get([a-zA-Z]+)$/",$function,$matches);
			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var})) {
					return $this->{$var};
				}
				return false;
			}

	}

// METODOS PRINCIPALES  ***********************************************************************************

	/**
	* Cargamos el archivo XML
	*
	*/
	function get(){
		if ( !$this->_xml ) $this->_module->exception( App::xlat( $this->_error_message ) ,501);

		if ( !$xmlContent = @file_get_contents( $this->_xml ) ){
			//$this->_module->exception( App::xlat('No se pudo leer el contenido del archivo %s',$this->_xml),501);
			return false;
		}

		// Iniciamos SimpleXML. Se almacenan resultados en propiedad _content
			$this->content = new SimpleXMLElement($xmlContent);

		return $this->getContent();
	}

	/**
	* Establecemos el archivo con el cual vamos a trabajar
	*
	*/
	function setFile($xml=null){
		if ( !$xml ) $this->_module->exception( App::xlat( $this->_error_message ) ,501);

		if ( $this->_fileSystem->isFound($xml) ){
			$this->_xml = $xml;
			return $this;
		}

		$this->_module->exception( App::xlat( "No se ha encontrado el archivo %s en la ruta especificada", $xml ) ,501);
	}

	/**
	* Total de nodos del XML
	*
	* @return Int
	*/
	function size(){
		if ( !$this->content ) $this->_module->exception( App::xlat( $this->_error_message ) ,501);
		return count( $this->content );
	}

	/**
	* Regresa el contenido del XML
	*
	*/
	function getContent(){
		if ( !$this->content ) $this->_module->exception( App::xlat( $this->_error_message ) ,501);

		// Aplicamos la paginacion si esta establecida
			if ($this->pagination && $this->limit){

			// Sacamos los parametros que ocupamos del array
				$paginados	= array();
				foreach($this->content as $story => $index) {
					//if ( isset($index->path) ){ // Obtenemos los datos de configuracion del XML
						//$cfg[] = array ('id' => (string)$index->id,'path' => (string)$index->path);
					//	$id	 	= (string)$index->id;
					//	$path	= (string)$index->path;
					//}else {
						$paginados[] = array ('id' => (string)$index->description['id'],'description' => (string)$index->description);
					//}
				}

			// Ahora paginamos los resultados
				$per_page = $this->limit;
				$cur_page = $this->pagination;
				if (!$cur_page) $cur_page = 1;
				$page_max = $cur_page * $per_page;
				$page_min = $page_max - $per_page;

			// Sacamos el maximo total de paginas que esta galeria puede tener
				$total_pages = intval($this->size() / $this->limit);
				if ($total_pages % $this->limit) { $total_pages+=1; }


				$final_array = array();
				for ($i = $page_min; $i < $page_max; $i++) {
					if ( is_array(@$paginados[$i]) ){
						$final_array[]= @$paginados[$i];
					}
				}

				unset($this->content);
				//$this->content = array_merge((array)$final_array, array('pages'=>$total_pages), array('id'=>$id), array('path'=>$path) );
				$this->content = array_merge((array)$final_array, array('pages'=>$total_pages));
			}

		return $this->content;
	}

	/**
	 * Crea un archivo XML para una galeria
	 *
	 * @return Array | xml
	 *
	 */
	function create(){

		// Revisamos que exista el directorio donde se guardara el XML
			if(!$this->_fileSystem->isFound( $this->path ) ){
					$this->_module->getModel('Flashmsg')->error( App::xlat('No existe el directorio donde se almacenará su archivo XML') );
					return false; //header( 'Location :'.App::base('/account') );
					exit;
			}

		$_tmp_xml_content = '';
        foreach((array)$this->xmlfiles AS $key => $value){
            $_tmp_xml_content .=
								PHP_EOL.chr(9)
								.'<image>'.PHP_EOL.chr(9).chr(9)
									.'<description id="'.$value['id'].'">'
								  		.htmlentities(@$value['description'])
									.'</description>'.PHP_EOL.chr(9)
								.'</image>';
		}
		// Crear archivo XML
			$xml_content = '<?xml version="1.0" encoding="utf-8" ?>'.PHP_EOL.'<gallery>'.$_tmp_xml_content.PHP_EOL.'</gallery>';
			$fp = @fopen($this->path.DS.$this->xml,'w');

			if($fp){
				$write = fwrite($fp,$xml_content);
				fclose($fp);
			}else{
				$this->_module->getResourceSingleton('Flashmsg')->error( App::xlat('No se ha creado la galeria de fotos correctamente para este producto.') );
				return false;
			}
		// Regresamos el nombre del XML creado
			return $this->xml;
	}


}