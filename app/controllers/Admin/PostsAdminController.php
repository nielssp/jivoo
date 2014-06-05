<?php
class PostsAdminController extends AdminController {
  
  protected $models = array('Post');
  
  public function index() {
    return $this->render('admin/dashboard.html');
  }
  public function add() {
    $this->title = tr('Add post');
    $this->post = $this->Post->create();
    return $this->render();
  }
}