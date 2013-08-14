<?php

class Module_Core_Repository_Resource_Arrays extends Core_Model_Repository_Resource {

	/**
	 * Regresa un array asociativo, anteponiendo la opcion 0
	 *
	 * @param String Array
	 * @return Array
	 */
	public function toCombo($array=null,$llave='id',$desc='name',$option_0=false){
		if (	!is_array($array) || !count($array)>0	) return false;
		if(!$option_0) $option_0 = App::xlat('FORM_LABEL_choose_option');

		$parentCat[]= $option_0;
		foreach($array as $key){
			$parentCat[$key[$llave]]=$key[$desc];
		}
		return $parentCat;
	}

	/**
	 * Regresa un array asociativo
	 *
	 * @param String Array
	 * @return Array
	 */
	public function toAssociative($var=null,$llave='id',$desc='name'){
		if (	!is_array($var) || !count($var)>0	) return false;

		$parentCat = array();
		//$parentCat['']= App::xlat('Sectores disponibles');
		foreach($var as $key){
			$parentCat[$key[$llave]]=$key[$desc];
		}
		return $parentCat;
	}

	/**
	 * Convierte un array del tipo [0] => array( [indice] => 'valor'), [1] => array( [indice] => 'valor')
	 * al tipo asociativo para usarse en SELECTs u ordenarlo mejor y convertirlo en:
	 * Array(    [1] => 30 cm    [2] => 35 cm )
	 *
	 * @param Array $vars | El array con los datos
	 * @param String $index | El indice buscado
	 * @param Int $nuevo_index | El indice que le quieres dar, debe ser numerico, pues se incrementara en 1
	 * @return Array
	 */
	public function Associative($vars,$index='nombre',$nuevo_index=false){
		if (	!is_array($vars) || !count($vars)>0	) return false;

		$parentCat = array();
		foreach($vars as $var){
			if ($nuevo_index){
				$parentCat[$nuevo_index]=$var[$index];
				$nuevo_index++;
			}else{
				$parentCat[]=$var[$index];
			}
		}

		return $parentCat;
	}

	/**
	 * Formatea strings separados por comas a formato de IN
	 * Ejemplo: "roberto,mejia" regresa: 'roberto','mejia'. Este resultado se asigna a un IN
	 *
	 * @param String $parametros
	 * @return String
	 */
	function getIN($parametros){
		$_IN ='';
		$codigo = explode(',',$parametros );
		for($j=0; $j<=count($codigo)-1; $j++){
			$_IN .= "'".mysql_escape_string($codigo[$j])."',";
		}
		return rtrim($_IN,',');
	}

	/**
	 * Formatea arrays Key|Value para ser usados en un Query, dentro del WHERE para un IN
	 * Ejemplo: array('0' => 'roberto','1' => 'mejia'), regresa:  'roberto','mejia'. Este resultado se asigna a un IN
	 *
	 * @param Array $parametros
	 * @return String
	 */
	function arrayToIN($parametros=null){
		if ( !$parametros || !is_array($parametros) ) return false;

		$in='';
		foreach($parametros as $key){

			$r = explode(',',$key);
			if ( count($r)>0){
				for ($i=0; $i <= count($r)-1;$i++){
					$in .= "'".$r[$i]."',";
				}
			}else{
				$in .= "'".$key."',";
			}
		}
		return rtrim($in , ',');
	}

	/**
	 * Formatea arrays [n] { Key|Value } para ser usados en un Query, dentro del WHERE para un IN
	 * Ejemplo Array( [0] =>
	 * 					array ( '0' => 'roberto',
	 * 							'1' => 'mejia'
	 * 					)
	 * 			)
	 * Regresara: 'roberto','mejia'
	 *
	 * @param Array $parametros
	 * @return String
	 */
	function arrayToINdeep3($arr=null , $index){
		if ( !$arr || !$index ) return false;

		$sorted =array();
		foreach ($arr AS $key => $val){
				$sorted[] = $val[$index];
		}
		return $this->arrayToIN($sorted);
	}

	/**
	 * Formatea arrays Key|Value
	 * Ejemplo: array('0' => 'roberto','1' => 'mejia'), regresa:  roberto/mejia. o cualquier caracter en lugar de '/'
	 *
	 * @param Array $parametros
	 * @return String
	 */
	function arrayTo($parametros=null, $todos_menos_este=false, $caracter=false){
		if ( !$parametros || !is_array($parametros) ) return false;
		if ( !$caracter || !$todos_menos_este ) return false;

		// Quitamos del array el parametro definido en $todos_menos_este
			unset($parametros[$todos_menos_este]);
		// Si es un campo de rango, quitamos el parametro ligado
			if ( strstr($todos_menos_este,'_') ){
				$_tmp = explode('_',$todos_menos_este);
				unset( $parametros[$_tmp[0].'_1'] );
			}

		$url='';
		// Componemos la url
		foreach($parametros as $key => $value){

			if ( $value ){
				$url .= $caracter.$key.$caracter.$value;
			}
		}
		return rtrim($url , $caracter);
	}
}