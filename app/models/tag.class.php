<?php

if (!is_a($this, 'Posts')) {
  exit('This model should be loaded from the Posts module.');
}

class Tag extends ActiveRecord implements ILinkable {

  protected $hasAndBelongsToMany = array(
  	'Post' => array('connection' => 'other',
  	                'join' => 'posts_tags',
                    'otherKey' => 'post_id',
  	                'thisKey' => 'tag_id'),
  );

  private static $posts;

  public static function setModule(Posts $postsModule) {
    self::$posts = $postsModule;
  }

  public function getPath() {
    return self::$posts->getPath($this);
  }

  public function getLink() {
    return self::$posts->getLink($this);
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

Tag::setModule($this);
