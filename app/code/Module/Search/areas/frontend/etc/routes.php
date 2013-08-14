<?php

  // Busquedas para INTRO
  $route = new Zend_Controller_Router_Route(
      ':type/buscar',
      array('module'    => 'Search',
          'controller'  => 'index',
          'action'      => 'search') );
  $router->addRoute('search', $route);


// Busquedas para biblia DEFAULT. Estas busquedas realizarian la paginacion sin ajax
  $route = new Zend_Controller_Router_Route(
      'biblia/buscar/:keyword/*',
      array('module'     => 'Search',
            'controller' => 'index',
            'action'     => 'bible-search') );
  $router->addRoute('bible_search', $route);

// Busquedas utilizando la paginacion en AJAX
  $route = new Zend_Controller_Router_Route(
      'bible/ajax-search/*',
      array( 'module'     => 'Search',
             'controller' => 'index',
             'action'     => 'ajax-bible-search'));
  $router->addRoute('ajax-bible-search', $route);