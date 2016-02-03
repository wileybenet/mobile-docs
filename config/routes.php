<?php // REST API Routing

  Router::ns_route('/', [
    'GET@support' => 'View#support',
    'POST@login' => 'Session#login',
    'GET@logout' => 'Session#logout'
  ]);

  Router::resource('/api/', 'user');
  Router::resource('/api/', 'doc');
  Router::resource('/api/', 'post');
  Router::resource('/api/', 'solution');
  Router::resource('/api/', 'asset');

  Router::ns_route('/api/', [
    // 'GET@doc/:name' => 'Doc#template',

    // 'GET@doc/:doc_id/version' => 'Doc#version',
    // 'GET@post/:post_id/version' => 'Post#version'
  ]);
  
?>