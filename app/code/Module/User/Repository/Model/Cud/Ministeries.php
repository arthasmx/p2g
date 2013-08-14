<?php
require_once 'Module/Core/Repository/Model/Db/Actions.php';
class Module_User_Repository_Model_Cud_Ministeries extends Module_Core_Repository_Model_Db_Actions{

  function save($params=array()){
    if( empty($params['user']) ){
      die('{"status":false}');
    }

    $db = $this->get_db();
    $db->beginTransaction();

    try{
      $this->set_table('user_ministeries');
      $where = $this->table->getAdapter()->quoteInto('username = ?', $params['user']);
      $this->table->delete($where);

      if( ! empty($params['min_chosen']) ){
        foreach($params['min_chosen'] AS $ministery){
          $data = array( 'ministery' => $ministery, 'username'  => $params['user'] );
          $this->table->insert($data);
        }
      }

      $db->commit();
      die('{"status":true}');

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false}');
    }

  }

  function save_multiple($params=array()){
    if( empty($params['ids']) || empty($params['mini']) ){
      die('{"status":false}');
    }

    $db = $this->get_db();
    $db->beginTransaction();
    $this->set_table('user_ministeries');

    try{
      foreach($params['mini'] AS $ministery){
        foreach($params['ids'] AS $user){
          $data = array( 'ministery' => $ministery, 'username'  => $user );
          $this->table->insert($data);
        }
      }

      $duplicated = $this->get_duplicated_ministeries();
      if( ! empty($duplicated) ){
        foreach($duplicated AS $duplicate){
          $where[] = $this->table->getAdapter()->quoteInto('ministery = ?', $duplicate['ministery']);
          $where[] = $this->table->getAdapter()->quoteInto('username = ?', $duplicate['username']);
          $where[] = $this->table->getAdapter()->quoteInto('id != ?', $duplicate['id']);

          $this->table->delete($where);
          $where=null;
        }
      }

      $db->commit();
      die('{"status":true}');

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false}');
    }

  }

  function get_duplicated_ministeries(){
    $this->set_table('user_ministeries');

    $select = $this->table->select()
                   ->group( 'ministery' )
                   ->having( 'count(*) > 1');

    return $this->table->fetchAll($select)->toArray();
  }

}