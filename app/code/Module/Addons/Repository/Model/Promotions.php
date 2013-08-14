<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Promotions extends Module_Core_Repository_Model_Abstract {

  private   $image_config   = null;
  private   $folder_config  = null;
  
  function init(){
    $this->folder_config = App::module('Articles')->getConfig('core','folders');
    $this->image_config  = App::module('Articles')->getConfig('core','articles');
    $this->session       = App::module('Core')->getModel('Namespace')->get( 'user' );
  }

  function upload( $username=null ){
    // $username is provided when admins uploads promotion to business, otherwise, we use session's username
    if( empty($username) ){
      $username = $this->session->user['username'];
    }

    $business = App::module('Core')->getModel('Namespace')->get( 'business' );
    if( empty($business->business['promotions']) ) {
      $file = App::module('Addons')->getModel('Cud/Promotions')->add_promotion($this->folder_config['promotions']);
    }else{
      $file = $business->business['promotions'];
    }
    $path = str_replace( '\\','/', $file['path']);
    
    $uploaded_file = App::module('Core')->getModel('Filesystem')->plUploader_upload( $path  , $file['picture'] );
    $image         = App::module('Core')->getModel('Image');

    // promotion web
    $image->resize_image($uploaded_file, $this->image_config['promotions_width'], $this->image_config['promotions_height'],'exact');
    $image->saveImage( $uploaded_file, 100 );
    // promotion mobile
    $image->resize_image($uploaded_file, $this->image_config['promotions_cel_width'], $this->image_config['promotions_cel_height'],'exact');
    $image->saveImage( $path.DS.'mobile'.DS.$file['picture'], 100 );
    // promotion tablet
    $image->resize_image($uploaded_file, $this->image_config['promotions_tab_width'], $this->image_config['promotions_tab_height'],'exact');
    $image->saveImage( $path.DS.'tablet'.DS.$file['picture'], 100 );

    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  }

  function get_preview($id=null){
    if( empty($id) ){
      $session  = App::module('Core')->getModel('Namespace')->get( 'business' );
      $id       = $session->business['promotions']['id'];
    }

    $select    = $this->_db->select()->from(array('sp'  => 'site_promotions'  ) )->where('sp.id = ?', $id );
    $promotion = $this->_db->query( $select )->fetch();

    if ( empty( $promotion ) ){
      return null;
    }else{
      $business->business['promotion'] = $promotion;
      return $promotion;
    }

  }

  function preview($id=null){
    $promotion = $this->get_preview($id);
    return empty( $promotion ) ?
      null
    :
      $promotion;
  }

  function edit( $id=null ){
    $select = $this->_db->select()
                   ->from(array('vp' => 'view_promotions' ) )
                   ->where( 'vp.id = ?', $id )
                   ->limit(1);

    if( ! in_array('admin', $this->session->user['privileges']) && ! in_array('root', $this->session->user['privileges']) ){
      $select->where( 'vp.username = ?', $this->session->user['username'] );
    }

    $promotion = $this->_db->query( $select )->fetch();

    if( empty($promotion) ){
      App::module('Core')->exception( App::xlat('EXC_promotion_wasnt_found') . '[PER82]' );
    }

    $business = App::module('Core')->getModel('Namespace')->get( 'business' );
    $business->business['promotions'] = array_merge($promotion,array('path'=>WP.str_replace('/','\\',$promotion['path'])));
    return $promotion;
  }

  function get($id=null, $area='frontend'){
    $select = $this->_db->select()
                        ->from(array('vp' => 'view_promotions' ) )
                        ->where( 'vp.id = ?', $id )
                        ->limit(1);

    if( $area === 'frontend' ){
      $select->where( 'vp.status = ?', 'enabled' );
    }

    $promotion = $this->_db->query( $select )->fetch();

    return empty($promotion) ?
      App::module('Core')->exception( App::xlat('EXC_promotion_wasnt_found') . '[PER103]' )
    :
      $promotion;
  }

  function latest(){
    $sub = $this->_db->select()
                ->from( array( 'sp' => 'site_promotions') , array('id'=>'MAX(sp.id)') )
                ->where( 'sp.status = ?', 'enabled' )
                ->where( 'sp.main = ?', 'yes' )
                ->group('sp.username');

    $select = $this->_db->select()
                   ->from( array( 'vp' => 'view_promotions' ) )
                   ->join( array('sp' => $sub), "sp.id = vp.id", array() )
                   ->where( 'NOW() >= vp.start' )
                   ->where( 'NOW() <= vp.finish' );
    
    return $this->_db->query( $select )->fetchAll();
  }

  function promotions( $current_page = 1 ){  
    $select = $this->_db->select()
                        ->from( array( 'vp' => 'view_promotions' ) )
                        ->where( 'vp.city = ?', 'mazatlan' ) // @todo: remove this hardcode
                        ->where( 'vp.status = ?', 'enabled')
                        ->where( 'NOW() >= vp.start' )
                        ->where( 'NOW() <= vp.finish' )
                        ->order( 'vp.start DESC');
  
    return $this->setPaginator_page($current_page)->paginate_query( $select );
  }

  function business($business=null, $area='frontend'){
    $select = $this->_db->select()
                        ->from(array('vp' => 'view_promotions' ) )
                        ->where( 'vp.username = ?', $business );

    if( $area === 'frontend' ){
      $select->where( 'vp.status = ?', 'enabled' );
    }

    $promotions = $this->_db->query( $select )->fetchAll();

    return empty($promotions) ?
      null
    :
      $promotions;
  }



  function jqGrid_admin_main_promotions($username=null){
    $select = $this->_db->select()->from( array('sp' => 'site_promotions'), array('id') );

    if( ! empty($username) ){
      $select->where('sp.username = ?', $username )
             ->where('sp.status = ?', 'enabled' )
             ->where('sp.city = ?', $this->session->user['city'] );
    }

    $promotions = $this->_db->query( $select )->fetchAll();
    return empty($promotions)? null : $this->grid_shared_code();
  }

  function grid_shared_code(){
    App::module('Core')->getModel('Namespace')->clear('promotions');
    App::module('Core')->getModel('Libraries')->promotions();
    App::module('Core')->getModel('Libraries')->json2();

//    $user_privileges = App::module('User')->getModel('User')->get_privileges( $this->session->user['username'], true );
    if( ! in_array('admin', $this->session->user['privileges'] ) && ! in_array('root', $this->session->user['privileges'] ) ){
      $this->list_grid();
    }else{
      $this->list_grid_admin();
    }

    return true; // required when MAIN CATEGORY have not been created
  }

  function list_grid($parent=null){
    $grid = App::module('Core')->getModel('Grid');
    $custom_header_shared_code  = "if( jQuery( promotions.dom.list_toolbar ).length == 0 ){
                                     jQuery('div.ui-jqgrid-titlebar').after( '<div class=\"custom-cbox-bar\" >'+
                                     '<select onchange=\"promotions.status(this.value)\" id=\"cbox_status\"> <option value=\"\">Status</option> <option value=\"enabled\">Activo</option> <option value=\"disabled\">Inactivo</option> </select>'+
                                     '<span class=\"ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-secondary\" id=\"del\"> <span class=\"ui-button-text\">Eliminar</span>  <span class=\"ui-button-icon-secondary ui-icon ui-icon-check\"></span>  </span>' +
                                     '<span class=\"error\" id=\"listing_action\"></span>' +
                                     '</div>' );
                                   }else{
                                     if( ! jQuery( promotions.dom.list_grid + \" input[type='checkbox']\").is(':checked') ){
                                       jQuery( promotions.dom.list_toolbar ).remove();
                                     }
                                   }";
    $custom_header_last = "";

    $grid->setOptions( array('url'=>App::base('/promotions/list-data-loader'), 'caption'=>App::xlat('jqGrid_social_listing_topic'), 'sortname'=>'id', 'sortorder'=>'asc', 'hidegrid'=>'false') )
         ->setAttribs( array('multiselect'=>'true', 'multiboxonly'=>'true', 'viewrecords'=>'true') )
         ->setNavigate(false)
         ->add_model( array(

             array('width'=>40, 'align'=>'center',  'title'=>App::xlat('Id'), 'name'=>'id' ),
             array('width'=>420, 'search'=>'false', 'type'=>'picture', 'title'=>App::xlat('picture'), 'name'=>'image'),
             array('width'=>420, 'search'=>'false', 'title'=>App::xlat('description'), 'name'=>'description'),
             array('width'=>100, 'type'=>'date', 'title'=>App::xlat('start'), 'name'=>'start'),
             array('width'=>100, 'type'=>'date', 'title'=>App::xlat('finish'), 'name'=>'finish'),
             array('width'=>90, 'search'=>'false', 'type'=>'span', 'title'=>App::xlat('main'), 'name'=>'main'),
             array('width'=>90, 'type'=>'span', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('status'), 'name'=>'status')

         ))->setGrid_id('#promotions-listing')
         ->setOn_cell_select(",onCellSelect:function(row,col){
                                if( col > 0 ){
                                  redirect( baseUrl + '/promotions/edit/' + row );
                                }else{
                                  $custom_header_shared_code
                                }
                              }")
         ->setOn_select_all(",onSelectAll:function(rows,status){ $custom_header_shared_code }")
         ->setWidth('980')
         ->setResize(true)
         ->jqGrid();
  }

  function list_grid_admin($parent=null){
    $grid = App::module('Core')->getModel('Grid');
    $custom_header_shared_code  = "if( jQuery( promotions.dom.list_toolbar ).length == 0 ){
                                     jQuery('div.ui-jqgrid-titlebar').after( '<div class=\"custom-cbox-bar\" >'+
                                     '<select onchange=\"promotions.status(this.value)\" id=\"cbox_status\"> <option value=\"\">Status</option> <option value=\"enabled\">Activo</option> <option value=\"disabled\">Inactivo</option> </select>'+
                                     '<select onchange=\"promotions.language(this.value)\" id=\"cbox_lang\"> <option value=\"\">Idioma</option> <option value=\"es\">Español</option> <option value=\"en\">English</option> </select>'+
                                     '<span class=\"error\" id=\"listing_action\"></span>' +
                                     '</div>' );
                                   }else{
                                     if( ! jQuery( promotions.dom.list_grid + \" input[type='checkbox']\").is(':checked') ){
                                       jQuery( promotions.dom.list_toolbar ).remove();
                                     }
                                   }";
    $custom_header_last = "";

    $grid->setOptions( array('url'=>App::base('/promotions/list-data-loader'), 'caption'=>App::xlat('jqGrid_social_listing_topic'), 'sortname'=>'id', 'sortorder'=>'asc', 'hidegrid'=>'false') )
         ->setAttribs( array('multiselect'=>'true', 'multiboxonly'=>'true', 'viewrecords'=>'true') )
         ->setNavigate(false)
         ->add_model( array(

             array('width'=>40, 'align'=>'center',  'title'=>App::xlat('Id'), 'name'=>'id' ),
             array('width'=>420, 'title'=>App::xlat('picture'), 'type'=>'picture', 'name'=>'image'),
             array('width'=>120, 'search'=>'false', 'title'=>App::xlat('description'), 'name'=>'description'),
             array('width'=>120, 'title'=>App::xlat('username'), 'name'=>'username'),
             array('width'=>120, 'title'=>App::xlat('author'), 'name'=>'author'),

             array('width'=>120, 'title'=>App::xlat('city'), 'name'=>'city_name'),
             array('width'=>120, 'title'=>App::xlat('state'), 'name'=>'state_name'),
             array('width'=>120, 'title'=>App::xlat('country'), 'name'=>'country_name'),

             array('width'=>90, 'type'=>'date', 'title'=>App::xlat('created'), 'name'=>'start'),
             array('width'=>90, 'type'=>'date', 'title'=>App::xlat('updated'), 'name'=>'finish'),
             array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('status'), 'name'=>'status')

         ))->setGrid_id('#promotions-listing')
         ->setOn_cell_select(",onCellSelect:function(row,col){
                                if( col > 0 ){
                                  redirect( baseUrl + '/promotions/edit/' + row );
                                }else{
                                  $custom_header_shared_code
                                }
                              }")
         ->setOn_select_all(",onSelectAll:function(rows,status){ $custom_header_shared_code }")
         ->setWidth('980')
         ->setResize(true)
         ->jqGrid();

  }

  function jqGrid_list( $params=null ){
    if( empty($params) ){
      return null;
    }

    if( ! in_array('admin', $this->session->user['privileges'] ) && ! in_array('root', $this->session->user['privileges'] ) ){
      $select = $this->jqGrid_user_list($params);
    }else{
      $select = $this->jqGrid_admin_list($params);
    }

    return $this->setPaginator_page( @$params['page'] )
                ->setItems_per_page( @$params['rows'] )
                ->setGrid_id_container('id')
                ->jqGrid_query_for_listing($select, $params);
  }

  function jqGrid_user_list( $params=null ){
    if( empty($params) ){
      return null;
    }

    return $this->_db->select()
                     ->from( array('vp' => 'view_promotions'), array('id','image'=>'CONCAT(path,"mobile/",picture)','description','start','finish','main','status') )
                     ->where( 'vp.username = ?', $this->session->user['username'] )
                     ->where( 'vp.city = ?', $this->session->user['city'] )
                     ->where( 'vp.status != ?', 'deleted' )
                     ->order( 'vp.main ASC' );
  }

  function jqGrid_admin_list( $params=null ){
    if( empty($params) ){
      return null;
    }
    return $this->_db->select()->from( array('vp' => 'view_promotions'), array('id','image'=>'CONCAT(path,"mobile/",picture)','description','username','author','city_name','state_name','country_name','start','finish','status') ) ;
  }



}