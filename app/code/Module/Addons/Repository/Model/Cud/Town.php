<?php
require_once 'Module/Core/Repository/Model/Db/Actions.php';
class Module_Addons_Repository_Model_Cud_Town extends Module_Core_Repository_Model_Db_Actions{

  private $town = null;
  private $params  = array();
  private $session = null;
  private $folders = null;
  private $addons  = null;

  function stat($seo=null, $status=null){
    if( empty($seo) || empty($status) ){
      return false;
    }

    $this->set_table('site_towns');
    $data  = array('status' => $status);
    $where = $this->table->getAdapter()->quoteInto('seo = ?', $seo );
    $this->table->update($data, $where);
    return true;
  }

  function save($params=array()){
    $required_params = array('action','btn','tags');
    // $this->session   = App::module('Core')->getModel('Namespace')->get( 'user' );
    if( ! App::module('Core')->getModel('Arrays')->params_key_exists($params, $required_params) ){
      die ("{'error':'true'}");
    }

    $this->params = $params;

    if( $this->params['action']=='save' ){
      $this->insert_new_town();
      $this->create_required_folders_to_town();

    }else{ // updates
      $this->update_town();
    }

    $this->town_to_session();
    return $this->town;
  }

  private function insert_new_town(){
    $db = $this->get_db();
    $db->beginTransaction();

    $folder = App::module('Addons')->getModel('Cities')->town_folder($this->params['city'],$this->params['seo']);
    $sort   = App::module('Addons')->getModel('Cities')->town_sort($this->params['city']);
    $user   = App::module('Core')->getModel('Namespace')->get( 'user' );

    try{
      $this->set_table('site_towns');
      $created = date("Y-m-d H:i:s");
      $data = array(
          'city'    => $this->params['city'],
          'name'    => $this->params['name'],
          'seo'     => $this->params['seo'],
          'article' => $this->params['article'],
          'author'  => $user->user['username'],
          'folder'  => $folder,
          'created' => $created,
          'sort'    => $sort);
      $this->table->insert($data);
      $this->town = $db->lastInsertId();

      // login
      $pwd = App::module('Acl')->getModel('Acl')->generate_password($this->params['pass'], $created);
      $this->set_table('acl');
      $data = array(
          'username' => $this->params['username'],
          'passwd'   => $pwd,
          'email'    => $this->params['username'],
          'status'   => 'enabled',
          'created'  => $created );
      $this->table->insert($data);

      // user
      $this->set_table('user');
      $data = array(
          'username'       => $this->params['username'],
          'name'           => $this->params['name'],
          'seo'            => $this->params['seo'],
          'created_acl_bk' => $created,
          'type'           => 'town',
          'city'           => $this->params['city'] );
      $this->table->insert($data);

      // privileges
      $this->set_table('user_privileges');
      $data = array(
          'username'   => $this->params['username'],
          'privilege'  => '4' ); // town
      $this->table->insert($data);

      // Tags
      $this->set_table('site_town_tags');
      $where = $this->table->getAdapter()->quoteInto('town = ?', $this->params['seo']);
      $this->table->delete($where);

      foreach($this->params['tags'] AS $tag){
        $data = array( 'town' => $this->params['seo'], 'tag'=> $tag );
        $this->table->insert($data);
      }

      $cities = App::module('Addons')->getModel('Cities');
      $this->folders = $cities->set_town_folders($folder);

      // try to create town's current folder before commit
        App::module('Core')->getModel('Filesystem')->create_folder( $this->folders['base'] , $this->folders['article'] );

      $db->commit();
      return true;

    }catch(Exception $e){
      $db->rollBack();
      die("{'error':'".$e->getMessage()."'}");
    }

  }

  private function update_town(){
    if( empty($this->params['article_id']) ){
      die("{'error':'article_id was not provided [ERR124]'}");
    }
    $seo_changed      = false;
    $username_changed = false;

    $this->town    = $this->params['article_id'];
    $this->session = App::module('Core')->getModel('Namespace')->get( 'town' );
    $this->folders = $this->session->town['folders'];
    $this->addons  = $this->session->town['addons'];
    $acl           = App::module('Acl')->getModel('Acl');

    $db = $this->get_db();
    $db->beginTransaction();

    try{

      // $email was changed ? check if email is duplicated
      if( $this->params['username'] !== $this->session->town['username'] ){
        $username_checked = $acl->does_this_username_already_exists( $this->params['username'] );
        if( is_array($username_checked)  ){
          die( json_encode( array('duplicated' => array('username') )) );
        }
        $username_changed=true;
      }
      // seo was modified, check if seo is duplicated
      if( $this->params['seo'] !== $this->session->town['seo'] ){
        $seo_checked = $acl->does_town_seo_already_exists( $this->params['seo'] );
        if( is_array($seo_checked)  ){
          die( json_encode( array('duplicated' => array('seo') )) );
        }
        $seo_changed=true;
      }

      // PASSWORD CHANGED
      if( ! empty($this->params['pass'])  &&  ! empty($this->params['confirmation']) ){
        if( $this->params['pass'] !== $this->params['confirmation']  ){
          die( json_encode( array('unmatch' => array('pass') )) );
        }else{
          $date  = date('Y-m-d H:i:s');
          $pwd   = $acl->generate_password($this->params['pass'],$date);

          $this->set_table('acl');
          $data  = array( 'passwd' => $pwd, 'created' => $date, 'updated' => $date );
          $where = $this->table->getAdapter()->quoteInto('username = ?', $this->session->town['username']);
          $this->table->update($data, $where);

          $this->set_table('user');
          $data  = array( 'created_acl_bk' => $date );
          $where = $this->table->getAdapter()->quoteInto('username = ?', $this->session->town['username']);
          $this->table->update($data, $where);
        }
      }


      $this->set_table('site_towns');
      $data = array( 'city'    => $this->params['city'],
                     'article' => $this->params['article']);

      // seo modified ?
      if( $seo_changed === true ){
        $data = array_merge($data,array('name' => $this->params['name'], 'seo' => $this->params['seo']));
      }
      // publicate ?
      if( $this->params['btn']==='save_publicate' ){
        $data = array_merge($data,array('status'=>'enabled'));
      }

      $where = $this->table->getAdapter()->quoteInto('seo = ?', $this->session->town['seo']);
      $this->table->update($data, $where);


      // deletes and reinserts tags (in case they were modified)
      $this->set_table('site_town_tags');
      $where = $this->table->getAdapter()->quoteInto('town = ?', $this->session->town['seo']);
      $this->table->delete($where);

      foreach($this->params['tags'] AS $tag){
        $data = array( 'town' => $this->session->town['seo'], 'tag'=> $tag );
        $this->table->insert($data);
      }


      // $email was changed ? pff!
        if( $username_changed===true ){

          $old = $this->session->town['username']; 
          $new = $this->params['username'];

          // acl
          $this->set_table('acl');
          $data  = array( 'username' => $new, 'email' => $new);
          $where = $this->table->getAdapter()->quoteInto('username = ?', $old);
          $this->table->update($data, $where);

          // user
          $this->set_table('user');
          $data  = array( 'username' => $new);
          $where = $this->table->getAdapter()->quoteInto('username = ?', $old);
          $this->table->update($data, $where);

          // user_privileges
          $this->set_table('user_privileges');
          $data  = array( 'username' => $new);
          $where = $this->table->getAdapter()->quoteInto('username = ?', $old);
          $this->table->update($data, $where);

          // articles_details
          $this->set_table('articles_details');
          $data  = array( 'username' => $new);
          $where = $this->table->getAdapter()->quoteInto('username = ?', $old);
          $this->table->update($data, $where);

          // articles_drafts
          $this->set_table('articles_drafts');
          $data  = array( 'username' => $new);
          $where = $this->table->getAdapter()->quoteInto('username = ?', $old);
          $this->table->update($data, $where);

          // site_promotions
          $this->set_table('site_promotions');
          $data  = array( 'username' => $new);
          $where = $this->table->getAdapter()->quoteInto('username = ?', $old);
          $this->table->update($data, $where);

          // site_social
          $this->set_table('site_social');
          $data  = array( 'username' => $new);
          $where = $this->table->getAdapter()->quoteInto('username = ?', $old);
          $this->table->update($data, $where);


          // AUTHOR instead of USERNAME (wrong!)


          // site_banner
          $this->set_table('site_banner');
          $data  = array( 'author' => $new);
          $where = $this->table->getAdapter()->quoteInto('author = ?', $old);
          $this->table->update($data, $where);

          // site_towns
          $this->set_table('site_towns');
          $data  = array( 'author' => $new);
          $where = $this->table->getAdapter()->quoteInto('author = ?', $old);
          $this->table->update($data, $where);

          // site_town_sections
          $this->set_table('site_town_sections');
          $data  = array( 'author' => $new);
          $where = $this->table->getAdapter()->quoteInto('author = ?', $old);
          $this->table->update($data, $where);

          // user session
          $user_session = App::module('Core')->getModel('Namespace')->get('user');
          $user_session->user['username'] = $new;
        }

      // password was changed ?
        $this->params['pass'] = trim($this->params['pass']);
        if( $this->params['pass'] !== $this->session->town['pass'] ){
          $acl_created_date = $acl->get_user_register_date_bk($this->session->town['username']);
          if( empty($acl_created_date) ){
            App::module('Core')->exception( App::xlat('EXC_password_was_not_changed') . '[ERR179]' );
          }
          $pwd = $acl->generate_password($this->params['pass'],$acl_created_date);

          $this->set_table('acl');
          $data  = array( 'passwd' => $pwd );
          $where = $this->table->getAdapter()->quoteInto('username = ?', $this->session->town['username']);
          $this->table->update($data, $where);
        }

        // seo was changed ?
        if( $seo_changed === true ){
          $name     = $this->params['name'];
          $new      = $this->params['seo'];
          $old      = $this->session->town['seo'];
          $username = $this->session->town['username'];

        // user
          $this->set_table('user');
          $data  = array( 'seo' => $new);
          $where = $this->table->getAdapter()->quoteInto('username = ?', $username);
          $this->table->update($data, $where);

        // site_towns
          $this->set_table('site_towns');
          $data  = array( 'seo' => $new, 'name'=>$name);
          $where = $this->table->getAdapter()->quoteInto('seo = ?', $old);
          $this->table->update($data, $where);

        // site_town_addons
          $this->set_table('site_town_addons');
          $data  = array( 'town' => $new);
          $where = $this->table->getAdapter()->quoteInto('town = ?', $old);
          $this->table->update($data, $where);

        // site_town_sections
          $this->set_table('site_town_sections');
          $data  = array( 'town' => $new);
          $where = $this->table->getAdapter()->quoteInto('town = ?', $old);
          $this->table->update($data, $where);

        // site_town_tags
          $this->set_table('site_town_tags');
          $data  = array( 'town' => $new);
          $where = $this->table->getAdapter()->quoteInto('town = ?', $old);
          $this->table->update($data, $where);

        }

      $db->commit();
      return true;

    }catch(Exception $e){
      $db->rollBack();
      die("{'error':'".$e->getMessage()."'}");
    }

  }



  private function town_to_session(){
    if ( empty($this->town) || empty($this->params) ){
      return false;
    }
    
    $session = App::module('Core')->getModel('Namespace');
    $session->clear( 'town' );

    if( $this->params['btn']==='next' || $this->params['btn']==='save' ){
      $article_session  = $session->get( 'town' );

      $fields = array();
      $fields_to_store_in_session = array('city','seo','name','tags','article','username','pass','article_id');
      foreach ($this->params as $key=>$value) {
        if ( in_array($key, $fields_to_store_in_session) ) {
          $fields[$key] = $value;
        }
      }

      $fields['article_id']  = $this->town;
      $fields['folders']     = $this->folders;
      $fields['addons']      = $this->addons;
      $article_session->town = $fields;
    }else{
      // btn = save_close || save_new || save_publicate
    }
    return true;
  }

  private function create_required_folders_to_town(){
    $file_system      = App::module('Core')->getModel('Filesystem');
    $required_folders = array('towns', 'gallery', 'gallery'.DS.'thumbnails', 'gallery'.DS.'admin-thumbs' );
    foreach($required_folders AS $folder){
      $file_system->create_folder( $this->folders['base'], $this->folders['article'] . DS . $folder );
    }
    return true;
  }



  function save_section($params){
    $required_params = array('town_section','town_status','section_desc');
    $session         = App::module('Core')->getModel('Namespace')->get( 'town' );
    $user_session    = App::module('Core')->getModel('Namespace')->get( 'user' );
    $db              = $this->get_db();

    if( ! App::module('Core')->getModel('Arrays')->params_key_exists($params, $required_params) || empty($session->town['seo']) || empty($user_session->user['username']) ){
      die ("{'error':'true'}");
    }

    $this->set_table('site_town_sections');

    if( $params['town_status']=='not-available' ){
      // insertamos la section
      $data = array(
          'town'    => $session->town['seo'],
          'section' => $params['town_section'],
          'article' => $params['section_desc'],
          'created' => date("Y-m-d H:i:s"),
          'author'  => $user_session->user['username']);
      $this->table->insert($data);
      $town_section = $db->lastInsertId();

    }else{
      // actualizamos la section
      $data = array(
          'article' => $params['section_desc'],
          'author'  => $user_session->user['username']);

      $where[] = $this->table->getAdapter()->quoteInto('town = ?', $session->town['seo']);
      $where[] = $this->table->getAdapter()->quoteInto('section = ?', $params['town_section']);

      $this->table->update($data, $where);
      $town_section = 100;
    }

    // adding section to session
    $session->town['sections'][ $params['town_section'] ] = array(
      'status'  => 'available',
      'article' => $params['section_desc']
    );

    return $town_section;
  }

  function add_link($urls=null){
    if( empty($urls) ){
      die('{"status":false, "message":"'. App::xlat('jSon_error_all_fields_are_required') .' [ER215]", "css_class":"error"}');
    }

    $db      = $this->get_db();
    $session = App::module('Core')->getModel('Namespace')->get( 'town' );

    $db->beginTransaction();
    $this->set_table('site_town_addons');

    try{
      $where[] = $this->table->getAdapter()->quoteInto('town = ?', $session->town['seo']);
      $where[] = 'type = "link" OR type = "video"';
      $this->table->delete($where);

      if( $urls==='none' ){
        $db->commit();
        $session->town['addons']['links'] = null;
        die('{"status":true, "message":"'. App::xlat('jSon_success_saving_changes') .'", "css_class":"success"}');
      }

      foreach( $urls AS $url){
        if( empty($url['url']) || empty($url['desc']) || empty($url['type']) ){
          throw new Exception('{"status":false, "message":"'. App::xlat('jSon_error_all_fields_are_required') .' [ER220]", "css_class":"error"}');
        }

        if( $url['type']=='video' ){
          $the_type      = 'video';
          $the_reference = $this->get_youtube_video_id( $url['url'] );
        }else{
          $the_type      = 'link';
          $the_reference = $url['url'];
        }

        $data = array( 'town'        => $session->town['seo'],
                       'type'        => $the_type,
                       'reference'   => $the_reference,
                       'description' => $url['desc'],
                       'class'       => $url['type']);
        $this->table->insert($data);
      }

      $db->commit();
      $session->town['addons']['links'] = $urls;
      die('{"status":true, "message":"'. App::xlat('jSon_success_saving_changes') .'", "css_class":"success"}');
    }catch (Exception $e){
      $db->rollBack();
      unset($session->town['addons']['links']);
      die('{"status":false, "message":"'. App::xlat('jSon_error_all_fields_are_required') .' [ER245]", "css_class":"error"}');
    }

  }

  function get_youtube_video_id($url=null){
    preg_match( "/https?:\/\/(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[?=&+%\w-]*/i", $url, $match);
    return empty($match[1])?
    null
    :
    $match[1];
  }

  function quit_section($section=null){
    if( empty($section) ){
      die("{'error':'true'}");
    }
    $session = App::module('Core')->getModel('Namespace')->get( 'town' );

    $this->set_table('site_town_sections');    
    $where[] = $this->table->getAdapter()->quoteInto('town = ?', $session->town['seo']);
    $where[] = $this->table->getAdapter()->quoteInto('section = ?', $section);
    $this->table->delete($where);

    unset( $session->town['sections'][ $section ] );
    die("100");
  }


  function add_map_coordinates($coordinates=null){
    $this->session = App::module('Core')->getModel('Namespace')->get( 'town' );
    // coordinates already in table ? updates, else, inserts :p
    $coord_in_session = empty( $this->session->town['addons']['map']['reference'] ) ? $this->get_previous_coordinates() : true;

    try{
      $this->set_table('site_town_addons');
      if( empty($coord_in_session) ){
        $data = array( 'town' => $this->session->town['seo'], 'type' => 'map','reference' => $coordinates, 'class' => 'map');
        $this->table->insert($data);
      }else{
        $data = array('reference' => $coordinates);
        $this->table->update($data, array('town = ?' => $this->session->town['seo'], 'type =?' => 'map' ));
      }

      $this->session->town['addons']['map']['reference'] = $coordinates;
      die('{"status":true, "message":"'. App::xlat('jSon_success_coordinates_saved') .'", "css_class":"success"}');
    }catch(Exception $e){
      die('{"status":false, "message":"'. App::xlat('jSon_error_saving_coordinates') .'", "css_class":"error"}');
    }

  }

  private function get_previous_coordinates(){
    $this->set_table('site_town_addons');
    $select = $this->table->select()
                          ->where( 'town = ?', $this->session->town['seo'] )
                          ->where( 'type       = ?', 'map');
    return $this->table->fetchAll($select)->toArray();
  }  

  function del_map_coordinates(){
    $session = App::module('Core')->getModel('Namespace')->get( 'town' );

    $this->set_table('site_town_addons');
    $where[] = $this->table->getAdapter()->quoteInto('town = ?', $session->town['seo']);
    $where[] = $this->table->getAdapter()->quoteInto('type = ?', 'map');
    $this->table->delete($where);

    unset($session->town['addons']['map']);
    die('{"status":true, "message":"'. App::xlat('jSon_success_coordinates_saved') .'", "css_class":"success"}');
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