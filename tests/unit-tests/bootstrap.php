<?php
require('../../app/essentials.php');

$paths = array('app/modules', 'app/models', 'app/classes', 'app/controllers',
  'app/helpers'
);

foreach ($paths as $path) {
  $dir = opendir(p($path));
  while ($file = readdir()) {
    $filex = explode('.', $file);
    class_exists($filex[0]);
  }
}

