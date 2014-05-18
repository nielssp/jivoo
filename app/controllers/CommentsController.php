<?php
class CommentsController extends AppController {

  protected $helpers = array('Html','Pagination','Form');

  protected $models = array('Post','Comment');

  public function before() {
    parent::before();
    $this->config = $this->config['blog'];
  }

  public function index($post) {
    return $this->render('not-implemented.html');
  }

  public function add($post) {
    if ($this->Auth->isAllowed('frontend.posts.comments.add')) {
      $commentValidator = $this->Comment->getValidator();
      if ($this->config['anonymousCommenting']) {
        unset($commentValidator->author->presence);
        unset($commentValidator->email->presence);
      }
      else {
        $commentValidator->author->presence = true;
        $commentValidator->email->presence = true;
      }
      if ($this->request->hasValidData()) {
        $this->newComment = $this->Comment->create(
          $this->request->data['Comment'], 
          array('author','email','website','content'));
        if (!empty($this->newComment->website) and
             preg_match('/^https?:\/\//', $this->newComment->website) == 0) {
          $this->newComment->website = 'http://' . $this->newComment->website;
        }
        if ($this->user) {
          $this->newComment->user = $this->user;
          $this->newComment->author = $this->user->username;
          $this->newComment->email = $this->user->email;
        }
        $this->newComment->post = $this->post;
        $this->newComment->ip = $this->request->ip;
        if ($this->config['commentApproval'] and
             !$this->Auth->isAllowed('backend.posts.comments.approve')) {
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
          
          $this->refresh(array('page' => $this->Pagination->getPages()), 
            'comment' . $this->newComment->id);
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
}
