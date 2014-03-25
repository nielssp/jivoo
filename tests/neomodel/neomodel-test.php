<?php

ini_set('display_errors', true);
require '../../lib/Jivoo/bootstrap.php';
Lib::import('Core');
Lib::import('Jivoo/Database');
Lib::import('Jivoo/Database/PdoMysql');
Lib::import('Jivoo/Models');
Lib::import('Jivoo/Models/Validation');
Lib::import('Jivoo/Models/Condition');
Lib::import('Jivoo/Models/Selection');
Lib::import('Jivoo/Helpers');

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
