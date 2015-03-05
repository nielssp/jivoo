<?php
require_once '../../lib/Jivoo/Core/bootstrap.php';

$app = new Jivoo\Core\App(
  'app',
  'user',
  basename(__FILE__)
);

$app->paths->share = '../../share';

// $app->incomplete ...
// default environments

$app->run('development');
