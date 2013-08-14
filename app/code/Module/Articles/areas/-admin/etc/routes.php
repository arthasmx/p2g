<?php
// $router está definido como instancia al router del Front Controller
// Ayuda y ejemplos de rutas en el punto 7.5.6 de
// http://framework.zend.com/manual/en/zend.controller.router.html#zend.controller.router.default-routes

	/**
	 *	Mensajes privados escritos por el usuario para Admin o Root
	 */
    $route = new Zend_Controller_Router_Route(
		'articles/ajax/admin/:controller/:action/*',
        array(
            'module'        => 'Articles',
        	'controller_prefix' => 'ajax_admin',
      		'controller'  	=> 'article',
        	'action'        => 'listado',
        )
    );
    $router->addRoute('articles.info', $route);

    $route = new Zend_Controller_Router_Route(
        'artmenu/:action',
        array(
            'module'        => 'Articles',
        	'controller'	=> 'Index'
        )
    );
    $router->addRoute('admin.article.listing', $route);