<?php

$route = new Zend_Controller_Router_Route(
  'login',
  array(
    'module'	   => 'Acl',
    'controller' => 'Index',
    'action'     => 'Login'
  ));
$router->addRoute('acl.login', $route);

$route = new Zend_Controller_Router_Route(
    'registrar-empresa',
    array(
        'module'	   => 'Acl',
        'controller' => 'Index',
        'action'     => 'business-register'
    ));
$router->addRoute('acl.business.register', $route);

$route = new Zend_Controller_Router_Route(
  'password-recover/:action/*',
  array(
    'module'	 => 'Acl',
    'controller' => 'Forgotpwd',
    'action'     => 'recover'
  ));
$router->addRoute('acl.recoverpwd', $route);