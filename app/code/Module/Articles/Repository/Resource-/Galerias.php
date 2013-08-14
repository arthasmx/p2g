<?php
require_once('Module/Catalogo/Repository/Resource/Abstract.php');
class Module_Catalogo_Repository_Resource_Productos_Galerias extends Module_Catalogo_Repository_Resource_Abstract {

	function getImagenPrincipal($producto) {
		if(!$producto) return false;

		$query = "SELECT if(( CHAR_LENGTH(`foto`) = 0),NULL,`foto`) AS foto FROM catalogo WHERE id='".mysql_escape_string($producto)."'";
		try{
			if ($foto=$this->_db->query($query)->fetch()){
				return $foto['foto'];
			}
		}catch (Exception $e){}
		return false;
	}

	/**
	 * Establece la imagen por defecto
	 * @return boolean
	 */
	function setImagenPrincipal() {
		if(!$this->producto_id || !$this->imagen_principal) return false;

		$query = "UPDATE catalogo SET foto='".mysql_escape_string($this->imagen_principal)."' WHERE id='".mysql_escape_string($this->producto_id)."'";
		try{
			$this->_db->query($query);
			return true;
		}catch (Exception $e){}
	}


}