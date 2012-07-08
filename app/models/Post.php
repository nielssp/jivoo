<?php

/**
 * Post model
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string $content
 * @property int $date Timestamp
 * @method Tag[] getTags() Retrieve all tags associated with post
 * @method bool hasTag(Tag $tag) Check if a tag belongs to post
 * @method void addTag(Tag $tag) Add a tag to post
 * @method void removeTag(Tag $tag) Remove tag from post
 * @method Comment[] getComments() Retrieve all comments associated with post
 * @method bool hasComment(Comment $comment) Check if a comment belongs to post
 * @method void addComment(Comment $comment) Add a comment to post
 * @method void removeComment(Comment $comment) Remove a comment from post
 * @method User getUser() Retrieve the user associeated with post
 * @method void setUser(User $user) Set the user
 * @method Category getCategory()
 * @method void setCategory(Category $category)
 */
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

  protected $validate = array(
    'title' => array(
      'presence' => TRUE,
    ),
    'name' => array(
      'presence' => TRUE,
      'unique' => TRUE,
      'minLength' => 1,
      'maxLength' => 50,
      array(
        'match' => '/^[a-z0-9-]+$/',
        'message' => 'Only lowercase letters, numbers and dashes allowed.'
      ),
    ),
  );

  protected $defaults = array(
    'comments' => 0,
    'user_id' => 0
  );

  public function getRoute() {
    return array(
      'controller' => 'Posts',
      'action' => 'view',
      'parameters' => array($this->id)
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

  public function formatDate() {
    return fdate($this->date);
  }

  public function formatTime() {
    return ftime($this->date);
  }

  public function removeAllTags() {
    $tags = $this->getTags();
    foreach ($tags as $tag) {
      $this->removeTag($tag);
    }
  }

  public function createAndAddTags($csvTags) {
    $tags = explode(',', $csvTags);
    foreach ($tags as $title) {
      $title = trim($title);
      $name = Tag::createName($title);
      if ($title == '' OR $name == '') {
        continue;
      }
      $existing = Tag::first(
        SelectQuery::create()
          ->where('name = ?')
          ->addVar($name)
      );
      if ($existing !== FALSE) {
        $this->addTag($existing);
      }
      else {
        $tag = Tag::create();
        $tag->tag = $title;
        $tag->name = $name;
        $tag->save();
        $this->addTag($tag);
      }
    }
  }

  public function getCommentHierachy() {
    // recursive action here!!
  }
}

