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
    $this->render('post.html');
  }
  
  public function create() {
    $form = new Form('create-post');
    $form->add(new InputText('title'));
    $form->add(new InputTextarea('content'));
    $form->add(new InputText('tags'));
    $form->add(new InputText('name'));
    $form->add(new InputBoolean('commenting'));
    $this->Html->beginForm('create-post');
  }
  
}
