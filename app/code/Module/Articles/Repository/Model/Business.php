<?php
class Module_Articles_Repository_Model_Business extends Core_Model_Repository_Model{

  private   $core           = null;
  private   $session        = null;
  private   $image_config   = null;
  private   $folder_config  = null;

  function init(){
    $this->core          = App::module('Core')->getModel('Abstract');
    $this->session       = App::module('Core')->getModel('Namespace')->get( 'business' );
    $this->folder_config = $this->_module->getConfig('core','folders');
  }

  function business_list_grid(){
    $grid = App::module('Core')->getModel('Grid');
    $custom_header_shared_code  = "if( jQuery( business.dom.list_toolbar ).length == 0 ){
                                     jQuery('div.ui-jqgrid-titlebar').after( '<div class=\"custom-cbox-bar\" >'+
                                            '<select onchange=\"business.status(this.value)\" id=\"cbox_status\"> <option value=\"\">Status</option> <option value=\"enabled\">Activo</option> <option value=\"disabled\">Inactivo</option> </select>'+
                                            '<select onchange=\"business.language(this.value)\" id=\"cbox_lang\"> <option value=\"\">Idioma</option> <option value=\"es\">Español</option> <option value=\"en\">English</option> </select>'+
                                            '<select onchange=\"business.promote(this.value)\" id=\"cbox_promote\"> <option value=\"\">Promocionar</option> <option value=\"enabled\">Si</option> <option value=\"disabled\">No</option> </select>'+
                                            '<select onchange=\"business.mobile(this.value)\" id=\"cbox_mobile\"> <option value=\"\">Dispositivo Móvil</option> <option value=\"enabled\">Activado</option> <option value=\"disabled\">Desactivado</option> </select>'+
                                            '<span class=\"error\" id=\"listing_action\"></span>' +
                                            '</div>' );
                                     }else{
                                       if( ! jQuery( business.dom.list_grid + \" input[type='checkbox']\").is(':checked') ){
                                         jQuery( business.dom.list_toolbar ).remove();
                                       }
                                     }";
    $custom_header_last = "";

    $grid->setOptions( array('url'=>App::base('/business/list-data-loader'), 'caption'=>App::xlat('jqGrid_business_listing_topic'), 'sortname'=>'article_id', 'hidegrid'=>'false') )
         ->setAttribs( array('multiselect'=>'true', 'multiboxonly'=>'true', 'viewrecords'=>'true') )
         ->setNavigate(false)
         ->add_model( array( array('width'=>60,  'title'=>App::xlat('Id'), 'name'=>'article_id' ),
                      array('width'=>200, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('title'), 'name'=>'title'),
                      array('width'=>200, 'search'=>'false', 'sortable'=>'false', 'title'=>App::xlat('seo'),   'name'=>'seo'),

                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('created'), 'name'=>'created'),
                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('publicated'), 'name'=>'publicated'),
                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('business_date'), 'name'=>'business_date'),
                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('stop_publication'), 'name'=>'stop_publication'),

                      array('width'=>120, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('author'), 'name'=>'author'),

                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('status'), 'name'=>'status'),
                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'es'=>'Español','en'=>'English'), 'title'=>App::xlat('language'), 'name'=>'language'),
                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('promote'), 'name'=>'promote'),
                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('mobile'), 'name'=>'mobile') )
  
         )->setGrid_id('#business-listing')
         ->set_foot_bar('#footerBar')
         ->setOn_cell_select(",onCellSelect:function(row,col){
                                if( col > 0 ){
                                  redirect( baseUrl + '/business/edit/' + row );
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
                   ->where('va.type = ?', App::xlat('empresas') );

    return $this->core->setPaginator_page( @$params['page'] )
                ->setItems_per_page( @$params['rows'] )
                ->setGrid_id_container('article_id')
                ->jqGrid_query_for_listing($select, $params);
  }

  function get_business_list( $current_page = null, $type=null, $status="all", $publicated=false, $written_only=false){
    $select  = $this->core->_db->select()
                    ->from( array('va' => 'view_articles' ) )
                    ->join( array('at' => 'articles_tags'), 'at.article_id = va.article_id',  array() )
                    ->where( 'va.language = ?', App::locale()->getLang() )
                    ->where( 'at.tag = ?', $type )
                    ->order( 'va.created DESC');

    if( $status!=="all" ){
      $select->where( 'va.status = ?', $status );
    }
    if( $publicated===true ){
      $select->where( 'va.publicated <= ?', date("Y-m-d h:i:s") );
    }

    if( $written_only===true ){
      $select->where( 'va.written = 1' );
    }

    return $this->core->setPaginator_page($current_page)->paginate_query( $select );
  }

  function get_business_page( $username=null){
    $select  = $this->core->_db->select()
                    ->from( array('vp' => 'user_business_main_page' ) )
                    // ->where( 'va.language = ?', App::locale()->getLang() )
                    ->where( 'vp.username= ?', $username)
                    ->limit(1);

    $page = $this->core->_db->query( $select )->fetch();
    return empty($page['mainpage'])?null:$page['mainpage'];
  }

  function get_by_tag( $tag='no-tag-given-from_you' ){
    $select  = $this->core->_db->select()
                               ->from( array('va' => 'view_articles' ) )
                               ->join( array('at' => 'articles_tags'), 'at.article_id = va.article_id',  array() )
                               ->where( 'va.language = ?', App::locale()->getLang() )
                               ->where( 'at.tag = ?', $tag )
                               ->group( 'va.article_id' )
                               ->order( 'va.created DESC');

    $tag = $this->core->_db->query( $select )->fetchAll();
    return empty($tag)?null:$tag;
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
    $this->session->business = $article;
    return $article;
  }



  private function get_media_folder(){
    return WP . DS . $this->folder_config['folder'];
  }

  private function get_business_folder($article_id=null, $date=null){
    if( empty($article_id) ){
      App::module('Core')->exception( App::xlat('EXC_article_id_missing') . ' [EER89]' );
    }

    if( empty($date) ){
      $date = date("Y-m-d");
    }
    return  DS. $this->folder_config['business'] .DS . App::module('Core')->getModel('Dates')->toDate(10, $date) .DS. $article_id . DS;
  }

  function set_business_folders($article_id=null, $date=null){
    $business = $this->get_business_folder($article_id, $date);

    $session->business['folders']['url']     = str_replace(DS,"/",$business );
    $session->business['folders']['business'] = $business;

    $session->business['folders']['base']    = $this->get_media_folder();
    $session->business['folders']['path']    = $session->business['folders']['base'] . $business;
    $session->business['folders']['gallery'] = $session->business['folders']['path'] . $this->folder_config['image'];
    $session->business['folders']['thumb']   = $session->business['folders']['gallery'] .DS. $this->folder_config['thumb'];
    $session->business['folders']['thumbnails']   = $session->business['folders']['gallery'] .DS. $this->folder_config['thumbnails'];

    return $session->business['folders'];
  }

}