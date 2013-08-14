<?php

// Carga de imagenes
$route = new Zend_Controller_Router_Route( 'gallery/:action', array('module'     => 'Addons',
                                                                     'controller' => 'Gallery'));
$router->addRoute('gallery_admin', $route);


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

  // Categories Route
  $route = new Zend_Controller_Router_Route(
      'categories/:action/*',
      array('module'      => 'Addons',
            'controller'  => 'Category',
            'action'      => 'list'));
  $router->addRoute('categories-section', $route);

  $route = new Zend_Controller_Router_Route(
      'categories/edit/:parent/*',
      array('module'    => 'Addons',
          'controller'  => 'Category',
          'action'      => 'edit'));
  $router->addRoute('categories-edit', $route);

  // Municipios Route
  $route = new Zend_Controller_Router_Route(
      'user/town/:seo',
      array('module'     => 'Addons',
            'controller' => 'Towns',
            'action'     => 'edit'));
  $router->addRoute('user_town-section', $route);

  $route = new Zend_Controller_Router_Route(
      'towns/:action/*',
      array('module'     => 'Addons',
            'controller' => 'Towns',
            'action'     => 'list'));
  $router->addRoute('town-section', $route);

  $route = new Zend_Controller_Router_Route(
      'towns-files-paginate/:action/:page',
      array('module'      => 'Addons',
            'controller'  => 'Towns'));
  $router->addRoute('towns-paginate-local-files', $route);

  $route = new Zend_Controller_Router_Route(
      'towns-up/:action/*',
      array('module'      => 'Addons',
            'controller'  => 'Towns'));
  $router->addRoute('towns-uploads', $route);

  // Banner Route
  $route = new Zend_Controller_Router_Route(
      'banner/:action/*',
      array('module'     => 'Addons',
            'controller' => 'Banner',
            'action'     => 'list'));
  $router->addRoute('banner-section', $route);


  // Social Route
  $route = new Zend_Controller_Router_Route(
      'social/:action/*',
      array('module'     => 'Addons',
            'controller' => 'Social',
            'action'     => 'list'));
  $router->addRoute('social-section', $route);

  $route = new Zend_Controller_Router_Route( 'social/edit/:id', array('module' => 'Addons', 'controller'  => 'social', 'action' => 'edit'));
  $router->addRoute('social-edit', $route);

  // Promotions Route
  $route = new Zend_Controller_Router_Route(
      'promotions/:action/*',
      array('module'     => 'Addons',
            'controller' => 'Promotions',
            'action'     => 'list'));
  $router->addRoute('promotions-section', $route);

  $route = new Zend_Controller_Router_Route( 'promotions/edit/:id', array('module' => 'Addons', 'controller'  => 'promotions', 'action' => 'edit'));
  $router->addRoute('promotions-edit', $route);