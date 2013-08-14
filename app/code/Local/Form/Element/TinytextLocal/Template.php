<?php

require_once "Xplora/Form/Element/Tinytext/Template/Interface.php";

class Local_Form_Element_TinytextLocal_Template implements Xplora_Form_Element_Tinytext_Template_Interface {

	protected $attribs = null;
	protected $cfg	= null;
	protected $validKey = array(
								'theme',
								'plugins',
								'language',
								'ask',

								'theme_advanced_statusbar_location',
								'theme_advanced_toolbar_location',
								'theme_advanced_toolbar_align',

								'theme_advanced_buttons1',
								'theme_advanced_buttons2',
								'theme_advanced_buttons3',
								'theme_advanced_buttons1_add',
								'theme_advanced_buttons2_add',
								'theme_advanced_buttons3_add',

								'theme_advanced_resizing',
								'extended_valid_elements'
								);

	public function getAttrib($key=null) {
		if ($key) {
			if ( isset($this->attribs[$key]) ) {
				return $this->attribs[$key];
			}
		}
		return false;
	}

	public function __construct($attribs=null) {
		$this->attribs=$attribs;
	}

	/**
	 * Este metodo debe retornar el codigo de la plantilla a utilizar
	 *
	 */
	public function get() {}

	/**
	 * Crea las opciones de TinyMCE personalizadas
	 * Ejecuta la funcion si $this->getAttrib es un Array con opciones
	 *
	 * @return Array
	 */
	protected function getJs() {
		$i=0;
		$js='';
		$invalidKeys =false;

		foreach (	$this->attribs as $key => $vale		){

			if ( in_array( $key , $this->validKey ) ){
				$js .= $this->includeAttribs($key, $vale);
			}
			else {
				$invalidKeys[$i+=1] = 'La llave <b>'.$key.'</b> es invalida.<br>';
			}
		}

		if (!$invalidKeys){
			if ( substr( $js,strlen($js)-1 ) ==','){
				return substr( $js,0,strlen($js)-1 );
			}
			return $js;
		}
		else {
			return false;
		}
	}

	protected function includeAttribs($key,$val) {
		$params = explode(',',$val);
		$js = '';

		if ( is_bool($val) ){
			if ( $val>0 )//TRUE
				$js = $key.' : true,';
			else
				$js = $key.' : false,';
			return $js;
		}
		else{
			$comma = ',';
			for ($i=0; $i <= count($params)-1; $i++){

				if ($i == count($params)-1) {$comma = '';}
				if ( substr($params[$i],0,1) != "-"	){ // El valor del key que comienza con "-", indica que lo queremos quitar
						$js .= $params[$i].$comma;
				}
			}
		}
		return $key. ' : "'.$js.'",'.PHP_EOL;
	}
}

?>