<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Menu extends Module_Core_Repository_Model_Abstract {

  private $user_menus = array();

  function get_admin($username){
    if( empty($username) ){
      return null;
    }
    return $this->get('admin-top','vum.sort ASC',$username);
  }

  function get($menu_section_to_load = "admin-top", $sort_by='vum.izq ASC', $username=null){

    $select = $this->_db->select()
                   ->from(     array('vum' => 'view_user_menus') )
                   ->where('vum.section = ?', $menu_section_to_load)
                   ->where('vum.status= ?', 'enabled')
                   ->group('vum.id')
                   ->order($sort_by);

    if( ! empty($username) ){
      $select->join(     array('mp'  => 'menu_privileges') , 'mp.menu_id = vum.id', array() );
      $select->join(     array('p'   => 'privileges')      , 'p.name = mp.privilege', array() );
      $select->join(     array('up'  => 'user_privileges') , 'up.privilege = p.privilege', array() );
      $select->where('up.username = ?', $username);
    }

    $this->user_menus = $this->_db->query( $select )->fetchAll();
    return empty($this->user_menus)? null : $this->children_parser();
  }

  function children_parser() {
    $nodes = array();
    $tree  = array();
    foreach ($this->user_menus as &$node) {
      $node["children"] = array();
      $id = $node["id"];
      $parent_id = $node["parent"];
      $nodes[$id] =& $node;
      if (array_key_exists($parent_id, $nodes)) {
        $nodes[$parent_id]["children"][] =& $node;
      } else {
        $tree[] =& $node;
      }
    }

    $tree['allowed'] = $this->allowed_menus_to_validate(); 
    return $tree;
  }

  function parse_menu_to_ul($array,$ul_id=null,$current=null) {
    if( empty($array) ){
      return null;
    }

    $base_url = rtrim(App::base(),"www");
    $out="<ul $ul_id >";
    foreach($array as $elem){

      if( ! is_array($elem['children']) ){
        $out = $out ."<li". (($current==$elem['seo'])?' class="current"':null) ."> <a href='". (empty($elem['url'])?$base_url:$base_url.$elem['url']) ."'>". $elem['name'] ."</a></li>";
      }else{
        if( App::getDesign()->getCurrentLayout()==='intro' && $elem['seo']=='inicio' ){
        }else{
          $out = $out."<li". (($current==$elem['seo'])?' class="current"':null) ."> <a href='". (empty($elem['url'])?$base_url:$base_url.$elem['url']) ."'>". $elem['name'] ."</a>". $this->parse_menu_to_ul($elem['children']) ."</li>";
        }
      }
    }
    $out=$out."</ul>";
    return $out;
  }

  function allowed_menus_to_validate(){
    $allowed[] = '';
    $allowed[] = '/';
    $allowed[] = '/logout';

    foreach( $this->user_menus AS $menu ){
      if( ! empty( $menu['url'] ) ){
        $allowed[] = $menu['url'];
      }
    }

    $user_privileges = App::module('User')->getModel('User')->get_privileges();
    foreach( $user_privileges AS $privileges){
      switch( $privileges['name'] ){
        case 'user':
          break;
        case 'editor':
          $allowed[] = 'articles/edit';
          break;
        case 'business':
          $allowed[] = '/business/edit';
          break;
        case 'admin':
          break;
        case 'root':
          $allowed[] = 'events/edit';
          break;
      }
    }

    return $allowed;
  }

}