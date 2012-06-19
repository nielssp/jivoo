<?php

class PostsController extends ApplicationController {

  protected $helpers = array('Html');

  public function index() {
    $select = SelectQuery::create()
      ->orderByDescending('date')
      ->limit(5);
    if (isset($this->request->query['offset'])) {
      $select->offset($this->request->query['offset']);
    }
    $this->posts = Post::all($select);

    $this->render();
  }

  public function view($post) {
    $this->post = Post::find($post);
    $this->title = $this->post->title;
    $this->render();
  }
  
  public function add() {
    if ($this->request->isPost()) {
    }
    $this->render();
  }

  public function tagIndex() {
    $this->render('not-implemented.html');
  }
  
  public function viewTag($tag) {
    $this->render('not-implemented.html');
  }
  
  public function commentIndex($post) {
    $this->render('not-implemented.html');
  }
  
  public function viewComment($post, $comment) {
    $this->render('not-implemented.html');
  }
  
}
