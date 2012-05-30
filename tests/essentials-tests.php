<?php

require('../app/essentials.php');

echo '<pre>';

print_r(matchDependencyVersion('mysql<>2.5'));

print_r(readFileMeta(p(CLASSES . 'db-drivers/mysql.class.php')));
echo '</pre>';