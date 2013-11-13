<?php
// app/config/routes.php
return array(
  Route::root('Posts'),
  Route::error('App::notFound'),
  Route::resource('Posts'),
  Route::auto('App::test'),
);
