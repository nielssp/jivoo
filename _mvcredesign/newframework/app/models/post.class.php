<?php

if (!is_a($this, 'Posts')) {
  exit('This model should be loaded from the Posts module.');
}

class Post extends ActiveRecord implements ILinkable {

  protected $hasAndBelongsToMany = array(
  	'Tag' => array('join' => 'posts_tags',
  	               'otherKey' => 'tag_id',
  	               'thisKey' => 'post_id'),
  );

  protected $hasMany = array(
  	'Comment' => array('thisKey' => 'post_id',
                       'count' => 'comments'),
  );

  protected $belongsTo = array(
    'User' => array('connection' => 'this',
                    'otherKey' => 'user_id')
  );

  protected $hasOne = array(
    'Category' => array('class' => 'Tag')
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

  public function formatDate() {
    return fdate($this->date);
  }

  public function formatTime() {
    return ftime($this->date);
  }
}

Post::setModule($this);