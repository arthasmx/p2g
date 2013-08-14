<?php
require_once 'Module/Core/Repository/Model/Db/Actions.php';
class Module_Addons_Repository_Model_Cud_Categories extends Module_Core_Repository_Model_Db_Actions{

  private $session = null;

  private function check_required_params($params=null){
    $required_params = array('name','seo','language','status','parent');
    $this->session   = App::module('Core')->getModel('Namespace')->get( 'user' );
    if( ! App::module('Core')->getModel('Arrays')->params_key_exists($params, $required_params) || empty($this->session->user) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_not_saved') .'"}');
    }
  }

  function create($params=null){
    $this->check_required_params($params);

    $db = $this->get_db();
    $db->beginTransaction();

    $range = App::module('Addons')->getModel('Categories')->get_category_range($params['parent']);

    try{
      $this->set_table('categories');

      $data  = array('izq' => new Zend_Db_Expr('izq + 2') );
      $where = $this->table->getAdapter()->quoteInto('izq > ?', $range['izq']);
      $this->table->update($data, $where);      

      $data  = array('der' => new Zend_Db_Expr('der + 2') );
      $where = $this->table->getAdapter()->quoteInto('der > ?', $range['izq']);
      $this->table->update($data, $where);

      $data = array(
        'created'    => date("Y-m-d H:i:s"),
        'username'   => $this->session->user['username'],
        'parent_id'  => $params['parent'],
        'izq'        => $range['izq'] + 1,
        'der'        => $range['izq'] + 2,
        'updated'    => date("Y-m-d H:i:s"),
        'updated_by' => $this->session->user['username'] );

      $this->table->insert($data);
      $id = $db->lastInsertId();

      $this->set_table('categories_lang');
      $data = array(
          'category_id' => $id,
          'language'    => $params['language'],
          'name'        => $params['name'],
          'seo'         => $params['seo'] );
      $this->table->insert($data);

      $db->commit();
      die($id);

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_not_saved') .'"}');
    }

  }

  function update($params=null){
    $this->check_required_params($params);

    $db = $this->get_db();
    $db->beginTransaction();

    try{
      $this->set_table('categories_lang');
      $data = array('language' => $params['language'],
                    'name'     => $params['name'],
                    'seo'      => $params['seo'] );
      $where = $this->table->getAdapter()->quoteInto('category_id = ?', $params['parent']);
      $this->table->update($data, $where);

      $this->set_table('categories');
      $data = array('updated'    => date("Y-m-d H:i:s"),
                    'updated_by' => $this->session->user['username'] );
      $where = $this->table->getAdapter()->quoteInto('id = ?', $params['parent']);
      $this->table->update($data, $where);

      $db->commit();
      die('{"status":true, "message":"'. App::xlat('jSon_error_category_updated') .'"}');

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_not_saved') .'"}');
    }
  }

  function delete($category=null){
    if( empty($category) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_missing') .'"}');
    }

    // WE CANNOT DELETE CATEGORIES WITH SUBCATEGORIES
    $this_category_has_subcategories = App::module('Addons')->getModel('Categories')->get_children( $category );
    if( ! empty($this_category_has_subcategories) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_not_empty') .'"}');
    }

/*
 * @todo: sacar articulos que esten dentro de esta categoria o alguna de su subcategorias y mostrar una advertencia al respecto
 */

    $db = $this->get_db();
    $db->beginTransaction();
    $categories = App::module('Addons')->getModel('Categories');
    $range  = $categories->get_category_range($category);
    $parent = $categories->get_parents($category,false);

    if( empty( $parent['parent'] ) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_main_cannot_be_deleted') .'"}');
    }

    try{
      $this->set_table('categories');

        $where[] = $this->table->getAdapter()->quoteInto('izq >= ?', $range['izq'] );
        $where[] = $this->table->getAdapter()->quoteInto('izq <= ?', $range['der'] );
        $this->table->delete($where);

        $data  = array('der' => new Zend_Db_Expr('der - ' . $range['to_rest']) );
        $where = $this->table->getAdapter()->quoteInto('der > ?', $range['der']);
        $this->table->update($data, $where);

        $data  = array('izq' => new Zend_Db_Expr('izq - ' . $range['to_rest']) );
        $where = $this->table->getAdapter()->quoteInto('izq > ?', $range['der']);
        $this->table->update($data, $where);

      $this->set_table('categories_lang');
        $where = $this->table->getAdapter()->quoteInto('category_id = ?', $category );
        $this->table->delete($where);

      $db->commit();
      die('{"status":true, "parent_id": '.$parent['parent'].'}');

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_main_cannot_be_deleted') .'"}');
    }    

  }
  



  function update_field_value($type=null,$value=null,$ids=''){
    $ids = json_decode($ids);
    if( empty($type) || empty($value) || empty($ids) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_update_db_error') .' [MY153]"}');
    }

    $db = $this->get_db();
    $db->beginTransaction();

    if( $type=='status' ){
      $this->set_table('categories');
    }elseif( $type=='language' ){
      $this->set_table('categories_lang');
    }else{
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_update_db_error') .' [MY161]"}');
    }

    try{
      foreach($ids AS $id){
        $category = App::module('Addons')->getModel('Categories')->get_category($id);

        $data    = array( $type => $value );
        $where[] = $this->table->getAdapter()->quoteInto('izq >= ?', $category['izq'] );
        $where[] = $this->table->getAdapter()->quoteInto('der <= ?', $category['der'] );
        $this->table->update($data, $where);

        $where = null;
      }

      $db->commit();
      die('{"status":true, "message":"'. App::xlat('jSon_error_category_updated') .'"}');

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false, "message":"'. App::xlat('jSon_error_category_update_db_error') .' [MY185]"}');
    }
  }

}