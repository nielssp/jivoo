<?php
namespace Chat\Models;

use Jivoo\ActiveModels\ActiveModel;
use Jivoo\ActiveModels\ActiveRecord;

class Message extends ActiveModel {
  
  protected $mixins = array('Timestamps');
  
  protected $validate = array(
    'message' => array(
      'presence' => true,
    ),
  );
}