<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Cities extends Module_Core_Repository_Model_Abstract {

  private $image_config   = null;
  private $folder_config  = null;
  private $town_session   = null;

  function init(){
    $this->folder_config = App::module('Articles')->getConfig('core','folders');
    $this->image_config  = App::module('Articles')->getConfig('core','articles');
    $this->session       = App::module('Core')->getModel('Namespace')->get( 'user' );
    $this->town_session  = App::module('Core')->getModel('Namespace')->get( 'town' );
  }

  function menu($city='mazatlan'){
    $select    = $this->_db->select()
                           ->from(array('st'  => 'site_towns'), array('name','seo','city') )
                           ->where('st.city = ?', $city)
                           ->where('st.seo != ?', $city)
                           ->where('st.status = ?', 'enabled')
                           ->order('st.sort ASC');

    $towns = $this->_db->query( $select )->fetchAll();

    if ( empty( $towns ) ){
      return null;
    }else{
      return $towns;
    }

  }

  function get_towns_from_city($city='mazatlan', $enabled_only = true){
    $select    = $this->_db->select()
                           ->from(array('st'  => 'site_towns') )
                           ->where('st.city = ?', $city)
                           ->where('st.seo != ?', $city)
                           ->order('st.sort ASC');

    if( $enabled_only===true ){
      $select->where('st.status = ?','enabled');
    }

    $towns = $this->_db->query( $select )->fetchAll();

    if ( empty( $towns ) ){
      return null;
    }else{
      return $towns;
    }

  }



  function town($town='town-was-not-given!',$section_style='edit', $status=null){
    $select = $this->_db->select()
                        ->from(array('st'  => 'site_towns') )
                        ->where('st.seo    = ?', $town);

    if( ! is_null($status) ){
      $select->where('st.status = ?', $status );
    }

    $town = $this->_db->query( $select )->fetch();

    if ( empty( $town ) ){
      App::module('Core')->exception( App::xlat('EXC_city_wasnt_found') . '[ERTOWN47]' );
    }else{
      switch($section_style){
        case 'frontend':
                $sections = $this->get_town_sections($town['seo']);
                break;
        case 'menu':
                $sections = $this->get_town_sections_for_menu($town['seo']);
                break;
        case 'edit':
                $sections = $this->get_town_sections_for_editing($town['seo']);
                break;
        case 'session':
                $sections = $this->get_full_session_sections_for_editing($town['seo']);
                break;
      }
      return array_merge($town, array('sections'=> $sections) );
    }

  }

  function get_tags( $town =null, $parse=null){
    $tags = $this->_db->select()
                            ->from(array('vt' => 'view_town_tags' ) )
                            ->where( 'vt.town = ?', $town )
                            ->group('vt.seo');

    $tags = $this->_db->query( $tags )->fetchAll();

    if( empty($tags) ){
      return null;
    }

    return empty($parse) ?
      $tags
    :
      $this->parse_tags_to_select( $tags );
  }

  function parse_tags_to_select($tags=null){
    $parsed = array();
    foreach ($tags AS $tag){
      $parsed[] = $tag['seo'];
    }
    return $parsed;
  }

  function get_addons($town ='town-was-not-given!', $parse=null){
    $select = $this->_db->select()
                              ->from(array('ta' => 'site_town_addons' ) )
                              // ->where( 'ta.status = "enabled"' )
                              ->where( 'ta.town = ?', $town)
                              ->order( 'ta.type' );

    $addons = $this->_db->query( $select )->fetchAll();

    if( empty($addons) ){
      return null;
    }

    return empty($parse) ?
      $addons
    :
      $this->parse_addons_to_select( $addons );
  }

  function parse_addons_to_select($addons=null){
    $parsed = array();
    foreach ($addons AS $addon){
      if( in_array($addon['type'], array('map','gallery') )){
        $parsed[$addon['type']] = $addon;
      }elseif($addon['type']=='link'){
        $parsed[$addon['type']][] = array_merge($addon, array('desc'=>$addon['description'], 'type'=>$addon['type'],'url'=>$addon['reference'] ) );
      }elseif($addon['type']=='video'){
        $parsed[$addon['type']][] = array_merge($addon, array('desc'=>$addon['description'], 'type'=>$addon['type'],'url'=> 'http://www.youtube.com/v/' . $addon['reference'] ) );
      }else{
        $parsed[$addon['type']][] = $addon;
      }
    }

    return $parsed;
  }



  function section($town='town-was-not-given!', $section='section-was-not-given!',$status='enabled'){
    $select = $this->_db->select()
                        ->from(array('sts'  => 'site_town_sections') )
                        ->join( array('st' => 'site_towns'), 'st.seo = sts.town',  array('city','name','folder') )
                        ->where('sts.town = ?', $town)
                        ->where('sts.section = ?', $section)
                        ->where('sts.status = ?', $status );

    $section = $this->_db->query( $select )->fetch();

    if ( empty( $section ) ){
      App::module('Core')->exception( App::xlat('EXC_section_wasnt_found') . '[ERTOWN142]' );
    }else{
      return $section;
    }

  }

  function get_sections($all=false){
    $sections = App::module('Core')->getModel('Parser')->get_enum_values_from_column('site_town_sections','section',array('mapa-turistico','image-gallery','como-llegar'));

    $used_sections = $this->get_town_sections_for_editing($this->town_session->town['seo']);

    $checked = array();
    foreach( $sections AS $key=>$value){
      $checked[] = array( 'status' => ( array_key_exists($key, $used_sections) ) ? 'available':'not-available',
                          'key'    => $key,
                          'value'  => $value);
    }
    return $checked;
  }

  function get_town_sections( $town=null ){
    if ( empty( $town ) ){
      return array();
    }
    $select = $this->_db->select()
                        ->from(array('sts'  => 'site_town_sections') )
                        ->join(array('st'  => 'site_towns'),'st.seo = sts.town',array('folder') )
                        ->where('sts.town   = ?', $town)
                        ->where('sts.status = ?', 'enabled');
    $sections = $this->_db->query( $select )->fetchAll();

    return empty($sections)? array(): $sections;
  }

  function get_town_sections_for_menu( $town=null ){
    if ( empty( $town ) ){
      return array();
    }
    $select = $this->_db->select()
                        ->from(array('st'  => 'site_town_sections'), array('id','town','section','status') )
                        ->where('st.town   = ?', $town)
                        ->where('st.status = ?', 'enabled');
    $sections = $this->_db->query( $select )->fetchAll();

    return empty($sections)? array(): $sections;
  }

  function get_town_sections_for_editing( $town=null ){
    if ( empty( $town ) ){
      return array();
    }
    $select = $this->_db->select()
                        ->from(array('st'  => 'site_town_sections'), array('section') )
                        ->where('st.town   = ?', $town)
                        ->where('st.status = ?', 'enabled');
    $sections = $this->_db->query( $select )->fetchAll();

    if( empty($sections) ){
      return array();
    }

    $daSection = array();
    foreach( $sections AS $section ){
      $daSection[$section['section']] = App::xlat( $section['section'] );
    }
    return $daSection;
  }

  function get_full_session_sections_for_editing( $town=null ){
    if ( empty( $town ) ){
      return array();
    }
    $select = $this->_db->select()
                        ->from(array('st' => 'site_town_sections'), array('section','article','status') )
                        ->where('st.town  = ?', $town);
    $sections = $this->_db->query( $select )->fetchAll();

    if( empty($sections) ){
      return array();
    }

    $daSection = array();
    foreach( $sections AS $section ){
      $daSection[$section['section']] = array( 'status'  => 'available'
                                              ,'article' => $section['article'] );
    }
    return $daSection;
  }


  function town_sort($city='not-given-city'){
    $select = $this->_db->select()
                        ->from(array('st'  => 'site_towns'), array( 'value' => 'MAX(st.sort) + 1' ) )
                        ->where('st.city = ?', $city);
    $sort = $this->_db->query( $select )->fetch();
    return (empty( $sort['value'] ) || $sort['value']<1 ) ? 1 : $sort['value'];
  }

  function town_folder($city=null,$seo=null){
    if( empty($city) ){
      return null;
    }
    if( empty($seo) || ( $city == $seo ) ){
      return "/$city/";
    }
    return "/$city/towns/$seo/";
  }

  private function get_media_folder(){
    return WP . DS . $this->folder_config['folder']. DS . $this->folder_config['cities'];
  }

  function set_town_folders($folder=null){

    $session->article['folders']['url']     = $folder;
    $folder = str_replace("/",DS,$folder);
    $session->article['folders']['article'] = $folder;

    $session->article['folders']['base']    = $this->get_media_folder();
    $session->article['folders']['path']    = $session->article['folders']['base'] . $folder;
    $session->article['folders']['gallery'] = $session->article['folders']['path'] . $this->folder_config['image'];
    $session->article['folders']['thumb']   = $session->article['folders']['gallery'] .DS. $this->folder_config['thumb'];
    $session->article['folders']['thumbnails']   = $session->article['folders']['gallery'] .DS. $this->folder_config['thumbnails'];

    return $session->article['folders'];
  }

  /* CITIES */
  function cities(){
    $select = $this->_db->select()
                        ->from(array('sc'  => 'site_cities') )
                        ->where('status = ?','enabled')
                        ->order('sc.name ASC');

    $cities = $this->_db->query( $select )->fetchAll();

    return empty( $cities ) ? null : $cities;
  }

  function load_gallery( $page=1,$max_files_to_show=28 ){
    $files = App::module('Core')->getModel('Filesystem')->get_files_from_path( $this->town_session->town['folders']['thumbnails'], array( "include" => "/\.jpg$/i") );
    if ( empty( $files ) ){
      return null;
    }

    // sets file name counter (edit required)
    if( empty( $this->town_session->town['file_name_counter'] ) ){
      $this->town_session->town[ 'file_name_counter'] = count($files) + 100;
    }

    if( count($files) > $max_files_to_show ){
      return App::module('Core')->getModel('Filesystem')->paginate_files_in_folder('admin_gallery',$page,$max_files_to_show);
    }

    return array('files' => $files
                ,'path'  => '/' . $this->folder_config['cities'] .'/' . $this->town_session->town['folders']['url'] );
  }

  function main_pix_preview(){
    if( empty( $this->town_session->town['folders'] ) ){
      return null;
    }

    $required_images = array('slider', 'article', 'promote', 'listing', 'aside', 'mobile');
//    App::module('Core')->getModel('Namespace')->clear('mainpix');

    $path    = $this->town_session->town['folders']['gallery'].DS;
//    $session = App::module('Core')->getModel('Namespace')->get( 'mainpix' );
//    $session->mainpix['path'] = $this->town_session->town['folders']['url'];
    $images['path'] = '/cities' . $this->town_session->town['folders']['url'];

    foreach( $required_images AS $image ){
      if( App::module('Core')->getModel('Filesystem')->check_folder( $path.$image.'.jpg' ) ){
//        $session->mainpix['images'][$image]=$image.'.jpg';
        $images['images'][$image] = $image.'.jpg';
      }else{
//        $session->mainpix=null;
        $images=null;
        break;
      }
    }

    //return $session->mainpix;
    return $images;
  }

  function upload_main_pix(){
    $this->image_config = App::module('Articles')->getConfig('core','articles');
    $path_to_save       = $this->town_session->town['folders']['gallery'] .DS;
    $uploaded_file      = App::module('Core')->getModel('Filesystem')->plUploader_upload( $path_to_save , "main.jpg" );

    $image = App::module('Core')->getModel('Image');
    // listing
    $image->resize_image($uploaded_file, $this->image_config['list_width'], $this->image_config['list_height'],'crop');
    $image->saveImage( $path_to_save . 'listing.jpg', 80 );
    // promote
    $image->resize_image($uploaded_file, $this->image_config['promote_width'], $this->image_config['promote_height'],'crop');
    $image->saveImage( $path_to_save . 'promote.jpg', 80 );
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
    //$image->resize_image($uploaded_file, $this->image_config['slider_thumb_width'], $this->image_config['slider_thumb_height'],'exact');
    //$image->saveImage( $path_to_save . $this->folder_config['thumbnails'] . DS . 'slider.jpg', 100 );
    // article
    $image->resize_image($uploaded_file, $this->image_config['article_width'], $this->image_config['article_height'],'exact');
    $image->saveImage( $path_to_save . 'article.jpg', 90 );

    @unlink($uploaded_file);
    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  }

  function image_to_gallery(){
    $current_image_name = $this->get_image_name();
    $uploaded_file      = App::module('Core')->getModel('Filesystem')->plUploader_upload( $this->town_session->town['folders']['gallery'] .DS , $current_image_name );

    $image = App::module('Core')->getModel('Image');
    // thumbs (for admin section)
    $image->resize_image($uploaded_file, $this->image_config['thumb_width'], $this->image_config['thumb_height'],'crop');
    $image->saveImage( $this->town_session->town['folders']['thumb'] . DS . $current_image_name, 80 );
    // thumbnails (mini-gallery)
    $image->resize_image($uploaded_file, $this->image_config['thumbnails_width'], $this->image_config['thumbnails_height'],'crop');
    $image->saveImage( $this->town_session->town['folders']['thumbnails'] . DS . $current_image_name, 80 );
    // image
    $image->resize_image($uploaded_file, $this->image_config['gallery_width'], $this->image_config['gallery_height'],'exact');
    $image->saveImage( $uploaded_file, 90 );

    $this->relate_addon_to_article('gallery');

    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  }

  private function get_image_name(){
    return $this->get_new_name('file','.jpg');
  }

  private function get_new_name($prefix=null,$extension=null){
    $this->town_session->town[ $prefix . '_name_counter'] = empty( $this->town_session->town[ $prefix . '_name_counter'] ) ? 100 : $this->town_session->town[ $prefix . '_name_counter'] + 1;
    return $this->town_session->town['seo'] . '_' . $this->town_session->town[ $prefix . '_name_counter'] . $extension;
  }



  function delete_image($image=null){
    if( empty( $this->town_session->town['article_id'] ) || empty($image) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_image_deleted') .'"}');
    }
  
    $file        = $this->town_session->town['folders']['gallery'].DS.$image;
    $thumb       = $this->town_session->town['folders']['thumbnails'].DS.$image;
    $thumb_admin = $this->town_session->town['folders']['thumb'].DS.$image;
    $fSys        = App::module('Core')->getModel('Filesystem');
  
    if( ! $fSys->check_folder( $file ) || $fSys->delete($thumb)===false || $fSys->delete($thumb_admin)===false ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_image_deleted') .'"}');
    }
    $fSys->delete( $file );
    die('{"status":true, "message":"'. App::xlat('jSon_success_image_deleted') .'"}');
  }

}
