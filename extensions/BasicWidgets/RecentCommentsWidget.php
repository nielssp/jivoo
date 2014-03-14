<?php
/**
 * Recent comments widget
 * @package PeanutCMS\Posts
 * @property-read ActiveModel $Comment Comment model
 */
class RecentCommentsWidget extends WidgetBase {
  
  protected $models = array('Comment');
  
  public function getDefaultTitle() {
    return tr('Recent comments');
  }
  
  public function main($config) {
    $limit = 5;
    if (isset($config['limit'])) {
      $limit = $config['limit'];
    }
    $this->comments = $this->Comment
      ->where('approved = %b', true)
      ->orderByDescending('date')
      ->limit($limit);
    return $this->fetch();
  }
}
