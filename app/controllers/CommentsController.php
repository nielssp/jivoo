<?php

class CommentsController extends AppController {

  protected $helpers = array('Html', 'Pagination', 'Form');
  
  protected $models = array('Post', 'Comment');

  public function index($post) {
    $this->render('not-implemented.html');
  }

  public function view($post, $comment) {
    $this->post = $this->Post->find($post);
    
    if (!$this->post
      OR ($this->post->status != 'published'
        AND !$this->Auth->hasPermission('backend.posts.viewDraft'))) {
      return $this->render('404.html');
    }
    $comments = $this->post->getComments(
      SelectQuery::create()->orderBy('date')->where('status = "approved"')
    );
    $this->render('not-implemented.html');
  }
}
