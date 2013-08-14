<?php
class Module_Core_Repository_Resource_Filesystem extends Core_Model_Repository_Resource {

	/**
	 * Archivo a ser procesado
	 *
	 * @var String
	 */
	protected $_file	= null;
    // Flag para indicar si quieres crear 1 archivo XML con las imagenes subidas al server
	protected $doXml	= false;
	protected $pixXml	= array();

	/**
	 * Inicializacion de clase
	 *
	 */
	function init() {
	}

    function setDoXML($flag=false){
    	$this->doXml=$flag;
    	return $this;
	}

	/**
	* Establecemos el nombre de archivo a procesar
	*
	*/
	function setFile($file=null){
		if ( !$file ) return false;

		$this->_file = $file;
		return $this;
	}

	/**
	* Descripcion de funcion
	*
	*/
	function getFile(){
		if ( !$this->_file ) {
			require_once("Module/Core/Exception.php");
			throw new Module_Core_Exception(App::xlat('No se ha establecido el archivo'));
		}

		return $this->_file;
	}

	/**
	* Revisamos la existencia de un archivo en disco y lo establecemos al objeto
	* @param string $file | Nombre del archivo
	*/
	function isFound($file=false){
		if ( !$file ) return false;

		if ( file_exists( $file ) ){
			$this->setFile($file);
			clearstatcache();
			return true;
		}
		return false;
	}

	/**
	 * Obtenemos el nombre de una archivo.
	 *
	 */
	function getFileName($fullName=false){
		$datos = pathinfo( $this->getFile() );

		if ($fullName)
			return $datos['dirname'].'/'.$datos['basename'];
		else
			return $datos['basename'];
	}

	/**
	 * Obtenemos el nombre de un archivo en general sin necesidad de asignarlo a toda una clase
	 *
	 */
	function getFileNameGeneric($fil=false,$fullName=false,$onlyFiles=false){
		$datos = pathinfo( $fil );

		// Investigamos si es un DIRECTORIO, sin no usar la funcion IS_DIR
			if($onlyFiles && !isset($datos['extension']) ) return false;

		if ($fullName){
			echo '<pre>'; print_r($datos); echo '</pre>';
		}else{
			return $datos['basename'];
		}
	}

	/**
	 * Obtenemos el Path de 1 archivo
	 *
	 */
	function getFilePath(){
		$datos = pathinfo( $this->getFile() );
		return $datos['dirname'];
	}

	/**
	 * Obtenemos la extension de 1 archivo
	 *
	 */
	function getFileExt(){
		$datos = pathinfo( $this->getFile() );
		return $datos['extension'];
	}

	/**
	 * Obtenemos la extension de 1 archivo sin que este sea establecido para uso en la clase
	 *
	 */
	function getExtension($file=false){
		if(!$file) return false;
		$datos = pathinfo( $file );

		return @$datos['extension'];
	}

	/**
	 * Obtenemos el tamaño de un archivo sin que este sea establecido para uso en la clase
	 *
	 */
	function getFileSize($size=false){
		if(!$size || $size<1) return false;
		return @$size/1024;
	}

	/**
	 * Devuelve PATH/ARCHIVO usando REGEX para quitar la extension
	 * Util para establecer automaticamente nombres de archivos comprimidos (util para un sitio mio)
	 *
	 */
	function regexFileName(){

		try{
			preg_match_all('/(.*\/)?(.*?)\./', $this->getFile() , $file );
			return $file[1][0].$file[2][0];
		}catch (Exception $e){
			return false;
		}
	}


	/**
	* Elimina 1 archivo especificado
	* @param string $file | Nombre del archivo a eliminar
	*/
	function delete($file=null){
		if ( !$file || !@unlink($file) ) return false;
		return true;
	}

	/**
	* @desc Lista los archivos de un directorio, segun la extension dada
	* @param string $extension | Extension de los archivos deseados
	* @param string $path | Ubicacion absoluta de los archivos
	*/
	function old_getFilesFromPath($extension=false, $path=false)	{
    	if (!$extension || !$path) return false;

    	// Abrimos el directorio
    		$list = array();
		    if (!$dir_handle = opendir($path)) {
		    	return false;
		    }

        // Comenzamos su lectura
		    while($file = readdir($dir_handle)){
				// Son directorios, continuemos con otro loop
		        	if($file == "." || $file == ".."){continue;}
		        // Revisamos la extension del archivo leido
		        	if($this->getExtension(strtolower($file)) == strtolower($extension)){
		            	array_push($list, $file);
		        	}
		    }

		    if(@$list[0])
    			return $list;
		    else
    			return false;
	}

	function getFilesFromPath($path,array $options=array()) {
		// Revisamos las opciones para determinar includes y excludes
			$includes=array("/^pdf$/");
			$excludes=array();
			if (isset($options['include'])) $includes=(array)$options['include'];
			if (isset($options['exclude'])) $excludes=(array)$options['exclude'];
		// Abrimos el directorio
			if (!is_dir($path) || !is_readable($path) || !$dir_handle = opendir($path)) {
				$this->_module->exception("No se ha podido leer la ruta ".$path);
			}

        // Comenzamos su lectura
        	$files=array();
		    while($file = readdir($dir_handle)){
				// Son directorios, continuemos con otro loop
		        	if($file == "." || $file == ".."){continue;}
		        // Revisamos si el archivo tiene coincidencias en includes o en excludes
		        	$en_includes=false;
		        	foreach($includes as $include) {
		        		try {
			        		if (preg_match($include,$file,$matches)) {
			        			$en_includes=true;
			        		};
		        		} catch (Exception $e) {};
		        	}
		  			$en_excludes=false;
		        	foreach($excludes as $exclude) {
		        		try {
			        		if (preg_match($exclude,$file,$matches)) {
			        			$en_excludes=true;
			        		};
		        		} catch (Exception $e) {};
		        	}
		        // Revisamos la extension del archivo leido
		        	if ($en_includes && !$en_excludes) array_push($files, $file);
		    }
		// Devolvemos array con archivos en la ruta
		    if (sizeof($files)<1) {
		    	return false;
		    } else {
		    	return $files;
		    }
	}

	/**
	 * Crea carpeta en el path indicado
	 * @param $path | Path donde se va a crear la carpeta
	 * @param $nombre | Nombre de la carpeta nueva
	 * @return boolean | True si se creo la carpeta, False para un error
	 */
	function crearCarpeta($path=false,$nombre=false){
		if($this->isFound($path.$nombre)) return false;

		// Creamos la carpeta
			if(!mkdir($path.$nombre)) return false;

		return true;
	}


	/**
	 * Renombrar/Mover carpetas/archivos.
	 * @param string $NAME_ACT | Nombre del archivo actual
	 * @param string $NAME_NEW | Nuevo nombre para el archivo
	 */
	public function moveFile($NAME_ACT,$NAME_NEW){
		if ( file_exists($NAME_NEW) )
			{	return false;	}
		else
			{
				clearstatcache();
				if ( file_exists($NAME_ACT) )
					{	rename($NAME_ACT, strtolower($NAME_NEW));
						return true;	}
				else
					{	return false;	}
			}
	}


	/**
	* @desc El path del directorio de galerias
	* @param string $path | Ubicacion de las galerias
	*/
	public function pathGalerias($path=false){
    	return WP.DS.App::module('Catalogo')->getConfig('core','productos_main_path').DS.$path;
	}


    /**
    * @desc Metodillo para subir UNA imagen al server. Solamente JPG jiji
    * @param $field_id | Id del campo FILE del formulario
    * @param $path | Subdirectorio donde se almacenara la imagen
    * @param $name | Nombre que recibira la imagen. Se incrementa de 1 en 1, comenzando desde 100 ( Nombre-foto-100.jpg)
    * @param $files| Objeto $_FILES del formulario
    * @param $total_imagenes | Contador de imagenes actuales
    * @todo Validarlo
    */
    function uploadFile($field_id=false,$path=false,$name=false,$files=false,$total_imagenes=0){
    	// Regreso TRUE para crear el producto en la BD
    		if(!$field_id||!$path||!$name||!$files) return true;

    	$pixId = 100+$total_imagenes;
    	$maxImageSize=App::module('Catalogo')->getConfig('core','productos_max_image_size');
		foreach ($files[$field_id] AS $key => $value) {
			if ($files[$field_id]['error']===0 ) {
				if($this->getFileSize($files[$field_id]['size'])<$maxImageSize){
					$tmp_name = $files[$field_id]['tmp_name'];
					$newName = $this->pathGalerias($path).DS.$name.'-'.$pixId.".jpg";

					// Evitamos sobreescribir la imagen
						if($this->isFound($newName)){
							for($pId=$pixId+1;$pId<=300;$pId++){
								$otro_nombre = $this->pathGalerias($path).DS.$name.'-'.$pId.".jpg";
								if( !$this->isFound($otro_nombre) )	{
									$newName = $otro_nombre;
									break;
								}
							}
						}

					move_uploaded_file($tmp_name, $newName);
				}else{
					// El archivo es muy grande...
                    $this->_module->getModel('Flashmsg')->error(App::xlat('La imagen %s es demasiado grande. El tamaño maximo permitido es de %s',array($value,$maxImageSize)));
                    return false;
                    break;
				}
				// Se almacenan las imagenes para crear 1 XML si se desea
					$this->pixXml[]=array('id'=>$pixId);
					$pixId++;
			}
		}
		return true;
	}



    /**
    * @desc Metodillo para subir VARIAS imagenes al server. Solamente JPG jiji
    * @param $field_id | Id del campo FILE del formulario
    * @param $path | Subdirectorio donde se almacenara la imagen
    * @param $name | Nombre que recibira la imagen. Se incrementa de 1 en 1, comenzando desde 100 ( Nombre-foto-100.jpg)
    * @param $files| Objeto $_FILES del formulario
    * @param $pixId | Id numerico del cual comenzara el renombrado de los archivos
    * @todo Validarlo
    */
    function uploadFiles($field_id=false,$path=false,$name=false,$files=false,$pixId=100){
    	// Regreso TRUE para crear el producto en la BD
    		if(!$field_id||!$path||!$name||!$files) return true;

    	$maxImageSize=App::module('Catalogo')->getConfig('core','productos_max_image_size');

		foreach ($files[$field_id]['name'] as $key => $value) {
			if ($files[$field_id]['error'][$key]===0 ) {
				if($this->getFileSize($files[$field_id]['size'][$key])<$maxImageSize){
					$tmp_name = $files[$field_id]['tmp_name'][$key];
					$newName = $name.'_'.$pixId;
					move_uploaded_file($tmp_name, $this->pathGalerias($path).DS.$newName.".jpg");
				}else{
					// El archivo es muy grande...
                    $this->_module->getResourceSingleton('Flashmsg')->error(App::xlat('La imagen %s es demasiado grande. El tamaño maximo permitido es de %s',array($value,$maxImageSize)));
                    return false;
                    break;
				}

				// Se almacenan las imagenes para crear 1 XML si se desea
					$this->pixXml[]=array('id'=>$pixId);
					$pixId++;
			}
		}

        // Deseas crear una galeria XML de las fotos ?
        	if($this->doXml) {

        		return $this->_module->getResourceSingleton('Xml')
									 ->setXml( @$name.'.xml' )
									 ->setXmlfiles( $this->pixXml )
									 ->setPath( $this->pathGalerias($path).DS.'xml' )
									 ->create();
			}

		return true;
	}

	/**
	 * Sacamos los N archivos mas nuevos de un directorio
	 * array $archivos | Array de archivos
	 * string $path | Path del directorio a obtener sus archivos
	 * int $maximo | Maximo numero de archivos a obtener
	 */
	function listNewestFiles($archivos=array(), $path=false, $maximo=5){
		if(!$path) return false;
			$i=1;
		// Obtenemos los archivo mas recientes
			foreach ($archivos as $archivo) {
				// Sacamos la fecha de creacion o acceso al archivo
				$archivos_fecha[]=date("Y-m-d", filemtime($path.DS.$archivo));
				$archivos_nombre[]=$archivo;
			}
			arsort($archivos_fecha);
			arsort($archivos_nombre);

			foreach ($archivos_fecha as $key=>$archivo) {
				$los_archivos[]=$archivos_nombre[$key];
				if($i>=$maximo){break;}
				$i++;
			}

		return $los_archivos;
	}

	function checkDir($path,$force=false) {
		if (!file_exists($path)) {
			if (!mkdir($path,0777,true)) {
				if ($force) return "errorforce"; //$this->_module->exception(App::xlat("No se ha podido crear el directorio: %s",array($path)));
				return false;
			}
		}
		if (!is_writable($path)) {
			if ($force)  return "errorforce";	//$this->_module->exception(App::xlat("No se puede escribir en el directorio: %s",array($path)));
			return false;
		}
		return true;
	}
	function forceDir($path) {
		return $this->checkDir($path,true);
	}

}