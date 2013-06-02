<?php
return array(
  Route::auto('Posts', array('except' => array('tagIndex', 'viewTag'))),
  Route::root('Posts::index'),
  Route::error('App::notFound'),
  Route::match('tags', 'Posts::tagIndex'),
  Route::match('tags/:1', 'Posts::viewTag'),
  Route::match('posts/:1/comments', 'Comments::index'),
  Route::match('posts/:1/comments/:2', 'Comments::view'),
  Route::match(':*', 'Pages/view', 4),
  Route::match(':controller/:action/:*', array()),
);
