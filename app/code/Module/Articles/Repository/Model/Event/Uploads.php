<?php
class Module_Articles_Repository_Model_Event_Uploads extends Core_Model_Repository_Model{

  private   $core           = null;
  private   $session        = null;
  private   $image_config   = null;
  private   $folder_config  = null;

  function init(){
    $this->core          = App::module('Core')->getModel('Abstract');
    $this->session       = App::module('Core')->getModel('Namespace')->get( 'event' );
    $this->folder_config = $this->_module->getConfig('core','folders');
  }

  function zip_gallery(){
    $uploaded_file_name = 'gallery.zip';
    $uploaded_file      = App::module('Core')->getModel('Filesystem')->plUploader_upload( $this->session->event['folders']['path'], $uploaded_file_name );

    $this->relate_addon_to_article( 'gallery' );
    App::module('Core')->getModel('Zip')->unzip( $this->session->event['folders']['gallery'] , $uploaded_file );
    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  }

  function gallery(){
    $current_image_name = $this->get_image_name();
    $this->image_config = $this->_module->getConfig('core','articles');
    $uploaded_file      = App::module('Core')->getModel('Filesystem')->plUploader_upload( $this->session->event['folders']['gallery'] .DS , $current_image_name );

    $image = App::module('Core')->getModel('Image');
    // thumbs (for admin section)
    $image->resize_image($uploaded_file, $this->image_config['thumb_width'], $this->image_config['thumb_height'],'crop');
    $image->saveImage( $this->session->event['folders']['thumb'] . DS . $current_image_name, 80 );
    // thumbnails (mini-gallery)
    $image->resize_image($uploaded_file, $this->image_config['thumbnails_width'], $this->image_config['thumbnails_height'],'crop');
    $image->saveImage( $this->session->event['folders']['thumbnails'] . DS . $current_image_name, 80 );
    // image
    $image->resize_image($uploaded_file, $this->image_config['gallery_width'], $this->image_config['gallery_height'],'exact');
    $image->saveImage( $uploaded_file, 90 );

    $this->relate_addon_to_article( 'gallery' );

    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  }

  function main_pix(){
    $this->image_config = $this->_module->getConfig('core','articles');
    $path_to_save       = $this->session->event['folders']['gallery'] .DS;
    $uploaded_file      = App::module('Core')->getModel('Filesystem')->plUploader_upload( $path_to_save , "main.jpg" );

    $image = App::module('Core')->getModel('Image');
// listing
    $image->resize_image($uploaded_file, $this->image_config['list_width'], $this->image_config['list_height'],'crop');
    $image->saveImage( $path_to_save . 'listing.jpg', 80 );
// promote
    $image->resize_image($uploaded_file, $this->image_config['event_width'], $this->image_config['event_height'],'crop');
    $image->saveImage( $path_to_save . 'promote.jpg', 90 );
// aside SMALL
    $image->resize_image($uploaded_file, $this->image_config['aside_width'], $this->image_config['aside_height'],'crop');
    $image->saveImage( $path_to_save . 'aside.jpg', 80 );
// aside BIG
    $image->resize_image($uploaded_file, $this->image_config['aside_big_width'], $this->image_config['aside_big_height'],'crop');
    $image->saveImage( $path_to_save . 'aside-big.jpg', 80 );
// mobile
    $image->resize_image($uploaded_file, $this->image_config['mobile_width'], $this->image_config['mobile_height'],'crop');
    $image->saveImage( $path_to_save . 'mobile.jpg', 100 );
// slider
    $image->resize_image($uploaded_file, $this->image_config['slider_width'], $this->image_config['slider_height'],'crop');
    $image->saveImage( $path_to_save . 'slider.jpg', 100 );
// slider-thumb (content slider preview)
    $image->resize_image($uploaded_file, $this->image_config['slider_thumb_width'], $this->image_config['slider_thumb_height'],'exact');
    $image->saveImage( $path_to_save . $this->folder_config['thumbnails'] . DS . 'slider.jpg', 100 );
// article
    $image->resize_image($uploaded_file, $this->image_config['article_width'], $this->image_config['article_height'],'exact');
    $image->saveImage( $path_to_save . 'article.jpg', 90 );

    @unlink($uploaded_file);
    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  }



  private function get_image_name(){
    return $this->get_new_name('file','.jpg');
  }

  private function get_new_name($prefix=null,$extension=null){
    $this->session->event[ $prefix . '_name_counter'] = empty( $this->session->event[ $prefix . '_name_counter'] ) ? 100 : $this->session->event[ $prefix . '_name_counter'] + 1;
    return $this->session->event['seo'] . '_' . $this->session->event[ $prefix . '_name_counter'] . $extension;
  }

  private function relate_addon_to_article( $addon_type=null ){
    if( empty($this->session->event['addons'][$addon_type]) && ! empty($addon_type) ){
      $last_inserted_id = $this->_module->getModel('Cud/Events')->relate_addon_to_article($this->session->event['article_id'], $addon_type);
      if( ! empty($last_inserted_id) ){
        $this->session->event['addons'][$addon_type] = $last_inserted_id;
      }
    }
  }  

}