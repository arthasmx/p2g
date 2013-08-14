<?php

class Module_Core_Repository_Resource_Filter extends Core_Model_Repository_Resource {

	/**
	 * Hace un utf8_decode a un array
	 *
	 * @param String Array
	 */
	public function utf8_d($var=null){
		return true;
		if (	is_array($var) && count($var)>0	){
			foreach($var As $key => $val){
				$r[$key] = utf8_decode( $val );
			}
			return $r;
		}else {
			return utf8_decode($val);
		}
	}

	/**
	 * Ejecuta un utf8_encode para un array
	 *
	 * @param String Array
	 */
	public function utf8_e($var=null){
		return true;

		if (	is_array($var) && count($var)>0	){
			foreach($var As $key => $val){
				$r[$key] = utf8_encode( $val );
			}
			return $r;
		}else {
			return utf8_decode($val);
		}
	}

	/**
	 * Ejecuta un MYSQL_ESCAPE_STRING para un array y asi quitar apostrofos o comillas
	 *
	 * @param String Array
	 */
	public function escape($var=null){
		if (	is_array($var) && count($var)>0	){
			foreach($var As $key => $val){
				$r[$key] = mysql_escape_string( $val );
			}
			return $r;
		}else {
			return mysql_escape_string( $var );
		}
	}

	/**
	 * Da formato a una fecha con DIA y Mes
	 *
	 */
	public function formatDate($date=false){

		if ( $date ){

			$partes = explode('-', $date);
			return '<div id="date-format-1"><h1>'.$partes[0].'</h1>'.$partes[1].'</div>';
		}
	return false;
	}

	/**
	 * Crea una URL Friendly para un post, blog, articulo. Solamente deja las letras quitando todo simbolo
	 *
	 * @param String $texto
	 * @return String Seo
	 */
	public function seoUrl($texto) {
		// Quitamos acentos o ñ
			$noAllowed = array("á", "é", "í", "ó", "ú", "Á","É","Í","Ó","Ú","Ñ","ñ");
			$Allowed = array("a", "e", "i", "o", "u","A","E","I","O","U","N","n" );
			$string = str_replace($noAllowed, $Allowed, $texto);
		// Lo hacemos minuscula
			$string = strtolower($string);
		// Quitamos caracteres que no sean letras
			$string = rtrim(ereg_replace("[^A-Za-z0-9 ]", "", $string)," ");
		//Por ultimo, cambiamos los espacios por guines
			$string = str_replace(" ", "-", $string);

			return $string;
	}

	public function cutText($texto,$tamano = 150, $strip =true) {
		if ( $strip ){
			$texto =strip_tags($texto);
		}
		return substr($texto,0,$tamano);
	}

}