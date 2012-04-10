<?php

if (!is_a($this, 'Posts')) {
  exit('This model should be loaded from the Posts module.');
}

class Comment extends ActiveRecord implements ILinkable {

  protected $hasMany = array(
    'Reply' => array('class' => 'Comment',
                     'plural' => 'Replies',
                     'connection' => 'other',
                     'thisKey' => 'parent_id')
  );

  protected $belongsTo = array(
    'Post' => array('connection' => 'this',
                    'otherKey' => 'post_id'),
    'Parent' => array('class' => 'Comment',
                      'connection' => 'this',
                      'otherKey' => 'parent_id'),
    'User' => array('connection' => 'this',
                    'otherKey' => 'user_id')
  );

  protected $defaults = array(
    'user_id' => 0,
    'parent_id' => 0,
    'email' => '',
    'website' => ''
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

Comment::setModule($this);