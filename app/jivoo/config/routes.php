<?php
return array(
  Route::root('Posts::index'),
  Route::error('App::notFound'),
  Route::match('posts', 'Posts::index'),
  Route::match('tags', 'Posts::tagIndex'),
  Route::match('tags/*', 'Posts::viewTag'),
  Route::match('feed/posts.rss', 'Posts::feed'),
  Route::match('login', 'App::login'),
  Route::match('comments/*', 'Comments::view'),
  Route::auto('Admin'),
  Route::auto('Admin::Posts'),
  Route::auto('Admin::Pages'),
  Route::auto('Admin::Comments'),
  Route::auto('Admin::Users'),
);
