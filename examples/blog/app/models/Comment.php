<?php
namespace Blog\Models;

use Jivoo\ActiveModels\ActiveModel;
use Jivoo\ActiveModels\ActiveRecord;

class Comment extends ActiveModel {
  
  protected $mixins = array('Timestamps');
  
  protected $belongsTo = array('Post');
  
  protected $validate = array(
    'author' => array(
      'presence' => true,
    ),
    'content' => array(
      'presence' => true,
    ),
  );

  public function getRoute(ActiveRecord $record) {
    return array(
      'controller' => 'Comments',
      'action' => 'view',
      $record->postId, $record->id
    );
  }
}