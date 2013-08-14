<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Banner extends Module_Core_Repository_Model_Abstract {

  private   $image_config   = null;
  private   $folder_config  = null;

  function init(){
    $this->folder_config = App::module('Articles')->getConfig('core','folders');
    $this->image_config  = App::module('Articles')->getConfig('core','articles');
  }

  function upload( $username=null ){
    // $username is provided when admins uploads banner to business, otherwise, we use session's username
    if( empty($username) ){
      $session  = App::module('Core')->getModel('Namespace')->get( 'user' );
      $username = $session->user['username'];
    }

    $business = App::module('Core')->getModel('Namespace')->get( 'business' );
    if( empty($business->business['banner']) ) {
      $file_path = App::module('Addons')->getModel('Cud/Banner')->relate_banner_to_business($username,$this->folder_config['banners']);
    }else{
      $file_path = WP.DS.'media'.DS.$this->folder_config['banners'].DS.$business->business['banner']['folder'].DS;
    }

    $uploaded_file = App::module('Core')->getModel('Filesystem')->plUploader_upload( $file_path , $username . ".jpg" );
    $image         = App::module('Core')->getModel('Image');

    // banner
    $image->resize_image($uploaded_file, $this->image_config['banner_width'], $this->image_config['banner_height'],'exact');
    $image->saveImage( $uploaded_file, 100 );

    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  }

  function get_preview($username=null){
    if( empty($username) ){
      $session  = App::module('Core')->getModel('Namespace')->get( 'user' );
      $username = $session->user['username'];
    }

    $select = $this->_db->select()->from(array('sb'  => 'site_banner'  ) )->where('sb.username = ?', $username );
    $banner = $this->_db->query( $select )->fetch();

    if ( empty( $banner ) ){
      return null;
    }else{
      $business = App::module('Core')->getModel('Namespace')->get( 'business' );
      $business->business['banner'] = $banner;
      return $banner;
    }

  }

  function preview($username=null){
    $banner = $this->get_preview($username);
    return empty( $banner ) ?
        null
      :
        $banner['folder'] . $banner['username'] . '.jpg?'.date('s');
  }

}