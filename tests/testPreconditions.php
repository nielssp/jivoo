<?php
define('DEBUG', TRUE);

require('../app/essentials.php');

new Errors();

$a = 2;
$b = 3;

precondition($a <= $b);
precondition(is_string($a));
