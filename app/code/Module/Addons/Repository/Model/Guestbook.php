<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Guestbook extends Module_Core_Repository_Model_Abstract {

  function get_signs($current_page = 1, $enabled_only = true){
    $select = $this->_shared_query($enabled_only);

    $guestbook = $this->setPaginator_page($current_page)->paginate_query( $select );
    return empty($guestbook)? null : $guestbook;
  }

  function latest($enabled_only = true){
    $select = $this->_shared_query($enabled_only,true);
    $select->limit( App::getConfig('aside_guestbook_limit') );

    $latest = $this->_db->query( $select )->fetchAll();
    return empty($latest)? null : $latest;
  }

  private function _shared_query($enabled_only=true, $truncate=false){
    if( $truncate===true ){
      $select = $this->_db->select()
                     ->from( array('g'  => 'guestbook'), array('g.id','g.name','g.email','LEFT(g.comment,200) AS comment','g.gender','g.created','g.lang','g.status') );
    }else{
      $select = $this->_db->select()
                     ->from( array('g'  => 'guestbook') );
    }

    $select->where('g.lang = ?', App::locale()->getLang() )
           ->order('g.created DESC');

    if( $enabled_only === true ){
      $select->where('g.status = ?', "enabled");
    }
    return $select;
  }

}