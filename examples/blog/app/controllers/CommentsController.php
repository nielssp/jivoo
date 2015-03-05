<?php
namespace Blog\Controllers;

class CommentsController extends AppController {
  
  protected $models = array('Post', 'Comment');
  
  public function index($postId) {
    $this->post = $this->Post->find($postId);
    if (!$this->post)
      return $this->notFound();
    
    $this->comments = $this->post->comments->orderBy('created');
    return $this->render();
  }

  public function view($postId, $commentId) {
    $this->post = $this->Post->find($postId);
    if (!$this->post)
      return $this->notFound();
    $this->comment = $this->post->comments->find($commentId);
    if (!$this->comment)
      return $this->notFound();
    $this->title = $this->post->title;
    return $this->render();
  }
  
  public function add($postId) {
    $this->post = $this->Post->find($postId);
    if (!$this->post)
      return $this->notFound();

    $this->title = tr('Add comment');
    if ($this->request->hasValidData('Comment')) {
      $this->comment = $this->post->comments->create(
        $this->request->data['Comment'],
        array('author', 'content')
      );
      if ($this->comment->save()) {
        $this->session->flash->success = tr('Comment saved.');
        return $this->redirect($this->comment);
      }
    }
    else {
      $this->comment = $this->post->comments->create();
    }
    return $this->render('comments/edit.html');
  }
  
  public function edit($postId, $commentId) {
    if (!$this->Auth->isLoggedIn())
      $this->Auth->authenticationError();

    $this->post = $this->Post->find($postId);
    if (!$this->post)
      return $this->notFound();
    $this->comment = $this->post->comments->find($commentId);
    if (!$this->comment)
      return $this->notFound();
    
    $this->title = tr('Edit comment');
    if ($this->request->hasValidData('Comment')) {
      $this->comment->addData($this->request->data['Comment']);
      if ($this->comment->save()) {
        $this->session->flash->success = tr('Comment saved.');
        return $this->redirect($this->comment);
      }
    }
    return $this->render('comments/edit.html');
  }

  public function delete($postId, $commentId) {
    if (!$this->Auth->isLoggedIn())
      $this->Auth->authenticationError();
    $this->post = $this->Post->find($postId);
    if (!$this->post)
      return $this->notFound();
    $this->comment = $this->post->comments->find($commentId);
    if (!$this->comment)
      return $this->notFound();
    
    if ($this->request->hasValidData()) {
      $this->comment->delete();
      $this->session->flash->success = tr('Comment deleted.');
      return $this->redirect($this->post);
    }
    return $this->redirect($this->comment);
  }
}