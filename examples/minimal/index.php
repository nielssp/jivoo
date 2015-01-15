<?php
require_once '../../lib/Jivoo/Core/bootstrap.php';

$app = new App(
  'app',
  'user',
  basename(__FILE__)
);

$app->paths->share = '../../share';

$app->run('development');
