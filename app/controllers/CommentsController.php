<?php

class CommentsController extends AppController {

  protected $helpers = array('Html', 'Pagination', 'Form', 'Filtering',
    'Backend', 'Json', 'Bulk'
  );
  
  protected $models = array('Comment');

  public function preRender() {
    $this->Filtering->addSearchColumn('content');
    $this->Filtering->addFilterColumn('status');
    $this->Filtering->addFilterColumn('author');
    $this->Filtering->addFilterColumn('date');

    $this->Filtering->addPredefined(tr('Approved'), 'status:approved');
    $this->Filtering->addPredefined(tr('Pending'), 'status:pending');
    $this->Filtering->addPredefined(tr('Spam'), 'status:spam');

    $this->Pagination->setLimit(10);

    $this->Bulk
      ->addUpdateAction('approve', tr('Approve'), array('status' => 'approved'));
    $this->Bulk
      ->addUpdateAction('unapprove', tr('Unapprove'),
        array('status' => 'pending')
      );
    $this->Bulk->addUpdateAction('spam', tr('Spam'), array('status' => 'spam'));
    $this->Bulk
      ->addUpdateAction('notspam', tr('Not spam'),
        array('status' => 'approved')
      );

    $this->Bulk->addDeleteAction('delete', tr('Delete'));
  }

  public function index($post) {
    $this->render('not-implemented.html');
  }

  public function view($post, $comment) {
    $this->render('not-implemented.html');
  }
}
