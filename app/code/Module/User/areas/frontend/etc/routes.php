<?php

// $router está definido como instancia al router del Front Controller
// Ayuda y ejemplos de rutas en el punto 7.5.6 de
// http://framework.zend.com/manual/en/zend.controller.router.html#zend.controller.router.default-routes

// LOGIN Y LOGOUT
	$route = new Zend_Controller_Router_Route(
	    'area-selector/:action/*',
	    array(
	        'module'	 => 'User',
	        'controller' => 'areaselector',
	        'action'     => 'index'
	    )
	);
	$router->addRoute('area.selector', $route);

