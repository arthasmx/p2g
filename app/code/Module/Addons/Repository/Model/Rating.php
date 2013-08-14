<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Rating extends Module_Core_Repository_Model_Abstract {

  function get_rate($id = 0, $available_rates_only = true ){
    $select = $this->_db->select()
                   ->from(array('r'  => 'rate'), array('id','reference','votes','points','type','stat', 'rating' => 'TRUNCATE((r.points / r.votes),0)'))
                   ->where( 'r.reference = ?', $id )
                   ->limit(1);

    if( ! empty($available_rates_only) ){
      $select->where( 'r.stat = ?', 1 );
    }

    $rating = $this->_db->query( $select )->fetch();
    return empty($rating) ? NULL : $rating;
  }

}