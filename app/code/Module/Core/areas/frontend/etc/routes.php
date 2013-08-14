<?php

// $router está definido como instancia al router del Front Controller
// Ayuda y ejemplos de rutas en el punto 7.5.6 de
// http://framework.zend.com/manual/en/zend.controller.router.html#zend.controller.router.default-routes

	// Carga de los estados
    $route = new Zend_Controller_Router_Route(
        'site/states',
        array(
            'module'        => 'Core',
            'controller'    => 'World',
            'action'        => 'states',
        )
    );
    $router->addRoute('estados', $route);

// Ruta para acciones del core
	$route = new Zend_Controller_Router_Route(
	    'core/:controller/:action/*',
	    array(
	        'module'	 => 'Core',
	    )
	);
	$router->addRoute('core', $route);


	// Paginacion de archivos
	$route = new Zend_Controller_Router_Route(
	    'file-paginate/:page',
	    array(
	        'module'        => 'Core',
	        'controller'    => 'Index',
	        'action'        => 'file-paginate',
	    )
	);
	$router->addRoute('paginator', $route);