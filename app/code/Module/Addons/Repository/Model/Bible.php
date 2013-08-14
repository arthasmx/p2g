<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Bible extends Module_Core_Repository_Model_Abstract {

  function search($string_to_search_for = null, $current_page = null, $store_keyword_in_session = true){
    if( empty($string_to_search_for) ){
      return null;
    }

    $select = $this->_db->select()
                   ->from(array('bi'  => 'rv60_bible'  ) )
                   ->join(array('bo'  => 'rv60_books'  ), 'bo.book_id = bi.book_id AND bo.lang_id = bi.lang_id', array('book','seo') )
                   ->join(array('la'  => 'languages'   ), 'la.id = bi.lang_id', array())
                   ->where('la.prefix = ?', App::locale()->getLang() )
                   ->order('bi.book_id ASC');

    $where = null;
    if( strlen($string_to_search_for) > 5 ){
      $select->where( "MATCH(texto) AGAINST(?)", $string_to_search_for );
      $where = "MATCH(texto) AGAINST(". $this->_db->quote($string_to_search_for) .")";
    }else{
      $select->where( "bi.texto LIKE ?", "%" .$string_to_search_for . "%" );
      $where = "bi.texto LIKE ". $this->_db->quote("%". $string_to_search_for ."%");
    }

    if( ! empty($store_keyword_in_session) ){
      $session = App::module('Core')->getModel('Namespace')->get( 'search' );
      $session->search['keyword'] = $string_to_search_for;
      $session->search['summary'] = $this->get_search_resume($where);
    }

    return $this->setPaginator_page($current_page)
                ->setFilter_section( App::xlat('LINK_bible') ) // Required to identify the search section. In this case, filters will be applied to BIBLE section
                ->setAjax_url( App::base('/') . 'bible/ajax-search' )
                ->ajax_paginate_query( $select, "bible.paginate('search-data','url')" );
  }

  function ajax_search_paginate($params=null){
    $session = App::module('Core')->getModel('Namespace')->get( 'search' );
    if( empty($params[App::xlat('route_paginator_page')]) || empty($session->search['keyword'])  ){
      return null;
    }
    return $this->search($session->search['keyword'], $params[App::xlat('route_paginator_page')], FALSE);
  }

  function get_search_resume($search_style=null){
    if( empty($search_style) ){return null;}

    $select = $this->_db->select()
                   ->from(array('bi' => 'rv60_bible' ) , array() )
                   ->join(array('bo' => 'rv60_books' ) , 'bo.book_id = bi.book_id AND bo.lang_id = bi.lang_id', array('verses' => 'COUNT(bo.seo)', 'bo.book', 'bo.seo', 'bo.testament') )
                   ->join(array('la' => 'languages'  ) , 'la.id = bi.lang_id', array() )
                   ->where('la.prefix = ?', App::locale()->getLang() )
                   ->where($search_style)
                   ->group('bo.book')
                   ->order('bi.book_id');

    $resume = $this->_db->query( $select )->fetchAll();
    return empty($resume) ? null : $resume;
  }

  function get_phrase(){
    $session = App::module('Core')->getModel('Namespace')->get( 'array_random' );
    $select = $this->prepare_phrase_query();

    if( ! empty($session->array_random) ){
      foreach($session->array_random AS $phrase_already_shown){
        $select->Where(" NOT (ph.book_id = {$phrase_already_shown['book_id']} AND ph.cap_id = {$phrase_already_shown['cap_id']} AND ph.ver_id = {$phrase_already_shown['ver_id']} ) ");
      }
    }

    $result = $this->_db->query( $select )->fetch();

    if ( empty($result) ){
      $session->array_random = array();
      $select = $this->prepare_phrase_query();
      $result = $this->_db->query( $select )->fetch();
    }

    if( ! empty($result) ){
      $session->array_random[] = array(  'book_id' => $result['book_id']
                                         , 'cap_id' => $result['cap']
                                         , 'ver_id' => $result['ver']
                                       );
      return $result;
    }
    return null;
  }

  protected function prepare_phrase_query(){
    return $this->_db->select()
                     ->from(array('bi'  => 'rv60_bible'  ), array('book_id','cap','ver','lang_id','texto') )
                     ->join(array('ph'  => 'rv60_phrase' ), 'ph.book_id = bi.book_id AND ph.cap_id = bi.cap AND ph.ver_id = bi.ver', array())
                     ->join(array('bo'  => 'rv60_books'  ), 'bo.book_id = bi.book_id AND bo.lang_id = bi.lang_id', array('book','seo') )
                     ->join(array('la'  => 'languages'   ), 'la.id = bi.lang_id')
                     ->where('la.prefix = ?', App::locale()->getLang() )
                     ->limit(1);
  }

  function get_book_details($book_seo_name = "not_given"){
    $select = $this->_db->select()
                   ->from(array('bo'  => 'rv60_books'  ), array('book_id','book','seo','lang_id', 'testament') )
                   ->join(array('la'  => 'languages'   ), 'la.id = bo.lang_id', array('name','prefix','namespace','status') )
                   ->where('la.prefix = ?', App::locale()->getLang() )
                   ->where('bo.seo = ?' , $book_seo_name)
                   ->limit(1);
    $details = $this->_db->query( $select )->fetch();

    if( empty($details) ){
      App::module('Core')->exception( App::xlat('EXC_book_wasnt_found') . '<br />Launched at method get_book_details, file Repository/Model/Bible' );
    }

    $summary         = $this->get_book_chapters_and_verses_summary($details['book_id'], $details['lang_id']);
    $string_to_array = App::module('Core')->getModel('Parser')->string_to_array( $details['book'], array("1ra", "2da", "3ra", "de") );
    return array_merge($details, $summary, $string_to_array);
  }

  function get_book_chapters_and_verses_summary($book_id = 0, $lang_id = 0){
    $select = $this->_db->select()
                   ->from( array('la'  => 'languages')  , array() )
                   ->join( array('bi'  => 'rv60_bible') , 
                                 'bi.lang_id = la.id',
                           array('verses' => 'COUNT(bi.ver)', 'chapter' => 'COUNT( DISTINCT(bi.cap) )') )
                   ->where('bi.book_id = ?' , $book_id)
                   ->where('la.id= ?', $lang_id );
    $sumary = $this->_db->query( $select )->fetch();

    return empty( $sumary) ? false : $sumary;
  }

  function get_verses($book_seo_name = "not_given", $chapter_id = 0){
    $select = $this->_db->select()
                   ->from(array('bi'  => 'rv60_bible'  ), array('id', 'ver', 'texto') )
                   ->join(array('bo'  => 'rv60_books'  ), 'bo.book_id = bi.book_id AND bo.lang_id = bi.lang_id', array() )
                   ->join(array('la'  => 'languages'   ), 'la.id = bi.lang_id', array())
                   ->where('la.prefix = ?', App::locale()->getLang() )
                   ->where('bo.seo = ?' , $book_seo_name)
                   ->where('bi.cap = ?' , $chapter_id);
    $verses = $this->_db->query( $select )->fetchAll();

    if( empty($verses) ){
      App::module('Core')->exception( App::xlat('EXC_verse_wasnt_found') . '<br />Launched at method get_verses, file Repository/Model/Bible' );
    }

    return $verses;
  }

  function get_verse($book_seo_name = "not_given", $chapter = 0, $verse = 0){

    $select = $this->_db->select()
                   ->from(array('bi'  => 'rv60_bible'  ), array('id', 'texto') )
                   ->join(array('bo'  => 'rv60_books'  ), 'bo.book_id = bi.book_id AND bo.lang_id = bi.lang_id', array() )
                   ->where('bo.seo = ?' , $book_seo_name)
                   ->where('bi.cap = ?' , $chapter)
                   ->where('bi.ver = ?' , $verse);
    $res = $this->_db->query( $select )->fetch();

    if( empty($res) ){
      App::module('Core')->exception( App::xlat('EXC_verse_wasnt_found') . '<br />Launched at method get_verse, file Repository/Model/Bible' );
    }

    $total_verses_in_chapter = $this->get_verses_in_chapter($book_seo_name, $chapter);

    return array_merge($res, array('verse'=>$verse, 'chapter'=>$chapter, 'verses_in_chapter' => $total_verses_in_chapter['total']));
  }

  function get_books($testament = null){

    $select = $this->_db->select()
                   ->from(array('bo'  => 'rv60_books'  ), array('book_id' ,'book' ,'seo', 'testament') )
                   ->join(array('la'  => 'languages'   ), 'la.id = bo.lang_id', array())
                   ->where('la.prefix = ?', App::locale()->getLang() )
                   ->group( array ('bo.book_id') )
                   ->order( array('bo.book_id ASC') );

    if( ($testament == "old") || ($testament == "new") ){
      $select->where( "bo.testament = ?", $testament );
    }

    $res = $this->_db->query( $select )->fetchAll();

    if( empty($res) ){
      App::module('Core')->exception( App::xlat('EXC_books_werent_found') . '<br />Launched at method get_verses, file Repository/Model/Bible' );
    }

    return $res;
  }

  function get_books_for_dropbox($testament = null){
    $books = $this->get_books( $testament );

    if( empty($books) ){
      return array("0" => App::xlat("EXC_books_werent_found"));
    }

    $current_testament = 'old';
    $options   = ($testament == 'new')? array() :  array('old' => App::xlat('BIBLE_book_choose'));
    foreach($books AS $book){
      if($book['testament'] <> $current_testament){
        $testament    = "new";
        $options['new'] = App::xlat('BIBLE_book_choose');
      }
      $options[ $book['seo'] ] = $book['book'];
    }
    return $options;
  }

  function get_books_for_dropbox_in_json($testament = null){
    return App::module('Core')->getModel('Json')->encode( $this->get_books_for_dropbox($testament) );
  }

  function get_chapters($book_seo = null){
    $select = $this->_db->select()
                        ->distinct()
                        ->from(array('bi'  => 'rv60_bible'  ), array('cap') )
                        ->join(array('bo'  => 'rv60_books'   ), 'bo.book_id = bi.book_id', array())
                        ->where('bo.seo = ?', $book_seo);

    $res = $this->_db->query( $select )->fetchAll();

    if( empty($res) ){
      App::module('Core')->exception( App::xlat('EXC_chapters_werent_found') .$book_seo. '<br />Launched at method get_chapters, file Repository/Model/Bible' );
    }

    return $res;
  }

  function get_chapters_for_pagination($book_seo = null, $current_chapter = 1){
    $select = $this->_db->select()
                   ->distinct()
                   ->from(array('bi'  => 'rv60_bible'  ), array('cap') )
                   ->join(array('bo'  => 'rv60_books'   ), 'bo.book_id = bi.book_id', array())
                   ->where('bo.seo = ?', $book_seo)
                   ->where('bi.cap >= ?', $current_chapter-3)
                   ->where('bi.cap <= ?', $current_chapter+3);

    $res = $this->_db->query( $select )->fetchAll();

    if( empty($res) ){
      App::module('Core')->exception( App::xlat('EXC_chapters_werent_found') . '<br />Launched at method get_chapters_for_pagination, file Repository/Model/Bible' );
    }

    return $res;
  }

  function get_verses_for_pagination($book_seo = null, $current_chapter = null, $current_verse = null ){
    $select = $this->_db->select()
                   ->from(array('bi'  => 'rv60_bible'  ), array('ver') )
                   ->join(array('bo'  => 'rv60_books'  ), 'bo.book_id = bi.book_id AND bo.lang_id = bi.lang_id', array())
                   ->where('bo.seo = ?', $book_seo)
                   ->where('bi.cap = ?', $current_chapter)
                   ->where('bi.ver >= ?', $current_verse-5)
                   ->where('bi.ver <= ?', $current_verse+5);

    $res = $this->_db->query( $select )->fetchAll();

    if( empty($res) ){
      App::module('Core')->exception( App::xlat('EXC_verse_wasnt_found') . '<br />Launched at method get_verses_for_pagination, file Repository/Model/Bible' );
    }

    return $res;
  }

  function get_verses_in_chapter($book=null, $chapter=0){
    $select = $this->_db->select()
                   ->from(array('bi'  => 'rv60_bible'  ), array('total' => 'COUNT(bi.ver)') )
                   ->join(array('bo'  => 'rv60_books'  ), 'bo.book_id = bi.book_id AND bo.lang_id = bi.lang_id', array() )
                   ->where('bo.seo = ?' , $book)
                   ->where('bi.cap = ?' , $chapter);

    $verses = $this->_db->query( $select )->fetch();
    
    if( empty($verses) ){
      App::module('Core')->exception( App::xlat('EXC_verse_wasnt_found') . '<br />Launched at method get_verses_in_chapter, file Repository/Model/Bible' );
    }

    return array_merge($verses, array('chapter'=>$chapter) );
  }

}