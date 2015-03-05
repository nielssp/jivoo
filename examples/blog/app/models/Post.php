<?php
namespace Blog\Models;

use Jivoo\ActiveModels\ActiveModel;
use Jivoo\Routing\ILinkable;
use Jivoo\ActiveModels\ActiveRecord;

class Post extends ActiveModel {
  public function getRoute(ActiveRecord $record) {
    return array(
      'controller' => 'Posts',
      'action' => 'view',
      $record->id
    );
  }
}