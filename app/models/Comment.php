<?php

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
  
  protected $validate = array(
    'content' => array(
      'presence' => TRUE,
    ),
    'author' => array(
      'presence' => TRUE,
    ),
    'email' => array(
      'presence' => TRUE,
      'email' => TRUE
    ),
  );
  
  protected $fields = array(
    'author' => 'Name',
    'email' => 'Email',
    'website' => 'Website',
    'content' => 'Content'
  );

  protected $defaults = array(
    'date' => array('time'),
    'email' => '',
    'website' => ''
  );
  
  public static function setAnonymousCommenting($value = FALSE) {
    if ($value) {
      self::$validateOverride['Comment'] = array(
        'content' => array(
          'presence' => TRUE
        ),
        'email' => array(
          'email' => TRUE
        ),
      );
    }
    else {
      self::$validateOverride['Comment'] = array(
        'content' => array(
          'presence' => TRUE,
        ),
        'author' => array(
          'presence' => TRUE,
        ),
        'email' => array(
          'presence' => TRUE,
          'email' => TRUE
        ),
      );
    }
  } 

  public function getRoute() {
    return array(
      'controller' => 'Posts',
      'action' => 'viewComment',
      'parameters' => array($this->post_id, $this->id)
    );
  }

  public function formatDate() {
    return fdate($this->date);
  }

  public function formatTime() {
    return ftime($this->date);
  }
}
