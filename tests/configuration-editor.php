<?php

function printArrayRows($array, $level = 0) {
  foreach ($array as $key => $value) {
    echo '<tr>';
    echo '<td>';
    for ($i = 0; $i < $level; $i++) {
      echo '- ';
    }
    echo $key . '</td>';
    echo '<td>' . $value . '</td>';
    echo '</tr>';
    if (is_array($value)) {
      printArrayRows($value, $level + 1);
    }
  }
}

require('../app/essentials.php');
$errors = new Errors();
$config = new Configuration($errors);

echo '<table>';
echo '<tr>';
echo '<th>Configuration key</th>';
echo '<th>Value</th>';
echo '</tr>';

printArrayRows($config->get());

echo '</table>';
