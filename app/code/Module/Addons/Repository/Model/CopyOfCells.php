<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Cells extends Module_Core_Repository_Model_Abstract {

  function get($current_page = 1, $enabled_only = true){
    $select = $this->_db->select()
                   ->from( array('c' => 'zone_sector_view') )
                   ->where('c.language = ?', App::locale()->getLang() );

    if( $enabled_only === true ){
      $select->where('c.status = ?', "enabled");
    }

    $cells = empty($current_page) ?
      $this->_db->query( $select )->fetchAll()
    :
      $this->setPaginator_page($current_page)->paginate_query( $select )
    ;

    return empty($cells)? null : $cells;
  }

}