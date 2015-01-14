<?php
/**
 * Recent comments widget
 * @package PeanutCMS\Posts
 * @property-read ActiveModel $Comment Comment model
 */
class RecentCommentsWidget extends Widget {
  
  protected $models = array('Comment');
  
  protected $helpers = array('Html');
  
  public function getDefaultTitle() {
    return tr('Recent comments');
  }
  
  public function main($config) {
    $limit = 5;
    if (isset($config['limit'])) {
      $limit = $config['limit'];
    }
    $this->comments = $this->Comment
      ->where('status = %CommentStatus', 'approved')
      ->orderByDescending('created')
      ->limit($limit);
    return $this->fetch();
  }
}
