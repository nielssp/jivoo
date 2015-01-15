<?php
return array(
  Route::root('App::index'),
  Route::error('App::notFound'),
  Route::match('**', 'Pages::view'),
);
