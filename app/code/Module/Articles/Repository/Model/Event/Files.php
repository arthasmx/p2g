<?php
class Module_Articles_Repository_Model_Event_Files extends Core_Model_Repository_Model{

  private   $core           = null;
  private   $session        = null;
  private   $image_config   = null;
  private   $folder_config  = null;

  function init(){
    $this->core          = App::module('Core')->getModel('Abstract');
    $this->session       = App::module('Core')->getModel('Namespace')->get( 'event' );
    $this->folder_config = $this->_module->getConfig('core','folders');
  }

  function main_pix_preview(){
    $required_images = array('slider', 'article', 'promote', 'listing', 'aside', 'mobile');
    App::module('Core')->getModel('Namespace')->clear('event_mainpix');

    $path            = $this->session->event['folders']['gallery'].DS;
    $session         = App::module('Core')->getModel('Namespace')->get( 'event_mainpix' );
    $session->mainpix['path'] = $this->session->event['folders']['url'];

    foreach( $required_images AS $image ){
      if( App::module('Core')->getModel('Filesystem')->check_folder( $path.$image.'.jpg' ) ){
        $session->mainpix['images'][$image]=$image.'.jpg'; 
      }else{
        $session->mainpix=null;
        break;
      }
    }

    return $session->mainpix;
  }

  function load_gallery($page=1,$max_files_to_show=28){
    $session_gallery = App::module('Core')->getModel('Namespace')->get( 'event_files' );
    unset( $session_gallery->event_files['admin_gallery'] );

    $files = App::module('Core')->getModel('Filesystem')->get_files_from_path( $this->session->event['folders']['thumbnails'], array( "include" => "/\.jpg$/i") );
    if ( empty( $files ) ){
      return null;
    }

    $session_gallery->event_files['admin_gallery']['path']  = $this->session->event['folders']['url'];
    $session_gallery->event_files['admin_gallery']['files'] = $files;

    // sets file name counter (edit required)
      if( empty( $this->session->event['file_name_counter'] ) ){
        $this->session->event[ 'file_name_counter'] = count($files) + 100;
      }

    if( count($files) > $max_files_to_show ){
      return App::module('Core')->getModel('Filesystem')->events_paginate_files_in_folder('admin_gallery',$page,$max_files_to_show);
    }

    return array('files' => $files
                ,'path'  => $this->session->event['folders']['url'] );
  }

  function get_images_files_and_paginate($page=1){
    return $this->load_gallery($page);
  }

  function get_gallery_thumbnails($thumb=null){
    if( empty($thumb) ){
      return null;
    }
    $files = App::module('Core')->getModel('Filesystem')->get_files_from_path( $thumb, array( "include" => "/\.jpg$/i") );
    return empty($files)?
      null
    :
      $files;
  }



  function delete_image($image=null){
    if( empty( $this->session->event['article_id'] ) || empty($image) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_image_deleted') .'"}');
    }

    $file  = $this->session->event['folders']['gallery'].DS.$image;
    $thumb = $this->session->event['folders']['thumbnails'].DS.$image;
    $fSys  = App::module('Core')->getModel('Filesystem');

    if( ! $fSys->check_folder( $file ) || $fSys->delete($thumb)===false ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_image_deleted') .'"}');
    }
    $fSys->delete( $file );
    die('{"status":true, "message":"'. App::xlat('jSon_success_image_deleted') .'"}');
  }

}