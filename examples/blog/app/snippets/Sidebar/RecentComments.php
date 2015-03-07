<?php
namespace Blog\Snippets\Sidebar;

use Jivoo\Snippets\Snippet;

class RecentComments extends Snippet {
  protected $models = array('Comment');
  
  public function get() {
    $limit = 5;

    $comments = $this->Comment
      ->orderByDescending('created')
      ->limit($limit);

    $this->view->data->comments = $comments;

    return $this->render();
  }
}
