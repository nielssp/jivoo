<?php
namespace Blog\Snippets\Comments;

use Jivoo\Snippets\SnippetBase;

class Add extends SnippetBase {
  protected $helpers = array('Form');
  
  protected $models = array('Comment', 'Post');
  
  protected $parameters = array('postId');
  
  private $post;
  
  public function before() {
    $this->post = $this->Post->find($this->postId);
    if (!$this->post)
      return $this->invalid();
    return null;
  }
  
  public function get() {
    $comment = $this->post->comments->create();
    $this->view->data->comment = $comment;
    return $this->render('comments/edit.html');
  }
  
  public function post($data) {
    $comment = $this->post->comments->create(
      $data['Comment'],
      array('author', 'content')
    );
    if ($comment->save()) {
      $this->session->flash->success = tr('Comment saved.');
      return $this->redirect($comment);
    }
    $this->view->data->comment = $comment;
    return $this->render('comments/edit.html');
  }
}
