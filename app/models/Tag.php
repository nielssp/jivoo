<?php

class Tag extends ActiveRecord implements ILinkable {

  protected $hasAndBelongsToMany = array(
  	'Post' => array('connection' => 'other',
  	                'join' => 'posts_tags',
                    'otherKey' => 'post_id',
  	                'thisKey' => 'tag_id'),
  );

  public function getRoute() {
    return array(
      'controller' => 'Posts',
      'action' => 'viewTag',
      'parameters' => array($this->name)
    );
  }

  public static function createName($title) {
    return strtolower(
      preg_replace(
        '/[ \-]/', '-', preg_replace(
          '/[^(a-zA-Z0-9 \-)]/', '', $title
        )
      )
    );
  }
}

