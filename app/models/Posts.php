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
}
