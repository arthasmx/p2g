<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Audio extends Module_Core_Repository_Model_Abstract {

  private $cfg = null;

  function init(){
    $this->cfg = App::module('Addons')->getConfig('core','audio');
  }

  function get_preaching($page=1){
    return $this->get_audio_files( $this->cfg['category']['preach'], $this->cfg['preach']['max_results'], $page, TRUE );
  }

  // Allows only MP3 Files
  function get_preach($id=null, $check_if_file_exist = null, $enabled_only=null){
    if( empty($id)){
      return null;
    }
    $select = $this->_db->select()
                   ->from( array('f'  => 'files') )
                   ->join( array('fd' => 'files_details'), 'f.id = fd.doc_id',  array('name','description','category','thumbnail','lang') )
                   ->where('f.doctype = ?', "audio" )
                   ->where('f.id = ?', $id)
                   ->where('fd.lang = ?', App::locale()->getLang() );

    if( $enabled_only === true ){
      $select->where('f.status = ?', "enabled");
    }

    $preach = $this->_db->query($select)->fetch();
    if( empty($preach) ){
      return null;
    }

    if( empty($check_if_file_exist) ){
      return $preach;
    }else{ // Make sure audio file is in disk
      $year_month          = App::module('Core')->getModel('Dates')->toDate(8, $preach['event_date'] );
      $file                = $preach['reference'];

      // gets the Disk's file name
      eval("\$path_to_file = ".$this->cfg['path'].";");
      $path_to_file .= $preach['category'] 
                       .DS
                       .$year_month
                       .DS
                       .$file;

      if( empty($path_to_file) || ! file_exists( $path_to_file ) ){
        return null;
      }
      return array_merge($preach, array('file' => App::media( "/{$preach['category']}/$year_month/$file" ) ) );
    }
  }

  function get_audio_files($category = null, $max_results = 10, $current_page = null, $enabled_only=null){
    if( empty($category) ){
      return null;
    }

    $select = $this->_db->select()
                   ->from( array('f'  => 'files') )
                   ->join( array('fd' => 'files_details'), 'f.id = fd.doc_id',  array('name','description','category','thumbnail','lang') )
                   ->where('f.doctype = ?', "audio" )
                   ->where('fd.category = ?', $category )
                   ->where('fd.lang = ?', App::locale()->getLang() )
                   ->order('f.event_date DESC');

    if( $enabled_only === true ){
      $select->where('f.status = ?', "enabled");
    }

    $audio_files = $this->setItems_per_page($max_results)->setPaginator_page($current_page)->paginate_query( $select );
    if( empty( $audio_files ) ){
      return null;
    }

    // gets related files
    foreach($audio_files['items'] AS $key=>$audio){
      $audio_files['items'][$key]['related_files'] = $this->get_related_files($audio['id']);
    }
    return $audio_files;
  }

  private function get_related_files($reference=null, $field = "file_id"){
    if( empty( $reference ) ){
      return null;
    }

    $select = $this->_db->select()
                   ->from( array('fr'  => 'files_relation') )
                   ->where("fr.{$field} = ?", $reference );

    $related_files = $this->_db->query($select)->fetch();
    return empty($related_files) ? 
      null
    :
      $related_files;
  }

}