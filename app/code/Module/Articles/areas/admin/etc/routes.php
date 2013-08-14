<?php

// articles

$route = new Zend_Controller_Router_Route(
  'articles/:action/*',
  array('module'      => 'Articles',
        'controller'  => 'index',
        'action'      => 'list'));
$router->addRoute('article-section', $route);

$route = new Zend_Controller_Router_Route( 'articles/edit/:id', array('module'      => 'Articles', 'controller'  => 'index', 'action' => 'edit'));
$router->addRoute('article-edit', $route);

$route = new Zend_Controller_Router_Route(
    'articles-up/:action/*',
    array('module'    => 'Articles',
        'controller'  => 'uploads'));
$router->addRoute('article-uploads', $route);

$route = new Zend_Controller_Router_Route(
    'articles-files-paginate/:action/:page',
    array('module'    => 'Articles',
        'controller'  => 'files'));
$router->addRoute('paginate-local-files', $route);

$route = new Zend_Controller_Router_Route(
    'articles-fi/:action',
    array('module'    => 'Articles',
        'controller'  => 'files'));
$router->addRoute('article-files', $route);


// events

$route = new Zend_Controller_Router_Route(
  'events/:action/*',
  array('module'      => 'Articles',
        'controller'  => 'events',
        'action'      => 'list'));
$router->addRoute('events-section', $route);

$route = new Zend_Controller_Router_Route( 'events/edit/:id', array('module'      => 'Articles', 'controller'  => 'events', 'action' => 'edit'));
$router->addRoute('events-edit', $route);

$route = new Zend_Controller_Router_Route(
    'events-up/:action/*',
    array('module'     => 'Articles',
          'controller' => 'uploads'));
$router->addRoute('events-uploads', $route);

$route = new Zend_Controller_Router_Route(
    'events-fi/:action',
    array('module'     => 'Articles',
          'controller' => 'files'));
$router->addRoute('events-files', $route);


// business

$route = new Zend_Controller_Router_Route(
    'business/:action/*',
    array('module'      => 'Articles',
        'controller'  => 'business',
        'action'      => 'list'));
$router->addRoute('business-section', $route);

$route = new Zend_Controller_Router_Route( 'business/edit/:id', array('module'      => 'Articles', 'controller'  => 'business', 'action' => 'edit'));
$router->addRoute('business-edit', $route);

$route = new Zend_Controller_Router_Route(
    'business-up/:action/*',
    array('module'     => 'Articles',
        'controller' => 'uploads'));
$router->addRoute('business-uploads', $route);

$route = new Zend_Controller_Router_Route(
    'business-fi/:action',
    array('module'     => 'Articles',
        'controller' => 'files'));
$router->addRoute('business-files', $route);

