<?php

class PostsController extends ApplicationController {

  protected $helpers = array('Html');

  public function index() {
    $this->posts = Post::all(
      SelectQuery::create()
        ->orderByDescending('date')
        ->limit(5)
    );

    $this->render();
  }

  public function view($post) {
    $this->post = Post::find($post);
    $this->title = $this->post->title;
    $this->render();
  }
  
  public function add() {
    if ($this->Request->isPost()) {
    }
    $this->render();
  }
  
}
