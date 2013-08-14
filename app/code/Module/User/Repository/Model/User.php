<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_User_Repository_Model_User extends Module_Core_Repository_Model_Abstract {

  public $user 			     = null;

  protected $basic_data  = null;

  function init(){
    $this->_namespace  = App::module('Core')->getModel('Namespace')->get( 'user' );
  }

  function login( $user=null, $pass=null){
    return $this->get_basic_data( $user, $pass );
  }

  function get_basic_data( $user=null, $pass=null ){
    if ( empty($user) || empty($pass) ){
      return false;
    }

    $select = $this->_db->select()
                   ->from(array('a' => 'acl'  ) )
                   ->join(array('u' => 'user' ), 'a.username = u.username')
                   ->where( 'a.username = ?', $user )
                   ->where( "a.passwd= SHA1(CONCAT('".$pass."', SHA1(a.created)))");

    $this->basic_data = $this->_db->query( $select )->fetch();

    if( ! empty($this->basic_data) ){
      $this->basic_data['session_life'] = App::module('Acl')->getModel('Acl')->refresh_session_time();
      $this->basic_data['menu']         = App::module('Addons')->getModel('Menu')->get_admin($user);
      $this->basic_data['privileges']   = $this->get_privileges($user,true);

      return $this->basic_data;
    }
    return false;
  }

  function save_user_data_to_session(){
    if ( empty($this->basic_data) ){
      return false;
    }

    $fields = array();
    $fields_to_store_in_session = array('username', 'name', 'last_name', 'maiden_name', 'avatar', 'folder', 'profession', 'mailing_list', 'lastlogin', 'session_life', 'menu', 'allowed_menus', 'privileges', 'city');
    foreach ($this->basic_data as $key=>$value) {
      if ( in_array($key, $fields_to_store_in_session) ) {
       $fields[$key] = $value;
      }
    }
    $this->_namespace->user = $fields;
    return true;
  }

  function unload_user_data(){
   require_once('Zend/Session.php');
   Zend_Session::destroy();
   return true;
  }

  function get_privileges($username=null, $to_validate=null){
    $select  = $this->_db->select()
                    ->from(array('vup' => 'view_user_privileges' ) )
                    ->where( 'vup.username = ?', (empty($username)? $this->basic_data['username'] : $username) )
                    ->order( 'vup.privilege ASC' );

    $privileges = $this->_db->query( $select )->fetchAll();

    if( empty($privileges) ){
      return false;
    }

    if( empty($to_validate) ){
      return $privileges;
    }

    // parse privileges to make validations
    $priv = null;
    foreach($privileges AS $privilege){
      $priv[$privilege['privilege']] = $privilege['name'];
    }
    return $priv;
  }



  function get_users_by_type($type="not-a-valid-user-type"){
    $select  = $this->_db->select()
                         ->from(array('u' => 'user') )
                         ->where( 'u.type= ?', $type );

    $users = $this->_db->query( $select )->fetchAll();
    return empty($users) ? array() : $users;
  }

  function get_users_by_type_to_select( $type="not-a-valid-user-type" ){
    $users = $this->get_users_by_type( $type );
    if( ! empty($users) ){

      $business=array();
      foreach( $users AS $user ){
        $business[$user['username']]=$user['name'].' '.$user['last_name'].' '.$user['maiden_name'];
      }

      return $business;
    }

    // none found at get_users_by_type
    return array();
  }

}