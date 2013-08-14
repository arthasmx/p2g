<?php
class Module_Acl_Repository_Model_Acl extends Core_Model_Repository_Model {

  public $user = null;

  public $username      = false;
  public $passwd        = false;  

  public $data          = array();
  public $privileges    = null;
  public $life          = null;

  protected $_resource  = null;
  protected $_namespace = null;
  protected $_errors    = array();
  protected $_messages  = false;

  const LIFE            = 14400;

  function init() {

    if (!$this->_namespace) {
      $this->_namespace = App::module('Core')->getModel('Namespace')->get(get_class($this));
    }

    $this->load_session_data();
    $this->save_session_data();

    $this->_messages = array(
        'ERROR_LOGIN'     => App::xlat('ERROR_LOGIN'), //'Usuario o contraseña incorrectos.',
        'ERROR_USER'      => App::xlat('ERROR_USER'), //'Usuario incorrectos.',
        'ERROR_PASSWD'    => App::xlat('ERROR_PASSWD'), //'Contraseña incorrecta.',
        'ERROR_LOCKED'    => App::xlat('ERROR_LOCKED'), //'El usuario <b>%user%</b> está bloqueado. Contacte con el sopote.',
        'ERROR_DELETED'   => App::xlat('ERROR_DELETED'), //'El usuario <b>%user%</b> ha sido desactivado permanentemente.',
        'ERROR_ACTIVE'    => App::xlat('ERROR_ACTIVE'), //'El usuario <b>%user%</b> no ha sido activado aún.',
        'WARNING_TIMEOUT' => App::xlat('WARNING_TIMEOUT') //'Ha pasado demasiado tiempo y su sesión ha sido finalizada.',
    );

  }

  protected function load_session_data() {
    foreach ($this->_namespace as $var=>$value) {
      $this->{$var}=$value;
    }
    $this->load_user_data();
  }

  protected function save_session_data() {
    foreach ($this as $var=>$value) {
      if ($var[0] != "_") {
        $this->_namespace->{$var}=$value;
      }
    }
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
    /**
     * Recarga los datos de la session despues de modificar el USERNAME.
     * Esto solo cuando se utilice el email como username
     */
    public function reload($datos=false,$username=false){
        if(!$datos || !$username) return false;
        $this->user = $username;
        $this->data = $datos;
        $this->save_session_data();
    }

    /**
     * Carga de datos del resource
     *
     * @return bool
     */
    protected function load_user_data($login=false) {

        // Si está identificado
            if ($this->user) {

                // Comprobamos el timeout
                    if ($this->life<=time()) {
                        $this->_addError('WARNING_TIMEOUT',array('user'=>$this->user));
                        App::events()->dispatch('module_acl_timeout',array('user'=>$this->user),false,Core_Model_Log::INFO);
                        $this->logout();
                        return false;
                    } else {

                        // Renovamos el timeout
                            $this->life=time()+self::LIFE;

                        if($login){

                            // Cargamos datos del usuario
                                $this->data=$this->_resource->reset()->setUsername($this->user)->getRow();
                            // Cargamos los privilegios
                                $this->data['privileges'] = $this->getAllUserPrivileges($this->user);
                            // Guardamos los idiomas disponibles de la aplicacion
                                $this->_setLanguages();
 
                                $this->_setPrivileges();
                                $this->save_session_data();

                                // Comprobamos el STATUS del usuario
                                /*
                                    0= Disabled
                                    1= activo OK!
                                    2= reported
                                    3= baneado/bloqueado
                                    4= hidden
                                    5= eliminado
                                 */
                                switch ($this->data['status']){
                                    case 0:
                                        $this->_addError('ERROR_ACTIVE',array('user'=>$this->user));
                                        $this->logout();
                                        return false;
                                        break;
                                    case 2:
                                        //App::log('debug')->info( '7 - Marcado como bloqueado' );
                                        $this->_addError('ERROR_LOCKED',array('user'=>$this->user));
                                        $this->logout();
                                        return false;
                                        break;
                                    case 3:
                                        //App::log('debug')->info( '7 - Marcado como eliminado' );
                                        $this->_addError('ERROR_DELETED',array('user'=>$this->user));
                                        $this->logout();
                                        return false;
                                        break;
                                }
                        }

                    }

                return true;
            }

            return false;
    }



    /**
     * Elimina datos de la sesión y reinicia el usermanager
     */
    protected function _unloadData() {
        // Eliminamos todos los atributos públicos y reiniciamos el namespace
            foreach ($this as $var=>$value) {
                if ($var[0]!="_") {
                    $this->{$var}=null;
                }
            }
            $this->_namespace->unsetAll();
            //$this->_namespace->lock();
    }



    protected function _setPrivileges() {
        $this->privileges=array();
        foreach ($this->data['privileges'] as $var=>$value) {
            $this->privileges[$value['area']]=1;
        }
    }

    protected function _setLanguages(){
        echo 'Mover este metodo al modulo ADDONS'; exit;
        $xLang = App::module('Addons')->getModel('Languages')->get_enabled_languages();
        foreach ($xLang as $lang) {
            $langs[$lang['prefix']]=$lang;
        }
        $this->data['languages']=$langs;
    }

// MAIN ****************************************************************************************

  function login($user,$pwd) {

$this->setUserName($user)->setPasswd(md5($pwd));
echo "<pre>"; print_r( $this->username );  echo "</pre>";
echo "<pre>"; print_r( $this->passwd );  echo "</pre>";
exit;    

    if ( $this->setUserName($user)->setPasswd(md5($pwd))->getRow() ) {
        $this->user = $user;
        $this->life = time()+self::LIFE;

        if ($this->load_user_data(true)) {
    
            // Actualizamos los datos del usuario para almacenar el acceso
                $this->_resource->reset()->setUsername($this->user)->updateAccess();
            // Lanzamos evento
                App::events()->dispatch('module_acl_login',array('user'=>$this->user),false,Core_Model_Log::NOTICE );
            return true;
        }
        return false;
    
    } else {
        $this->_addError('ERROR_LOGIN',array('user'=>$user,'pwd'=>$pwd));
        return false;
    }
  }


    /**
     * Decidimos a que area y pagina de nuevo ingreso, enviar al usuario.
     * @return none
     */
    function loginRedirectAreaByActivation(){
        if(!$this->isLogged()) return false;

        if(sizeof($this->data['privileges'])>1){
            // Tiene varios, llevarlo a pagina de seleccion de areas
                App::module('Core')->getModel('flashmsg')->success( App::xlat("PUBLIC_SIGNUP_activated_1")."<br />".App::xlat("PUBLIC_SIGNUP_activated_2") );
                header('Location:' . App::base('/area-selector') );
                exit;
        }else{

            // Tiene solo 1, llevarlo al area que le corresponde
            switch ( $this->data['privileges'][0]['privilege'] ) {
                case 1:
                        // Usuario
                        header('Location:' . App::base('/user/area-user/activated') );
                        exit;
                        break;
                case 2:
                        // Business
                        header('Location:' . App::base('/business/area-business/activated') );
                        exit;
                        break;
                case 3:
                        // Admin
                        header('Location:' . App::base('/admin/area-admin/activated') );
                        exit;
                        break;
                case 4:
                        // Root
                        header('Location:' . App::base('/root/area-root/activated') );
                        exit;
                        break;
                default:
                        echo '<pre>'; print_r('Esta es una area no registrada en el SWITCH'); echo '</pre>';
                        echo '<pre>'; print_r('Mirate el archivo Module-User-Areas-Frontend-Blocks-Login-IndexBlockController.php'); echo '</pre>';
                        exit;
                        break;
            }
        }

    }

    /**
     * Realiza un login de un usuario sin que sea necesaria la contraseña
     *
     * @event: module_acl_login si el usuario se ha identificado correctamente
     * @param string $user
     * @return bool
     */
    function autologin($user) {
        $this->flushErrors();

        if (!$this->isLogged()) {

            if ( $this->_resource->reset()->setUserName($user)->getRow() ) {

                $this->user=$user;
                $this->life=time()+self::LIFE;

                if ($this->load_user_data(true)) {

                    // Actualizamos los datos del usuario para almacenar el acceso
                        $this->_resource->reset()->setUsername($this->data['username'])->updateAccess();
                    // Lanzamos evento
                        App::events()->dispatch('module_acl_login',array('user'=>$this->data['username']),false,Core_Model_Log::NOTICE);
                    return true;
                }
                return false;

            } else {

                $this->_addError('ERROR_LOGIN',array('user'=>$user,'pwd'=>false));
                return false;
            }

        }

        return true;
    }

    /**
     * Realiza un logout de un usuario.
     *
     * @event: module_acl_logout si el usuario se ha desconectado correctamente
     * @return unknown
     */
    function logout($notify=true) {
        $user=$this->user;
        if ($this->isLogged()) {
            $this->_unloadData();
            if ($notify) App::events()->dispatch('module_acl_logout',array('user'=>$user),false,Core_Model_Log::NOTICE);
        }
        return true;
    }

    /**
     * Devuelve los errores que se hayan producido y vacia la cola de errores
     *
     * @return mixed [array | bool]
     */
    function flushErrors () {
        if (count($this->_errors)) {
            $errors=$this->_errors;
            $this->_errors=array();
            return $errors;
        }
        return false;
    }

    /**
     * Comprueba si se ha iniciado sesión
     *
     * @return bool
     */
    function isLogged() {
        if ($this->user) {
            return true;
        }
        return false;
    }

    function isLoggedIn() {
        return $this->isLogged();
    }

    /**
     * Comprueba si el usuario identificado tiene privilegios para una zona determinada
     *
     * @param string $zone (id de la zona)
     * @return bool
     */
    function hasPrivileges($zone) {
        if (!$this->isLogged()) return false;
        /*if (isset($this->data[("priv_".$zone)])) {
            if ($this->data[("priv_".$zone)]==1) return true;
        }*/
        if (isset($this->privileges[$zone])) return true;
        return false;
    }

    /**
     * Obtiene un array con los privilegios del usuario
     *
     * @return array
     */
    function getPrivileges() {
        if (sizeof($this->privileges)) {
            return $this->privileges;
        } else {
            return false;
        }
    }

    /**
     * Obtiene un array con todos los privilegios del usuario sin necesidad de estar logeado
     * ArthasMX
     * @return array
     */
    function getAllUserPrivileges($user=false) {
        if (!$user) return false;
        return $this->_module->getResource('Privileges')->reset()->setLanguage(App::locale()->getLang())->setUsername($user)->setOrder( array('privilege'=> 'ASC') )->get(true);
    }

    /**
     * Requiere que el usuario disponga de los privilegios para la zona especificada
     *
     * En caso de que no disponga de privilegios, se lanzan 2 eventos para que otros módulos puedan insertar sus mensajes de error
     * o realizar la redirección oportuna.
     *
     * Se retorna true si el usuario dispone de privilegios
     *
     * @param string $zone
     * @return bool
     */
    function requirePrivileges($zones) {
        if (!$this->isLogged()) {
            // No tiene privilegios, despachamos 2 eventos por si algun módulo ha incluido observadores que muestren el error
            App::events()->dispatch('module_acl_noprivileges_login',array('zone'=>(array)$zones,'user'=>$this->user),false,Core_Model_Log::INFO ); // No son errores graves, simplemente que no ha hecho login
            //App::events()->dispatch('module_acl_noprivileges_login_'.(array)$zones[0],array('user'=>$this->user),false,Core_Model_Log::INFO );
        }

        // Permitimos poder recibir un string con los privilegios a requerir o un array completo de privilegios
            $found=true;
            foreach ((array)$zones as $zone) {
                if (!$this->hasPrivileges($zone)) {
                    // No tiene privilegios, despachamos 2 eventos por si algun módulo ha incluido observadores que muestren el error
                        App::events()->dispatch('module_acl_noprivileges',array('zone'=>$zone,'user'=>$this->user),false,Core_Model_Log::WARN ); // Estos si son graves, ha hecho login pero no tiene permisos
                        //App::events()->dispatch('module_acl_noprivileges_'.strtolower($zone),array('user'=>$this->user),false,Core_Model_Log::WARN);
                    $found=false;
                }
            }

        return $found;
    }

    /**
     * Requiere que el usuario no disponga de los privilegios para la zona especificada
     *
     * Es el inverso de requirePrivileges
     *
     * En caso de que disponga de alguno de los privilegios devuelve false
     *
     * @param string $zone
     * @return bool
     */
    function requireNoPrivileges($zones) {
        // Permitimos poder recibir un string con los privilegios a requerir o un array completo de privilegios
            $nofound=true;
            foreach ((array)$zones as $zone) {
                if ($this->hasPrivileges($zone)) {
                    $nofound=false;
                }
            }
        return $nofound;
    }

    function create($user,$passwd,$email,$autologin=true,$privileges=1) {
        $this->_resource->create($user,$passwd,$email);

        // Lanzamos evento para que Otros módulos puedan sincronizarse con éste
            //App::events()->dispatch('module_acl_create',array('user'=>$user,'passwd'=>$passwd,'email'=>$email,'privileges'=>$privileges),false,Core_Model_Log::NOTICE );

        // Creamos los privilegios para el usuario
            $this->_module->getResource('Privileges')->setUsername($user)->saveUserPrivileges((array)$privileges);

        // Realizamos login
            if ($autologin) $this->login($user,$passwd);
    }

    function changePasswd($passwd,$user=false) {
        if ($user===false) $user=$this->user;

        $this->_resource->changePasswd($user,$passwd);

        // Lanzamos evento para que Otros módulos puedan sincronizarse con éste
            App::events()->dispatch('module_acl_change_passwd',array('user'=>$user,'passwd'=>$passwd),false,Core_Model_Log::NOTICE );

        return true;
    }

    function changeEmail($email,$user=false) {
        if ($user===false) $user=$this->user;

        $this->_resource->changeEmail($user,$email);

        // Lanzamos evento para que Otros módulos puedan sincronizarse con éste
            App::events()->dispatch('module_acl_change_email',array('user'=>$user,'email'=>$email,false,Core_Model_Log::NOTICE ));

        return true;
    }

    function grant($privileges,$user=false) {
        if ($user===false) $user=$this->user;
        $this->_resource->setUsername($user)->grant($privileges);
        return true;
    }

    function ungrant($privileges,$user=false) {
        if ($user===false) $user=$this->user;
        $this->_resource->setUsername($user)->ungrant($privileges);
        return true;
    }

    function activate($user=false) {
        if ($user===false) $user=$this->user;
        $this->_resource->setUsername($user)->activate();
        return true;
    }
    function deactivate($user=false) {
        if ($user===false) $user=$this->user;
        $this->_resource->setUsername($user)->deactivate();
        return true;
    }
    function lock($user=false) {
        if ($user===false) $user=$this->user;
        $this->_resource->setUsername($user)->lock();
        return true;
    }
    function unlock($user=false) {
        if ($user===false) $user=$this->user;
        $this->_resource->setUsername($user)->unlock();
        return true;
    }

    function detail($user) {
        return $this->_resource->reset()->setUserName($user)->getRow();
    }

    /**
     * Regresa el USERNAME real sin @ para cuando deseamos obtener solamente el NOMBRE, sin el @dominio.com
     * Esto se usa para los nombres de los archivos subidos por el usuario en turno, asi sabemos a quien le pertenecen
     * @return unknown
     */
    function getUsername(){
        if(App::getConfig('email_as_login_id')>0){
            $tmp=explode("@",$this->user);
            if(sizeof($tmp)>0) return $tmp[0];
            else return false;
        }
        return $this->user;
    }
}