<?php

// Carga de imagenes
/*
$route = new Zend_Controller_Router_Route( 'uploader/:action', array('module'     => 'Core',
                                                                     'controller' => 'Uploader'));
$router->addRoute('uploader', $route);
*/

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

// Paginacion de archivos
/*
$route = new Zend_Controller_Router_Route(
    '/file-paginate/:page',
    array(
        'module'        => 'Core',
        'controller'    => 'Index',
        'action'        => 'file-paginate',
    )
);
$router->addRoute('paginator', $route);
*/