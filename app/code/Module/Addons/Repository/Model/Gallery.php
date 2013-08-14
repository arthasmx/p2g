<?php
class Module_Addons_Repository_Model_Gallery extends Core_Model_Repository_Model{

  private $_gallery_basepath = null;
  private $_thumbnails_path  = null;
  private $_allowed_ext      = null;
  private $_article_path     = null; // Article creation date (year / month)

  private $file                   = null;
  private $mimes                  = null;
  private $gallery_folder         = null;
  private $uploaded_file_size     = null;
  private $uploaded_file_max_size = 10485760;


  function init() {
    $config                   = $this->_module->getConfig('core','gallery');
    $this->gallery_folder     = WP . DS . App::getConfig('media_folder') . $config['basepath'];
    $this->mimes              = App::module('Core')->getConfig('core', 'mime');

    $this->_gallery_basepath  = $config['basepath'];
    $this->_thumbnails_path   = $config['thumbnails'];
    $this->_allowed_ext       = $config['extension'];
  }

  function get_gallery_base_path(){
    return $this->_gallery_basepath;
  }

  function get_thumbnails_base_path(){
    if( empty($this->_article_path) ){
      return false;
    }
    return DS . App::getConfig('media_folder'). $this->get_gallery_base_path().$this->_article_path.$this->_thumbnails_path;
  }

  function set_article_base_path($article = null){
    if( empty($article['created']) || empty($article['id'])){
      return null;
    }
    $this->_article_path = App::module('Core')->getModel('Dates')->toDate(8, $article['created']) . DS . $article['id'];
  }

  function get_gallery_files($article = null){
    if ( empty($article) ){
      return null;
    }
    $this->set_article_base_path($article);

    $thumbs            = $this->get_thumbnails_base_path();
    $filesys           = App::module('Core')->getModel('Filesystem');
    $allowed_regex_ext = $this->get_allowed_extensions_regex();

    return $filesys->get_files_from_path($thumbs, $this->_article_path, array( "include" => $allowed_regex_ext, "paginate"=>true) );
  }

  function get_allowed_extensions() {
    return $this->_allowed_ext;
  }

  function get_allowed_extensions_regex() {
    $regex = array();
    foreach ($this->get_allowed_extensions() as $ext=>$allowed) {
      $regex[$ext] = $allowed['regex'];
    }
    return $regex;
  }


/*
  function image_upload($file=null){
    $checks = $this->check_uploads_settings($file);
    if( is_array($checks) ){
      return json_encode($checks);
    }

    $was_uploaded = $this->upload();

    return empty($was_uploaded) ?
    json_encode(array('error'=> 'Could not save uploaded file.' . 'The upload was cancelled, or server error encountered'))
    :
    json_encode(array('success'=>true));
  }
*/

  function represent($id=null,$file=null){
    $checks = $this->check_uploads_settings($id,$file);
    if( is_array($checks) ){
      return json_encode($checks);
    }

    /*
     * @ Necesito el nombre que tomara el archivo del articulo para representarlo....
     * Que tal si al guardar el articulo tambien se guarde en session el detalle del articulo (sin TEXTO) para asi utilizar las funciones de arriba o para
     * no tener que estar pasando tantos parametros ?
     *
     * SEEE CAMBIARLO A USO DE SESSIONES!!
     * asi podremos sacar facil el ID que se le dio
     * no necesitamos enviarlo como parametro
     * usariamos la fecha del articulo en lugar de la fecha actual (cuando creas la carpeta, porque puede que no concuerde...)
     *
     * PERFECTO, CODE REFACTOR HERE!
     */
    $was_uploaded = $this->upload();
    if( empty($was_uploaded) ){
      return json_encode(array('error'=> 'Could not save uploaded file.' . 'The upload was cancelled, or server error encountered'));
    }

    /*
     * Falta el proceso para redimenzionar la imagen :D
     * */
    return json_encode(array('success'=>true));
  }

  private function check_uploads_settings($id=null,$file=null){
    if( empty($id) || empty($file) ){
      return array('error' => "No files were uploaded.");
    }

    $this->gallery_folder = App::module('Core')->getModel('Dates')->toDate(8, date('Y-m-d') ) . DS . $id;

    if( $this->check_directory() === false ){
      return array('error' => "Server error. Upload directory isn't writable.");
    }

    // Getting content length from server
    if( ! empty($_SERVER["CONTENT_LENGTH"]) ){
      $this->uploaded_file_size = $_SERVER["CONTENT_LENGTH"];

      if ($this->uploaded_file_size == 0) {
        return array('error' => 'File is empty');
      }

      if ($this->uploaded_file_size > $this->uploaded_file_max_size) {
        return array('error' => 'File is too large');
      }
    }

    $pathinfo  = pathinfo($file);
    $filename  = $pathinfo['filename'];
    $extension = @$pathinfo['extension'];		// hide notices if extension is empty

    if( ! array_key_exists($extension, $this->mimes) ){
      $these = implode(', ', array_keys($this->mimes));
      return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
    }

    $this->file = $this->gallery_folder . $file;
    return true;
  }

  private function check_directory() {
    if ( ! file_exists($this->gallery_folder)) {
      if ( ! mkdir($this->gallery_folder,0777,true)) {
        return false;
      }
    }
    if ( ! is_writable($this->gallery_folder)) {
      return false;
    }
    return true;
  }

  private function upload(){
    $input    = fopen("php://input", "r");
    $temp     = tmpfile();
    $realSize = stream_copy_to_stream($input, $temp);
    fclose($input);

    if ($realSize != $this->uploaded_file_size ){
      return false;
    }

    $target = fopen($this->file, "w");
    fseek($temp, 0, SEEK_SET);
    stream_copy_to_stream($temp, $target);
    fclose($target);
    return true;
  }

}