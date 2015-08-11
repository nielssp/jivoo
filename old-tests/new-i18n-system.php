<?php
require '../src/bootstrap.php';

ini_set('display_errors', true);

use Jivoo\Core\I18n\Locale;

$expr = 'n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2';

$php = Locale::convertExpr($expr);
var_dump($php);

for ($n = 0; $n < 10; $n++) {
  var_dump(eval('return ' . $php . ';'));
}