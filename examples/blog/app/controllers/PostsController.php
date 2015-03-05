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
  
  public function add() {
    if (!$this->Auth->isLoggedIn())
      $this->Auth->authenticationError();
    $this->title = tr('Add post');
    if ($this->request->hasValidData('Post')) {
      $this->post = $this->Post->create($this->request->data['Post']);
      if ($this->post->save()) {
        $this->session->flash->success = tr('Post saved.');
        return $this->redirect($this->post);
      }
    }
    else {
      $this->post = $this->Post->create();
    }
    return $this->render('posts/edit.html');
  }
  
  public function edit($postId) {
    if (!$this->Auth->isLoggedIn())
      $this->Auth->authenticationError();
    $this->post = $this->Post->find($postId);
    if (!$this->post)
      return $this->notFound();
    $this->title = tr('Edit post');
    if ($this->request->hasValidData('Post')) {
      $this->post->addData($this->request->data['Post']);
      if ($this->post->save()) {
        $this->session->flash->success = tr('Post saved.');
        return $this->redirect($this->post);
      }
    }
    return $this->render('posts/edit.html');
  }

  public function delete($postId) {
    if (!$this->Auth->isLoggedIn())
      $this->Auth->authenticationError();
    $this->post = $this->Post->find($postId);
    if (!$this->post)
      return $this->notFound();
    if ($this->request->hasValidData()) {
      $this->post->delete();
      $this->session->flash->success = tr('Post deleted.');
      return $this->redirect('index');
    }
    return $this->redirect($this->post);
  }
}