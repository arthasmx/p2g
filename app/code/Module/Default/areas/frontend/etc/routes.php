<?php
// Basic
$route = new Zend_Controller_Router_Route(
		':action/*',
    array('module'     => 'Default',
          'controller' => 'index',
          'action'     => 'index'));
$router->addRoute('public-section', $route);

// main categories

  $route = new Zend_Controller_Router_Route(
      'hoteles/:tag',
      array('module'     => 'Default',
            'controller' => 'index',
            'action'     => 'hoteles'));
  $router->addRoute('tag-hoteles-section', $route);

  $route = new Zend_Controller_Router_Route(
      'cafes-y-restaurantes/:tag',
      array('module'   => 'Default',
          'controller' => 'index',
          'action'     => 'cafes-y-restaurantes'));
  $router->addRoute('tag-cafes-y-restaurantes-section', $route);


// Tag
$route = new Zend_Controller_Router_Route(
    'tag/:action/*',
    array('module'     => 'Default',
          'controller' => 'index',
          'action'     => 'tag'));
$router->addRoute('tag-section', $route);

// Multimedia
  $route = new Zend_Controller_Router_Route(
      'descargar/audio/:folder/:year/:month/:file',
      array('module'     => 'Default',
            'controller' => 'index',
            'action'     => 'audio-download'));
  $router->addRoute('download', $route);

  $route = new Zend_Controller_Router_Route(
      'download/:date/:type/:id/:reference',
      array('module'     => 'Default',
            'controller' => 'index',
            'action'     => 'download-article-addon'));
  $router->addRoute('downloads', $route);


// Municipios
$route = new Zend_Controller_Router_Route(
    ':city/municipio/:town/:section',
    array('module'     => 'Default',
          'controller' => 'Cities',
          'action'     => 'town'));
$router->addRoute('cities-section', $route);


function array_push_assoc($array, $key, $value){
 $array[$key] = $value;
 return $array;
}