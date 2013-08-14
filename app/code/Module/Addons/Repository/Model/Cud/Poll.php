<?php
require_once 'Module/Core/Repository/Model/Db/Actions.php';
class Module_Addons_Repository_Model_Cud_Poll extends Module_Core_Repository_Model_Db_Actions{

  function poll($id=0,$vote=null){
    $check = $this->more_than_once_voting_attempt($id);
    if ( $check == true ){
      return false;
    }

    $this->set_table('poll_votes');
    $data = array('poll_id'   => $id  
                  ,'ip'       => $_SERVER['REMOTE_ADDR']
                  ,'vote'     => $vote
                  ,'created'  => date("Y-m-d H:i:s") );
    $this->table->insert($data);
    return true;
  }

  function more_than_once_voting_attempt($id){
    $this->set_table('poll_votes');
    $allow_table_modifications_if_no_records_were_changed_within_this_time = App::module('Core')->getModel('Dates')->rest_hours_to_date();

    $select = $this->table->select()
                   ->from($this->table, array('already_voted' => 'COUNT(*)'))
                   ->where("poll_id = ?", $id)
                   ->where("ip = ?", $_SERVER['REMOTE_ADDR'])
                   ->where("created BETWEEN '$allow_table_modifications_if_no_records_were_changed_within_this_time' AND '". date('Y-m-d H:i:s') . "'" );

    return ( $this->table->fetchRow($select)->already_voted == 0 ) ? false : true;
  }

}