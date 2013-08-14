<?php

if ( App::locale()->getLang() ==='es' ){
  $all_routes = array(
    array('route'=>'eventos/*', 'route_name'=>'list-events', 'controller'=>'events', 'action'=>'list'),
    array('route'=>'eventos/proximos-eventos/*', 'route_name'=>'next-event', 'controller'=>'events', 'action'=>'next'),
    array('route'=>'eventos/eventos-anteriores/*', 'route_name'=>'previous-event', 'controller'=>'events', 'action'=>'previous'),
    array('route'=>'evento/:seo', 'route_name'=>'read-event', 'controller'=>'events', 'action'=>'read'),

    array('route'=>'articulos/*', 'route_name'=>'list-articles', 'controller'=>'index', 'action'=>'list'),
    array('route'=>'articulo/:seo', 'route_name'=>'read-article', 'controller'=>'index', 'action'=>'read'),
    array('route'=>'empresas/*', 'route_name'=>'list-business', 'controller'=>'business', 'action'=>'list'),
    array('route'=>'empresa/:seo', 'route_name'=>'read-business', 'controller'=>'business', 'action'=>'read'),

    array('route'=>'promociones/*', 'route_name'=>'promotion-list', 'controller'=>'business', 'action'=>'promotions'),
    array('route'=>'empresa/:seo/promocion/:id', 'route_name'=>'read-promo', 'controller'=>'business', 'action'=>'read-promo')

//    array('route'=>'anuncios/*', 'route_name'=>'list-anuncios', 'controller'=>'announcement', 'action'=>'list'),
//    array('route'=>'anuncio/:seo', 'route_name'=>'read-anuncio', 'controller'=>'announcement', 'action'=>'read')
  );

  foreach($all_routes AS $route){

    $parsed_route = new Zend_Controller_Router_Route(
        $route['route'],
        array('module'     => 'Articles',
              'controller' => $route['controller'],
              'action'     => $route['action']));
    $router->addRoute($route['route_name'], $parsed_route);
  }

}


/*

$route = new Zend_Controller_Router_Route(
    'que-comer/:type/*',
    array('module'     => 'Articles',
          'controller' => 'Food',
          'action'     => 'list' ));
$router->addRoute('food-listing', $route );

$route = new Zend_Controller_Router_Route(
    'comida/:seo/*',
    array('module'     => 'Articles',
          'controller' => 'Food',
          'action'     => 'read' ));
$router->addRoute('food-read', $route );




$route = new Zend_Controller_Router_Route(
    'diversion/:type/*',
    array('module'     => 'Articles',
          'controller' => 'Fun',
          'action'     => 'list' ));
$router->addRoute('fun-listing', $route );

$route = new Zend_Controller_Router_Route(
    'a-donde-ir/:seo/*',
    array('module'     => 'Articles',
          'controller' => 'Fun',
          'action'     => 'read' ));
$router->addRoute('fun-read', $route );




$route = new Zend_Controller_Router_Route(
    'hospedaje/:type/*',
    array('module'     => 'Articles',
          'controller' => 'Lodging',
          'action'     => 'list' ));
$router->addRoute('hospedaje-listing', $route );

$route = new Zend_Controller_Router_Route(
    'dormir-en/:seo/*',
    array('module'     => 'Articles',
          'controller' => 'Lodging',
          'action'     => 'read' ));
$router->addRoute('hospedaje-read', $route );


*/