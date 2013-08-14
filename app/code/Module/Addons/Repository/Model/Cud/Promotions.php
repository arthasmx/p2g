<?php
require_once 'Module/Core/Repository/Model/Db/Actions.php';
class Module_Addons_Repository_Model_Cud_Promotions extends Module_Core_Repository_Model_Db_Actions{

  function add_promotion($promotion=null){
    require_once 'Zend/Date.php';
    $date      = new Zend_Date();
    $timestamp = $date->getTimestamp();
    $folder    = App::module('Core')->getModel('Dates')->toDate( 10, date("Y-m-d") ).DS;
    $path      = WP.DS.'media'.DS.$promotion.DS;
    $session   = App::module('Core')->getModel('Namespace')->get( 'user' );
    $picture_name = $session->user['username'].$timestamp.'.jpg';
    $picture_path = '/media/'.$promotion.'/'.str_replace('\\','/',$folder);

    if( App::module('Core')->getModel('Filesystem')->create_folder( $path, $folder ) === true){
      App::module('Core')->getModel('Filesystem')->create_folder( $path, $folder.DS.'mobile' ); // mobile
      App::module('Core')->getModel('Filesystem')->create_folder( $path, $folder.DS.'tablet' ); // tablet

      $db = $this->get_db();
      $this->set_table('site_promotions');

      $data = array( 'city' => $session->user['city'], 'username' => $session->user['username'], 'picture' => $picture_name, 'path' => $picture_path, 'created' => date("Y-m-d H:i:s")  );
      $this->table->insert($data);
      $id = $db->lastInsertId();

      if( empty($id) ){
        return null;
      }

      $business = App::module('Core')->getModel('Namespace')->get( 'business' );
      $business->business['promotions'] = array( 'id' => $id, 'picture' => $picture_name, 'www_path' => $picture_path, 'path' => $path.$folder);
      return $business->business['promotions'];

    }else{
      die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Folder ['. $folder .'] was not created."}, "id" : "id"}');
    }

  }

  function promotion_status_change($username=null,$status='disable'){
    $this->set_table('site_promotions');
    $data  = array( 'status' => $status );
    $where = $this->table->getAdapter()->quoteInto('username = ?', $username);
    $this->table->update($data,$where);
    return true;
  }



  function save($params=array(), $username=null){
    $this->set_table('site_promotions');
    $user = App::module('Core')->getModel('Namespace')->get( 'user' );

    // En caso de establecer esta promocion como principal, marcaremos las otras como NO principales
    if( $params['main']=='yes' ){
      $data  = array( 'main' => 'no');
      $where = $this->table->getAdapter()->quoteInto('username = ?', $user->user['username']);
      $this->table->update($data, $where);
    }

    $data = array( 'description'     => $params['description']
                   ,'start'          => $params['start']
                   ,'finish'         => $params['finish']
                   ,'main'           => $params['main']
                   ,'onclick_action' => $params['onclick_action']
                   ,'onclick_url'    => $params['onclick_url']
                   ,'status'         => 'enabled');

    $session = App::module('Core')->getModel('Namespace')->get( 'business' );
    if( ! empty($session->business['promotions']) ){ // creates a promotion without image

      $where = $this->table->getAdapter()->quoteInto('id = ?', $session->business['promotions']['id']);
      $this->table->update($data, $where);
      $id = $session->business['promotions']['id'];

    }else{
      if( empty($username) ){
        $username = $user->user['username'];
      }

      // @todo: CITY se esta poniendo por default. cuando un admin usa este metodo, debe especificar la ciudad
      $db = $this->get_db();
      $this->table->insert(array_merge($data,array('username' => $username)));
      $id = $db->lastInsertId();

    }

    return $id;
  }



  function update_field_value($type=null,$value=null,$ids=''){
    $ids = json_decode($ids);
    if( empty($type) || empty($value) || empty($ids) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_promote_action_' . $type ) .'"}');
    }

    $this->set_table('site_promotions');

    $db = $this->get_db();
    $db->beginTransaction();

    try{
      foreach($ids AS $id){
        $data  = array( $type => $value );
        $this->table->update($data, array('id = ?' => $id ));
      }

      $db->commit();
      die('{"status":true, "message":"'. App::xlat('jSon_changes_applied' ) .'"}');

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_promotions_action_' . $type ) .'"}');
    }
  }

}