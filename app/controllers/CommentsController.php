<?php

class CommentsController extends ApplicationController {
  
  protected $helpers = array('Html', 'Pagination', 'Form', 'Filtering', 'Backend');
  
  public function index($post) {
    $this->render('not-implemented.html');
  }
  
  public function view($post, $comment) {
    $this->render('not-implemented.html');
  }

  public function manage() {
    $this->Backend->requireAuth('backend.comments.manage');
  
    $select = SelectQuery::create()
    ->orderByDescending('date');
  
    $this->Filtering->addSearchColumn('content');
    $this->Filtering->addFilterColumn('status');
    $this->Filtering->addFilterColumn('author');
    $this->Filtering->addFilterColumn('date');
  
    $this->Filtering->filter($select);
  
    if (isset($this->request->query['filter'])) {
      $this->Pagination->setCount(Comment::count(clone $select));
    }
    else {
      $this->Pagination->setCount(Comment::count());
    }
  
    $this->Pagination->setLimit(10)->paginate($select);
  
    $this->comments = Comment::all($select);
    $this->title = tr('Comments');
  
    $this->returnToThis();
    $this->render();
  }
  
  public function approve($comment = NULL) {
    $this->Backend->requireAuth('backend.comments.approve');
  
    if ($this->request->isPost()) {
      if (isset($comment)) {
        $comment = Comment::find($comment);
        if ($comment) {
          $comment->status = 'approved';
          $comment->save(array('validate' => FALSE));
        }
      }
    }
    if (!$this->request->isAjax()) {
      $this->goBack();
      $this->redirect(array('action' => 'comments'));
    }
  }
}