<?php
use Jivoo\Routing\Route;

$this->root = 'snippet:FrontPage';

$this->error = 'action:App::notFound';

$this->match('posts/:1', 'action:Posts::view(:1)');
$this->match('about', 'snippet:Misc\About');

$this->match('about', array(
  'snippet' => 'Misc\About'
));

return array(
  Route::root('App::index'),
  Route::error('App::notFound'),
  Route::match('**', 'Pages::view'),
);
