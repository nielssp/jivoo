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



//class Post extends ActiveRecord {
//  // Stupid and weird
//  protected $Integer_id;
//  protected $String_title;
//  protected $Text_content;
//  protected $Date_createdAt;
//  protected $Date_updatedAt;
//  protected $Collection_Comment_comments;
//  protected $Collection_Tag_tags;
//  protected $User_user;
//}

class Post extends ActiveRecord {
  protected $hasMany = array(
    'comments' => 'Comment'
  );

  protected $belongsTo = array(
    'User'
  );

  protected $hasAndBelongsToMany = array(
    'tags' => 'Tag'
  );

  protected $mixins = array('timestamps');
}

class Category extends ActiveRecord {
  const plural = 'Categories';
}

class PostModel extends ActiveModel {
}

class Posts extends ActiveModel {
}

$this->Posts->find(1);
$this->Post->find(1);

$this->Post->set('date = %d', time());

$model = 'Category';

echo var_dump(constant($model . '::plural'));
