<?php
namespace Blog\Snippets\Sidebar;

use Jivoo\Snippets\SnippetBase;

class RecentPosts extends SnippetBase {
  protected $models = array('Post');
  
  public function get() {
    $limit = 5;

    $posts = $this->Post
      ->orderByDescending('created')
      ->limit($limit);

    $this->view->data->posts = $posts;

    return $this->render();
  }
}
