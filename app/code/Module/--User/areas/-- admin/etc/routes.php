<?php

  $route = new Zend_Controller_Router_Route(
    'admin/:action/*',
    array(
        'module'	   => 'User',
        'controller' => 'index',
        'action'     => 'index'
  ));
$router->addRoute('admin-login', $route);