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
  
// PROPOSAL FOR VIRTUAL FIELDS
//   protected $virtual = array(
//     'author' => array(
//       'association' => 'user',
//       'field' => 'username'
//     ),
//     'approvedComments' => array(
//       'association' => 'comments',
//       'field' => 'id',
//       'function' => 'COUNT',
//       'where' => '{Comment}.status = "approved"', // not good
//       'group' => 'postId'
//     )
//   );

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

  protected $actions = array(
    'view' => 'Posts::view::%id%',
    'edit' => 'Admin::Posts::edit::%id%',
    'publish' => 'Admin::Posts::publish::%id%',
    'unpublish' => 'Admin::Posts::unpublish::%id%',
    'delete' => 'Admin::Posts::delete::%id%',
  );

  public function getRoute(ActiveRecord $record) {
    return array(
      'controller' => 'Posts',
      'action' => 'view',
      'parameters' => array($record->id)
    );
  }
  
  public function install() {
    if ($this->count() != 0)
      return;

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
      $comment->status = 'approved';
      $comment->save();
    }
    else {
      // install failed
    }
  }
}
