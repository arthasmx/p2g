<?php
class Module_Acl_Repository_Model_Acl extends Core_Model_Repository_Model {

  public $life          = null;

  private $user_data    = null;
  private $user         = null;

  const LIFETIME	= 432000; // 5 dias (3600 segundos x hora)

  function init() {
    $this->user = empty($this->user) ? App::module('User')->getModel('User')  :  $this->user;
  }

  function login($user=null, $pwd=null) {
    $this->user_data = $this->user->login($user, $this->generate_salt($pwd) );
    if ( empty($this->user_data) ) {
      App::module('Core')->getModel('Flashmsg')->error(App::xlat('LOGIN_bad_credentials'));
      return false;
    }

    if( $this->is_user_enabled() ){
      $this->user->save_user_data_to_session();
      $this->update_user_access();

      // TOWN USERS
      if( ! empty( $this->user_data['privileges']['4'] ) && sizeof($this->user_data['privileges'])===1  ){
        header("Location: " . App::www() . 'www/admin/user/town/' . $this->user_data['seo'] );
      }else{
        header("Location: " . App::www() . 'admin/' );
      }
      exit;
    }
    return false;
  }
  
  private function is_user_enabled(){
    switch ($this->user_data['status']){
      case 'enabled':
          return true;
          break;
      case 'mustvalidate':
          $error_message = App::xlat('ERROR_MUSTVALIDATE');
          break;
      case 'banned':
          $error_message = App::xlat('ERROR_BANNED');
          break;
      case 'reported':
          $error_message = App::xlat('ERROR_REPORTED');
          break;
      case 'disabled':
      default:
          $error_message = App::xlat('ERROR_DISABLED');
          break;
    }
    App::module('Core')->getModel('Flashmsg')->error( str_replace('%email%', $this->user_data['username'], $error_message ) );
    $this->logout();
    return false;
  }

  private function update_user_access(){
    require_once 'Module/Core/Repository/Model/Db/Actions.php';
    $db = new Module_Core_Repository_Model_Db_Actions;

    $db->set_table('acl');
    $params = array('lastlogin' => date("Y-m-d H:i:s"), 'access_ip' => App::module('Core')->getModel('Parser')->get_ip() );
    $where  = $db->table->getAdapter()->quoteInto('username = ?', $this->user_data['username']);

    $db->table->update($params, $where);
  }

  private function generate_salt($password=null){
    if( empty($password) ){
      App::module('Core')->exception( 'Forbidden', 403 );
    }
    return md5($this->_module->getConfig('core','salt') . $password);
  }

  function logout() {
    $this->user->unload_user_data();
    header("Location: " . App::www() );
    exit;
  }

  function is_user_logged() {
    $this->user_data = App::module('Core')->getModel('Namespace')->get( 'user' );

    if ( empty( $this->user_data->user )  ||  ($this->user_data->user['session_life'] <= time() )  ){
      App::module('Core')->getModel('Flashmsg')->error( App::xlat('ERROR_LOGIN_NOACTIVITY_NOPRIVILEGES') );
      $this->logout();
    }

    $this->user_data->user['session_life'] = $this->refresh_session_time();
    return true;
  }

  function refresh_session_time(){
    return time() + $this->_module->getConfig('core','session_life');
  }

  function get_user_privileges(){
    if( empty($this->user_data->user) ){
      App::module('Core')->getModel('Flashmsg')->error( App::xlat('ERROR_LOGIN_NOACTIVITY_NOPRIVILEGES') );
      $this->logout();
    }

    $core   = App::module('Core')->getModel('Abstract');
    $select = $core->_db->select()
                   ->from( array('p'  => 'privileges'),  array('p.id', 'p.name', 'p.privilege', 'p.picture') )
                   ->join( array('up' => 'user_privileges'), 'up.privilege = p.privilege',  array() )
                   ->where('up.username = ?', $this->user_data->user['username']);

    $privileges = $core->_db->query( $select )->fetchAll();

    if( empty($privileges) ){
      App::module('Core')->getModel('Flashmsg')->error( App::xlat('ERROR_LOGIN_NOACTIVITY_NOPRIVILEGES') );
      $this->logout();
    }

    return $privileges;
  }

  function get_logged_user_data(){
    return empty($this->user_data->user) ? null : $this->user_data->user;
  }

  function check_user_section_access(){
    $menus = App::module('Core')->getModel('Namespace')->get( 'user' );

    $uri = str_replace( array('/www/admin','/admin'), '', $_SERVER['REQUEST_URI']);

    if( ! in_array( $uri , $menus->user['menu']['allowed'] ) ){
      header("Location: " . App::base() );
      exit;
    }

  }

  function is_admin( $username = 'not-username-given-at-all!' ){
    $core   = App::module('Core')->getModel('Abstract');
    $select = $core->_db->select()
                        ->from( array('up'    => 'user_privileges'),  array() )
                        ->where( $this->core->grouped_where("up.privilege", array('55','777') ) )
                        ->where('up.username  = ?', $username);

    $admin = $core->_db->query( $select )->fetchAll();

    return empty($admin) ? false:true;
  }

  function is_logged_user_admin_from_session(){
    $privileges = $this->user->_namespace->user['privileges'];
    return ( empty($privileges['55']) && empty($privileges['777']) ) ? 
      false
    :
      true;
  }

  function generate_password($pass=null, $created=null){
    if( empty($pass) || empty($created) ){
      App::module('Core')->exception( App::xlat('EXC_acl_no_password_given_to_generate_salted_pass') . '[ERR135]' );
    }
    $hash_1 = $this->generate_salt($pass);
    $hash_2 = sha1($created);
    return sha1( $hash_1 . $hash_2 );
  }

  function does_this_username_already_exists($value=null){
    if( empty($value) ){
      return array('field'=>$value);
    }

    $core   = App::module('Core')->getModel('Abstract');
    $select = $core->_db->select()
                   ->from(array('a'  => 'acl'  ), array('is_duplicated' => "COUNT(a.username)") )
                   ->where("a.username= ?", $value);

    $duplicated = $core->_db->query( $select )->fetch();
    return ($duplicated['is_duplicated'] > 0) ? array( 'duplicated' => 'username' ):null;      
  }

  function does_this_email_already_exists($username=null,$value=null){
    if( empty($value) || empty($username) ){
      return array('field'=>$value);
    }

    $core   = App::module('Core')->getModel('Abstract');
    $select = $core->_db->select()
                   ->from(array('a'  => 'acl'  ), array('is_duplicated' => "COUNT(a.email)") )
                   ->where("a.username != ?", $username)
                   ->where("a.email = ?", $value);

    $duplicated = $core->_db->query( $select )->fetch();
    return ($duplicated['is_duplicated'] > 0) ? array( 'duplicated' => 'email' ):null;
  }

  function check_if_email_is_duplicated($email=null){
    if( empty($email) ){
      return array('field'=>'email');
    }

    $core   = App::module('Core')->getModel('Abstract');
    $select = $core->_db->select()
                        ->from(array('a'  => 'acl'  ), array('is_duplicated' => "COUNT(a.email)") )
                        ->where("a.email = ?", $email);

    $duplicated = $core->_db->query( $select )->fetch();
    return ($duplicated['is_duplicated'] > 0) ? array( 'duplicated' => 'email' ):null;
  }

  function does_town_seo_already_exists($seo=null){
    if( empty($seo) ){
      return array('field'=>$seo);
    }

    $core   = App::module('Core')->getModel('Abstract');
    $select = $core->_db->select()
                        ->from(array('st'  => 'site_towns'  ), array('is_duplicated' => "COUNT(st.id)") )
                        ->where("st.seo = ?", $seo);

    $duplicated = $core->_db->query( $select )->fetch();
    return ($duplicated['is_duplicated'] > 0) ? array( 'duplicated' => 'seo' ):null;
  }


  function business_register($params=null){
    $duplicated_email = $this->check_if_email_is_duplicated($params['user'],$params['user']);
    if( is_array($duplicated_email) ){
      return 0;
    }
    
    
    require_once 'Module/Core/Repository/Model/Db/Actions.php';
    $db = new Module_Core_Repository_Model_Db_Actions;
    $db2    = $db->get_db();

    $created = date('Y-m-d H:i:s');
    $pwd     = $this->generate_password($params['password'], $created);
    $hash    = substr( md5( $params['user'].time().rand(0,9999) ), 0, 32);
    $seo     = App::module('Core')->getModel('Parser')->string_to_seo( $params['name'] );

    $db2->beginTransaction();

    try{
      // login
      $db->set_table('acl');
      $data = array(
        'username' => $params['user'],
        'passwd' => $pwd,
        'email'    => $params['user'],
        'created'  => $created );
      $db->table->insert($data);

      // email confirmation
      $db->set_table('acl_account_activate');
      $data = array(
        'hash'         => $hash,
        'username'     => $params['user'],
        'request_ip'   => App::module('Core')->getModel('ip')->get(),
        'request_date' => $created,
        'expires'      => time()+self::LIFETIME );
      $db->table->insert($data);

      // user
      $db->set_table('user');
      $data = array(
          'username'       => $params['user'],
          'name'           => $params['name'],
          'seo'            => $seo,
          'created_acl_bk' => $created,
          'type'           => 'business',
          'city'           => $params['city'] );
      $db->table->insert($data);

      // privileges
      $db->set_table('user_privileges');
      $data = array(
          'username'   => $params['user'],
          'privilege'  => '3' );
      $db->table->insert($data);

      // article (business main page)
      $db->set_table('articles');
      $data = array( 'type' => App::xlat('empresas') );
      $db->table->insert($data);
      $article_id = $db2->lastInsertId();

      // article details (business main page details)
      $db->set_table('articles_details');
      $data = array(
          'article_id' => $article_id,
          'language'   => 'es',
          'title'      => $params['name'],
          'seo'        => $seo,
          'email'      => $params['user'],
          'phone'      => $params['phone'],
          'address'    => $params['address'],
          'created'    => $created,
          'publicated' => $created,
          'username'   => $params['user']);
      $db->table->insert($data);

      // user business main page
      $db->set_table('user_business_main_page');
      $data = array(
          'mainpage' => $article_id,
          'username' => $params['user'],
          'language' => 'es' );
      $db->table->insert($data);

/*
      $db->set_table('articles_tags');
      $where = $db->table->getAdapter()->quoteInto('article_id = ?', $this->article);
      $db->table->delete($where);
      foreach($this->params['tags'] AS $tag){
        $data = array( 'article_id' => $this->article, 'tag'=> $tag );
        $db->table->insert($data);
      }
  */    

      // create business folder
      $folders = App::module('Articles')->getModel('Business')->set_business_folders($article_id, $created);
      App::module('Core')->getModel('Filesystem')->create_folder( $folders['base'] , $folders['business'] );

      // updates the FOLDER field
      $db->set_table('articles');
      $data  = array( 'folder' => $folders['url'] );
      $where = $db->table->getAdapter()->quoteInto('article = ?', $article_id);
      $db->table->update($data, $where);

      $db2->commit();

      // App::events()->dispatch('module_acl_business_register',array("to"=>$params['user'], "comment"=>'comentarios no necesario enviar', "name"=>$params['name'], "email"=>$params['user']));
      return $article_id;

    }catch(Exception $e){
      $db2->rollBack();
      return 0;
    }

    // si se registra, mostrar mensaje de registro exitoso y que vaya a confirmar su email en unos minutos
    // no ? mostrar mensaje de error, poniendo los bordes de los campos con error en color rojo
  }



  function get_user_register_date_bk($username='no.username.given'){
    $core   = App::module('Core')->getModel('Abstract');
    $select = $core->_db->select()
                        ->from( array('u'  => 'user'),  array('u.created_acl_bk') )
                        ->where('u.username = ?', $username);

    $created_bk = $core->_db->query( $select )->fetch();

    if( empty($created_bk['created_acl_bk']) ){
      return null;
    }

    return $created_bk['created_acl_bk'];
  }

}