<?php

// $router está definido como instancia al router del Front Controller
// Ayuda y ejemplos de rutas en el punto 7.5.6 de
// http://framework.zend.com/manual/en/zend.controller.router.html#zend.controller.router.default-routes

	$route = new Zend_Controller_Router_Route(
		'blog/:year/:month',
		array(
		    'module'		=> 'Blog',
		    'controller'	=> 'index',
		    'action'		=> 'index',
		    'year'			=> '',
		    'month'			=> '',
		)
	);
	$router->addRoute('blog.todos', $route);

	$route = new Zend_Controller_Router_Route(
		'read/:id',
		array(
		    'module'		=> 'Blog',
		    'controller'	=> 'index',
		    'action'		=> 'read',
		)
	);
	$router->addRoute('blog.read', $route);