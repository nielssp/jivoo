<?php
namespace Blog\Models;

use Jivoo\ActiveModels\ActiveModel;

class Comment extends ActiveModel {
  
  protected $mixins = array('Timestamps');
  
  protected $validate = array(
    'author' => array(
      'presence' => true,
    ),
    'content' => array(
      'presence' => true,
    ),
  );

}