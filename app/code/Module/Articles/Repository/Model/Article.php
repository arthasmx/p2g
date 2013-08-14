<?php
class Module_Articles_Repository_Model_Article extends Core_Model_Repository_Model{
  const MOREBREAK_TAG       = '<!-- pagebreak -->';
  const MOREBREAK_SUBSTR    = 500;

  private   $core           = null;
  private   $session        = null;
  private   $image_config   = null;
  private   $folder_config  = null;

  function init(){
    $this->core          = App::module('Core')->getModel('Abstract');
    $this->session       = App::module('Core')->getModel('Namespace')->get( 'article' );
    $this->folder_config = $this->_module->getConfig('core','folders');
  }

  function get_article_list( $current_page = null, $type=null, $publicated=false, $past_next=false, $written_only=false, $status="all"){
    $select  = $this->core->_db->select()
                          ->from(array('va' => 'view_articles' ) )
                          ->where( 'va.language = ?', App::locale()->getLang() )
                          ->order( 'va.created DESC');

    if( ! empty($type) ){
      $select->where( 'va.type = ?', $type );
    }
    if( $status!=="all" ){
      $select->where( 'va.status = ?', $status );
    }
    if( $publicated===true ){
      $select->where( 'va.publicated <= ?', date("Y-m-d h:i:s") );
    }

    if( $past_next==="next" ){
      $select->where( 'va.event_date >= ?', date("Y-m-d h:i:s") );
    }elseif( $past_next==="past" ){
      $select->where( 'va.event_date <= ?', date("Y-m-d h:i:s") );
    }

    if( $written_only===true ){
      $select->where( 'va.written = 1' );
    }

    return $this->core->setPaginator_page($current_page)->paginate_query( $select );
  }

  function get_articles_for_content_slider($type=null, $limit=null){

    $articles  = $this->core->_db->select()
                      ->from(array('va' => 'view_articles' ) )
                      ->where( 'va.publicated <= ?', date("Y-m-d") )
                      ->where( "va.stop_publication = '0000-00-00' OR va.stop_publication IS NULL OR va.stop_publication < ?", date("Y-m-d") )
                      ->where( 'va.language = ?', App::locale()->getLang() )
                      ->where( 'va.promote = "enabled"' )
                      ->where( 'va.status  = "enabled"' )
                      ->where( 'va.written = 1' )
                      ->order( 'va.publicated DESC' );

    if( ! empty($limit) ){
      $articles->limit( $limit );
    }

    if( ! empty($type) ){
      $articles->where( 'va.type_id = ?', $type );
    }else{
      $articles->where( $this->core->grouped_where("va.type", array( App::xlat('anuncios') , App::xlat('articulos'), App::xlat('eventos') ) ) );
    }

    return $this->core->_db->query( $articles )->fetchAll();
  }

  function read_full_article($article_seo_OR_id = 'not_given!', $enabled_only=null, $throw_exceptions = false){
    $article = $this->core->_db->select()
                    ->from(array('va' => 'view_articles_admin' ) )
                    ->where( 'va.language = ?', App::locale()->getLang() )
                    ->where( 'va.seo = ?', $article_seo_OR_id )
                    ->orWhere('va.article_id = ?' , $article_seo_OR_id)
                    ->where( 'va.written = 1' )
                    ->limit(1);

    if( ! empty($enabled_only) ){
      $article->where( "va.status = ?", 'enabled' );
    }

    $article = $this->core->_db->query( $article )->fetch();

    if( empty($article) ){
      if($throw_exceptions===true){
        App::module('Core')->exception( App::xlat('EXC_article_wasnt_found') . '[ERR89]' );
      }else{
        return null;
      }
    }

    return $article;
  }

  function get_article_basic_data( $article_seo_OR_id = 'not_given!', $status="all" ){
    $article = $this->core->_db->select()
                    ->from(array('va' => 'view_articles' ) )
                    ->where( 'va.language = ?', App::locale()->getLang() )
                    ->where( 'va.seo = ?', $article_seo_OR_id )
                    ->orWhere('va.article_id = ?' , $article_seo_OR_id)
                    ->where( 'va.written = 1' )
                    ->limit(1);

    if( $status!=="all" ){
      $article->where( $this->core->grouped_where("va.status", array('enabled','promote') ) );
    }

    $article = $this->core->_db->query( $article )->fetch();
    return empty( $article ) ? false : $article;
  }

  function get_article( $article_seo_OR_id = "not_given!", $throw_exceptions = true ){
    $article = $this->get_article_basic_data( $article_seo_OR_id );

    if( empty($article) ){
      if($throw_exceptions===true){
        App::module('Core')->exception( App::xlat('EXC_article_wasnt_found') . '<br />Launched at method get_article, file Repository/Model/Article' );
      }else{
        return null;
      }
    }

    return $article;
  }

  function latest( $type='articulos' , $enabled_only = true){
    $select  = $this->core->_db->select()
                          ->from(array('va' => 'view_articles' ) )
                          ->where( 'va.type = ?', $type )
                          ->where( 'va.language = ?', App::locale()->getLang() )
                          ->order( 'va.publicated DESC')
                          ->limit( 15 );

    if( $enabled_only === true){
      $select->where('va.status = ?', 'enabled');
    }

    $events = $this->core->_db->query( $select )->fetchAll();
    return empty($events)? null : $events;
  }

  function get_article_addons($article_id = 0, $parse=null){
    $select = $this->core->_db->select()
                         ->from(array('af'  => 'articles_addons' ) )
                         ->where( 'af.status = "enabled"' )
                         ->where( 'af.article_id = ?', $article_id )
                         ->order( 'af.type' );

    $addons = $this->core->_db->query( $select )->fetchAll();

    if( empty($addons) ){
      return null;
    }

    return empty($parse) ?
      $addons
    :
      $this->parse_addons_to_select( $addons );
  }

  function parse_addons_to_select($addons=null){
    $parsed = array();
    foreach ($addons AS $addon){
      if( in_array($addon['type'], array('map','gallery') )){
        $parsed[$addon['type']] = $addon;
      }elseif($addon['type']=='link'){
        $parsed[$addon['type']][] = array_merge($addon, array('desc'=>$addon['description'], 'type'=>$addon['type'],'url'=>$addon['reference'] ) );
      }elseif($addon['type']=='video'){
        $parsed[$addon['type']][] = array_merge($addon, array('desc'=>$addon['description'], 'type'=>$addon['type'],'url'=> 'http://www.youtube.com/v/' . $addon['reference'] ) );
      }else{
        $parsed[$addon['type']][] = $addon;
      }
    }

    return $parsed;
  }

  function get_tags( $article=null, $parse=null){
    $tags = $this->core->_db->select()
                 ->from(array('vt' => 'view_tags' ) )
                 ->where( 'vt.language = ?', App::locale()->getLang() )
                 ->where( 'vt.article_id = ?', $article )
                 ->group('vt.seo');

    $tags = $this->core->_db->query( $tags )->fetchAll();

    if( empty($tags) ){
      return null;
    }

    return empty($parse) ?
      $tags
    :
      $this->parse_tags_to_select( $tags );
  }

  function related_tags($seo="no-seo-given"){
// metodo para obtener todas los tags en onde el articulo fue relacionado
// con esto nos sirve para mostrar otros resultados    
  }

  function parse_tags_to_select($tags=null){
    $parsed = array();
    foreach ($tags AS $tag){
      $parsed[] = $tag['seo'];
    }
    return $parsed;
  }

  function get_route_by_type($type_id=null){
    $route_by_type = array( $this->_module->getConfig('core','article_type_announcement_id')  => App::xlat('route_announcement')
                            ,$this->_module->getConfig('core','article_type_event_id')        => App::xlat('route_events')
                            ,$this->_module->getConfig('core','article_type_article_id')      => App::xlat('route_articles') );

    return empty($type_id) || !array_key_exists($type_id, $route_by_type) ?
             null
           :
             $route_by_type[$type_id];
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
      App::module('Core')->exception( App::xlat('EXC_article_wasnt_found') . '<br />Launched at method EDIT(), file Repository/Model/Article' );
    }

    $article['section'] = 'edit';
    $this->session->article = $article;
    return $article;
  }


 


  function article_list_grid(){
    $grid = App::module('Core')->getModel('Grid');
    $custom_header_shared_code  = "if( jQuery( articles.dom.list_toolbar ).length == 0 ){
                                     jQuery('div.ui-jqgrid-titlebar').after( '<div class=\"custom-cbox-bar\" >'+
                                            '<select onchange=\"articles.status(this.value)\" id=\"cbox_status\"> <option value=\"\">Status</option> <option value=\"enabled\">Activo</option> <option value=\"disabled\">Inactivo</option> </select>'+
                                            '<select onchange=\"articles.language(this.value)\" id=\"cbox_lang\"> <option value=\"\">Idioma</option> <option value=\"es\">Español</option> <option value=\"en\">English</option> </select>'+
                                            '<select onchange=\"articles.promote(this.value)\" id=\"cbox_promote\"> <option value=\"\">Promocionar</option> <option value=\"enabled\">Si</option> <option value=\"disabled\">No</option> </select>'+
                                            '<select onchange=\"articles.mobile(this.value)\" id=\"cbox_mobile\"> <option value=\"\">Dispositivo Móvil</option> <option value=\"enabled\">Activado</option> <option value=\"disabled\">Desactivado</option> </select>'+
                                            '<span class=\"error\" id=\"listing_action\"></span>' +
                                            '</div>' );
                                   }else{
                                     if( ! jQuery( articles.dom.list_grid + \" input[type='checkbox']\").is(':checked') ){
                                       jQuery( articles.dom.list_toolbar ).remove();
                                     }
                                   }";
    $custom_header_last = "";

    $grid->setOptions( array('url'=>App::base('/articles/list-data-loader'), 'caption'=>App::xlat('jqGrid_article_listing_topic'), 'sortname'=>'article_id', 'hidegrid'=>'false') )
         ->setAttribs( array('multiselect'=>'true', 'multiboxonly'=>'true', 'viewrecords'=>'true') )
        // ->avoid_searching( array('article_id','author','type','category','status','language','promote') )
         ->setNavigate(false)
         ->add_model( array( array('width'=>60,  'title'=>App::xlat('Id'), 'name'=>'article_id' ),
                      array('width'=>200, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('title'), 'name'=>'title'),
                      array('width'=>200, 'search'=>'false', 'sortable'=>'false', 'title'=>App::xlat('seo'),   'name'=>'seo'),

                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('created'), 'name'=>'created'),
                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('publicated'), 'name'=>'publicated'),
                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('event_date'), 'name'=>'event_date'),
                      array('width'=>90, 'type'=>'date', 'title'=>App::xlat('stop_publication'), 'name'=>'stop_publication'),

                      array('width'=>120, 'searchoptions'=>'{sopt:["cn"]}', 'title'=>App::xlat('author'), 'name'=>'author'),

                      array('width'=>120, 'type'=>'remote', 'surl'=>App::base('/articles/jqgrid-types'), 'title'=>App::xlat('type'), 'name'=>'type'),

                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('status'), 'name'=>'status'),
                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'es'=>'Español','en'=>'English'), 'title'=>App::xlat('language'), 'name'=>'language'),
                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('promote'), 'name'=>'promote'),
                      array('width'=>60, 'type'=>'select', 'options'=>array(''=>App::xlat('all'),'enabled'=>App::xlat('enabled'),'disabled'=>App::xlat('disabled')), 'title'=>App::xlat('mobile'), 'name'=>'mobile') )

         )->setGrid_id('#article-listing')
          ->set_foot_bar('#footerBar')
          ->setOn_cell_select(",onCellSelect:function(row,col){
                                 if( col > 0 ){
                                   redirect( baseUrl + '/articles/edit/' + row );
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
                         ->from( array('va' => 'view_articles'), array('article_id','title','seo','created','publicated','event_date','stop_publication','author','type_name','status','language','promote','mobile') )
                         ->where( 'va.type = ?', App::xlat('articulos') );

    // @todo : move this into a login session to check easily
    // @todo : create a new method to validate faster
    $user = App::module('Core')->getModel('Namespace')->get( 'user' );
    $user_privileges = App::module('User')->getModel('User')->get_privileges( $user->user['username'], true );
    if( ! in_array('admin', $user_privileges) && ! in_array('root', $user_privileges) ){
      $select->where( 'va.username = ?', $user->user['username'] );
    }

    return $this->core->setPaginator_page( @$params['page'] )
                      ->setItems_per_page( @$params['rows'] )
                      ->setGrid_id_container('article_id')
                      ->jqGrid_query_for_listing($select, $params);
  }

  /*
   * @todo: Modificar esta consulta para que obtenga los datos desde view_categories
   * problema: el idioma del seo de las categorias
   */
  function jqGrid_record_types(){
    $select  = $this->core->_db->select()->from( array('va' => 'view_articles'), array('type'=>'DISTINCT(type)','type_name') )->where('va.type != ?', App::xlat('section_page') );
    $records = $this->core->_db->query( $select )->fetchAll();
    if(empty($records)){
      return null;
    }

    $result = "<option value=''>".App::xlat('all')."</option>";
    foreach($records AS $record){
      $result .= "<option value='".$record['type']."'>".$record['type_name']."</option>";
    }
    return "<select>$result</select>";
  }



  private function get_media_folder(){
    return WP . DS . $this->folder_config['folder'];
  }

  private function get_article_folder($article_id=null, $date=null){
    if( empty($article_id) ){
      App::module('Core')->exception( App::xlat('EXC_article_id_missing') . '<br />Launched at method get_article_folder, file Repository/Model/Article' );
    }

    if( empty($date) ){
      $date = date("Y-m-d");
    }
    return  DS. $this->folder_config['articles'] .DS . App::module('Core')->getModel('Dates')->toDate(10, $date) .DS. $article_id . DS;
  }

  function set_article_folders($article_id=null, $date=null){
    $article = $this->get_article_folder($article_id, $date);

    $session->article['folders']['url']     = str_replace(DS,"/",$article );
    $session->article['folders']['article'] = $article;

    $session->article['folders']['base']    = $this->get_media_folder();
    $session->article['folders']['path']    = $session->article['folders']['base'] . $article;
    $session->article['folders']['gallery'] = $session->article['folders']['path'] . $this->folder_config['image'];
    $session->article['folders']['thumb']   = $session->article['folders']['gallery'] .DS. $this->folder_config['thumb'];
    $session->article['folders']['thumbnails']   = $session->article['folders']['gallery'] .DS. $this->folder_config['thumbnails'];

    return $session->article['folders'];
  }

}