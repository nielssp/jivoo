<?php

function printArrayRows($array, $prefix = '') {
  foreach ($array as $key => $value) {
    echo '<tr>';
    echo '<td>';
    echo $prefix . $key . '</td>';
    echo '<td>' . $value . '</td>';
    echo '</tr>';
    if (is_array($value)) {
      printArrayRows($value, $prefix . $key . '.');
    }
  }
}

require('../lib/Jivoo/bootstrap.php');
Lib::import('Core');
$config = new AppConfig('../config/config.php');

echo '<table>';
echo '<tr>';
echo '<th>Configuration key</th>';
echo '<th>Value</th>';
echo '</tr>';

printArrayRows($config->getArray());

echo '</table>';
