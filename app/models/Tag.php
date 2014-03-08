<?php

class Tag extends ActiveModel {

  protected $hasAndBelongsToMany = array(
    'posts' => 'Post'
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

