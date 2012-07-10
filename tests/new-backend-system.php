<?php
define('ALLOW_REDIRECT', FALSE);
define('DEBUG', TRUE);
include '../app/essentials.php';

echo '<pre>';

$core = new Core();
$backend = $core->loadModule('backend');

$controller = new PostsController(
  $core->loadModule('Templates'),
  $core->loadModule('Routes')
);

$menu = $backend->createMenu();

foreach ($menu as $category) {
  echo $category->label . PHP_EOL;
  foreach ($category as $item) {
    echo '..' . $item->label . PHP_EOL;
  }
}

echo '</pre>';
