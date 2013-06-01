<?php
return array(
  Route::auto('Posts'),
  Route::root('Posts/index'),
  Route::error('App/404'),
  Route::match('tags', 'Posts/tagIndex'),
  Route::match('tags/?', 'Posts/viewTag'),
  Route::match('posts/?/comments', 'Comments/index'),
  Route::match('posts/?/comments/?', 'Comments/view'),
  'posts' => 'Posts/index',
  'posts/*' => 'Posts/view',
  'tags' => 'Posts/tagIndex',
  'tags/*' => 'Posts/viewTag',
  'posts/*/comments' => 'Comments/index',
  'posts/*/comments/*' => 'Comments/view',
);
