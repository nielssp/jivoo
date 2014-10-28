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

  protected $virtual = array(
    'jsonTags'
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
  
  public function afterSave(ActiveModelEvent $event) {
    if (isset($event->record->jsonTags)) {
      $tags = Json::decode($event->record->jsonTags);
      if (is_array($tags)) {
        $event->record->tags->removeAll();
        $Tag = $this->getDatabase()->Tag;
        foreach ($tags as $name => $tag) {
          $existing = $Tag->where('name = %s', $name)->first();
          if ($existing) {
            $event->record->tags->add($existing);
          }
          else {
            $new = $Tag->create();
            $new->tag = $tag;
            $new->name = $name;
            if ($new->save())
              $event->record->tags->add($new);
          }
        }
      }
    }
  }
  
  public function recordCreateJsonTags(ActiveRecord $record) {
    if (!$record->isNew()) {
      $tagObject = array();
      foreach ($record->tags as $tag) {
        $tagObject[$tag->name] = $tag->tag;
      }
      if (count($tagObject) == 0)
        $record->jsonTags = '{}';
      else
        $record->jsonTags = Json::encode($tagObject);
    }
    else {
      $record->jsonTags = '{}';
    }
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
