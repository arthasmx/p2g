<?php
//require_once('Module/Blog/Repository/Model/Abstract.php');
class Module_Article_Repository_Model_Galerias extends Core_Model_Repository_Model {

	private $_tipo = false;
	private $_basepath = array();					// Ruta base de las galerias
	private $_baseurl = array();					// Url base de las galerias
	private $_permitidas = false;				// Array de extensiones permitidas
//	private $_dimensiones_minimas = false;		// Array con dimensiones minimas de las imagenes a subir
//	private $_thumbnails = false;				// Array con dimensiones de thumbnails que crearemos para cada imagen subida
//	public  $path_imagen_principal = false;		// Ruta de la imagen principal

	function init() {
		// Procesamos la configuracion y la cargamos en atributos
			$config=$this->_module->getConfig('core','articulos_galeria'); // Cargamos la configuracion del ini
			// Rutas
				foreach ($config['tipo'] AS $key=>$value){
					try {
						eval("\$this->_baseurl['".$key."']=".$value['baseurl'].";"); 		// Evaluamos esta configuracion por si incluye variables
						eval("\$this->_basepath['".$key."']=".$value['basepath'].";"); 		// Evaluamos esta configuracion por si incluye variables
					} catch (Exception $e) {} 										// No queremos lanzar excepciones si hay errores en la evaluacion
				}
			// Extensiones permitidas
				$this->_permitidas=$config['permitidas'];
	}

// Getters #############################################################################################

	function getPath($articulo,$tipo='actividad') {
		return $this->_basepath[$tipo].DS;
	}

	function setTipo($tipo=false) {
		if(!$tipo) $this->_module->exception("Se necesita especificar el tipo de articulo,actividad para obtener sus imagenes!");
		$this->_tipo=$tipo;
		return $this;
	}
	function getTipo() {
		return $this->_tipo;
	}
	
	/**
	 * Obtenemos la imagen por default del articulo
	 */
	function imagenPrincipal($articulo=false){
		if ( $foto=$this->_module->getResource('Blog/Galerias')->getImagenPrincipal($articulo)){
			return $foto;
		}
		return false;
	}

	function getUrl($articulo,$tipo='actividad') {
		return $this->_baseurl[$tipo];
	}

	function getPermitidas() {
		return $this->_permitidas;
	}

	function getFormatosPermitidos() {
		$formatos=array();
		// $this->getExtensionesPermitidas() contiene un array con todas las extensiones que permitiremos en la galeria
		foreach ($this->getPermitidas() as $permitida) {
			$formatos[]=$permitida['nombre'];
		}
		return array_unique($formatos);
	}

	function getMimesPermitidos() {
		$mimes=array();
		// $this->getPermitidas() contiene un array con todas las extensiones que permitiremos en la galeria
		foreach ($this->getPermitidas() as $ext=>$permitida) {
			$mimes[$ext]=$permitida['mime'];
		}
		return $mimes;
	}

	function getExpresionesPermitidas() {
		$expresiones=array();
		// $this->getPermitidas() contiene un array con todas las extensiones que permitiremos en la galeria
		foreach ($this->getPermitidas() as $ext=>$permitida) {
			$expresiones[$ext]=$permitida['regex'];
		}
		return $expresiones;
	}

	function getDimensionesMinimas($indice=false) {
		if ($indice && isset($this->_dimensiones_minimas[$indice])) {
			return (int)$this->_dimensiones_minimas[$indice];
		}
		return $this->_dimensiones_minimas;
	}

	function getThumbnailsConfig($aspectRatio=false,$indice=false) {
		$aspectRatio=str_replace(":","_",$aspectRatio);
		if ($aspectRatio && isset($this->_thumbnails[$aspectRatio])) {
			if ($indice && isset($this->_thumbnails[$aspectRatio][$indice])) {
				return (int)$this->_thumbnails[$aspectRatio][$indice];
			} else {
				return (int)$this->_thumbnails[$aspectRatio];
			}
		}
		return $this->_thumbnails;
	}

// Métodos principales #############################################################################################

	function cargar($articulo) {
		// Obtenemos la ruta en la que se encuentran las imagenes de éste articulo
		
echo '<pre>'; print_r($this->getTipo()); echo '</pre>';
exit;		
		
			$path=$this->getPath($articulo);
			echo '<pre>'; print_r($path); echo '</pre>';
			exit;
		// Accedemos al recurso filesystem del core para obtener los archivos que hay en la ruta
			$filesys = App::module('Core')->getResource('Filesystem');
			try {
				// Generamos el array con las expresiones regulares de las extensiones permitidas
					$expresiones_permitidas=$this->getExpresionesPermitidas();

				// Cargamos las imagenes del directorio
					$imagenes = $filesys->getFilesFromPath($path,array(
						"include"	=>	$expresiones_permitidas, // Solo cargaremos los archivos del directorio cuyas extensiones esten entre las permitidas para la galeria
					));
				// Devolvemos las imagenes
					if (sizeof($imagenes)<1) {
						return false;
					} else {
						return $imagenes;
					}
			} catch (Exception $e) {
				return false; // Evitamos que se lance la excepcion en caso de que no exista la ruta o no se pueda leer.
			}
	}

	function borrarImagen($articulo,$imagen) {
		if (stristr($articulo,"..") || stristr($imagen,"..")) {
			echo "false";
			exit;
		}

		// Obtenemos la ruta en la que se encuentran las imagenes de éste articulo
			$path=$this->getPath($articulo);
		// Eliminamos el archivo si existe
			if (!file_exists($path.DS.$imagen)) {
				$resultado=true;
			} else {
				$filesys = App::module('Core')->getResource('Filesystem');
				$resultado = $filesys->delete($path.DS.$imagen);
			}
		// Salida en función del resultado
			if ($resultado) {
				$this->_borraThumbnails($path,$imagen);
				echo "true";
			} else {
				echo "false";
			}
			exit;
	}

	function dimensionesImagen($articulo,$imagen) {
		$path=$this->getPath($articulo);
		$archivo_imagen=$path.DS.$imagen;
		if (is_readable($archivo_imagen)) {
			$dim=getimagesize($archivo_imagen);
			if (!is_array($dim)) return false;
			// Calculamos el aspect ratio
			return array("width"=>$dim[0],"height"=>$dim[1],"aspect"=>$this->_calculaAspectRatio($dim[0],$dim[1]),"aspect_decimal"=>($dim[0]/$dim[1]));
		}
		return false;
	}

	/**
	 * Devuelve un array con la configuración de los thumbnails, pero en este caso las dimensiones de la configuracion
	 * se cambian por las de las imagenes si existen
	 * @param $articulo
	 * @param $imagen
	 * @return array
	 */
	function dimensionesThumbnails($articulo,$imagen) {
		$thumbnails=$this->getThumbnailsConfig();
		$path=$this->getPath($articulo);
		if (is_array($thumbnails)) {
			foreach ($thumbnails as $aspect=>$thumbs) {
				foreach ($thumbs as $resolucion=>$dimensiones) {
					$archivo_imagen=$path.DS.$aspect.DS.$resolucion.DS.$imagen;
					if (is_readable($archivo_imagen)) {
						$dim=@getimagesize($archivo_imagen);
						$thumbnails[$aspect][$resolucion]=array("width"=>$dim[0],"height"=>$dim[1]);
					} else {
						$thumbnails[$aspect][$resolucion]=false;
					}
				}
			}
		}
		return $thumbnails;
	}

	function cargarImagen(array $imagen,$articulo) {
		if (!isset($imagen['name']) || !isset($imagen['type']) || !isset($imagen['tmp_name'])) {
			return false;
		}

		$filesystem=App::module("Core")->getResource("Filesystem");
		/*
			 // Datos que se reciben en el array
			[name] => img_136-1.gif
			[type] => image/gif
			[tmp_name] => L:\_DESARROLLO\webserver\tmp\php8B39.tmp
			[error] => 0
			[size] => 227
		*/

			if (!is_readable($imagen['tmp_name'])) {
				$this->_module->exception("No se ha podido cargar la imagen");
			}

			// Comprobamos el mime
				$mimes_permitidos=$this->getMimesPermitidos();
				$permitida=false;
				$extension=false;
				foreach($mimes_permitidos as $ext=>$mime) {
					if ($imagen['type']==$mime) {
						$permitida=true;
						$extension=$ext;
					}
				}
				if (!$permitida) {
					$this->_module->exception("No se permiten imagenes del tipo ".$imagen['type']);
				}

			// Comprobamos dimensiones
				list($width, $height, $type, $attr) = getimagesize($imagen['tmp_name']);
				if ($width<$this->getDimensionesMinimas('width')) {
					$this->_module->exception(App::xlat("La imagen debe de ser al menos de %spx de ancho, ésta imagen tiene %spx",array($this->getDimensionesMinimas('width'),$width)));
				}
				if ($height<$this->getDimensionesMinimas('height')) {
					$this->_module->exception(App::xlat("La imagen debe de ser al menos de %spx de alto, ésta imagen tiene %spx",array($this->getDimensionesMinimas('height'),$height)));
				}

			// Comprobamos directorio
				$path=$this->getPath($articulo);
				try {
					$ok=$filesystem->forceDir($path); // Solicitamos al filesystem que cree el directorio si no existe, si falla, lanzará una excepcion
				} catch (Exception $e) {
					$this->_module->exception($e->getMessage(),$e->getCode());
				}

			// Generamos el nombre de la imagen y del archivo de destino
				// Aprovechamos que antes almacenamos la extension para el mime type del archivo que se ha subido.
				$nombre_imagen=date('Ymdhis').".".$extension;
				$archivo_imagen=$path.DS.$nombre_imagen;

			// Si el archivo de destino existe, lo borramos
				if (file_exists($archivo_imagen)) $filesystem->delete($archivo_imagen);

			// Almacenamos la imagen en su resolución original
				if (!move_uploaded_file($imagen['tmp_name'],$archivo_imagen)) {
					$this->_module->exception(App::xlat("No se ha podido copiar la imagen a: %s",array($archivo_imagen)));
				}

			// Creamos thumbnails
				try {
					$this->_creaThumbnails($path,$nombre_imagen);
				} catch (Exception $e) {
					$this->_module->exception(App::xlat("Error al generar los thumbnails: %s",array($e->getMessage())));
				}

				return $nombre_imagen;
	}

	function recortarImagen($articulo,$imagen_a_recortar,$basewidth,$baseheight,$x1,$y1,$x2,$y2) {
		$filesystem=App::module("Core")->getResource("Filesystem");

		// Ruta base de trabajo
			$path=$this->getPath($articulo);

		// Abrimos la imagen a recortar
			$archivo_imagen_a_recortar=$path.DS.$imagen_a_recortar;
			if (!file_exists($archivo_imagen_a_recortar) || !is_readable($archivo_imagen_a_recortar)) {
				$this->_module->exception("No se ha podido leer la imagen original en ".$archivo_imagen_a_recortar);
			}
			// Extension
				$pathinfo=pathinfo($archivo_imagen_a_recortar);
				$extension=$pathinfo['extension'];

		// Obtenemos las dimensiones de la imagen a recortar
			$dimensiones_imagen_a_recortar=$this->dimensionesImagen($articulo,$imagen_a_recortar);
			if (!$dimensiones_imagen_a_recortar) {
				$this->_module->exception("No se han podido obtener las dimensioens de la imagen original");
			}

		// Calculamos dimensiones de recorte que vamos a realizaren base a los parámetros recibidos
			$crop=array();
			// Las coordenadas de recorte recibidas son en base a las dimensiones recibidas en los parámetros, tendremos que escalarlas a las dimensiones originales
			$factorx=$dimensiones_imagen_a_recortar['width']/$basewidth;
			$factory=$dimensiones_imagen_a_recortar['height']/$baseheight;
			$crop["x1"]=$x1*$factorx;
			$crop["x2"]=$x2*$factorx;
			$crop["y1"]=$y1*$factory;
			$crop["y2"]=$y2*$factory;
			$crop["width"]=intval($crop["x2"]-$crop["x1"]);
			$crop["height"]=intval($crop["y2"]-$crop["y1"]);
			// Comprobamos que las dimensiones resultantes no sean inferiores a las minimas
				$dimensiones_minimas=$this->getDimensionesMinimas();
				if ($crop["width"]<$dimensiones_minimas['width'] || $crop['height']<$dimensiones_minimas['height']) {
					$this->_module->exception(App::xlat("Dimensiones de la imagen resultante: %s",array($crop['width']."x".$crop['height']."px")).PHP_EOL.App::xlat("Dimensiones mínimas permitidas: %s",array($dimensiones_minimas['width']."x".$dimensiones_minimas['height']."px")).PHP_EOL.PHP_EOL.App::xlat("Por favor, seleccione un área mayor."));
				}

		// Comprobamos el directorio donde vamos a grabar la imagen
			try {
				$ok=$filesystem->forceDir($path); // Solicitamos al filesystem que cree el directorio si no existe, si falla, lanzará una excepcion
			} catch (Exception $e) {
				$this->_module->exception($e->getMessage(),$e->getCode());
			}

		// Generamos el nombre de la imagen y del archivo de destino
			// El nombre sera el mismo para mantener la posicion, pero se cambiara la parte del nombre correspondiente a la version
			$tmp=(array)explode("_",str_replace(".".$extension,"",$imagen_a_recortar));
			$nombre_imagen=$tmp[0]."_".date('Ymdhis').".".$extension;
			$archivo_imagen=$path.DS.$nombre_imagen;

		// Si el archivo de destino existe, lo borramos
			if (file_exists($archivo_imagen)) $filesystem->delete($archivo_imagen);

		// Redimensionamos imagen y la grabamos con el nuevo nombre
			require_once("Xplora/Thumb.php");
			$thumb=new Xplora_Thumb($archivo_imagen_a_recortar);
			$thumb->crop($crop['x1'],$crop['y1'],$crop['width'],$crop['height']);
			$thumb->save($archivo_imagen,80);

			// Creamos thumbnails
				try {
					$this->_creaThumbnails($path,$nombre_imagen);
				} catch (Exception $e) {
					$this->_module->exception(App::xlat("Error al generar los thumbnails: %s",array($e->getMessage())));
				}

		return $nombre_imagen;
	}

// Métodos privados #############################################################################################

	private function _creaThumbnails($path,$nombre_imagen) {
		// Comprobamos imagen original
			$archivo_imagen=$path.DS.$nombre_imagen;
			if (!file_exists($archivo_imagen) || !is_readable($archivo_imagen)) {
				$this->_module->exception(App::xlat("No se puede leer la imagen original en: %s",array($archivo_imagen)));
			}
		// Cargamos librerias
			require_once("Xplora/Thumb.php");
			$filesystem=App::module("Core")->getResource("Filesystem");
		// Creamos los thumbnails
			$thumbnails=$this->getThumbnailsConfig();
			foreach ($thumbnails as $aspectRatio=>$thumbs) {
				foreach ($thumbs as $resolucion=>$dimensiones) {
					if (!isset($dimensiones['width']) || !isset($dimensiones['height'])) {
						$this->_module->exception(App::xlat("Error en la configuración, no se pueden obtener las dimensiones del thumbnail: %s.%s",array($aspectRatio,$resolucion)));
					}
					$dir_destino=$path.DS.$aspectRatio.DS.$resolucion;
					$filesystem->forcedir($dir_destino);

					$thumb=new Xplora_Thumb($archivo_imagen);
					$thumb->resize($dimensiones['width'],$dimensiones['height']);
					$thumb->save($dir_destino.DS.$nombre_imagen,80);
				}
			}
			return true;
	}

	private function _borraThumbnails($path,$nombre_imagen) {
		// Cargamos librerias
			$filesystem=App::module("Core")->getResource("Filesystem");
		// Creamos los thumbnails
			$thumbnails=$this->getThumbnailsConfig();
			foreach ($thumbnails as $aspectRatio=>$thumbs) {
				foreach ($thumbs as $resolucion=>$dimensiones) {
					if (!isset($dimensiones['width']) || !isset($dimensiones['height'])) {
						$this->_module->exception(App::xlat("Error en la configuración, no se pueden obtener las dimensiones del thumbnail: %s.%s",array($aspectRatio,$resolucion)));
					}
					$dir_destino=$path.DS.$aspectRatio.DS.$resolucion;
					$archivo_thumb=$dir_destino.DS.$nombre_imagen;
					$filesystem->delete($archivo_thumb);
				}
			}
			return true;
	}

	private function _calculaAspectRatio($width,$height) {
		$mcd=$this->_getMCD((int)$width,(int)$height);
		return ($width/$mcd.":".$height/$mcd);
	}

	private function _getMCD($a,$b) {
     	if ($b == 0) return $a;
     	return $this->_getMCD($b, $a % $b);
     }

}