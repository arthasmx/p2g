<?php
// Basic
$route = new Zend_Controller_Router_Route(
		'mobile/:action/*',
    array(	'module'     => 'Mobile',
		        'controller' => 'index',
		        'action'     => 'index'));
$router->addRoute('mobile-section', $route);
