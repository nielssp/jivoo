<?php
/**
 * Recent posts widget
 * @package PeanutCMS\Posts
 * @property-read ActiveModel $Post Post model
 */
class RecentPostsWidget extends WidgetBase {
  
  protected $models = array('Post');
  
  public function getDefaultTitle() {
    return tr('Recent posts');
  }
  
  public function main($config) {
    $limit = 5;
    if (isset($config['limit'])) {
      $limit = $config['limit'];
    }
    $this->posts = $this->Post
      ->where('status = "published"')
      ->orderByDescending('createdAt')
      ->limit($limit);
    return $this->fetch();
  }
}
