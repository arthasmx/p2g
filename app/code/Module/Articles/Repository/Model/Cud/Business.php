<?php
require_once 'Module/Core/Repository/Model/Db/Actions.php';
class Module_Articles_Repository_Model_Cud_Business extends Module_Core_Repository_Model_Db_Actions{

  private $article = null;
  private $params  = array();
  private $session = null;
  private $folders = null;
  private $addons  = null;

  function save($params=array()){
    $required_params = array('action','btn','tags');
    $this->session = App::module('Core')->getModel('Namespace')->get( 'user' );
    if( ! App::module('Core')->getModel('Arrays')->params_key_exists($params, $required_params) || empty($this->session) ){
      die ("{'error':'true'}");
    }

    $this->params = $params;
    $this->check_for_duplicated_values($this->params['action']);

    if( $this->params['action']=='save' ){
      $this->insert_new_article();
      $this->create_required_folders_to_article();

    }else{ // updates
      $this->update_article();
    }

    $this->article_to_session();
    return $this->article;
  }



  private function insert_new_article(){
    $db = $this->get_db();
    $db->beginTransaction();

    try{

      $this->set_table('articles');
      $data = array( 'type' => App::xlat('empresas') );
      $this->table->insert($data);
      $this->article = $db->lastInsertId();

      $this->set_table('articles_details');
      $created = date("Y-m-d H:i:s");
      $data = array(
          'article_id'       => $this->article,
          'language'         => $this->params['language'],
          'title'            => $this->params['title'],
          'seo'              => $this->params['seo'],
          'phone'            => $this->params['phone'],
          'address'          => $this->params['address'],
          'article'          => $this->params['article'],
          'created'          => $created,
          'publicated'       => ( empty($this->params['publicate_at']) ? null:$this->params['publicate_at'] ),
          'event_date'       => ( empty($this->params['event_date']) ? null:$this->params['event_date'] ),
          'stop_publication' => ( empty($this->params['stop_publication']) ? null:$this->params['stop_publication'] ),
          'username'         => $this->params['username']);
      $this->table->insert($data);

      $this->set_table('articles_tags');
      $where = $this->table->getAdapter()->quoteInto('article_id = ?', $this->article);
      $this->table->delete($where);

      foreach($this->params['tags'] AS $tag){
        $data = array( 'article_id' => $this->article, 'tag'=> $tag );
        $this->table->insert($data);
      }

        $article = App::module('Articles')->getModel('Business');
        $this->folders = $article->set_business_folders($this->article, $created);

      // try to create article's current folder before commit
        App::module('Core')->getModel('Filesystem')->create_folder( $this->folders['base'] , $this->folders['business'] );

      // updates the FOLDER field
        $this->set_table('articles');
        $data  = array( 'folder' => $this->folders['url'] );
        $where = $this->table->getAdapter()->quoteInto('article = ?', $this->article);
        $this->table->update($data, $where);

      // creates user in ACL table
        $pass = App::module('Acl')->getModel('Acl')->generate_password( $this->params['pass'], $created );
        $this->set_table('acl');
        $data = array(
            'username'  => $this->params['username'],
            'passwd'    => $pass,
            'email'     => $this->params['email'],
            'status'    => 'enabled',
            'created'   => $created);
        $this->table->insert($data);

        // creates user in USER table
        $this->set_table('user');
        $data = array(
            'username'       => $this->params['username'],
            'name'           => $this->params['title'],
            'created_acl_bk' => $created,
            'type'           => 'business');
        $this->table->insert($data);

        // creates business privilege to user in USER_PRIVILEGES table
        $this->set_table('user_privileges');
        $data = array(
            'username'  => $this->params['username'],
            'privilege' => 3);
        $this->table->insert($data);

        // creates business main page relation at USER_BUSINESS_MAIN_PAGE table
        $this->set_table('user_business_main_page');
        $data = array(
            'username' => $this->params['username'],
            'language' => $this->params['language'],
            'mainpage' => $this->article);
        $this->table->insert($data);

      $db->commit();
      return true;

    }catch(Exception $e){
      $db->rollBack();
      die("{'error':'saving error: insert_new_article [".$e->getMessage()."]'}");
    }

  }

  private function create_required_folders_to_article(){
    $file_system = App::module('Core')->getModel('Filesystem');

    $required_folders = array('gallery', 'gallery'.DS.'thumbnails', 'gallery'.DS.'admin-thumbs' );
    foreach($required_folders AS $folder){
      $file_system->create_folder( $this->folders['base'], $this->folders['business'] . DS . $folder );
    }

    return true;
  }

  private function update_article(){
    if( empty($this->params['article_id']) ){
      die("{'error':'true'}");
    }
    $this->article = $this->params['article_id'];
    $session       = App::module('Core')->getModel('Namespace')->get( 'business' );
    $this->folders = $session->business['folders'];
    $this->addons  = $session->business['addons'];

    $db = $this->get_db();
    $db->beginTransaction();

    try{

      if( ! empty($this->params['pass'])  &&  ! empty($this->params['confirmation']) ){
        if( $this->params['pass'] !== $this->params['confirmation']  ){
          die( json_encode( array('unmatch' => 'pass') ) );
        }else{
          $date  = date('Y-m-d H:i:s');
          $pwd   = App::module('Acl')->getModel('acl')->generate_password($this->params['pass'],$date);

          $this->set_table('acl');
          $data  = array( 'passwd' => $pwd, 'created' => $date, 'updated' => $date );
          $where = $this->table->getAdapter()->quoteInto('username = ?', $this->params['username']);
          $this->table->update($data, $where);

          $this->set_table('user');
          $data  = array( 'created_acl_bk' => $date );
          $where = $this->table->getAdapter()->quoteInto('username = ?', $this->params['username']);
          $this->table->update($data, $where);
        }
      }

      $this->set_table('articles_details');
      $data = array(
          'language'         => $this->params['language'],
          'title'            => $this->params['title'],
          'seo'              => $this->params['seo'],
          'phone'            => $this->params['phone'],
          'address'          => $this->params['address'],
          'article'          => $this->params['article'] );
      $where = $this->table->getAdapter()->quoteInto('article_id = ?', $this->article);

      // publicate ?
      if( $this->params['btn']==='save_publicate' ){
        $data = array_merge($data,array('status'=>'enabled'));
      }

      $this->table->update($data, $where);

      $this->set_table('articles_tags');
      $where = $this->table->getAdapter()->quoteInto('article_id = ?', $this->article);
      $this->table->delete($where);

      foreach($this->params['tags'] AS $tag){
        $data = array( 'article_id' => $this->article, 'tag'=> $tag );
        $this->table->insert($data);
      }

      $db->commit();
      return true;

    }catch(Exception $e){
      $db->rollBack();
      die("{'error':'true'}");
    }

  }

  private function article_to_session(){
    if ( empty($this->params) ){
      return false;
    }
    $session = App::module('Core')->getModel('Namespace');
    $session->clear( 'business' );

    if( $this->params['btn']==='next' || $this->params['btn']==='save' ){
      $business_session  = $session->get( 'business' );

      $fields = array();
      $fields_to_store_in_session = array('username','pass','title','seo','phone','address','email','tags','language','article');
      foreach ($this->params as $key=>$value) {
        if ( in_array($key, $fields_to_store_in_session) ) {
          $fields[$key] = $value;
        }
      }

      $fields['article_id'] = $this->article;
      $fields['folders']    = $this->folders;
      $fields['addons']     = $this->addons;
      $fields['type']       = App::xlat('empresas'); // multi-idioma de categorias (articulos[es], articles[en], archive[fr])
      $business_session->business = $fields;
    }else{
      // btn = save_close || save_new || save_publicate
      $session->clear( 'business_mainpix' );
      $session->clear( 'business_files' );
    }
    return true;
  }


  private function check_for_duplicated_values($action='save'){
    $duplicated=null;

    if( $action == 'save' ){
      $user  = App::module('Acl')->getModel('Acl')->does_this_username_already_exists( $this->params['username'] );
      if( ! empty($user) ){
        $duplicated[] = $user;
      }
    }

    $email = App::module('Acl')->getModel('Acl')->does_this_email_already_exists( $this->params['username'], $this->params['email'] );
    if( ! empty($email) ){
      $duplicated[] = $email;
    }

    if( ! empty($duplicated) ){
      die( json_encode($duplicated) );
    }

  }



  function relate_addon_to_article($article_id=null, $type=null, $reference=null, $description=null, $class=null){
    $db = $this->get_db();
    $this->set_table('articles_addons');

    $data = array( 'article_id' => $article_id, 'type' => $type, 'reference' => $reference, 'description' => $description, 'class' => $class);
    $this->table->insert($data);
    $id = $db->lastInsertId();

    $this->set_table('articles');
    $data = array('addon' => 'enabled');
    $this->table->update($data, array('article = ?' => $article_id ));

    return empty( $id ) ?
      null
    :
      $id;
  }

  function delete_addon_relation($article=null,$reference=null,$type=null){
    $this->set_table('articles_addons');
    $where[] = $this->table->getAdapter()->quoteInto('article_id = ?', $article);
    $where[] = $this->table->getAdapter()->quoteInto('reference  = ?', $reference);
    $where[] = $this->table->getAdapter()->quoteInto('type       = ?', $type);

    $this->table->delete($where);
  }



  function add_link($urls=null){
    if( empty($urls) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_all_fields_are_required') .' [ERE190]", "css_class":"error"}');
    }

    $this->set_table('articles_addons');
    $db      = $this->get_db();
    $session = App::module('Core')->getModel('Namespace')->get( 'business' );
    $db->beginTransaction();

    try{
      $where[] = $this->table->getAdapter()->quoteInto('article_id = ?', $session->business['article_id']);
      $where[] = 'type = "link" OR type = "video"';
      $this->table->delete($where);

      if( $urls==='none' ){ // remove all links previously set
        $db->commit();
        $session->business['addons']['links'] = null;
        die('{"status":true, "message":"'. App::xlat('jSon_success_saving_changes') .'", "css_class":"success"}');
      }

      foreach( $urls AS $url){
        if( empty($url['url']) || empty($url['desc']) || empty($url['type']) ){
          throw new Exception('{"status":false, "message":"'. App::xlat('jSon_error_all_fields_are_required') .' [ERE211]", "css_class":"error"}');
        }

        if( $url['type']=='video' ){
          $the_type      = 'video';
          $the_reference = $this->get_youtube_video_id( $url['url'] );
        }else{
          $the_type      = 'link';
          $the_reference = $url['url'];
        }

        $data = array( 'article_id'  => $session->business['article_id'],
            'type'        => $the_type,
            'reference'   => $the_reference,
            'description' => $url['desc'],
            'class'       => $url['type']);
        $this->table->insert($data);
      }

      $this->set_table('articles');
      $data = array('addon' => 'enabled');
      $this->table->update($data, array('article = ?' => $session->business['article_id'] ));

      $db->commit();
      $session->business['addons']['links'] = $urls;
      die('{"status":true, "message":"'. App::xlat('jSon_success_saving_changes') .'", "css_class":"success"}');
    }catch (Exception $e){
      $db->rollBack();
      unset($session->business['addons']['links']);
      die('{"status":false, "message":"'. App::xlat('jSon_error_all_fields_are_required') .' [ERE240]", "css_class":"error"}');
    }

  }

  function add_coordinates($coordinates=null){
    $this->session          = App::module('Core')->getModel('Namespace')->get( 'business' );
    // coordinates already in table ? updates, else, inserts :p
    $coord_in_session = empty( $this->session->business['addons']['map']['reference'] ) ? $this->get_previous_coordinates() : true;

    try{
      $this->set_table('articles_addons');
      if( empty($coord_in_session) ){
        $data = array( 'article_id' => $this->session->business['article_id'], 'type' => 'map','reference' => $coordinates, 'class' => 'map');
        $this->table->insert($data);

        $this->set_table('articles');
        $data = array('addon' => 'enabled');
        $this->table->update($data, array('article = ?' => $this->session->business['article_id'] ));

      }else{
        $data = array('reference' => $coordinates);
        $this->table->update($data, array('article_id = ?' => $this->session->business['article_id'], 'type =?' => 'map' ));
      }

      $this->session->business['addons']['map']['reference'] = $coordinates;
      die('{"status":true, "message":"'. App::xlat('jSon_success_coordinates_saved') .'", "css_class":"success"}');
    }catch(Exception $e){
      die('{"status":false, "message":"'. App::xlat('jSon_error_saving_coordinates') .'", "css_class":"error"}');
    }

  }

  function del_coordinates(){
    $session = App::module('Core')->getModel('Namespace')->get( 'business' );

    $this->set_table('articles_addons');
    $where[] = $this->table->getAdapter()->quoteInto('article_id = ?', $session->business['article_id']);
    $where[] = $this->table->getAdapter()->quoteInto('type  = ?', 'map');

    $this->table->delete($where);
    unset($session->business['addons']['map']);
    die('{"status":true, "message":"'. App::xlat('jSon_success_coordinates_saved') .'", "css_class":"success"}');
  }

  function get_youtube_video_id($url=null){
    preg_match( "/https?:\/\/(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[?=&+%\w-]*/i", $url, $match);
    return empty($match[1])?
    null
    :
    $match[1];
  }

  private function get_previous_coordinates(){
    $this->set_table('articles_addons');
    $select = $this->table->select()
                   ->where( 'article_id = ?', $this->session->business['article_id'] )
                   ->where( 'type       = ?', 'map');
    return $this->table->fetchAll($select)->toArray();
  }



  function update_field_value($type=null,$value=null,$ids=''){
    $ids = json_decode($ids);
    if( empty($type) || empty($value) || empty($ids) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_article_action_' . $type ) .'"}');
    }

    $this->set_table('articles_details');

    $db = $this->get_db();
    $db->beginTransaction();

    try{
      foreach($ids AS $id){
        $data  = array( $type => $value );
        $this->table->update($data, array('article_id = ?' => $id ));
      }

      $db->commit();
      die('{"status":true, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_article_action_' . $type ) .'"}');

    }catch(Exception $e){
      $db->rollBack();
      die('{"status":false, "message":"'. App::xlat('jSon_error_cannot_apply_changes_to_article_action_' . $type ) .'"}');
    }
  }

}