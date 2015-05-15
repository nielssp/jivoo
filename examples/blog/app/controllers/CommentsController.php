<?php
namespace Blog\Controllers;

class CommentsController extends AppController {
  
  protected $models = array('Post', 'Comment');
  
  public function index($postId) {
    return $this->redirect(array(
      'controller' => 'Posts',
      'action' => 'view',
      'parameters' => array($postId),
      'fragment' => 'comments'
    ));
  }

  public function view($postId, $commentId) {
    $post = $this->Post->find($postId);
    if (!$post)
      return $this->notFound();

    $comment = $post->comments->find($commentId);
    if (!$comment)
      return $this->redirect($post);

    $position = $post->comments
      ->orderBy('created')
      ->rowNumber($comment);

    $page = ceil($position / 5);

    return $this->redirect(array(
      'controller' => 'Posts',
      'action' => 'view',
      'parameters' => array($post->id),
      'query' => array('page' => $page),
      'fragment' => 'comment' . $comment->id
    ));
  }
  
  public function add($postId) {
    return $this->redirect(array(
      'controller' => 'Posts',
      'action' => 'view',
      'parameters' => array($postId),
      'fragment' => 'comment'
    ));
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
