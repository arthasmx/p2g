<?php
class Module_Article_Repository_Helper_Imagen extends Core_Model_Repository_Helper {

	var $articulo = false;

	function getArticulo() {
		if (!$this->articulo) {
			$this->_module->exception("Debe especificar el array de un articulo mediante setArticulo(\$articulo)");
		}
		return $this->articulo;
	}

	function imagenPrincipal() {
		$articulo=$this->getArticulo();
		$galeria = $this->_module->getModel('Galerias')->setTipo($articulo['tipo']);

		// Obtenemos la imagen definida por default
			if(!$foto = $articulo['foto']){
				$fotos= $galeria->cargar($articulo['articulo']);
				$foto=$fotos[0];
			}
		$path=$galeria->getUrl($articulo['articulo'],$galeria->getTipo() ).'/';

		return sprintf(
					"<div class='imagen'>
						<a href='%s' class='border-1-white' ><img src='%s' width='80' height='60' class='image' /></a>
					</div>",
					App::base("/articles/".$articulo['seo']),
					$path.$foto
				);

	}

	function principal() {
		return $this->imagenPrincipal();
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function cabecera() {
		return $this->imagenX(1);
	}
	function izquierda() {
		return $this->imagenX(2);
	}
	function derecha() {
		return $this->imagenX(3);
	}

	function imagenX($tipo=1) {
		$articulo=$this->getArticulo();
		$galeria = $this->_module->getModel('Galerias');

		// Obtenemos la imagen definida por default
			if(!$foto = $articulo['foto']){
				$fotos= $galeria->cargar($articulo['articulo']);
				$foto=$fotos[0];
			}

		$ancho=568;
		$alto=100;
		switch($tipo){
			case 1:
			default:
					$class='';
					$path=$galeria->getUrl($articulo['articulo']).'/4_3/568x100/';
					break;
			case 2:
					$ancho=200;
					$alto=100;
					$path=$galeria->getUrl($articulo['articulo']).'/4_3/200x100/';
					$class="float-left pad-right-10";
					break;
			case 3:
					$ancho=200;
					$alto=100;
					$path=$galeria->getUrl($articulo['articulo']).'/4_3/200x100/';
					$class="float-right pad-left-10";
					break;
		}

		return sprintf(
					"<div class='imagen $class'>
						<img src='%s' width='".$ancho."' height='".$alto."' class='image' />
					</div>",
					$path.$foto
				);

	}

}
