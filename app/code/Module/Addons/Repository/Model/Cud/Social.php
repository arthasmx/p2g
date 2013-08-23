<?php
require_once 'Module/Core/Repository/Model/Db/Actions.php';
class Module_Addons_Repository_Model_Cud_Social extends Module_Core_Repository_Model_Db_Actions{

  private $user     = null;
  private $business_session = null;
  private $params   = array();
  private $session  = null;

  private $folders       = null;
  private $folder_config = null;

  private $image_config = null;

  private $addons  = null;


  // shared code
  private $created       = null;

  function init(){
    $this->created          = date('Y-m-d H:i:s');
    $this->folder_config    = App::module('Articles')->getConfig('core','folders');
    $this->image_config     = App::module('Articles')->getConfig('core','articles');
    $this->user             = App::module('Core')->getModel('Namespace')->get( 'user' );
    $this->business_session = App::module('Core')->getModel('Namespace')->get( 'business' );
  }



  function update_field_value($type=null,$value=null,$ids=''){
    $ids = json_decode($ids);
    if( empty($type) || empty($value) || empty($ids) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_social_event_action_' . $type ) .'"}');
    }

    $this->set_table('site_social');

    $db = $this->get_db();
    $db->beginTransaction();

    try{
      foreach($ids AS $id){
        $data  = array( $type => $value );
        $this->table->update($data, array('id = ?' => $id ));
      }

      $db->commit();
      die('{"status":true, "message":"'. App::xlat('changes_applied_to_social_event_action_' . $type ) .'"}');

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_social_event_action_' . $type ) .'"}');
    }

  }



  function upload($image=null){
    $this->upload_image_shared_code();

    $current_image = $this->get_image_name($image);
    $uploaded_file = App::module('Core')->getModel('Filesystem')->plUploader_upload( $this->business_session->business['social']['folders']['base'] . $this->business_session->business['social']['folders']['gallery'].DS , $current_image );

    $image = App::module('Core')->getModel('Image');
    // thumbs (for admin section)
    $image->resize_image($uploaded_file, $this->image_config['thumb_width'], $this->image_config['thumb_height'],'crop');
    $image->saveImage( $this->business_session->business['social']['folders']['base'] . $this->business_session->business['social']['folders']['thumb'] . DS . $current_image, 80 );
    // thumbnails (mini-gallery)
    $image->resize_image($uploaded_file, $this->image_config['thumbnails_width'], $this->image_config['thumbnails_height'],'crop');
    $image->saveImage( $this->business_session->business['social']['folders']['base'] . $this->business_session->business['social']['folders']['thumbnails'] . DS . $current_image, 80 );
    // image
    $image->resize_image($uploaded_file, $this->image_config['gallery_width'], $this->image_config['gallery_height'],'exact');
    $image->saveImage( $uploaded_file, 90 );

    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  }

  function upload_main_pix(){
    $this->upload_image_shared_code();

    $path_to_save  = $this->business_session->business['social']['folders']['base'] . $this->business_session->business['social']['folders']['gallery'] .DS;
    $uploaded_file = App::module('Core')->getModel('Filesystem')->plUploader_upload( $path_to_save , "main.jpg" );

    $image = App::module('Core')->getModel('Image');
    // listing
    $image->resize_image($uploaded_file, $this->image_config['list_width'], $this->image_config['list_height'],'crop');
    $image->saveImage( $path_to_save . 'listing.jpg', 80 );
    // promote
    $image->resize_image($uploaded_file, $this->image_config['promote_width'], $this->image_config['promote_height'],'crop');
    $image->saveImage( $path_to_save . 'promote.jpg', 80 );
    // social
    $image->resize_image($uploaded_file, $this->image_config['social_width'], $this->image_config['social_height'],'crop');
    $image->saveImage( $path_to_save . 'social.jpg', 80 );
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
    // article
    $image->resize_image($uploaded_file, $this->image_config['article_width'], $this->image_config['article_height'],'exact');
    $image->saveImage( $path_to_save . 'article.jpg', 90 );

    @unlink($uploaded_file);
    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  }



  private function upload_image_shared_code(){
    $this->save_clean_due_image_upload(); // to assign images correctly, we need the social_event_id from DB so, let's check whether social_event has already been saved
    $this->create_required_folders();
  }

  private function save_clean_due_image_upload(){
    if( ! empty($this->business_session->business['social']['id']) ){
      return null;
    }

    try{
      $is_admin = App::module('Acl')->getModel('Acl')->is_logged_user_admin_from_session();
      $db       = $this->get_db();

      $this->set_table('site_social');
      $data = array(
          'city'     => $this->user->user['city'],
          'username' => empty( $is_admin ) ? '':$this->user->user['username'],
          'event'    => $this->created,
          'created'  => $this->created);
      $this->table->insert($data);
      $id = $db->lastInsertId();
      $this->business_session->business['social']['id'] = $id;
      return $id;

    }catch(Exception $e){
      die("{'error':'".$e->getMessage()."'}");
    }

  }

  private function create_required_folders($created=null){

    if( ! empty( $this->business_session->business['social']['folders'] ) ){
      return null; // already created
    }
    $file_system = App::module('Core')->getModel('Filesystem');
    $year_month  = App::module('Core')->getModel('Dates')->toDate(12,$created);

    $base = WP . DS . $this->folder_config['folder'] . DS . $this->folder_config['social'];

    $this_social_event_folder = $year_month . $this->business_session->business['social']['id'];

    // required folders
    $required_folders = array( 'path'       => $this_social_event_folder,
                               'gallery'    => $this_social_event_folder.DS.'gallery',
                               'thumbnails' => $this_social_event_folder.DS.'gallery'.DS.'thumbnails',
                               'thumb'      => $this_social_event_folder.DS.'gallery'.DS.'admin-thumbs' );

    foreach($required_folders AS $folder){
      $file_system->create_folder( $base, $folder );
    }

    $this->business_session->business['social']['folders'] = array_merge( $required_folders, array('url'=>str_replace(DS, '/', '/media/social' . $this_social_event_folder), 'base'=> $base) );
  }  

  private function get_image_name($image_name=null){
    $this->business_session->business['social']['file_name_counter'] = empty( $this->business_session->business['social']['file_name_counter'] ) ? 100 : $this->business_session->business['social']['file_name_counter'] + 1;

    if( ! empty( $this->business_session->business['social']['seo'] ) ){
      return $this->business_session->business['social']['seo'] . '_' . $this->business_session->business['social'][ 'file_name_counter'] . '.jpg';
    }

    // si esta vacio $image_name, es porque no ocupaste elegir business, pues no eres admin
    // entonces, el nombre de los archivos lo obtenemos de tu nombre de usuario
    $parser = App::module('Core')->getModel('Parser');

    $this->business_session->business['social']['seo'] = empty($image_name) ? $parser->string_to_seo( $this->user->user['name'] ) : $parser->string_to_seo($image_name);

    return $this->business_session->business['social']['seo'] . '_' . $this->business_session->business['social'][ 'file_name_counter'] . '.jpg';
  }



  function save($params=array()){
    $this->params = $params;

    if( $this->params['action']=='save' ){
      if( empty( $this->business_session->business['social']['id'] ) ){
        $resp = $this->new_social_event();
      }else{ // update
        $resp = $this->update_new_social_event();
      }
    }else{
      // edit
      $resp = $this->update_social_event();
    }

    return $resp;
  }

  private function new_social_event(){
    try{
      $is_admin = App::module('Acl')->getModel('Acl')->is_logged_user_admin_from_session();
      $db       = $this->get_db();

      $this->set_table('site_social');
      $data = array(
          'business'    => empty( $is_admin ) ? $this->user->user['username'] : $this->params['business'],
          'username'    => empty( $is_admin ) ? '':$this->user->user['username'],
          'city'        => $this->user->user['city'],
          'description' => $this->params['description'],
          'event'       => $this->params['event'] . date(' H:i:s'),
          'folder'      => $this->business_session->business['social']['folder'],
          'created'     => $this->created,
          'status'      => 'enabled');

      $this->table->insert($data);
      $id = $db->lastInsertId();
      $this->business_session->business['social']['id'] = $id;

      // Tags
      $this->set_table('site_social_tags');
      foreach($this->params['tags'] AS $tag){
        $data = array( 'social' => $id, 'tag'=> $tag );
        $this->table->insert($data);
      }

      $this->create_required_folders();

      return $id;

    }catch(Exception $e){
      die("{'error':'".$e->getMessage()."'}");
    }

  }

  // se utiliza cuando primero subes imagenes y despues grabas el formulario
  private function update_new_social_event(){
    try{
      $is_admin = App::module('Acl')->getModel('Acl')->is_logged_user_admin_from_session();
      $db       = $this->get_db();

      $this->set_table('site_social');
      $data = array(
          'business'    => empty( $is_admin ) ? $this->user->user['username'] : $this->params['business'],
          'description' => $this->params['description'],
          'event'       => $this->params['event'] . date(' H:i:s'),
          'folder'      => $this->business_session->business['social']['folder'],
          'status'      => 'enabled');
      $where = $this->table->getAdapter()->quoteInto('id = ?', $this->business_session->business['social']['id'] );
      $this->table->update($data, $where);

      // Tags
      $this->set_table('site_social_tags');
      foreach($this->params['tags'] AS $tag){
        $data = array( 'social' => $this->business_session->business['social']['id'], 'tag'=> $tag );
        $this->table->insert($data);
      }

      return 777;

    }catch(Exception $e){
      die("{'error':'".$e->getMessage()."'}");
    }

  }



  private function update_social_event(){
    $is_admin = App::module('Acl')->getModel('Acl')->is_logged_user_admin_from_session();
    try{

      $this->set_table('site_social');
      $data = array(
          'business'    => empty( $is_admin ) ? $this->user->user['username'] : $this->params['business'],
          'description' => $this->params['description'],
          'event'       => $this->params['event'] . date(' H:i:s') );
      $where = $this->table->getAdapter()->quoteInto('id = ?', $this->params['social_id'] );
      $this->table->update($data, $where);

      // Tags
      $this->set_table('site_social_tags');
      $where = $this->table->getAdapter()->quoteInto('social = ?', $this->params['social_id'] );
      $this->table->delete($where);

      foreach($this->params['tags'] AS $tag){
        $data = array( 'social' => $this->params['social_id'], 'tag'=> $tag );
        $this->table->insert($data);
      }

      return 777;

    }catch(Exception $e){
      die("{'error':'".$e->getMessage()."'}");
    }

  }

}