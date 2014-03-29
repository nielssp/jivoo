<?php
class Post extends ActiveModel {
  protected $mixins = array('Timestamps');

  protected $hasAndBelongsToMany = array(
    'tags' => array(
      'model' => 'Tag',
      'join' => 'PostTag',
      'thisKey' => 'postId',
      'otherKey' => 'tagId'
    ),
  );

  protected $hasMany = array(
    'comments' => 'Comment',
  );

  protected $belongsTo = array(
    'User',
  );

  protected $validate = array(
    'title' => array(
      'presence' => true,
    ),
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
    'content' => array(
      'presence' => true,
    ),
  );

  public function getRoute(ActiveRecord $record) {
    return array(
      'controller' => 'Posts',
      'action' => 'view',
      'parameters' => array($record->id)
    );
  }
  
  public function install() {
    $post = $this->create();
    $post->title = tr('Welcome to Jivoo');
    $post->name = 'welcome-to-jivoo';
    $post->content = '<p>' . tr('Welcome to Jivoo') . '<p>
<p>' . tr('This post indicates that Jivoo has been installed correctly, and is ready to be used.') . '</p>';
    if ($post->save()) {
      $comment = $post->comments->create();
      $comment->ip = '';
      $comment->author = 'Jivoo';
      $comment->email = 'jivoo@apakoh.dk';
      $comment->content = 'Welcome to Jivoo.';
      $comment->approved = true;
      $comment->save();
    }
    else {
      // install failed
    }
  }
}
