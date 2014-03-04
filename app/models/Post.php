<?php
class Post extends ActiveModel {

  protected $hasAndBelongsToMany = array(
    'tags' => array(
      'model' => 'Tag',
      'join' => 'PostTag',
      'thisKey' => 'postId',
      'otherKey' => 'tagId'
    ),
  );

  protected $hasMany = array(
    'Comment',
  );

  protected $belongsTo = array(
    'user' => 'User',
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
}
