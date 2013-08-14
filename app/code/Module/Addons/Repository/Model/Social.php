<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Social extends Module_Core_Repository_Model_Abstract {

  private $image_config   = null;
  private $folder_config  = null;
  private $business_session   = null;

  function init(){
    $this->folder_config     = App::module('Articles')->getConfig('core','folders');
    $this->image_config      = App::module('Articles')->getConfig('core','articles');
    $this->business_session  = App::module('Core')->getModel('Namespace')->get( 'business' );
    $this->session           = App::module('Core')->getModel('Namespace')->get( 'user' );
  }

  function reload_gallery( $page=1,$max_files_to_show=28 ){
    $files = App::module('Core')->getModel('Filesystem')->get_files_from_path( $this->business_session->business['social']['folders']['base']. $this->business_session->business['social']['folders']['thumb'], array( "include" => "/\.jpg$/i") );
    if ( empty( $files ) ){
      return null;
    }

    // sets file name counter (edit required)
    if( empty( $this->business_session->business['social']['file_name_counter'] ) ){
      $this->business_session->business['social'][ 'file_name_counter'] = count($files) + 100;
    }

    if( count($files) > $max_files_to_show ){
      return App::module('Core')->getModel('Filesystem')->paginate_files_in_folder('admin_gallery',$page,$max_files_to_show);
    }

    return array('files' => $files
        ,'path'  => $this->business_session->business['social']['folders']['url'] );
  }

  function main_pix_preview(){
    if( empty( $this->business_session->business['social']['folders'] ) ){
      return null;
    }

    $required_images = array('slider', 'article', 'promote', 'listing', 'aside', 'mobile');
    $path            = $this->business_session->business['social']['folders']['base'] . $this->business_session->business['social']['folders']['gallery'].DS;
    $images['path']  = $this->business_session->business['social']['folders']['url'];

    foreach( $required_images AS $image ){
      if( App::module('Core')->getModel('Filesystem')->check_folder( $path.$image.'.jpg' ) ){
        $images['images'][$image] = $image.'.jpg';
      }else{
        $images=null;
        break;
      }
    }

    return $images;
  }

  function delete_image($image=null){
    if( empty( $this->business_session->business['social']['id'] ) || empty($image) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_image_deleted') .'"}');
    }

    $base        = $this->business_session->business['social']['folders']['base'];
    $file        = $this->business_session->business['social']['folders']['gallery'].DS.$image;
    $thumb       = $this->business_session->business['social']['folders']['thumbnails'].DS.$image;
    $thumb_admin = $this->business_session->business['social']['folders']['thumb'].DS.$image;
    $fSys        = App::module('Core')->getModel('Filesystem');

    if( ! $fSys->check_folder( $base.$file ) || $fSys->delete($base.$thumb)===false || $fSys->delete($base.$thumb_admin)===false ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_image_deleted') .'"}');
    }
    $fSys->delete( $file );
    die('{"status":true, "message":"'. App::xlat('jSon_success_image_deleted') .'"}');
  }



  // gets grid JS controls definition
  function jqGrid_definition_resources(){
    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->social_list();
    $libraries->json2();

    $is_admin = App::module('Acl')->getModel('Acl')->is_logged_user_admin_from_session();
    if( empty($is_admin) ){
      $select = $this->jqGrid_user_resources();
    }else{
      $select = $this->jqGrid_admin_resources();
    }

  }

  function jqGrid_user_resources($parent=null){
    $grid = App::module('Core')->getModel('Grid');
    $custom_header_shared_code  = "if( jQuery( social.dom.list_toolbar ).length == 0 ){
                                     jQuery('div.ui-jqgrid-titlebar').after( '<div class=\"custom-cbox-bar\" >'+
                                       '<select onchange=\"social.status(this.value)\" id=\"cbox_status\"> <option value=\"\">Status</option> <option value=\"enabled\">Activo</option> <option value=\"disabled\">Inactivo</option> </select>'+
                                       '<span class=\"ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-secondary\" id=\"del\"> <span class=\"ui-button-text\">Eliminar</span>  <span class=\"ui-button-icon-secondary ui-icon ui-icon-check\"></span>  </span>' +
                                       '<span class=\"error\" id=\"listing_action\"></span>' +
                                     '</div>' );
                                    }else{
                                      if( ! jQuery( social.dom.list_grid + \" input[type='checkbox']\").is(':checked') ){
                                        jQuery( social.dom.list_toolbar ).remove();
                                      }
                                    }";
    $custom_header_last = "";

    $grid->setOptions( array('url'=>App::base('/social/list-data-loader'), 'caption'=>App::xlat('jqGrid_social_listing_topic'), 'sortname'=>'id', 'sortorder'=>'asc', 'hidegrid'=>'false') )
         ->setAttribs( array('multiselect'=>'true', 'multiboxonly'=>'true', 'viewrecords'=>'true') )
         ->setNavigate(false)
         ->add_model( array(

             array('width'=>40, 'align'=>'center',  'title'=>App::xlat('Id'), 'name'=>'id' ),

             array('width'=>120, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('business'), 'name'=>'business_name'),
             array('width'=>120, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('username'), 'name'=>'username'),

             array('width'=>500, 'search'=>'false', 'title'=>App::xlat('description'), 'name'=>'description'),

             array('width'=>90, 'type'=>'date', 'title'=>App::xlat('event_date'), 'name'=>'event'),
             array('width'=>90, 'type'=>'date', 'title'=>App::xlat('created'), 'name'=>'created'),

             array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled') ), 'title'=>App::xlat('status'), 'name'=>'status')

         ))->setGrid_id('#social-listing')
         ->set_foot_bar('#footerBar')
         ->setOn_cell_select(",onCellSelect:function(row,col){
                                 if( col > 0 ){
                                   redirect( baseUrl + '/social/edit/' + row );
                                 }else{
                                   $custom_header_shared_code
                                 }
                              }")
         ->setOn_select_all(",onSelectAll:function(rows,status){ $custom_header_shared_code }")
         ->setWidth('980')
         ->setResize(true)
         ->jqGrid();
  }

  function jqGrid_admin_resources($parent=null){
    $grid = App::module('Core')->getModel('Grid');
    $custom_header_shared_code  = "if( jQuery( social.dom.list_toolbar ).length == 0 ){
                                     jQuery('div.ui-jqgrid-titlebar').after( '<div class=\"custom-cbox-bar\" >'+
                                       '<select onchange=\"social.status(this.value)\" id=\"cbox_status\"> <option value=\"\">Status</option> <option value=\"enabled\">Activo</option> <option value=\"disabled\">Inactivo</option> </select>'+
                                       '<span class=\"error\" id=\"listing_action\"></span>' +
                                     '</div>' );
                                   }else{
                                     if( ! jQuery( social.dom.list_grid + \" input[type='checkbox']\").is(':checked') ){
                                      jQuery( social.dom.list_toolbar ).remove();
                                     }
                                   }";
    $custom_header_last = "";

    $grid->setOptions( array('url'=>App::base('/social/list-data-loader'), 'caption'=>App::xlat('jqGrid_social_listing_topic'), 'sortname'=>'id', 'sortorder'=>'asc', 'hidegrid'=>'false') )
         ->setAttribs( array('multiselect'=>'true', 'multiboxonly'=>'true', 'viewrecords'=>'true') )
         ->setNavigate(false)
         ->add_model( array(
                              array('width'=>40, 'align'=>'center',  'title'=>App::xlat('Id'), 'name'=>'id' ),

                              array('width'=>120, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('business'), 'name'=>'business_name'),
                              array('width'=>120, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('username'), 'name'=>'username'),

                              array('width'=>500, 'search'=>'false', 'title'=>App::xlat('description'), 'name'=>'description'),

                              array('width'=>90, 'type'=>'date', 'title'=>App::xlat('event_date'), 'name'=>'event'),
                              array('width'=>90, 'type'=>'date', 'title'=>App::xlat('created'), 'name'=>'created'),

                              array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled'),'deleted'=>App::xlat('deleted') ), 'title'=>App::xlat('status'), 'name'=>'status')

    ))->setGrid_id('#social-listing')
    ->set_foot_bar('#footerBar')
    ->setOn_cell_select(",onCellSelect:function(row,col){
                            if( col > 0 ){
                              redirect( baseUrl + '/social/edit/' + row );
                            }else{
                              $custom_header_shared_code
                            }
                         }")
    ->setOn_select_all(",onSelectAll:function(rows,status){ $custom_header_shared_code }")
    ->setWidth('980')
    ->setResize(true)
    ->jqGrid();
  }




  // gets grid content
  function jqGrid_list( $params=null ){
    if( empty($params) ){
      return null;
    }

    $is_admin = App::module('Acl')->getModel('Acl')->is_logged_user_admin_from_session();
    if( empty($is_admin) ){
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
                ->from( array('ss' => 'view_social'), array('id', 'business_name', 'username', 'description', 'event', 'created', 'status') )
                ->where( 'ss.business = ?', $this->session->user['username'] )
                ->where( 'ss.status != ?', 'deleted' )
                ->order( 'ss.event DESC' );
  }

  function jqGrid_admin_list( $params=null ){
    if( empty($params) ){
      return null;
    }
    return $this->_db->select()
            ->from( array('ss' => 'view_social'), array('id', 'business_name', 'username', 'description', 'event', 'created', 'status') )
            ->order( 'ss.event DESC' );
  }


  private function get_event($event='not-given', $exeption = false){
    $select = $this->_db->select()
                   ->from(array('ss' => 'view_social') )
                   ->where('ss.id    = ?', $event );
    $social = $this->_db->query( $select )->fetch();

    if( empty( $social ) ){
      return $exeption===true ? 
               App::module('Core')->exception( App::xlat('EXC_social_event_wasnt_found') . '[ERSOCIAL277]' )
             :
               null;
    }
    return $social;
  }

  private function social_folders($date=null,$id=null){
    if( empty($date) || empty($id) ){
      App::module('Core')->exception( App::xlat('EXC_social_event_folders_cannot_be_set') . '[ERSOCIAL230]' );
    }

    $base                     = WP . DS . $this->folder_config['folder'] . DS . $this->folder_config['social'];
    $this_social_event_folder = App::module('Core')->getModel('Dates')->toDate(12, $date ) . $id;

    $folders = array( 'base'       => $base,
                      'path'       => $this_social_event_folder,
                      'gallery'    => $this_social_event_folder.DS.'gallery',
                      'thumbnails' => $this_social_event_folder.DS.'gallery'.DS.'thumbnails',
                      'thumb'      => $this_social_event_folder.DS.'gallery'.DS.'admin-thumbs',
                      'url'        => str_replace(DS, '/', '/media/social' . $this_social_event_folder) );

    return $folders;
  }

  function edit($social='id-not-given'){
    $social = $this->get_event($social,true);

    $select = $this->_db->select()
                        ->from(array('sst'  => 'site_social_tags') )
                        ->where('sst.social = ?', $social['id'] );
    $social_tags = $this->_db->query( $select )->fetchAll();

    // parse tags
    if( is_array($social_tags) ){
      foreach( $social_tags AS $tags ){
        $st[]=$tags['tag'];
      }
      $social_tags = $st;
    }

    // data
    $this->business_session->business['social'] = array_merge($social, array('tags'=> $social_tags) );
    // folders
    $this->business_session->business['social']['folders'] = $this->social_folders( $this->business_session->business['social']['created'],$this->business_session->business['social']['id'] );

    return true;
  }



  function events($business='not-given', $status='enabled', $current_page = 1){
    $events = $this->_db->select()
                        ->from( array( 'vs' => 'view_social') )
                        ->where( 'vs.status   = ?', $status )
                        ->where( 'vs.business = ?', $business )
                        ->limit(10)
                        ->order('vs.event DESC');
    return $this->_db->query( $events )->fetchAll();
  }

  function get_event_images($event=null, $created=null){
    if( empty($created) || empty($event) ){
      return null;
    }

    $folders = $this->social_folders( $created, $event );

    $files = App::module('Core')->getModel('Filesystem')->get_files_from_path( $folders['base'].$folders['gallery'] , array( "include" => array("/\.jpg$/"), "exclude" => array("/listing\.jpg$/","/side\.jpg$/","/promote\.jpg$/","/social\.jpg$/","/article\.jpg$/","/aside-big\.jpg$/","/mobile\.jpg$/","/slider\.jpg$/") ) );
    return empty( $files ) ?
             null
           :
             array( 'images' => $files, 'path' => $folders['url'] );
  }

}