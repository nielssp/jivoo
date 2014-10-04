<?php

class Comment extends ActiveModel {
  protected $mixins = array('Timestamps');

  protected $hasMany = array(
    'replies' => 'Comment'
  );

  protected $belongsTo = array(
    'Post',
    'parent' => array(
      'model' => 'Comment',
      'otherKey' => 'parentId'
    ),
    'User'
  );

  protected $validate = array(
    'content' => array(
      'presence' => true,
      'maxLength' => 1024,
    ),
    'author' => array(
      'presence' => true
    ),
    'email' => array(
      'presence' => true,
      'email' => true
    ),
    'website' => array(
      'url' => true,
    ),
  );

  protected $labels = array(
    'author' => 'Name',
    'email' => 'Email',
    'website' => 'Website',
    'content' => 'Content',
    'status' => 'Status',
    'createdAt' => 'Created at',
    'updatedAt' => 'Updated at',
  );

  protected $actions = array(
    'view' => 'Comments::view::%id%',
    'edit' => 'Admin::Comments::edit::%id%',
  );

  public function getRoute(ActiveRecord $record) {
    return array(
      'controller' => 'Comments',
      'action' => 'view',
      'parameters' => array($record->postId, $record->id)
    );
  }
}
