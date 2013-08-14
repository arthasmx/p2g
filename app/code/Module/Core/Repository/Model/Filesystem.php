<?php
class Module_Core_Repository_Model_Filesystem extends Core_Model_Repository_Model{

  private $file	 = null;
  private $mimes = null;


  private $session = null;

  function init(){
    $this->mimes          = $this->_module->getConfig('core', 'mime');
  }

  // File MUST exist, otherwise it'll throw an exception
  // The objective of this class is to handle existing files, that's why U have to set FULLPATH file name
  // $file MUST has a relative path ( predicaciones/folder/folder/filename.extension )
  function set_file($file = null){
    if ( empty($file) ){
      $this->_module->exception( App::xlat('EXC_file_is_not_set') );
    }

    // All uploaded files MUST be located in this folder (audio, video, files)
    $media_folder = WP .DS. App::getConfig('media_folder');
    $this->file = $media_folder .DS. $file;
    if( ! $this->is_found() ){
      $this->_module->exception( App::xlat('EXC_file_wasnt_found') );
    }

    return $this;
  }

  function get_file(){
    if ( empty($this->file) ) {
      $this->_module->exception( App::xlat('EXC_file_is_not_set') );
    }
    return $this->file;
  }

  function get_file_info(){
    $basic             = pathinfo( $this->get_file() );
    $basic['mime']     = $this->get_mime($basic['extension']);
    $advanced          = stat( $this->get_file() );
    return array_merge( $basic, array( 'size'=>$advanced['size'], 'atime'=>$advanced['atime'], 'mtime'=>$advanced['mtime'] ) );
  }

  function is_found(){
    return ( empty($this->file) || ! file_exists( $this->file ) ) ?
      false
    :
      true;
  }

  function force_to_download(){
    $file_info = $this->get_file_info();
    $file_to_download = $file_info['dirname'] .DS. $file_info['basename'];

    header("Content-Description: File Transfer");
    header("Content-Type: {$file_info['mime']}");
    header('Content-Length: ' . $file_info['size'] );
    header('Content-Disposition: attachment; filename="' . $file_info['basename'] . '"');
    header('Content-Transfer-Encoding: binary');

    $stream = fopen($file_to_download, 'rb');
    while(!feof($stream)) {
      print fread($stream, 1024);
      flush(); ob_flush();
    }
    fclose ($stream);
    exit;
  }




//*******************************************************
// GENERIC METHODS: They can be used without setting file

  function delete($file=null){
    if ( empty($file) || !@unlink($file) ) return false;
    return true;
  }

  function get_mime($extension=null){
    return (empty($extension) || ! array_key_exists($extension, $this->mimes) ) ?
      "unknown/$extension"
    :
      $this->mimes[$extension];
  }

  function get_any_file_details($file=null){
    return empty($file)? false : pathinfo($file);
  }

  function get_files_from_path($full_path, array $options=array()) {
    $full_path = str_replace('\\', DS , $full_path );
    if (!is_dir($full_path) || !is_readable($full_path) || !$dir_handle = opendir($full_path)) {
      $this->_module->exception("No se ha podido leer la ruta ".$full_path);
    }

    $includes = array();
    $excludes = array();
    if (! empty($options['include'])) $includes = (array)$options['include'];
    if (! empty($options['exclude'])) $excludes = (array)$options['exclude'];

    $files=array();
    while($file = readdir($dir_handle)){
      if($file == "." || $file == ".."){
        continue;
      }
      // Revisamos si el archivo tiene coincidencias en includes o en excludes
      $en_includes=false;
      foreach($includes as $include) {
        try {
          if (preg_match($include,$file,$matches)) {
            $en_includes=true;
          };
        } catch (Exception $e) {
        };
      }
      $en_excludes=false;
      foreach($excludes as $exclude) {
        try {
          if (preg_match($exclude,$file,$matches)) {
            $en_excludes=true;
          };
        } catch (Exception $e) {
        };
      }
      if ($en_includes && !$en_excludes) array_push($files, $file);
    }

    return empty($files)?
      null
    :
      $files;
  }

  function paginate_files_in_folder($index=null, $current=1, $max_images = 28){
    if( empty($this->session) || empty($index) ){
      $this->session = App::module('Core')->getModel('Namespace')->get( 'files' );
    }

    if( empty($this->session->files[$index]) ){
      return null;
    }

    $length = count($this->session->files[$index]['files']);
    $pages  = ceil($length / $max_images);
    $start  = ceil( ($current - 1) * $max_images);
    // $finish = ($start +  $max_images) - 1;
    // $finish = ( $length > $finish )? $finish : $length;

    $paginated = array_slice($this->session->files[$index]['files'], $start, $max_images);
    if( empty($paginated) ){
      return null;
    }

    $this->session->files[$index]['paginate'] = $paginated;
    $this->session->files[$index]['html']     = $this->pagination_links($current, $pages);

    return array('files' => $this->session->files[$index]['paginate']
                ,'html'  => $this->session->files[$index]['html']
                ,'path'  => $this->session->files[$index]['path']);
  }

  function events_paginate_files_in_folder($index=null, $current=1, $max_images = 28){
    if( empty($this->session) || empty($index) ){
      $this->session = App::module('Core')->getModel('Namespace')->get( 'event_files' );
    }

    if( empty($this->session->event_files[$index]) ){
      return null;
    }

    $length = count($this->session->event_files[$index]['files']);
    $pages  = ceil($length / $max_images);
    $start  = ceil( ($current - 1) * $max_images);

    $paginated = array_slice($this->session->event_files[$index]['files'], $start, $max_images);
    if( empty($paginated) ){
      return null;
    }

    $this->session->event_files[$index]['paginate'] = $paginated;
    $this->session->event_files[$index]['html']     = $this->pagination_links($current, $pages);

    return array('files' => $this->session->event_files[$index]['paginate']
                ,'html'  => $this->session->event_files[$index]['html']
                ,'path'  => $this->session->event_files[$index]['path']);
  }


  function pagination_links($page, $pages){
    if( $pages <= 1 ){ return null; }
    $page_param_tpl = App::base("");
    $pages_to_render = '<div class="f-pagination"> <span class="numeric-pages">';

    for( $pagination = 1; $pagination <= $pages; $pagination++ ){
      $pages_to_render .= "<span class='page numeric". (($pagination == $page)? ' current':null) ."'><a data-page='".$pagination."' class='paginate-link'>".$pagination."</a></span>";
    }

    return $pages_to_render . "</span> </div>";
  }

  function create_folder($path=null, $folder=null) {
    $error_msg = App::xlat('EXC_filesystem_folder_not_available') . '<br />Launched at method create_folder, file Repository/Model/Filesystem';

    if ( empty($folder) || empty($path) || ! is_writable($path) ){
      App::module('Core')->exception( $error_msg );
    }

    if( ! file_exists($path . $folder) ){
      if( ! mkdir($path . DS . $folder, 0777, true) ){
        App::module('Core')->exception( $error_msg );
      }
    }

    return true;
  }

  function check_folder($path=null){
    return ( empty($path) || ! file_exists($path) || ! is_writable($path) ) ? null : true;
  }



//  function plUploader_upload($path=null,$folder=null,$file_name=null){
  function plUploader_upload($path=null,$file_name=null){
    $this->plUploader_headers();

    $check_path = $this->check_folder($path);
    if( empty($check_path)  ){
      die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory [233]."}, "id" : "id"}');
    }

    // Get parameters
    $chunk    = ! empty($_REQUEST["chunk"])  ? intval($_REQUEST["chunk"])  : 0;
    $chunks   = ! empty($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

    $filePath = $path . $file_name;

    // Look for the content type header
    if (! empty($_SERVER["HTTP_CONTENT_TYPE"])){
      $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
    }
    if (! empty($_SERVER["CONTENT_TYPE"])){
      $contentType = $_SERVER["CONTENT_TYPE"];
    }

    // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
    if (strpos($contentType, "multipart") !== false) {
      if (! empty($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        // Open temp file
        $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
        if ($out) {
          // Read binary input stream and append it to temp file
          $in = fopen($_FILES['file']['tmp_name'], "rb");

          if ($in) {
            while ($buff = fread($in, 4096))
              fwrite($out, $buff);
          } else
            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
          fclose($in);
          fclose($out);
          @unlink($_FILES['file']['tmp_name']);
        } else
          die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
      } else
        die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
    } else {
      // Open temp file
      $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
      if ($out) {
        // Read binary input stream and append it to temp file
        $in = fopen("php://input", "rb");

        if ($in) {
          while ($buff = fread($in, 4096))
            fwrite($out, $buff);
        } else
          die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

        fclose($in);
        fclose($out);
      } else
        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
    }

    // Check if file has been uploaded
    if (!$chunks || $chunk == $chunks - 1) {
      // Strip the temp .part suffix off
      rename("{$filePath}.part", $filePath);
    }

    // Return JSON-RPC response. File uploaded successfully
    // die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

    return $filePath;


  }

  // HTTP headers for no cache etc
  function plUploader_headers(){
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
  }

  function captcha_folder(){
    $week = date('W');
    $this->create_folder(WP . DS .'media'. DS . 'captchas'.DS, $week);
    return array('dir' => WP . DS .'media'. DS . 'captchas'.DS. $week, 'url'=> App::base("/media/captchas/$week/") );
  }

}