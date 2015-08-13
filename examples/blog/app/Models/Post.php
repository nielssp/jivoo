<?php
namespace Blog\Models;

use Jivoo\ActiveModels\ActiveModel;
use Jivoo\Routing\Linkable;
use Jivoo\ActiveModels\ActiveRecord;

class Post extends ActiveModel {
  
  protected $mixins = array('Timestamps');
  
  protected $hasMany = array(
    'comments' => 'Comment'
  );
  
  protected $validate = array(
    'title' => array(
      'presence' => true,
    ),
    'content' => array(
      'presence' => true,
    ),
  );
  
  public function getRoute(ActiveRecord $record) {
    return array(
      'controller' => 'Posts',
      'action' => 'view',
      $record->id
    );
  }
}