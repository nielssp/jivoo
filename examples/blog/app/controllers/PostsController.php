<?php
namespace Blog\Controllers;

class PostsController extends AppController {
  
  protected $models = array('Post');
  
  public function index() {
    $this->posts = $this->Post->orderByDescending('created');
    return $this->render();
  }

  public function view($postId) {
    $this->post = $this->Post->find($postId);
    if (!$this->post)
      return $this->notFound();
    $this->title = $this->post->title;
    return $this->render();
  }
}