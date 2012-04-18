<?php
require('../app/essentials.php');
$errors = new Errors();
$config = new Configuration($errors);

echo '<table>';
echo '<tr>';
echo '<th>Configuration key</th>';
echo '<th>Value</th>';
echo '</tr>';

foreach ($config->get() as $key => $value) {
  echo '<tr>';
  echo '<td>' . $key . '</td>';
  echo '<td>' . $value . '</td>';
  echo '</tr>';
}

echo '</table>';
