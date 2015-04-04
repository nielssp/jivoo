<?php

interface ISnippet {
  public function render();
}

class Snippet implements ISnippet {
  
  public function get() {
    return $this->render();
  }
  
  public function post($data) {
    return $this->get();
  }
  
  public function put($data) {
    return $this->get();
  }
  
  public function patch($data) {
    return $this->get();
  }
  
  public function delete() {
    return $this->get();
  }
  
  public function render() {
    if ($this->request->isGet())
      return $this->get();
    $name = get_class($this);
    if (!$this->request->hasValidData($name))
      return $this->get();
    $data = $this->request->data[$name];
    switch ($this->request->method) {
      case 'POST':
        return $this->post($data);
      case 'PUT':
        return $this->put($data);
      case 'PATCH':
        return $this->patch($data);
      case 'DELETE':
        return $this->delete();
    }
    return $this->invalid();
  }
}



class AddComment extends Snippet {
  protected $models = array('Comment');
  
  public function get() {
    $comment = $this->Comment->create();
    return $this->render($comment);
  }
  
  public function post($data) {
    $comment = $this->Comment->create($data, array('name', 'content'));
    if ($comment->save()) {
      $this->session->flash->success = tr('Comment saved');
      return $this->refresh();
    }
    return $this->render($comment);
  }
}

class ViewComment extends Snippet {
  protected $models = array('Comment');
  
  protected $parameters = array('commentId');
  
  public function get() {
    $comment = $this->Comment->find($this->commentId);
    if ($comment === null)
      return $this->invalid();
    return $this->render($comment);
  }
}

class ViewComments extends Snippet {
  protected $models = array('Post', 'Comment');
  
  protected $parameters = array('postId');
  
  public function get() {
    $post = $this->Post->find($this->postId);
    if ($post === null)
      return $this->invalid();
    $comments = $this->Post->comments;
    return $this->render($comments);
  }
}


// view-comments.html.php
?>

<div>
<?php foreach ($comments as $comment): ?>
<?php echo $Snippet->ViewComment($comment); ?>
<?php endforeach; ?>
</div>

<?php echo $Snippet->AddComment(); ?>
