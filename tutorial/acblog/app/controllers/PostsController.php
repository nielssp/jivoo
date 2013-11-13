<?php
// app/controllers/PostsController.php
class PostsController extends AppController {

  // Models used by this controller
  protected $models = array('Post');

  // Helpers used by this controller
  protected $helpers = array('Html', 'Form');

  // Index action: View all posts
  public function index() {
    $select = SelectQuery::create()
      ->orderByDescending('created_at');
    $this->posts = $this->Post->all($select);
    $this->render();
  }

  // View action: View a single post
  public function view($postId) {
    $this->post = $this->Post->find($postId);
    if (!$this->post) {
      return $this->render('not-found.html');
    }
    return $this->render();
  }

  // Add action: Create a new post
  public function add() {
    if ($this->request->hasValidData()) {
      $this->post = $this->Post->create($this->request->data['post']);
      $this->post->created_at = time();
      if ($this->post->save()) {
        $this->session->notice('Post saved');
        $this->refresh();
      }
    }
    else {
      $this->post = $this->Post->create();
    }
    $this->render();
  }
}
