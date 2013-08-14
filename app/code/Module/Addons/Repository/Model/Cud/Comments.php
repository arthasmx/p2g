<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Cud_Comments extends Module_Core_Repository_Model_Abstract {

  function comment($name=null, $email=null, $comment=null, $reference=null, $type='article'){
    App::module('Core')->getModel('Parser')->check_function_params( func_get_args() );

    $this->_db->beginTransaction();

      $comments_tbl = sprintf("INSERT INTO comments(reference,type,created) VALUES('%s','%s','%s');"
                              , $reference, $type, date('Y-m-d H:i:s') );
      $this->_db->query($comments_tbl);
      $id = $this->_db->lastInsertId();

      $comments_data_tbl = sprintf("INSERT INTO comments_data(comment_id,comment,author,email) VALUES(%d,'%s','%s','%s');"
                                   ,$id, $comment, $name, $email);
      $this->_db->query($comments_data_tbl);
      $data_id = $this->_db->lastInsertId();


      if( empty($id) || empty($data_id) ){
        $this->_db->rollBack();
        return null;
      }

    $this->_db->commit();
    return $id;
  }

  function reply($name=null, $email=null, $comment=null, $reference=null, $type=null, $parent=null, $child=null){
    $vars_to_check = func_get_args();
    unset($vars_to_check[6]);
    App::module('Core')->getModel('Parser')->check_function_params( $vars_to_check );

    $child = $this->_get_next_child($parent,$child);

    $this->_db->beginTransaction();

      $comments_tbl = sprintf("INSERT INTO comments(reference, parent_id, child_id, type, created) VALUES('%s',%d,'%s','%s','%s');"
                              , $reference, $parent, $child, $type, date('Y-m-d H:i:s') );
      $this->_db->query($comments_tbl);
      $id = $this->_db->lastInsertId();

      $comments_data_tbl = sprintf("INSERT INTO comments_data(comment_id,comment,author,email) VALUES(%d,'%s','%s','%s');"
                                   ,$id, $comment, $name, $email);
      $this->_db->query($comments_data_tbl);
      $data_id = $this->_db->lastInsertId();


      if( empty($id) || empty($data_id) ){
        $this->_db->rollBack();
        return null;
      }

    $this->_db->commit();
    return $id;
  }

  private function _get_next_child($parent=null,$child=null){
    $index_position = strrchr($child,".");

    $counter = $this->_db->select()
                    ->from(array('c' => 'comments' ) , array('total_children' => 'COUNT(*)' ) )
                    ->where('c.parent_id = ?', $parent);

    if( empty($child) || $index_position===FALSE ){
      $children = $this->_db->query( $counter )->fetch();
      return '1.' . ($children['total_children'] + 1);

    }else{
      $counter->where('c.child_id LIKE "'. $child .'.%"');
      $children = $this->_db->query( $counter )->fetch();
      return  $child . '.' . ($children['total_children'] + 1);
    }

  }

}