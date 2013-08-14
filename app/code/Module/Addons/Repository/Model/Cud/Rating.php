<?php
require_once 'Module/Core/Repository/Model/Db/Actions.php';
class Module_Addons_Repository_Model_Cud_Rating extends Module_Core_Repository_Model_Db_Actions{

  function rate($id = 0, $rate = 0){
    $check = $this->more_than_once_rating_attempt($id);
    if ( $check == true ){
      return json_encode(array('result'=>'error'));
    }


    $valid_rate = $this->get_valid_rate($rate);

    $this->set_table('rate');
    if( $record_found = $this->already_in_database($id,'reference') ){
      $data  = array('votes'   => $record_found['votes']+1
                    ,'points' => $record_found['points']+$valid_rate );

      $where = $this->table->getAdapter()->quoteInto('reference = ?', $id);
      $this->table->update($data, $where);

    }else{
      $data = array('reference'=> $id  ,'votes'=> '1'  ,'points'=> $valid_rate );
      $this->table->insert($data);
    }

    $this->set_table('rate_votes');
    $data = array('rate_reference' => $id  
                  ,'ip'            => $_SERVER['REMOTE_ADDR']
                  ,'rate'          => $valid_rate
                  ,'created'       => date("Y-m-d H:i:s") );
    $this->table->insert($data);

    return json_encode(array('result'=>'success'));
  }

  function more_than_once_rating_attempt($id){
    $this->set_table('rate_votes');
    $allow_table_modifications_if_no_records_were_changed_within_this_time = App::module('Core')->getModel('Dates')->rest_hours_to_date();

    $select = $this->table->select()
                   ->from($this->table, array('already_rated' => 'COUNT(*)'))
                   ->where("rate_reference = ?", $id)
                   ->where("ip = ?", $_SERVER['REMOTE_ADDR'])
                   ->where("created BETWEEN '$allow_table_modifications_if_no_records_were_changed_within_this_time' AND '". date('Y-m-d H:i:s') . "'" );

    return ( $this->table->fetchRow($select)->already_rated == 0 ) ? false : true;
  }

  function get_valid_rate($rate = 1){
    $allowed_rates = App::module('Addons')->getConfig('core','rating');
    if( array_key_exists($rate, $allowed_rates) ){
      return $rate;
    }
    return 1;
  }

}