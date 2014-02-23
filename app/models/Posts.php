<?php

class Posts extends ActiveModel {

  protected $hasAndBelongsToMany = array(
    'tags' => array(
      'model' => 'Tags',
      'join' => 'PostsTags',
      'thisKey' => 'postId',
      'otherKey' => 'tagId'
    ),
  );

  protected $hasMany = array(
    'Comments',
  );

  protected $belongsTo = array(
    'user' => 'Users',
  );

  protected $hasOne = array(
    'category' => array('model' => 'Tags')
  );

  protected $validate = array(
    'title' => array(
      'presence' => true,
    ),
    'name' => array(
      'presence' => true,
      'unique' => true,
      'minLength' => 1,
      'maxLength' => 50,
      'rule0' => array(
        'match' => '/^[a-z0-9-]+$/',
        'message' => 'Only lowercase letters, numbers and dashes allowed.'
      ),
    ),
    'content' => array(
      'presence' => true,
    ),
  );

  protected function createSchema(Schema $s) {
    $s->addInteger('id', Type::UNSIGNED | Type::AUTO_INCREMENT | Type::NOT_NULL);
    $s->addString('name', 255, Schema::NOT_NULL);
    $s->addString('title', 255, Schema::NOT_NULL);
    $s->addText('content', Schema::NOT_NULL);
    $s->addInteger('date', Schema::NOT_NULL | Schema::UNSIGNED);
    $s->addInteger('comments', Schema::NOT_NULL | Schema::UNSIGNED);
    $s->addString('state', 50, Schema::NOT_NULL);
    $s->addString('commenting', 10, Schema::NOT_NULL);
    $s->addInteger('userId', Schema::NOT_NULL | Schema::UNSIGNED);
    $s->addString('status', 50, Schema::NOT_NULL);
    $s->setPrimaryKey('id');
    $s->addUnique('name', 'name');
    $s->addIndex('date', 'date');
  }
}
