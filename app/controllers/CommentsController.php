<?php

class CommentsController extends ApplicationController {
  
  protected $helpers = array('Html', 'Pagination', 'Form', 'Filtering', 'Backend', 'Json', 'Bulk');
  
  public function init() {
    $this->Filtering->addSearchColumn('content');
    $this->Filtering->addFilterColumn('status');
    $this->Filtering->addFilterColumn('author');
    $this->Filtering->addFilterColumn('date');
    
    $this->Filtering->addPredefined(tr('Approved'), 'status:approved');
    $this->Filtering->addPredefined(tr('Pending'), 'status:pending');
    $this->Filtering->addPredefined(tr('Spam'), 'status:spam');
    
    $this->Pagination->setLimit(10);
    
    $this->Bulk->addUpdateAction('approve', tr('Approve'), array('status' => 'approved'));
    $this->Bulk->addUpdateAction('unapprove', tr('Unapprove'), array('status' => 'pending'));
    $this->Bulk->addUpdateAction('spam', tr('Spam'), array('status' => 'spam'));
    $this->Bulk->addUpdateAction('notspam', tr('Not spam'), array('status' => 'approved'));
    
    $this->Bulk->addDeleteAction('delete', tr('Delete'));
  }
  
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
  
    $this->Filtering->filter($select);
  
    if (isset($this->request->query['filter'])) {
      $this->Pagination->setCount(Comment::count($select));
    }
    else {
      $this->Pagination->setCount(Comment::count());
    }
    
    if ($this->Bulk->isBulk()) {
      if ($this->Bulk->isDelete()) {
        $query = SelectQuery::create();
      }
      else {
        $query = UpdateQuery::create();
      }
      $this->Filtering->filter($query);
      $this->Bulk->select($query);
      Comment::execute($query);
      if (!$this->request->isAjax()) {
        $this->refresh();
      }
    }
  
    $this->Pagination->paginate($select);
  
    $this->comments = Comment::all($select);
    $this->title = tr('Comments');

    if ($this->request->isAjax()) {
      $html = '';
      foreach ($this->comments as $this->comment) {
        $html .= $this->render('comments/comment.html', true);
      }
      $this->Json->respond(array('html' => $html));
    }
    else {
      $this->returnToThis();
      $this->render();
    }
  }

  public function edit($comment) {
    $this->Backend->requireAuth('backend.comments.edit');

    $this->comment = Comment::find($comment);
    if (!isset($this->comment)) {
    }

    if ($this->request->isPost() AND $this->request->checkToken()) {
      $this->comment->addData($this->request->data['comment']);
      $this->comment->save(array('validate' => false));
      if (!$this->request->isAjax()) {
        $this->goBack();
        $this->redirect(array('action' => 'comments'));
      }
    }
    
    $this->title = tr('Edit comment');
    
    if (!$this->request->isAjax()) {
      $this->render();
    }
    else {
      $this->Json->respond(array(
        'html' => $this->render('comments/comment.html', true)
      ));
    }
  }

  public function delete($comment) {
    $this->Backend->requireAuth('backend.comments.delete');
  }
}
