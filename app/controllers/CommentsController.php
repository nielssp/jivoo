<?php

class CommentsController extends AppController {

  protected $helpers = array('Html', 'Pagination', 'Form');
  
  protected $models = array('Post', 'Comment');

  public function index($post) {
    $this->render('not-implemented.html');
  }

  public function add($post) {
    if ($this->Auth->hasPermission('frontend.posts.comments.add')) {
      if ($this->request->hasValidData()) {
        $this->newComment = $this->Comment->create(
          $this->request->data['Comment'],
          array('author', 'email', 'website', 'content')
        );
        if (!empty($this->newComment->website)
          AND preg_match('/^https?:\/\//', $this->newComment->website) == 0) {
          $this->newComment->website = 'http://' . $this->newComment->website;
        }
        if ($this->user) {
          $this->newComment->user = $this->user;
          $this->newComment->author = $this->user->username;
          $this->newComment->email = $this->user->email;
        }
        $this->newComment->post = $this->post;
        $this->newComment->ip = $this->request->ip;
        if ($this->config['Blog']['commentApproval']
          AND !$this->Auth->hasPermission('backend.posts.comments.approve')) {
          $this->newComment->approved = false;
        }
        else {
          $this->newComment->approved = true;
        }
        if ($this->newComment->save()) {
          $this->Pagination->setCount($this->comments->count());
          if (!empty($this->newComment->author)) {
            $this->request->cookies['comment_author'] = $this->newComment->author;
          }
          if (!empty($this->newComment->email)) {
            $this->request->cookies['comment_email'] = $this->newComment->email;
          }
          if (!empty($this->newComment->website)) {
            $this->request->cookies['comment_website'] = $this->newComment->website;
          }

          $this->refresh(
            array('page' => $this->Pagination->getPages()),
            'comment' . $this->newComment->id
          );
        }
      }
      else {
        $this->newComment = $this->Comment->create();
        if (isset($this->request->cookies['comment_author'])) {
          $this->newComment->author = $this->request->cookies['comment_author'];
        }
        if (isset($this->request->cookies['comment_email'])) {
          $this->newComment->email = $this->request->cookies['comment_email'];
        }
        if (isset($this->request->cookies['comment_website'])) {
          $this->newComment->website = $this->request->cookies['comment_website'];
        }
      }
    }
    return $this->render();
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
