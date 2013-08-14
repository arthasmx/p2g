<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Comments extends Module_Core_Repository_Model_Abstract {

  function get_comment($id = 0){
    $select = $this->_basic_select();
    $select->where('c.id = ?', $id);

    $comment = $this->_db->query( $select )->fetch();
    return empty($comment)? null : $comment;
  }

  function get_comments($id = null, $current_page = 1, $type='articulos', $enabled_only = true){
    $parent_comments = $this->get_parent_comments($id, $current_page, $type, $enabled_only);
    if( empty($parent_comments) ){
      return null;
    }

    foreach($parent_comments['items'] AS $key => $comment){
      $parent_comments['items'][$key]['replies'] = $this->get_replies($comment['id']);
    }

    return $parent_comments;
  }

  function get_parent_comments($id = null, $current_page = 1, $type='articulos', $enabled_only = true){
    $select = $this->_basic_select();
    $select->where('c.reference = ?', $id)
           ->where('c.parent_id = 0')
           ->where('c.type = ?', $type)
           ->order('c.created DESC');

    if( $enabled_only === true ){
      $select->where('c.status = ?', "enabled");
    }

    $comments = $this->setPaginator_page($current_page)->paginate_query( $select );;
    return empty($comments)? null : $comments;
  }

  function get_replies($id=NULL, $enabled_only = true){
    $select = $this->_basic_select();
    $select->where('c.parent_id = ?', $id)->order('c.child_id ASC');

    if( $enabled_only === true ){
      $select->where('c.status = ?', "enabled");
    }

    $replies = $this->_db->query( $select )->fetchAll();
    return empty($replies)? array() : $replies;
  }

  function latest($enabled_only = true){
    $select = $this->_basic_select();

    if( $enabled_only === true){
      $select->where('status = ?', 'enabled');
    }
    $select->order('created DESC')->limit( App::getConfig('aside_comments_limit') );

    $comments = $this->_db->query( $select )->fetchAll();
    return empty($comments)? null : $comments;
  }

  function comment_url($reference=null,$type=null){
    if( empty($reference) || empty($type) ){
      return null;
    }

    if($type=='events' || $type=='announcement' ||$type=='article' ){
      $article_data = App::module('Articles')->getModel('Article')->get_article_basic_data($reference);
      return App::base( App::xlat('route_'.$type) . $article_data['seo'] );
    }

    /**
     * @todo: Complete this method to get valid URL when TYPE equals to gallery, image, video, audio
     */
  }

  private function _basic_select(){
    return $this->_db->select()
                        ->from( array('c'  => 'comments') )
                        ->join( array('cd' => 'comments_data'), 'cd.comment_id = c.reference',  array('comment','author','email','likes','dislikes','reported','category') );
  }

}