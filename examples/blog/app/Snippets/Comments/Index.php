<?php
namespace Blog\Snippets\Comments;

use Jivoo\Snippets\SnippetBase;

class Index extends SnippetBase {
  protected $helpers = array('Pagination');
  
  protected $models = array('Comment', 'Post');
  
  protected $parameters = array('postId');
  
  public function get() {
    $post = $this->Post->find($this->postId);
    if (!$post)
      return $this->invalid();

    $comments = $post->comments->orderBy('created');
    
    $comments = $this->Pagination->paginate($comments);

    $this->viewData['comments'] = $comments;

    return $this->render();
  }
}