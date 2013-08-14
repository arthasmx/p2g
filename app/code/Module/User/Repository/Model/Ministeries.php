<?php
class Module_User_Repository_Model_Ministeries extends Core_Model_Repository_Model{
  private   $core           = null;
  private   $session        = null;
  private   $image_config   = null;
  private   $folder_config  = null;

  function init(){
    $this->core          = App::module('Core')->getModel('Abstract');
    $this->session       = App::module('Core')->getModel('Namespace')->get( 'ministeries' );
    $this->folder_config = $this->_module->getConfig('core','ministery');
  }

  function list_grid(){
    $grid = App::module('Core')->getModel('Grid');
    $custom_header_shared_code  = "var frame = jQuery( 'fieldset#min-options' );
                                   if( jQuery( \"#ministeries-listing input[type='checkbox']\").is(':checked')  ){
                                     frame.removeClass('hide');
                                   }else{
                                     frame.addClass('hide');
                                   }";

    $custom_header_last = "";

    $grid->setOptions( array('url'=>App::base('/ministeries/list-data-loader'), 'sortname'=>'name', 'sortorder'=>'asc', 'hidegrid'=>'false') )
         ->setAttribs( array('multiselect'=>'true', 'multiboxonly'=>'true', 'viewrecords'=>'true') )
         ->setNavigate(false)
         ->add_model( array( array('width'=>200, 'search'=>'false', 'sortable'=>'false', 'title'=>App::xlat('username'), 'name'=>'username'), 
                      array('width'=>200, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('name'), 'name'=>'name'),
                      array('width'=>200, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('last_name'), 'name'=>'last_name'),
                      array('width'=>200, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('maiden_name'), 'name'=>'maiden_name') )

         )->setGrid_id('#ministeries-listing')
         ->set_foot_bar('#ministeriesFBar')
         ->setOn_cell_select(",onCellSelect:function(row,col){
                                if( col > 0 ){

                                  $.ajax({
                                    url: '". App::base('/ministeries/user') ."',
                                    type: 'post',
                                    data: {user:row},
                                    beforeSend:function(){
                                      blockUI_ajax_saving('#min-1','on', ministeries.msg.loading,'50%');
                                    },
                                    error:function(){
                                      blockUI_ajax_saving('#min-1','off');
                                    },
                                    success: function(data, textStatus, jqXHR) {
                                      blockUI_ajax_saving('#min-1','off');
                                      jQuery('#edit-ministeries').html(data).dialog({modal: true, width: '50em', height: 'auto',
                                                                                     buttons:{
                                                                                       '".App::xlat('BUTTON_save')."' : function(){
                                                                                         ministeries.save();
                                                                                       },
                                                                                       '".App::xlat('BUTTON_clear_selection')."': function() {
                                                                                         jQuery('#min_chosen option:selected').removeAttr('selected');
                                                                                       },
                                                                                       '".App::xlat('BUTTON_close')."': function() {
                                                                                         jQuery( this ).dialog('close');
                                                                                       }
                                                                                     },
                                                                                     close: function() {
                                                                                       jQuery('#ministeries-listing').jqGrid('resetSelection');
                                                                                       jQuery( 'fieldset#min-options' ).addClass('hide');
                                                                                     }
                                                                                    }).dialog('open');
                                    } });

                                }else{
                                  $custom_header_shared_code
                                }
                              }")
         ->setOn_select_all(",onSelectAll:function(rows,status){ $custom_header_shared_code }")
         ->jqGrid();

    if( empty($this->session->ministeries['form']) || empty($this->session->ministeries['html']) ){
      $form = App::module('Addons')->getModel('Categories')->get_family_grouped_for_select('pastor-principal');
      unset($form['Diaconos'],$form['Ministros'],$form['Secretaria'],$form['Co Pastor']);
      $main = array('Principales' => array('co-pastor'=>'Co Pastor', 'diaconos'=>'Díaconos', 'ministros'=>'Ministros', 'secretaria'=>'Secretaria') );
      $this->session->ministeries['form'] = array_merge($main,$form);
    
      $this->session->ministeries['html'] = App::module('Addons')->getModel('Categories')->get_family_grouped_for_select_grouped('pastor-principal');
    }

  }

  function jqGrid_admin_list( $params=null ){
    if( empty($params) ){
      return null;
    }

    $select = $this->core->_db->select()
                   ->from( array('u' => 'vista_users_to_add_ministery'), array('username','name','last_name','maiden_name') )
                   ->where('u.type != ?', 'bussiness' )
                   ->where('u.status = ?', 'enabled' );

    return $this->core->setPaginator_page( @$params['page'] )
                ->setItems_per_page( @$params['rows'] )
                ->setGrid_id_container('username')
                ->jqGrid_query_for_listing($select, $params);
  }

  function jqGrid_record_ministeries(){
    $select  = $this->core->_db->select()->from( array('u' => 'vista_users_to_add_ministery'), array('seo'=>'DISTINCT(seo)','ministery') )->where('u.status = ? AND u.seo IS NOT NULL', 'enabled' );
    $records = $this->core->_db->query( $select )->fetchAll();
    if(empty($records)){
      return null;
    }

    $result = "<option value=''>".App::xlat('all')."</option>";
    foreach($records AS $record){
      $result .= "<option value='".$record['seo']."'>".$record['ministery']."</option>";
    }
    return "<select>$result</select>";
  }

  function get_user_ministeries( $username=null){
    if ( empty($username) ){
      die( App::xlat('EXC_user_wasnt_found') . ' [ERU115]' );
    }

    $user = $this->core->_db->select()
                 ->from( array('u' => 'user'), array("CONCAT(u.name,' ',u.last_name,' ',u.maiden_name) AS name", 'avatar') )
                 ->where('u.username = ?', $username );
    $user = $this->core->_db->query( $user )->fetch();

    if( empty($user) ){
      die( App::xlat('EXC_user_wasnt_found') . ' [ERU115]' );
    }

    $ministeries = $this->core->_db->select()
                        ->from( array('um' => 'vista_user_ministeries'),array('ministery_id','ministery','seo') )
                        ->where('um.username = ?', $username );
    $ministeries = $this->core->_db->query( $ministeries )->fetchAll();

    return array_merge( $user, array('ministeries'=>$ministeries) );
  }



  function upload_picture( $file,$avatar,$user ){
    $folders = App::module('Articles')->getConfig('core','folders');
    $file    = WP . DS . $folders['folder'] . $avatar;

    if( ! empty($_FILES['user_min']) &&  move_uploaded_file( $_FILES["user_min"]["tmp_name"], $file) ){

      $image = App::module('Core')->getModel('Image');
      $image->resize_image($file, '159', 144,'exact');
      $image->saveImage( $file, 90 );

      // updates user image
      require_once 'Module/Core/Repository/Model/Db/Actions.php';
      $db = new Module_Core_Repository_Model_Db_Actions;

      $db->set_table('user');
      $params = array('avatar' => $avatar );
      $where  = $db->table->getAdapter()->quoteInto('username = ?', $user);
      $db->table->update($params, $where);

      die('{"status":true}');
    }
    die('{"status":false}');
  }

}