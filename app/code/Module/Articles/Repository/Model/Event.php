<?php
class Module_Articles_Repository_Model_Event extends Core_Model_Repository_Model{

  private   $core           = null;
  private   $session        = null;
  private   $image_config   = null;
  private   $folder_config  = null;

  function init(){
    $this->core          = App::module('Core')->getModel('Abstract');
    $this->session       = App::module('Core')->getModel('Namespace')->get( 'event' );
    $this->folder_config = $this->_module->getConfig('core','folders');
  }

  private function get( $date=null, $enabled_only = true){
    $select  = $this->core->_db->select()
                    ->from(array('va' => 'view_articles' ) )
                    ->where( 'va.type = ?', App::xlat( 'eventos' ) )
                    ->where( 'va.language = ?', App::locale()->getLang() )
                    ->order( 'va.event_date DESC')
                    ->limit( '15' );

    if( ! empty($date) ){
      $select->where( 'va.event_date = ?', $date );
    }
    if( $enabled_only === true){
      $select->where('va.status = ?', 'enabled');
    }

    $events = $this->core->_db->query( $select )->fetchAll();
    return empty($events)? null : $events;
  }

  function today(){
    return $this->get( date('Y-m-d') );
  }
  function tomorrow(){
    return $this->get( App::module('Core')->getModel('Dates')->tomorrow() );
  }
  function month($date_start=null,$date_end=null,$enabled_only=true){

    $select  = $this->core->_db->select()
                          ->from(array('va' => 'view_articles' ) )
                          ->where( 'va.type = ?', App::xlat( 'eventos' ) )
                          ->where( 'va.language = ?', App::locale()->getLang() )
                          ->where( "va.event_date BETWEEN '$date_start' AND '$date_end'" )
                          ->order( 'va.created DESC')
                          ->limit( '15' );
    if( $enabled_only === true){
      $select->where('va.status = ?', 'enabled');
    }

    $events = $this->core->_db->query( $select )->fetchAll();
    return empty($events)? null : $events;
  }



  function event_list_grid(){
    $grid = App::module('Core')->getModel('Grid');
    $custom_header_shared_code  = "if( jQuery( events.dom.list_toolbar ).length == 0 ){
                                     jQuery('div.ui-jqgrid-titlebar').after( '<div class=\"custom-cbox-bar\" >'+
                                            '<select onchange=\"events.status(this.value)\" id=\"cbox_status\"> <option value=\"\">Status</option> <option value=\"enabled\">Activo</option> <option value=\"disabled\">Inactivo</option> </select>'+
                                            '<select onchange=\"events.language(this.value)\" id=\"cbox_lang\"> <option value=\"\">Idioma</option> <option value=\"es\">Español</option> <option value=\"en\">English</option> </select>'+
                                            '<select onchange=\"events.promote(this.value)\" id=\"cbox_promote\"> <option value=\"\">Promocionar</option> <option value=\"enabled\">Si</option> <option value=\"disabled\">No</option> </select>'+
                                            '<select onchange=\"events.mobile(this.value)\" id=\"cbox_mobile\"> <option value=\"\">Dispositivo Móvil</option> <option value=\"enabled\">Activado</option> <option value=\"disabled\">Desactivado</option> </select>'+
                                            '<span class=\"error\" id=\"listing_action\"></span>' +
                                            '</div>' );
                                     }else{
                                       if( ! jQuery( events.dom.list_grid + \" input[type='checkbox']\").is(':checked') ){
                                         jQuery( events.dom.list_toolbar ).remove();
                                       }
                                     }";
    $custom_header_last = "";

    $grid->setOptions( array('url'=>App::base('/events/list-data-loader'), 'caption'=>App::xlat('jqGrid_event_listing_topic'), 'sortname'=>'article_id', 'hidegrid'=>'false') )
         ->setAttribs( array('multiselect'=>'true', 'multiboxonly'=>'true', 'viewrecords'=>'true') )
         ->setNavigate(false)
         ->add_model( array( array('width'=>60,  'title'=>App::xlat('Id'), 'name'=>'article_id' ),
                      array('width'=>200, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('title'), 'name'=>'title'),
                      array('width'=>200, 'search'=>'false', 'sortable'=>'false', 'title'=>App::xlat('seo'),   'name'=>'seo'),

                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('created'), 'name'=>'created'),
                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('publicated'), 'name'=>'publicated'),
                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('event_date'), 'name'=>'event_date'),
                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('stop_publication'), 'name'=>'stop_publication'),

                      array('width'=>120, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('author'), 'name'=>'author'),

                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('status'), 'name'=>'status'),
                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'es'=>'Español','en'=>'English'), 'title'=>App::xlat('language'), 'name'=>'language'),
                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('promote'), 'name'=>'promote'),
                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('mobile'), 'name'=>'mobile') )
  
         )->setGrid_id('#event-listing')
         ->set_foot_bar('#footerBar')
         ->setOn_cell_select(",onCellSelect:function(row,col){
                                if( col > 0 ){
                                  redirect( baseUrl + '/events/edit/' + row );
                                }else{
                                  $custom_header_shared_code
                                }
                              }")
         ->setOn_select_all(",onSelectAll:function(rows,status){ $custom_header_shared_code }")
         ->jqGrid();

  }

  function jqGrid_admin_list( $params=null ){
    if( empty($params) ){
      return null;
    }

    $select = $this->core->_db
                   ->select()
                   ->from( array('va' => 'view_articles'), array('article_id','title','seo','created','publicated','event_date','stop_publication','author','status','language','promote','mobile') )
                   ->where('va.type = ?', App::xlat('type_events') );

    return $this->core->setPaginator_page( @$params['page'] )
                ->setItems_per_page( @$params['rows'] )
                ->setGrid_id_container('article_id')
                ->jqGrid_query_for_listing($select, $params);
  }



  function edit( $article_seo_OR_id=null ){
    $article = $this->core->_db->select()
                    ->from(array('va' => 'view_articles_admin' ) )
                    ->where( 'va.language = ?', App::locale()->getLang() )
                    ->where( 'va.seo = ?', $article_seo_OR_id )
                    ->orWhere('va.article_id = ?' , $article_seo_OR_id)
                    ->where( 'va.written = 1' )
                    ->limit(1);
    $article = $this->core->_db->query( $article )->fetch();

    if( empty($article) ){
      App::module('Core')->exception( App::xlat('EXC_article_wasnt_found') . ' [ERE93]' );
    }

    $article['section'] = 'edit';
    $this->session->event = $article;
    return $article;
  }

  private function get_media_folder(){
    return WP . DS . $this->folder_config['folder'];
  }

  private function get_event_folder($article_id=null, $date=null){
    if( empty($article_id) ){
      App::module('Core')->exception( App::xlat('EXC_article_id_missing') . ' [EER89]' );
    }

    if( empty($date) ){
      $date = date("Y-m-d");
    }
    return  DS. $this->folder_config['events'] .DS . App::module('Core')->getModel('Dates')->toDate(10, $date) .DS. $article_id . DS;
  }

  function set_event_folders($article_id=null, $date=null){
    $event = $this->get_event_folder($article_id, $date);

    $session->event['folders']['url']     = str_replace(DS,"/",$event );
    $session->event['folders']['event'] = $event;

    $session->event['folders']['base']    = $this->get_media_folder();
    $session->event['folders']['path']    = $session->event['folders']['base'] . $event;
    $session->event['folders']['gallery'] = $session->event['folders']['path'] . $this->folder_config['image'];
    $session->event['folders']['thumb']   = $session->event['folders']['gallery'] .DS. $this->folder_config['thumb'];
    $session->event['folders']['thumbnails']   = $session->event['folders']['gallery'] .DS. $this->folder_config['thumbnails'];

    return $session->event['folders'];
  }

}