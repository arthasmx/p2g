<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Core_Repository_Model_Arrays extends Module_Core_Repository_Model_Abstract {

  function asociative_array_to_mysql_IN($array=array()){
    if ( empty($array) ) return false;

    $mysql_in_format='';
    foreach($array as $key){

    	 $r = explode(',',$key);
    	 if ( count($r)>0){
    		  for ($i=0; $i <= count($r)-1;$i++){
    			  $mysql_in_format .= "'".$r[$i]."',";
    		  }
    	 }else{
    		  $mysql_in_format .= "'".$key."',";
    	 }
    }
    return rtrim($mysql_in_format , ',');
  }

  function array_push_assoc($array, $key, $value){
    $array[$key] = $value;
    return $array;
  }

  function params_key_exists($given_params, $expected_params=array()){
    foreach ($expected_params as $key){
      if ( ! array_key_exists( $key, $given_params) || empty($given_params[$key]) ){
        return false;
      }
    }
    return true;
  }

  function multidimensional_array_to_mysql_IN($array=array(), $desired_key = null,$is_number=false ){
    if ( empty($array) || empty($desired_key) ) return false;

    $mysql_in_format = null;
    foreach($array as $value){
      $mysql_in_format .= ($is_number===true) ?
        $value[$desired_key] . ","
      :
        "'" . $value[$desired_key] . "',";
    }
    return rtrim($mysql_in_format , ',');
  }
  
  function array_to_tree($array=array(),$level=null ,$tag='ol', $tag_id=null,$route=null){
    if( ! is_array($array) || empty($tag_id) ){
      return null;
    }

    $tree = null;
    foreach ( $array as $node ) {
      if ($node['parent'] == $level ) {
        $tree = empty($route)?
          $tree . "<li>" . $node['name'] . $this->array_to_tree( $array, $node['id'], $tag, $tag_id,$route  ) . "</li>"
        :
          $tree . "<li> <a href='".App::base($route . $node['id'] )."' data-id='". $node['id'] ."'>" . $node['name'] . $this->array_to_tree( $array, $node['id'], $tag, $tag_id,$route  ) . " </a> </li>";
      }
    }

    return empty($tree) ?
     ''
    :
      "<$tag class='$tag_id treeview-gray'>". $tree . "</$tag>";
  }

}