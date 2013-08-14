<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Categories extends Module_Core_Repository_Model_Abstract {

  function get_category($id=null,$enabled_only=null, $error_type='exception'){
    if( empty($id) ){
      App::module('Core')->getModel('Error')->render( App::xlat('EXC_category_id_not_found') . '<br />Launched at method get_category_id, file Repository/Model/Categories', $error_type );
    }

    $select = $this->_db->select()
                   ->from( array('vc' => 'view_categories') )
                   ->where('vc.id = ?', $id );

    if( ! empty($enabled_only) ){
      $select->where('vc.status = ?', 'enabled');
    }

    $category = $this->_db->query( $select )->fetch();
    return empty($category)?
      null
    : 
      $category;

  }

  function get_category_id($seo=null){
    $data = $this->get_category_by_seo($seo);
    if( empty($data) ){
      die ("{'error':'true'}");
    }
    return $data['id'];
  }

  function get_category_by_seo($seo=null,$enabled_only=null){
    if( empty($seo) ){
      return null;
    }

    $select = $this->_db->select()
                   ->from( array('vc' => 'view_categories') )
                   ->where('vc.seo = ?', $seo)
                   ->where('vc.language = ?', App::locale()->getLang() );

    if( ! empty($enabled_only) ){
      $select->where('vc.status = ?', 'enabled');
    }

    $category = $this->_db->query( $select )->fetch();
    return empty($category)? null : $category;
  }

  // This method requires the PARENT seo to get his children
  function get_children_by_seo($parent_seo=null, $render_style=null, $seo_OR_id='seo', $enabled_only=null){
    $parent = $this->get_category_by_seo($parent_seo);
    if( empty($parent) ){
      return null;
    }

    return ($render_style=="select") ?
             $this->get_children_for_select($parent['id'],$seo_OR_id)
           :
             $this->get_children($parent['id']);
  }

  function get_children($parent_id=0, $username=null, $enabled_only=null){
    if( empty($parent_id) ){
      return null;
    }

    $select = $this->_db->select()
                   ->from( array('vc' => 'view_categories') )
                   ->where('vc.parent = ?', $parent_id)
                   ->where('vc.language = ?', App::locale()->getLang() )
                   ->order('vc.id ASC');

    if( ! empty($username) ){
      $select->where('vc.username = ?', $username);
    }
    if( ! empty($enabled_only) ){
      $select->where('vc.status = ?', 'enabled');
    }

    $children = $this->_db->query( $select )->fetchAll();
    return empty($children) ? null : $children;
  }

  function get_children_for_select($parent=null, $seo_OR_id='seo'){
    $children       = $this->get_children($parent);
    $children_select = array();

    foreach($children AS $child){
      $children_select[ $child[$seo_OR_id] ] = $child['name'];
    }

    return empty($children_select) ? null : $children_select;
  }



  function get_parents($child,$recursive=true,$error_type='exception') {
    $range = $this->get_category($child);
    if( empty($range) ){
      App::module('Core')->getModel('Error')->render( App::xlat('EXC_category_id_not_found') . '<br />Launched at method get_parents, file Repository/Model/Categories', $error_type );
    }
    $izq = $range['izq'];
    $der = $range['der'];

    $limit = null;
    $order = "vc.izq ASC";
    if ( empty($recursive) ) {
      $limit = "1";
      $order = "vc.izq DESC";
    }

    $select = $this->_db->select()
                        ->from( array('vc' => 'view_categories') )
                        ->where('vc.izq < ?', $izq)
                        ->where('vc.der > ?', $der)
                        ->where('vc.language = ?', App::locale()->getLang() )
                        //->where('vc.parent != 0')
                        ->orWhere('vc.id = ?', $child)
                        ->limit($limit)
                        ->order($order);

    if ( empty($recursive) ) {
      $parents = $this->_db->query( $select )->fetch();
    }else{
      $parents = $this->_db->query( $select )->fetchAll();
    }

    return empty($parents) ?
      null
    :
      $parents;
  }

  // gets nodes from ME until find my latest parent ( parent > parent > parent > me )
  function get_family_back($child,$recursive=true,$error_type='exception',$izq=null,$der=null) {
    $parents = $this->get_parents( $child,$recursive,$error_type );

    if( empty($parents) ){
      App::module('Core')->getModel('Error')->render( App::xlat('EXC_category_id_not_found') . '<br />Launched at method get_parents, file Repository/Model/Categories', $error_type );
    }
    $last_parent = end($parents);
    $children    = $this->get_children( $last_parent['id'] );

    return empty($children) ?
      $parents
    :
      array_merge($parents,$children);
  }

  // gets nodes from ME until find the inner CHILDREN ( me > child > child )
  // this is an awesome method to get ALL TREE; I mean, ALL! with sub/sub/sub/sub categories (great to do updates)
  function get_family_forward($me_seo=null,$sort_by='vc.izq ASC',$error_type='exception') {
    $me = $this->get_category_by_seo($me_seo);
    if( empty($me) ){
      App::module('Core')->getModel('Error')->render( App::xlat('EXC_category_id_not_found') . '<br />Launched at method get_parents, file Repository/Model/Categories', $error_type );
    }

    $select = $this->_db->select()
                   ->from( array('vc' => 'view_categories') )
                   ->where('vc.izq >= ?', $me['izq'] )
                   ->where('vc.der <= ?', $me['der'] )
                   ->order($sort_by);

    $children = $this->_db->query( $select )->fetchAll();
    return empty($children) ? null : $children;
  }

  /**
   * Parsea (convierte) un array secuencial de categorias e hijos y lo devuelve como array multi-dimensional de categorias anidadas
   * Ejemplo:
   *		[0] Categoria 1
   *			[0] Categoria 1.1
   * 				[0] Categoria 1.1.1
   * 				[1]	Categoria 1.1.2
   * 			[1] Categoria 1.2
   * 				[0] Categoria 1.2.1
   * 		[1] Categoria 2
   * 100% Cargo Culting
   * http://stackoverflow.com/questions/3261228/convert-flat-array-to-the-multi-dimentional
   */
  function children_parser($source) {
    $nodes = array();
    $tree  = array();
    foreach ($source as &$node) {
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
    return $tree;
  }

  function get_family_grouped_for_select($category_seo=null){
    if( empty($category_seo) ){
      return array();
    }
    $family        = $this->get_family_forward($category_seo);
    $parsed_family = $this->children_parser( $family );

    $options = null;
    $mo  = null;
    foreach( $parsed_family[0]['children'] AS $family){
      foreach( $family['children'] AS $option ){
        $options[$option['seo']]=$option['name'];
      }
      $mo[ $family['name'] ] = $options;
      $options=null;
    }
    return $mo;
  }

  function get_family_grouped_for_select_grouped($category_seo=null){
    if( empty($category_seo) ){
      return array();
    }
    $family        = $this->get_family_forward($category_seo);
    $parsed_family = $this->children_parser( $family );

    $html          = null;
    $first         = '<optgroup label="'.App::xlat('ministery_main').'">';
    foreach( $parsed_family[0]['children'] AS $family){

      if( empty( $family['children'] ) ){
        $first .= '<option value="'.$family['seo'].'">'. $family['name'] .'</option>';
      }else{

        $html .= '<optgroup label="'.$family['name'].'">';
          foreach( $family['children'] AS $option ){
            $html .= '<option value="'. $option['seo']. '">'. $option['name'] .'</option>';
          }
        $html .= '</optgroup>';

      }

    }

    return $first.'</optgroup>'.$html;
  }
  


  function get_category_range( $category_id=null ){
    if( empty( $category_id ) ) {
      return null;
    }

    $select = $this->_db->select()
                   ->from( array('c' => 'categories'), array('id','izq','der','to_rest'=>'(der-izq)+1') )
                   ->where('id = ?', $category_id );

    $range = $this->_db->query( $select )->fetch();
    return empty($range)?
      null
    :
      $range;
  }



  function grid_shared_code($parent=null){
    App::module('Core')->getModel('Namespace')->clear('categories');
    App::module('Core')->getModel('Libraries')->categories();
    App::module('Core')->getModel('Libraries')->json2();

    $this->list_grid( $parent );

    return true; // required when MAIN CATEGORY have not been created
  }

  function list_grid($parent=null){
    $url = App::base('/categories/list-data-loader');
    if( ! empty($parent) ){
      $url = App::base('/categories/list-data-loader/parent/' . $parent);
    }

    $grid = App::module('Core')->getModel('Grid');

    $custom_header_shared_code  = "if( jQuery( categories.dom.list_toolbar ).length == 0 ){
                                     jQuery('div.ui-jqgrid-titlebar').after( '<div class=\"custom-cbox-bar\" >'+
                                                                               '<select onchange=\"categories.status(this.value)\" id=\"cbox_status\"> <option value=\"\">Status</option> <option value=\"enabled\">Activo</option> <option value=\"disabled\">Inactivo</option> </select>'+
                                                                               '<select onchange=\"categories.language(this.value)\" id=\"cbox_lang\"> <option value=\"\">Idioma</option> <option value=\"es\">Español</option> <option value=\"en\">English</option> </select>'+
                                                                               '<span class=\"error\" id=\"listing_action\"></span>' +
                                                                             '</div>' );
                                   }else{
                                     if( ! jQuery( categories.dom.list_grid + \" input[type='checkbox']\").is(':checked') ){
                                       jQuery( categories.dom.list_toolbar ).remove();
                                     }
                                   }";
    $custom_header_last = "";

    $grid->setOptions( array('url'=>$url, 'caption'=>' ', 'sortname'=>'id', 'sortorder'=>'asc', 'hidegrid'=>'false') )
        ->setAttribs( array('multiselect'=>'true', 'multiboxonly'=>'true', 'viewrecords'=>'true') )
        ->setNavigate(false)
        ->add_model( array( array('width'=>40, 'align'=>'center',  'title'=>App::xlat('Id'), 'name'=>'id' ),

            array('width'=>120, 'search'=>'false', 'title'=>App::xlat('title'), 'name'=>'name'),
            array('width'=>120, 'search'=>'false', 'sortable'=>'false', 'title'=>App::xlat('seo'),   'name'=>'seo'),

            array('width'=>50, 'search'=>'false', 'sortable'=>'false', 'align'=>'center', 'title'=>App::xlat('parent_id'), 'name'=>'parent'),
            array('width'=>50, 'search'=>'false', 'align'=>'center', 'title'=>App::xlat('children'), 'name'=>'children'),

            array('width'=>50, 'search'=>'false', 'sortable'=>'false', 'align'=>'center', 'title'=>App::xlat('izq'), 'name'=>'izq'),
            array('width'=>50, 'search'=>'false', 'sortable'=>'false', 'align'=>'center', 'title'=>App::xlat('der'), 'name'=>'der'),

            array('width'=>90, 'type'=>'date', 'title'=>App::xlat('created'), 'name'=>'created'),
            array('width'=>90, 'type'=>'date', 'title'=>App::xlat('updated'), 'name'=>'updated'),

            array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('status'), 'name'=>'status'),
            array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'es'=>'Español','en'=>'English'), 'title'=>App::xlat('language'), 'name'=>'language'),

            array('width'=>150, 'search'=>'false', 'title'=>App::xlat('author'), 'name'=>'author') )

        )->setGrid_id('#categories-listing')
        ->setOn_cell_select(",onCellSelect:function(row,col){
                               if( col > 0 ){
                                 redirect( baseUrl + '/categories/edit/' + row );
                               }else{
                                 $custom_header_shared_code
                               }
                             }")
      ->setOn_select_all(",onSelectAll:function(rows,status){ $custom_header_shared_code }")
      ->setWidth('980')
      ->setResize(true)
      ->jqGrid();
  }

  function jqGrid_admin_list( $params=null ){
    if( empty($params) ){
      return null;
    }

    $select = $this->_db->select()
                   ->from( array('vc' => 'view_categories'), array('id','name','seo','parent','children','izq','der','created','updated','status','language') )
                   ->join( array('u' => 'user'), 'u.username = vc.username',  array('author'=>"CONCAT(u.name,' ',u.last_name,' ',u.maiden_name)") )
                   ->where('vc.parent = ?',  (empty($params['parent'])? 1 : $params['parent']) )
                   ->where('vc.language = ?', App::locale()->getLang() );

    return $this->setPaginator_page( @$params['page'] )
                ->setItems_per_page( @$params['rows'] )
                ->setGrid_id_container('id')
                ->jqGrid_query_for_listing($select, $params);
  }

  function jqGrid_admin_main_categories(){
    $select = $this->_db->select()
                   ->from( array('c' => 'categories'), array('id') )
                   ->where('c.parent_id = 1');

    $main_categories = $this->_db->query( $select )->fetchAll();
    return empty($main_categories)? null : $this->grid_shared_code();
  }
  
}