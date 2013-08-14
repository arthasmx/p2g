<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Cells extends Module_Core_Repository_Model_Abstract {

  function cells($enabled_only = false,$parse=false){
    $select = $this->_db->select()
                   ->from( array('c' => 'cells'), array('id','sector_id','leader_id','landlord','address','hours','map_cordinates','language','status') )
                   ->join( array('u' => 'user'), "u.username = c.leader_id",  array('leader'=>"CONCAT(u.name,' ',u.last_name,' ',u.maiden_name)") )
                   ->join( array('s' => 'cell_sector'), "s.id = c.sector_id",  array('zone_id') );

    if( $enabled_only === true ){
      $select->join( array('a' => 'acl'), 'a.username = c.leader_id',  array() )->where('a.status = ?', "enabled");
    }

    $cells = $this->_db->query( $select )->fetchAll();
    return empty($cells)? null : empty($parse)? $cells : $this->parse_cells($cells);
  }

  function sectors($enabled_only = false,$parse=false){
    $select = $this->_db->select()
                   ->from( array('s' => 'cell_sector'), array('sector_id' => 's.id', 'sector' => 's.name', 's.seo', 's.zone_id', 'supervisor_id'=>'s.supervisor' ) )
                   ->join( array('u' => 'user'), 'u.username = s.supervisor',  array('supervisor'=>"CONCAT(u.name,' ',u.last_name,' ',u.maiden_name)") )
                   ->order(array('s.zone_id ASC','s.id ASC'));

    if( $enabled_only === true ){
      $select->join( array('a' => 'acl'), 'a.username = s.supervisor',  array() )->where('a.status = ?', "enabled");
    }

    $sector = $this->_db->query( $select )->fetchAll();
    return empty($sector)? null : empty($parse)? $sector : $this->parse_sectors($sector);
  }

  function zones($enabled_only = false,$parse=false){
    $select = $this->_db->select()
                   ->from( array('u' => 'user'),  array('shepherd'=>"CONCAT(u.name,' ',u.last_name,' ',u.maiden_name)") )
                   ->join( array('z' => 'cell_zone'), 'z.shepherd = u.username',  array( 'zone_id'=> 'z.id','z.name','z.seo', 'shepherd_id'=> 'z.shepherd') );

    if( $enabled_only === true ){
      $select->join( array('a' => 'acl'), 'a.username = z.shepherd',  array() )->where('a.status = ?', "enabled");
    }

    $zones = $this->_db->query( $select )->fetchAll();
    return empty($zones)? null : empty($parse)? $zones : $this->parse_zones($zones);
  }



  function parse_sectors($sectors=array()){
    $parsed=array();
    foreach($sectors AS $sector){
      $parsed[ $sector['sector_id'] ] = $sector;
    }
    return $parsed;
  }

  function parse_zones($zones=array()){
    $parsed=array();
    foreach($zones AS $zone){
      $parsed[ $zone['zone_id'] ] = $zone;
    }
    return $parsed;
  }

  function parse_cells($cells=array()){
    $parsed=array();
    foreach($cells AS $cell){
      $parsed[ $cell['zone_id'] ][] = $cell;
    }
    return $parsed;
  }

}