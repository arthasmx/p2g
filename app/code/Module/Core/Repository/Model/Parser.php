<?php

class Module_Core_Repository_Model_Parser extends Core_Model_Repository_Model{

  public function string_to_seo($string) {
    $noAllowed = array("á", "é", "í", "ó", "ú", "Á","É","Í","Ó","Ú","Ñ","ñ");
    $Allowed = array("a", "e", "i", "o", "u","A","E","I","O","U","N","n" );
    $string = str_replace($noAllowed, $Allowed, $string);
    $string = strtolower($string);
    $string = rtrim(preg_replace("[^A-Za-z0-9 ]", "", $string)," ");
    $string = str_replace(" ", "-", $string);
    return $string;
  }

  public function truncate_string($string,$tamano = 150, $strip =true) {
    if ( $strip ){
      $string =strip_tags($string);
    }
    return substr($string,0,$tamano);
  }

  function truncate_text($text=null, $max=150,$strip_tags=false, $symbol='...'){
    if( empty($text) ){
      return null;
    }
    if ( $strip_tags===true ){
      $text = strip_tags($text);
    }

    $temp = substr($text, 0, $max);
    $last = strrpos($temp, " ");
    $temp = substr($temp, 0, $last);
    $temp = preg_replace("/([^\w])$/", "", $temp);
    return $temp . $symbol;
  }

  public function check_function_params($params=array(), $throw_exception = true){
    foreach ($params AS $param){
      if( empty($param) ){
        return empty($throw_exception) ?
          false
        : $this->_module->exception( App::xlat('EXC_missing_arguments_at_adding_comments') );
      }
    }
    return true;
  }

  public function get_ip() {
    if (@$_SERVER['HTTP_X_FORWARDED_FOR']) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }

  /**
   * Debido a problemas con el Multibyte de caracteres especiales (como los acentuados), se optó temporalmente
   * por quitarlos.
   * @todo: Hacer este metodo MULTIBYTE
   */
  public function string_to_array($string=null, $allowed_string=array()){
    if(empty($string)){
      return null;
    }
    $noAllowed = array("á", "é", "í", "ó", "ú", "Á","É","Í","Ó","Ú");
    $Allowed   = array("a", "e", "i", "o", "u","A","E","I","O","U" );
    $string    = str_replace($noAllowed, $Allowed, $string);

    return array('separated'=> $this->parse_allowed_string($string, $allowed_string) );
  }
    private function parse_allowed_string($str=null,$allowed_string=array()){
      $strings = explode(" ", $str);
      if( count($strings) <=1 ){
        return str_split( $str );
      }

      $parsed_string = array();
      foreach($strings AS $string){
        if( array_search($string,$allowed_string)===false ){
          $parsed_string = array_merge($parsed_string, array('space'=>''), str_split( $string ) );
        }else{
          $parsed_string[] = $string;
        }
      }
      return $parsed_string;
    }


  function get_enum_values_from_column($table=null,$field=null,$to_remove=array()){
    if( empty($table) || empty($field) ){
      return array();
    }

    $db = App::module('Core')->getModel('Db')->get();

    $sections = $db->query( "SHOW COLUMNS FROM $table WHERE Field='$field'" )->fetch();
    preg_match('/^enum\((.*)\)$/', $sections['Type'], $matches);

    foreach( explode(',', $matches[1]) as $value ){
      $section = trim( $value, "'" );
      $enum[$section] = App::xlat($section);
    }

    if( ! empty($to_remove) ){
      foreach ($to_remove AS $remove){
        unset($enum[$remove]);
      }
    }

    return $enum;
  }
}