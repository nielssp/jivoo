<?php

ini_set('display_errors', true);
require '../../lib/Core/bootstrap.php';
Lib::import('Core');
Lib::import('Core/Database');
Lib::import('Core/Database/PdoMysql');
Lib::import('Core/Models');
Lib::import('Core/Models/Validation');
Lib::import('Core/Models/Condition');
Lib::import('Core/Models/Selection');
Lib::import('Core/Helpers');

interface IActiveCollection extends ISelection {
  public function add(IActiveRecord $record);
  public function has(IActiveRecord $record);
  public function remove(IActiveRecord $record);
}

interface ITypeAdapter {
  public function encode(DataType $type, $value);

  public function decode(DataType $type, $value);
}



class InvalidDataTypeException extends Exception { }


header('Content-Type: text/plain');

$db = new PdoMysqlDatabase(array(
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
));

$posts = new Posts($db);

echo $posts->count() . PHP_EOL;

foreach ($posts->orderBy('title') as $post) {
  echo $post->id . ': ' . $post->title . PHP_EOL;
}


// $post->e('title');

// e($post, 'title');

//var_dump(Logger::getLog());
