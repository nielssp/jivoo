<?php

class Tag extends ActiveModel {

  protected $hasAndBelongsToMany = array(
    'posts' => 'Post'
  );
  
  protected $validate = array(
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
    'tag' => array(
      'presence' => true,
      'maxLength' => 50
    ),
  );

  protected $actions = array(
    'view' => 'Posts::viewTag::%name%',
    'edit' => 'Admin::Tags::edit::%id%',
    'delete' => 'Admin::Tags::delete::%id%',
  );

  public function getRoute(ActiveRecord $record) {
    return array(
      'controller' => 'Posts',
      'action' => 'viewTag',
      'parameters' => array($record->name)
    );
  }

  public static function createName($title) {
    return strtolower(
      preg_replace('/[ \-]/', '-',
        preg_replace('/[^(a-zA-Z0-9 \-)]/', '', $title)));
  }
}

