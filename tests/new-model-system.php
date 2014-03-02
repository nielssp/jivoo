<?php
include '../lib/Core/bootstrap.php';

Lib::import('Core');
Lib::import('Core/Routing');
Lib::import('Core/Models');
Lib::import('Core/Models/Condition');
Lib::import('Core/Models/Selection');
Lib::import('Core/Models/Validation');
Lib::import('Core/Database');
Lib::import('Core/Database/PdoMysql');

$db = new PdoMysqlDatabase(array(
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
));


header('Content-Type:text/plain');


