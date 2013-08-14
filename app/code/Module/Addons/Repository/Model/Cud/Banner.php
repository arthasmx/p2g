<?php
require_once 'Module/Core/Repository/Model/Db/Actions.php';
class Module_Addons_Repository_Model_Cud_Banner extends Module_Core_Repository_Model_Db_Actions{

  function relate_banner_to_business($username=null,$banner=null){
    $date   = date('Y-m-d H:i:s');
    $folder = App::module('Core')->getModel('Dates')->toDate( 10, date("Y-m-d") ).DS;
    $path   = WP.DS.'media'.DS.$banner.DS;

    if( App::module('Core')->getModel('Filesystem')->create_folder( $path, $folder ) === true){

      $db = $this->get_db();
      $this->set_table('site_banner');

      $data = array( 'username' => $username, 'folder' => $folder, 'created' => $date);
      $this->table->insert($data);
      $id = $db->lastInsertId();

      if( empty($id) ){
        return null;
      }
      return $path.$folder;

    }else{
      die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Folder ['. $folder .'] was not created."}, "id" : "id"}');
    }

  }


  function banner_status_change($status='disabled',$username=null){
    $this->set_table('site_banner');
    $data  = array( 'status' => $status );
    $where = $this->table->getAdapter()->quoteInto('username = ?', $username);
    $this->table->update($data,$where);
    return true;
  }

  function update_field_value($type=null,$value=null,$ids=''){
    $ids = json_decode($ids);
    if( empty($type) || empty($value) || empty($ids) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_banner_action_' . $type ) .'"}');
    }

    $this->set_table('site_banner');

    $db = $this->get_db();
    $db->beginTransaction();

    try{
      foreach($ids AS $id){
        $data  = array( $type => $value );
        $this->table->update($data, array('id = ?' => $id ));
      }

      $db->commit();
      die('{"status":true, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_banner_action_' . $type ) .'"}');

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_banner_action_' . $type ) .'"}');
    }
  }

}