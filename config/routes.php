<?php
return array(
  Route::auto('Posts'),
  Route::root('Posts/index'),
  Route::error('App/404'),
  Route::match('tags', 'Posts::tagIndex'),
  Route::match('tags/?', 'Posts::viewTag::%1'),
  Route::match('posts/?/comments', 'Comments::index::%1'),
  Route::match('posts/?/comments/?', 'Comments::view::%1::%2'),
  'posts' => 'Posts/index',
  'posts/*' => 'Posts/view',
  'tags' => 'Posts/tagIndex',
  'tags/*' => 'Posts/viewTag',
  'posts/*/comments' => 'Comments/index',
  'posts/*/comments/*' => 'Comments/view',
);
