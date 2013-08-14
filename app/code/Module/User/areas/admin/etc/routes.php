<?php
  $route = new Zend_Controller_Router_Route(
    ':action',
    array(
        'module'	   => 'User',
        'controller' => 'index'
  ));
$router->addRoute('admin', $route);

$route = new Zend_Controller_Router_Route(
    'ministeries/:action',
    array(
        'module'	    => 'User',
        'controller' => 'ministeries',
        'action'     => 'list'
    ));
$router->addRoute('ministeries', $route);
