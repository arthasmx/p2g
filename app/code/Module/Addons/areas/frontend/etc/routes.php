<?php

// Rating
  $route = new Zend_Controller_Router_Route(
           'rate/:id/:rate',
           array( 'module'     => 'Addons',
                  'controller' => 'Rate',
                  'action'     => 'rate'));
  $router->addRoute('rate', $route);

  // Poll
  $route = new Zend_Controller_Router_Route(
           'poll/vote/:id/:vote',
           array( 'module'     => 'Addons',
                  'controller' => 'ajax',
                  'action'     => 'poll-vote'));
  $router->addRoute('vote', $route);

  // Comments
  $route = new Zend_Controller_Router_Route(
      'comments/:action',
      array( 'module'     => 'Addons',
             'controller' => 'Comments'));
  $router->addRoute('comment', $route);

  // Social Route
  $route = new Zend_Controller_Router_Route(
      'social/preview/:id',
      array('module'     => 'Addons',
            'controller' => 'Social',
            'action'     => 'preview'));
  $router->addRoute('social-preview', $route);

  // Guestbook
  $guestbook_params = array( 'module' => 'Addons', 'controller' => 'Guestbook');
  $guestbook_routes = array( 'en' => "guestbook"
                            ,'es' => 'libro-de-visitas');

  foreach($guestbook_routes AS $lang=>$value){
    $base = new Zend_Controller_Router_Route($value . '/*', array_push_assoc($guestbook_params, "action", "show"  ));
    $add  = new Zend_Controller_Router_Route($value . '/add', array_push_assoc($guestbook_params, "action", "add"  ));

    $router->addRoute('guestbook_show_'.$lang, $base);
    $router->addRoute('guestbook_add_'.$lang, $add);
  }