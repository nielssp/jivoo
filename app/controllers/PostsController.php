<?php

class PostsController extends ApplicationController {

  protected $helpers = array('Html', 'Pagination', 'Form');

  public function index() {
    $select = SelectQuery::create()
      ->orderByDescending('date');
    $this->Pagination->setCount(Post::count());
    
    $this->Pagination->paginate($select);

    $this->posts = Post::all($select);

    $this->render();
  }

  public function view($post) {
    $this->reroute();

    $this->post = Post::find($post);

    if (!$this->post) {
      $this->render('404.html');
      return;
    }
    $this->title = $this->post->title;
    if ($this->request->isAjax()) {
      if ($this->request->isPost()) {
        echo json_encode($this->request->data);
      }
      else {
        echo json_encode($this->request->query);
      }
//      echo $this->post->json();
    }
    else {
      $this->render();
    }
  }
  
  public function add() {
    if ($this->request->isPost()) {
      $this->request->form['post'];
    }
    $this->render('backend/edit-post.html');
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
